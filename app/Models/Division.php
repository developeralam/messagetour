<?php

namespace App\Models;

use App\Enum\DivisionStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Division extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'country_id',
        'status'
    ];

    protected $casts = [
        'status' => DivisionStatus::class
    ];

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    public function districts(): HasMany
    {
        return $this->hasMany(District::class, 'division_id', 'id');
    }
}
