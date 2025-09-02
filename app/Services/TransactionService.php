<?php

namespace App\Services;

use App\Models\ChartOfAccount;
use App\Models\Ledger;
use App\Models\Transactions;
use Illuminate\Support\Facades\DB;

class TransactionService
{
    public static function recordTransaction(array $data): Transactions
    {
        return DB::transaction(function () use ($data) {
            $transaction = Transactions::create($data);

            $debitAccount = ChartOfAccount::findOrFail($data['debit_account_id']);
            $creditAccount = ChartOfAccount::findOrFail($data['credit_account_id']);

            foreach (
                [
                    ['account' => $debitAccount, 'debit' => $data['amount'], 'credit' => 0],
                    ['account' => $creditAccount, 'debit' => 0, 'credit' => $data['amount']],
                ] as $entry
            ) {
                $lastBalance = $entry['account']->current_balance;
                $closing = $lastBalance + $entry['debit'] - $entry['credit'];

                Ledger::create([
                    'account_id' => $entry['account']->id,
                    'transaction_id' => $transaction->id,
                    'date' => $data['date'],
                    'opening_balance' => $lastBalance,
                    'debit' => $entry['debit'],
                    'credit' => $entry['credit'],
                    'closing_balance' => $closing,
                    'description' => $data['description'] ?? null,
                ]);

                $entry['account']->update(['current_balance' => $closing]);
            }

            return $transaction;
        });
    }
}
