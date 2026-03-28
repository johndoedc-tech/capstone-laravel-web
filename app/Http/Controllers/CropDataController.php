<?php

namespace App\Http\Controllers;

use App\Models\CropProduction;
use App\Imports\CropProductionImport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class CropDataController extends Controller
{
    /**
     * Display the crop data management page
     */
    public function index(Request $request)
    {
        $query = CropProduction::query();

        // Apply filters
        if ($request->filled('municipality')) {
            $query->where('municipality', $request->municipality);
        }

        if ($request->filled('crop')) {
            $query->where('crop', $request->crop);
        }

        if ($request->filled('year')) {
            $query->where('year', $request->year);
        }

        if ($request->filled('month')) {
            $query->where('month', $request->month);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('municipality', 'like', "%{$search}%")
                  ->orWhere('crop', 'like', "%{$search}%");
            });
        }

        // Get paginated results
        $cropData = $query->orderBy('year', 'desc')
                         ->orderBy('month', 'desc')
                         ->paginate(20);

        // Get filter options
        $municipalities = CropProduction::distinct()->pluck('municipality')->sort()->values();
        $crops = CropProduction::distinct()->pluck('crop')->sort()->values();
        $years = CropProduction::distinct()->pluck('year')->sort()->values();
        
        // Get statistics
        $totalRecords = CropProduction::count();
        $municipalitiesCount = CropProduction::distinct('municipality')->count();
        $cropTypesCount = CropProduction::distinct('crop')->count();
        $minYear = CropProduction::min('year');
        $maxYear = CropProduction::max('year');

        return view('admin.crop-data.index', compact(
            'cropData',
            'municipalities',
            'crops',
            'years',
            'totalRecords',
            'municipalitiesCount',
            'cropTypesCount',
            'minYear',
            'maxYear'
        ));
    }

    /**
     * Import CSV file
     */
    public function import(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt|max:10240', // Max 10MB
        ]);

        try {
            // Increase execution time and memory limit for large imports
            set_time_limit(600); // 10 minutes
            ini_set('memory_limit', '1024M'); // 1GB
            ini_set('max_input_time', '600');

            $file = $request->file('csv_file');
            
            // Optional: Clear existing data
            if ($request->has('clear_existing')) {
                DB::table('crop_production')->truncate();
            }

            // Use fast import method
            $this->fastImport($file->getPathname());

            $count = CropProduction::count();

            return redirect()->back()->with('success', "Successfully imported! Total records: {$count}");
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Import failed: ' . $e->getMessage());
        }
    }

    /**
     * Fast import using direct database inserts
     */
    private function fastImport($filePath)
    {
        $handle = fopen($filePath, 'r');
        
        if ($handle === false) {
            throw new \Exception('Unable to open file');
        }

        // Read header row
        $header = fgetcsv($handle);
        
        // Map header to lowercase for case-insensitive matching
        $header = array_map('strtolower', $header);
        
        $batch = [];
        $batchSize = 500;
        $now = now();

        while (($row = fgetcsv($handle)) !== false) {
            // Create associative array from header and row
            $data = array_combine($header, $row);
            
            // Skip empty rows
            if (empty($data['municipality'])) {
                continue;
            }

            $batch[] = [
                'municipality' => strtoupper(trim($data['municipality'])),
                'farm_type' => strtoupper(trim($data['farm type'])),
                'year' => (int) $data['year'],
                'month' => strtoupper(trim($data['month'])),
                'crop' => strtoupper(trim($data['crop'])),
                'area_planted' => !empty($data['area planted(ha)']) ? (float) $data['area planted(ha)'] : null,
                'area_harvested' => !empty($data['area harvested(ha)']) ? (float) $data['area harvested(ha)'] : null,
                'production' => !empty($data['production(mt)']) ? (float) $data['production(mt)'] : null,
                'productivity' => !empty($data['productivity(mt/ha)']) ? (float) $data['productivity(mt/ha)'] : null,
                'created_at' => $now,
                'updated_at' => $now,
            ];

            // Insert when batch is full
            if (count($batch) >= $batchSize) {
                DB::table('crop_production')->insert($batch);
                $batch = [];
            }
        }

        // Insert remaining records
        if (!empty($batch)) {
            DB::table('crop_production')->insert($batch);
        }

        fclose($handle);
    }

    /**
     * Add single crop data
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'municipality' => 'required|string|max:100',
            'farm_type' => 'required|string|in:IRRIGATED,RAINFED',
            'year' => 'required|integer|min:2000|max:2100',
            'month' => 'required|string|max:20',
            'crop' => 'required|string|max:100',
            'area_planted' => 'nullable|numeric|min:0',
            'area_harvested' => 'nullable|numeric|min:0',
            'production' => 'nullable|numeric|min:0',
            'productivity' => 'nullable|numeric|min:0',
        ]);

        $validated['municipality'] = strtoupper($validated['municipality']);
        $validated['farm_type'] = strtoupper($validated['farm_type']);
        $validated['month'] = strtoupper($validated['month']);
        $validated['crop'] = strtoupper($validated['crop']);

        CropProduction::create($validated);

        return redirect()->back()->with('success', 'Crop data added successfully!');
    }

    /**
     * Delete crop data
     */
    public function destroy($id)
    {
        $cropData = CropProduction::findOrFail($id);
        $cropData->delete();

        return redirect()->back()->with('success', 'Crop data deleted successfully!');
    }

    /**
     * Delete all crop data
     */
    public function deleteAll()
    {
        DB::table('crop_production')->truncate();
        
        return redirect()->back()->with('success', 'All crop data deleted successfully!');
    }

    /**
     * Get statistics for dashboard
     */
    public function getStatistics()
    {
        return response()->json([
            'total_records' => CropProduction::count(),
            'municipalities' => CropProduction::distinct('municipality')->count(),
            'crop_types' => CropProduction::distinct('crop')->count(),
            'years_covered' => [
                'min' => CropProduction::min('year'),
                'max' => CropProduction::max('year')
            ],
            'total_production' => CropProduction::sum('production'),
            'total_area' => CropProduction::sum('area_harvested'),
        ]);
    }
}
