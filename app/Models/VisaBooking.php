<?php

namespace App\Models;

use App\Models\Visa;
use App\Models\Order;
use App\Models\EvisaBookingDetails;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VisaBooking extends Model
{
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'order_id',
        'visa_id',
        'total_traveller',
        'docuemnts_collection_date'
    ];

    protected $casts = [
        'docuemnts_collection_date' => 'date'
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
    public function visa(): BelongsTo
    {
        return $this->belongsTo(Visa::class);
    }
    public function evisa_booking_detail(): HasOne
    {
        return $this->hasOne(EvisaBookingDetails::class, 'visa_booking_id', 'id');
    }
}
