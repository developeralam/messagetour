<?php

use App\Models\TravelProductBooking;
use Mary\Traits\Toast;
use Livewire\Volt\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;

new #[Layout('components.layouts.customer')] #[Title('Product Booking')] class extends Component {
    use WithPagination;
    use Toast;
    public array $headers;
    public string $search = '';
    public function mount()
    {
        $this->headers = $this->headers();
    }
    public function headers(): array
    {
        return [['key' => 'id', 'label' => '#'], ['key' => 'travelproduct.title', 'label' => 'Product Title'], ['key' => 'order.paymentgateway.name', 'label' => 'Payment Method'], ['key' => 'payment_status', 'label' => 'Payment Status'], ['key' => 'total_amount', 'label' => 'Total Amount'], ['key' => 'orderstatus', 'label' => 'Order Status']];
    }
    public function productbookings()
    {
        return TravelProductBooking::query()
            ->with(['travelproduct', 'order'])
            ->whereHas('order', function ($q) {
                $q->where('user_id', auth()->user()->id);
            })
            ->latest()
            ->paginate(10);
    }

    public function with(): array
    {
        return [
            'productbookings' => $this->productbookings(),
        ];
    }
}; ?>

<div>
    <x-header title="Order List" size="text-xl" separator class="bg-white px-2 pt-2" />
    <x-card>
        <x-table :headers="$headers" :rows="$productbookings" with-pagination>
            @scope('cell_id', $productbooking, $productbookings)
                {{ $loop->iteration + ($productbookings->currentPage() - 1) * $productbookings->perPage() }}
            @endscope
            @scope('cell_total_amount', $productbooking)
                BDT {{ $productbooking->order->total_amount }}
            @endscope
            @scope('cell_orderstatus', $productbooking)
                @if ($productbooking->order->status == \App\Enum\OrderStatus::Pending)
                    <x-badge value="{{ $productbooking->order->status->label() }}"
                        class="bg-yellow-100 text-yellow-700 p-3 text-xs font-semibold" />
                @elseif ($productbooking->order->status == \App\Enum\OrderStatus::OnHold)
                    <x-badge value="{{ $productbooking->order->status->label() }}"
                        class="bg-orange-100 text-orange-700 p-3 text-xs font-semibold" />
                @elseif ($productbooking->order->status == \App\Enum\OrderStatus::Delivered)
                    <x-badge value="{{ $productbooking->order->status->label() }}"
                        class="bg-green-100 text-green-700 p-3 text-xs font-semibold" />
                @elseif ($productbooking->order->status == \App\Enum\OrderStatus::Cancelled)
                    <x-badge value="{{ $productbooking->order->status->label() }}"
                        class="bg-red-100 text-red-700 p-3 text-xs font-semibold" />
                @elseif ($productbooking->order->status == \App\Enum\OrderStatus::Returned)
                    <x-badge value="{{ $productbooking->order->status->label() }}"
                        class="bg-purple-100 text-purple-700 p-3 text-xs font-semibold" />
                @elseif ($productbooking->order->status == \App\Enum\OrderStatus::Confirmed)
                    <x-badge value="{{ $productbooking->order->status->label() }}"
                        class="bg-green-100 text-green-700 p-3 text-xs font-semibold" />
                @elseif ($productbooking->order->status == \App\Enum\OrderStatus::Shipping)
                    <x-badge value="{{ $productbooking->order->status->label() }}"
                        class="bg-blue-100 text-blue-700 p-3 text-xs font-semibold" />
                @elseif ($productbooking->order->status == \App\Enum\OrderStatus::Failed)
                    <x-badge value="{{ $productbooking->order->status->label() }}"
                        class="bg-red-100 text-red-700 p-3 text-xs font-semibold" />
                @endif
            @endscope
            @scope('cell_payment_status', $productbooking)
                @if ($productbooking->order->payment_status == \App\Enum\PaymentStatus::Paid)
                    <x-badge value="{{ $productbooking->order->payment_status->label() }}"
                        class="bg-green-100 text-green-700 p-3 text-xs font-semibold" />
                @elseif ($productbooking->order->payment_status == \App\Enum\PaymentStatus::Unpaid)
                    <x-badge value="{{ $productbooking->order->payment_status->label() }}"
                        class="bg-yellow-100 text-yellow-700 p-3 text-xs font-semibold" />
                @elseif ($productbooking->order->payment_status == \App\Enum\PaymentStatus::Failed)
                    <x-badge value="{{ $productbooking->order->payment_status->label() }}"
                        class="bg-red-100 text-red-700 p-3 text-xs font-semibold" />
                @elseif ($productbooking->order->payment_status == \App\Enum\PaymentStatus::Cancelled)
                    <x-badge value="{{ $productbooking->order->payment_status->label() }}"
                        class="bg-red-100 text-red-700 p-3 text-xs font-semibold" />
                @endif
            @endscope
            @scope('actions', $productbooking)
                <div class="flex items-center">
                    <x-button icon="fas.print" link="/order/{{ $productbooking->order->id }}/invoice"
                        class="btn-primary btn-action text-white" external />
                </div>
            @endscope
        </x-table>
    </x-card>
</div>
