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

    public const ROLE_ADMIN = 'admin';
    public const ROLE_FARMER = 'farmer';
    public const ROLE_LGU_VALIDATOR = 'lgu_validator';

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
        'lgu_municipality',
        'lgu_barangay',
        'is_active',
    ];

    /**
     * Check if user is admin
     */
    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    /**
     * Check if user is farmer
     */
    public function isFarmer(): bool
    {
        return $this->role === self::ROLE_FARMER;
    }

    public function isLguValidator(): bool
    {
        return $this->role === self::ROLE_LGU_VALIDATOR;
    }

    public function isActiveLguValidator(): bool
    {
        return $this->isLguValidator() && (bool) $this->is_active;
    }

    public function normalizedLguMunicipality(): ?string
    {
        $municipality = trim((string) $this->lgu_municipality);

        return $municipality !== '' ? strtoupper($municipality) : null;
    }

    public function normalizedLguBarangay(): ?string
    {
        $barangay = trim((string) $this->lgu_barangay);

        return $barangay !== '' ? strtoupper($barangay) : null;
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
            'is_active' => 'boolean',
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
