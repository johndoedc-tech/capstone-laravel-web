<?php

namespace App\Services;

use App\Models\FarmerCalendarEvent;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class CommunityCropSignalService
{
    public function dashboardPulse(User $user): array
    {
        $municipality = $user->preferred_municipality;

        if (! $municipality) {
            return [
                'has_location' => false,
                'has_data' => false,
                'municipality' => null,
                'window_label' => 'Next 180 days',
                'items' => collect(),
                'signals' => collect(),
                'alternatives' => collect(),
                'message' => 'Set your farm location to see crops near you.',
            ];
        }

        $signals = $this->getSignals([
            'municipality' => $municipality,
            'exclude_user_id' => $user->id,
            'from_date' => Carbon::today()->toDateString(),
            'to_date' => Carbon::today()->addDays(180)->toDateString(),
        ]);

        return [
            'has_location' => true,
            'has_data' => $signals->isNotEmpty(),
            'municipality' => $this->formatLabel($municipality),
            'window_label' => 'Next 180 days',
            'items' => $signals->take(4)->values(),
            'signals' => $signals->values(),
            'alternatives' => $signals
                ->filter(fn (array $signal) => in_array($signal['pressure_key'], ['low', 'balanced'], true))
                ->sortBy([
                    ['pressure_score', 'asc'],
                    ['expected_production_mt', 'desc'],
                ])
                ->take(3)
                ->values(),
            'message' => $signals->isNotEmpty()
                ? 'You are seeing grouped plans near your town. Names stay private.'
                : 'No nearby crop plans are recorded in Harviana yet. This does not confirm low supply.',
        ];
    }

    public function cropPlanAdvice(User $user, string $crop, string $estimatedHarvestDate): array
    {
        $municipality = $user->preferred_municipality;

        if (! $municipality) {
            return [
                'has_location' => false,
                'has_data' => false,
                'selected' => null,
                'alternatives' => collect(),
                'message' => 'Set your town first to see nearby plans.',
            ];
        }

        $harvestDate = Carbon::parse($estimatedHarvestDate);
        $signals = $this->getSignals([
            'municipality' => $municipality,
            'exclude_user_id' => $user->id,
            'from_date' => $harvestDate->copy()->startOfMonth()->toDateString(),
            'to_date' => $harvestDate->copy()->endOfMonth()->toDateString(),
        ]);
        $selectedCropKey = $this->normalizeCropKey($crop);
        $selected = $signals->firstWhere('crop_key', $selectedCropKey);
        $selected ??= $this->emptyCropSignal($crop);
        $alternatives = $signals
            ->reject(fn (array $signal) => $signal['crop_key'] === $selectedCropKey)
            ->filter(fn (array $signal) => in_array($signal['pressure_key'], ['low', 'balanced'], true))
            ->sortBy([
                ['pressure_score', 'asc'],
                ['expected_production_mt', 'desc'],
            ])
            ->take(3)
            ->values();

        return [
            'has_location' => true,
            'has_data' => $signals->isNotEmpty(),
            'municipality' => $this->formatLabel($municipality),
            'harvest_month' => $harvestDate->format('F Y'),
            'selected' => $selected,
            'alternatives' => $alternatives,
            'message' => $this->buildAdviceMessage($selected, $alternatives, $harvestDate),
            'privacy_note' => 'You only see grouped plans. Names stay private.',
        ];
    }

    private function getSignals(array $filters): Collection
    {
        $damageTotals = FarmerCalendarEvent::query()
            ->select('crop_plan_event_id', DB::raw('SUM(COALESCE(damage_area_sqm, 0)) as reported_damage_sqm'))
            ->where('category', 'damage_report')
            ->where('lgu_validation_status', FarmerCalendarEvent::VALIDATION_APPROVED)
            ->whereNotNull('crop_plan_event_id')
            ->groupBy('crop_plan_event_id');

        $query = FarmerCalendarEvent::query()
            ->from('farmer_calendar_events as plans')
            ->join('users', 'users.id', '=', 'plans.user_id')
            ->leftJoin('farmer_calendar_events as harvests', 'harvests.id', '=', 'plans.harvest_event_id')
            ->leftJoinSub($damageTotals, 'damage_totals', function ($join) {
                $join->on('damage_totals.crop_plan_event_id', '=', 'plans.id');
            })
            ->where('users.role', 'farmer')
            ->where('plans.category', 'crop_plan')
            ->whereNotNull('plans.crop')
            ->whereNotNull('plans.estimated_harvest_date')
            ->where(function ($statusQuery) {
                $statusQuery->where('plans.is_completed', false)
                    ->whereRaw('COALESCE(harvests.actual_harvest_production_mt, plans.actual_harvest_production_mt, 0) = 0');
            });

        if (! empty($filters['municipality'])) {
            $this->applyMunicipalityFilter($query, (string) $filters['municipality']);
        }

        if (! empty($filters['exclude_user_id'])) {
            $query->where('plans.user_id', '!=', (int) $filters['exclude_user_id']);
        }

        if (! empty($filters['from_date'])) {
            $query->whereDate('plans.estimated_harvest_date', '>=', (string) $filters['from_date']);
        }

        if (! empty($filters['to_date'])) {
            $query->whereDate('plans.estimated_harvest_date', '<=', (string) $filters['to_date']);
        }

        return $query
            ->select([
                'plans.id',
                'plans.user_id',
                'plans.crop',
                'plans.desired_area_sqm',
                'plans.predicted_production_mt',
                'plans.estimated_harvest_date',
                DB::raw('COALESCE(damage_totals.reported_damage_sqm, 0) as reported_damage_sqm'),
            ])
            ->get()
            ->groupBy(fn ($plan) => $this->normalizeCropKey($plan->crop))
            ->map(fn (Collection $plans, string $cropKey) => $this->summarizeCropPlans($plans, $cropKey))
            ->sortByDesc('pressure_score')
            ->values();
    }

    private function summarizeCropPlans(Collection $plans, string $cropKey): array
    {
        $plannedAreaSqm = 0.0;
        $damagedAreaSqm = 0.0;
        $expectedProduction = 0.0;
        $harvestMonths = collect();

        foreach ($plans as $plan) {
            $areaSqm = max(0, (float) ($plan->desired_area_sqm ?? 0));
            $damageSqm = min($areaSqm, max(0, (float) ($plan->reported_damage_sqm ?? 0)));
            $predictedProduction = max(0, (float) ($plan->predicted_production_mt ?? 0));
            $damageRatio = $areaSqm > 0 ? min(1, $damageSqm / $areaSqm) : 0;

            $plannedAreaSqm += $areaSqm;
            $damagedAreaSqm += $damageSqm;
            $expectedProduction += max(0, $predictedProduction * (1 - $damageRatio));

            if ($plan->estimated_harvest_date) {
                $harvestMonths->push(Carbon::parse($plan->estimated_harvest_date)->format('M Y'));
            }
        }

        $pressureScore = ($plans->count() * 2)
            + (($plannedAreaSqm / 10000) * 3)
            + $expectedProduction;
        $pressure = $this->resolvePressure($pressureScore, $plans->count());

        return [
            'crop' => $this->formatLabel((string) ($plans->first()->crop ?? $cropKey)),
            'crop_key' => $cropKey,
            'plan_count' => $plans->count(),
            'farmer_count' => $plans->pluck('user_id')->unique()->count(),
            'planned_area_sqm' => round($plannedAreaSqm, 2),
            'damaged_area_sqm' => round($damagedAreaSqm, 2),
            'expected_production_mt' => round($expectedProduction, 2),
            'pressure_score' => round($pressureScore, 2),
            'pressure_key' => $pressure['key'],
            'label' => $pressure['label'],
            'tone' => $pressure['tone'],
            'short_message' => $pressure['message'],
            'harvest_window' => $harvestMonths->unique()->take(2)->implode(', '),
        ];
    }

    private function emptyCropSignal(string $crop): array
    {
        return [
            'crop' => $this->formatLabel($crop),
            'crop_key' => $this->normalizeCropKey($crop),
            'plan_count' => 0,
            'farmer_count' => 0,
            'planned_area_sqm' => 0.0,
            'damaged_area_sqm' => 0.0,
            'expected_production_mt' => 0.0,
            'pressure_score' => 0.0,
            'pressure_key' => 'none',
            'label' => 'No nearby plans recorded',
            'tone' => 'slate',
            'short_message' => 'No nearby plans are recorded yet.',
            'harvest_window' => '',
        ];
    }

    private function resolvePressure(float $score, int $planCount): array
    {
        if ($score >= 8 || $planCount >= 5) {
            return [
                'key' => 'high',
                'label' => 'High nearby planting pressure',
                'tone' => 'amber',
                'message' => 'Recorded plans indicate high nearby planting pressure.',
            ];
        }

        if ($score >= 3 || $planCount >= 2) {
            return [
                'key' => 'balanced',
                'label' => 'Moderate nearby planting pressure',
                'tone' => 'sky',
                'message' => 'Recorded plans indicate moderate nearby planting pressure.',
            ];
        }

        return [
            'key' => 'low',
            'label' => 'Low nearby planting pressure',
            'tone' => 'emerald',
            'message' => 'Recorded plans indicate low nearby planting pressure.',
        ];
    }

    private function buildAdviceMessage(array $selected, Collection $alternatives, Carbon $harvestDate): string
    {
        if ($selected['pressure_key'] === 'high') {
            $alternativeText = $alternatives->isNotEmpty()
                ? ' You can compare it with ' . $alternatives->pluck('crop')->take(2)->implode(' or ') . '.'
                : ' You may check other crops first.';

            return "{$selected['crop']} has high nearby planting pressure for {$harvestDate->format('F')}.{$alternativeText}";
        }

        if ($selected['pressure_key'] === 'balanced') {
            return "{$selected['crop']} has moderate nearby planting pressure for {$harvestDate->format('F')}. Compare your options.";
        }

        if ($selected['pressure_key'] === 'none') {
            return "No nearby {$selected['crop']} plans are recorded for {$harvestDate->format('F')}. This does not confirm low supply.";
        }

        return "{$selected['crop']} has low nearby planting pressure for {$harvestDate->format('F')}. Check your farm estimate.";
    }

    private function applyMunicipalityFilter($query, string $municipality): void
    {
        $canonicalMunicipality = strtoupper(trim($municipality));
        $normalizedMunicipality = $this->normalizeMunicipalityKey($municipality);

        $query->where(function ($innerQuery) use ($canonicalMunicipality, $normalizedMunicipality) {
            $innerQuery->whereRaw('UPPER(users.preferred_municipality) = ?', [$canonicalMunicipality])
                ->orWhereRaw("UPPER(REPLACE(users.preferred_municipality, ' ', '')) = ?", [$normalizedMunicipality]);
        });
    }

    private function normalizeMunicipalityKey(?string $municipality): string
    {
        return str_replace(' ', '', strtoupper(trim((string) $municipality)));
    }

    private function normalizeCropKey(?string $crop): string
    {
        return strtoupper(trim((string) $crop));
    }

    private function formatLabel(string $value): string
    {
        return ucwords(strtolower(str_replace('_', ' ', trim($value))));
    }
}
