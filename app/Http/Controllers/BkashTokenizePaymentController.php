<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use App\Services\BkashPaymentGatewayService;

class BkashTokenizePaymentController extends Controller
{
    public function index()
    {
        return view('bkashT::bkash-payment');
    }
    public function createPayment(Request $request)
    {
        $order = Order::where('tran_id', $request->tran_id)->firstOrFail();
        return app(BkashPaymentGatewayService::class)->makePayment($order);
    }

    public function callBack(Request $request)
    {
        return app(BkashPaymentGatewayService::class)->handleCallback($request);
    }
}
