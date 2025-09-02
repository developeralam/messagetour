<?php

namespace App\Services;

use App\Library\SslCommerz\SslCommerzNotification;

class SSLCOMMERZPaymentService
{
    public function makePayment($order)
    {
        $post_data = [
            'total_amount' => $order->total_amount,
            'currency' => 'BDT',
            'tran_id' => $order->tran_id,
            'cus_name' => $order->name,
            'cus_email' => $order->email,
            'cus_add1' => 'Dhaka,Bangladesh',
            'cus_postcode' => $order->zipcode,
            'cus_country' => $order->country->name,
            'cus_city' => $order->district->name,
            'cus_phone' => $order->phone,
            'shipping_method' => 'NO',
            'num_of_item' => 1,
            'product_name' => class_basename($order->sourceable_type),
            'product_profile' => 'general',
            'product_category' => 'Booking',
            'value_a' => $order->id, // Custom value - Order ID
            'value_d' => auth()->id(), // Send user_id here
        ];

        $sslc = new SslCommerzNotification();

        return $sslc->makePayment($post_data, 'checkout', 'json');
    }
}
