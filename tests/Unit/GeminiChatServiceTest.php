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

    public function test_numbered_steps_are_not_cut_to_first_item_only(): void
    {
        $this->configureGemini();
        $this->fakeGeminiReply(
            "Here are the steps for planting carrots:\n"
            . "1. Prepare loose and well-drained soil\n"
            . "2. Sow seeds around 1 cm deep\n"
            . "3. Water lightly to keep soil moist\n"
            . "4. Thin seedlings when true leaves appear\n"
            . "5. Keep weeds low and monitor pests"
        );

        $service = new GeminiChatService();

        $result = $service->generateReply('Give me full steps in planting carrot.', [], []);

        $this->assertStringContainsString('1. Prepare loose and well-drained soil.', $result['reply']);
        $this->assertStringContainsString('3. Water lightly to keep soil moist.', $result['reply']);
        $this->assertStringContainsString('5. Keep weeds low and monitor pests.', $result['reply']);
        $this->assertNotSame('Here are the steps for planting carrots: 1.', $result['reply']);
    }

    public function test_inline_numbered_steps_are_split_into_clean_lines(): void
    {
        $this->configureGemini();
        $this->fakeGeminiReply(
            'Here are the steps for planting carrots: 1. Prepare loose and well-drained soil '
            . '2. Sow seeds around 1 cm deep 3. Water lightly to keep soil moist '
            . '4. Thin seedlings when true leaves appear 5. Monitor pests and diseases'
        );

        $service = new GeminiChatService();

        $result = $service->generateReply('Give me full steps in planting carrot.', [], []);

        $this->assertStringContainsString("\n2. Sow seeds around 1 cm deep.", $result['reply']);
        $this->assertStringContainsString("\n4. Thin seedlings when true leaves appear.", $result['reply']);
        $this->assertStringContainsString("\n5. Monitor pests and diseases.", $result['reply']);
    }

    public function test_english_explanatory_reply_is_split_into_two_paragraphs(): void
    {
        $this->configureGemini();
        $this->fakeGeminiReply(
            'Carrots grow best in loose soil with good drainage and consistent moisture. '
            . 'Prepare raised beds so heavy rainfall does not cause waterlogging in Benguet farms. '
            . 'After sowing, water lightly but regularly to support even germination and early root growth. '
            . 'Thin seedlings at the right stage to improve airflow and allow roots to size up properly.'
        );

        $service = new GeminiChatService();

        $result = $service->generateReply('How should I manage carrot establishment in a rainy upland field?', [], []);

        $this->assertStringContainsString("\n\n", $result['reply']);
        $this->assertStringContainsString('Carrots grow best in loose soil with good drainage and consistent moisture.', $result['reply']);
        $this->assertStringContainsString('Thin seedlings at the right stage to improve airflow and allow roots to size up properly.', $result['reply']);
    }

    public function test_system_instruction_contains_da_car_channels(): void
    {
        $this->configureGemini();

        $service = new GeminiChatService();
        $reflection = new \ReflectionClass($service);
        $method = $reflection->getMethod('buildSystemInstruction');
        $method->setAccessible(true);

        $instruction = $method->invoke($service, [], 'english');

        $this->assertIsString($instruction);
        $this->assertStringContainsString('Use numbered steps only when the user explicitly asks for process, how-to, or step-by-step guidance; otherwise use short paragraphs.', $instruction);
        $this->assertStringContainsString('For English explanatory answers, use basic paragraphing with 2 short paragraphs and 2 to 3 sentences per paragraph.', $instruction);
        $this->assertStringContainsString('https://car.da.gov.ph/?page_id=374', $instruction);
        $this->assertStringContainsString('ored@car.da.gov.ph', $instruction);
        $this->assertStringContainsString('apcobenguet@gmail.com', $instruction);
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
