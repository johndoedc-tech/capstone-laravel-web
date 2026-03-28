<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CropProduction;
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
            ->map(function ($item) {
                return [
                    'municipality' => $item->municipality,
                    'value' => round($item->value, 2)
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
                'unit' => $this->getUnit($view)
            ]
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

        // Monthly production data
        $monthlyQuery = CropProduction::where('municipality', $municipality);
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
        $cropDistQuery = CropProduction::where('municipality', $municipality);
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
        $farmQuery = CropProduction::where('municipality', $municipality);
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
        $summaryQuery = CropProduction::where('municipality', $municipality);
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
            ],
            'monthly_data' => $monthlyData,
            'crop_distribution' => $cropDistribution,
            'farm_type_breakdown' => $farmTypeBreakdown
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
            $query = CropProduction::where('municipality', trim($municipality));

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
