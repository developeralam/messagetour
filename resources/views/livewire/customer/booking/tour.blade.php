<?php

use App\Models\TourBooking;
use Mary\Traits\Toast;
use Livewire\Volt\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;

new #[Layout('components.layouts.customer')] #[Title('Tour Booking')] class extends Component {
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
        return [['key' => 'id', 'label' => '#'], ['key' => 'title', 'label' => 'Tour Title'], ['key' => 'type', 'label' => 'Tour Type'], ['key' => 'tour.location', 'label' => 'Location'], ['key' => 'start_date', 'label' => 'Start Date'], ['key' => 'end_date', 'label' => 'End Date'], ['key' => 'order.paymentgateway.name', 'label' => 'Payment Method'], ['key' => 'payment_status', 'label' => 'Payment Status'], ['key' => 'total_amount', 'label' => 'Total Amount'], ['key' => 'orderstatus', 'label' => 'Booking Status']];
    }
    public function tourbookings()
    {
        return TourBooking::query()
            ->with(['tour', 'order'])
            ->whereHas('order', function ($q) {
                $q->where('user_id', auth()->user()->id);
            })
            ->latest()
            ->paginate(10);
    }

    public function with(): array
    {
        return [
            'tourbookings' => $this->tourbookings(),
        ];
    }
}; ?>

<div>
    <x-header title="Tour Booking List" size="text-xl" separator class="bg-white px-2 pt-2" />
    <x-card>
        <x-table :headers="$headers" :rows="$tourbookings" with-pagination>
            @scope('cell_id', $tourbooking, $tourbookings)
                {{ $loop->iteration + ($tourbookings->currentPage() - 1) * $tourbookings->perPage() }}
            @endscope
            @scope('cell_title', $tourbooking)
                {{ $tourbooking->tour->title }}
            @endscope
            @scope('cell_total_amount', $tourbooking)
                BDT {{ number_format($tourbooking->order->total_amount) }}
            @endscope
            @scope('cell_start_date', $tourbooking)
                {{ $tourbooking->tour->start_date->format('d M, Y') }}
            @endscope
            @scope('cell_end_date', $tourbooking)
                {{ $tourbooking->tour->end_date->format('d M, Y') }}
            @endscope
            @scope('cell_type', $tourbooking)
                <x-badge value="{{ $tourbooking->tour->type->label() }}" class="bg-primary text-white p-3" />
            @endscope
            @scope('cell_orderstatus', $tourbooking)
                @if ($tourbooking->order->status == \App\Enum\OrderStatus::Pending)
                    <x-badge value="{{ $tourbooking->order->status->label() }}"
                        class="bg-yellow-100 text-yellow-700 p-3 text-xs font-semibold" />
                @elseif ($tourbooking->order->status == \App\Enum\OrderStatus::OnHold)
                    <x-badge value="{{ $tourbooking->order->status->label() }}"
                        class="bg-orange-100 text-orange-700 p-3 text-xs font-semibold" />
                @elseif ($tourbooking->order->status == \App\Enum\OrderStatus::Delivered)
                    <x-badge value="{{ $tourbooking->order->status->label() }}"
                        class="bg-green-100 text-green-700 p-3 text-xs font-semibold" />
                @elseif ($tourbooking->order->status == \App\Enum\OrderStatus::Cancelled)
                    <x-badge value="{{ $tourbooking->order->status->label() }}"
                        class="bg-red-100 text-red-700 p-3 text-xs font-semibold" />
                @elseif ($tourbooking->order->status == \App\Enum\OrderStatus::Returned)
                    <x-badge value="{{ $tourbooking->order->status->label() }}"
                        class="bg-purple-100 text-purple-700 p-3 text-xs font-semibold" />
                @elseif ($tourbooking->order->status == \App\Enum\OrderStatus::Confirmed)
                    <x-badge value="{{ $tourbooking->order->status->label() }}"
                        class="bg-green-100 text-green-700 p-3 text-xs font-semibold" />
                @elseif ($tourbooking->order->status == \App\Enum\OrderStatus::Shipping)
                    <x-badge value="{{ $tourbooking->order->status->label() }}"
                        class="bg-blue-100 text-blue-700 p-3 text-xs font-semibold" />
                @elseif ($tourbooking->order->status == \App\Enum\OrderStatus::Failed)
                    <x-badge value="{{ $tourbooking->order->status->label() }}"
                        class="bg-red-100 text-red-700 p-3 text-xs font-semibold" />
                @endif
            @endscope
            @scope('cell_payment_status', $tourbooking)
                @if ($tourbooking->order->payment_status == \App\Enum\PaymentStatus::Paid)
                    <x-badge value="{{ $tourbooking->order->payment_status->label() }}"
                        class="bg-green-100 text-green-700 p-3 text-xs font-semibold" />
                @elseif ($tourbooking->order->payment_status == \App\Enum\PaymentStatus::Unpaid)
                    <x-badge value="{{ $tourbooking->order->payment_status->label() }}"
                        class="bg-yellow-100 text-yellow-700 p-3 text-xs font-semibold" />
                @elseif ($tourbooking->order->payment_status == \App\Enum\PaymentStatus::Failed)
                    <x-badge value="{{ $tourbooking->order->payment_status->label() }}"
                        class="bg-red-100 text-red-700 p-3 text-xs font-semibold" />
                @elseif ($tourbooking->order->payment_status == \App\Enum\PaymentStatus::Cancelled)
                    <x-badge value="{{ $tourbooking->order->payment_status->label() }}"
                        class="bg-red-100 text-red-700 p-3 text-xs font-semibold" />
                @endif
            @endscope
            @scope('actions', $tourbooking)
                <div class="flex items-center">
                    <x-button icon="fas.print" link="/order/{{ $tourbooking->order->id }}/invoice"
                        class="btn-primary btn-action text-white" external />
                </div>
            @endscope
        </x-table>
    </x-card>
</div>
