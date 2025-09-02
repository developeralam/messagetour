<?php

namespace App\Models;

use App\Enum\TravelProductStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TravelProduct extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = [
        'title',
        'slug',
        'sku',
        'brand',
        'description',
        'regular_price',
        'offer_price',
        'thumbnail',
        'stock',
        'is_featured',
        'created_by',
        'action_id',
        'status',
    ];
    protected $casts = [
        'is_featured' => 'boolean',
        'status' => TravelProductStatus::class

    ];
    public function getThumbnailLinkAttribute()
    {
        if ($this->thumbnail) {
            return Storage::disk('public')->url('/' . $this->thumbnail);
        }
    }
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }
    public function actionBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'action_id', 'id');
    }
}
