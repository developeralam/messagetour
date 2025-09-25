<?php

namespace App\Models;

use App\Models\User;
use App\Models\Agent;
use App\Models\Customer;
use App\Models\ChartOfAccount;
use App\Enum\TransactionStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Income extends Model
{
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'customer_id',
        'agent_id',
        'account_id',
        'amount',
        'reference',
        'remarks',
        'payment_slip',
        'status',
        'created_by',
        'action_by',
    ];

    protected $casts = [
        'status' => TransactionStatus::class,
    ];

    /**
     * Get the customer that owns the income.
     */
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
    /**
     * Get the customer that owns the income.
     */
    public function agent()
    {
        return $this->belongsTo(Agent::class);
    }
    /**
     * Get the account associated with the income.
     */
    public function account()
    {
        return $this->belongsTo(ChartOfAccount::class, 'account_id');
    }
    /**
     * Get the user who created the income.
     */
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who performed the action.
     */
    public function actionBy()
    {
        return $this->belongsTo(User::class, 'action_by');
    }
}
