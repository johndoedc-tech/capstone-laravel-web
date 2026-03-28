<?php

namespace App\Jobs;

use App\Models\Prediction;
use App\Services\CropPredictionService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class ProcessBatchPrediction implements ShouldQueue
{
    use Queueable;

    public $timeout = 300; // 5 minutes
    public $tries = 3;

    protected $userId;
    protected $predictions;

    /**
     * Create a new job instance.
     */
    public function __construct($userId, array $predictions)
    {
        $this->userId = $userId;
        $this->predictions = $predictions;
    }

    /**
     * Execute the job.
     */
    public function handle(CropPredictionService $predictionService): void
    {
        Log::info('Processing batch prediction', [
            'user_id' => $this->userId,
            'count' => count($this->predictions)
        ]);

        foreach ($this->predictions as $index => $predictionData) {
            try {
                // Make prediction via ML API
                $result = $predictionService->predict($predictionData);
                $prediction = $result['prediction'] ?? $result;

                // Save to database
                Prediction::create([
                    'user_id' => $this->userId,
                    'municipality' => $predictionData['municipality'],
                    'farm_type' => $predictionData['farm_type'],
                    'year' => $predictionData['year'],
                    'month' => $predictionData['month'],
                    'crop' => $predictionData['crop'],
                    'area_planted_ha' => $predictionData['area_planted'],
                    'area_harvested_ha' => $predictionData['area_harvested'],
                    'productivity_mt_ha' => $predictionData['productivity'],
                    'predicted_production_mt' => $prediction['production_mt'] ?? $prediction['Production_mt'] ?? 0,
                    'expected_from_productivity' => $prediction['expected_from_productivity'] ?? $prediction['Expected_from_Productivity'] ?? 0,
                    'difference' => $prediction['difference'] ?? $prediction['Difference'] ?? 0,
                    'confidence_score' => $prediction['confidence_score'] ?? $prediction['Confidence_Score'] ?? 0,
                    'api_response_time_ms' => $result['api_response_time_ms'] ?? null,
                    'status' => 'success'
                ]);

                $predictionNumber = $index + 1;
                Log::info("Batch prediction {$predictionNumber} completed successfully");

            } catch (\Exception $e) {
                $predictionNumber = $index + 1;
                Log::error("Batch prediction {$predictionNumber} failed", [
                    'error' => $e->getMessage(),
                    'data' => $predictionData
                ]);

                // Save failed prediction
                try {
                    Prediction::create([
                        'user_id' => $this->userId,
                        'municipality' => $predictionData['municipality'],
                        'farm_type' => $predictionData['farm_type'],
                        'year' => $predictionData['year'],
                        'month' => $predictionData['month'],
                        'crop' => $predictionData['crop'],
                        'area_planted_ha' => $predictionData['area_planted'],
                        'area_harvested_ha' => $predictionData['area_harvested'],
                        'productivity_mt_ha' => $predictionData['productivity'],
                        'predicted_production_mt' => 0,
                        'expected_from_productivity' => 0,
                        'difference' => 0,
                        'confidence_score' => 0,
                        'status' => 'failed',
                        'error_message' => $e->getMessage()
                    ]);
                } catch (\Exception $dbError) {
                    Log::error('Failed to save batch prediction error', [
                        'error' => $dbError->getMessage()
                    ]);
                }
            }
        }

        Log::info('Batch prediction processing completed', [
            'user_id' => $this->userId,
            'total_predictions' => count($this->predictions)
        ]);
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('Batch prediction job failed completely', [
            'user_id' => $this->userId,
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString()
        ]);
    }
}
