<?php

namespace App\Models;

use App\Models\Order;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TravelProductBooking extends Model
{
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['order_id', 'travel_product_id', 'qty'];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
    public function travelproduct(): BelongsTo
    {
        return $this->belongsTo(TravelProduct::class, 'travel_product_id', 'id');
    }
}
