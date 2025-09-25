<?php

namespace App\Models;

use App\Models\Review;
use App\Enum\HotelType;
use App\Enum\HotelStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Casts\AsCollection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Hotel extends Model
{
    use HasFactory, SoftDeletes;
    protected $casts = [
        'images' => AsCollection::class,
        'is_featured' => 'boolean',
        'type' => HotelType::class,
        'status' => HotelStatus::class,
        'checkin_time' => 'datetime',
        'checkout_time' => 'datetime',
    ];
    protected $fillable = [
        'name',
        'slug',
        'address',
        'country_id',
        'division_id',
        'district_id',
        'zipcode',
        'phone',
        'email',
        'website',
        'checkin_time',
        'checkout_time',
        'is_featured',
        'description',
        'type',
        'thumbnail',
        'images',
        'google_map_iframe',
        'status',
        'created_by',
        'action_id',
    ];

    public function rooms(): HasMany
    {
        return $this->hasMany(HotelRoom::class, 'hotel_id');
    }
    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }
    public function division(): BelongsTo
    {
        return $this->belongsTo(Division::class);
    }
    public function district(): BelongsTo
    {
        return $this->belongsTo(District::class);
    }
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }
    public function actionBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'action_id', 'id');
    }
    public function getThumbnailLinkAttribute()
    {
        if ($this->thumbnail) {
            // Direct file path for better compatibility
            $filePath = public_path('storage/' . $this->thumbnail);
            if (file_exists($filePath)) {
                return url('storage/' . $this->thumbnail);
            }
            // Fallback to Storage URL
            return Storage::disk('public')->url($this->thumbnail);
        }
        return asset('empty-hotel.png');
    }

    public function getImagesLinkAttribute()
    {
        if ($this->images && $this->images->isNotEmpty()) {
            return $this->images->map(function ($image) {
                if (isset($image['url'])) {
                    // Check if it's already a full URL or just a path
                    if (str_starts_with($image['url'], 'http')) {
                        return $image['url'];
                    }

                    // If it's a path, check if file exists and return proper URL
                    $filePath = public_path('storage/' . $image['url']);
                    if (file_exists($filePath)) {
                        return url('storage/' . $image['url']);
                    }

                    // Fallback to Storage URL
                    return Storage::disk('public')->url($image['url']);
                }
                return null;
            })->filter()->values();
        }
        return collect();
    }
    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }
    public function reviews(): MorphMany
    {
        return $this->morphMany(Review::class, 'reviewable');
    }
    /**
     * Get the average rating of the hotel.
     *
     * @return float|null
     */
    public function averageRating()
    {
        return $this->reviews()->avg('rating');  // This will return the average rating
    }
}
