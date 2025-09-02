<?php

namespace App\Models;

use Carbon\Carbon;
use App\Models\User;
use App\Enum\HotelType;
use App\Models\Country;
use App\Enum\HotelRoomType;
use App\Enum\CorporateQueryStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CorporateQuery extends Model
{
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'destination_country',
        'name',
        'email',
        'phone',
        'group_size',
        'travel_date',
        'program',
        'hotel_type',
        'hotel_room_type',
        'meals',
        'meals_choices',
        'recommend_places',
        'activities',
        'visa_service',
        'air_ticket',
        'tour_guide',
        'status',
        'action_by',
    ];

    protected $casts = [
        'status' => CorporateQueryStatus::class,
        'hotel_type' => HotelType::class,
        'hotel_room_type' => HotelRoomType::class,
        'visa_service' => 'boolean',
        'air_ticket' => 'boolean',
        'tour_guide' => 'boolean',
    ];

    public function destination(): BelongsTo
    {
        return $this->belongsTo(Country::class, 'destination_country');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function actionBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'action_by', 'id');
    }

    public function getFormattedTravelDateAttribute()
    {
        if (!$this->travel_date || !str_contains($this->travel_date, ' to ')) {
            return $this->travel_date; // return as-is if invalid
        }

        [$start, $end] = explode(' to ', $this->travel_date);

        return Carbon::parse($start)->format('d M, Y') . ' to ' . Carbon::parse($end)->format('d M, Y');
    }

    public function getTravelStartDateAttribute()
    {
        if (!$this->travel_date || !str_contains($this->travel_date, ' to ')) {
            return $this->travel_date; // or null
        }

        [$start, $end] = explode(' to ', $this->travel_date);

        return Carbon::parse($start)->format('d M, Y');
    }

    public function getTravelEndDateAttribute()
    {
        if (!$this->travel_date || !str_contains($this->travel_date, ' to ')) {
            return null;
        }

        [$start, $end] = explode(' to ', $this->travel_date);

        return Carbon::parse($end)->format('d M, Y');
    }
}
