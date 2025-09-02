<?php

namespace App\Models;

use App\Models\HotelRoom;
use App\Models\HotelRoomBooking;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HotelRoomBookingItems extends Model
{
    use SoftDeletes;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'hotel_room_booking_id',
        'room_id',
        'hotel_adult',
        'hotel_child',
        'hotel_infant'
    ];

    public function hotelroombooking(): BelongsTo
    {
        return $this->belongsTo(HotelRoomBooking::class, 'hotel_room_booking_id', 'id');
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(HotelRoom::class, 'room_id', 'id');
    }
}
