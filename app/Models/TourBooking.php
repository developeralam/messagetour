<?php

namespace App\Models;

use App\Models\Tour;
use App\Models\Order;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TourBooking extends Model
{
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'order_id',
        'tour_id',
    ];

    public function order():BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
    public function tour():BelongsTo
    {
        return $this->belongsTo(Tour::class);
    }
}
