<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Exception;

class CropPredictionService
{
    protected $apiUrl;
    protected $timeout = 30; // API timeout in seconds
    
    public function __construct()
    {
        // API URL from config or env
        $this->apiUrl = config('services.ml_api.url', 'http://127.0.0.1:5000');
    }
    
    /**
     * Check if the ML API is healthy
     */
    public function healthCheck()
    {
        try {
            $startTime = microtime(true);
            $response = Http::timeout(5)->get("{$this->apiUrl}/api/health");
            $responseTime = (microtime(true) - $startTime) * 1000; // Convert to milliseconds
            
            Log::info('ML API Health Check', [
                'status' => $response->successful() ? 'success' : 'failed',
                'response_time_ms' => round($responseTime, 2)
            ]);
            
            return $response->successful() ? $response->json() : null;
        } catch (Exception $e) {
            Log::error('ML API Health Check Failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return null;
        }
    }
    
    /**
     * Get available options for the form (with caching)
     */
    public function getAvailableOptions()
    {
        return Cache::remember('ml_api_available_options', 3600, function () {
            try {
                $startTime = microtime(true);
                $response = Http::timeout($this->timeout)->get("{$this->apiUrl}/api/available-options");
                $responseTime = (microtime(true) - $startTime) * 1000;
                
                if ($response->successful()) {
                    $data = $response->json();
                    
                    Log::info('ML API Available Options Fetched', [
                        'response_time_ms' => round($responseTime, 2),
                        'cached' => false
                    ]);
                    
                    return $data;
                }
                
                throw new Exception('Failed to fetch available options: ' . $response->status());
            } catch (Exception $e) {
                Log::error('Failed to get available options', [
                    'error' => $e->getMessage(),
                    'url' => "{$this->apiUrl}/api/available-options"
                ]);
                throw $e;
            }
        });
    }
    
    /**
     * Make a single prediction
     * 
     * @param array $data
     * @return array
     */
    public function predict(array $data)
    {
        // Convert month number to month name
        $monthMap = [
            1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April',
            5 => 'May', 6 => 'June', 7 => 'July', 8 => 'August',
            9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December'
        ];
        
        $monthValue = is_numeric($data['month']) 
            ? ($monthMap[(int)$data['month']] ?? $data['month'])
            : $data['month'];
        
        // NEW MODEL (68.17% accuracy): Only 6 features needed
        // Municipality name normalization: "LA TRINIDAD" -> "LATRINIDAD"
        $normalizedMunicipality = strtoupper(str_replace(' ', '', $data['municipality']));
        
        $requestData = [
            'MUNICIPALITY' => $normalizedMunicipality,
            'FARM_TYPE' => strtoupper($data['farm_type']),
            'YEAR' => (int)$data['year'],
            'MONTH' => $monthValue,
            'CROP' => strtoupper($data['crop']),
            'Area_planted_ha' => (float)$data['area_planted']
        ];
        
        try {
            $startTime = microtime(true);
            
            Log::info('ML API Prediction Request', [
                'raw_input' => $data,
                'formatted_request' => $requestData
            ]);
            
            $response = Http::timeout($this->timeout)->post("{$this->apiUrl}/api/predict", $requestData);
            
            $responseTime = (microtime(true) - $startTime) * 1000;
            
            if ($response->successful()) {
                $result = $response->json();
                
                // Normalize new API format: production_mt -> predicted_production
                if (isset($result['prediction']['production_mt']) && !isset($result['prediction']['predicted_production'])) {
                    $result['prediction']['predicted_production'] = $result['prediction']['production_mt'];
                }
                
                Log::info('ML API Prediction Success', [
                    'response_time_ms' => round($responseTime, 2),
                    'prediction' => $result['prediction'] ?? 'N/A'
                ]);
                
                // Add response time to result
                $result['api_response_time_ms'] = round($responseTime);
                
                return $result;
            }
            
            $error = $response->json()['error'] ?? 'Unknown error occurred';
            
            Log::error('ML API Prediction Failed', [
                'status' => $response->status(),
                'error' => $error,
                'response_time_ms' => round($responseTime, 2)
            ]);
            
            throw new Exception($error);
            
        } catch (Exception $e) {
            Log::error('Prediction Exception', [
                'error' => $e->getMessage(),
                'input' => $requestData,
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }
    
    /**
     * Make batch predictions
     * 
     * @param array $predictions
     * @return array
     */
    public function batchPredict(array $predictions)
    {
        try {
            $formattedData = array_map(function($item) {
                return [
                    'MUNICIPALITY' => $item['municipality'],
                    'FARM_TYPE' => $item['farm_type'],
                    'YEAR' => $item['year'],
                    'MONTH' => $item['month'],
                    'CROP' => $item['crop'],
                    'Area_planted_ha' => $item['area_planted'],
                    'Area_harvested_ha' => $item['area_harvested'],
                    'Productivity_mt_ha' => $item['productivity']
                ];
            }, $predictions);
            
            $response = Http::post("{$this->apiUrl}/api/batch-predict", [
                'predictions' => $formattedData
            ]);
            
            if ($response->successful()) {
                return $response->json();
            }
            
            throw new Exception('Batch prediction failed');
            
        } catch (Exception $e) {
            Log::error('Batch prediction failed: ' . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Get model information and metadata
     */
    public function getModelInfo()
    {
        try {
            $response = Http::get("{$this->apiUrl}/api/model-info");
            
            if ($response->successful()) {
                return $response->json();
            }
            
            throw new Exception('Failed to fetch model info');
        } catch (Exception $e) {
            Log::error('Failed to get model info: ' . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Generate multi-year forecast
     * 
     * @param array $data
     * @return array
     */
    public function forecast(array $data)
    {
        // Python API only needs CROP and MUNICIPALITY
        // It returns pre-generated forecasts (doesn't use FARM_TYPE or FORECAST_YEARS)
        $requestData = [
            'CROP' => strtoupper($data['crop']),
            'MUNICIPALITY' => strtoupper($data['municipality'])
        ];
        
        try {
            $startTime = microtime(true);
            
            Log::info('ML API Forecast Request', [
                'request' => $requestData,
                'requested_years' => $data['forecast_years'] ?? 'default'
            ]);
            
            $response = Http::timeout($this->timeout)->post("{$this->apiUrl}/api/forecast", $requestData);
            
            $responseTime = (microtime(true) - $startTime) * 1000;
            
            if ($response->successful()) {
                $result = $response->json();
                
                // Filter forecast data to requested number of years
                $requestedYears = $data['forecast_years'] ?? 5;
                if (isset($result['forecast']) && is_array($result['forecast'])) {
                    $result['forecast'] = array_slice($result['forecast'], 0, $requestedYears);
                }
                
                // Add metadata
                $result['metadata'] = array_merge($result['metadata'] ?? [], [
                    'requested_years' => $requestedYears,
                    'farm_type' => $data['farm_type'] ?? 'All',
                    'api_response_time_ms' => round($responseTime)
                ]);
                
                Log::info('ML API Forecast Success', [
                    'response_time_ms' => round($responseTime, 2),
                    'forecast_count' => count($result['forecast'] ?? []),
                    'years_filtered' => $requestedYears
                ]);
                
                return $result;
            }
            
            $error = $response->json()['error'] ?? 'Forecast generation failed';
            
            Log::error('ML API Forecast Failed', [
                'status' => $response->status(),
                'error' => $error,
                'response_time_ms' => round($responseTime, 2)
            ]);
            
            throw new Exception($error);
            
        } catch (Exception $e) {
            Log::error('Forecast Exception', [
                'error' => $e->getMessage(),
                'input' => $requestData,
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }
}