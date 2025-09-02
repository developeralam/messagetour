<?php

use App\Models\CarBooking;
use Mary\Traits\Toast;
use Livewire\Volt\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;

new #[Layout('components.layouts.customer')] #[Title('Car Booking')] class extends Component {
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
        return [['key' => 'id', 'label' => '#'], ['key' => 'thumbnail', 'label' => 'Car'], ['key' => 'title', 'label' => 'Car Title'], ['key' => 'seating_capacity', 'label' => 'Seeting Capacity'], ['key' => 'car_type', 'label' => 'Car Type'], ['key' => 'hour_rent', 'label' => 'Hour Rent'], ['key' => 'ac_facility', 'label' => 'Ac Facility'], ['key' => 'rent', 'label' => 'Car Rent'], ['key' => 'order.paymentgateway.name', 'label' => 'Payment Method'], ['key' => 'payment_status', 'label' => 'Payment Status'], ['key' => 'total_amount', 'label' => 'Total Amount'], ['key' => 'orderstatus', 'label' => 'Booking Status'], ['key' => 'created_at', 'label' => 'Booking Date']];
    }
    public function carbookings()
    {
        return CarBooking::query()
            ->with(['order', 'car'])
            ->whereHas('order', function ($q) {
                $q->where('user_id', auth()->user()->id);
            })
            ->latest()
            ->paginate(10);
    }

    public function with(): array
    {
        return [
            'carbookings' => $this->carbookings(),
        ];
    }
}; ?>

<div>
    <x-header title="Car Booking List" size="text-xl" separator class="bg-white px-2 pt-2" />
    <x-card>
        <x-table :headers="$headers" :rows="$carbookings" with-pagination>
            @scope('cell_id', $carbooking, $carbookings)
                {{ $loop->iteration + ($carbookings->currentPage() - 1) * $carbookings->perPage() }}
            @endscope
            @scope('cell_thumbnail', $carbooking)
                <x-avatar image="{{ $carbooking->car->image_link ?? '/empty-product.png' }}" class="!w-10" />
            @endscope
            @scope('cell_title', $carbooking)
                {{ $carbooking->car?->title . ' (' . $carbooking->car?->model_year . ')' }}
            @endscope
            @scope('cell_car_type', $carbooking)
                <x-badge value="{{ $carbooking->car->car_type->label() }}" class="bg-primary text-white p-3 text-xs" />
            @endscope
            @scope('cell_ac_facility', $carbooking)
                @if ($carbooking->ac_facility == 1)
                    <x-badge value="Yes" class="bg-primary text-white p-3 text-xs" />
                @else
                    <x-badge value="No" class="bg-primary text-white p-3 text-xs" />
                @endif
            @endscope
            @scope('cell_created_at', $carbooking)
                {{ $carbooking->created_at->format('d M, Y') }}
            @endscope
            @scope('cell_orderstatus', $carbooking)
                @if ($carbooking->order->status == \App\Enum\OrderStatus::Pending)
                    <x-badge value="{{ $carbooking->order->status->label() }}"
                        class="bg-yellow-100 text-yellow-700 p-3 text-xs font-semibold" />
                @elseif ($carbooking->order->status == \App\Enum\OrderStatus::OnHold)
                    <x-badge value="{{ $carbooking->order->status->label() }}"
                        class="bg-orange-100 text-orange-700 p-3 text-xs font-semibold" />
                @elseif ($carbooking->order->status == \App\Enum\OrderStatus::Delivered)
                    <x-badge value="{{ $carbooking->order->status->label() }}"
                        class="bg-green-100 text-green-700 p-3 text-xs font-semibold" />
                @elseif ($carbooking->order->status == \App\Enum\OrderStatus::Cancelled)
                    <x-badge value="{{ $carbooking->order->status->label() }}"
                        class="bg-red-100 text-red-700 p-3 text-xs font-semibold" />
                @elseif ($carbooking->order->status == \App\Enum\OrderStatus::Returned)
                    <x-badge value="{{ $carbooking->order->status->label() }}"
                        class="bg-purple-100 text-purple-700 p-3 text-xs font-semibold" />
                @elseif ($carbooking->order->status == \App\Enum\OrderStatus::Confirmed)
                    <x-badge value="{{ $carbooking->order->status->label() }}"
                        class="bg-green-100 text-green-700 p-3 text-xs font-semibold" />
                @elseif ($carbooking->order->status == \App\Enum\OrderStatus::Shipping)
                    <x-badge value="{{ $carbooking->order->status->label() }}"
                        class="bg-blue-100 text-blue-700 p-3 text-xs font-semibold" />
                @elseif ($carbooking->order->status == \App\Enum\OrderStatus::Failed)
                    <x-badge value="{{ $carbooking->order->status->label() }}"
                        class="bg-red-100 text-red-700 p-3 text-xs font-semibold" />
                @endif
            @endscope
            @scope('cell_payment_status', $carbooking)
                @if ($carbooking->order->payment_status == \App\Enum\PaymentStatus::Paid)
                    <x-badge value="{{ $carbooking->order->payment_status->label() }}"
                        class="bg-green-100 text-green-700 p-3 text-xs font-semibold" />
                @elseif ($carbooking->order->payment_status == \App\Enum\PaymentStatus::Unpaid)
                    <x-badge value="{{ $carbooking->order->payment_status->label() }}"
                        class="bg-yellow-100 text-yellow-700 p-3 text-xs font-semibold" />
                @elseif ($carbooking->order->payment_status == \App\Enum\PaymentStatus::Failed)
                    <x-badge value="{{ $carbooking->order->payment_status->label() }}"
                        class="bg-red-100 text-red-700 p-3 text-xs font-semibold" />
                @elseif ($carbooking->order->payment_status == \App\Enum\PaymentStatus::Cancelled)
                    <x-badge value="{{ $carbooking->order->payment_status->label() }}"
                        class="bg-red-100 text-red-700 p-3 text-xs font-semibold" />
                @endif
            @endscope
            @scope('cell_total_amount', $carbooking)
                BDT {{ $carbooking->order->total_amount }}
            @endscope
            @scope('actions', $carbooking)
                <div class="flex items-center">
                    <x-button icon="fas.print" link="/order/{{ $carbooking->order->id }}/invoice" external
                        class="btn-primary btn-action text-white" />
                </div>
            @endscope
        </x-table>
    </x-card>
</div>
