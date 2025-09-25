<?php

namespace App\Models;

use App\Models\User;
use App\Models\Country;
use App\Models\District;
use App\Models\Division;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Customer extends Model
{
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'address',
        'image',
        'country_id',
        'division_id',
        'district_id',
        'secondary_address',
        'action_by',
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

    public function getImageLinkAttribute()
    {
        if ($this->image) {
            // Direct file path for better compatibility
            $filePath = public_path('storage/' . $this->image);
            if (file_exists($filePath)) {
                return url('storage/' . $this->image);
            }
            // Fallback to Storage URL
            return Storage::disk('public')->url($this->image);
        }
        return asset('empty-user.jpg');
    }

    public function actionBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'action_by', 'id');
    }
}
