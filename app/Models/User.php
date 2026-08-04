<?php

namespace App\Models;

use App\Enums\UserRole;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'name', 'email', 'phone', 'password', 'role', 'status',
        'avatar_url', 'gender', 'date_of_birth', 'city_id',
        'provider', 'provider_id', 'fcm_token',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'phone_verified_at' => 'datetime',
            'password' => 'hashed',
            'role' => UserRole::class,
            'date_of_birth' => 'date',
        ];
    }

    // ---------- Relationships ----------
    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }

    public function ownedGyms(): HasMany
    {
        return $this->hasMany(Gym::class, 'owner_id');
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(GymReview::class);
    }

    public function favorites(): BelongsToMany
    {
        return $this->belongsToMany(Gym::class, 'favorites')->withTimestamps();
    }

    public function memberships(): HasMany
    {
        return $this->hasMany(MembershipHistory::class);
    }

    // ---------- Helpers ----------
    public function isAdmin(): bool
    {
        return $this->role === UserRole::ADMIN;
    }

    public function isGymOwner(): bool
    {
        return $this->role === UserRole::GYM_OWNER;
    }

    public function isCustomer(): bool
    {
        return $this->role === UserRole::CUSTOMER;
    }
}
