<?php

namespace App\Services;

use App\Models\CalendarEventAudit;
use App\Models\FarmerCalendarEvent;
use App\Models\User;
use Illuminate\Support\Facades\Schema;

class EventAuthenticityService
{
    private const HIGH_HARVEST_MULTIPLIER = 1.5;
    private const MAX_LOCATION_ACCURACY_METERS = 1000;
    private const MAX_HARVEST_DATE_DRIFT_DAYS = 30;

    public function refresh(FarmerCalendarEvent $event): FarmerCalendarEvent
    {
        if (! $this->supportsAuthenticityColumns()) {
            return $event;
        }

        $flags = $this->evaluate($event);

        $event->forceFill([
            'authenticity_status' => count($flags) > 0
                ? FarmerCalendarEvent::AUTHENTICITY_NEEDS_REVIEW
                : FarmerCalendarEvent::AUTHENTICITY_CLEAR,
            'authenticity_flags' => $flags,
            'authenticity_checked_at' => now(),
        ])->save();

        return $event->fresh();
    }

    public function evaluate(FarmerCalendarEvent $event): array
    {
        $flags = [];
        $isDamageReport = $event->category === 'damage_report';
        $isHarvestReport = $event->actual_harvest_recorded_at !== null
            || $event->actual_harvest_production_mt !== null;

        if (! $isDamageReport && ! $isHarvestReport) {
            return [];
        }

        if ($isDamageReport && ! $event->damage_photo_path && ! $event->evidence_photo_path) {
            $flags[] = $this->flag(
                'missing_photo',
                'Photo is missing.',
                'warning',
                'Ask for a field photo before approving if the report looks unclear.'
            );
        }

        if ($isHarvestReport && ! $event->evidence_photo_path) {
            $flags[] = $this->flag(
                'missing_harvest_photo',
                'Harvest photo is missing.',
                'info',
                'A harvest can still be checked, but photo proof would make it stronger.'
            );
        }

        if (! $event->evidence_latitude || ! $event->evidence_longitude) {
            $flags[] = $this->flag(
                'missing_location',
                'Location proof is missing.',
                'info',
                'The farmer may have denied location access or had a weak connection.'
            );
        } elseif ($event->evidence_accuracy_m !== null && (float) $event->evidence_accuracy_m > self::MAX_LOCATION_ACCURACY_METERS) {
            $flags[] = $this->flag(
                'low_location_accuracy',
                'Location accuracy is weak.',
                'info',
                'The captured GPS location is broad, so verify the farmer details manually.'
            );
        }

        if ($isHarvestReport) {
            $actualHarvest = (float) ($event->actual_harvest_production_mt ?? 0);
            $predictedHarvest = (float) ($event->predicted_production_mt ?? 0);

            if ($actualHarvest > 0 && $predictedHarvest > 0 && $actualHarvest > ($predictedHarvest * self::HIGH_HARVEST_MULTIPLIER)) {
                $flags[] = $this->flag(
                    'high_actual_harvest',
                    'Harvest is much higher than estimate.',
                    'warning',
                    'Check the photo, notes, and farm area before approval.'
                );
            }

            if ($event->actual_harvest_date && $event->estimated_harvest_date) {
                $dateDrift = abs($event->actual_harvest_date->diffInDays($event->estimated_harvest_date, false));

                if ($dateDrift > self::MAX_HARVEST_DATE_DRIFT_DAYS) {
                    $flags[] = $this->flag(
                        'harvest_date_far_from_estimate',
                        'Harvest date is far from estimate.',
                        'info',
                        'This may be valid, but LGU should check the crop timeline.'
                    );
                }
            }
        }

        if ($event->evidence_photo_hash && $this->hasReusedPhoto($event)) {
            $flags[] = $this->flag(
                'reused_photo',
                'Photo was used before.',
                'warning',
                'Review the photo carefully before approval.'
            );
        }

        return $flags;
    }

    public function audit(FarmerCalendarEvent $event, ?User $actor, string $action, array $metadata = []): void
    {
        if (! Schema::hasTable('calendar_event_audits')) {
            return;
        }

        CalendarEventAudit::create([
            'farmer_calendar_event_id' => $event->id,
            'user_id' => $actor?->id,
            'action' => $action,
            'metadata' => $metadata,
        ]);
    }

    private function hasReusedPhoto(FarmerCalendarEvent $event): bool
    {
        if (! Schema::hasColumn('farmer_calendar_events', 'evidence_photo_hash')) {
            return false;
        }

        return FarmerCalendarEvent::query()
            ->where('id', '!=', $event->id)
            ->where('evidence_photo_hash', $event->evidence_photo_hash)
            ->where(function ($query) {
                $query->where('category', 'damage_report')
                    ->orWhereNotNull('actual_harvest_recorded_at');
            })
            ->exists();
    }

    private function supportsAuthenticityColumns(): bool
    {
        return Schema::hasColumn('farmer_calendar_events', 'authenticity_status')
            && Schema::hasColumn('farmer_calendar_events', 'authenticity_flags')
            && Schema::hasColumn('farmer_calendar_events', 'authenticity_checked_at');
    }

    private function flag(string $code, string $label, string $severity, string $message): array
    {
        return compact('code', 'label', 'severity', 'message');
    }
}
