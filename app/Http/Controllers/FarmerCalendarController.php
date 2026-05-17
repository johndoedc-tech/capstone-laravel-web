<?php

namespace App\Http\Controllers;

use App\Models\FarmerCalendarEvent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

class FarmerCalendarController extends Controller
{
    private const HARVEST_BASE_DAYS = [
        'cabbage' => 58,
        'broccoli' => 60,
        'lettuce' => 55,
        'cauliflower' => 68,
        'chinese cabbage' => 58,
        'carrots' => 90,
        'garden peas' => 63,
        'white potato' => 100,
        'snap beans' => 62,
        'sweet pepper' => 80,
    ];

    private const TRANSPLANTED_CROPS = [
        'cabbage',
        'broccoli',
        'lettuce',
        'cauliflower',
        'chinese cabbage',
        'sweet pepper',
    ];

    private const FERTILIZATION_STAGES = [
        'cabbage' => [
            ['key' => 'basal', 'label' => 'Basal fertilizer', 'offset' => 0],
            ['key' => 'side_dress_1', 'label' => 'First side-dress', 'offset' => 21],
            ['key' => 'head_formation', 'label' => 'Head formation feeding', 'offset' => 42],
        ],
        'broccoli' => [
            ['key' => 'basal', 'label' => 'Basal fertilizer', 'offset' => 0],
            ['key' => 'side_dress_1', 'label' => 'First side-dress', 'offset' => 21],
            ['key' => 'head_formation', 'label' => 'Before head formation', 'offset' => 42],
        ],
        'lettuce' => [
            ['key' => 'basal', 'label' => 'Basal fertilizer', 'offset' => 0],
            ['key' => 'side_dress_1', 'label' => 'Light side-dress', 'offset' => 18],
        ],
        'cauliflower' => [
            ['key' => 'basal', 'label' => 'Basal fertilizer', 'offset' => 0],
            ['key' => 'side_dress_1', 'label' => 'First side-dress', 'offset' => 21],
            ['key' => 'curd_formation', 'label' => 'Curd formation feeding', 'offset' => 45],
        ],
        'chinese cabbage' => [
            ['key' => 'basal', 'label' => 'Basal fertilizer', 'offset' => 0],
            ['key' => 'side_dress_1', 'label' => 'First side-dress', 'offset' => 14],
            ['key' => 'side_dress_2', 'label' => 'Second side-dress', 'offset' => 28],
        ],
        'carrots' => [
            ['key' => 'basal', 'label' => 'Basal fertilizer', 'offset' => 0],
            ['key' => 'side_dress_1', 'label' => 'Root development side-dress', 'offset' => 28],
        ],
        'garden peas' => [
            ['key' => 'basal', 'label' => 'Basal compost/P-K', 'offset' => 0],
            ['key' => 'flowering', 'label' => 'Flowering or pod formation feed', 'offset' => 35],
        ],
        'white potato' => [
            ['key' => 'basal', 'label' => 'Basal fertilizer', 'offset' => 0],
            ['key' => 'hilling', 'label' => 'Hilling side-dress', 'offset' => 25],
            ['key' => 'tuber_initiation', 'label' => 'Tuber initiation feeding', 'offset' => 45],
        ],
        'snap beans' => [
            ['key' => 'basal', 'label' => 'Basal fertilizer', 'offset' => 0],
            ['key' => 'flowering', 'label' => 'Flowering or early pod feed', 'offset' => 35],
        ],
        'sweet pepper' => [
            ['key' => 'basal', 'label' => 'Basal fertilizer', 'offset' => 0],
            ['key' => 'side_dress_1', 'label' => 'First side-dress', 'offset' => 21],
            ['key' => 'fruit_setting', 'label' => 'Flowering and fruit setting feed', 'offset' => 45],
        ],
    ];

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
            ->map(fn ($event) => $this->formatEvent($event));

