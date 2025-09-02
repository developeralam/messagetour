<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class VisaFee extends Model
{
    use SoftDeletes;
    
    protected $fillable = [
        'visa_id',
        'fee_type',
        'fee'
    ];
    public function visa(): BelongsTo
    {
        return $this->belongsTo(Visa::class);
    }
}
