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
use App\Http\Controllers\FarmerChatbotController;
use App\Services\UserActivityFeedService;
use App\Models\CropProduction;
use App\Models\Prediction;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

$emptyActivityStats = static fn () => [
    'total_activities' => 0,
    'predictions' => 0,
    'forum_interactions' => 0,
    'calendar_events' => 0,
    'registrations' => 0,
    'admin_actions' => 0,
    'filters' => [
        'all' => ['label' => 'All', 'count' => 0],
        'predictions' => ['label' => 'Predictions', 'count' => 0],
        'forum' => ['label' => 'Forum', 'count' => 0],
        'calendar' => ['label' => 'Calendar', 'count' => 0],
        'registrations' => ['label' => 'Registrations', 'count' => 0],
        'admin_actions' => ['label' => 'Admin Actions', 'count' => 0],
    ],
];

$resolveDashboardActivityContext = static function (Request $request, UserActivityFeedService $activityFeed) use ($emptyActivityStats): array {
    $activityFilter = $activityFeed->normalizeActivityFilter($request->query('activity_type'));

    try {
        $activityStats = $activityFeed->summary();
        $recentActivities = $activityFeed->recent(5, $activityFilter);

        return [
            'activityFeedUnavailable' => false,
            'activityFilter' => $activityFilter,
            'activityStats' => $activityStats,
            'recentActivities' => $recentActivities,
            'compactRecentActivities' => $activityFeed->recentCompacted(5, $activityFilter),
        ];
    } catch (\Throwable $exception) {
        report($exception);

        return [
            'activityFeedUnavailable' => true,
            'activityFilter' => $activityFilter,
            'activityStats' => $emptyActivityStats(),
            'recentActivities' => collect(),
            'compactRecentActivities' => collect(),
        ];
    }
};

$resolveActivityPageContext = static function (Request $request, UserActivityFeedService $activityFeed) use ($emptyActivityStats): array {
    $activityFilter = $activityFeed->normalizeActivityFilter($request->query('activity_type'));

    try {
        $activityStats = $activityFeed->summary();
        $activities = $activityFeed->paginate(20, $activityFilter);

        return [
            'activityFeedUnavailable' => false,
            'activityFilter' => $activityFilter,
            'activities' => $activities,
            'compactActivities' => $activityFeed->compactPredictions($activities->getCollection()),
            'activityStats' => $activityStats,
        ];
    } catch (\Throwable $exception) {
        report($exception);

        $activities = new LengthAwarePaginator(
            collect(),
            0,
            20,
            LengthAwarePaginator::resolveCurrentPage(),
            [
                'path' => $request->url(),
                'query' => $request->query(),
            ]
        );

        return [
            'activityFeedUnavailable' => true,
            'activityFilter' => $activityFilter,
            'activities' => $activities,
            'compactActivities' => collect(),
            'activityStats' => $emptyActivityStats(),
        ];
    }
};

Route::get('/', function () {
    return view('welcome');
})->name('welcome');

// Shared dashboard entry point: redirect admins to admin dashboard, keep farmers on farmer dashboard.
Route::get('/dashboard', function () {
    if (auth()->user()->isAdmin()) {
        return redirect()->route('admin.dashboard');
    }

    return app(FarmerDashboardController::class)->index();
})
    ->middleware(['auth', 'force-password-change', 'verified'])
    ->name('dashboard');

Route::middleware(['auth', 'force-password-change'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::middleware('farmer')->group(function () {
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

            // Farmer Chatbot API
            Route::prefix('chatbot')->name('chatbot.')->group(function () {
                Route::get('/history', [FarmerChatbotController::class, 'history'])->name('history');
                Route::post('/message', [FarmerChatbotController::class, 'send'])
                    ->middleware('throttle:20,1')
                    ->name('message');
                Route::post('/reset', [FarmerChatbotController::class, 'reset'])
                    ->middleware('throttle:10,1')
                    ->name('reset');
            });
        });

        // Farmer Predictions routes
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

        // Farmer Interactive Map route
        Route::get('/map', [MapController::class, 'index'])->name('map.index');
    });

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
Route::middleware(['auth', 'force-password-change', 'admin'])->prefix('admin')->name('admin.')->group(function () use ($resolveDashboardActivityContext, $resolveActivityPageContext) {
    // Dashboard
    Route::get('/dashboard', function (Request $request, UserActivityFeedService $activityFeed) use ($resolveDashboardActivityContext) {
        return view('admin.dashboard', $resolveDashboardActivityContext($request, $activityFeed));
    })->name('dashboard');

    Route::get('/activities', function (Request $request, UserActivityFeedService $activityFeed) use ($resolveActivityPageContext) {
        return view('admin.activities.index', $resolveActivityPageContext($request, $activityFeed));
    })->name('activities.index');

    // Crop Data Management routes
    Route::prefix('crop-data')->name('crop-data.')->group(function () {
        Route::get('/', [CropDataController::class, 'index'])->name('index');
        Route::post('/import', [CropDataController::class, 'import'])->name('import');
        Route::post('/store', [CropDataController::class, 'store'])->name('store');
        Route::put('/{id}', [CropDataController::class, 'update'])->name('update');
        Route::post('/{id}/archive', [CropDataController::class, 'archive'])->name('archive');
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
    Route::put('/users/{user}/reset-password', [UserController::class, 'resetPassword'])->name('users.password.reset');
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
    Route::get('/settings', function (Request $request, UserActivityFeedService $activityFeed) use ($resolveDashboardActivityContext) {
        return view('admin.dashboard', $resolveDashboardActivityContext($request, $activityFeed)); // Placeholder - will create later
    })->name('settings.index');
});

require __DIR__.'/auth.php';
