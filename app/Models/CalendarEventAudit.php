<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CalendarEventAudit extends Model
{
    use HasFactory;

    protected $fillable = [
        'farmer_calendar_event_id',
        'user_id',
        'action',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    public function event()
    {
        return $this->belongsTo(FarmerCalendarEvent::class, 'farmer_calendar_event_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
