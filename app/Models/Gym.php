<?php

namespace App\Models;

use App\Enums\GymStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Scout\Searchable;

class Gym extends Model
{
    use HasFactory, Searchable, SoftDeletes;

    protected $fillable = [
        'owner_id', 'city_id', 'name', 'slug', 'description', 'address', 'area', 'postal_code',
        'latitude', 'longitude', 'phone', 'email', 'website', 'cover_image',
        'opening_time', 'closing_time', 'is_24x7', 'ladies_only', 'gender_preference',
        'trainer_count', 'crowd_level', 'peak_hours', 'starting_price',
        'status', 'rejection_reason', 'is_verified', 'is_featured', 'approved_at',
        'source', 'source_id', 'source_url', 'last_synced_at',
    ];

    protected function casts(): array
    {
        return [
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
            'peak_hours' => 'array',
            'is_24x7' => 'boolean',
            'ladies_only' => 'boolean',
            'is_verified' => 'boolean',
            'is_featured' => 'boolean',
            'starting_price' => 'decimal:2',
            'rating_avg' => 'decimal:2',
            'status' => GymStatus::class,
            'approved_at' => 'datetime',
        ];
    }

    // ---------- Relationships ----------
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(GymImage::class)->orderBy('sort_order');
    }

    public function videos(): HasMany
    {
        return $this->hasMany(GymVideo::class);
    }

    public function trainers(): HasMany
    {
        return $this->hasMany(GymTrainer::class);
    }

    public function plans(): HasMany
    {
        return $this->hasMany(GymPlan::class)->where('is_active', true);
    }

    public function amenities(): BelongsToMany
    {
        return $this->belongsToMany(Amenity::class, 'gym_amenity');
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(GymReview::class)->where('status', 'published');
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function offers(): HasMany
    {
        return $this->hasMany(Offer::class)->where('is_active', true);
    }

    public function favoritedBy(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'favorites')->withTimestamps();
    }

    // ---------- Scout ----------
    public function toSearchableArray(): array
    {
        return [
            'id' => (int) $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'address' => $this->address,
            'area' => $this->area,
            'city_id' => (int) $this->city_id,
            'city' => $this->city?->name,
            'latitude' => (float) $this->latitude,
            'longitude' => (float) $this->longitude,
            'rating_avg' => (float) $this->rating_avg,
            'starting_price' => (float) $this->starting_price,
            'is_verified' => $this->is_verified,
            'status' => $this->status?->value,
        ];
    }

    public function shouldBeSearchable(): bool
    {
        return $this->status === GymStatus::APPROVED;
    }

    // ---------- Query Scopes ----------
    public function scopeApproved($q)
    {
        return $q->where('status', GymStatus::APPROVED->value);
    }

    /**
     * Haversine distance in kilometers.
     */
    public function scopeNearby($q, float $lat, float $lng, float $radiusKm = 10)
    {
        return $q->selectRaw(
            "*, ( 6371 * acos( cos(radians(?)) * cos(radians(latitude))
             * cos(radians(longitude) - radians(?))
             + sin(radians(?)) * sin(radians(latitude)) ) ) AS distance_km",
            [$lat, $lng, $lat]
        )->havingRaw('distance_km <= ?', [$radiusKm])
         ->orderBy('distance_km');
    }
}
