<?php

use App\Models\VisaBooking;
use Mary\Traits\Toast;
use Livewire\Volt\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;

new #[Layout('components.layouts.customer')] #[Title('Visa Booking')] class extends Component {
    use WithPagination, Toast;
    public array $headers;
    public string $search = '';
    public function mount()
    {
        $this->headers = $this->headers();
    }
    public function headers(): array
    {
        return [['key' => 'id', 'label' => '#'], ['key' => 'visa.origin.name', 'label' => 'Origin Country'], ['key' => 'visa.destination.name', 'label' => 'Destination Country'], ['key' => 'type', 'label' => 'Visa Type'], ['key' => 'docuemnts_collection_date', 'label' => 'Documents Collection Date'], ['key' => 'order.paymentgateway.name', 'label' => 'Payment Method'], ['key' => 'payment_status', 'label' => 'Payment Status'], ['key' => 'total_amount', 'label' => 'Total Amount'], ['key' => 'orderstatus', 'label' => 'Booking Status'], ['key' => 'created_at', 'label' => 'Booking Date']];
    }

    /**
     * Fetch visa bookings for the authenticated user.
     *
     * @return Livewire\WithPagination
     */
    public function visaBooking()
    {
        return VisaBooking::query()
            ->with(['visa', 'order'])
            ->whereHas('order', function ($q) {
                $q->where('user_id', auth()->user()->id);
            })
            ->latest()
            ->paginate(10);
    }

    public function with(): array
    {
        return [
            'visaBooking' => $this->visaBooking(),
        ];
    }
}; ?>

<div>
    <x-header title="Visa Booking" size="text-xl" separator class="bg-white px-2 pt-2" />
    <x-card>
        <x-table :headers="$headers" :rows="$visaBooking" with-pagination>
            @scope('cell_id', $visabooking, $visaBooking)
                {{ $loop->iteration + ($visaBooking->currentPage() - 1) * $visaBooking->perPage() }}
            @endscope
            @scope('cell_docuemnts_collection_date', $visabooking)
                {{ $visabooking->docuemnts_collection_date->format('d M, Y') }}
            @endscope
            @scope('cell_created_at', $visabooking)
                {{ $visabooking->created_at->format('d M, Y') }}
            @endscope
            @scope('cell_type', $visabooking)
                {{ $visabooking->visa->type->label() }}
            @endscope
            @scope('cell_created_at', $visabooking)
                {{ $visabooking->created_at->format('d M, Y') }}
            @endscope
            @scope('cell_orderstatus', $visabooking)
                @if ($visabooking->order->status == \App\Enum\OrderStatus::Pending)
                    <x-badge value="{{ $visabooking->order->status->label() }}"
                        class="bg-yellow-100 text-yellow-700 p-3 text-xs font-semibold" />
                @elseif ($visabooking->order->status == \App\Enum\OrderStatus::OnHold)
                    <x-badge value="{{ $visabooking->order->status->label() }}"
                        class="bg-orange-100 text-orange-700 p-3 text-xs font-semibold" />
                @elseif ($visabooking->order->status == \App\Enum\OrderStatus::Delivered)
                    <x-badge value="{{ $visabooking->order->status->label() }}"
                        class="bg-green-100 text-green-700 p-3 text-xs font-semibold" />
                @elseif ($visabooking->order->status == \App\Enum\OrderStatus::Cancelled)
                    <x-badge value="{{ $visabooking->order->status->label() }}"
                        class="bg-red-100 text-red-700 p-3 text-xs font-semibold" />
                @elseif ($visabooking->order->status == \App\Enum\OrderStatus::Returned)
                    <x-badge value="{{ $visabooking->order->status->label() }}"
                        class="bg-purple-100 text-purple-700 p-3 text-xs font-semibold" />
                @elseif ($visabooking->order->status == \App\Enum\OrderStatus::Confirmed)
                    <x-badge value="{{ $visabooking->order->status->label() }}"
                        class="bg-green-100 text-green-700 p-3 text-xs font-semibold" />
                @elseif ($visabooking->order->status == \App\Enum\OrderStatus::Shipping)
                    <x-badge value="{{ $visabooking->order->status->label() }}"
                        class="bg-blue-100 text-blue-700 p-3 text-xs font-semibold" />
                @elseif ($visabooking->order->status == \App\Enum\OrderStatus::Failed)
                    <x-badge value="{{ $visabooking->order->status->label() }}"
                        class="bg-red-100 text-red-700 p-3 text-xs font-semibold" />
                @endif
            @endscope
            @scope('cell_payment_status', $visabooking)
                @if ($visabooking->order->payment_status == \App\Enum\PaymentStatus::Paid)
                    <x-badge value="{{ $visabooking->order->payment_status->label() }}"
                        class="bg-green-100 text-green-700 p-3 text-xs font-semibold" />
                @elseif ($visabooking->order->payment_status == \App\Enum\PaymentStatus::Unpaid)
                    <x-badge value="{{ $visabooking->order->payment_status->label() }}"
                        class="bg-yellow-100 text-yellow-700 p-3 text-xs font-semibold" />
                @elseif ($visabooking->order->payment_status == \App\Enum\PaymentStatus::Failed)
                    <x-badge value="{{ $visabooking->order->payment_status->label() }}"
                        class="bg-red-100 text-red-700 p-3 text-xs font-semibold" />
                @elseif ($visabooking->order->payment_status == \App\Enum\PaymentStatus::Cancelled)
                    <x-badge value="{{ $visabooking->order->payment_status->label() }}"
                        class="bg-red-100 text-red-700 p-3 text-xs font-semibold" />
                @endif
            @endscope
            @scope('cell_total_amount', $visabooking)
                BDT {{ number_format($visabooking->order->total_amount) }}
            @endscope
            @scope('actions', $visabooking)
                <div class="flex items-center">
                    <x-button icon="fas.eye" link="/customer/visa-booking/{{ $visabooking['id'] }}/view"
                        class="btn-primary btn-action text-white" />
                </div>
            @endscope
        </x-table>
    </x-card>
</div>
