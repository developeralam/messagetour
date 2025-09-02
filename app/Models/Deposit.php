<?php

namespace App\Models;

use App\Models\Bank;
use App\Models\User;
use App\Models\Agent;
use App\Enum\DepositType;
use App\Enum\DepositStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Deposit extends Model
{
    use SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $fillable = [
        'agent_id',
        'amount',
        'trx_id',
        'payment_type',
        'deposit_form',
        'deposit_to',
        'branch',
        'reference_number',
        'payment_slip',
        'deposit_date',
        'status',
        'created_by',
        'action_by',
    ];

    protected $casts = [
        'status' => DepositStatus::class,
        'payment_type' => DepositType::class,
        'deposit_date' => 'date'
    ];

    public function agent(): BelongsTo
    {
        return $this->belongsTo(Agent::class);
    }

    public function actionBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'action_by', 'id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }

    public function depositTo(): BelongsTo
    {
        return $this->belongsTo(Bank::class, 'deposit_to', 'id');
    }
}
