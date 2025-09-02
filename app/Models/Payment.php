<?php

namespace App\Models;

use App\Models\Bank;
use App\Models\User;
use App\Models\Agent;
use App\Enum\WalletPaymentStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'agent_id',
        'payment_gateway_id',
        'depositBank_name',
        'receive_bank_id',
        'branch_name',
        'amount',
        'desposit_date',
        'image',
        'reference',
        'status',
        'action_id'
    ];

    protected $casts = [
        'deposit_date' => 'date',
        'status' => WalletPaymentStatus::class,
    ];

    public function agent(): BelongsTo
    {
        return $this->belongsTo(Agent::class);
    }
    public function paymentgateway(): BelongsTo
    {
        return $this->belongsTo(paymentgateway::class, 'payment_gateway_id', 'id');
    }
    public function receivebank(): BelongsTo
    {
        return $this->belongsTo(Bank::class, 'receive_bank_id', 'id');
    }
    public function actionby(): BelongsTo
    {
        return $this->belongsTo(User::class, 'action_id', 'id');
    }
}
