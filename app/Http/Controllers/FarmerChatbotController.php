<?php

namespace App\Http\Controllers;

use App\Models\Prediction;
use App\Services\GeminiChatService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Throwable;

class FarmerChatbotController extends Controller
{
    private const SESSION_HISTORY_LIMIT = 20;
    private const CONTEXT_HISTORY_LIMIT = 6;
    private const QUOTA_COOLDOWN_SECONDS = 65;
    private const IN_FLIGHT_REQUEST_SECONDS = 30;
    private const DUPLICATE_RESPONSE_TTL_SECONDS = 12;
    private const IDEMPOTENCY_RESPONSE_TTL_SECONDS = 180;
    private const RECENT_PREDICTIONS_CACHE_SECONDS = 120;

    public function history(Request $request): JsonResponse
    {
        return response()->json([
            'success' => true,
            'history' => $this->getHistory($request),
        ]);
    }

    public function send(Request $request, GeminiChatService $chatService): JsonResponse
    {
        $cooldownSeconds = $this->getQuotaCooldownSeconds($request);

        if ($cooldownSeconds > 0) {
            return response()->json([
                'success' => false,
                'message' => "The assistant is temporarily waiting for Gemini quota reset. Please try again in {$cooldownSeconds} seconds.",
            ], 429);
        }

        $validated = $request->validate([
            'message' => ['required', 'string', 'max:2000'],
            'request_id' => ['nullable', 'string', 'max:128'],
        ]);

        $message = trim(strip_tags((string) $validated['message']));
        $requestId = $this->sanitizeRequestId((string) ($validated['request_id'] ?? ''));

        if ($message === '') {
            return response()->json([
                'success' => false,
                'message' => 'Please enter a valid question.',
            ], 422);
        }

        if ($requestId !== null) {
            $cachedRequestResponse = Cache::get($this->idempotencyResponseCacheKey($request, $requestId));

            if (is_array($cachedRequestResponse) && isset($cachedRequestResponse['success']) && $cachedRequestResponse['success'] === true) {
                return response()->json($cachedRequestResponse);
            }
        }

        $history = $this->getHistory($request);
        $messageFingerprint = $this->messageFingerprint($message);
        $historyFingerprint = $this->historyFingerprint($history);
        $cachedResponse = Cache::get($this->duplicateResponseCacheKey($request, $messageFingerprint, $historyFingerprint));

        if (is_array($cachedResponse) && isset($cachedResponse['success']) && $cachedResponse['success'] === true) {
            return response()->json($cachedResponse);
        }

        $inFlightLock = Cache::lock($this->inFlightRequestKey($request), self::IN_FLIGHT_REQUEST_SECONDS);

        if (!$inFlightLock->get()) {
            return response()->json([
                'success' => false,
                'message' => 'Please wait for the current assistant reply to finish before sending another question.',
            ], 429);
        }

        try {
            $history = $this->getHistory($request);
            $historyFingerprint = $this->historyFingerprint($history);
            $cachedResponse = Cache::get($this->duplicateResponseCacheKey($request, $messageFingerprint, $historyFingerprint));

            if (is_array($cachedResponse) && isset($cachedResponse['success']) && $cachedResponse['success'] === true) {
                return response()->json($cachedResponse);
            }

            try {
                $result = $chatService->generateReply(
                    $message,
                    array_slice($history, -self::CONTEXT_HISTORY_LIMIT),
                    $this->buildFarmerContext($request)
                );
            } catch (Throwable $exception) {
                report($exception);

                $isQuotaError = $this->isQuotaOrRateLimitError($exception->getMessage());

                if ($isQuotaError) {
                    $this->activateQuotaCooldown($request);
                }

                Log::warning('Farmer chatbot request failed.', [
                    'user_id' => $request->user()?->id,
                    'error' => $exception->getMessage(),
                    'is_quota_error' => $isQuotaError,
                ]);

                return response()->json([
                    'success' => false,
                    'message' => $this->toFriendlyMessage($exception->getMessage()),
                ], $isQuotaError ? 429 : 503);
            }

            $now = now()->toIso8601String();

            $history[] = [
                'role' => 'user',
                'text' => $message,
                'at' => $now,
            ];

            $history[] = [
                'role' => 'assistant',
                'text' => $result['reply'],
                'at' => $now,
            ];

            $history = array_slice($history, -self::SESSION_HISTORY_LIMIT);
            $this->putHistory($request, $history);

            $responsePayload = [
                'success' => true,
                'reply' => $result['reply'],
                'history' => $history,
                'metadata' => [
                    'model' => $result['model'] ?? null,
                    'finish_reason' => $result['finish_reason'] ?? null,
                    'truncated' => $result['truncated'] ?? null,
                    'tokens' => $result['tokens'] ?? null,
                    'request_id' => $requestId,
                ],
            ];

            Cache::put(
                $this->duplicateResponseCacheKey($request, $messageFingerprint, $historyFingerprint),
                $responsePayload,
                self::DUPLICATE_RESPONSE_TTL_SECONDS
            );

            if ($requestId !== null) {
                Cache::put(
                    $this->idempotencyResponseCacheKey($request, $requestId),
                    $responsePayload,
                    self::IDEMPOTENCY_RESPONSE_TTL_SECONDS
                );
            }

            return response()->json($responsePayload);
        } finally {
            try {
                $inFlightLock->release();
            } catch (Throwable $exception) {
                Log::debug('Farmer chatbot in-flight lock release skipped.', [
                    'user_id' => $request->user()?->id,
                    'error' => $exception->getMessage(),
                ]);
            }
        }
    }

