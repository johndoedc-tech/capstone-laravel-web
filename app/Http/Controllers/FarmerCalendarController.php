<?php

namespace App\Http\Controllers;

use App\Models\FarmerCalendarEvent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class FarmerCalendarController extends Controller
{
    /**
     * Display the calendar page
     */
    public function index()
    {
        return view('farmers.calendar.index');
    }

    /**
     * Get events for a specific month
     */
    public function getEvents(Request $request)
    {
        $year = $request->year ?? now()->year;
        $month = $request->month ?? now()->month;

        $events = FarmerCalendarEvent::where('user_id', Auth::id())
            ->forMonth($year, $month)
            ->orderBy('event_date')
            ->orderBy('reminder_time')
            ->get()
            ->map(function ($event) {
                return [
                    'id' => $event->id,
                    'date' => $event->event_date->format('Y-m-d'),
                    'day' => $event->event_date->day,
                    'type' => $event->event_type,
                    'title' => $event->title,
                    'description' => $event->description,
                    'category' => $event->category,
                    'category_icon' => $event->category_icon,
                    'category_color' => $event->category_color,
                    'crop' => $event->crop,
                    'reminder_time' => $event->reminder_time ? $event->reminder_time->format('H:i') : null,
                    'is_completed' => $event->is_completed,
                ];
            });

        // Group events by day for easy access
        $eventsByDay = $events->groupBy('day');

        return response()->json([
            'success' => true,
            'year' => (int) $year,
            'month' => (int) $month,
            'events' => $events,
            'events_by_day' => $eventsByDay,
        ]);
    }

    /**
     * Store a new event
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'event_date' => 'required|date',
            'event_type' => 'required|in:note,reminder',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'category' => 'nullable|string|in:pest,harvest,planting,fertilizer,weather,other',
            'crop' => 'nullable|string|max:100',
            'reminder_time' => 'nullable|date_format:H:i',
        ]);

        $event = FarmerCalendarEvent::create([
            'user_id' => Auth::id(),
            'event_date' => $validated['event_date'],
            'event_type' => $validated['event_type'],
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'category' => $validated['category'] ?? 'other',
            'crop' => $validated['crop'] ?? null,
            'reminder_time' => $validated['reminder_time'] ?? null,
        ]);

        return response()->json([
            'success' => true,
            'message' => $validated['event_type'] === 'reminder' ? 'Reminder set successfully!' : 'Note added successfully!',
            'event' => [
                'id' => $event->id,
                'date' => $event->event_date->format('Y-m-d'),
                'day' => $event->event_date->day,
                'type' => $event->event_type,
                'title' => $event->title,
                'description' => $event->description,
                'category' => $event->category,
                'category_icon' => $event->category_icon,
                'category_color' => $event->category_color,
                'crop' => $event->crop,
                'reminder_time' => $event->reminder_time ? $event->reminder_time->format('H:i') : null,
                'is_completed' => $event->is_completed,
            ],
        ]);
    }

    /**
     * Update an event
     */
    public function update(Request $request, $id)
    {
        $event = FarmerCalendarEvent::where('user_id', Auth::id())
            ->findOrFail($id);

        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'category' => 'nullable|string|in:pest,harvest,planting,fertilizer,weather,other',
            'crop' => 'nullable|string|max:100',
            'reminder_time' => 'nullable|date_format:H:i',
            'is_completed' => 'sometimes|boolean',
        ]);

        $event->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Event updated successfully!',
            'event' => [
                'id' => $event->id,
                'date' => $event->event_date->format('Y-m-d'),
                'day' => $event->event_date->day,
                'type' => $event->event_type,
                'title' => $event->title,
                'description' => $event->description,
                'category' => $event->category,
                'category_icon' => $event->category_icon,
                'category_color' => $event->category_color,
                'crop' => $event->crop,
                'reminder_time' => $event->reminder_time ? $event->reminder_time->format('H:i') : null,
                'is_completed' => $event->is_completed,
            ],
        ]);
    }

    /**
     * Delete an event
     */
    public function destroy($id)
    {
        try {
            $userId = Auth::id();
            
            if (!$userId) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not authenticated',
                ], 401);
            }

            $event = FarmerCalendarEvent::where('user_id', $userId)
                ->where('id', $id)
                ->first();

            if (!$event) {
                return response()->json([
                    'success' => false,
                    'message' => 'Event not found or not authorized',
                ], 404);
            }

            $event->delete();

            return response()->json([
                'success' => true,
                'message' => 'Event deleted successfully!',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting event: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Toggle event completion status
     */
    public function toggleComplete($id)
    {
        $event = FarmerCalendarEvent::where('user_id', Auth::id())
            ->findOrFail($id);

        $event->update(['is_completed' => !$event->is_completed]);

        return response()->json([
            'success' => true,
            'is_completed' => $event->is_completed,
            'message' => $event->is_completed ? 'Marked as complete!' : 'Marked as incomplete',
        ]);
    }

    /**
     * Get today's reminders for the user
     */
    public function getTodayReminders()
    {
        $reminders = FarmerCalendarEvent::where('user_id', Auth::id())
            ->where('event_type', 'reminder')
            ->where('is_completed', false)
            ->whereDate('event_date', now()->toDateString())
            ->orderBy('reminder_time')
            ->get()
            ->map(function ($event) {
                return [
                    'id' => $event->id,
                    'title' => $event->title,
                    'description' => $event->description,
                    'category' => $event->category,
                    'category_icon' => $event->category_icon,
                    'reminder_time' => $event->reminder_time ? $event->reminder_time->format('h:i A') : 'All day',
                    'crop' => $event->crop,
                ];
            });

        return response()->json([
            'success' => true,
            'count' => $reminders->count(),
            'reminders' => $reminders,
        ]);
    }

    /**
     * Get upcoming reminders (next 7 days)
     */
    public function getUpcomingReminders()
    {
        $reminders = FarmerCalendarEvent::where('user_id', Auth::id())
            ->where('event_type', 'reminder')
            ->where('is_completed', false)
            ->whereBetween('event_date', [now()->toDateString(), now()->addDays(7)->toDateString()])
            ->orderBy('event_date')
            ->orderBy('reminder_time')
            ->get()
            ->map(function ($event) {
                return [
                    'id' => $event->id,
                    'title' => $event->title,
                    'description' => $event->description,
                    'category' => $event->category,
                    'category_icon' => $event->category_icon,
                    'date' => $event->event_date->format('M d'),
                    'day_name' => $event->event_date->format('l'),
                    'is_today' => $event->event_date->isToday(),
                    'is_tomorrow' => $event->event_date->isTomorrow(),
                    'reminder_time' => $event->reminder_time ? $event->reminder_time->format('h:i A') : null,
                    'crop' => $event->crop,
                ];
            });

        return response()->json([
            'success' => true,
            'count' => $reminders->count(),
            'reminders' => $reminders,
        ]);
    }
}
