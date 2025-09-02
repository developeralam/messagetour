<?php

namespace App\Models;

use App\Models\Car;
use App\Models\Order;
use App\Models\Country;
use App\Models\District;
use App\Models\Division;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CarBooking extends Model
{
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'order_id',
        'car_id',
        'pickup_country_id',
        'pickup_division_id',
        'pickup_district_id',
        'dropoff_district_id',
        'pickup_datetime',
        'return_datetime',
    ];

    protected $casts = [
        'pickup_datetime' => 'datetime',
        'return_datetime' => 'datetime',
    ];

    public function car(): BelongsTo
    {
        return $this->belongsTo(Car::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function pickupCountry(): BelongsTo
    {
        return $this->belongsTo(Country::class, 'pickup_country_id');
    }

    public function pickupDivision(): BelongsTo
    {
        return $this->belongsTo(Division::class, 'pickup_division_id');
    }

    public function pickupDistrict(): BelongsTo
    {
        return $this->belongsTo(District::class, 'pickup_district_id');
    }

    public function dropoffCity(): BelongsTo
    {
        return $this->belongsTo(District::class, 'dropoff_district_id');
    }
}
