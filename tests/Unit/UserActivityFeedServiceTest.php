<?php

namespace Tests\Unit;

use App\Services\UserActivityFeedService;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use stdClass;
use Tests\TestCase;

class UserActivityFeedServiceTest extends TestCase
{
    public function test_compact_predictions_do_not_merge_different_prediction_scenarios(): void
    {
        $service = new UserActivityFeedService();

        $activities = new Collection([
            $this->makePredictionActivity(
                activityId: 2,
                activityAt: Carbon::parse('2026-04-08 10:05:00'),
                year: 2027,
                month: 2,
                farmType: 'RAINFED'
            ),
            $this->makePredictionActivity(
                activityId: 1,
                activityAt: Carbon::parse('2026-04-08 10:03:00'),
                year: 2026,
                month: 1,
                farmType: 'RAINFED'
            ),
        ]);

        $compacted = $service->compactPredictions($activities);

        $this->assertCount(2, $compacted);
        $this->assertSame('Created a prediction', $compacted[0]->title);
        $this->assertSame('Created a prediction', $compacted[1]->title);
    }

    public function test_compact_predictions_group_forecast_batches_with_accurate_description(): void
    {
        $service = new UserActivityFeedService();

        $activities = new Collection([
            $this->makePredictionActivity(
                activityId: 4,
                activityAt: Carbon::parse('2026-04-08 10:05:00'),
                year: 2028,
                month: 1,
                farmType: 'Forecast',
                batchId: 'forecast_batch_1'
            ),
            $this->makePredictionActivity(
                activityId: 3,
                activityAt: Carbon::parse('2026-04-08 10:04:00'),
                year: 2027,
                month: 1,
                farmType: 'Forecast',
                batchId: 'forecast_batch_1'
            ),
            $this->makePredictionActivity(
                activityId: 2,
                activityAt: Carbon::parse('2026-04-08 10:03:00'),
                year: 2026,
                month: 1,
                farmType: 'Forecast',
                batchId: 'forecast_batch_1'
            ),
        ]);

        $compacted = $service->compactPredictions($activities);

        $this->assertCount(1, $compacted);
        $this->assertSame('Created a forecast batch', $compacted[0]->title);
        $this->assertSame('Forecasted SNAP BEANS in BAKUN for 2026-2028', $compacted[0]->description);
        $this->assertSame(3, $compacted[0]->grouped_prediction_count);
    }

    private function makePredictionActivity(
        int $activityId,
        Carbon $activityAt,
        int $year,
        int $month,
        string $farmType,
        ?string $batchId = null
    ): stdClass {
        $activity = new stdClass();
        $activity->activity_type = 'prediction';
        $activity->activity_id = $activityId;
        $activity->activity_at = $activityAt;
        $activity->actor_id = 42;
        $activity->user_name = 'Jeydifarmer1';
        $activity->user_role = 'farmer';
        $activity->status = 'success';
        $activity->crop = 'SNAP BEANS';
        $activity->municipality = 'BAKUN';
        $activity->prediction_year = $year;
        $activity->prediction_month = $month;
        $activity->prediction_farm_type = $farmType;
        $activity->prediction_batch_id = $batchId;
        $activity->type_label = 'Prediction';
        $activity->title = 'Created a prediction';
        $activity->description = 'Predicted SNAP BEANS in BAKUN';

        return $activity;
    }
}
