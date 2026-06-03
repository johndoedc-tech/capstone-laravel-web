<?php

namespace App\Services;

use App\Models\FarmerCalendarEvent;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class MunicipalSupplyForecastService
{
    public function getMunicipalityOutlook(string $municipality, array $filters = []): Collection
    {
        return $this->getPlanRows(array_merge($filters, [
            'municipality' => $municipality,
        ]))
            ->groupBy(fn ($plan) => $this->normalizeCropKey($plan->crop))
            ->map(fn (Collection $cropPlans, string $cropName) => $this->summarizePlans($cropPlans, [
                'crop' => $cropName,
            ]))
            ->sortByDesc('net_expected_production_mt')
            ->values();
    }

    public function getMunicipalitySummaries(array $filters = []): Collection
    {
        $farmerCounts = $this->getFarmerCountsByMunicipality();

        return $this->getPlanRows($filters)
            ->groupBy(fn ($plan) => $this->normalizeMunicipalityKey($plan->preferred_municipality))
            ->map(function (Collection $plans, string $normalizedMunicipality) use ($farmerCounts) {
                $municipality = $this->formatLabel((string) ($plans->first()->preferred_municipality ?? $normalizedMunicipality));
                $summary = $this->summarizePlans($plans, [
                    'municipality' => $municipality,
                    'normalized_municipality' => $normalizedMunicipality,
                ]);

                $summary['farmer_count'] = (int) ($farmerCounts->get($normalizedMunicipality)['farmer_count'] ?? 0);

                return $summary;
            })
            ->sortByDesc('net_expected_production_mt')
            ->values();
    }

    public function getFarmerCountsByMunicipality(): Collection
    {
        return User::query()
            ->where('role', 'farmer')
            ->whereNotNull('preferred_municipality')
            ->where('preferred_municipality', '!=', '')
            ->get(['id', 'preferred_municipality'])
            ->groupBy(fn (User $user) => $this->normalizeMunicipalityKey($user->preferred_municipality))
            ->map(function (Collection $farmers, string $normalizedMunicipality) {
                return [
                    'municipality' => $this->formatLabel((string) $farmers->first()->preferred_municipality),
                    'normalized_municipality' => $normalizedMunicipality,
                    'farmer_count' => $farmers->count(),
                ];
            });
    }

    public function getFarmerCountForMunicipality(string $municipality): int
    {
        return (int) ($this->getFarmerCountsByMunicipality()
            ->get($this->normalizeMunicipalityKey($municipality))['farmer_count'] ?? 0);
    }

    private function getPlanRows(array $filters): Collection
    {
        $seasonYear = isset($filters['year']) && $filters['year'] !== ''
            ? (int) $filters['year']
            : (int) now()->year;
        $seasonStart = Carbon::create($seasonYear, 1, 1)->startOfYear()->toDateString();
        $seasonEnd = Carbon::create($seasonYear, 12, 31)->endOfYear()->toDateString();

        $damageTotals = FarmerCalendarEvent::query()
            ->select('crop_plan_event_id', DB::raw('SUM(COALESCE(damage_area_sqm, 0)) as reported_damage_sqm'))
            ->where('category', 'damage_report')
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
            ->whereNotNull('users.preferred_municipality')
            ->where('users.preferred_municipality', '!=', '')
            ->where('plans.category', 'crop_plan')
            ->whereNotNull('plans.crop')
            ->where(function ($innerQuery) use ($seasonStart, $seasonEnd) {
                $innerQuery->whereDate('plans.event_date', '<=', $seasonEnd)
                    ->where(function ($dateQuery) use ($seasonStart) {
                        $dateQuery->whereNull('plans.estimated_harvest_date')
                            ->orWhereDate('plans.estimated_harvest_date', '>=', $seasonStart);
                    });
            });

        if (! empty($filters['municipality'])) {
            $this->applyUserMunicipalityFilter($query, (string) $filters['municipality']);
        }

        if (! empty($filters['crop'])) {
            $query->whereRaw('UPPER(plans.crop) = ?', [strtoupper((string) $filters['crop'])]);
        }

        if (! empty($filters['farm_type'])) {
            $query->whereRaw('UPPER(plans.water_source) = ?', [strtoupper((string) $filters['farm_type'])]);
        }

        return $query
            ->select([
                'plans.id',
                'plans.crop',
                'plans.desired_area_sqm',
                'plans.predicted_production_mt',
                'plans.is_completed as plan_is_completed',
                'plans.estimated_harvest_date',
                'users.preferred_municipality',
                DB::raw('COALESCE(damage_totals.reported_damage_sqm, 0) as reported_damage_sqm'),
                DB::raw('harvests.is_completed as harvest_is_completed'),
                DB::raw('COALESCE(harvests.actual_harvest_production_mt, plans.actual_harvest_production_mt) as actual_harvest_production_mt'),
            ])
            ->get();
    }

    private function summarizePlans(Collection $plans, array $identity): array
    {
        $summary = array_merge($identity, [
            'plan_count' => 0,
            'harvested_count' => 0,
            'damaged_plan_count' => 0,
            'planned_area_sqm' => 0.0,
            'damaged_area_sqm' => 0.0,
            'predicted_production_mt' => 0.0,
            'harvested_production_mt' => 0.0,
            'damaged_production_mt' => 0.0,
            'net_expected_production_mt' => 0.0,
            'supply_forecast_mt' => 0.0,
        ]);

        foreach ($plans as $plan) {
            $areaSqm = max(0, (float) ($plan->desired_area_sqm ?? 0));
            $damageSqm = min($areaSqm, max(0, (float) ($plan->reported_damage_sqm ?? 0)));
            $predictedProduction = max(0, (float) ($plan->predicted_production_mt ?? 0));
            $damageRatio = $areaSqm > 0 ? max(0, min(1, $damageSqm / $areaSqm)) : 0;
            $damagedProduction = $predictedProduction * $damageRatio;
            $adjustedProduction = max(0, $predictedProduction - $damagedProduction);
            $isHarvested = $this->isTruthy($plan->harvest_is_completed) || $this->isTruthy($plan->plan_is_completed);
            $actualHarvestProduction = max(0, (float) ($plan->actual_harvest_production_mt ?? 0));
            $harvestedProduction = $actualHarvestProduction > 0
                ? $actualHarvestProduction
                : ($isHarvested ? $adjustedProduction : 0);
            $remainingProduction = max(0, $adjustedProduction - $harvestedProduction);

            $summary['plan_count']++;
            $summary['harvested_count'] += ($isHarvested || $actualHarvestProduction > 0) ? 1 : 0;
            $summary['damaged_plan_count'] += $damageSqm > 0 ? 1 : 0;
            $summary['planned_area_sqm'] += $areaSqm;
            $summary['damaged_area_sqm'] += $damageSqm;
            $summary['predicted_production_mt'] += $predictedProduction;
            $summary['harvested_production_mt'] += $harvestedProduction;
            $summary['damaged_production_mt'] += $damagedProduction;
            $summary['net_expected_production_mt'] += $remainingProduction;
            $summary['supply_forecast_mt'] += $harvestedProduction + $remainingProduction;
        }

        foreach ([
            'planned_area_sqm',
            'damaged_area_sqm',
            'predicted_production_mt',
            'harvested_production_mt',
            'damaged_production_mt',
            'net_expected_production_mt',
            'supply_forecast_mt',
        ] as $field) {
            $summary[$field] = round($summary[$field], 2);
        }

        return $summary;
    }

    private function applyUserMunicipalityFilter($query, string $municipality): void
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
        return strtoupper(trim($value));
    }

    private function isTruthy($value): bool
    {
        return in_array($value, [true, 1, '1', 't', 'true', 'TRUE'], true);
    }
}