        // Group by full date to avoid leaking events into adjacent-month cells
        // that share the same day number (e.g., May 1 and June 1).
        $eventsByDay = $events->groupBy('date');

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
            'category' => 'nullable|string|in:pest,harvest,planting,crop_plan,fertilizer,weather,other',
            'crop' => 'nullable|required_if:category,crop_plan|string|max:100',
            'desired_area_sqm' => 'nullable|numeric|min:0.01|max:999999999.99',
            'water_source' => 'nullable|required_if:category,crop_plan|string|in:rainfed,irrigated',
            'planting_material' => 'nullable|required_if:category,crop_plan|string|in:seed,seedling',
            'reminder_time' => 'nullable|date_format:H:i',
        ]);

        $isCropPlan = ($validated['category'] ?? null) === 'crop_plan';
        $harvestEstimate = $isCropPlan
            ? $this->getHarvestEstimate(
                $validated['crop'],
                $validated['water_source'],
                $validated['planting_material'],
                $validated['event_date'],
            )
            : null;
        $fertilizationStages = $isCropPlan
            ? $this->getFertilizationStages(
                $validated['crop'],
                $validated['water_source'],
                $validated['planting_material'],
                $validated['event_date'],
            )
            : [];
        $supportsHarvestEstimate = $this->supportsCalendarColumns([
            'estimated_harvest_date',
            'estimated_harvest_days',
            'harvest_event_id',
        ]);
        $supportsStageLinks = $this->supportsCalendarColumns([
            'crop_plan_event_id',
            'crop_plan_stage',
        ]);

        $event = DB::transaction(function () use ($validated, $isCropPlan, $harvestEstimate, $fertilizationStages, $supportsHarvestEstimate, $supportsStageLinks) {
            $eventData = [
                'user_id' => Auth::id(),
                'event_date' => $validated['event_date'],
                'event_type' => $validated['event_type'],
                'title' => $validated['title'],
                'description' => $validated['description'] ?? null,
                'category' => $validated['category'] ?? 'other',
                'crop' => $validated['crop'] ?? null,
                'desired_area_sqm' => $isCropPlan
                    ? ($validated['desired_area_sqm'] ?? null)
                    : null,
                'water_source' => $isCropPlan
                    ? ($validated['water_source'] ?? null)
                    : null,
                'planting_material' => $isCropPlan
                    ? ($validated['planting_material'] ?? null)
                    : null,
                'reminder_time' => $validated['reminder_time'] ?? null,
            ];

            if ($supportsHarvestEstimate) {
                $eventData['estimated_harvest_date'] = $harvestEstimate['date'] ?? null;
                $eventData['estimated_harvest_days'] = $harvestEstimate['days'] ?? null;
            }

            $event = FarmerCalendarEvent::create($eventData);

            if ($isCropPlan && $harvestEstimate) {
                $harvestEvent = FarmerCalendarEvent::create([
                    'user_id' => Auth::id(),
                    'event_date' => $harvestEstimate['date'],
                    'event_type' => 'note',
                    'title' => 'Harvest ' . $validated['crop'],
                    'description' => $this->buildHarvestDescription($validated, $harvestEstimate['days']),
                    'category' => 'harvest',
                    'crop' => $validated['crop'],
                    'desired_area_sqm' => $validated['desired_area_sqm'] ?? null,
                    'water_source' => $validated['water_source'],
                    'planting_material' => $validated['planting_material'],
                ]);

                if ($supportsHarvestEstimate) {
                    $event->update(['harvest_event_id' => $harvestEvent->id]);
                }
            }

            if ($supportsStageLinks) {
                foreach ($fertilizationStages as $stage) {
                    FarmerCalendarEvent::create([
                        'user_id' => Auth::id(),
                        'event_date' => $stage['date'],
                        'event_type' => 'note',
                        'title' => $stage['label'] . ' - ' . $validated['crop'],
                        'description' => $this->buildFertilizationDescription($validated, $stage),
                        'category' => 'fertilizer',
                        'crop' => $validated['crop'],
                        'desired_area_sqm' => $validated['desired_area_sqm'] ?? null,
                        'water_source' => $validated['water_source'],
                        'planting_material' => $validated['planting_material'],
                        'crop_plan_event_id' => $event->id,
                        'crop_plan_stage' => 'fertilizer_' . $stage['key'],
                    ]);
                }
            }

            return $event->fresh();
        });

        return response()->json([
            'success' => true,
            'message' => $isCropPlan
                ? 'Crop plan added successfully!'
                : ($validated['event_type'] === 'reminder' ? 'Reminder set successfully!' : 'Note added successfully!'),
            'event' => $this->formatEvent($event),
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
            'category' => 'nullable|string|in:pest,harvest,planting,crop_plan,fertilizer,weather,other',
            'crop' => 'nullable|required_if:category,crop_plan|string|max:100',
            'desired_area_sqm' => 'nullable|numeric|min:0.01|max:999999999.99',
            'water_source' => 'nullable|required_if:category,crop_plan|string|in:rainfed,irrigated',
            'planting_material' => 'nullable|required_if:category,crop_plan|string|in:seed,seedling',
            'reminder_time' => 'nullable|date_format:H:i',
            'is_completed' => 'sometimes|boolean',
        ]);

        $event->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Event updated successfully!',
            'event' => $this->formatEvent($event),
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

            if ($event->category === 'crop_plan'
                && $this->supportsCalendarColumns(['harvest_event_id'])
                && $event->harvest_event_id) {
                FarmerCalendarEvent::where('user_id', $userId)
                    ->where('id', $event->harvest_event_id)
                    ->delete();
            }

            if ($event->category === 'crop_plan'
                && $this->supportsCalendarColumns(['crop_plan_event_id'])) {
                FarmerCalendarEvent::where('user_id', $userId)
                    ->where('crop_plan_event_id', $event->id)
                    ->delete();
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

    private function formatEvent(FarmerCalendarEvent $event): array
    {
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
            'desired_area_sqm' => $event->desired_area_sqm !== null ? (float) $event->desired_area_sqm : null,
            'water_source' => $event->water_source,
            'planting_material' => $event->planting_material,
            'estimated_harvest_date' => $event->estimated_harvest_date?->format('Y-m-d'),
            'estimated_harvest_days' => $event->estimated_harvest_days,
            'harvest_event_id' => $event->harvest_event_id,
            'crop_plan_event_id' => $event->crop_plan_event_id,
            'crop_plan_stage' => $event->crop_plan_stage,
            'reminder_time' => $event->reminder_time ? $event->reminder_time->format('H:i') : null,
            'is_completed' => $event->is_completed,
        ];
    }

    private function getHarvestEstimate(string $crop, string $waterSource, string $plantingMaterial, string $planningDate): array
    {
        $cropKey = strtolower(trim($crop));
        $days = self::HARVEST_BASE_DAYS[$cropKey] ?? 75;

        if ($plantingMaterial === 'seed' && in_array($cropKey, self::TRANSPLANTED_CROPS, true)) {
            $days += 30;
        }

        if ($waterSource === 'rainfed') {
            $days += 7;
        }

        return [
            'days' => $days,
            'date' => Carbon::parse($planningDate)->addDays($days)->toDateString(),
        ];
    }

    private function buildHarvestDescription(array $validated, int $days): string
    {
        $waterSource = ucfirst($validated['water_source']);
        $plantingMaterial = ucfirst($validated['planting_material']);

        return "Estimated from crop plan: {$days} days after planning date. Water source: {$waterSource}. Seed type: {$plantingMaterial}.";
    }

    private function getFertilizationStages(string $crop, string $waterSource, string $plantingMaterial, string $planningDate): array
    {
        $cropKey = strtolower(trim($crop));
        $rules = self::FERTILIZATION_STAGES[$cropKey] ?? [
            ['key' => 'basal', 'label' => 'Basal fertilizer', 'offset' => 0],
            ['key' => 'side_dress_1', 'label' => 'First side-dress', 'offset' => 21],
        ];
        $fieldStartDelay = $this->getFieldStartDelayDays($cropKey, $plantingMaterial);

        return array_map(function ($stage) use ($planningDate, $waterSource, $fieldStartDelay) {
            $rainfedDelay = $waterSource === 'rainfed' && $stage['offset'] > 0 ? 3 : 0;
            $daysFromPlanning = $fieldStartDelay + $stage['offset'] + $rainfedDelay;

            return [
                'key' => $stage['key'],
                'label' => $stage['label'],
                'days_from_planning' => $daysFromPlanning,
                'date' => Carbon::parse($planningDate)->addDays($daysFromPlanning)->toDateString(),
                'rainfed_delay_days' => $rainfedDelay,
            ];
        }, $rules);
    }

    private function getFieldStartDelayDays(string $cropKey, string $plantingMaterial): int
    {
        if ($plantingMaterial === 'seed' && in_array($cropKey, self::TRANSPLANTED_CROPS, true)) {
            return 30;
        }

        return 0;
    }

    private function buildFertilizationDescription(array $validated, array $stage): string
    {
        $waterSource = ucfirst($validated['water_source']);
        $plantingMaterial = ucfirst($validated['planting_material']);
        $note = "Generated from crop plan: {$stage['label']} {$stage['days_from_planning']} days after planning date.";
        $note .= " Water source: {$waterSource}. Seed type: {$plantingMaterial}.";

        if (($stage['rainfed_delay_days'] ?? 0) > 0) {
            $note .= ' Rainfed schedule includes a small delay; apply when soil moisture is available.';
        }

        return $note;
    }

    private function supportsCalendarColumns(array $columns): bool
    {
        foreach ($columns as $column) {
            if (!Schema::hasColumn('farmer_calendar_events', $column)) {
                return false;
            }
        }

        return true;
    }
}
