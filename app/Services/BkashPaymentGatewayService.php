<?php

namespace App\Services;

use App\Models\Order;
use App\Enum\OrderStatus;
use App\Enum\PaymentStatus;
use App\Models\Transaction;
use Illuminate\Http\Request;
use App\Services\AgentWalletServices;
use Karim007\LaravelBkashTokenize\Facade\BkashPaymentTokenize;

class BkashPaymentGatewayService
{

    public function makePayment(Order $order)
    {
        $invoice = $order->tran_id;

        $payload = [
            'intent' => 'sale',
            'mode' => '0011',
            'payerReference' => $invoice,
            'currency' => 'BDT',
            'amount' => (string) round($order->total_amount),
            'merchantInvoiceNumber' => $invoice,
            'callbackURL' => config("bkash.callbackURL"),
        ];

        $response = BkashPaymentTokenize::cPayment(json_encode($payload));

        if (isset($response['bkashURL'])) {
            return redirect()->away($response['bkashURL']);
        }

        return redirect()->back()->with('error-alert2', $response['statusMessage'] ?? 'Something went wrong.');
    }

    public function executePayment(Request $request)
    {
        $paymentID = $request->paymentID;

        $response = BkashPaymentTokenize::executePayment($paymentID);

        if (!$response || !isset($response['transactionStatus'])) {
            $response = BkashPaymentTokenize::queryPayment($paymentID);
        }

        if (isset($response['statusCode']) && $response['statusCode'] == "0000" && $response['transactionStatus'] == "Completed") {
            $trxID = $response['trxID'];
            $order = Order::where('tran_id', $response['merchantInvoiceNumber'])->first();

            if ($order) {
                $order->update([
                    'payment_status' => PaymentStatus::Paid,
                    'status' => OrderStatus::Confirmed,
                ]);

                Transaction::create([
                    'order_id' => $order->id,
                ]);
                AgentWalletServices::handle($order);
            }

            return BkashPaymentTokenize::success('Thank you for your payment', $trxID);
            return redirect()->route('order.invoice', ['order' => $order->id]);
        }

        return BkashPaymentTokenize::failure($response['statusMessage'] ?? 'Failed to execute payment');
    }

    public function handleCallback(Request $request)
    {
        if ($request->status == 'success') {
            return $this->executePayment($request);
        } elseif ($request->status == 'cancel') {
            return BkashPaymentTokenize::cancel('Your payment is canceled');
        } else {
            return BkashPaymentTokenize::failure('Your transaction is failed');
        }
    }
}
