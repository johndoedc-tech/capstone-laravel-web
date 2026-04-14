<?php

namespace Tests\Unit;

use App\Services\GeminiChatService;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class GeminiChatServiceTest extends TestCase
{
    public function test_english_prompt_keeps_english_reply_when_provider_returns_filipino(): void
    {
        $this->configureGemini();
        $this->fakeGeminiReply('Maaari kang magtanim ng repolyo sa malamig na lugar at diligan nang tama.');

        $service = new GeminiChatService();

        $result = $service->generateReply('How do I reduce pest damage on cabbage?', [], []);

        $this->assertSame(
            'I can help with crops, weather, map insights, and predictions. Please ask a specific farming question.',
            $result['reply']
        );
    }

    public function test_new_question_does_not_return_identical_previous_answer(): void
    {
        $this->configureGemini();
        $this->fakeGeminiReply('Plant carrots at the start of the cool season and keep soil drainage consistent.');

        $service = new GeminiChatService();

        $history = [
            [
                'role' => 'user',
                'text' => 'What is the best month to plant carrots in Tuba?',
            ],
            [
                'role' => 'assistant',
                'text' => 'Plant carrots at the start of the cool season and keep soil drainage consistent.',
            ],
        ];

        $result = $service->generateReply('How much water should carrots get per week?', $history, []);

        $this->assertSame(
            'I may have repeated my last answer. Share the crop, municipality, and month so I can give a more specific recommendation.',
            $result['reply']
        );
    }

    public function test_same_question_can_keep_same_answer_without_forced_fallback(): void
    {
        $this->configureGemini();
        $this->fakeGeminiReply('Plant carrots at the start of the cool season and keep soil drainage consistent.');

        $service = new GeminiChatService();

        $history = [
            [
                'role' => 'user',
                'text' => 'What is the best month to plant carrots in Tuba?',
            ],
            [
                'role' => 'assistant',
                'text' => 'Plant carrots at the start of the cool season and keep soil drainage consistent.',
            ],
        ];

        $result = $service->generateReply('What is the best month to plant carrots in Tuba?', $history, []);

        $this->assertSame(
            'Plant carrots at the start of the cool season and keep soil drainage consistent.',
            $result['reply']
        );
    }

    private function configureGemini(): void
    {
        config()->set('services.gemini.base_url', 'https://generativelanguage.googleapis.com/v1beta');
        config()->set('services.gemini.api_key', 'test-gemini-key');
        config()->set('services.gemini.model', 'gemini-2.0-flash');
        config()->set('services.gemini.fallback_models', '');
        config()->set('services.gemini.timeout', 20);
        config()->set('services.gemini.retries', 0);
        config()->set('services.gemini.allow_http_retry', false);
        config()->set('services.gemini.max_output_tokens', 480);
        config()->set('services.gemini.model_quota_cooldown_seconds', 75);
    }

    private function fakeGeminiReply(string $replyText): void
    {
        Http::fake([
            'https://generativelanguage.googleapis.com/*' => Http::response([
                'candidates' => [
                    [
                        'content' => [
                            'parts' => [
                                ['text' => $replyText],
                            ],
                        ],
                        'finishReason' => 'STOP',
                    ],
                ],
                'usageMetadata' => [
                    'promptTokenCount' => 18,
                    'candidatesTokenCount' => 42,
                    'totalTokenCount' => 60,
                ],
            ], 200),
        ]);
    }
}
