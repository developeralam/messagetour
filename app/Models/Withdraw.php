<?php

namespace App\Models;

use App\Enum\WithdrawStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Withdraw extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'withdraw_method_id',
        'amount',
        'description',
        'trx_id',
        'agent_id',
        'approved_by',
        'status',
        'action_by',
    ];

    protected $casts = [
        'status' => WithdrawStatus::class
    ];

    public function method(): BelongsTo
    {
        return $this->belongsTo(WithdrawMethod::class, 'withdraw_method_id');
    }
    public function agent(): BelongsTo
    {
        return $this->belongsTo(Agent::class);
    }
    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
    public function actiondBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'action_by');
    }
}
