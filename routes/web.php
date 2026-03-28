<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CropPredictionController;
use App\Http\Controllers\CropDataController;
use App\Http\Controllers\MapController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\FarmerDashboardController;
use App\Http\Controllers\FarmerCalendarController;
use App\Http\Controllers\ForumController;
use App\Models\CropProduction;
use App\Models\Prediction;

Route::get('/', function () {
    return view('welcome');
})->name('welcome');

// Enhanced Farmer Dashboard
Route::get('/dashboard', [FarmerDashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    
    // Farmer Dashboard API endpoints
    Route::prefix('farmer')->name('farmer.')->group(function () {
        Route::post('/preferences', [FarmerDashboardController::class, 'savePreferences'])->name('preferences.save');
        Route::get('/recommendations', [FarmerDashboardController::class, 'getRecommendations'])->name('recommendations');
        Route::get('/compare-crops', [FarmerDashboardController::class, 'compareCrops'])->name('compare');
        Route::get('/calendar', [FarmerDashboardController::class, 'getCalendarData'])->name('calendar');
        Route::get('/scenario', [FarmerDashboardController::class, 'calculateScenario'])->name('scenario');
        Route::get('/options', [FarmerDashboardController::class, 'getOptions'])->name('options');
        
        // Farmer Calendar Page
        Route::get('/my-calendar', [FarmerCalendarController::class, 'index'])->name('calendar.page');
        
        // Farmer Calendar Events API
        Route::get('/calendar-events', [FarmerCalendarController::class, 'getEvents'])->name('calendar.events');
        Route::post('/calendar-events', [FarmerCalendarController::class, 'store'])->name('calendar.store');
        Route::put('/calendar-events/{id}', [FarmerCalendarController::class, 'update'])->name('calendar.update');
        Route::delete('/calendar-events/{id}', [FarmerCalendarController::class, 'destroy'])->name('calendar.destroy');
        Route::post('/calendar-events/{id}/delete', [FarmerCalendarController::class, 'destroy'])->name('calendar.delete');
        Route::post('/calendar-events/{id}/toggle', [FarmerCalendarController::class, 'toggleComplete'])->name('calendar.toggle');
        Route::get('/reminders/today', [FarmerCalendarController::class, 'getTodayReminders'])->name('reminders.today');
        Route::get('/reminders/upcoming', [FarmerCalendarController::class, 'getUpcomingReminders'])->name('reminders.upcoming');
    });

    // Predictions routes (requires authentication)
    Route::prefix('predictions')->group(function () {
        Route::get('/', [CropPredictionController::class, 'index'])->name('predictions.index');
        Route::get('/predict', [CropPredictionController::class, 'index'])->name('predictions.predict.form');
        Route::post('/predict', [CropPredictionController::class, 'predict'])->name('predictions.predict');
        Route::post('/forecast', [CropPredictionController::class, 'forecast'])->name('predictions.forecast');
        Route::post('/historical', [CropPredictionController::class, 'historical'])->name('predictions.historical');
        Route::post('/batch-predict', [CropPredictionController::class, 'batchPredict'])->name('predictions.batch');
        Route::get('/history', [CropPredictionController::class, 'history'])->name('predictions.history');
        Route::get('/options', [CropPredictionController::class, 'getOptions'])->name('predictions.options');
        Route::get('/forecast-batch', [CropPredictionController::class, 'getForecastBatch'])->name('predictions.forecast-batch');
        Route::delete('/clear-history', [CropPredictionController::class, 'clearHistory'])->name('predictions.clear-history');
    });

    // Interactive Map route
    Route::get('/map', [MapController::class, 'index'])->name('map.index');

    // Forum Routes
    Route::prefix('forum')->name('forum.')->group(function () {
        Route::get('/', [ForumController::class, 'index'])->name('index');
        Route::get('/create', [ForumController::class, 'create'])->name('create');
        Route::post('/', [ForumController::class, 'store'])->name('store');
        Route::get('/{slug}', [ForumController::class, 'show'])->name('show');
        Route::delete('/{id}', [ForumController::class, 'destroy'])->name('destroy');
        Route::post('/{postId}/comment', [ForumController::class, 'storeComment'])->name('comment.store');
        Route::delete('/comment/{id}', [ForumController::class, 'destroyComment'])->name('comment.destroy');
        Route::post('/vote', [ForumController::class, 'vote'])->name('vote');
        Route::post('/best-answer/{commentId}', [ForumController::class, 'markBestAnswer'])->name('best-answer');
    });
});

// Admin Routes
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    // Dashboard
    Route::get('/dashboard', function () {
        return view('admin.dashboard');
    })->name('dashboard');

    // Crop Data Management routes
    Route::prefix('crop-data')->name('crop-data.')->group(function () {
        Route::get('/', [CropDataController::class, 'index'])->name('index');
        Route::post('/import', [CropDataController::class, 'import'])->name('import');
        Route::post('/store', [CropDataController::class, 'store'])->name('store');
        Route::delete('/{id}', [CropDataController::class, 'destroy'])->name('destroy');
        Route::post('/delete-all', [CropDataController::class, 'deleteAll'])->name('delete-all');
        Route::get('/statistics', [CropDataController::class, 'getStatistics'])->name('statistics');
    });

    // Predictions routes
    Route::prefix('predictions')->name('predictions.')->group(function () {
        Route::get('/', [CropPredictionController::class, 'index'])->name('index');
        Route::get('/predict', [CropPredictionController::class, 'index'])->name('predict.form');
        Route::post('/predict', [CropPredictionController::class, 'predict'])->name('predict');
        Route::post('/forecast', [CropPredictionController::class, 'forecast'])->name('forecast');
        Route::post('/historical', [CropPredictionController::class, 'historical'])->name('historical');
        Route::post('/batch-predict', [CropPredictionController::class, 'batchPredict'])->name('batch');
        Route::get('/history', [CropPredictionController::class, 'history'])->name('history');
        Route::get('/options', [CropPredictionController::class, 'getOptions'])->name('options');
        Route::get('/forecast-batch', [CropPredictionController::class, 'getForecastBatch'])->name('forecast-batch');
        Route::delete('/clear-history', [CropPredictionController::class, 'clearHistory'])->name('clear-history');
    });

    // Interactive Map
    Route::get('/map', [MapController::class, 'index'])->name('map.index');

    // Users Management
    Route::get('/users', [UserController::class, 'index'])->name('users.index');
    Route::post('/users', [UserController::class, 'store'])->name('users.store');
    Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
    Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');

    // Reports
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/', [ReportController::class, 'index'])->name('index');
        Route::get('/production-summary', [ReportController::class, 'productionSummary'])->name('production-summary');
        Route::get('/prediction-analytics', [ReportController::class, 'predictionAnalytics'])->name('prediction-analytics');
        Route::get('/comparative-analysis', [ReportController::class, 'comparativeAnalysis'])->name('comparative-analysis');
        Route::get('/user-activity', [ReportController::class, 'userActivity'])->name('user-activity');
    });

    // Settings (placeholder)
    Route::get('/settings', function () {
        return view('admin.dashboard'); // Placeholder - will create later
    })->name('settings.index');
});

require __DIR__.'/auth.php';
