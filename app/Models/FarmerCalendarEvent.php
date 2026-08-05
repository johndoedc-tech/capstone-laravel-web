<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FarmerCalendarEvent extends Model
{
    use HasFactory;

    public const VALIDATION_PENDING = 'pending';
    public const VALIDATION_APPROVED = 'approved';
    public const VALIDATION_REJECTED = 'rejected';

    public const AUTHENTICITY_UNCHECKED = 'unchecked';
    public const AUTHENTICITY_CLEAR = 'clear';
    public const AUTHENTICITY_NEEDS_REVIEW = 'needs_review';

    public const VALIDATION_STATUS_LABELS = [
        self::VALIDATION_PENDING => 'Pending LGU validation',
        self::VALIDATION_APPROVED => 'LGU approved',
        self::VALIDATION_REJECTED => 'Needs correction',
    ];

    public const AUTHENTICITY_STATUS_LABELS = [
        self::AUTHENTICITY_UNCHECKED => 'Evidence not checked',
        self::AUTHENTICITY_CLEAR => 'Evidence OK',
        self::AUTHENTICITY_NEEDS_REVIEW => 'Needs review',
    ];

    protected $fillable = [
        'user_id',
        'event_date',
        'event_type',
        'title',
        'description',
        'category',
        'crop',
        'desired_area_sqm',
        'damage_area_sqm',
        'water_source',
        'planting_material',
        'estimated_harvest_date',
        'estimated_harvest_days',
        'harvest_event_id',
        'crop_plan_event_id',
        'crop_plan_stage',
        'predicted_production_mt',
        'prediction_confidence',
        'prediction_source',
        'actual_harvest_date',
        'actual_harvest_amount',
        'actual_harvest_unit',
        'actual_harvest_production_mt',
        'actual_harvest_notes',
        'actual_harvest_recorded_at',
        'reminder_time',
        'reminder_sent',
        'is_completed',
        'lgu_validation_status',
        'lgu_validated_by',
        'lgu_validated_at',
        'lgu_validation_notes',
        'lgu_validation_revision',
        'submitted_to_lgu_at',
        'damage_photo_path',
        'damage_photo_original_name',
        'evidence_photo_path',
        'evidence_photo_original_name',
        'evidence_photo_hash',
        'evidence_latitude',
        'evidence_longitude',
        'evidence_accuracy_m',
        'evidence_captured_at',
        'evidence_user_agent',
        'authenticity_status',
        'authenticity_flags',
        'authenticity_checked_at',
        'authenticity_notes',
    ];

    protected $casts = [
        'event_date' => 'date',
        'desired_area_sqm' => 'decimal:2',
        'damage_area_sqm' => 'decimal:2',
        'estimated_harvest_date' => 'date',
        'estimated_harvest_days' => 'integer',
        'predicted_production_mt' => 'decimal:2',
        'prediction_confidence' => 'decimal:4',
        'actual_harvest_date' => 'date',
        'actual_harvest_amount' => 'decimal:2',
        'actual_harvest_production_mt' => 'decimal:4',
        'actual_harvest_recorded_at' => 'datetime',
        'reminder_time' => 'datetime:H:i',
        'reminder_sent' => 'boolean',
        'is_completed' => 'boolean',
        'lgu_validated_at' => 'datetime',
        'lgu_validation_revision' => 'integer',
        'submitted_to_lgu_at' => 'datetime',
        'evidence_latitude' => 'decimal:7',
        'evidence_longitude' => 'decimal:7',
        'evidence_accuracy_m' => 'decimal:2',
        'evidence_captured_at' => 'datetime',
        'authenticity_flags' => 'array',
        'authenticity_checked_at' => 'datetime',
    ];

    /**
     * Get the user that owns the event.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function lguValidator()
    {
        return $this->belongsTo(User::class, 'lgu_validated_by');
    }

    public function audits()
    {
        return $this->hasMany(CalendarEventAudit::class)->latest();
    }

    public function getLguValidationStatusLabelAttribute(): string
    {
        return self::VALIDATION_STATUS_LABELS[$this->lgu_validation_status]
            ?? str_replace('_', ' ', ucfirst((string) $this->lgu_validation_status));
    }

    public function getAuthenticityStatusLabelAttribute(): string
    {
        return self::AUTHENTICITY_STATUS_LABELS[$this->authenticity_status]
            ?? str_replace('_', ' ', ucfirst((string) $this->authenticity_status));
    }

    public function isLguPending(): bool
    {
        return $this->lgu_validation_status === self::VALIDATION_PENDING;
    }

    public function isLguApproved(): bool
    {
        return $this->lgu_validation_status === self::VALIDATION_APPROVED;
    }

    public function isLguRejected(): bool
    {
        return $this->lgu_validation_status === self::VALIDATION_REJECTED;
    }

    /**
     * Scope to get events for a specific month.
     */
    public function scopeForMonth($query, $year, $month)
    {
        return $query->whereYear('event_date', $year)
                     ->whereMonth('event_date', $month);
    }

    /**
     * Scope to get today's reminders that haven't been sent.
     */
    public function scopePendingReminders($query)
    {
        return $query->where('event_type', 'reminder')
                     ->where('reminder_sent', false)
                     ->whereDate('event_date', now()->toDateString());
    }

    /**
     * Get category icon
     */
    public function getCategoryIconAttribute()
    {
        $icons = [
            'damage_report' => '!',
            'pest' => '🐛',
            'harvest' => '🌾',
            'planting' => '🌱',
            'crop_plan' => '🌱',
            'fertilizer' => '💧',
            'weather' => '🌤️',
            'other' => '📝',
        ];

        return $icons[$this->category] ?? '📝';
    }

    /**
     * Get category color
     */
    public function getCategoryColorAttribute()
    {
        $colors = [
            'damage_report' => 'red',
            'pest' => 'red',
            'harvest' => 'green',
            'planting' => 'emerald',
            'crop_plan' => 'emerald',
            'fertilizer' => 'blue',
            'weather' => 'yellow',
            'other' => 'gray',
        ];

        return $colors[$this->category] ?? 'gray';
    }
}
