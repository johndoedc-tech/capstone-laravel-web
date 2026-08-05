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
            ->assertSee('Pending validation queue');

        $this->actingAs($validator)
            ->post(route('lgu.validation.approve', $damage), [
                'notes' => 'Verified with farmer.',
                'expected_revision' => 0,
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('farmer_calendar_events', [
            'id' => $damage->id,
            'lgu_validation_status' => FarmerCalendarEvent::VALIDATION_APPROVED,
            'lgu_validated_by' => $validator->id,
            'lgu_validation_notes' => 'Verified with farmer.',
        ]);

        $this->actingAs($validator)
            ->get(route('lgu.dashboard', ['status' => FarmerCalendarEvent::VALIDATION_APPROVED]))
            ->assertOk()
            ->assertDontSee('Typhoon damage - Cabbage')
            ->assertSee('No pending applications found for this queue.');
    }

    public function test_lgu_validator_can_view_finalized_records_from_their_municipality(): void
    {
        $validator = User::factory()->create([
            'role' => User::ROLE_LGU_VALIDATOR,
            'lgu_municipality' => 'BUGUIAS',
            'is_active' => true,
        ]);

        $localFarmer = User::factory()->create([
            'role' => User::ROLE_FARMER,
            'preferred_municipality' => 'BUGUIAS',
        ]);

        $outsideFarmer = User::factory()->create([
            'role' => User::ROLE_FARMER,
            'preferred_municipality' => 'MANKAYAN',
        ]);

        $recordAttributes = [
            'event_date' => now()->toDateString(),
            'event_type' => 'note',
            'category' => 'damage_report',
            'crop' => 'Cabbage',
            'desired_area_sqm' => 500,
            'damage_area_sqm' => 50,
            'submitted_to_lgu_at' => now()->subDay(),
        ];

        FarmerCalendarEvent::create(array_merge($recordAttributes, [
            'user_id' => $localFarmer->id,
            'title' => 'Approved local report',
            'lgu_validation_status' => FarmerCalendarEvent::VALIDATION_APPROVED,
            'lgu_validated_by' => $validator->id,
            'lgu_validated_at' => now(),
        ]));

        FarmerCalendarEvent::create(array_merge($recordAttributes, [
            'user_id' => $localFarmer->id,
            'title' => 'Declined local report',
            'lgu_validation_status' => FarmerCalendarEvent::VALIDATION_REJECTED,
            'lgu_validated_by' => $validator->id,
            'lgu_validated_at' => now(),
            'lgu_validation_notes' => 'Please provide a clearer photo.',
        ]));

        FarmerCalendarEvent::create(array_merge($recordAttributes, [
            'user_id' => $localFarmer->id,
            'title' => 'Pending local report',
            'lgu_validation_status' => FarmerCalendarEvent::VALIDATION_PENDING,
        ]));

        FarmerCalendarEvent::create(array_merge($recordAttributes, [
            'user_id' => $outsideFarmer->id,
            'title' => 'Outside municipality report',
            'lgu_validation_status' => FarmerCalendarEvent::VALIDATION_APPROVED,
            'lgu_validated_at' => now(),
        ]));

        $this->actingAs($validator)
            ->get(route('lgu.records'))
            ->assertOk()
            ->assertSee('Decision Records')
            ->assertSee('Approved local report')
            ->assertSee('Declined local report')
            ->assertDontSee('Pending local report')
            ->assertDontSee('Outside municipality report');

        $this->actingAs($validator)
            ->get(route('lgu.records', ['status' => FarmerCalendarEvent::VALIDATION_APPROVED]))
            ->assertOk()
            ->assertSee('Approved local report')
            ->assertDontSee('Declined local report');
    }

    public function test_farmer_calendar_hides_approved_harvest_activities_but_keeps_history_payload(): void
    {
        $farmer = User::factory()->create([
            'role' => User::ROLE_FARMER,
            'preferred_municipality' => 'BUGUIAS',
            'cooperative' => 'Test Cooperative',
        ]);

        $planDate = now()->startOfMonth()->addDays(3);
        $harvestDate = $planDate->copy()->addDays(60);

        $plan = FarmerCalendarEvent::create([
            'user_id' => $farmer->id,
            'event_date' => $planDate->toDateString(),
            'event_type' => 'note',
            'title' => 'Plan Cabbage',
            'category' => 'crop_plan',
            'crop' => 'Cabbage',
            'desired_area_sqm' => 800,
            'water_source' => 'rainfed',
            'planting_material' => 'seedling',
            'estimated_harvest_date' => $harvestDate->toDateString(),
            'lgu_validation_status' => FarmerCalendarEvent::VALIDATION_APPROVED,
            'is_completed' => true,
        ]);

        $harvest = FarmerCalendarEvent::create([
            'user_id' => $farmer->id,
            'event_date' => $harvestDate->toDateString(),
            'event_type' => 'note',
            'title' => 'Harvest Cabbage',
            'category' => 'harvest',
            'crop' => 'Cabbage',
            'desired_area_sqm' => 800,
            'water_source' => 'rainfed',
            'planting_material' => 'seedling',
            'actual_harvest_date' => $harvestDate->toDateString(),
            'actual_harvest_amount' => 250,
            'actual_harvest_unit' => 'kg',
            'actual_harvest_production_mt' => 0.25,
            'actual_harvest_recorded_at' => now(),
            'lgu_validation_status' => FarmerCalendarEvent::VALIDATION_APPROVED,
            'lgu_validated_at' => now(),
            'is_completed' => true,
        ]);

        $plan->update(['harvest_event_id' => $harvest->id]);

        $fertilizer = FarmerCalendarEvent::create([
            'user_id' => $farmer->id,
            'event_date' => $planDate->toDateString(),
            'event_type' => 'note',
            'title' => 'Basal fertilizer - Cabbage',
            'category' => 'fertilizer',
            'crop' => 'Cabbage',
            'crop_plan_event_id' => $plan->id,
            'crop_plan_stage' => 'fertilizer_basal',
        ]);

        $calendarResponse = $this->actingAs($farmer)
            ->getJson(route('farmer.calendar.events', [
                'year' => $planDate->year,
                'month' => $planDate->month,
            ]));

        $calendarResponse->assertOk();
        $eventIds = collect($calendarResponse->json('events'))->pluck('id')->all();
        $this->assertNotContains($plan->id, $eventIds);
        $this->assertNotContains($fertilizer->id, $eventIds);

        $harvestMonthResponse = $this->actingAs($farmer)
            ->getJson(route('farmer.calendar.events', [
                'year' => $harvestDate->year,
                'month' => $harvestDate->month,
            ]));

        $harvestMonthResponse->assertOk();
        $harvestEventIds = collect($harvestMonthResponse->json('events'))->pluck('id')->all();
        $this->assertNotContains($harvest->id, $harvestEventIds);

        $cropPlansResponse = $this->actingAs($farmer)
            ->getJson(route('farmer.calendar.crop-plans'));

        $cropPlansResponse->assertOk()
            ->assertJsonPath('crop_plans.0.id', $plan->id)
            ->assertJsonPath('crop_plans.0.actual_harvest_validation_status', FarmerCalendarEvent::VALIDATION_APPROVED)
            ->assertJsonPath('crop_plans.0.actual_harvest_production_mt', 0.25);
    }
}
