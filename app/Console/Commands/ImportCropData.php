<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Imports\CropProductionImport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\DB;

class ImportCropData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:crop-data {file?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import crop production data from CSV file';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $file = $this->argument('file') ?? 'c:\xampp\htdocs\capstone\fulldataset.csv';
        
        if (!file_exists($file)) {
            $this->error("File not found: {$file}");
            $this->info("Usage: php artisan import:crop-data [file_path]");
            return 1;
        }
        
        $this->info('Starting import from: ' . $file);
        
        // Clear existing data (optional - comment out if you want to keep existing data)
        if ($this->confirm('Do you want to clear existing crop_production data before importing?', true)) {
            DB::table('crop_production')->truncate();
            $this->info('Existing data cleared.');
        }
        
        $startTime = microtime(true);
        
        try {
            Excel::import(new CropProductionImport, $file);
            
            $endTime = microtime(true);
            $duration = round($endTime - $startTime, 2);
            
            $count = DB::table('crop_production')->count();
            
            $this->info("✅ Import completed successfully!");
            $this->info("📊 Total records imported: {$count}");
            $this->info("⏱️  Time taken: {$duration} seconds");
            
            return 0;
        } catch (\Exception $e) {
            $this->error("❌ Import failed: " . $e->getMessage());
            return 1;
        }
    }
}
