<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Prediction extends Model
{
    protected $fillable = [
        'user_id',
        'batch_id',
        'municipality',
        'farm_type',
        'year',
        'month',
        'crop',
        'area_planted_ha',
        'area_harvested_ha',
        'productivity_mt_ha',
        'predicted_production_mt',
        'expected_from_productivity',
        'difference',
        'confidence_score',
        'api_response_time_ms',
        'status',
        'error_message',
    ];

    protected $casts = [
        'year' => 'integer',
        'month' => 'integer',
        'area_planted_ha' => 'decimal:2',
        'area_harvested_ha' => 'decimal:2',
        'productivity_mt_ha' => 'decimal:2',
        'predicted_production_mt' => 'decimal:2',
        'expected_from_productivity' => 'decimal:2',
        'difference' => 'decimal:2',
        'confidence_score' => 'decimal:4',
        'api_response_time_ms' => 'integer',
    ];

    /**
     * Get the user that owns the prediction
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope to get predictions for a specific user
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope to get successful predictions only
     */
    public function scopeSuccessful($query)
    {
        return $query->where('status', 'success');
    }

    /**
     * Scope to get predictions by crop
     */
    public function scopeByCrop($query, $crop)
    {
        return $query->where('crop', $crop);
    }

    /**
     * Scope to get predictions by municipality
     */
    public function scopeByMunicipality($query, $municipality)
    {
        return $query->where('municipality', $municipality);
    }
}
