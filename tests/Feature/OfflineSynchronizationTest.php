<?php

namespace Tests\Feature;

use App\Models\CalendarEventAudit;
use App\Models\FarmerCalendarEvent;
use App\Models\OfflineOperationReceipt;
use App\Models\User;
use App\Services\IdempotentOperationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;
use Tests\TestCase;

class OfflineSynchronizationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        if (($_ENV['DB_CONNECTION'] ?? null) === 'sqlite' && ! extension_loaded('pdo_sqlite')) {
            $this->markTestSkipped('The pdo_sqlite extension is not available in this PHP runtime.');
        }

        parent::setUp();
        Storage::fake('public');
        Http::fake([
            '*' => Http::response([
                'prediction' => [
                    'production_mt' => 0.25,
                    'production_per_ha_mt' => 3.12,
                    'confidence_score' => 0.8,
                ],
            ]),
        ]);
    }

    public function test_crop_plan_replay_creates_one_plan(): void
    {
        $farmer = $this->farmer();
        $key = (string) Str::uuid();
        $payload = $this->cropPlanPayload();

        $first = $this->actingAs($farmer)->withHeader('Idempotency-Key', $key)
            ->postJson(route('farmer.calendar.store'), $payload)
            ->assertOk();
        $second = $this->actingAs($farmer)->withHeader('Idempotency-Key', $key)
            ->postJson(route('farmer.calendar.store'), $payload)
            ->assertOk()
            ->assertJsonPath('idempotent_replay', true);

        $this->assertSame($first->json('event.id'), $second->json('event.id'));
        $this->assertSame(1, FarmerCalendarEvent::where('user_id', $farmer->id)->where('category', 'crop_plan')->count());
        $this->assertSame(1, OfflineOperationReceipt::where('idempotency_key', $key)->count());
    }

    public function test_damage_report_replay_creates_one_record_and_one_attachment(): void
    {
        $farmer = $this->farmer();
        $plan = $this->plan($farmer);
        $key = (string) Str::uuid();
        $payload = [
            'event_date' => now()->toDateString(),
            'event_type' => 'note',
            'title' => 'Storm damage - Cabbage',
            'description' => 'Test damage report.',
            'category' => 'damage_report',
            'damage_area_sqm' => 25,
            'crop_plan_event_id' => $plan->id,
        ];

        $this->actingAs($farmer)->withHeaders(['Idempotency-Key' => $key, 'Accept' => 'application/json'])
            ->post(route('farmer.calendar.store'), array_merge($payload, [
                'damage_photo' => UploadedFile::fake()->image('damage.jpg', 320, 240),
            ]))
            ->assertOk();

        $this->actingAs($farmer)->withHeaders(['Idempotency-Key' => $key, 'Accept' => 'application/json'])
            ->post(route('farmer.calendar.store'), array_merge($payload, [
                'damage_photo' => UploadedFile::fake()->image('damage-replay.jpg', 320, 240),
            ]))
            ->assertOk()
            ->assertJsonPath('idempotent_replay', true);

        $this->assertSame(1, FarmerCalendarEvent::where('user_id', $farmer->id)->where('category', 'damage_report')->count());
        $this->assertCount(1, Storage::disk('public')->allFiles('damage-reports'));
    }

    public function test_actual_harvest_replay_does_not_duplicate_submission_or_audit(): void
    {
        $farmer = $this->farmer();
        $plan = $this->plan($farmer);
        $key = (string) Str::uuid();
        $payload = [
            'actual_harvest_date' => now()->toDateString(),
            'actual_harvest_amount' => 125,
            'actual_harvest_unit' => 'kg',
            'actual_harvest_notes' => 'Offline field test.',
        ];

        $this->actingAs($farmer)->withHeader('Idempotency-Key', $key)
            ->postJson(route('farmer.calendar.harvest', $plan), $payload)
            ->assertOk();
        $this->actingAs($farmer)->withHeader('Idempotency-Key', $key)
            ->postJson(route('farmer.calendar.harvest', $plan), $payload)
            ->assertOk()
            ->assertJsonPath('idempotent_replay', true);

        $plan->refresh();
        $this->assertSame('0.1250', $plan->actual_harvest_production_mt);
        $this->assertSame(1, CalendarEventAudit::where('farmer_calendar_event_id', $plan->id)
            ->where('action', 'actual_harvest_submitted')->count());
    }

    public function test_lgu_replay_does_not_increment_revision_twice(): void
    {
        [$validator, $report] = $this->pendingLguReport();
        $key = (string) Str::uuid();
        $payload = ['notes' => 'Verified.', 'expected_revision' => 0];

        $this->actingAs($validator)->withHeader('Idempotency-Key', $key)
            ->postJson(route('lgu.validation.approve', $report), $payload)
            ->assertOk()
            ->assertJsonPath('event.lgu_validation_revision', 1);
        $this->actingAs($validator)->withHeader('Idempotency-Key', $key)
            ->postJson(route('lgu.validation.approve', $report), $payload)
            ->assertOk()
            ->assertJsonPath('idempotent_replay', true);

        $this->assertSame(1, $report->fresh()->lgu_validation_revision);
    }

    public function test_outdated_lgu_revision_returns_conflict_without_overwrite(): void
    {
        [$validator, $report] = $this->pendingLguReport();
        $report->update(['lgu_validation_revision' => 1]);

        $this->actingAs($validator)->withHeader('Idempotency-Key', (string) Str::uuid())
            ->postJson(route('lgu.validation.reject', $report), [
                'notes' => 'Needs a clearer photo.',
                'expected_revision' => 0,
            ])
            ->assertStatus(409)
            ->assertJsonPath('code', 'lgu_validation_conflict')
            ->assertJsonPath('current_server.revision', 1);

        $this->assertSame(FarmerCalendarEvent::VALIDATION_PENDING, $report->fresh()->lgu_validation_status);
    }

    public function test_another_user_cannot_reuse_an_operation_key(): void
    {
        $firstFarmer = $this->farmer();
        $secondFarmer = $this->farmer(['email' => 'second@example.test']);
        $key = (string) Str::uuid();
        $payload = [
            'event_date' => now()->toDateString(),
            'event_type' => 'note',
            'title' => 'Offline note',
            'category' => 'other',
        ];

        $this->actingAs($firstFarmer)->withHeader('Idempotency-Key', $key)
            ->postJson(route('farmer.calendar.store'), $payload)
            ->assertOk();
        $this->actingAs($secondFarmer)->withHeader('Idempotency-Key', $key)
            ->postJson(route('farmer.calendar.store'), array_merge($payload, ['title' => 'Cross-account replay']))
            ->assertStatus(409)
            ->assertJsonPath('code', 'idempotency_key_conflict');

        $this->assertDatabaseMissing('farmer_calendar_events', ['user_id' => $secondFarmer->id, 'title' => 'Cross-account replay']);
    }

    public function test_expired_session_and_validation_errors_return_json_failures(): void
    {
        $this->withHeader('Idempotency-Key', (string) Str::uuid())
            ->postJson(route('farmer.calendar.store'), $this->cropPlanPayload())
            ->assertUnauthorized();

        $farmer = $this->farmer();
        $key = (string) Str::uuid();
        $this->actingAs($farmer)->withHeader('Idempotency-Key', $key)
            ->postJson(route('farmer.calendar.store'), ['category' => 'crop_plan'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['event_date', 'event_type', 'title', 'crop']);
        $this->assertDatabaseMissing('offline_operation_receipts', ['idempotency_key' => $key]);
    }

    public function test_idempotent_transaction_rolls_back_record_and_receipt_after_failure(): void
    {
        $farmer = $this->farmer();
        $key = (string) Str::uuid();
        $request = Request::create('/offline-test', 'POST', [], [], [], [
            'HTTP_IDEMPOTENCY_KEY' => $key,
            'HTTP_ACCEPT' => 'application/json',
        ]);
        $request->setUserResolver(fn () => $farmer);

        try {
            app(IdempotentOperationService::class)->execute($request, 'farmer.calendar_event.create', function () use ($farmer) {
                FarmerCalendarEvent::create([
                    'user_id' => $farmer->id,
                    'event_date' => now()->toDateString(),
                    'event_type' => 'note',
                    'title' => 'Must roll back',
                    'category' => 'other',
                ]);
                throw new RuntimeException('Simulated failure.');
            });
            $this->fail('The simulated failure should be thrown.');
        } catch (RuntimeException $exception) {
            $this->assertSame('Simulated failure.', $exception->getMessage());
        }

        $this->assertDatabaseMissing('farmer_calendar_events', ['title' => 'Must roll back']);
        $this->assertDatabaseMissing('offline_operation_receipts', ['idempotency_key' => $key]);
    }

    private function farmer(array $attributes = []): User
    {
        return User::factory()->create(array_merge([
            'role' => User::ROLE_FARMER,
            'preferred_municipality' => 'BUGUIAS',
            'cooperative' => 'Test Cooperative',
        ], $attributes));
    }

    private function cropPlanPayload(): array
    {
        return [
            'event_date' => now()->toDateString(),
            'event_type' => 'note',
            'title' => 'Plan Cabbage',
            'description' => 'Offline crop plan test.',
            'category' => 'crop_plan',
            'crop' => 'Cabbage',
            'desired_area_sqm' => 800,
            'water_source' => 'rainfed',
            'planting_material' => 'seedling',
        ];
    }

    private function plan(User $farmer): FarmerCalendarEvent
    {
        return FarmerCalendarEvent::create([
            'user_id' => $farmer->id,
            'event_date' => now()->subMonth()->toDateString(),
            'event_type' => 'note',
            'title' => 'Plan Cabbage',
            'category' => 'crop_plan',
            'crop' => 'Cabbage',
            'desired_area_sqm' => 500,
            'water_source' => 'rainfed',
            'planting_material' => 'seedling',
            'lgu_validation_status' => FarmerCalendarEvent::VALIDATION_APPROVED,
        ]);
    }

    private function pendingLguReport(): array
    {
        $farmer = $this->farmer();
        $validator = User::factory()->create([
            'role' => User::ROLE_LGU_VALIDATOR,
            'lgu_municipality' => 'BUGUIAS',
            'is_active' => true,
        ]);
        $report = FarmerCalendarEvent::create([
            'user_id' => $farmer->id,
            'event_date' => now()->toDateString(),
            'event_type' => 'note',
            'title' => 'Damage report',
            'category' => 'damage_report',
            'crop' => 'Cabbage',
            'desired_area_sqm' => 500,
            'damage_area_sqm' => 25,
            'lgu_validation_status' => FarmerCalendarEvent::VALIDATION_PENDING,
            'lgu_validation_revision' => 0,
            'submitted_to_lgu_at' => now(),
        ]);

        return [$validator, $report];
    }
}
