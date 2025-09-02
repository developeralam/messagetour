<?php

namespace App\Models;

use Carbon\Carbon;
use App\Models\User;
use App\Enum\CarType;
use App\Enum\CarStatus;
use App\Enum\RentalType;
use App\Models\Country;
use App\Models\District;
use App\Models\Division;
use App\Models\CarBooking;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Car extends Model
{
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'title',
        'slug',
        'model_year',
        'image',
        'seating_capacity',
        'car_cc',
        'car_type',
        'color',
        'include_with_pricing',
        'exclude_with_pricing',
        'area_limitation',
        'max_distance',
        'ac_facility',
        'extra_time_cost_by_hour',
        'extra_time_cost',
        'status',
        'created_by',
        'action_id',
        'country_id',
        'division_id',
        'district_id',
        'price_2_hours',
        'price_4_hours',
        'price_half_day',
        'price_day',
        'price_per_day',
        'service_type',
        'with_driver'
    ];

    protected $casts = [
        'car_type' => CarType::class,
        'status' => CarStatus::class,
        'with_driver' => RentalType::class,
        'ac_facility' => 'boolean'
    ];

    public function createdby(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }
    public function actionby(): BelongsTo
    {
        return $this->belongsTo(User::class, 'action_id', 'id');
    }

    public function getImageLinkAttribute()
    {
        if ($this->image) {
            return Storage::disk('public')->url('/' . $this->image);
        }
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(CarBooking::class, 'car_id', 'id');
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class, 'country_id', 'id');
    }

    public function division(): BelongsTo
    {
        return $this->belongsTo(Division::class, 'division_id', 'id');
    }

    public function district(): BelongsTo
    {
        return $this->belongsTo(District::class, 'district_id', 'id');
    }

    public function scopeAvailableBetween($query, $pickupDateTime, $dropoffDateTime)
    {
        return $query->whereDoesntHave('bookings', function ($q) use ($pickupDateTime, $dropoffDateTime) {
            $q->where(function ($q2) use ($pickupDateTime, $dropoffDateTime) {
                $q2->where(function ($sub) use ($pickupDateTime, $dropoffDateTime) {
                    // Assuming 'pickup_datetime' and 'return_datetime' are in 'car_bookings'
                    $sub->whereRaw("pickup_datetime <= ?", [$dropoffDateTime])
                        ->whereRaw("return_datetime >= ?", [$pickupDateTime]);
                });
            });
        });
    }



    // public function scopeAvailableForBooking(Builder $query, $rentType, $pickupDateTime)
    // {
    //     return $query->where('rent_type', $rentType)
    //         ->where('status', CarStatus::Available)
    //         ->whereNotIn('id', function ($subQuery) use ($pickupDateTime, $rentType) {
    //             $subQuery->select('car_id')
    //                 ->from('car_bookings')
    //                 ->where('pickup_datetime', Carbon::parse($pickupDateTime)->format('Y-m-d H:i:s'))
    //                 ->where('rent_type', $rentType);
    //         });
    // }
}
