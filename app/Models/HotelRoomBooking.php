<?php

namespace App\Models;

use App\Models\Order;
use App\Enum\BookingStatus;
use App\Models\HotelRoomBookingItems;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HotelRoomBooking extends Model
{
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'order_id',
        'check_in',
        'check_out',
        'status',
    ];

    protected $casts = [
        'check_in' => 'date',
        'check_out' => 'date',
        'status' => BookingStatus::class,
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function hotelbookingitems(): HasMany
    {
        return $this->hasMany(HotelRoomBookingItems::class, 'hotel_room_booking_id', 'id');
    }
}
