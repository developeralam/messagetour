<?php

namespace App\Models;

use App\Enum\CountryStatus;
use App\Models\Division;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Country extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'status'
    ];

    protected $casts = [
        'status' => CountryStatus::class
    ];

    public function divisions(): HasMany
    {
        return $this->hasMany(Division::class, 'country_id', 'id');
    }
}
