<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\MapDataController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Map API Routes (Public - no authentication required)
Route::prefix('map')->name('api.map.')->group(function () {
    // Main map data with filters
    Route::get('/data', [MapDataController::class, 'getMapData'])->name('data');
    
    // Filter options for dropdowns
    Route::get('/filters', [MapDataController::class, 'getFilterOptions'])->name('filters');
    
    // Municipality details
    Route::get('/municipality/{municipality}', [MapDataController::class, 'getMunicipalityDetails'])->name('municipality');
    
    // Timeline data for animation
    Route::get('/timeline', [MapDataController::class, 'getTimelineData'])->name('timeline');
    
    // Comparison between municipalities
    Route::get('/compare', [MapDataController::class, 'compareData'])->name('compare');
    
    // Statistics summary
    Route::get('/statistics', [MapDataController::class, 'getStatistics'])->name('statistics');
});
