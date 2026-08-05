<?php

namespace App\Http\Controllers;

use App\Models\FarmerCalendarEvent;
use App\Services\EventAuthenticityService;
use App\Services\IdempotentOperationService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class LguValidationController extends Controller
{
    public function index(Request $request): View
    {
        $validator = Auth::user();
        $type = $request->query('type', 'all');
        $search = trim((string) $request->query('search', ''));

        $query = FarmerCalendarEvent::query()
            ->with(['user', 'lguValidator', 'audits.user'])
            ->where(function (Builder $query) {
                $query->where('category', 'damage_report')
                    ->orWhereNotNull('actual_harvest_recorded_at');
            })
            ->where('lgu_validation_status', FarmerCalendarEvent::VALIDATION_PENDING);

        $this->scopeToValidator($query, $validator);

        if ($type === 'damage') {
            $query->where('category', 'damage_report');
        } elseif ($type === 'harvest') {
            $query->whereNotNull('actual_harvest_recorded_at');
        }

        if ($search !== '') {
            $searchLike = '%' . strtolower($search) . '%';
            $query->where(function (Builder $query) use ($searchLike) {
                $query->whereRaw('LOWER(title) LIKE ?', [$searchLike])
                    ->orWhereRaw('LOWER(crop) LIKE ?', [$searchLike])
                    ->orWhereHas('user', function (Builder $userQuery) use ($searchLike) {
                        $userQuery->whereRaw('LOWER(name) LIKE ?', [$searchLike])
                            ->orWhereRaw('LOWER(email) LIKE ?', [$searchLike]);
                    });
            });
        }

        $items = $query
            ->orderByDesc('submitted_to_lgu_at')
            ->orderByDesc('created_at')
            ->paginate(12)
            ->withQueryString();

        return view('lgu.dashboard', [
            'items' => $items,
            'type' => $type,
            'search' => $search,
            'validator' => $validator,
        ]);
    }

    public function records(Request $request): View
    {
        $validator = Auth::user();
        $status = (string) $request->query('status', 'all');
        $type = (string) $request->query('type', 'all');
        $search = trim((string) $request->query('search', ''));

        if (! in_array($status, ['all', FarmerCalendarEvent::VALIDATION_APPROVED, FarmerCalendarEvent::VALIDATION_REJECTED], true)) {
            $status = 'all';
        }

        $query = FarmerCalendarEvent::query()
            ->with(['user', 'lguValidator', 'audits.user'])
            ->where(function (Builder $query) {
                $query->where('category', 'damage_report')
                    ->orWhereNotNull('actual_harvest_recorded_at');
            })
            ->whereIn('lgu_validation_status', [
                FarmerCalendarEvent::VALIDATION_APPROVED,
                FarmerCalendarEvent::VALIDATION_REJECTED,
            ]);

        $this->scopeToValidator($query, $validator);

        if ($status !== 'all') {
            $query->where('lgu_validation_status', $status);
        }

        if ($type === 'damage') {
            $query->where('category', 'damage_report');
        } elseif ($type === 'harvest') {
            $query->whereNotNull('actual_harvest_recorded_at');
        }

        if ($search !== '') {
            $searchLike = '%' . strtolower($search) . '%';
            $query->where(function (Builder $query) use ($searchLike) {
                $query->whereRaw('LOWER(title) LIKE ?', [$searchLike])
                    ->orWhereRaw('LOWER(crop) LIKE ?', [$searchLike])
                    ->orWhereHas('user', function (Builder $userQuery) use ($searchLike) {
                        $userQuery->whereRaw('LOWER(name) LIKE ?', [$searchLike])
                            ->orWhereRaw('LOWER(email) LIKE ?', [$searchLike]);
                    });
            });
        }

        $items = $query
            ->orderByDesc('lgu_validated_at')
            ->orderByDesc('submitted_to_lgu_at')
            ->orderByDesc('created_at')
            ->paginate(15)
            ->withQueryString();

        return view('lgu.records', [
            'items' => $items,
            'status' => $status,
            'type' => $type,
            'search' => $search,
            'validator' => $validator,
        ]);
    }

    public function approve(Request $request, FarmerCalendarEvent $event): Response
    {
        $validated = $request->validate([
            'notes' => 'nullable|string|max:1000',
            'expected_revision' => 'required|integer|min:0',
        ]);

        return app(IdempotentOperationService::class)->execute(
            $request,
            'lgu.validation.approve',
            fn () => $this->applyDecision($request, $event->id, FarmerCalendarEvent::VALIDATION_APPROVED, $validated)
        );
    }

    public function reject(Request $request, FarmerCalendarEvent $event): Response
    {
        $validated = $request->validate([
            'notes' => 'required|string|max:1000',
            'expected_revision' => 'required|integer|min:0',
        ]);

        return app(IdempotentOperationService::class)->execute(
            $request,
            'lgu.validation.reject',
            fn () => $this->applyDecision($request, $event->id, FarmerCalendarEvent::VALIDATION_REJECTED, $validated)
        );
    }

    private function applyDecision(Request $request, int $eventId, string $decision, array $validated): Response
    {
        return DB::transaction(function () use ($request, $eventId, $decision, $validated) {
            $event = FarmerCalendarEvent::query()->lockForUpdate()->findOrFail($eventId);
            $this->authorizeEvent($event);

            $expectedRevision = (int) $validated['expected_revision'];
            $currentRevision = (int) $event->lgu_validation_revision;

            if ($event->lgu_validation_status !== FarmerCalendarEvent::VALIDATION_PENDING || $currentRevision !== $expectedRevision) {
                $payload = [
                    'success' => false,
                    'message' => 'This report changed on the server. Review the latest status before deciding again.',
                    'code' => 'lgu_validation_conflict',
                    'current_server' => [
                        'id' => $event->id,
                        'status' => $event->lgu_validation_status,
                        'revision' => $currentRevision,
                        'notes' => $event->lgu_validation_notes,
                        'validated_at' => $event->lgu_validated_at?->toIso8601String(),
                    ],
                    'queued_decision' => [
                        'status' => $decision,
                        'expected_revision' => $expectedRevision,
                        'notes' => $validated['notes'] ?? null,
                    ],
                ];

                return $request->expectsJson()
                    ? response()->json($payload, 409)
                    : back()->withErrors(['validation' => $payload['message']]);
            }

            $event->update([
                'lgu_validation_status' => $decision,
                'lgu_validated_by' => Auth::id(),
                'lgu_validated_at' => now(),
                'lgu_validation_notes' => $validated['notes'] ?? null,
                'lgu_validation_revision' => $currentRevision + 1,
            ]);

            $action = $decision === FarmerCalendarEvent::VALIDATION_APPROVED ? 'lgu_approved' : 'lgu_rejected';
            $message = $decision === FarmerCalendarEvent::VALIDATION_APPROVED
                ? 'Report approved and marked as LGU verified.'
                : 'Report returned to the farmer for correction.';

            app(EventAuthenticityService::class)->audit($event->fresh(), Auth::user(), $action, [
                'notes' => $validated['notes'] ?? null,
                'authenticity_status' => $event->authenticity_status,
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => $message,
                    'event' => [
                        'id' => $event->id,
                        'lgu_validation_status' => $decision,
                        'lgu_validation_revision' => $currentRevision + 1,
                    ],
                ]);
            }

            return back()->with('success', $message);
        });
    }

    private function authorizeEvent(FarmerCalendarEvent $event): void
    {
        abort_unless(
            $event->category === 'damage_report' || $event->actual_harvest_recorded_at !== null,
            404
        );

        $query = FarmerCalendarEvent::query()->whereKey($event->id);
        $this->scopeToValidator($query, Auth::user());

        abort_unless($query->exists(), 403);
    }

    private function scopeToValidator(Builder $query, $validator): void
    {
        $municipality = $validator?->normalizedLguMunicipality();

        if (! $municipality) {
            $query->whereRaw('1 = 0');

            return;
        }

        $query->whereHas('user', function (Builder $userQuery) use ($municipality) {
            $userQuery->whereRaw('UPPER(preferred_municipality) = ?', [$municipality])
                ->orWhereRaw("UPPER(REPLACE(preferred_municipality, ' ', '')) = ?", [str_replace(' ', '', $municipality)]);
        });
    }
}
