<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FarmerCalendarEvent extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'event_date',
        'event_type',
        'title',
        'description',
        'category',
        'crop',
        'reminder_time',
        'reminder_sent',
        'is_completed',
    ];

    protected $casts = [
        'event_date' => 'date',
        'reminder_time' => 'datetime:H:i',
        'reminder_sent' => 'boolean',
        'is_completed' => 'boolean',
    ];

    /**
     * Get the user that owns the event.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
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
            'pest' => '🐛',
            'harvest' => '🌾',
            'planting' => '🌱',
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
            'pest' => 'red',
            'harvest' => 'green',
            'planting' => 'emerald',
            'fertilizer' => 'blue',
            'weather' => 'yellow',
            'other' => 'gray',
        ];

        return $colors[$this->category] ?? 'gray';
    }
}