    public function reset(Request $request): JsonResponse
    {
        $request->session()->forget($this->historySessionKey($request));

        return response()->json([
            'success' => true,
            'message' => 'Conversation reset.',
            'history' => [],
        ]);
    }

    private function buildFarmerContext(Request $request): array
    {
        $user = $request->user();

        $recentPredictions = Cache::remember(
            $this->recentPredictionsCacheKey($user->id),
            self::RECENT_PREDICTIONS_CACHE_SECONDS,
            function () use ($user): array {
                return Prediction::query()
                    ->where('user_id', $user->id)
                    ->latest('created_at')
                    ->limit(3)
                    ->get(['crop', 'municipality', 'predicted_production_mt', 'confidence_score', 'created_at'])
                    ->map(function (Prediction $prediction): array {
                        return [
                            'crop' => $prediction->crop,
                            'municipality' => $prediction->municipality,
                            'predicted_production_mt' => round((float) $prediction->predicted_production_mt, 2),
                            'confidence_score' => $prediction->confidence_score !== null
                                ? round((float) $prediction->confidence_score, 4)
                                : null,
                            'created_at' => optional($prediction->created_at)->toDateString(),
                        ];
                    })
                    ->values()
                    ->all();
            }
        );

        return [
            'preferred_municipality' => $user->preferred_municipality,
            'favorite_crops' => is_array($user->favorite_crops) ? $user->favorite_crops : [],
            'recent_predictions' => $recentPredictions,
        ];
    }

    private function toFriendlyMessage(string $rawError): string
    {
        $normalizedError = strtolower($rawError);

        if (str_contains($normalizedError, 'api key') || str_contains($normalizedError, 'permission_denied') || str_contains($normalizedError, 'unauthorized')) {
            return 'Assistant configuration is not ready yet. Please contact the administrator.';
        }

        if (str_contains($normalizedError, 'resource_exhausted') || str_contains($normalizedError, 'quota') || str_contains($normalizedError, '429')) {
            return 'The assistant hit a Gemini API rate or quota limit. Enable paid tier/billing for this Gemini project or wait for quota reset, then try again.';
        }

        if (str_contains($normalizedError, 'not_found') || str_contains($normalizedError, '404')) {
            return 'The configured Gemini model was not found for this API project. Check GEMINI_MODEL and ensure it is available in your project tier.';
        }

        if (str_contains($normalizedError, 'timed out') || str_contains($normalizedError, 'timeout')) {
            return 'The assistant took too long to respond. Please try again.';
        }

        return 'The assistant is temporarily unavailable. Please try again shortly.';
    }

