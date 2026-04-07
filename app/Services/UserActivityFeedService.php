<?php

namespace App\Services;

use App\Models\FarmerCalendarEvent;
use App\Models\ForumComment;
use App\Models\ForumPost;
use App\Models\Prediction;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class UserActivityFeedService
{
    public function recent(int $limit = 5): Collection
    {
        return $this->decorate(
            DB::query()
                ->fromSub($this->baseQuery(), 'activities')
                ->orderByDesc('activity_at')
                ->limit($limit)
                ->get()
        );
    }

    public function paginate(int $perPage = 20): LengthAwarePaginator
    {
        $activities = DB::query()
            ->fromSub($this->baseQuery(), 'activities')
            ->orderByDesc('activity_at')
            ->paginate($perPage);

        $activities->setCollection($this->decorate(collect($activities->items())));

        return $activities;
    }

    public function summary(): array
    {
        return [
            'total_activities' => Prediction::count() + ForumPost::count() + ForumComment::count() + FarmerCalendarEvent::count() + User::count(),
            'predictions' => Prediction::count(),
            'forum_interactions' => ForumPost::count() + ForumComment::count(),
            'calendar_events' => FarmerCalendarEvent::count(),
            'registrations' => User::count(),
        ];
    }

    private function baseQuery(): Builder
    {
        return $this->predictionQuery()
            ->unionAll($this->forumPostQuery())
            ->unionAll($this->forumCommentQuery())
            ->unionAll($this->calendarEventQuery())
            ->unionAll($this->userRegistrationQuery());
    }

    private function decorate(Collection $activities): Collection
    {
        return $activities->map(function (object $activity) {
            $activity->activity_at = Carbon::parse($activity->activity_at);
            $activity->activity_key = $activity->activity_type . '-' . $activity->activity_id;
            $activity->role_label = Str::headline((string) $activity->user_role);
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
            default => 'Performed an activity in the system',
        };
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

    private function predictionQuery(): Builder
    {
        return Prediction::query()
            ->leftJoin('users', 'users.id', '=', 'predictions.user_id')
            ->selectRaw("'prediction' as activity_type")
            ->selectRaw('predictions.id as activity_id')
            ->selectRaw('predictions.created_at as activity_at')
            ->selectRaw('predictions.user_id as actor_id')
            ->selectRaw("COALESCE(users.name, 'Unknown User') as user_name")
            ->selectRaw("COALESCE(users.role, 'user') as user_role")
            ->selectRaw('predictions.status as status')
            ->selectRaw('predictions.crop as crop')
            ->selectRaw('predictions.municipality as municipality')
            ->selectRaw('NULL as subject')
            ->selectRaw('NULL as subject_slug')
            ->selectRaw('NULL as event_type');
    }

    private function forumPostQuery(): Builder
    {
        return ForumPost::query()
            ->join('users', 'users.id', '=', 'forum_posts.user_id')
            ->selectRaw("'forum_post' as activity_type")
            ->selectRaw('forum_posts.id as activity_id')
            ->selectRaw('forum_posts.created_at as activity_at')
            ->selectRaw('forum_posts.user_id as actor_id')
            ->selectRaw('users.name as user_name')
            ->selectRaw("COALESCE(users.role, 'user') as user_role")
            ->selectRaw('NULL as status')
            ->selectRaw('forum_posts.crop as crop')
            ->selectRaw('forum_posts.municipality as municipality')
            ->selectRaw('forum_posts.title as subject')
            ->selectRaw('forum_posts.slug as subject_slug')
            ->selectRaw('NULL as event_type');
    }

    private function forumCommentQuery(): Builder
    {
        return ForumComment::query()
            ->join('users', 'users.id', '=', 'forum_comments.user_id')
            ->join('forum_posts', 'forum_posts.id', '=', 'forum_comments.post_id')
            ->selectRaw("'forum_comment' as activity_type")
            ->selectRaw('forum_comments.id as activity_id')
            ->selectRaw('forum_comments.created_at as activity_at')
            ->selectRaw('forum_comments.user_id as actor_id')
            ->selectRaw('users.name as user_name')
            ->selectRaw("COALESCE(users.role, 'user') as user_role")
            ->selectRaw('NULL as status')
            ->selectRaw('forum_posts.crop as crop')
            ->selectRaw('forum_posts.municipality as municipality')
            ->selectRaw('forum_posts.title as subject')
            ->selectRaw('forum_posts.slug as subject_slug')
            ->selectRaw('NULL as event_type');
    }

    private function calendarEventQuery(): Builder
    {
        return FarmerCalendarEvent::query()
            ->join('users', 'users.id', '=', 'farmer_calendar_events.user_id')
            ->selectRaw("'calendar_event' as activity_type")
            ->selectRaw('farmer_calendar_events.id as activity_id')
            ->selectRaw('farmer_calendar_events.created_at as activity_at')
            ->selectRaw('farmer_calendar_events.user_id as actor_id')
            ->selectRaw('users.name as user_name')
            ->selectRaw("COALESCE(users.role, 'user') as user_role")
            ->selectRaw('NULL as status')
            ->selectRaw('farmer_calendar_events.crop as crop')
            ->selectRaw('NULL as municipality')
            ->selectRaw('farmer_calendar_events.title as subject')
            ->selectRaw('NULL as subject_slug')
            ->selectRaw('farmer_calendar_events.event_type as event_type');
    }

    private function userRegistrationQuery(): Builder
    {
        return User::query()
            ->selectRaw("'user_registered' as activity_type")
            ->selectRaw('users.id as activity_id')
            ->selectRaw('users.created_at as activity_at')
            ->selectRaw('users.id as actor_id')
            ->selectRaw('users.name as user_name')
            ->selectRaw("COALESCE(users.role, 'user') as user_role")
            ->selectRaw('NULL as status')
            ->selectRaw('NULL as crop')
            ->selectRaw('NULL as municipality')
            ->selectRaw('NULL as subject')
            ->selectRaw('NULL as subject_slug')
            ->selectRaw('NULL as event_type');
    }
}
