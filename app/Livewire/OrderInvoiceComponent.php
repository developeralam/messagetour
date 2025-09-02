<?php

namespace App\Livewire;

use App\Models\Order;
use Livewire\Component;

class OrderInvoiceComponent extends Component
{
    public Order $order;

    public function mount($order)
    {
        $this->order = $order;
    }
    
    public function render()
    {
        return view('livewire.order-invoice-component');
    }
}
