<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class FarmerPlantingGuidanceCopyTest extends TestCase
{
    public function test_dashboard_does_not_turn_high_production_into_a_crop_recommendation(): void
    {
        $view = file_get_contents(
            dirname(__DIR__, 2) . '/resources/views/dashboard-simple.blade.php'
        );

        $this->assertStringContainsString('Planting Guidance', $view);
        $this->assertStringContainsString('High nearby pressure', $view);
        $this->assertStringContainsString('Nearby plan data unavailable', $view);
        $this->assertStringContainsString(
            'Your own plans, buyer demand, and market prices are not included.',
            $view
        );
        $this->assertStringContainsString('Past average:', $view);
        $this->assertStringContainsString('Nearby expected supply', $view);
        $this->assertStringContainsString(
            "@if(!(\$cropBalancePulse['available'] ?? true))",
            $view
        );
        $this->assertStringNotContainsString("x-text=\"'Rank ' + row.rank\"", $view);
        $this->assertStringNotContainsString('looks strongest this year', $view);
        $this->assertStringNotContainsString('Strong past production', $view);
        $this->assertStringNotContainsString('Stable past production', $view);
        $this->assertStringNotContainsString('predicted_top5', $view);
        $this->assertStringNotContainsString("return 'Good option'", $view);
    }

    public function test_crop_signal_endpoint_exposes_unavailable_state(): void
    {
        $controller = file_get_contents(
            dirname(__DIR__, 2) . '/app/Http/Controllers/FarmerDashboardController.php'
        );

        $this->assertStringContainsString("'available' => \$available", $controller);
        $this->assertStringContainsString('$available ? 200 : 503', $controller);
    }
}
