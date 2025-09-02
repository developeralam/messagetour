<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
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
}
