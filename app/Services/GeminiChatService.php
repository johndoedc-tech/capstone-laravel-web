<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Throwable;

class GeminiChatService
{
    private string $baseUrl;
    private ?string $apiKey;
    private string $model;
    private array $fallbackModels;
    private int $timeout;
    private int $retries;

    public function __construct()
    {
        $baseUrl = $this->sanitizeConfigString((string) config('services.gemini.base_url', 'https://generativelanguage.googleapis.com/v1beta'));
        $apiKey = $this->sanitizeConfigString((string) config('services.gemini.api_key', ''));
        $model = $this->sanitizeConfigString((string) config('services.gemini.model', 'gemini-2.0-flash'));

        $this->baseUrl = rtrim($baseUrl !== '' ? $baseUrl : 'https://generativelanguage.googleapis.com/v1beta', '/');
        $this->apiKey = $apiKey !== '' ? $apiKey : null;
        $this->model = $model !== '' ? $model : 'gemini-2.0-flash';
        $this->fallbackModels = $this->normalizeFallbackModels(config('services.gemini.fallback_models', []));
        $this->timeout = (int) config('services.gemini.timeout', 20);
        $this->retries = (int) config('services.gemini.retries', 0);
    }

    public function generateReply(string $message, array $history = [], array $context = []): array
    {
        $trimmedMessage = trim($message);

        if ($trimmedMessage === '') {
            throw new RuntimeException('Chat message cannot be empty.');
        }

        if ($this->apiKey === null || trim($this->apiKey) === '') {
            throw new RuntimeException('Gemini API key is not configured.');
        }

        $contents = $this->buildContents($trimmedMessage, $history);

        $payload = [
            'systemInstruction' => [
                'parts' => [
                    ['text' => $this->buildSystemInstruction($context)],
                ],
            ],
            'contents' => $contents,
            'generationConfig' => [
                'temperature' => 0.4,
                'maxOutputTokens' => 320,
            ],
        ];

        $modelsToTry = array_values(array_unique(array_merge([$this->model], $this->fallbackModels)));
        $lastException = null;

        foreach ($modelsToTry as $modelName) {
            try {
                return $this->requestWithModel($modelName, $payload);
            } catch (RuntimeException $exception) {
                $lastException = $exception;

                if ($this->isQuotaOrRateLimitError($exception->getMessage()) && count($modelsToTry) > 1) {
                    Log::warning('Gemini chatbot model exhausted. Trying fallback model.', [
                        'current_model' => $modelName,
                        'error' => $exception->getMessage(),
                    ]);

                    continue;
                }

                throw $exception;
            }
        }

        throw $lastException ?? new RuntimeException('Gemini API request failed on all configured models.');
    }

    private function requestWithModel(string $modelName, array $payload): array
    {
        $url = "{$this->baseUrl}/models/{$modelName}:generateContent";
        $attempts = max(1, $this->retries + 1);

        $startTime = microtime(true);

        try {
            $response = Http::timeout($this->timeout)
                ->retry($attempts, 250)
                ->withQueryParameters(['key' => $this->apiKey])
                ->acceptJson()
                ->post($url, $payload);
        } catch (Throwable $exception) {
            Log::error('Gemini chatbot request failed before response.', [
                'model' => $modelName,
                'error' => $exception->getMessage(),
            ]);

            throw $exception;
        }

        $latencyMs = round((microtime(true) - $startTime) * 1000, 2);
        $body = $response->json();
        $body = is_array($body) ? $body : [];

        if (!$response->successful()) {
            $providerMessage = $this->extractProviderErrorMessage($body);

            Log::warning('Gemini chatbot provider returned non-success response.', [
                'status' => $response->status(),
                'model' => $modelName,
                'latency_ms' => $latencyMs,
                'provider_error' => $providerMessage,
            ]);

            throw new RuntimeException("Gemini API request failed ({$response->status()}): {$providerMessage}");
        }

        $reply = $this->extractReplyText($body);

        if ($reply === null || trim($reply) === '') {
            Log::warning('Gemini chatbot response missing text candidate.', [
                'model' => $modelName,
                'latency_ms' => $latencyMs,
            ]);

            throw new RuntimeException('Gemini response did not include text output.');
        }

        $finishReason = strtoupper((string) data_get($body, 'candidates.0.finishReason', ''));

        $tokenUsage = [
            'prompt' => data_get($body, 'usageMetadata.promptTokenCount'),
            'response' => data_get($body, 'usageMetadata.candidatesTokenCount'),
            'total' => data_get($body, 'usageMetadata.totalTokenCount'),
        ];

        $reply = $this->normalizeAssistantReply($reply, $finishReason === 'MAX_TOKENS');

        Log::info('Gemini chatbot response generated.', [
            'model' => $modelName,
            'latency_ms' => $latencyMs,
            'finish_reason' => $finishReason,
            'token_usage' => $tokenUsage,
        ]);

        return [
            'reply' => trim($reply),
            'model' => $modelName,
            'tokens' => $tokenUsage,
        ];
    }

    private function normalizeFallbackModels(mixed $fallbackModels): array
    {
        if (is_string($fallbackModels)) {
            $fallbackModels = array_map('trim', explode(',', $fallbackModels));
        }

        if (!is_array($fallbackModels)) {
            return [];
        }

        return array_values(array_filter(array_map(fn ($model) => $this->sanitizeConfigString((string) $model), $fallbackModels)));
    }

