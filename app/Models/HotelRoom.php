<?php

namespace App\Models;

use App\Enum\HotelRoomStatus;
use Carbon\Carbon;
use App\Enum\HotelRoomType;
use App\Models\HotelRoomBookingItems;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Casts\AsCollection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class HotelRoom extends Model
{
    use HasFactory;
    protected $casts = [
        'images' => AsCollection::class,
        'type' => HotelRoomType::class,
        'status' => HotelRoomStatus::class
    ];
    protected $fillable = [
        'hotel_id',
        'name',
        'room_no',
        'slug',
        'type',
        'room_size',
        'max_occupancy',
        'regular_price',
        'offer_price',
        'thumbnail',
        'images',
        'status',
        'created_by',
        'action_id',
    ];
    public function aminities(): BelongsToMany
    {
        return $this->belongsToMany(Aminities::class);
    }
    public function hotel(): BelongsTo
    {
        return $this->belongsTo(Hotel::class);
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

    /**
     * Check if the room is already booked for the given date range.
     *
     * @param string $checkIn
     * @param string $checkOut
     * @return bool
     */
    public function isBookedForDates($checkIn, $checkOut)
    {
        if (empty($checkIn) || empty($checkOut)) {
            return false; // If dates are missing, assume room is available
        }
        // If no bookings exist at all, return false (room is available)
        if (!HotelRoomBooking::exists()) {
            return false;
        }

        // Query the bookings for the room and check for overlapping dates
        return $this->hotelRoomBookingItems()
            ->whereHas('hotelroombooking', function ($query) use ($checkIn, $checkOut) {
                $query->where(function ($query) use ($checkIn, $checkOut) {
                    $query->whereBetween('check_in', [$checkIn, $checkOut])
                        ->orWhereBetween('check_out', [$checkIn, $checkOut])
                        ->orWhere(function ($query) use ($checkIn, $checkOut) {
                            $query->where('check_in', '<=', $checkIn)
                                ->where('check_out', '>=', $checkOut);
                        });
                });
            })
            ->exists();
    }

    // Relationship with hotel room booking items
    public function hotelRoomBookingItems(): HasMany
    {
        return $this->hasMany(HotelRoomBookingItems::class, 'room_id', 'id');
    }
}
