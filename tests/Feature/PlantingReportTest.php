<?php

namespace Tests\Feature;

use App\Models\FarmerCalendarEvent;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PlantingReportTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        if (($_ENV['DB_CONNECTION'] ?? null) === 'sqlite' && ! extension_loaded('pdo_sqlite')) {
            $this->markTestSkipped('The pdo_sqlite extension is not available in this PHP runtime.');
        }

        parent::setUp();
    }

    public function test_planting_report_shows_lgu_approval_details_only_for_approved_harvest_data(): void
    {
        $admin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
        ]);

        $farmer = User::factory()->create([
            'role' => User::ROLE_FARMER,
            'preferred_municipality' => 'BUGUIAS',
            'cooperative' => 'Buguias Growers',
        ]);

        $approver = User::factory()->create([
            'name' => 'Maria LGU Approver',
            'lgu_municipality' => 'BUGUIAS',
            'lgu_barangay' => 'POBLACION',
            'is_active' => true,
        ]);

        $rejectedApprover = User::factory()->create([
            'name' => 'Rejected LGU Reviewer',
            'lgu_municipality' => 'BUGUIAS',
            'is_active' => true,
        ]);

        $approvedAt = now()->setTime(10, 20);
        $approvedPlan = $this->createCropPlan($farmer, 'Approved harvest plan');
        $approvedHarvest = FarmerCalendarEvent::create([
            'user_id' => $farmer->id,
            'event_date' => now()->toDateString(),
            'event_type' => 'note',
            'title' => 'Approved harvest data',
            'category' => 'harvest',
            'crop' => 'Cabbage',
            'desired_area_sqm' => 500,
            'actual_harvest_date' => now()->toDateString(),
            'actual_harvest_amount' => 250,
            'actual_harvest_unit' => 'kg',
            'actual_harvest_production_mt' => 0.25,
            'actual_harvest_recorded_at' => now(),
            'lgu_validation_status' => FarmerCalendarEvent::VALIDATION_APPROVED,
            'lgu_validated_by' => $approver->id,
            'lgu_validated_at' => $approvedAt,
        ]);
        $approvedPlan->update(['harvest_event_id' => $approvedHarvest->id]);

        $rejectedPlan = $this->createCropPlan($farmer, 'Rejected harvest plan');
        $rejectedHarvest = FarmerCalendarEvent::create([
            'user_id' => $farmer->id,
            'event_date' => now()->toDateString(),
            'event_type' => 'note',
            'title' => 'Rejected harvest data',
            'category' => 'harvest',
            'crop' => 'Carrot',
            'desired_area_sqm' => 500,
            'actual_harvest_date' => now()->toDateString(),
            'actual_harvest_amount' => 120,
            'actual_harvest_unit' => 'kg',
            'actual_harvest_production_mt' => 0.12,
            'actual_harvest_recorded_at' => now(),
            'lgu_validation_status' => FarmerCalendarEvent::VALIDATION_REJECTED,
            'lgu_validated_by' => $rejectedApprover->id,
            'lgu_validated_at' => now(),
        ]);
        $rejectedPlan->update(['harvest_event_id' => $rejectedHarvest->id]);

        $this->actingAs($admin)
            ->get(route('admin.reports.planting-report'))
            ->assertOk()
            ->assertSee('LGU verified by Maria LGU Approver')
            ->assertSee('Brgy. Poblacion, Buguias')
            ->assertSee($approvedAt->format('M d, Y h:i A'))
            ->assertDontSee('Rejected LGU Reviewer');

        $csvResponse = $this->actingAs($admin)
            ->get(route('admin.reports.planting-report', ['format' => 'csv']));

        $csvResponse->assertOk();
        $this->assertStringContainsString('Actual Harvest LGU Approval', $csvResponse->streamedContent());
        $this->assertStringContainsString('Maria LGU Approver', $csvResponse->streamedContent());
    }

    private function createCropPlan(User $farmer, string $title): FarmerCalendarEvent
    {
        return FarmerCalendarEvent::create([
            'user_id' => $farmer->id,
            'event_date' => now()->subDays(30)->toDateString(),
            'event_type' => 'note',
            'title' => $title,
            'category' => 'crop_plan',
            'crop' => 'Cabbage',
            'desired_area_sqm' => 500,
            'water_source' => 'rainfed',
            'planting_material' => 'seedling',
            'estimated_harvest_date' => now()->addDays(30)->toDateString(),
            'predicted_production_mt' => 0.30,
            'lgu_validation_status' => FarmerCalendarEvent::VALIDATION_APPROVED,
        ]);
    }
}