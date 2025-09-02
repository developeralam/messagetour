<?php

namespace App\Models;

use App\Models\ChartOfAccount;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OtherTransaction extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'payment_date',
        'receive_from',
        'post_to',
        'amount',
        'note',
    ];

    public function receiveFrom(): BelongsTo
    {
        return $this->belongsTo(ChartOfAccount::class, 'receive_from', 'id');
    }

    public function postTo(): BelongsTo
    {
        return $this->belongsTo(ChartOfAccount::class, 'post_to', 'id');
    }
}
