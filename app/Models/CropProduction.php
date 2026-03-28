<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CropProduction extends Model
{
    protected $table = 'crop_production';
    
    protected $fillable = [
        'municipality',
        'farm_type',
        'year',
        'month',
        'crop',
        'area_planted',
        'area_harvested',
        'production',
        'productivity'
    ];
    
    protected $casts = [
        'year' => 'integer',
        'area_planted' => 'float',
        'area_harvested' => 'float',
        'production' => 'float',
        'productivity' => 'float',
    ];
    
    // Query scopes for easy filtering
    public function scopeMunicipality($query, $municipality)
    {
        return $query->where('municipality', $municipality);
    }
    
    public function scopeCrop($query, $crop)
    {
        return $query->where('crop', $crop);
    }
    
    public function scopeYear($query, $year)
    {
        return $query->where('year', $year);
    }
    
    public function scopeFarmType($query, $farmType)
    {
        return $query->where('farm_type', $farmType);
    }
    
    public function scopeMonth($query, $month)
    {
        return $query->where('month', $month);
    }
}
