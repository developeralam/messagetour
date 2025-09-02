<?php

namespace App\Models;

use Carbon\Carbon;
use App\Enum\TourType;
use App\Models\Review;
use App\Models\Country;
use App\Enum\TourStatus;
use App\Models\District;
use App\Models\Division;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Casts\AsCollection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Tour extends Model
{
    use HasFactory, SoftDeletes;
    protected $casts = [
        'images' => AsCollection::class,
        'is_featured' => 'boolean',
        'status' => TourStatus::class,
        'type' => TourType::class,
        'start_date' => 'date',
        'end_date' => 'date',
        'validity' => 'date',
    ];
    protected $fillable = [
        'title',
        'slug',
        'location',
        'start_date',
        'end_date',
        'member_range',
        'minimum_passenger',
        'description',
        'country_id',
        'division_id',
        'district_id',
        'is_featured',
        'type',
        'regular_price',
        'offer_price',
        'validity',
        'thumbnail',
        'images',
        'created_by',
        'action_id',
        'status',
    ];

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
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }
    public function actionBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'action_id', 'id');
    }
    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }
    public function scopeActive($query)
    {
        return $query->where('status', TourStatus::Active);
    }
    public function scopeCheckValidity($query)
    {
        return $query->where('validity', '>=', Carbon::now());
    }
    public function getThumbnailLinkAttribute()
    {
        if ($this->thumbnail) {
            return Storage::disk('public')->url('/' . $this->thumbnail);
        }
    }
    public function reviews(): MorphMany
    {
        return $this->morphMany(Review::class, 'reviewable');
    }
}
