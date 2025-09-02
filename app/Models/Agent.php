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
    public function getBusinessLogoLinkAttribute()
    {
        if ($this->business_logo) {
            return Storage::disk('public')->url('/' . $this->business_logo);
        }
    }
    public function getTradeLicenceLinkAttribute()
    {
        if ($this->trade_licence) {
            return Storage::disk('public')->url('/' . $this->trade_licence);
        }
    }
    public function getPropiterImageLinkAttribute()
    {
        if ($this->agent_image) {
            return Storage::disk('public')->url('/' . $this->agent_image);
        }
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
    public function actionBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'action_by', 'id');
    }
}
