<?php

namespace App\Services;

use App\Models\AdminActivityLog;
use App\Models\FarmerCalendarEvent;
use App\Models\ForumComment;
use App\Models\ForumPost;
use App\Models\Prediction;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class UserActivityFeedService
{
    public function recent(int $limit = 5, string $activityFilter = 'all'): Collection
    {
        $query = DB::query()
            ->fromSub($this->baseQuery(), 'activities');

        return $this->decorate(
            $this->applyActivityFilter($query, $activityFilter)
                ->orderByDesc('activity_at')
                ->limit($limit)
                ->get()
        );
    }

    public function paginate(int $perPage = 20, string $activityFilter = 'all'): LengthAwarePaginator
    {
        $query = DB::query()
            ->fromSub($this->baseQuery(), 'activities');

        $activities = $this->applyActivityFilter($query, $activityFilter)
            ->orderByDesc('activity_at')
            ->paginate($perPage);

        $activities->setCollection($this->decorate(collect($activities->items())));

        return $activities;
    }

    public function summary(): array
    {
        $predictionCount = Prediction::count();
        $forumCount = ForumPost::count() + ForumComment::count();
        $calendarCount = FarmerCalendarEvent::count();
        $registrationCount = User::count();
        $adminActionsCount = AdminActivityLog::count();
        $totalCount = $predictionCount + $forumCount + $calendarCount + $registrationCount + $adminActionsCount;

        return [
            'total_activities' => $totalCount,
            'predictions' => $predictionCount,
            'forum_interactions' => $forumCount,
            'calendar_events' => $calendarCount,
            'registrations' => $registrationCount,
            'admin_actions' => $adminActionsCount,
            'filters' => [
                'all' => ['label' => 'All', 'count' => $totalCount],
                'predictions' => ['label' => 'Predictions', 'count' => $predictionCount],
                'forum' => ['label' => 'Forum', 'count' => $forumCount],
                'calendar' => ['label' => 'Calendar', 'count' => $calendarCount],
                'registrations' => ['label' => 'Registrations', 'count' => $registrationCount],
                'admin_actions' => ['label' => 'Admin Actions', 'count' => $adminActionsCount],
            ],
        ];
    }

    public function normalizeActivityFilter(?string $activityFilter): string
    {
        $filter = Str::lower((string) $activityFilter);

        return array_key_exists($filter, $this->activityTypeGroups()) ? $filter : 'all';
    }

    public function compactPredictions(Collection $activities): Collection
    {
        $compacted = collect();

        foreach ($activities as $activity) {
            $activityItem = clone $activity;
            $activityItem->is_prediction_group = false;
            $activityItem->grouped_prediction_count = 1;
            $activityItem->grouped_prediction_oldest_at = $activityItem->activity_at instanceof Carbon
                ? $activityItem->activity_at->copy()
                : null;
            $activityItem->grouped_prediction_years = $this->uniquePredictionValues($activityItem->prediction_year ?? null);
            $activityItem->grouped_prediction_months = $this->uniquePredictionValues($activityItem->prediction_month ?? null);
            $activityItem->grouped_prediction_farm_types = $this->uniquePredictionValues($activityItem->prediction_farm_type ?? null);

            if ($activityItem->activity_type !== 'prediction') {
                $compacted->push($activityItem);
                continue;
            }

            $lastActivity = $compacted->last();

            if (! $this->shouldMergePredictionActivity($lastActivity, $activityItem)) {
                $compacted->push($activityItem);
                continue;
            }

            $lastActivity->is_prediction_group = true;
            $lastActivity->grouped_prediction_count++;
            $lastActivity->grouped_prediction_oldest_at = $activityItem->activity_at->copy();
            $lastActivity->grouped_prediction_years = $this->mergePredictionValues(
                $lastActivity->grouped_prediction_years ?? [],
                $activityItem->prediction_year ?? null
            );
            $lastActivity->grouped_prediction_months = $this->mergePredictionValues(
                $lastActivity->grouped_prediction_months ?? [],
                $activityItem->prediction_month ?? null
            );
            $lastActivity->grouped_prediction_farm_types = $this->mergePredictionValues(
                $lastActivity->grouped_prediction_farm_types ?? [],
                $activityItem->prediction_farm_type ?? null
            );
            $lastActivity->title = $this->predictionGroupTitle($lastActivity);
            $lastActivity->description = $this->predictionGroupDescription($lastActivity);
        }

        return $compacted;
    }

    private function baseQuery(): Builder
    {
        return $this->predictionQuery()
            ->unionAll($this->forumPostQuery())
            ->unionAll($this->forumCommentQuery())
            ->unionAll($this->calendarEventQuery())
            ->unionAll($this->userRegistrationQuery())
            ->unionAll($this->adminActivityQuery());
    }

    private function applyActivityFilter(QueryBuilder $query, string $activityFilter): QueryBuilder
    {
        $normalizedFilter = $this->normalizeActivityFilter($activityFilter);
        $activityTypes = $this->activityTypeGroups()[$normalizedFilter] ?? [];

        if (empty($activityTypes)) {
            return $query;
        }

        return $query->whereIn('activity_type', $activityTypes);
    }

    private function decorate(Collection $activities): Collection
    {
        return $activities->map(function (object $activity) {
            $activity->activity_at = Carbon::parse($activity->activity_at);
            $activity->activity_key = $activity->activity_type . '-' . $activity->activity_id;
            $activity->role_label = Str::headline((string) ($activity->user_role ?: 'User'));
            $activity->metadata = match (true) {
                is_array($activity->metadata ?? null) => $activity->metadata,
                is_string($activity->metadata ?? null) => json_decode($activity->metadata, true) ?: [],
                default => [],
            };
            $activity->type_label = $this->typeLabel($activity->activity_type);
            $activity->title = $this->titleFor($activity);
            $activity->description = $this->descriptionFor($activity);

            return $activity;
        });
    }

    private function typeLabel(string $type): string
    {
        return match ($type) {
            'prediction' => 'Prediction',
            'forum_post' => 'Forum Post',
            'forum_comment' => 'Forum Reply',
            'calendar_event' => 'Calendar Event',
            'user_registered' => 'Registration',
            'admin_password_reset' => 'Admin Action',
            default => 'Activity',
        };
    }

    private function titleFor(object $activity): string
    {
        return match ($activity->activity_type) {
            'prediction' => $activity->status === 'failed' ? 'Attempted a prediction' : 'Created a prediction',
            'forum_post' => 'Published a forum post',
            'forum_comment' => 'Posted a forum reply',
            'calendar_event' => $activity->event_type === 'reminder' ? 'Added a reminder' : 'Added a calendar note',
            'user_registered' => 'Registered an account',
            'admin_password_reset' => "Reset a user's password",
            default => 'Recorded an activity',
        };
    }

    private function descriptionFor(object $activity): string
    {
        return match ($activity->activity_type) {
            'prediction' => $this->predictionDescription($activity),
            'forum_post' => 'Shared "' . Str::limit((string) $activity->subject, 60) . '"',
            'forum_comment' => 'Commented on "' . Str::limit((string) $activity->subject, 60) . '"',
            'calendar_event' => 'Added "' . Str::limit((string) $activity->subject, 60) . '" to the calendar',
            'user_registered' => 'Joined the platform as ' . Str::lower($activity->role_label),
            'admin_password_reset' => $this->adminPasswordResetDescription($activity),
            default => 'Performed an activity in the system',
        };
    }

    private function adminPasswordResetDescription(object $activity): string
    {
        $targetName = $activity->subject
            ?: data_get($activity->metadata, 'target_name')
            ?: 'a user';

        return 'Reset password for ' . $targetName;
    }

    private function predictionDescription(object $activity): string
    {
        $crop = $activity->crop ?: 'a crop';
        $municipality = $activity->municipality ?: 'an area';

        if ($activity->status === 'failed') {
            return 'Prediction attempt for ' . $crop . ' in ' . $municipality . ' did not complete';
        }

        return 'Predicted ' . $crop . ' in ' . $municipality;
    }

    private function predictionGroupTitle(object $activity): string
    {
        if (($activity->grouped_prediction_count ?? 1) <= 1) {
            return $this->titleFor($activity);
        }

        if ($this->isForecastPredictionGroup($activity)) {
            return 'Created a forecast batch';
        }

        return $activity->status === 'failed'
            ? 'Attempted ' . $activity->grouped_prediction_count . ' predictions'
            : 'Created ' . $activity->grouped_prediction_count . ' predictions';
    }

    private function predictionGroupDescription(object $activity): string
    {
        $description = $this->predictionDescription($activity);

        if (($activity->grouped_prediction_count ?? 1) <= 1) {
            return $description;
        }

        if ($this->isForecastPredictionGroup($activity)) {
            $crop = $activity->crop ?: 'a crop';
            $municipality = $activity->municipality ?: 'an area';
            $years = collect($activity->grouped_prediction_years ?? [])
                ->filter(static fn ($year) => $year !== null && $year !== '')
                ->map(static fn ($year) => (int) $year)
                ->unique()
                ->sort()
                ->values();

            if ($years->count() >= 2) {
                return 'Forecasted ' . $crop . ' in ' . $municipality . ' for ' . $years->first() . '-' . $years->last();
            }

            if ($years->count() === 1) {
                return 'Forecasted ' . $crop . ' in ' . $municipality . ' for ' . $years->first();
            }

            return 'Forecasted ' . $crop . ' in ' . $municipality . ' across multiple years';
        }

        return $description . ' in a short burst';
    }

    private function shouldMergePredictionActivity(mixed $lastActivity, object $currentActivity): bool
    {
        if (! is_object($lastActivity) || $lastActivity->activity_type !== 'prediction') {
            return false;
        }

        if ($currentActivity->activity_type !== 'prediction') {
            return false;
        }

        $sameActorContext = (int) $lastActivity->actor_id === (int) $currentActivity->actor_id
            && (string) $lastActivity->user_role === (string) $currentActivity->user_role
            && (string) $lastActivity->status === (string) $currentActivity->status;

        if (! $sameActorContext) {
            return false;
        }

        $lastBatchId = (string) ($lastActivity->prediction_batch_id ?? '');
        $currentBatchId = (string) ($currentActivity->prediction_batch_id ?? '');

        if ($lastBatchId !== '' || $currentBatchId !== '') {
            if ($lastBatchId === '' || $currentBatchId === '' || $lastBatchId !== $currentBatchId) {
                return false;
            }

            return $this->predictionActivitiesWithinBurstWindow($lastActivity, $currentActivity);
        }

        $samePredictionContext =
            (string) ($lastActivity->crop ?? '') === (string) ($currentActivity->crop ?? '')
            && (string) ($lastActivity->municipality ?? '') === (string) ($currentActivity->municipality ?? '')
            && (string) ($lastActivity->prediction_farm_type ?? '') === (string) ($currentActivity->prediction_farm_type ?? '')
            && (string) ($lastActivity->prediction_year ?? '') === (string) ($currentActivity->prediction_year ?? '')
            && (string) ($lastActivity->prediction_month ?? '') === (string) ($currentActivity->prediction_month ?? '');

        if (! $samePredictionContext) {
            return false;
        }

        return $this->predictionActivitiesWithinBurstWindow($lastActivity, $currentActivity);
    }

    private function predictionActivitiesWithinBurstWindow(object $lastActivity, object $currentActivity): bool
    {
        $groupNewestAt = $lastActivity->activity_at instanceof Carbon
            ? $lastActivity->activity_at
            : Carbon::parse($lastActivity->activity_at);

        $currentAt = $currentActivity->activity_at instanceof Carbon
            ? $currentActivity->activity_at
            : Carbon::parse($currentActivity->activity_at);

        return $groupNewestAt->diffInMinutes($currentAt) <= 5;
    }

    private function isForecastPredictionGroup(object $activity): bool
    {
        return ($activity->prediction_farm_type ?? null) === 'Forecast'
            || ! empty($activity->prediction_batch_id ?? null);
    }

    private function uniquePredictionValues(mixed $value): array
    {
        if ($value === null || $value === '') {
            return [];
        }

        return [$value];
    }

    private function mergePredictionValues(array $existingValues, mixed $value): array
    {
        if ($value === null || $value === '') {
            return array_values(array_unique($existingValues, SORT_REGULAR));
        }

        $existingValues[] = $value;

        return array_values(array_unique($existingValues, SORT_REGULAR));
    }

    private function activityTypeGroups(): array
    {
        return [
            'all' => [],
            'predictions' => ['prediction'],
            'forum' => ['forum_post', 'forum_comment'],
            'calendar' => ['calendar_event'],
            'registrations' => ['user_registered'],
            'admin_actions' => ['admin_password_reset'],
        ];
    }

    private function predictionQuery(): Builder
    {
        $userRoleExpression = $this->castForUnion('users.role');
        $eventTypeNullExpression = $this->nullForUnionText();
        $metadataNullExpression = $this->nullForUnionText();
        $farmTypeExpression = $this->castForUnion('predictions.farm_type');
        $batchIdExpression = $this->castForUnion('predictions.batch_id');

        return Prediction::query()
            ->leftJoin('users', 'users.id', '=', 'predictions.user_id')
            ->selectRaw("'prediction' as activity_type")
            ->selectRaw('predictions.id as activity_id')
            ->selectRaw('predictions.created_at as activity_at')
            ->selectRaw('predictions.user_id as actor_id')
            ->selectRaw("COALESCE(users.name, 'Unknown User') as user_name")
            ->selectRaw($userRoleExpression . ' as user_role')
            ->selectRaw('predictions.status as status')
            ->selectRaw('predictions.crop as crop')
            ->selectRaw('predictions.municipality as municipality')
            ->selectRaw('NULL as subject')
            ->selectRaw('NULL as subject_slug')
            ->selectRaw($eventTypeNullExpression . ' as event_type')
            ->selectRaw($metadataNullExpression . ' as metadata')
            ->selectRaw($farmTypeExpression . ' as prediction_farm_type')
            ->selectRaw('predictions.year as prediction_year')
            ->selectRaw('predictions.month as prediction_month')
            ->selectRaw($batchIdExpression . ' as prediction_batch_id');
    }

    private function forumPostQuery(): Builder
    {
        $userRoleExpression = $this->castForUnion('users.role');
        $eventTypeNullExpression = $this->nullForUnionText();
        $metadataNullExpression = $this->nullForUnionText();
        $nullTextExpression = $this->nullForUnionText();

        return ForumPost::query()
            ->join('users', 'users.id', '=', 'forum_posts.user_id')
            ->selectRaw("'forum_post' as activity_type")
            ->selectRaw('forum_posts.id as activity_id')
            ->selectRaw('forum_posts.created_at as activity_at')
            ->selectRaw('forum_posts.user_id as actor_id')
            ->selectRaw('users.name as user_name')
            ->selectRaw($userRoleExpression . ' as user_role')
            ->selectRaw('NULL as status')
            ->selectRaw('forum_posts.crop as crop')
            ->selectRaw('forum_posts.municipality as municipality')
            ->selectRaw('forum_posts.title as subject')
            ->selectRaw('forum_posts.slug as subject_slug')
            ->selectRaw($eventTypeNullExpression . ' as event_type')
            ->selectRaw($metadataNullExpression . ' as metadata')
            ->selectRaw($nullTextExpression . ' as prediction_farm_type')
            ->selectRaw('NULL as prediction_year')
            ->selectRaw('NULL as prediction_month')
            ->selectRaw($nullTextExpression . ' as prediction_batch_id');
    }

    private function forumCommentQuery(): Builder
    {
        $userRoleExpression = $this->castForUnion('users.role');
        $eventTypeNullExpression = $this->nullForUnionText();
        $metadataNullExpression = $this->nullForUnionText();
        $nullTextExpression = $this->nullForUnionText();

        return ForumComment::query()
            ->join('users', 'users.id', '=', 'forum_comments.user_id')
            ->join('forum_posts', 'forum_posts.id', '=', 'forum_comments.post_id')
            ->selectRaw("'forum_comment' as activity_type")
            ->selectRaw('forum_comments.id as activity_id')
            ->selectRaw('forum_comments.created_at as activity_at')
            ->selectRaw('forum_comments.user_id as actor_id')
            ->selectRaw('users.name as user_name')
            ->selectRaw($userRoleExpression . ' as user_role')
            ->selectRaw('NULL as status')
            ->selectRaw('forum_posts.crop as crop')
            ->selectRaw('forum_posts.municipality as municipality')
            ->selectRaw('forum_posts.title as subject')
            ->selectRaw('forum_posts.slug as subject_slug')
            ->selectRaw($eventTypeNullExpression . ' as event_type')
            ->selectRaw($metadataNullExpression . ' as metadata')
            ->selectRaw($nullTextExpression . ' as prediction_farm_type')
            ->selectRaw('NULL as prediction_year')
            ->selectRaw('NULL as prediction_month')
            ->selectRaw($nullTextExpression . ' as prediction_batch_id');
    }

    private function calendarEventQuery(): Builder
    {
        $userRoleExpression = $this->castForUnion('users.role');
        $eventTypeExpression = $this->castForUnion('farmer_calendar_events.event_type');
        $metadataNullExpression = $this->nullForUnionText();
        $nullTextExpression = $this->nullForUnionText();

        return FarmerCalendarEvent::query()
            ->join('users', 'users.id', '=', 'farmer_calendar_events.user_id')
            ->selectRaw("'calendar_event' as activity_type")
            ->selectRaw('farmer_calendar_events.id as activity_id')
            ->selectRaw('farmer_calendar_events.created_at as activity_at')
            ->selectRaw('farmer_calendar_events.user_id as actor_id')
            ->selectRaw('users.name as user_name')
            ->selectRaw($userRoleExpression . ' as user_role')
            ->selectRaw('NULL as status')
            ->selectRaw('farmer_calendar_events.crop as crop')
            ->selectRaw('NULL as municipality')
            ->selectRaw('farmer_calendar_events.title as subject')
            ->selectRaw('NULL as subject_slug')
            ->selectRaw($eventTypeExpression . ' as event_type')
            ->selectRaw($metadataNullExpression . ' as metadata')
            ->selectRaw($nullTextExpression . ' as prediction_farm_type')
            ->selectRaw('NULL as prediction_year')
            ->selectRaw('NULL as prediction_month')
            ->selectRaw($nullTextExpression . ' as prediction_batch_id');
    }

    private function userRegistrationQuery(): Builder
    {
        $userRoleExpression = $this->castForUnion('users.role');
        $eventTypeNullExpression = $this->nullForUnionText();
        $metadataNullExpression = $this->nullForUnionText();
        $nullTextExpression = $this->nullForUnionText();

        return User::query()
            ->selectRaw("'user_registered' as activity_type")
            ->selectRaw('users.id as activity_id')
            ->selectRaw('users.created_at as activity_at')
            ->selectRaw('users.id as actor_id')
            ->selectRaw('users.name as user_name')
            ->selectRaw($userRoleExpression . ' as user_role')
            ->selectRaw('NULL as status')
            ->selectRaw('NULL as crop')
            ->selectRaw('NULL as municipality')
            ->selectRaw('NULL as subject')
            ->selectRaw('NULL as subject_slug')
            ->selectRaw($eventTypeNullExpression . ' as event_type')
            ->selectRaw($metadataNullExpression . ' as metadata')
            ->selectRaw($nullTextExpression . ' as prediction_farm_type')
            ->selectRaw('NULL as prediction_year')
            ->selectRaw('NULL as prediction_month')
            ->selectRaw($nullTextExpression . ' as prediction_batch_id');
    }

    private function adminActivityQuery(): Builder
    {
        $userRoleExpression = $this->castForUnion('actor_users.role');
        $metadataExpression = $this->castForUnion('admin_activity_logs.metadata');
        $eventTypeNullExpression = $this->nullForUnionText();
        $nullTextExpression = $this->nullForUnionText();

        return AdminActivityLog::query()
            ->leftJoin('users as actor_users', 'actor_users.id', '=', 'admin_activity_logs.actor_id')
            ->leftJoin('users as subject_users', 'subject_users.id', '=', 'admin_activity_logs.subject_user_id')
            ->selectRaw("'admin_password_reset' as activity_type")
            ->selectRaw('admin_activity_logs.id as activity_id')
            ->selectRaw('admin_activity_logs.created_at as activity_at')
            ->selectRaw('admin_activity_logs.actor_id as actor_id')
            ->selectRaw("COALESCE(actor_users.name, 'Unknown Admin') as user_name")
            ->selectRaw($userRoleExpression . ' as user_role')
            ->selectRaw('NULL as status')
            ->selectRaw('NULL as crop')
            ->selectRaw('NULL as municipality')
            ->selectRaw('subject_users.name as subject')
            ->selectRaw('NULL as subject_slug')
            ->selectRaw($eventTypeNullExpression . ' as event_type')
            ->selectRaw($metadataExpression . ' as metadata')
            ->selectRaw($nullTextExpression . ' as prediction_farm_type')
            ->selectRaw('NULL as prediction_year')
            ->selectRaw('NULL as prediction_month')
            ->selectRaw($nullTextExpression . ' as prediction_batch_id');
    }

    private function castForUnion(string $expression): string
    {
        return DB::connection()->getDriverName() === 'pgsql'
            ? '(' . $expression . ')::text'
            : $expression;
    }

    private function nullForUnionText(): string
    {
        return DB::connection()->getDriverName() === 'pgsql'
            ? 'NULL::text'
            : 'NULL';
    }
}
