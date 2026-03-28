<?php

namespace App\Http\Controllers;

use App\Models\CropProduction;
use App\Models\Prediction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class FarmerDashboardController extends Controller
{
    /**
     * List of all municipalities
     */
    private array $municipalities = [
        'ATOK', 'BAKUN', 'BOKOD', 'BUGUIAS', 'ITOGON', 
        'KABAYAN', 'KAPANGAN', 'KIBUNGAN', 'LA TRINIDAD', 
        'MANKAYAN', 'SABLAN', 'TUBA', 'TUBLAY'
    ];

    /**
     * List of all crops
     */
    private array $crops = [
        'Cabbage', 'Broccoli', 'Lettuce', 'Cauliflower', 'Chinese Cabbage',
        'Carrots', 'Garden Peas', 'White Potato', 'Snap Beans', 'Sweet Pepper'
    ];

    /**
     * Month abbreviations
     */
    private array $months = [
        'JAN', 'FEB', 'MAR', 'APR', 'MAY', 'JUN',
        'JUL', 'AUG', 'SEP', 'OCT', 'NOV', 'DEC'
    ];

    /**
     * Display the farmer dashboard with enhanced widgets
     */
    public function index()
    {
        $user = Auth::user();
        
        // Basic statistics
        $totalRecords = CropProduction::count();
        $municipalitiesCount = CropProduction::distinct('municipality')->count();
        $cropTypesCount = CropProduction::distinct('crop')->count();
        $predictionsCount = Prediction::where('user_id', $user->id)->count();

        // User preferences
        $preferredMunicipality = $user->preferred_municipality;
        $favoriteCrops = $user->favorite_crops ?? [];

        return view('dashboard-simple', compact(
            'totalRecords',
            'municipalitiesCount',
            'cropTypesCount',
            'predictionsCount',
            'preferredMunicipality',
            'favoriteCrops'
        ));
    }

    /**
     * Save farmer preferences (municipality and favorite crops)
     */
    public function savePreferences(Request $request)
    {
        $request->validate([
            'preferred_municipality' => 'nullable|string|in:' . implode(',', $this->municipalities),
            'favorite_crops' => 'nullable|array|max:5',
            'favorite_crops.*' => 'string|in:' . implode(',', $this->crops),
        ]);

        /** @var \App\Models\User $user */
        $user = Auth::user();
        $user->update([
            'preferred_municipality' => $request->preferred_municipality,
            'favorite_crops' => $request->favorite_crops,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Preferences saved successfully!',
            'data' => [
                'preferred_municipality' => $user->preferred_municipality,
                'favorite_crops' => $user->favorite_crops,
            ]
        ]);
    }

    /**
     * Get crop recommendations based on municipality and month
     */
    public function getRecommendations(Request $request)
    {
        $request->validate([
            'municipality' => 'required|string',
            'month' => 'required|string|in:' . implode(',', $this->months),
        ]);

        $municipality = strtoupper($request->municipality);
        $month = strtoupper($request->month);

        // Get historical production data for the selected municipality and month
        $recommendations = CropProduction::select(
                'crop',
                DB::raw('AVG(production) as avg_production'),
                DB::raw('AVG(productivity) as avg_productivity'),
                DB::raw('AVG(area_harvested) as avg_area'),
                DB::raw('COUNT(*) as data_points'),
                DB::raw('MAX(production) as max_production'),
                DB::raw('MIN(production) as min_production')
            )
            ->where('municipality', $municipality)
            ->where('month', $month)
            ->where('production', '>', 0) // Only crops that actually produced
            ->groupBy('crop')
            ->orderByDesc('avg_production')
            ->limit(5)
            ->get();

        // Calculate a simple recommendation score based on:
        // - Average production (40%)
        // - Productivity/yield (30%)
        // - Consistency (low variance) (30%)
        $recommendations = $recommendations->map(function ($crop) {
            $variance = $crop->max_production > 0 
                ? ($crop->max_production - $crop->min_production) / $crop->max_production 
                : 1;
            $consistency = 1 - $variance; // Higher is better
            
            // Normalize scores (simple approach)
            $crop->recommendation_score = round(
                ($crop->avg_production * 0.4) + 
                ($crop->avg_productivity * 100 * 0.3) + 
                ($consistency * 100 * 0.3)
            , 2);
            
            $crop->avg_production = round($crop->avg_production, 2);
            $crop->avg_productivity = round($crop->avg_productivity, 2);
            $crop->avg_area = round($crop->avg_area, 2);
            $crop->consistency_rating = $consistency >= 0.7 ? 'High' : ($consistency >= 0.4 ? 'Medium' : 'Variable');
            
            return $crop;
        })->sortByDesc('recommendation_score')->values();

        // Get month name for display
        $monthNames = [
            'JAN' => 'January', 'FEB' => 'February', 'MAR' => 'March',
            'APR' => 'April', 'MAY' => 'May', 'JUN' => 'June',
            'JUL' => 'July', 'AUG' => 'August', 'SEP' => 'September',
            'OCT' => 'October', 'NOV' => 'November', 'DEC' => 'December'
        ];

        return response()->json([
            'success' => true,
            'municipality' => $municipality,
            'month' => $monthNames[$month] ?? $month,
            'recommendations' => $recommendations,
            'total_crops_analyzed' => $recommendations->count(),
        ]);
    }

    /**
     * Get crop comparison data for side-by-side analysis
     */
    public function compareCrops(Request $request)
    {
        $request->validate([
            'crops' => 'required|array|min:2|max:3',
            'crops.*' => 'string',
            'municipality' => 'nullable|string',
        ]);

        $crops = $request->crops;
        $municipality = $request->municipality ? strtoupper($request->municipality) : null;

        // Convert crop names to uppercase for database query
        $cropsUpper = array_map('strtoupper', $crops);

        $query = CropProduction::select(
                'crop',
                'month',
                DB::raw('AVG(production) as avg_production'),
                DB::raw('AVG(productivity) as avg_productivity'),
                DB::raw('AVG(area_harvested) as avg_area')
            )
            ->whereIn('crop', $cropsUpper);

        if ($municipality) {
            $query->where('municipality', $municipality);
        }

        $data = $query->groupBy('crop', 'month')
            ->orderBy('crop')
            ->get();

        // Organize data by crop (use original crop names for response)
        $comparison = [];
        foreach ($crops as $crop) {
            $cropUpper = strtoupper($crop);
            $cropData = $data->where('crop', $cropUpper);
            
            // Calculate yearly totals
            $yearlyProduction = $cropData->sum('avg_production');
            $avgProductivity = $cropData->avg('avg_productivity');
            $avgArea = $cropData->avg('avg_area');
            
            // Monthly breakdown
            $monthlyData = [];
            foreach ($this->months as $month) {
                $monthRecord = $cropData->where('month', $month)->first();
                $monthlyData[$month] = $monthRecord ? round($monthRecord->avg_production, 2) : 0;
            }

            // Find peak months (top 3)
            arsort($monthlyData);
            $peakMonths = array_slice(array_keys($monthlyData), 0, 3);

            $comparison[$crop] = [
                'yearly_production' => round($yearlyProduction, 2),
                'avg_productivity' => round($avgProductivity, 2),
                'avg_area' => round($avgArea, 2),
                'monthly_data' => $monthlyData,
                'peak_months' => $peakMonths,
            ];
        }

        return response()->json([
            'success' => true,
            'municipality' => $municipality ?? 'All Municipalities',
            'comparison' => $comparison,
        ]);
    }

    /**
     * Get planting calendar heatmap data
     */
    public function getCalendarData(Request $request)
    {
        $municipality = $request->municipality ? strtoupper($request->municipality) : null;
        $year = $request->year ? (int) $request->year : null;

        $query = CropProduction::select(
                'crop',
                'month',
                DB::raw('AVG(production) as avg_production'),
                DB::raw('MAX(production) as max_production')
            );

        if ($municipality) {
            $query->where('municipality', $municipality);
        }

        if ($year) {
            $query->where('year', $year);
        }

        $data = $query->groupBy('crop', 'month')
            ->orderBy('crop')
            ->get();

        // Organize data by crop and month
        $calendarData = [];
        $maxProduction = $data->max('avg_production') ?: 1;

        foreach ($this->crops as $crop) {
            // Match database format (uppercase)
            $cropUpper = strtoupper($crop);
            $cropData = $data->where('crop', $cropUpper);
            $monthlyData = [];
            $bestMonth = null;
            $bestProduction = 0;

            foreach ($this->months as $month) {
                $record = $cropData->where('month', $month)->first();
                $production = $record ? round($record->avg_production, 2) : 0;
                $monthlyData[$month] = $production;

                if ($production > $bestProduction) {
                    $bestProduction = $production;
                    $bestMonth = $month;
                }
            }

            $calendarData[$crop] = [
                'monthly' => $monthlyData,
                'best_month' => $bestMonth,
                'best_production' => $bestProduction,
                'total_production' => array_sum($monthlyData),
            ];
        }

        // Calculate insights
        $insights = [];
        
        // Best overall crop
        $bestCrop = collect($calendarData)->sortByDesc('total_production')->keys()->first();
        if ($bestCrop) {
            $insights[] = [
                'label' => 'Top Producing Crop',
                'value' => $bestCrop,
            ];
        }

        // Best month overall
        $monthTotals = [];
        foreach ($this->months as $month) {
            $monthTotals[$month] = collect($calendarData)->sum(fn($c) => $c['monthly'][$month] ?? 0);
        }
        arsort($monthTotals);
        $bestOverallMonth = array_key_first($monthTotals);
        if ($bestOverallMonth) {
            $monthNames = [
                'JAN' => 'January', 'FEB' => 'February', 'MAR' => 'March',
                'APR' => 'April', 'MAY' => 'May', 'JUN' => 'June',
                'JUL' => 'July', 'AUG' => 'August', 'SEP' => 'September',
                'OCT' => 'October', 'NOV' => 'November', 'DEC' => 'December'
            ];
            $insights[] = [
                'label' => 'Peak Harvest Month',
                'value' => $monthNames[$bestOverallMonth] ?? $bestOverallMonth,
            ];
        }

        // Current month recommendation
        $currentMonth = strtoupper(date('M'));
        $currentMonthBest = collect($calendarData)
            ->sortByDesc(fn($c) => $c['monthly'][$currentMonth] ?? 0)
            ->keys()
            ->first();
        if ($currentMonthBest) {
            $insights[] = [
                'label' => 'Best for This Month',
                'value' => $currentMonthBest,
            ];
        }

        return response()->json([
            'success' => true,
            'municipality' => $municipality ?? 'All Municipalities',
            'calendar' => $calendarData,
            'max_production' => $maxProduction,
            'insights' => $insights,
        ]);
    }

    /**
     * Calculate What-If scenario prediction
     */
    public function calculateScenario(Request $request)
    {
        $request->validate([
            'municipality' => 'required|string',
            'crop' => 'required|string',
            'month' => 'required|string|in:' . implode(',', $this->months),
            'area' => 'required|numeric|min:0.1|max:100',
        ]);

        $municipality = strtoupper($request->municipality);
        $crop = strtoupper($request->crop);
        $month = strtoupper($request->month);
        $area = floatval($request->area);

        // Get historical data for this scenario
        $historicalData = CropProduction::where('municipality', $municipality)
            ->where('crop', $crop)
            ->where('month', $month)
            ->where('production', '>', 0)
            ->get();

        if ($historicalData->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No historical data available for this scenario',
            ]);
        }

        // Calculate statistics
        $avgProductivity = $historicalData->avg('productivity');
        $maxProductivity = $historicalData->max('productivity');
        $minProductivity = $historicalData->min('productivity');
        $avgProduction = $historicalData->avg('production');
        $dataPoints = $historicalData->count();

        // Calculate predictions based on area
        $predictedProduction = round($avgProductivity * $area, 2);
        $bestCase = round($maxProductivity * $area, 2);
        $worstCase = round($minProductivity * $area, 2);

        // Generate recommendation
        $isRecommended = true;
        $recommendation = '';

        // Check if this is a good month for this crop
        $monthlyAvg = CropProduction::where('municipality', $municipality)
            ->where('crop', $crop)
            ->where('production', '>', 0)
            ->groupBy('month')
            ->selectRaw('month, AVG(productivity) as avg_prod')
            ->orderByDesc('avg_prod')
            ->get();

        $bestMonths = $monthlyAvg->take(3)->pluck('month')->toArray();
        $currentRank = $monthlyAvg->search(fn($m) => $m->month === $month) + 1;

        if (in_array($month, $bestMonths)) {
            $recommendation = "{$request->crop} performs excellently in this month! This is one of the top 3 months for productivity.";
            $isRecommended = true;
        } elseif ($currentRank <= 6) {
            $recommendation = "This is a decent month for {$request->crop}. Consider planting in " . implode(' or ', array_slice($bestMonths, 0, 2)) . " for potentially better yields.";
            $isRecommended = true;
        } else {
            $betterMonths = array_slice($bestMonths, 0, 2);
            $recommendation = "This may not be the optimal time for {$request->crop}. Historical data suggests " . implode(' or ', $betterMonths) . " typically yield better results.";
            $isRecommended = false;
        }

        // Month names for display
        $monthNames = [
            'JAN' => 'January', 'FEB' => 'February', 'MAR' => 'March',
            'APR' => 'April', 'MAY' => 'May', 'JUN' => 'June',
            'JUL' => 'July', 'AUG' => 'August', 'SEP' => 'September',
            'OCT' => 'October', 'NOV' => 'November', 'DEC' => 'December'
        ];

        return response()->json([
            'success' => true,
            'predicted_production' => $predictedProduction,
            'avg_productivity' => round($avgProductivity, 2),
            'best_case' => $bestCase,
            'worst_case' => $worstCase,
            'data_points' => $dataPoints,
            'recommendation' => $recommendation,
            'is_recommended' => $isRecommended,
            'best_months' => array_map(fn($m) => $monthNames[$m] ?? $m, $bestMonths),
        ]);
    }

    /**
     * Get available options for dropdowns
     */
    public function getOptions()
    {
        return response()->json([
            'municipalities' => $this->municipalities,
            'crops' => $this->crops,
            'months' => $this->months,
        ]);
    }
}
