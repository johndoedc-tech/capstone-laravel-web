<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CropProduction;
use App\Models\FarmerCalendarEvent;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MapDataController extends Controller
{
    /**
     * Get aggregated production data for map
     * GET /api/map/data?crop=CABBAGE&year=2024&view=production&farm_type=IRRIGATED
     */
    public function getMapData(Request $request)
    {
        $crop = $request->input('crop');
        $year = $request->input('year');
        $view = $request->input('view', 'production'); // production, productivity, area_planted, area_harvested
        $farmType = $request->input('farm_type'); // optional: IRRIGATED or RAINFED
        $farmerCounts = $this->getFarmerCountsByMunicipality();

        $query = CropProduction::query();

        if ($crop) {
            $query->where('crop', $crop);
        }

        if ($year) {
            $query->where('year', $year);
        }

        if ($farmType) {
            $query->where('farm_type', $farmType);
        }

        // Determine aggregation based on view type
        $selectField = match ($view) {
            'productivity' => 'AVG(productivity) as value',
            'area_planted' => 'SUM(area_planted) as value',
            'area_harvested' => 'SUM(area_harvested) as value',
            default => 'SUM(production) as value'
        };

        $data = $query
            ->select('municipality', DB::raw($selectField))
            ->groupBy('municipality')
            ->get()
            ->map(function ($item) use ($farmerCounts) {
                $farmerCount = $farmerCounts->get($this->normalizeMunicipalityKey($item->municipality))['farmer_count'] ?? 0;

                return [
                    'municipality' => $item->municipality,
                    'value' => round($item->value, 2),
                    'farmer_count' => $farmerCount,
                ];
            });

        // Calculate statistics
        $values = $data->pluck('value')->filter(fn($v) => $v > 0);

        return response()->json([
            'success' => true,
            'data' => $data,
            'metadata' => [
                'crop' => $crop,
                'year' => $year,
                'view' => $view,
                'farm_type' => $farmType,
                'min' => $values->min() ?? 0,
                'max' => $values->max() ?? 0,
                'avg' => round($values->avg() ?? 0, 2),
                'total' => round($values->sum(), 2),
                'farmer_total' => $farmerCounts->sum('farmer_count'),
                'unit' => $this->getUnit($view)
            ],
            'farmer_counts' => $farmerCounts->values(),
        ]);
    }

    /**
     * Get municipality details
     * GET /api/map/municipality/{name}?crop=CABBAGE&year=2024
     */
    public function getMunicipalityDetails(Request $request, $municipality)
    {
        $crop = $request->input('crop');
        $year = $request->input('year');
        $farmType = $request->input('farm_type');
        $farmerCount = $this->getFarmerCountForMunicipality($municipality);

        // Monthly production data
        $monthlyQuery = CropProduction::query();
        $this->applyMunicipalityFilter($monthlyQuery, $municipality);
        if ($crop)
            $monthlyQuery->where('crop', $crop);
        if ($year)
            $monthlyQuery->where('year', $year);
        if ($farmType)
            $monthlyQuery->where('farm_type', $farmType);
        $monthOrder = "'JAN','FEB','MAR','APR','MAY','JUN','JUL','AUG','SEP','OCT','NOV','DEC'";
        if (config('database.default') === 'pgsql') {
            $monthlyData = $monthlyQuery
                ->select('month', DB::raw('SUM(production) as total_production'))
                ->groupBy('month')
                ->orderByRaw("array_position(ARRAY[$monthOrder], month)")
                ->get();
        } else {
            $monthlyData = $monthlyQuery
                ->select('month', DB::raw('SUM(production) as total_production'))
                ->groupBy('month')
                ->orderByRaw("FIELD(month, $monthOrder)")
                ->get();
        }

        // Crop distribution (all crops for this municipality and year)
        $cropDistQuery = CropProduction::query();
        $this->applyMunicipalityFilter($cropDistQuery, $municipality);
        if ($year)
            $cropDistQuery->where('year', $year);
        if ($farmType)
            $cropDistQuery->where('farm_type', $farmType);
        $cropDistribution = $cropDistQuery
            ->select('crop', DB::raw('SUM(production) as total_production'))
            ->groupBy('crop')
            ->orderBy('total_production', 'desc')
            ->limit(10)
            ->get();

        // Farm type breakdown
        $farmQuery = CropProduction::query();
        $this->applyMunicipalityFilter($farmQuery, $municipality);
        if ($crop)
            $farmQuery->where('crop', $crop);
        if ($year)
            $farmQuery->where('year', $year);
        if ($farmType)
            $farmQuery->where('farm_type', $farmType);
        $farmTypeBreakdown = $farmQuery
            ->select(
                'farm_type',
                DB::raw('SUM(production) as total_production'),
                DB::raw('AVG(productivity) as avg_productivity'),
                DB::raw('SUM(area_harvested) as total_area')
            )
            ->groupBy('farm_type')
            ->get();

        // Summary statistics
        $summaryQuery = CropProduction::query();
        $this->applyMunicipalityFilter($summaryQuery, $municipality);
        if ($crop)
            $summaryQuery->where('crop', $crop);
        if ($year)
            $summaryQuery->where('year', $year);
        if ($farmType)
            $summaryQuery->where('farm_type', $farmType);
        $summary = $summaryQuery
            ->selectRaw('
                SUM(production) as total_production,
                AVG(productivity) as avg_productivity,
                SUM(area_planted) as total_area_planted,
                SUM(area_harvested) as total_area_harvested
            ')
            ->first();

        return response()->json([
            'success' => true,
            'municipality' => $municipality,
            'crop' => $crop,
            'year' => $year,
            'summary' => [
                'total_production' => round($summary->total_production ?? 0, 2),
                'avg_productivity' => round($summary->avg_productivity ?? 0, 2),
                'total_area_planted' => round($summary->total_area_planted ?? 0, 2),
                'total_area_harvested' => round($summary->total_area_harvested ?? 0, 2),
                'farmer_count' => $farmerCount,
            ],
            'monthly_data' => $monthlyData,
            'crop_distribution' => $cropDistribution,
            'farm_type_breakdown' => $farmTypeBreakdown,
            'production_outlook' => $this->getRealtimeProductionOutlook($request, $municipality),
        ]);
    }

    /**
     * Get filter options
     * GET /api/map/filters
     */
    public function getFilterOptions()
    {
        $municipalities = CropProduction::distinct()
            ->pluck('municipality')
            ->sort()
            ->values();

        $crops = CropProduction::distinct()
            ->pluck('crop')
            ->sort()
            ->values();

        $years = CropProduction::distinct()
            ->pluck('year')
            ->sort()
            ->values();

        $months = ['JAN', 'FEB', 'MAR', 'APR', 'MAY', 'JUN', 'JUL', 'AUG', 'SEP', 'OCT', 'NOV', 'DEC'];

        $farmTypes = CropProduction::distinct()
            ->pluck('farm_type')
            ->sort()
            ->values();

        return response()->json([
            'success' => true,
            'municipalities' => $municipalities,
            'crops' => $crops,
            'years' => $years,
            'months' => $months,
            'farm_types' => $farmTypes,
            'view_types' => [
                ['value' => 'production', 'label' => 'Production (mt)'],
                ['value' => 'productivity', 'label' => 'Productivity (mt/ha)'],
                ['value' => 'area_planted', 'label' => 'Area Planted (ha)'],
                ['value' => 'area_harvested', 'label' => 'Area Harvested (ha)']
            ]
        ]);
    }

    /**
     * Get timeline data for animation
     * GET /api/map/timeline?crop=CABBAGE&year=2024&view=production
     */
    public function getTimelineData(Request $request)
    {
        $crop = $request->input('crop');
        $year = $request->input('year');
        $view = $request->input('view', 'production');
        $farmType = $request->input('farm_type');

        $months = ['JAN', 'FEB', 'MAR', 'APR', 'MAY', 'JUN', 'JUL', 'AUG', 'SEP', 'OCT', 'NOV', 'DEC'];

        $selectField = match ($view) {
            'productivity' => 'AVG(productivity) as value',
            'area_planted' => 'SUM(area_planted) as value',
            'area_harvested' => 'SUM(area_harvested) as value',
            default => 'SUM(production) as value'
        };

        $timelineData = [];

        foreach ($months as $month) {
            $query = CropProduction::query()
                ->where('month', $month);

            if ($crop) {
                $query->where('crop', $crop);
            }

            if ($year) {
                $query->where('year', $year);
            }

            if ($farmType) {
                $query->where('farm_type', $farmType);
            }

            $monthData = $query
                ->select('municipality', DB::raw($selectField))
                ->groupBy('municipality')
                ->get()
                ->map(function ($item) {
                    return [
                        'municipality' => $item->municipality,
                        'value' => round($item->value, 2)
                    ];
                });

            $timelineData[] = [
                'month' => $month,
                'data' => $monthData
            ];
        }

        return response()->json([
            'success' => true,
            'crop' => $crop,
            'year' => $year,
            'view' => $view,
            'timeline' => $timelineData
        ]);
    }

    /**
     * Get comparison data between municipalities
     * GET /api/map/compare?municipalities=ATOK,BAKUN&crop=CABBAGE&year=2024
     */
    public function compareData(Request $request)
    {
        $municipalities = explode(',', $request->input('municipalities', ''));
        $crop = $request->input('crop');
        $year = $request->input('year');

        $comparisonData = [];

        foreach ($municipalities as $municipality) {
            $query = CropProduction::query();
            $this->applyMunicipalityFilter($query, trim($municipality));

            if ($crop) {
                $query->where('crop', $crop);
            }

            if ($year) {
                $query->where('year', $year);
            }

            $data = $query
                ->selectRaw('
                    municipality,
                    SUM(production) as total_production,
                    AVG(productivity) as avg_productivity,
                    SUM(area_planted) as total_area_planted,
                    SUM(area_harvested) as total_area_harvested
                ')
                ->groupBy('municipality')
                ->first();

            if ($data) {
                $comparisonData[] = [
                    'municipality' => $data->municipality,
                    'total_production' => round($data->total_production, 2),
                    'avg_productivity' => round($data->avg_productivity, 2),
                    'total_area_planted' => round($data->total_area_planted, 2),
                    'total_area_harvested' => round($data->total_area_harvested, 2),
                ];
            }
        }

        return response()->json([
            'success' => true,
            'crop' => $crop,
            'year' => $year,
            'comparison' => $comparisonData
        ]);
    }

    /**
     * Get statistics summary
     * GET /api/map/statistics
     */
    public function getStatistics()
    {
        $totalRecords = CropProduction::count();
        $municipalities = CropProduction::distinct('municipality')->count();
        $crops = CropProduction::distinct('crop')->count();
        $minYear = CropProduction::min('year');
        $maxYear = CropProduction::max('year');

        $totalProduction = CropProduction::sum('production');
        $totalArea = CropProduction::sum('area_harvested');
        $avgProductivity = CropProduction::avg('productivity');

        // Top producing municipalities
        $topMunicipalities = CropProduction::select('municipality', DB::raw('SUM(production) as total'))
            ->groupBy('municipality')
            ->orderBy('total', 'desc')
            ->limit(5)
            ->get();

        // Top crops
        $topCrops = CropProduction::select('crop', DB::raw('SUM(production) as total'))
            ->groupBy('crop')
            ->orderBy('total', 'desc')
            ->limit(5)
            ->get();

        return response()->json([
            'success' => true,
            'statistics' => [
                'total_records' => $totalRecords,
                'municipalities_count' => $municipalities,
                'crops_count' => $crops,
                'years_covered' => [
                    'min' => $minYear,
                    'max' => $maxYear,
                    'range' => ($maxYear - $minYear + 1) . ' years'
                ],
                'total_production' => round($totalProduction, 2),
                'total_area_harvested' => round($totalArea, 2),
                'avg_productivity' => round($avgProductivity, 2),
                'top_municipalities' => $topMunicipalities,
                'top_crops' => $topCrops
            ]
        ]);
    }

    /**
     * Apply resilient municipality filtering (case/spacing insensitive).
     */
    private function applyMunicipalityFilter($query, string $municipality): void
    {
        $canonicalMunicipality = strtoupper(trim($municipality));
        $normalizedMunicipality = str_replace(' ', '', $canonicalMunicipality);

        $query->where(function ($innerQuery) use ($canonicalMunicipality, $normalizedMunicipality) {
            $innerQuery->whereRaw('UPPER(municipality) = ?', [$canonicalMunicipality])
                ->orWhereRaw("UPPER(REPLACE(municipality, ' ', '')) = ?", [$normalizedMunicipality]);
        });
    }

    private function getFarmerCountsByMunicipality()
    {
        return User::query()
            ->where('role', 'farmer')
            ->whereNotNull('preferred_municipality')
            ->where('preferred_municipality', '!=', '')
            ->get(['id', 'preferred_municipality'])
            ->groupBy(fn (User $user) => $this->normalizeMunicipalityKey($user->preferred_municipality))
            ->map(function ($farmers, string $normalizedMunicipality) {
                $municipality = strtoupper(trim((string) $farmers->first()->preferred_municipality));

                return [
                    'municipality' => $municipality,
                    'normalized_municipality' => $normalizedMunicipality,
                    'farmer_count' => $farmers->count(),
                ];
            });
    }

    private function getFarmerCountForMunicipality(string $municipality): int
    {
        return (int) ($this->getFarmerCountsByMunicipality()
            ->get($this->normalizeMunicipalityKey($municipality))['farmer_count'] ?? 0);
    }

    private function getRealtimeProductionOutlook(Request $request, string $municipality)
    {
        $crop = $request->input('crop');
        $farmType = $request->input('farm_type');
        $seasonStart = now()->copy()->startOfYear()->toDateString();
        $seasonEnd = now()->copy()->endOfYear()->toDateString();

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
            ->where('plans.category', 'crop_plan')
            ->whereNotNull('plans.crop')
            ->where(function ($innerQuery) use ($seasonStart, $seasonEnd) {
                $innerQuery->whereDate('plans.event_date', '<=', $seasonEnd)
                    ->where(function ($dateQuery) use ($seasonStart) {
                        $dateQuery->whereNull('plans.estimated_harvest_date')
                            ->orWhereDate('plans.estimated_harvest_date', '>=', $seasonStart);
                    });
            });

        $this->applyUserMunicipalityFilter($query, $municipality);

        if ($crop) {
            $query->whereRaw('UPPER(plans.crop) = ?', [strtoupper($crop)]);
        }

        if ($farmType) {
            $query->whereRaw('UPPER(plans.water_source) = ?', [strtoupper($farmType)]);
        }

        $plans = $query
            ->select([
                'plans.id',
                'plans.crop',
                'plans.desired_area_sqm',
                'plans.predicted_production_mt',
                'plans.is_completed as plan_is_completed',
                'plans.estimated_harvest_date',
                DB::raw('COALESCE(damage_totals.reported_damage_sqm, 0) as reported_damage_sqm'),
                DB::raw('COALESCE(harvests.is_completed, false) as harvest_is_completed'),
            ])
            ->get();

        return $plans
            ->groupBy(fn ($plan) => strtoupper(trim((string) $plan->crop)))
            ->map(function ($cropPlans, string $cropName) {
                $summary = [
                    'crop' => $cropName,
                    'plan_count' => 0,
                    'harvested_count' => 0,
                    'damaged_plan_count' => 0,
                    'planned_area_sqm' => 0.0,
                    'damaged_area_sqm' => 0.0,
                    'predicted_production_mt' => 0.0,
                    'harvested_production_mt' => 0.0,
                    'damaged_production_mt' => 0.0,
                    'net_expected_production_mt' => 0.0,
                ];

                foreach ($cropPlans as $plan) {
                    $areaSqm = max(0, (float) ($plan->desired_area_sqm ?? 0));
                    $damageSqm = min($areaSqm, max(0, (float) ($plan->reported_damage_sqm ?? 0)));
                    $predictedProduction = max(0, (float) ($plan->predicted_production_mt ?? 0));
                    $damageRatio = $areaSqm > 0 ? max(0, min(1, $damageSqm / $areaSqm)) : 0;
                    $damagedProduction = round($predictedProduction * $damageRatio, 2);
                    $adjustedProduction = max(0, $predictedProduction - $damagedProduction);
                    $isHarvested = $this->isTruthy($plan->harvest_is_completed) || $this->isTruthy($plan->plan_is_completed);
                    $harvestedProduction = $isHarvested ? $adjustedProduction : 0;

                    $summary['plan_count']++;
                    $summary['harvested_count'] += $isHarvested ? 1 : 0;
                    $summary['damaged_plan_count'] += $damageSqm > 0 ? 1 : 0;
                    $summary['planned_area_sqm'] += $areaSqm;
                    $summary['damaged_area_sqm'] += $damageSqm;
                    $summary['predicted_production_mt'] += $predictedProduction;
                    $summary['harvested_production_mt'] += $harvestedProduction;
                    $summary['damaged_production_mt'] += $damagedProduction;
                    $summary['net_expected_production_mt'] += max(0, $adjustedProduction - $harvestedProduction);
                }

                foreach (['planned_area_sqm', 'damaged_area_sqm', 'predicted_production_mt', 'harvested_production_mt', 'damaged_production_mt', 'net_expected_production_mt'] as $field) {
                    $summary[$field] = round($summary[$field], 2);
                }

                return $summary;
            })
            ->sortByDesc('net_expected_production_mt')
            ->values();
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

    private function isTruthy($value): bool
    {
        return in_array($value, [true, 1, '1', 't', 'true', 'TRUE'], true);
    }

    private function normalizeMunicipalityKey(?string $municipality): string
    {
        return str_replace(' ', '', strtoupper(trim((string) $municipality)));
    }

    /**
     * Helper function to get unit based on view type
     */
    private function getUnit($view)
    {
        return match ($view) {
            'productivity' => 'mt/ha',
            'area_planted' => 'ha',
            'area_harvested' => 'ha',
            default => 'mt'
        };
    }
}