    private function historySessionKey(Request $request): string
    {
        return 'farmer_chatbot.history.' . $request->user()->id;
    }

    private function getHistory(Request $request): array
    {
        $history = $request->session()->get($this->historySessionKey($request), []);

        return is_array($history) ? $history : [];
    }

    private function putHistory(Request $request, array $history): void
    {
        $request->session()->put($this->historySessionKey($request), $history);
    }

    private function isQuotaOrRateLimitError(string $rawError): bool
    {
        $normalizedError = strtolower($rawError);

        return str_contains($normalizedError, 'resource_exhausted')
            || str_contains($normalizedError, 'quota')
            || str_contains($normalizedError, 'too many requests')
            || str_contains($normalizedError, '429');
    }

    private function quotaCooldownKey(Request $request): string
    {
        return 'farmer_chatbot.quota_cooldown.' . $request->user()->id;
    }

    private function activateQuotaCooldown(Request $request): void
    {
        $unlockTimestamp = now()->addSeconds(self::QUOTA_COOLDOWN_SECONDS)->timestamp;

        Cache::put(
            $this->quotaCooldownKey($request),
            $unlockTimestamp,
            self::QUOTA_COOLDOWN_SECONDS
        );
    }

    private function getQuotaCooldownSeconds(Request $request): int
    {
        $unlockTimestamp = Cache::get($this->quotaCooldownKey($request));

        if (!is_int($unlockTimestamp)) {
            return 0;
        }

        return max(0, $unlockTimestamp - now()->timestamp);
    }

    private function inFlightRequestKey(Request $request): string
    {
        return 'farmer_chatbot.in_flight.' . $request->user()->id;
    }

    private function duplicateResponseCacheKey(Request $request, string $messageFingerprint, string $historyFingerprint): string
    {
        return 'farmer_chatbot.duplicate_response.'
            . $request->user()->id
            . '.' . $historyFingerprint
            . '.' . $messageFingerprint;
    }

    private function idempotencyResponseCacheKey(Request $request, string $requestId): string
    {
        return 'farmer_chatbot.request_response.'
            . $request->user()->id
            . '.' . hash('sha256', strtolower(trim($requestId)));
    }

    private function sanitizeRequestId(string $requestId): ?string
    {
        $trimmed = trim($requestId);

        if ($trimmed === '') {
            return null;
        }

        $normalized = preg_replace('/[^A-Za-z0-9._:-]/', '', $trimmed);

        if (!is_string($normalized) || $normalized === '') {
            return null;
        }

        return substr($normalized, 0, 128);
    }

    private function messageFingerprint(string $message): string
    {
        return hash('sha256', strtolower(trim($message)));
    }

    private function historyFingerprint(array $history): string
    {
        $compactHistory = array_map(
            static function ($entry): array {
                if (!is_array($entry)) {
                    return ['role' => '', 'text' => ''];
                }

                return [
                    'role' => strtolower((string) ($entry['role'] ?? '')),
                    'text' => substr(trim((string) ($entry['text'] ?? '')), 0, 180),
                ];
            },
            array_slice($history, -self::CONTEXT_HISTORY_LIMIT)
        );

        $encoded = json_encode($compactHistory, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        return hash('sha256', $encoded !== false ? $encoded : '[]');
    }

    private function recentPredictionsCacheKey(int|string $userId): string
    {
        return 'farmer_chatbot.recent_predictions.' . $userId;
    }
}
