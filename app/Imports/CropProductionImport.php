<?php

namespace App\Imports;

use App\Models\CropProduction;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithUpserts;
use Illuminate\Contracts\Queue\ShouldQueue;

class CropProductionImport implements ToModel, WithHeadingRow, WithBatchInserts, WithChunkReading
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        // Skip empty rows
        if (empty($row['municipality'])) {
            return null;
        }

        return new CropProduction([
            'municipality' => strtoupper(trim($row['municipality'])),
            'farm_type' => strtoupper(trim($row['farm_type'])),
            'year' => (int) $row['year'],
            'month' => strtoupper(trim($row['month'])),
            'crop' => strtoupper(trim($row['crop'])),
            'area_planted' => !empty($row['area_plantedha']) ? (float) $row['area_plantedha'] : null,
            'area_harvested' => !empty($row['area_harvestedha']) ? (float) $row['area_harvestedha'] : null,
            'production' => !empty($row['productionmt']) ? (float) $row['productionmt'] : null,
            'productivity' => !empty($row['productivitymtha']) ? (float) $row['productivitymtha'] : null,
        ]);
    }

    public function batchSize(): int
    {
        return 500; // Insert 500 rows at a time
    }

    public function chunkSize(): int
    {
        return 500; // Read 500 rows at a time for better memory management
    }
}
