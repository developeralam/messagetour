<?php

namespace App\Models;

use Carbon\Carbon;
use App\Enum\OfferStatus;
use App\Enum\OfferType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Offer extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = [
        'title',
        'type',
        'slug',
        'thumbnail',
        'description',
        'coupon_id',
        'link',
        'applicable_users',
        'avail_this_offer_step_1',
        'avail_this_offer_step_2',
        'avail_this_offer_step_3',
        'action_id',
        'status',
    ];
    protected $casts = [
        'type' => OfferType::class,
        'status' => OfferStatus::class,
    ];
    public function getThumbnailLinkAttribute()
    {
        if ($this->thumbnail) {
            return Storage::disk('public')->url('/' . $this->thumbnail);
        }
    }
    public function scopeActive($query)
    {
        return $query->where('status', OfferStatus::Active);
    }
    public function coupon(): BelongsTo
    {
        return $this->belongsTo(Coupon::class, 'coupon_id', 'id');
    }
    public function actionBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'action_id', 'id');
    }
    public function faqs(): HasMany
    {
        return $this->hasMany(Faq::class, 'offer_id', 'id');
    }
}
