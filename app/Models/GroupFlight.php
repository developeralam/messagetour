<?php

namespace App\Models;

use App\Models\User;
use App\Enum\GroupFlightType;
use App\Enum\GroupFlightStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class GroupFlight extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = [
        'title',
        'slug',
        'thumbnail',
        'description',
        'type',
        'journey_route',
        'journey_transit',
        'return_route',
        'return_transit',
        'journey_date',
        'return_date',
        'airline_name',
        'airline_code',
        'baggage_weight',
        'is_food',
        'available_seat',
        'status',
        'action_id',
    ];

    protected $casts = [
        'journey_date' => 'date',
        'return_date' => 'date',
        'type' => GroupFlightType::class,
        'status' => GroupFlightStatus::class,
        'is_food' => 'boolean',
    ];
    public function actionBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'action_id', 'id');
    }
    public function getThumbnailLinkAttribute()
    {
        if ($this->thumbnail) {
            return Storage::disk('public')->url('/' . $this->thumbnail);
        }
    }
}