    private function sanitizeConfigString(string $value): string
    {
        return trim($value, " \t\n\r\0\x0B\"'");
    }

    private function isQuotaOrRateLimitError(string $message): bool
    {
        $normalizedMessage = strtolower($message);

        return str_contains($normalizedMessage, 'resource_exhausted')
            || str_contains($normalizedMessage, 'quota')
            || str_contains($normalizedMessage, 'too many requests')
            || str_contains($normalizedMessage, '429');
    }

    private function buildContents(string $message, array $history): array
    {
        $contents = [];

        foreach ($history as $entry) {
            if (!is_array($entry)) {
                continue;
            }

            $text = trim((string) ($entry['text'] ?? ''));

            if ($text === '') {
                continue;
            }

            $rawRole = strtolower((string) ($entry['role'] ?? 'user'));
            $role = $rawRole === 'assistant' ? 'model' : 'user';

            $contents[] = [
                'role' => $role,
                'parts' => [
                    ['text' => $text],
                ],
            ];
        }

        $contents[] = [
            'role' => 'user',
            'parts' => [
                ['text' => $message],
            ],
        ];

        return $contents;
    }

    private function buildSystemInstruction(array $context): string
    {
        $instruction = [
            'You are Harviana Assistant, a practical agriculture helper for farmers in Benguet.',
            'Focus on crop planning, production interpretation, weather-aware decision support, and how to use Harviana map/prediction features.',
            'Keep answers short, clear, and actionable.',
            'Respond in plain text only. Do not use Markdown symbols such as **, *, #, or backticks.',
            'Do not use markdown bullets. If listing items, use simple plain-text numbering like 1., 2., 3.',
            'Use at most 4 short sentences (or one compact list) and always end with a complete sentence.',
            'If data is missing or uncertain, say it clearly and suggest the next best step.',
            'Do not claim live data access unless the context explicitly includes it.',
            'Mirror the user language (English or Filipino).',
        ];

        $contextLines = $this->formatContext($context);

        if ($contextLines !== '') {
            $instruction[] = 'Farmer context:';
            $instruction[] = $contextLines;
        }

        return implode("\n", $instruction);
    }

    private function formatContext(array $context): string
    {
        $lines = [];

        $preferredMunicipality = trim((string) ($context['preferred_municipality'] ?? ''));
        if ($preferredMunicipality !== '') {
            $lines[] = "- Preferred municipality: {$preferredMunicipality}";
        }

        $favoriteCrops = $context['favorite_crops'] ?? [];
        if (is_array($favoriteCrops) && !empty($favoriteCrops)) {
            $cropList = implode(', ', array_map('strval', $favoriteCrops));
            $lines[] = "- Favorite crops: {$cropList}";
        }

        $recentPredictions = $context['recent_predictions'] ?? [];
        if (is_array($recentPredictions) && !empty($recentPredictions)) {
            $lines[] = '- Recent predictions:';

            foreach ($recentPredictions as $prediction) {
                if (!is_array($prediction)) {
                    continue;
                }

                $crop = (string) ($prediction['crop'] ?? 'Unknown crop');
                $municipality = (string) ($prediction['municipality'] ?? 'Unknown municipality');
                $production = (string) ($prediction['predicted_production_mt'] ?? 'N/A');
                $date = (string) ($prediction['created_at'] ?? 'Unknown date');

                $lines[] = "  - {$crop} in {$municipality}: {$production} MT ({$date})";
            }
        }

        return implode("\n", $lines);
    }

    private function extractReplyText(array $responseBody): ?string
    {
        $parts = data_get($responseBody, 'candidates.0.content.parts', []);

        if (!is_array($parts)) {
            return null;
        }

        $chunks = [];

        foreach ($parts as $part) {
            if (is_array($part) && isset($part['text']) && is_string($part['text'])) {
                $chunks[] = $part['text'];
            }
        }

        if (empty($chunks)) {
            return null;
        }

        return trim(implode("\n", $chunks));
    }

    private function extractProviderErrorMessage(array $responseBody): string
    {
        $message = trim((string) data_get($responseBody, 'error.message', 'Unknown provider error.'));
        $status = trim((string) data_get($responseBody, 'error.status', ''));

        if ($status === '') {
            return $message;
        }

        return "{$status}: {$message}";
    }

    private function normalizeAssistantReply(string $reply, bool $wasTruncated): string
    {
        $normalized = str_replace(['**', '`'], '', $reply);
        $normalized = preg_replace('/[ \t]+/', ' ', $normalized) ?? $normalized;
        $normalized = preg_replace('/\n{3,}/', "\n\n", $normalized) ?? $normalized;
        $normalized = trim($normalized);

        if ($normalized === '') {
            return $normalized;
        }

        if ($wasTruncated) {
            $lastPunctuation = max(
                strrpos($normalized, '.'),
                strrpos($normalized, '!'),
                strrpos($normalized, '?')
            );

            if ($lastPunctuation !== false && $lastPunctuation > (int) (strlen($normalized) * 0.55)) {
                $normalized = trim(substr($normalized, 0, $lastPunctuation + 1));
            }
        }

        if (!preg_match('/[.!?]["\')\]]?$/', $normalized)) {
            $normalized .= '.';
        }

        return $normalized;
    }
}
