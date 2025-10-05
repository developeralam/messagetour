<?php

namespace App\Models;

use App\Models\User;
use App\Models\Country;
use App\Models\District;
use App\Models\Division;
use App\Enum\AgentStatus;
use App\Enum\AgentType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Agent extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'agent_type',
        'agent_image',
        'business_name',
        'business_phone',
        'business_email',
        'business_logo',
        'propiter_nid',
        'propiter_etin_no',
        'trade_licence',
        'business_address',
        'primary_contact_address',
        'secondary_contact_address',
        'zipcode',
        'validity',
        'country_id',
        'division_id',
        'district_id',
        'wallet',
        'status',
        'credit_limit',
        'action_by',
    ];

    protected $casts = [
        'status' => AgentStatus::class,
        'agent_type' => AgentType::class,
        'validity' => 'date'
    ];

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

    // Alias for city (using district)
    public function city()
    {
        return $this->district();
    }
    public function getBusinessLogoLinkAttribute()
    {
        if ($this->business_logo) {
            // Direct file path for better compatibility
            $filePath = public_path('storage/' . $this->business_logo);
            if (file_exists($filePath)) {
                return url('storage/' . $this->business_logo);
            }
            // Fallback to Storage URL
            return Storage::disk('public')->url($this->business_logo);
        }
        return asset('empty-user.jpg');
    }
    public function getTradeLicenceLinkAttribute()
    {
        if ($this->trade_licence) {
            // Direct file path for better compatibility
            $filePath = public_path('storage/' . $this->trade_licence);
            if (file_exists($filePath)) {
                return url('storage/' . $this->trade_licence);
            }
            // Fallback to Storage URL
            return Storage::disk('public')->url($this->trade_licence);
        }
        return null;
    }

    // Business information attributes for PDF reports
    public function getNameAttribute()
    {
        return $this->business_name;
    }

    public function getMobileAttribute()
    {
        return $this->business_phone;
    }

    public function getEmailAttribute()
    {
        return $this->business_email;
    }

    public function getWebsiteAttribute()
    {
        return $this->business_email; // Using email as website fallback
    }

    public function getLogoLinkAttribute()
    {
        return $this->business_logo_link;
    }
    public function getPropiterImageLinkAttribute()
    {
        if ($this->agent_image) {
            // Direct file path for better compatibility
            $filePath = public_path('storage/' . $this->agent_image);
            if (file_exists($filePath)) {
                return url('storage/' . $this->agent_image);
            }
            // Fallback to Storage URL
            return Storage::disk('public')->url($this->agent_image);
        }
        return asset('empty-user.jpg');
    }
    public function actionBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'action_by', 'id');
    }

    public function paymentRequests(): HasMany
    {
        return $this->hasMany(AgentPaymentRequest::class);
    }
}
