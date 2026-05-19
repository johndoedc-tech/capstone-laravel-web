<?php

namespace App\Http\Controllers;

use App\Models\CropProduction;
use App\Models\FarmerCalendarEvent;
use App\Models\Prediction;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class ReportController extends Controller
{
    /**
     * Display reports dashboard
     */
    public function index()
    {
        // Get summary statistics
        $stats = [
            'totalRecords' => CropProduction::count(),
            'totalPredictions' => Prediction::count(),
            'totalUsers' => User::where('role', 'farmer')->count(),
            'municipalities' => CropProduction::distinct('municipality')->count(),
            'crops' => CropProduction::distinct('crop')->count(),
            'dateRange' => [
                'start' => CropProduction::min('year'),
                'end' => CropProduction::max('year'),
            ],
        ];

        $now = now();
        $startOfWeek = $now->copy()->startOfWeek();
        $lastThirtyDays = $now->copy()->subDays(30);

        $predictionSummary = [
            'predictions_this_week' => Prediction::successful()
                ->where('created_at', '>=', $startOfWeek)
                ->count(),
            'average_confidence' => (float) (Prediction::successful()
                ->whereNotNull('confidence_score')
                ->where('created_at', '>=', $lastThirtyDays)
                ->avg('confidence_score') ?? 0),
            'top_municipality' => Prediction::successful()
                ->whereNotNull('municipality')
                ->where('created_at', '>=', $lastThirtyDays)
                ->select('municipality', DB::raw('COUNT(*) as total'))
                ->groupBy('municipality')
                ->orderByDesc('total')
                ->first(),
            'window_start' => $lastThirtyDays,
            'week_start' => $startOfWeek,
        ];

        return view('admin.reports.index', compact('stats', 'predictionSummary'));
    }

    /**
     * Generate Production Summary Report
     */
    public function productionSummary(Request $request)
    {
        $query = CropProduction::query();

        // Apply filters
        if ($request->filled('year')) {
            $query->where('year', $request->year);
        }
        if ($request->filled('municipality')) {
            $query->where('municipality', $request->municipality);
        }
        if ($request->filled('crop')) {
            $query->where('crop', $request->crop);
        }

        // Get data grouped by municipality and crop
        $data = $query->select(
            'municipality',
            'crop',
            DB::raw('SUM(production) as total_production'),
            DB::raw('SUM(area_harvested) as total_area'),
            DB::raw('AVG(productivity) as avg_productivity'),
            DB::raw('COUNT(*) as record_count')
        )
        ->groupBy('municipality', 'crop')
        ->orderBy('municipality')
        ->orderBy('total_production', 'desc')
        ->get();

        // Get summary totals
        $totals = [
            'production' => $data->sum('total_production'),
            'area' => $data->sum('total_area'),
            'avg_productivity' => $data->avg('avg_productivity'),
            'records' => $data->sum('record_count'),
        ];

        // Get filter options
        $years = CropProduction::distinct()->pluck('year')->sort()->values();
        $municipalities = CropProduction::distinct()->pluck('municipality')->sort()->values();
        $crops = CropProduction::distinct()->pluck('crop')->sort()->values();

        if ($request->get('format') === 'pdf') {
            $pdf = Pdf::loadView('admin.reports.pdf.production-summary', compact('data', 'totals', 'request'));
            return $pdf->download('production-summary-' . now()->format('Y-m-d') . '.pdf');
        }

        if ($request->get('format') === 'csv') {
            return $this->exportProductionCSV($data, $request);
        }

        return view('admin.reports.production-summary', compact('data', 'totals', 'years', 'municipalities', 'crops'));
    }

    /**
     * Generate Prediction Analytics Report
     */
    public function predictionAnalytics(Request $request)
    {
        $query = Prediction::with('user');

        // Apply filters
        if ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }
        if ($request->filled('municipality')) {
            $query->where('municipality', $request->municipality);
        }
        if ($request->filled('crop')) {
            $query->where('crop', $request->crop);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Get predictions data
        $predictions = $query->orderBy('created_at', 'desc')->paginate(50);

        // Get analytics
        $analytics = [
            'total' => Prediction::count(),
            'successful' => Prediction::where('status', 'success')->count(),
            'failed' => Prediction::where('status', 'failed')->count(),
            'success_rate' => Prediction::count() > 0 
                ? round((Prediction::where('status', 'success')->count() / Prediction::count()) * 100, 2)
                : 0,
            'avg_confidence' => Prediction::where('status', 'success')->avg('confidence_score'),
            'avg_response_time' => Prediction::avg('api_response_time_ms'),
            'total_predicted_production' => Prediction::where('status', 'success')->sum('predicted_production_mt'),
        ];

        // Top crops predicted
        $topCrops = Prediction::select('crop', DB::raw('COUNT(*) as count'))
            ->groupBy('crop')
            ->orderBy('count', 'desc')
            ->limit(10)
            ->get();

        // Top municipalities
        $topMunicipalities = Prediction::select('municipality', DB::raw('COUNT(*) as count'))
            ->groupBy('municipality')
            ->orderBy('count', 'desc')
            ->limit(10)
            ->get();

        // Active users
        $activeUsers = Prediction::select('user_id', DB::raw('COUNT(*) as count'))
            ->groupBy('user_id')
            ->orderBy('count', 'desc')
            ->limit(10)
            ->with('user')
            ->get();

        // Get filter options
        $municipalities = Prediction::distinct()->pluck('municipality')->sort()->values();
        $crops = Prediction::distinct()->pluck('crop')->sort()->values();

        if ($request->get('format') === 'pdf') {
            $pdf = Pdf::loadView('admin.reports.pdf.prediction-analytics', compact('predictions', 'analytics', 'topCrops', 'topMunicipalities'));
            return $pdf->download('prediction-analytics-' . now()->format('Y-m-d') . '.pdf');
        }

        if ($request->get('format') === 'csv') {
            return $this->exportPredictionsCSV($predictions, $request);
        }

        return view('admin.reports.prediction-analytics', compact(
            'predictions',
            'analytics',
            'topCrops',
            'topMunicipalities',
            'activeUsers',
            'municipalities',
            'crops'
        ));
    }

    /**
     * Generate Comparative Analysis Report
     */
    public function comparativeAnalysis(Request $request)
    {
        // Compare different municipalities, crops, or time periods
        $compareBy = $request->get('compare_by', 'municipality'); // municipality, crop, year

        $data = CropProduction::select(
            $compareBy,
            DB::raw('SUM(production) as total_production'),
            DB::raw('SUM(area_harvested) as total_area'),
            DB::raw('AVG(productivity) as avg_productivity'),
            DB::raw('COUNT(*) as record_count')
        )
        ->when($request->filled('year'), function($q) use ($request) {
            $q->where('year', $request->year);
        })
        ->when($request->filled('municipality') && $compareBy !== 'municipality', function($q) use ($request) {
            $q->where('municipality', $request->municipality);
        })
        ->when($request->filled('crop') && $compareBy !== 'crop', function($q) use ($request) {
            $q->where('crop', $request->crop);
        })
        ->groupBy($compareBy)
        ->orderBy('total_production', 'desc')
        ->get();

        // Get filter options
        $years = CropProduction::distinct()->pluck('year')->sort()->values();
        $municipalities = CropProduction::distinct()->pluck('municipality')->sort()->values();
        $crops = CropProduction::distinct()->pluck('crop')->sort()->values();

        if ($request->get('format') === 'pdf') {
            $pdf = Pdf::loadView('admin.reports.pdf.comparative-analysis', compact('data', 'compareBy', 'request'));
            return $pdf->download('comparative-analysis-' . now()->format('Y-m-d') . '.pdf');
        }

        return view('admin.reports.comparative-analysis', compact('data', 'compareBy', 'years', 'municipalities', 'crops'));
    }

    /**
     * Generate User Activity Report
     */
    public function userActivity(Request $request)
    {
        $predictionFilter = function ($q) use ($request) {
            $q->where('status', 'success');

            if ($request->filled('start_date')) {
                $q->whereDate('created_at', '>=', $request->start_date);
            }

            if ($request->filled('end_date')) {
                $q->whereDate('created_at', '<=', $request->end_date);
            }
        };

        $sessionTable = config('session.table', 'sessions');
        $sessionLifetimeSeconds = (int) config('session.lifetime', 120) * 60;
        $activeSinceTimestamp = now()->subSeconds($sessionLifetimeSeconds)->timestamp;

        $sessionActivitySubquery = function () use ($sessionTable) {
            return DB::table($sessionTable)
                ->select('user_id', DB::raw('MAX(last_activity) as last_activity'))
                ->whereNotNull('user_id')
                ->groupBy('user_id');
        };

        $query = User::where('role', 'farmer')
            ->leftJoinSub($sessionActivitySubquery(), 'session_activity', function ($join) {
                $join->on('users.id', '=', 'session_activity.user_id');
            })
            ->select('users.*')
            ->selectRaw('COALESCE(session_activity.last_activity, 0) as last_activity_timestamp')
            ->selectRaw('CASE WHEN COALESCE(session_activity.last_activity, 0) >= ? THEN 1 ELSE 0 END as is_active', [$activeSinceTimestamp])
            ->withCount(['predictions' => $predictionFilter]);

        if ($request->filled('start_date') || $request->filled('end_date')) {
            $query->whereHas('predictions', $predictionFilter);
        }

        $users = $query
            ->orderByDesc('is_active')
            ->orderBy('predictions_count', 'desc')
            ->paginate(20);

        // PostgreSQL cannot aggregate directly on withCount aliases via avg('predictions_count').
        // Compute it from the counted result set instead.
        $avgPredictionsPerUser = User::where('role', 'farmer')
            ->whereHas('predictions', $predictionFilter)
            ->withCount(['predictions' => $predictionFilter])
            ->get()
            ->avg('predictions_count') ?? 0;

        $totalPredictionsQuery = Prediction::where('status', 'success');
        if ($request->filled('start_date')) {
            $totalPredictionsQuery->whereDate('created_at', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $totalPredictionsQuery->whereDate('created_at', '<=', $request->end_date);
        }

        $activeFarmersCount = User::where('role', 'farmer')
            ->joinSub($sessionActivitySubquery(), 'session_activity', function ($join) {
                $join->on('users.id', '=', 'session_activity.user_id');
            })
            ->where('session_activity.last_activity', '>=', $activeSinceTimestamp)
            ->count();

        $stats = [
            'total_farmers' => User::where('role', 'farmer')->count(),
            'active_farmers' => $activeFarmersCount,
            'total_predictions' => $totalPredictionsQuery->count(),
            'avg_predictions_per_user' => $avgPredictionsPerUser,
        ];

        if ($request->get('format') === 'pdf') {
            $pdf = Pdf::loadView('admin.reports.pdf.user-activity', compact('users', 'stats'));
            return $pdf->download('user-activity-' . now()->format('Y-m-d') . '.pdf');
        }

        return view('admin.reports.user-activity', compact('users', 'stats'));
    }

    /**
     * Generate Planting Report from farmer calendar crop plans.
     */
    public function plantingReport(Request $request)
    {
        $records = $this->getPlantingReportRecords($request);
        $summary = $this->buildPlantingReportSummary($records);
        $filters = $this->getPlantingReportFilters();

        if ($request->get('format') === 'pdf') {
            $pdf = Pdf::loadView('admin.reports.pdf.planting-report', compact('records', 'summary', 'request'));

            return $pdf->download('planting-report-' . now()->format('Y-m-d') . '.pdf');
        }

        if ($request->get('format') === 'csv') {
            return $this->exportPlantingReportCSV($records);
        }

        $page = LengthAwarePaginator::resolveCurrentPage();
        $perPage = 15;
        $paginatedRecords = new LengthAwarePaginator(
            $records->forPage($page, $perPage)->values(),
            $records->count(),
            $perPage,
            $page,
            [
                'path' => $request->url(),
                'query' => $request->query(),
            ]
        );

        return view('admin.reports.planting-report', compact('paginatedRecords', 'summary', 'filters'));
    }

    private function getPlantingReportRecords(Request $request): Collection
    {
        $damageTotals = FarmerCalendarEvent::query()
            ->select('crop_plan_event_id', DB::raw('SUM(COALESCE(damage_area_sqm, 0)) as reported_damage_sqm'))
            ->where('category', 'damage_report')
            ->whereNotNull('crop_plan_event_id')
            ->groupBy('crop_plan_event_id');

        $query = FarmerCalendarEvent::query()
            ->from('farmer_calendar_events as plans')
            ->join('users', 'users.id', '=', 'plans.user_id')
            ->leftJoinSub($damageTotals, 'damage_totals', function ($join) {
                $join->on('damage_totals.crop_plan_event_id', '=', 'plans.id');
            })
            ->where('plans.category', 'crop_plan')
            ->select([
                'plans.id',
                'plans.user_id',
                'plans.event_date',
                'plans.title',
                'plans.description',
                'plans.crop',
                'plans.desired_area_sqm',
                'plans.water_source',
                'plans.planting_material',
                'plans.estimated_harvest_date',
                'plans.predicted_production_mt',
                'plans.is_completed',
                'plans.created_at',
                'users.name as farmer_name',
                'users.email as farmer_email',
                'users.preferred_municipality',
                'users.cooperative',
                DB::raw('COALESCE(damage_totals.reported_damage_sqm, 0) as reported_damage_sqm'),
            ]);

        if ($request->filled('crop')) {
            $query->where('plans.crop', $request->crop);
        }

        if ($request->filled('municipality')) {
            $query->where('users.preferred_municipality', $request->municipality);
        }

        if ($request->filled('farm_type')) {
            $query->where('plans.water_source', $request->farm_type);
        }

        if ($request->filled('planting_month')) {
            $query->whereMonth('plans.event_date', (int) $request->planting_month);
        }

        if ($request->filled('planting_year')) {
            $query->whereYear('plans.event_date', (int) $request->planting_year);
        }

        if ($request->filled('harvest_month')) {
            $query->whereMonth('plans.estimated_harvest_date', (int) $request->harvest_month);
        }

        if ($request->filled('harvest_year')) {
            $query->whereYear('plans.estimated_harvest_date', (int) $request->harvest_year);
        }

        if ($request->filled('search')) {
            $search = trim((string) $request->search);
            $searchLike = '%' . strtolower($search) . '%';
            $query->where(function ($q) use ($search, $searchLike) {
                $q->whereRaw('LOWER(users.name) LIKE ?', [$searchLike])
                    ->orWhereRaw('LOWER(users.email) LIKE ?', [$searchLike])
                    ->orWhereRaw('LOWER(users.preferred_municipality) LIKE ?', [$searchLike])
                    ->orWhereRaw('LOWER(users.cooperative) LIKE ?', [$searchLike])
                    ->orWhereRaw('LOWER(plans.crop) LIKE ?', [$searchLike]);

                if (ctype_digit($search)) {
                    $q->orWhere('plans.id', (int) $search)
                        ->orWhere('plans.user_id', (int) $search);
                }
            });
        }

        $rows = $query
            ->orderByDesc('plans.event_date')
            ->orderByDesc('plans.created_at')
            ->get();

        $planIds = $rows->pluck('id')->filter()->values();
        $damageReports = $planIds->isNotEmpty()
            ? FarmerCalendarEvent::whereIn('crop_plan_event_id', $planIds)
                ->where('category', 'damage_report')
                ->orderByDesc('event_date')
                ->orderByDesc('created_at')
                ->get()
                ->groupBy('crop_plan_event_id')
            : collect();

        $records = $rows->map(function ($row) use ($damageReports) {
            $areaSqm = (float) ($row->desired_area_sqm ?? 0);
            $reportedDamageSqm = min($areaSqm, max(0, (float) ($row->reported_damage_sqm ?? 0)));
            $healthyRatio = $areaSqm > 0 ? max(0, min(1, ($areaSqm - $reportedDamageSqm) / $areaSqm)) : 1;
            $originalProduction = max(0, (float) ($row->predicted_production_mt ?? 0));
            $adjustedProduction = round($originalProduction * $healthyRatio, 2);
            $lossProduction = max(0, round($originalProduction - $adjustedProduction, 2));
            $plantingDate = $row->event_date ? Carbon::parse($row->event_date) : null;
            $harvestDate = $row->estimated_harvest_date ? Carbon::parse($row->estimated_harvest_date) : null;
            $latestDamage = $damageReports->get($row->id, collect())->first();
            $status = $this->resolvePlantingReportStatus($row, $reportedDamageSqm, $harvestDate);

            return [
                'id' => (int) $row->id,
                'farmer_id' => 'FARM-' . str_pad((string) $row->user_id, 6, '0', STR_PAD_LEFT),
                'farmer_name' => $row->farmer_name ?: 'Unknown Farmer',
                'farmer_email' => $row->farmer_email,
                'municipality' => $this->formatReportLabel($row->preferred_municipality),
                'cooperative' => $row->cooperative ?: null,
                'crop' => $this->formatReportLabel($row->crop),
                'notes' => $row->description ?: 'None',
                'planting_date' => $plantingDate,
                'harvest_date' => $harvestDate,
                'area_sqm' => round($areaSqm, 2),
                'area_ha' => round($areaSqm / 10000, 4),
                'damage_sqm' => round($reportedDamageSqm, 2),
                'damage_ha' => round($reportedDamageSqm / 10000, 4),
                'original_production_mt' => round($originalProduction, 2),
                'adjusted_production_mt' => $adjustedProduction,
                'loss_production_mt' => $lossProduction,
                'farm_type' => $this->formatReportLabel($row->water_source),
                'seed_type' => $this->formatReportLabel($row->planting_material),
                'status' => $status,
                'status_label' => $this->formatReportLabel($status),
                'damage_title' => $latestDamage?->title,
                'damage_description' => $latestDamage?->description,
                'damage_date' => $latestDamage?->event_date ? Carbon::parse($latestDamage->event_date) : null,
                'damage_reported_at' => $latestDamage?->created_at ? Carbon::parse($latestDamage->created_at) : null,
                'recorded_at' => $row->created_at ? Carbon::parse($row->created_at) : null,
            ];
        });

        if ($request->filled('status')) {
            $records = $records->where('status', $request->status)->values();
        }

        return $records->values();
    }

    private function resolvePlantingReportStatus($row, float $reportedDamageSqm, ?Carbon $harvestDate): string
    {
        if ($reportedDamageSqm > 0) {
            return 'damaged';
        }

        if ((bool) $row->is_completed || ($harvestDate && $harvestDate->isPast())) {
            return 'harvested';
        }

        return 'planted';
    }

    private function buildPlantingReportSummary(Collection $records): array
    {
        $totalAreaHa = round($records->sum('area_ha'), 2);
        $damageAreaHa = round($records->sum('damage_ha'), 2);
        $healthyAreaHa = max(0, round($totalAreaHa - $damageAreaHa, 2));
        $originalProduction = round($records->sum('original_production_mt'), 2);
        $adjustedProduction = round($records->sum('adjusted_production_mt'), 2);
        $lossProduction = max(0, round($originalProduction - $adjustedProduction, 2));
        $cropBreakdown = $records
            ->groupBy('crop')
            ->map(function (Collection $cropRecords, string $crop) {
                return [
                    'crop' => $crop,
                    'records' => $cropRecords->count(),
                    'area_ha' => round($cropRecords->sum('area_ha'), 2),
                    'production_mt' => round($cropRecords->sum('adjusted_production_mt'), 2),
                ];
            })
            ->sortByDesc('records')
            ->values();

        $maxCropRecords = max(1, (int) ($cropBreakdown->max('records') ?? 1));

        return [
            'total_records' => $records->count(),
            'planted_records' => $records->where('status', 'planted')->count() + $records->where('status', 'harvested')->count(),
            'damaged_records' => $records->where('status', 'damaged')->count(),
            'harvested_records' => $records->where('status', 'harvested')->count(),
            'total_area_ha' => $totalAreaHa,
            'healthy_area_ha' => $healthyAreaHa,
            'damage_area_ha' => $damageAreaHa,
            'healthy_area_percent' => $totalAreaHa > 0 ? round(($healthyAreaHa / $totalAreaHa) * 100, 1) : 0,
            'original_production_mt' => $originalProduction,
            'adjusted_production_mt' => $adjustedProduction,
            'loss_production_mt' => $lossProduction,
            'loss_percent' => $originalProduction > 0 ? round(($lossProduction / $originalProduction) * 100, 1) : 0,
            'crop_breakdown' => $cropBreakdown,
            'crop_types' => $cropBreakdown->count(),
            'max_crop_records' => $maxCropRecords,
        ];
    }

    private function getPlantingReportFilters(): array
    {
        $plans = FarmerCalendarEvent::where('category', 'crop_plan');

        $years = FarmerCalendarEvent::where('category', 'crop_plan')
            ->get(['event_date', 'estimated_harvest_date'])
            ->flatMap(function (FarmerCalendarEvent $event) {
                return [
                    optional($event->event_date)->year,
                    optional($event->estimated_harvest_date)->year,
                ];
            })
            ->filter()
            ->unique()
            ->sort()
            ->values();

        return [
            'crops' => (clone $plans)->whereNotNull('crop')->distinct()->orderBy('crop')->pluck('crop'),
            'municipalities' => User::where('role', 'farmer')
                ->whereNotNull('preferred_municipality')
                ->distinct()
                ->orderBy('preferred_municipality')
                ->pluck('preferred_municipality'),
            'farm_types' => (clone $plans)->whereNotNull('water_source')->distinct()->orderBy('water_source')->pluck('water_source'),
            'years' => $years,
            'months' => collect(range(1, 12))->mapWithKeys(fn ($month) => [$month => Carbon::create(null, $month, 1)->format('F')]),
            'statuses' => [
                'planted' => 'Planted',
                'damaged' => 'Damaged',
                'harvested' => 'Harvested',
            ],
        ];
    }

    private function formatReportLabel(?string $value): string
    {
        $value = trim((string) $value);

        return $value === '' ? '-' : ucwords(strtolower(str_replace('_', ' ', $value)));
    }

    /**
     * Export production data to CSV
     */
    private function exportProductionCSV($data, $request)
    {
        $filename = 'production-summary-' . now()->format('Y-m-d') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function() use ($data, $request) {
            $file = fopen('php://output', 'w');
            
            // Add header row
            fputcsv($file, ['Municipality', 'Crop', 'Total Production (MT)', 'Total Area (Ha)', 'Avg Productivity (MT/Ha)', 'Records']);
            
            // Add data rows
            foreach ($data as $row) {
                fputcsv($file, [
                    $row->municipality,
                    $row->crop,
                    number_format($row->total_production, 2),
                    number_format($row->total_area, 2),
                    number_format($row->avg_productivity, 2),
                    $row->record_count,
                ]);
            }
            
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    private function exportPlantingReportCSV(Collection $records)
    {
        $filename = 'planting-report-' . now()->format('Y-m-d') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function () use ($records) {
            $file = fopen('php://output', 'w');

            fputcsv($file, [
                'Farmer',
                'Farmer ID',
                'Municipality',
                'Cooperative',
                'Crop',
                'Planting Date',
                'Harvest Date',
                'Area (Ha)',
                'Original Production (MT)',
                'Adjusted Production (MT)',
                'Damage Area (Ha)',
                'Production Loss (MT)',
                'Farm Type',
                'Seed Type',
                'Status',
                'Damage Details',
                'Recorded At',
            ]);

            foreach ($records as $record) {
                fputcsv($file, [
                    $record['farmer_name'],
                    $record['farmer_id'],
                    $record['municipality'],
                    $record['cooperative'] ?? '',
                    $record['crop'],
                    $record['planting_date']?->format('Y-m-d'),
                    $record['harvest_date']?->format('Y-m-d'),
                    number_format($record['area_ha'], 4),
                    number_format($record['original_production_mt'], 2),
                    number_format($record['adjusted_production_mt'], 2),
                    number_format($record['damage_ha'], 4),
                    number_format($record['loss_production_mt'], 2),
                    $record['farm_type'],
                    $record['seed_type'],
                    $record['status_label'],
                    $record['damage_title'] ?: '',
                    $record['recorded_at']?->format('Y-m-d H:i'),
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Export predictions to CSV
     */
    private function exportPredictionsCSV($predictions, $request)
    {
        $filename = 'predictions-' . now()->format('Y-m-d') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function() use ($predictions) {
            $file = fopen('php://output', 'w');
            
            // Add header row
            fputcsv($file, [
                'Date', 'User', 'Municipality', 'Crop', 'Farm Type', 'Year', 'Month',
                'Area Planted', 'Predicted Production', 'Confidence Score', 'Status'
            ]);
            
            // Add data rows
            foreach ($predictions as $pred) {
                fputcsv($file, [
                    $pred->created_at->format('Y-m-d H:i'),
                    $pred->user->name ?? 'N/A',
                    $pred->municipality,
                    $pred->crop,
                    $pred->farm_type,
                    $pred->year,
                    $pred->month,
                    number_format($pred->area_planted_ha, 2),
                    number_format($pred->predicted_production_mt, 2),
                    number_format($pred->confidence_score ?? 0, 4),
                    $pred->status,
                ]);
            }
            
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
