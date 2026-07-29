<?php

namespace Tests\Feature;

use App\Models\FarmerCalendarEvent;
use App\Models\User;
use App\Services\CommunityCropSignalService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CommunityCropSignalServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        if (! in_array('sqlite', \PDO::getAvailableDrivers(), true)) {
            $this->markTestSkipped('The pdo_sqlite extension is not available in this PHP runtime.');
        }

        parent::setUp();
    }

    public function test_dashboard_pulse_keeps_all_crop_signals_for_planting_guidance(): void
    {
        $viewer = User::factory()->create([
            'role' => User::ROLE_FARMER,
            'preferred_municipality' => 'BUGUIAS',
        ]);
        $neighbor = User::factory()->create([
            'role' => User::ROLE_FARMER,
            'preferred_municipality' => 'BUGUIAS',
        ]);

        foreach (['Cabbage', 'Broccoli', 'Carrots', 'Lettuce', 'White Potato'] as $crop) {
            $this->createCropPlan($neighbor, $crop);
        }

        $pulse = app(CommunityCropSignalService::class)->dashboardPulse($viewer);

        $this->assertCount(4, $pulse['items']);
        $this->assertCount(5, $pulse['signals']);
        $this->assertNotNull($pulse['signals']->firstWhere('crop_key', 'WHITE POTATO'));
    }

    public function test_only_lgu_approved_damage_reduces_expected_nearby_supply(): void
    {
        $viewer = User::factory()->create([
            'role' => User::ROLE_FARMER,
            'preferred_municipality' => 'ATOK',
        ]);
        $neighbor = User::factory()->create([
            'role' => User::ROLE_FARMER,
            'preferred_municipality' => 'ATOK',
        ]);
        $plan = $this->createCropPlan($neighbor, 'Cabbage', 1000, 1.0);
        $damage = FarmerCalendarEvent::create([
            'user_id' => $neighbor->id,
            'event_date' => now()->toDateString(),
            'event_type' => 'note',
            'title' => 'Cabbage damage',
            'category' => 'damage_report',
            'crop' => 'Cabbage',
            'damage_area_sqm' => 500,
            'crop_plan_event_id' => $plan->id,
            'lgu_validation_status' => FarmerCalendarEvent::VALIDATION_PENDING,
        ]);

        $pendingSignal = app(CommunityCropSignalService::class)
            ->dashboardPulse($viewer)['signals']
            ->firstWhere('crop_key', 'CABBAGE');

        $this->assertSame(1.0, $pendingSignal['expected_production_mt']);

        $damage->update([
            'lgu_validation_status' => FarmerCalendarEvent::VALIDATION_APPROVED,
        ]);

        $approvedSignal = app(CommunityCropSignalService::class)
            ->dashboardPulse($viewer)['signals']
            ->firstWhere('crop_key', 'CABBAGE');

        $this->assertSame(0.5, $approvedSignal['expected_production_mt']);
    }

    private function createCropPlan(
        User $farmer,
        string $crop,
        float $areaSqm = 500,
        float $predictedProductionMt = 0.5
    ): FarmerCalendarEvent {
        return FarmerCalendarEvent::create([
            'user_id' => $farmer->id,
            'event_date' => now()->toDateString(),
            'event_type' => 'note',
            'title' => "Plan {$crop}",
            'category' => 'crop_plan',
            'crop' => $crop,
            'desired_area_sqm' => $areaSqm,
            'predicted_production_mt' => $predictedProductionMt,
            'estimated_harvest_date' => now()->addDays(45)->toDateString(),
            'is_completed' => false,
        ]);
    }
}
