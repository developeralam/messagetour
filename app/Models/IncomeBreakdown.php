<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IncomeBreakdown extends Model
{
    protected $fillable = [
        'income_id',
        'title',
        'amount',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    public function income(): BelongsTo
    {
        return $this->belongsTo(Income::class);
    }
}
