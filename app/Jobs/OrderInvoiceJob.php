<?php

namespace App\Jobs;

use App\Models\Order;
use App\Mail\OrderInvoiceMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class OrderInvoiceJob implements ShouldQueue
{
    use Queueable, Dispatchable;

    public $email;
    public Order $order;

    /**
     * Create a new job instance.
     */
    public function __construct($email, Order $order)
    {
        $this->email = $email;
        $this->order = $order;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            setMailConfig();
            Mail::to($this->email)
                ->send(new OrderInvoiceMail($this->order));
        } catch (\Exception $e) {
            \Log::error('Failed to send email: ' . $e->getMessage());
        }
    }
}
