<?php

use App\Models\HotelRoomBooking;
use Mary\Traits\Toast;
use Livewire\Volt\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;

new #[Layout('components.layouts.customer')] #[Title('Hotel Booking')] class extends Component {
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
        return [['key' => 'id', 'label' => '#'], ['key' => 'hotel_name', 'label' => 'Hotel'], ['key' => 'room_no', 'label' => 'Hotel Room No'], ['key' => 'created_at', 'label' => 'Booking Date'], ['key' => 'checkin_time', 'label' => 'Hotel Check In'], ['key' => 'checkout_time', 'label' => 'Hotel Check Out'], ['key' => 'order.paymentgateway.name', 'label' => 'Payment Method'], ['key' => 'payment_status', 'label' => 'Payment Status'], ['key' => 'total_amount', 'label' => 'Total Amount'], ['key' => 'orderstatus', 'label' => 'Order Status']];
    }
    public function hotebookings()
    {
        return HotelRoomBooking::query()
            ->with(['order'])
            ->whereHas('order', function ($q) {
                $q->where('user_id', auth()->user()->id);
            })
            ->latest()
            ->paginate(10);
    }

    public function with(): array
    {
        return [
            'hotebookings' => $this->hotebookings(),
        ];
    }
}; ?>

<div>
    <x-header title="Hotel Booking List" size="text-xl" separator class="bg-white px-2 pt-2" />
    <x-card>
        <x-table :headers="$headers" :rows="$hotebookings" with-pagination>
            @scope('cell_id', $hotebooking, $hotebookings)
                {{ $loop->iteration + ($hotebookings->currentPage() - 1) * $hotebookings->perPage() }}
            @endscope
            @scope('cell_hotel_name', $hotebooking)
                {{ $hotebooking->hotelbookingitems?->first()?->room?->hotel?->name }}
            @endscope
            @scope('cell_room_no', $hotebooking)
                {{ $hotebooking->hotelbookingitems?->first()?->room?->room_no }}
            @endscope
            @scope('cell_checkin_time', $hotebooking)
                {{ $hotebooking->hotelbookingitems?->first()?->room?->hotel?->checkin_time->format('H:i A') }}
            @endscope
            @scope('cell_checkout_time', $hotebooking)
                {{ $hotebooking->hotelbookingitems?->first()?->room?->hotel?->checkout_time->format('H:i A') }}
            @endscope
            @scope('cell_created_at', $hotebooking)
                {{ $hotebooking->created_at->format('d M, Y') }}
            @endscope
            @scope('cell_orderstatus', $hotebooking)
                @if ($hotebooking->order->status == \App\Enum\OrderStatus::Pending)
                    <x-badge value="{{ $hotebooking->order->status->label() }}"
                        class="bg-yellow-100 text-yellow-700 p-3 text-xs font-semibold" />
                @elseif ($hotebooking->order->status == \App\Enum\OrderStatus::OnHold)
                    <x-badge value="{{ $hotebooking->order->status->label() }}"
                        class="bg-orange-100 text-orange-700 p-3 text-xs font-semibold" />
                @elseif ($hotebooking->order->status == \App\Enum\OrderStatus::Delivered)
                    <x-badge value="{{ $hotebooking->order->status->label() }}"
                        class="bg-green-100 text-green-700 p-3 text-xs font-semibold" />
                @elseif ($hotebooking->order->status == \App\Enum\OrderStatus::Cancelled)
                    <x-badge value="{{ $hotebooking->order->status->label() }}"
                        class="bg-red-100 text-red-700 p-3 text-xs font-semibold" />
                @elseif ($hotebooking->order->status == \App\Enum\OrderStatus::Returned)
                    <x-badge value="{{ $hotebooking->order->status->label() }}"
                        class="bg-purple-100 text-purple-700 p-3 text-xs font-semibold" />
                @elseif ($hotebooking->order->status == \App\Enum\OrderStatus::Confirmed)
                    <x-badge value="{{ $hotebooking->order->status->label() }}"
                        class="bg-green-100 text-green-700 p-3 text-xs font-semibold" />
                @elseif ($hotebooking->order->status == \App\Enum\OrderStatus::Shipping)
                    <x-badge value="{{ $hotebooking->order->status->label() }}"
                        class="bg-blue-100 text-blue-700 p-3 text-xs font-semibold" />
                @elseif ($hotebooking->order->status == \App\Enum\OrderStatus::Failed)
                    <x-badge value="{{ $hotebooking->order->status->label() }}"
                        class="bg-red-100 text-red-700 p-3 text-xs font-semibold" />
                @endif
            @endscope
            @scope('cell_payment_status', $hotebooking)
                @if ($hotebooking->order->payment_status == \App\Enum\PaymentStatus::Paid)
                    <x-badge value="{{ $hotebooking->order->payment_status->label() }}"
                        class="bg-green-100 text-green-700 p-3 text-xs font-semibold" />
                @elseif ($hotebooking->order->payment_status == \App\Enum\PaymentStatus::Unpaid)
                    <x-badge value="{{ $hotebooking->order->payment_status->label() }}"
                        class="bg-yellow-100 text-yellow-700 p-3 text-xs font-semibold" />
                @elseif ($hotebooking->order->payment_status == \App\Enum\PaymentStatus::Failed)
                    <x-badge value="{{ $hotebooking->order->payment_status->label() }}"
                        class="bg-red-100 text-red-700 p-3 text-xs font-semibold" />
                @elseif ($hotebooking->order->payment_status == \App\Enum\PaymentStatus::Cancelled)
                    <x-badge value="{{ $hotebooking->order->payment_status->label() }}"
                        class="bg-red-100 text-red-700 p-3 text-xs font-semibold" />
                @endif
            @endscope
            @scope('cell_total_amount', $hotebooking)
                BDT {{ number_format($hotebooking->order->total_amount) }}
            @endscope
            @scope('actions', $hotebooking)
                <div class="flex items-center">
                    <x-button icon="fas.eye" link="/order/{{ $hotebooking->order->id }}/invoice"
                        class="btn-primary btn-action text-white" />
                </div>
            @endscope
        </x-table>
    </x-card>
</div>
