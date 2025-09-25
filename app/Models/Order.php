<?php

namespace App\Models;

use App\Models\User;
use App\Models\Coupon;
use App\Models\Country;
use App\Models\District;
use App\Models\Division;
use App\Enum\OrderStatus;
use App\Enum\PaymentStatus;
use App\Enum\ShippingMethod;
use App\Models\PaymentGateway;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Order extends Model
{
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'name',
        'email',
        'phone',
        'address',
        'division_id',
        'district_id',
        'country_id',
        'zipcode',
        'coupon_id',
        'coupon_amount',
        'subtotal',
        'delivery_charge',
        'shipping_charge',
        'shipping_method',
        'total_amount',
        'tran_id',
        'status',
        'payment_gateway_id',
        'payment_status',
        'action_by'
    ];

    protected $casts = [
        'status' => OrderStatus::class,
        'payment_status' => PaymentStatus::class,
        'shipping_method' => ShippingMethod::class,
    ];

    public function sourceable()
    {
        return $this->morphTo();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
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

    public function actionby(): BelongsTo
    {
        return $this->belongsTo(User::class, "action_by", "id");
    }

    public function coupon(): BelongsTo
    {
        return $this->belongsTo(Coupon::class);
    }

    public function paymentgateway(): BelongsTo
    {
        return $this->belongsTo(PaymentGateway::class, "payment_gateway_id", "id");
    }

    public function hotelRoomBookings(): HasMany
    {
        return $this->hasMany(HotelRoomBooking::class);
    }

    public function tourBookings(): HasMany
    {
        return $this->hasMany(TourBooking::class);
    }

    public function carBookings(): HasMany
    {
        return $this->hasMany(CarBooking::class);
    }

    public function visaBookings(): HasMany
    {
        return $this->hasMany(VisaBooking::class);
    }

    public function travelProductBookings(): HasMany
    {
        return $this->hasMany(TravelProductBooking::class);
    }

    public function agentPaymentRequests(): HasMany
    {
        return $this->hasMany(AgentPaymentRequest::class);
    }
}
