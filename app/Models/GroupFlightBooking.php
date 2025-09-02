<?php

namespace App\Models;

use App\Models\User;
use App\Models\GroupFlight;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GroupFlightBooking extends Model
{
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'group_flight_id',
        'visa',
        'passport'
    ];

    public function groupflight(): BelongsTo
    {
        return $this->belongsTo(GroupFlight::class, 'group_flight_id', 'id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
