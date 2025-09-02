<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Enum\OrderStatus;
use App\Enum\PaymentStatus;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use App\Services\AgentWalletServices;
use App\Library\SslCommerz\SslCommerzNotification;

class SslCommerzPaymentController extends Controller
{
    /**
     * Handle successful payment callback from SSLCOMMERZ.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function success(Request $request)
    {
        // Initialize SSLCOMMERZ notification class
        $sslc = new SslCommerzNotification();

        // Retrieve order ID passed from value_a field
        $orderId = $request->value_a;

        // Find the order from the database
        $order = Order::findOrFail($orderId);

        // ✅ Re-authenticate user manually
        if ($request->has('value_d')) {
            Auth::loginUsingId($request->value_d);
        }

        // Validate the transaction with SSLCOMMERZ
        if ($sslc->orderValidate($request->all(), $request->tran_id, $request->total_amount, $request->currency)) {
            // Update order as paid and confirmed
            $order->update([
                'payment_status' => PaymentStatus::Paid,
                'status' => OrderStatus::Confirmed,
            ]);
            Transaction::create([
                'order_id' => $order->id,
            ]);
            AgentWalletServices::handle($order);

            // Show success notification
            toastr()->success('Thank you for your payment !');
            // Redirect to order invoice or success page
        } else {
            // Validation failed
            toastr()->error('Payment validation failed!');
        }

        // Redirect to the order invoice page
        return redirect()->route('order.invoice', ['order' => $order->id]);
    }

    /**
     * Handle payment failure callback.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function fail(Request $request)
    {
        // Get order ID from request
        $orderId = $request->value_a;

        // Update order as failed
        Order::where('id', $orderId)->update([
            'payment_status' => PaymentStatus::Failed,
            'status' => OrderStatus::Failed,
        ]);

        // Show error notification
        toastr()->error('Payment Failed!');

        // Redirect back
        return redirect()->intended('/');
    }

    /**
     * Handle cancel callback from SSLCOMMERZ when user cancels the transaction.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function cancel(Request $request)
    {
        // Get order ID from request
        $orderId = $request->value_a;

        // Update order as cancelled
        Order::where('id', $orderId)->update([
            'payment_status' => PaymentStatus::Cancelled,
            'status' => OrderStatus::Cancelled,
        ]);

        // Show cancellation message
        toastr()->info('Order Cancelled!');

        // Redirect back
        return redirect()->intended('/');
    }

    /**
     * Handle Instant Payment Notification (IPN) from SSLCOMMERZ.
     * Ensures backend validation regardless of frontend result.
     *
     * @param Request $request
     * @return void
     */
    public function ipn(Request $request)
    {
        // Initialize SSLCOMMERZ notification class
        $sslc = new SslCommerzNotification();

        // Retrieve order ID
        $orderId = $request->value_a;

        // Find the order
        $order = Order::find($orderId);

        // Skip if already paid or order not found
        if (!$order || $order->payment_status == PaymentStatus::Paid) {
            return;
        }

        // Validate transaction via SSLCOMMERZ API
        if ($sslc->orderValidate($request->all(), $request->tran_id, $request->total_amount, $request->currency)) {
            // Mark order as paid and confirmed
            $order->update([
                'payment_status' => PaymentStatus::Paid,
                'status' => OrderStatus::Confirmed,
            ]);
        } else {
            // Mark order as failed if validation fails
            $order->update([
                'payment_status' => PaymentStatus::Failed,
                'status' => OrderStatus::Failed,
            ]);
        }
    }
}
