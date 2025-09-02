<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WithdrawMethod extends Model
{
    protected $fillable = [
        'name',
        'charge',
        'status',
        'action_id',
    ];

    protected $casts = [
        'status' => 'boolean'
    ];

    public function actionBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'action_by', 'id');
    }
}
