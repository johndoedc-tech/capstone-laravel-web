<?php

namespace Tests\Feature;

use App\Models\FarmerCalendarEvent;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LguValidationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        if (($_ENV['DB_CONNECTION'] ?? null) === 'sqlite' && ! extension_loaded('pdo_sqlite')) {
            $this->markTestSkipped('The pdo_sqlite extension is not available in this PHP runtime.');
        }

        parent::setUp();
    }

    public function test_lgu_validator_can_view_and_approve_assigned_damage_report(): void
    {
        $farmer = User::factory()->create([
            'role' => User::ROLE_FARMER,
            'preferred_municipality' => 'BUGUIAS',
            'cooperative' => 'Test Cooperative',
        ]);

        $validator = User::factory()->create([
            'role' => User::ROLE_LGU_VALIDATOR,
            'lgu_municipality' => 'BUGUIAS',
            'is_active' => true,
        ]);

        $plan = FarmerCalendarEvent::create([
            'user_id' => $farmer->id,
            'event_date' => now()->subDays(14)->toDateString(),
            'event_type' => 'note',
            'title' => 'Plan cabbage',
            'category' => 'crop_plan',
            'crop' => 'Cabbage',
            'desired_area_sqm' => 500,
            'lgu_validation_status' => FarmerCalendarEvent::VALIDATION_APPROVED,
        ]);

        $damage = FarmerCalendarEvent::create([
            'user_id' => $farmer->id,
            'event_date' => now()->toDateString(),
            'event_type' => 'note',
            'title' => 'Typhoon damage - Cabbage',
            'description' => 'Damage report for validation.',
            'category' => 'damage_report',
            'crop' => 'Cabbage',
            'desired_area_sqm' => 500,
            'damage_area_sqm' => 50,
            'crop_plan_event_id' => $plan->id,
            'lgu_validation_status' => FarmerCalendarEvent::VALIDATION_PENDING,
            'submitted_to_lgu_at' => now(),
        ]);

        $this->actingAs($validator)
            ->get(route('lgu.dashboard'))
            ->assertOk()
            ->assertSee('Typhoon damage - Cabbage')
            ->assertSee('Pending LGU validation');

        $this->actingAs($validator)
            ->post(route('lgu.validation.approve', $damage), [
                'notes' => 'Verified with farmer.',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('farmer_calendar_events', [
            'id' => $damage->id,
            'lgu_validation_status' => FarmerCalendarEvent::VALIDATION_APPROVED,
            'lgu_validated_by' => $validator->id,
            'lgu_validation_notes' => 'Verified with farmer.',
        ]);
    }
}
