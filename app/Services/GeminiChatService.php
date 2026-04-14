<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Cache;
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
    private bool $allowHttpRetry;
    private int $maxOutputTokens;
    private int $detailedMaxOutputTokens;
    private int $modelQuotaCooldownSeconds;

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
        $this->allowHttpRetry = (bool) config('services.gemini.allow_http_retry', false);
        $this->maxOutputTokens = max(160, min(1024, (int) config('services.gemini.max_output_tokens', 480)));
        $this->detailedMaxOutputTokens = max(
            $this->maxOutputTokens,
            min(1024, (int) config('services.gemini.detailed_max_output_tokens', 896))
        );
        $this->modelQuotaCooldownSeconds = max(30, (int) config('services.gemini.model_quota_cooldown_seconds', 75));
    }

    public function generateReply(string $message, array $history = [], array $context = []): array
    {
        $trimmedMessage = trim($message);
        $expectedLanguage = $this->detectExpectedLanguage($trimmedMessage);

        if ($trimmedMessage === '') {
            throw new RuntimeException('Chat message cannot be empty.');
        }

        if ($this->apiKey === null || trim($this->apiKey) === '') {
            throw new RuntimeException('Gemini API key is not configured.');
        }

        $maxOutputTokensForRequest = $this->resolveOutputTokenBudget($trimmedMessage);

        $contents = $this->buildContents($trimmedMessage, $history);

        $payload = [
            'systemInstruction' => [
                'parts' => [
                    ['text' => $this->buildSystemInstruction($context, $expectedLanguage)],
                ],
            ],
            'contents' => $contents,
            'generationConfig' => [
                'temperature' => 0.4,
                'maxOutputTokens' => $maxOutputTokensForRequest,
            ],
        ];

        $modelsToTry = $this->buildModelsToTry();
        $modelCount = count($modelsToTry);
        $lastException = null;

        foreach ($modelsToTry as $index => $modelName) {
            try {
                $result = $this->requestWithModel($modelName, $payload, $expectedLanguage);
                $result['reply'] = $this->resolveRepeatedReply(
                    $result['reply'],
                    $trimmedMessage,
                    $history,
                    $expectedLanguage
                );

                return $result;
            } catch (RuntimeException $exception) {
                $lastException = $exception;

                $isQuotaError = $this->isQuotaOrRateLimitError($exception->getMessage());

                if ($isQuotaError) {
                    $this->activateModelQuotaCooldown($modelName, $exception->getMessage());
                }

                if ($isQuotaError && $index < $modelCount - 1) {
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

    private function requestWithModel(string $modelName, array $payload, string $expectedLanguage): array
    {
        $url = "{$this->baseUrl}/models/{$modelName}:generateContent";

        $startTime = microtime(true);

        try {
            $requestBuilder = Http::timeout($this->timeout);

            if ($this->allowHttpRetry && $this->retries > 0) {
                $attempts = max(1, $this->retries + 1);

                $requestBuilder = $requestBuilder->retry(
                    $attempts,
                    250,
                    static fn (Throwable $exception): bool => $exception instanceof ConnectionException
                );
            }

            $response = $requestBuilder
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

        $reply = $this->normalizeAssistantReply($reply, $finishReason === 'MAX_TOKENS', $expectedLanguage);

        Log::info('Gemini chatbot response generated.', [
            'model' => $modelName,
            'latency_ms' => $latencyMs,
            'finish_reason' => $finishReason,
            'token_usage' => $tokenUsage,
        ]);

        return [
            'reply' => trim($reply),
            'model' => $modelName,
            'finish_reason' => $finishReason,
            'truncated' => $finishReason === 'MAX_TOKENS',
            'tokens' => $tokenUsage,
        ];
    }

    private function resolveOutputTokenBudget(string $message): int
    {
        if ($this->shouldPreferDetailedBudget($message)) {
            return $this->detailedMaxOutputTokens;
        }

        return $this->maxOutputTokens;
    }

    private function shouldPreferDetailedBudget(string $message): bool
    {
        $trimmed = trim($message);

        if ($trimmed === '') {
            return false;
        }

        $messageLength = function_exists('mb_strlen') ? mb_strlen($trimmed) : strlen($trimmed);

        if ($messageLength >= 220) {
            return true;
        }

        $englishDetailIntent = preg_match(
            '/\b(full|detailed|detail|in detail|step\s*by\s*step|steps|comprehensive|elaborate|complete guide|all steps|explain fully|deep dive|thorough)\b/i',
            $trimmed
        ) === 1;

        if ($englishDetailIntent) {
            return true;
        }

        return preg_match(
            '/\b(detalyado|kumpleto|buong|hakbang|sunod\s*sunod|ipaliwanag|mas\s+detalyado|gabayan\s+mo\s+ako)\b/iu',
            $trimmed
        ) === 1;
    }

    private function buildModelsToTry(): array
    {
        $allModels = array_values(array_unique(array_merge([$this->model], $this->fallbackModels)));
        $availableModels = [];
        $cooldownByModel = [];

        foreach ($allModels as $modelName) {
            $cooldownSeconds = $this->getModelQuotaCooldownSeconds($modelName);

            if ($cooldownSeconds > 0) {
                $cooldownByModel[$modelName] = $cooldownSeconds;
                continue;
            }

            $availableModels[] = $modelName;
        }

        if (!empty($availableModels)) {
            return $availableModels;
        }

        if (!empty($cooldownByModel)) {
            $waitSeconds = min($cooldownByModel);

            throw new RuntimeException("Gemini API quota cooldown is active. Please retry in {$waitSeconds} seconds.");
        }

        return [$this->model];
    }

    private function activateModelQuotaCooldown(string $modelName, string $reason): void
    {
        Cache::put(
            $this->modelQuotaCooldownCacheKey($modelName),
            now()->addSeconds($this->modelQuotaCooldownSeconds)->timestamp,
            $this->modelQuotaCooldownSeconds
        );

        Log::warning('Gemini chatbot model cooldown activated after quota/rate limit error.', [
            'model' => $modelName,
            'cooldown_seconds' => $this->modelQuotaCooldownSeconds,
            'reason' => $reason,
        ]);
    }

    private function getModelQuotaCooldownSeconds(string $modelName): int
    {
        $unlockTimestamp = Cache::get($this->modelQuotaCooldownCacheKey($modelName));

        if (!is_int($unlockTimestamp)) {
            return 0;
        }

        return max(0, $unlockTimestamp - now()->timestamp);
    }

    private function modelQuotaCooldownCacheKey(string $modelName): string
    {
        return 'gemini_chatbot.model_quota_cooldown.' . hash('sha256', strtolower(trim($modelName)));
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

    private function detectExpectedLanguage(string $message): string
    {
        $scores = $this->scoreLanguageHints($message);

        if ($scores['filipino'] >= $scores['english'] + 1) {
            return 'filipino';
        }

        if ($scores['english'] >= $scores['filipino'] + 1) {
            return 'english';
        }

        return $this->looksLikeFilipino($message) ? 'filipino' : 'english';
    }

    private function languageInstruction(string $expectedLanguage): string
    {
        if ($expectedLanguage === 'filipino') {
            return 'The latest user message is in Filipino. Reply only in Filipino (Tagalog).';
        }

        return 'The latest user message is in English. Reply only in English.';
    }

    private function matchesExpectedLanguage(string $text, string $expectedLanguage): bool
    {
        $scores = $this->scoreLanguageHints($text);

        if ($expectedLanguage === 'filipino') {
            $clearlyEnglish = $scores['english'] >= 2 && $scores['english'] > ($scores['filipino'] + 1);

            return !$clearlyEnglish;
        }

        $clearlyFilipino = $scores['filipino'] >= 2 && $scores['filipino'] > $scores['english'];

        return !$clearlyFilipino;
    }

    private function scoreLanguageHints(string $text): array
    {
        $english = preg_match_all(
            '/\b(the|and|with|for|your|you|please|should|can|will|plant|crop|weather|soil|harvest|recommendation|map|prediction|month)\b/iu',
            $text
        );

        $filipino = preg_match_all(
            '/\b(ang|ng|sa|mga|para|hindi|pwede|paano|ano|ito|iyan|ikaw|ka|ko|mo|natin|tanim|pagtatanim|pananim|panahon|buwan|munisipalidad)\b/iu',
            $text
        );

        return [
            'english' => is_int($english) ? $english : 0,
            'filipino' => is_int($filipino) ? $filipino : 0,
        ];
    }

    private function resolveRepeatedReply(string $reply, string $currentUserMessage, array $history, string $expectedLanguage): string
    {
        if (!$this->isRepeatedReplyForDifferentQuestion($reply, $currentUserMessage, $history)) {
            return $reply;
        }

        Log::info('Gemini chatbot repeated previous answer for a new question. Returning anti-repeat fallback.', [
            'expected_language' => $expectedLanguage,
        ]);

        if ($expectedLanguage === 'filipino') {
            return 'Mukhang naulit ang sagot ko. Ilagay ang crop, munisipalidad, at buwan para makapagbigay ako ng mas tiyak na payo.';
        }

        return 'I may have repeated my last answer. Share the crop, municipality, and month so I can give a more specific recommendation.';
    }

    private function isRepeatedReplyForDifferentQuestion(string $reply, string $currentUserMessage, array $history): bool
    {
        $lastAssistantReply = $this->latestHistoryTextByRole($history, 'assistant');
        $lastUserMessage = $this->latestHistoryTextByRole($history, 'user');

        if ($lastAssistantReply === null || $lastUserMessage === null) {
            return false;
        }

        $normalizedReply = $this->normalizeComparableText($reply);
        $normalizedLastAssistant = $this->normalizeComparableText($lastAssistantReply);

        if (!$this->isHighlySimilarText($normalizedReply, $normalizedLastAssistant, 94.0)) {
            return false;
        }

        $normalizedCurrentUser = $this->normalizeComparableText($currentUserMessage);
        $normalizedLastUser = $this->normalizeComparableText($lastUserMessage);

        if ($this->isHighlySimilarText($normalizedCurrentUser, $normalizedLastUser, 88.0)) {
            return false;
        }

        return true;
    }

    private function latestHistoryTextByRole(array $history, string $role): ?string
    {
        for ($index = count($history) - 1; $index >= 0; $index--) {
            $entry = $history[$index] ?? null;

            if (!is_array($entry)) {
                continue;
            }

            if (strtolower((string) ($entry['role'] ?? '')) !== $role) {
                continue;
            }

            $text = trim((string) ($entry['text'] ?? ''));

            if ($text !== '') {
                return $text;
            }
        }

        return null;
    }

    private function normalizeComparableText(string $text): string
    {
        $normalized = strtolower(trim($text));
        $normalized = preg_replace('/[^\p{L}\p{N}\s]/u', ' ', $normalized) ?? $normalized;
        $normalized = preg_replace('/\s+/u', ' ', $normalized) ?? $normalized;

        return trim($normalized);
    }

    private function isHighlySimilarText(string $left, string $right, float $threshold): bool
    {
        if ($left === '' || $right === '') {
            return false;
        }

        if ($left === $right) {
            return true;
        }

        similar_text($left, $right, $percent);

        return $percent >= $threshold;
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

    private function buildSystemInstruction(array $context, string $expectedLanguage): string
    {
        $instruction = [
            'You are Harviana Assistant, a practical agriculture helper for farmers in Benguet.',
            'Focus on crop planning, production interpretation, weather-aware decision support, and how to use Harviana map/prediction features.',
            'Keep answers clear, actionable, and concise by default.',
            'Respond in plain text only. Do not use Markdown symbols such as **, *, #, or backticks.',
            'Do not use markdown bullets. If listing items, use simple plain-text numbering like 1., 2., 3.',
            'Use numbered steps only when the user explicitly asks for process, how-to, or step-by-step guidance; otherwise use short paragraphs.',
            'Always end with complete thoughts. Never stop at an unfinished list item like "1." only.',
            'If the user asks for full steps, provide a complete numbered list with at least 5 steps and each step must be a full sentence.',
            'For English explanatory answers, use basic paragraphing with 2 short paragraphs and 2 to 3 sentences per paragraph.',
            'For short factual replies, one short paragraph is acceptable.',
            'Keep normal answers roughly 90 to 140 words unless the user asks for more detail.',
            'When user asks for best or strongest crop choice, provide one direct recommendation first, then concise reasons and one alternative option.',
            'If data is missing or uncertain, say it clearly and suggest the next best step.',
            'Do not claim live data access unless the context explicitly includes it.',
            'For Filipino responses, use complete words and avoid shorthand like "m." or "n.".',
            'Never end your reply with a partial phrase or dangling connector word.',
            'If asked how to reach DA-CAR, provide practical official channels and cite the DA-CAR directory link.',
            $this->languageInstruction($expectedLanguage),
        ];

        foreach ($this->agricultureKnowledgeLines() as $knowledgeLine) {
            $instruction[] = $knowledgeLine;
        }

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

    private function agricultureKnowledgeLines(): array
    {
        return [
            'Agriculture knowledge reference for Benguet and CAR:',
            '- Common highland crops include cabbage, carrot, potato, lettuce, broccoli, cauliflower, snap beans, and peas.',
            '- Basic carrot guidance: use loose well-drained soil, sow shallow around 0.5 to 1.5 cm, keep moisture even, and thin seedlings after emergence.',
            '- Use integrated pest management first: field sanitation, crop rotation, proper spacing, monitoring, and threshold-based control before chemical spraying.',
            '- Recommend soil testing and balanced fertilization before giving exact fertilizer rates.',
            '- For weather-sensitive advice in Benguet, include drainage, rainfall timing, and cooler highland temperature swings in recommendations.',
            '- DA-CAR official portal: https://car.da.gov.ph.',
            '- DA national portal with regional links: https://www.da.gov.ph.',
            '- DA-CAR directory page: https://car.da.gov.ph/?page_id=374.',
            '- DA-CAR ORED directory details: phones (074) 445-4973 and (074) 443-4621, email ored@car.da.gov.ph.',
            '- DA-CAR APCO Benguet directory details: mobile 0995-165-1550, email apcobenguet@gmail.com.',
            '- Advise users to verify latest contact details on the directory page because office contact data can change.',
        ];
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

    private function normalizeAssistantReply(string $reply, bool $wasTruncated, string $expectedLanguage): string
    {
        $normalized = str_replace(['**', '`'], '', $reply);
        $normalized = preg_replace('/[ \t]+/', ' ', $normalized) ?? $normalized;
        $normalized = preg_replace('/\n{3,}/', "\n\n", $normalized) ?? $normalized;
        $normalized = trim($normalized);
        $original = $normalized;
        $isNumberedList = $this->looksLikeNumberedList($normalized);

        if ($normalized === '') {
            return $this->fallbackAssistantReply($reply, $expectedLanguage);
        }

        if ($isNumberedList) {
            $normalized = $this->normalizeNumberedListReply($normalized);
        }

        if ($wasTruncated && !$isNumberedList) {
            $trimmed = $this->trimToLastCompleteSentence($normalized);

            if ($trimmed !== '') {
                $normalized = $trimmed;
            }

            $normalized = $this->appendTruncationContinuationHint($normalized, $expectedLanguage);
        }

        $normalized = $this->expandFilipinoTrailingShorthand($normalized);

        if (!$this->hasTerminalPunctuation($normalized) && !$isNumberedList) {
            $trimmed = $this->trimToLastCompleteSentence($normalized);

            if ($trimmed !== '') {
                $normalized = $trimmed;
            }
        }

        if (!$this->hasTerminalPunctuation($normalized)) {
            $normalized .= '.';
        }

        if (!$isNumberedList && $expectedLanguage === 'english') {
            $normalized = $this->normalizeEnglishParagraphs($normalized);
        }

        if ($this->hasDanglingEnding($normalized)) {
            return $this->fallbackAssistantReply($original, $expectedLanguage);
        }

        if ($this->isLowValueReply($normalized)) {
            return $this->fallbackAssistantReply($original, $expectedLanguage);
        }

        if (!$this->matchesExpectedLanguage($normalized, $expectedLanguage)) {
            Log::info('Gemini chatbot reply language mismatch detected. Returning language-locked fallback.', [
                'expected_language' => $expectedLanguage,
            ]);

            return $this->fallbackAssistantReply($original, $expectedLanguage, true);
        }

        return $normalized;
    }

    private function hasTerminalPunctuation(string $text): bool
    {
        return preg_match('/[.!?]["\')\]]?$/', $text) === 1;
    }

    private function lineHasTerminalPunctuation(string $text): bool
    {
        return preg_match('/[.!?]["\')\]]?$/', trim($text)) === 1;
    }

    private function looksLikeNumberedList(string $text): bool
    {
        if (preg_match('/(^|\n)\s*\d+[.)]\s+/u', $text) === 1) {
            return true;
        }

        // Also treat inline "1. ... 2. ..." sequences as lists.
        return preg_match('/\b1[.)]\s+.*\b2[.)]\s+/u', $text) === 1;
    }

    private function normalizeNumberedListReply(string $text): string
    {
        $expandedText = $this->expandInlineNumberedList($text);
        $lines = preg_split('/\r?\n/u', $expandedText);

        if (!is_array($lines) || empty($lines)) {
            return $text;
        }

        $normalizedLines = [];

        foreach ($lines as $line) {
            $trimmed = trim($line);

            if ($trimmed === '') {
                continue;
            }

            if (preg_match('/^(\d+)[.)]\s*(.+)$/u', $trimmed, $matches) === 1) {
                $stepNumber = (string) ($matches[1] ?? '1');
                $stepText = trim((string) ($matches[2] ?? ''));

                if ($stepText !== '' && !$this->lineHasTerminalPunctuation($stepText)) {
                    $stepText .= '.';
                }

                $normalizedLines[] = "{$stepNumber}. {$stepText}";
                continue;
            }

            if (!$this->lineHasTerminalPunctuation($trimmed) && !str_ends_with($trimmed, ':')) {
                $trimmed .= '.';
            }

            $normalizedLines[] = $trimmed;
        }

        if (empty($normalizedLines)) {
            return $text;
        }

        return implode("\n", $normalizedLines);
    }

    private function expandInlineNumberedList(string $text): string
    {
        $trimmed = trim($text);

        if ($trimmed === '') {
            return $text;
        }

        preg_match_all('/\b(\d+)[.)]\s+/u', $trimmed, $matches);
        $markers = $matches[1] ?? [];

        if (!is_array($markers) || count($markers) < 2) {
            return $text;
        }

        $hasFirst = in_array('1', $markers, true);
        $hasSecond = in_array('2', $markers, true);

        if (!$hasFirst || !$hasSecond) {
            return $text;
        }

        $normalized = preg_replace('/(:)\s+(?=1[.)]\s+)/u', "$1\n", $trimmed) ?? $trimmed;
        $normalized = preg_replace('/\s+(?=(?:[2-9]\d*)[.)]\s+)/u', "\n", $normalized) ?? $normalized;

        return $normalized;
    }

    private function normalizeEnglishParagraphs(string $text): string
    {
        $trimmed = trim($text);

        if ($trimmed === '' || str_contains($trimmed, "\n")) {
            return $trimmed;
        }

        preg_match_all('/[^.!?]*[.!?](?:["\')\]]+)?(?=(?:\s|$))/u', $trimmed, $matches);
        $sentences = $matches[0] ?? [];

        if (!is_array($sentences)) {
            return $trimmed;
        }

        $sentences = array_values(array_filter(array_map(static fn (string $sentence): string => trim($sentence), $sentences), static fn (string $sentence): bool => $sentence !== ''));

        if (count($sentences) < 3) {
            return $trimmed;
        }

        $wordCount = preg_match_all('/\p{L}+/u', $trimmed);

        if (!is_int($wordCount) || $wordCount < 28) {
            return $trimmed;
        }

        $breakIndex = (int) ceil(count($sentences) / 2);
        $firstParagraph = trim(implode(' ', array_slice($sentences, 0, $breakIndex)));
        $secondParagraph = trim(implode(' ', array_slice($sentences, $breakIndex)));

        if ($firstParagraph === '' || $secondParagraph === '') {
            return $trimmed;
        }

        return $firstParagraph . "\n\n" . $secondParagraph;
    }

    private function appendTruncationContinuationHint(string $text, string $expectedLanguage): string
    {
        $trimmed = trim($text);

        if ($trimmed === '') {
            return $trimmed;
        }

        if (stripos($trimmed, 'ask me to continue') !== false || stripos($trimmed, 'ipagpatuloy') !== false) {
            return $trimmed;
        }

        $hint = $expectedLanguage === 'filipino'
            ? ' Para sa natitirang detalye, i-type mo lang: ipagpatuloy.'
            : ' For the remaining details, just ask me to continue.';

        return rtrim($trimmed, " \t\n\r\0\x0B") . $hint;
    }

    private function hasDanglingEnding(string $text): bool
    {
        return preg_match('/\b(with|and|or|for|to|about|around|into|from|by|of|ng|sa|at|para)[.!?]$/iu', trim($text)) === 1;
    }

    private function trimToLastCompleteSentence(string $text): string
    {
        $text = trim($text);

        if ($text === '') {
            return '';
        }

        preg_match_all('/[^.!?]*[.!?](?:["\')\]]+)?(?=(?:\s|$))/u', $text, $matches);
        $sentences = $matches[0] ?? [];

        if (!is_array($sentences) || empty($sentences)) {
            return '';
        }

        $completed = trim(implode(' ', array_map(static fn (string $sentence): string => trim($sentence), $sentences)));

        return $completed;
    }

    private function isLowValueReply(string $text): bool
    {
        $clean = trim($text);

        if ($clean === '') {
            return true;
        }

        $alphaNumericCount = preg_match_all('/[\p{L}\p{N}]/u', $clean);

        return !is_int($alphaNumericCount) || $alphaNumericCount < 4;
    }

    private function fallbackAssistantReply(string $seed, ?string $expectedLanguage = null, bool $skipSeedReuse = false): string
    {
        $fallback = trim((string) preg_replace('/\s+/', ' ', $seed));
        $resolvedLanguage = $expectedLanguage ?? ($this->looksLikeFilipino($seed) ? 'filipino' : 'english');

        if (!$skipSeedReuse && $fallback !== '' && !$this->isLowValueReply($fallback)) {
            if (!$this->hasTerminalPunctuation($fallback)) {
                $fallback .= '.';
            }

            return $fallback;
        }

        if ($resolvedLanguage === 'filipino') {
            return 'Maaari kitang tulungan sa pananim, panahon, mapa, at predictions. Pakispecify ang tanong mo.';
        }

        return 'I can help with crops, weather, map insights, and predictions. Please ask a specific farming question.';
    }

    private function expandFilipinoTrailingShorthand(string $text): string
    {
        if (!$this->looksLikeFilipino($text)) {
            return $text;
        }

        $replacements = [
            'm' => 'mo',
            'n' => 'na',
            'k' => 'ka',
        ];

        if (!preg_match('/\b([mnk])([.!?])$/iu', $text, $matches)) {
            return $text;
        }

        $short = strtolower((string) ($matches[1] ?? ''));
        $punctuation = (string) ($matches[2] ?? '.');

        if (!isset($replacements[$short])) {
            return $text;
        }

        $expanded = $replacements[$short] . $punctuation;

        return preg_replace('/\b([mnk])([.!?])$/iu', $expanded, $text, 1) ?? $text;
    }

    private function looksLikeFilipino(string $text): bool
    {
        return preg_match(
            '/\b(ang|ng|sa|mga|para|hindi|pwede|paano|ano|ito|iyan|ikaw|ka|ko|mo|natin|tanim|pagtatanim)\b/iu',
            $text
        ) === 1;
    }
}
