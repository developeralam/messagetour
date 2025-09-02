<?php

namespace App\Livewire;

use App\Models\Order;
use Livewire\Component;

class InvoiceBookingTableComponent extends Component
{
    public Order $order;

    public function mount(Order $order)
    {
        $this->order = $order;
    }
    
    public function render()
    {
        return view('livewire.invoice-booking-table-component');
    }
}
