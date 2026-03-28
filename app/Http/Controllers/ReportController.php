<?php

namespace App\Http\Controllers;

use App\Models\CropProduction;
use App\Models\Prediction;
use App\Models\User;
use Illuminate\Http\Request;
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

        // Recent reports activity
        $recentPredictions = Prediction::with('user')
            ->where('status', 'success')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        return view('admin.reports.index', compact('stats', 'recentPredictions'));
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
        $query = User::where('role', 'farmer')
            ->withCount(['predictions' => function($q) {
                $q->where('status', 'success');
            }]);

        if ($request->filled('start_date')) {
            $query->whereHas('predictions', function($q) use ($request) {
                $q->whereDate('created_at', '>=', $request->start_date);
            });
        }

        $users = $query->orderBy('predictions_count', 'desc')->paginate(20);

        $stats = [
            'total_farmers' => User::where('role', 'farmer')->count(),
            'active_farmers' => User::where('role', 'farmer')->has('predictions')->count(),
            'total_predictions' => Prediction::count(),
            'avg_predictions_per_user' => User::where('role', 'farmer')->has('predictions')->withCount('predictions')->avg('predictions_count'),
        ];

        if ($request->get('format') === 'pdf') {
            $pdf = Pdf::loadView('admin.reports.pdf.user-activity', compact('users', 'stats'));
            return $pdf->download('user-activity-' . now()->format('Y-m-d') . '.pdf');
        }

        return view('admin.reports.user-activity', compact('users', 'stats'));
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
