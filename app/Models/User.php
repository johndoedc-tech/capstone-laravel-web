<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'google_id',
        'avatar',
        'role',
        'must_change_password',
        'preferred_municipality',
        'cooperative',
        'favorite_crops',
    ];

    /**
     * Check if user is admin
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /**
     * Check if user is farmer
     */
    public function isFarmer(): bool
    {
        return $this->role === 'farmer';
    }

    /**
     * Check if the farmer has completed onboarding (municipality & cooperative).
     */
    public function hasCompletedOnboarding(): bool
    {
        return ! empty($this->preferred_municipality) && ! empty($this->cooperative);
    }

    /**
     * Check if the farmer still needs to complete onboarding.
     */
    public function needsOnboarding(): bool
    {
        return ! $this->hasCompletedOnboarding();
    }

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'must_change_password' => 'boolean',
            'favorite_crops' => 'array',
        ];
    }

    /**
     * Get the predictions for the user
     */
    public function predictions()
    {
        return $this->hasMany(Prediction::class);
    }
}
