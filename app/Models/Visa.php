<?php

namespace App\Models;

use App\Enum\VisaType;
use App\Enum\VisaStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Visa extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = [
        'title',
        'slug',
        'sku_code',
        'origin_country',
        'destination_country',
        'processing_time',
        'application_form',
        'convenient_fee',
        'basic_info',
        'depurture_requirements',
        'destination_requirements',
        'checklists',
        'faq',
        'type',
        'application_form',
        'created_by',
        'action_id',
        'status',
    ];

    protected $casts = [
        'type' => VisaType::class,
        'status' => VisaStatus::class,
    ];

    public function origin(): BelongsTo
    {
        return $this->belongsTo(Country::class, 'origin_country');
    }
    public function destination(): BelongsTo
    {
        return $this->belongsTo(Country::class, 'destination_country');
    }
    public function visaFees(): HasMany
    {
        return $this->hasMany(VisaFee::class, 'visa_id', 'id');
    }
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }
    public function actionBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'action_id', 'id');
    }
    public function getApplicationFormLinkAttribute()
    {
        if ($this->application_form) {
            return Storage::disk('public')->url('/' . $this->application_form);
        }
    }
    // Helper method to get the visa type name
    public function getTypeNameAttribute(): string
    {
        return $this->type->label();
    }
}
