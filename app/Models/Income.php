<?php

namespace App\Models;

use App\Models\User;
use App\Models\Agent;
use App\Models\Customer;
use App\Models\ChartOfAccount;
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
        'action_by',
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
     * Get the user who performed the action.
     */
    public function actionBy()
    {
        return $this->belongsTo(User::class, 'action_by');
    }
}
