<?php

namespace App\Http\Controllers;

use App\Models\FarmerCalendarEvent;
use App\Services\EventAuthenticityService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

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

    public function approve(Request $request, FarmerCalendarEvent $event): RedirectResponse
    {
        $this->authorizeEvent($event);

        $validated = $request->validate([
            'notes' => 'nullable|string|max:1000',
        ]);

        $event->update([
            'lgu_validation_status' => FarmerCalendarEvent::VALIDATION_APPROVED,
            'lgu_validated_by' => Auth::id(),
            'lgu_validated_at' => now(),
            'lgu_validation_notes' => $validated['notes'] ?? null,
            'lgu_validation_revision' => (int) $event->lgu_validation_revision + 1,
        ]);

        app(EventAuthenticityService::class)->audit($event->fresh(), Auth::user(), 'lgu_approved', [
            'notes' => $validated['notes'] ?? null,
            'authenticity_status' => $event->authenticity_status,
        ]);

        return back()->with('success', 'Report approved and marked as LGU verified.');
    }

    public function reject(Request $request, FarmerCalendarEvent $event): RedirectResponse
    {
        $this->authorizeEvent($event);

        $validated = $request->validate([
            'notes' => 'required|string|max:1000',
        ]);

        $event->update([
            'lgu_validation_status' => FarmerCalendarEvent::VALIDATION_REJECTED,
            'lgu_validated_by' => Auth::id(),
            'lgu_validated_at' => now(),
            'lgu_validation_notes' => $validated['notes'],
            'lgu_validation_revision' => (int) $event->lgu_validation_revision + 1,
        ]);

        app(EventAuthenticityService::class)->audit($event->fresh(), Auth::user(), 'lgu_rejected', [
            'notes' => $validated['notes'],
            'authenticity_status' => $event->authenticity_status,
        ]);

        return back()->with('success', 'Report returned to the farmer for correction.');
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
