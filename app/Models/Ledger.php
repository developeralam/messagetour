<?php

namespace App\Models;

use App\Models\Transactions;
use App\Models\ChartOfAccount;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Ledger extends Model
{
    use SoftDeletes;

    protected $fillable = ['account_id', 'transaction_id', 'date', 'opening_balance', 'debit', 'credit', 'closing_balance', 'description'];

    public function account()
    {
        return $this->belongsTo(ChartOfAccount::class, 'account_id');
    }

    public function transaction()
    {
        return $this->belongsTo(Transactions::class);
    }
}
