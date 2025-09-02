<?php

namespace App\Models;

use App\Enum\DistrictStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class District extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'division_id',
        'status'
    ];

    protected $casts = [
        'status' => DistrictStatus::class
    ];

    public function division(): BelongsTo
    {
        return $this->belongsTo(Division::class);
    }
}
