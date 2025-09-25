<?php

namespace App\Models;

use App\Models\User;
use App\Models\ChartOfAccount;
use App\Enum\TransactionStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Expense extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'expenses_head_id',
        'amount',
        'account_id',
        'remarks',
        'status',
        'created_by',
        'action_by',
    ];

    protected $casts = [
        'status' => TransactionStatus::class,
    ];

    /**
     * Get the expense head associated with the expense.
     */
    public function expenseHead(): BelongsTo
    {
        return $this->belongsTo(ChartOfAccount::class, 'expenses_head_id');
    }
    /**
     * Get the account associated with the expense.
     */
    public function account(): BelongsTo
    {
        return $this->belongsTo(ChartOfAccount::class, 'account_id');
    }
    /**
     * Get the user who created the expense.
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who performed the action.
     */
    public function actionBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'action_by');
    }
}
