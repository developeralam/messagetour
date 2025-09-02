<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Ledger;
use App\Models\Transactions;
use Illuminate\Http\Request;
use App\Models\ChartOfAccount;
use App\Models\CorporateQuery;
use Barryvdh\DomPDF\Facade\Pdf;

class PDFController extends Controller
{
    public function order_invoice(Order $order)
    {
        $pdf = Pdf::loadView('pdf.order-invoice', compact('order'));

        return $pdf->stream("invoice-{$order->id}.pdf");
    }
    public function partner_booking_invoice(Order $order)
    {
        $pdf = Pdf::loadView('pdf.order-invoice', compact('order'));

        return $pdf->stream("invoice-{$order->id}.pdf");
    }
    public function partner_order_invoice(Order $order)
    {
        $pdf = Pdf::loadView('pdf.order-invoice', compact('order'));

        return $pdf->stream("invoice-{$order->id}.pdf");
    }
    public function partner_markup_invoice($order, Request $request)
    {
        $order = Order::findOrFail($order);
        // Get the custom amounts from the request
        $total_amount = $request->query('total_amount');
        $custom_amount = $request->query('custom_amount');

        // Pass the custom amounts along with the order to the view
        $pdf = Pdf::loadView('pdf.partner-markup-invoice', compact('order', 'total_amount', 'custom_amount'));

        return $pdf->stream("invoice-{$order->id}.pdf");
    }

    public function Customer_order_invoice(Order $order)
    {
        $pdf = Pdf::loadView('pdf.order-invoice', compact('order'));

        return $pdf->download("invoice-{$order->id}.pdf");
    }
    public function partner_corporate_query(CorporateQuery $query)
    {
        $pdf = Pdf::loadView('pdf.corporatequery', compact('query'));

        return $pdf->download("query-{$query->id}.pdf");
    }
    public function customer_corporate_query(CorporateQuery $query)
    {
        $pdf = Pdf::loadView('pdf.corporatequery', compact('query'));

        return $pdf->download("query-{$query->id}.pdf");
    }

    public function chart_of_account($category_id, $from_date, $to_date)
    {
        $business = auth()->user()->business;
        $account = ChartOfAccount::findOrFail($category_id);

        $openingBalance = Ledger::where('account_id', $category_id)
            ->where('date', '<', $from_date)
            ->orderByDesc('date')
            ->value('closing_balance');

        if ($openingBalance === null) {
            $openingBalance = $account->current_balance ?? 0;
        }

        $accountType = $account->type;

        if (in_array($accountType, ['revenue', 'expense'])) {
            $transactions = Transactions::where(function ($q) use ($from_date, $to_date) {
                $q->whereBetween('date', [$from_date, $to_date]);
            })->where(function ($q) use ($category_id) {
                $q->where('debit_account_id', $category_id)
                    ->orWhere('credit_account_id', $category_id);
            })->get();

            // return view('pdf.accounts.ledger-category', [
            //     'account' => $account,
            //     'account_type' => $accountType,
            //     'from_date' => $from_date,
            //     'to_date' => $to_date,
            //     'transactions' => $transactions,
            //     'business' => $business,
            // ]);
            $pdf = Pdf::loadView('pdf.accounts.ledger-category', [
                'account' => $account,
                'account_type' => $accountType,

                'from_date' => $from_date,
                'to_date' => $to_date,
                'transactions' => $transactions,
                'business' => $business,
            ]);

            return $pdf->stream('Accounts Report.pdf');
        }

        $transactions = Transactions::where(function ($q) use ($from_date, $to_date) {
            $q->whereBetween('date', [$from_date, $to_date]);
        })->where(function ($q) use ($category_id) {
            $q->where('debit_account_id', $category_id)
                ->orWhere('credit_account_id', $category_id);
        })->get();

        $openingDebits = Ledger::where('account_id', $category_id)
            ->where('date', '<', $from_date)
            ->sum('debit');

        $openingCredits = Ledger::where('account_id', $category_id)
            ->where('date', '<', $from_date)
            ->sum('credit');

        $opening_balance = $openingDebits - $openingCredits;

        $debitLedgers = Ledger::where('account_id', $category_id)
            ->whereBetween('date', [$from_date, $to_date])
            ->where('debit', '>', 0)
            ->get();

        $creditLedgers = Ledger::where('account_id', $category_id)
            ->whereBetween('date', [$from_date, $to_date])
            ->where('credit', '>', 0)
            ->get();

        // return view('pdf.accounts.ledger', [
        //     'account' => $account,
        // 'account_type' => $accountType,
        //     'from_date' => $from_date,
        //     'to_date' => $to_date,
        //     'receive_transcations' => $debitLedgers,
        //     'payment_transcations' => $creditLedgers,
        //     'opening_balance' => $opening_balance,
        //     'business' => $business,
        // ]);
        $pdf = Pdf::loadView('pdf.accounts.ledger', [
            'account' => $account,
            'account_type' => $accountType,
            'from_date' => $from_date,
            'to_date' => $to_date,
            'receive_transcations' => $debitLedgers,
            'payment_transcations' => $creditLedgers,
            'opening_balance' => $opening_balance,
            'business' => $business,
        ])->setPaper('a4', 'landscape');

        return $pdf->stream('Accounts Report.pdf');
    }

    public function chart_of_account_category($category_id, $from_date, $to_date)
    {
        $business = auth()->user()->business;
        $account = ChartOfAccount::findOrFail($category_id);

        $openingBalance = Ledger::where('account_id', $category_id)
            ->where('date', '<', $from_date)
            ->orderByDesc('date')
            ->value('closing_balance');

        if ($openingBalance === null) {
            $openingBalance = $account->current_balance ?? 0;
        }

        $accountType = $account->type;

        if (in_array($accountType, ['revenue', 'expense'])) {
            $transactions = Transactions::where(function ($q) use ($from_date, $to_date) {
                $q->whereBetween('date', [$from_date, $to_date]);
            })->where(function ($q) use ($category_id) {
                $q->where('debit_account_id', $category_id)
                    ->orWhere('credit_account_id', $category_id);
            })->get();

            // return view('pdf.accounts.ledger-category', [
            //     'account' => $account,
            //     'account_type' => $accountType,
            //     'from_date' => $from_date,
            //     'to_date' => $to_date,
            //     'transactions' => $transactions,
            //     'business' => $business,
            // ]);
            $pdf = Pdf::loadView('pdf.accounts.ledger-category', [
                'account' => $account,
                'account_type' => $accountType,

                'from_date' => $from_date,
                'to_date' => $to_date,
                'transactions' => $transactions,
                'business' => $business,
            ]);

            return $pdf->stream('Accounts Report.pdf');
        }

        $transactions = Transactions::where(function ($q) use ($from_date, $to_date) {
            $q->whereBetween('date', [$from_date, $to_date]);
        })->where(function ($q) use ($category_id) {
            $q->where('debit_account_id', $category_id)
                ->orWhere('credit_account_id', $category_id);
        })->get();

        $openingDebits = Ledger::where('account_id', $category_id)
            ->where('date', '<', $from_date)
            ->sum('debit');

        $openingCredits = Ledger::where('account_id', $category_id)
            ->where('date', '<', $from_date)
            ->sum('credit');

        $opening_balance = $openingDebits - $openingCredits;

        $debitLedgers = Ledger::where('account_id', $category_id)
            ->whereBetween('date', [$from_date, $to_date])
            ->where('debit', '>', 0)
            ->get();

        $creditLedgers = Ledger::where('account_id', $category_id)
            ->whereBetween('date', [$from_date, $to_date])
            ->where('credit', '>', 0)
            ->get();

        // return view('pdf.accounts.ledger-category', [
        //     'account' => $account,
        // 'account_type' => $accountType,
        //     'from_date' => $from_date,
        //     'to_date' => $to_date,
        //     'receive_transcations' => $debitLedgers,
        //     'payment_transcations' => $creditLedgers,
        //     'opening_balance' => $opening_balance,
        //     'business' => $business,
        // ]);
        $pdf = Pdf::loadView('pdf.accounts.ledger-category', [
            'account' => $account,
            'account_type' => $accountType,
            'from_date' => $from_date,
            'to_date' => $to_date,
            'receive_transcations' => $debitLedgers,
            'payment_transcations' => $creditLedgers,
            'opening_balance' => $opening_balance,
            'business' => $business,
        ])->setPaper('a4', 'landscape');

        return $pdf->stream('Accounts Report.pdf');
    }
}
