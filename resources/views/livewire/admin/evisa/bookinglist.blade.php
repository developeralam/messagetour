<?php

use Carbon\Carbon;
use App\Models\User;
use App\Models\Agent;
use App\Models\Order;
use App\Enum\UserType;
use App\Enum\VisaType;
use Mary\Traits\Toast;
use App\Enum\OrderStatus;
use App\Models\VisaBooking;
use Livewire\Volt\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Builder;

new #[Layout('components.layouts.admin')] #[Title('E-visa Bookings')] class extends Component {
    use WithPagination, Toast;
    public array $headers;

    public $statuses = [];
    public $user_types = [];

    public $status;
    public $search_date;
    public $user_type;

    public function mount()
    {
        $this->headers = $this->headers();
        $this->statuses = OrderStatus::getStatuses();
        $this->user_types = [['id' => UserType::Agent, 'name' => 'Agent'], ['id' => UserType::Customer, 'name' => 'Customer']];
    }

    /**
     * Delete an booking and its associated user.
     *
     * This function ensures that both the booking and user are deleted within a transaction
     * to maintain data integrity. If an error occurs, the deletion is rolled back.
     *
     * @param User $user The user associated with the booking to be deleted.
     * @return void
     */
    public function delete(Order $booking)
    {
        // Start transaction to ensure both deletions occur safely

        try {
            $booking->update([
                'action_by' => auth()->user()->id,
            ]);
            $booking->delete();
            $this->success('Booking Deleted Successfully');
        } catch (\Throwable $th) {
            $this->error(env('APP_DEBUG', false) ? $th->getMessage() : 'Something went wrong while deleting the booking');
        }
    }
    public function headers(): array
    {
        return [['key' => 'id', 'label' => '#'], ['key' => 'user.name', 'label' => 'Customer Name'], ['key' => 'phone', 'label' => 'Customer Mobile'], ['key' => 'booking_item', 'label' => 'Booking Item'], ['key' => 'coupon', 'label' => 'Coupon'], ['key' => 'coupon_amount', 'label' => 'Discount'], ['key' => 'paymentgateway.name', 'label' => 'Payment Gateway'], ['key' => 'total_amount', 'label' => 'Total Amount'], ['key' => 'purpose', 'label' => 'Booking Purpose'], ['key' => 'payment_status', 'label' => 'Payment Status'], ['key' => 'status', 'label' => 'Booking Status'], ['key' => 'action_by', 'label' => 'Last Action By']];
    }

    /**
     * Retrieve a paginated list of evisa bookings with their associated users.
     *
     * This function allows searching by `business_name` and applies eager loading
     * to optimize query performance.
     *
     * @return \Livewire\WithPagination Paginated list of evisa bookings.
     */
    public function bookings()
    {
        return Order::query()
            ->with([
                'user',
                'coupon',
                'paymentgateway',
                'sourceable' => function ($morph) {
                    if ($morph instanceof Builder) {
                        $morph->with('evisa_booking_detail'); // add this
                    }
                },
            ])
            ->where(function ($query) {
                $query->where('sourceable_type', VisaBooking::class)->orWhereHasMorph('sourceable', [VisaBooking::class], function ($q) {
                    $q->whereHas('visa', function ($q2) {
                        $q2->where('type', VisaType::Evisa);
                    });
                });
            })
            ->when($this->status, fn(Builder $q) => $q->where('status', $this->status))
            ->when($this->search_date, fn(Builder $q) => $q->whereDate('created_at', $this->search_date))
            ->when($this->user_type, function (Builder $q) {
                $q->whereHas('user', function ($query) {
                    $query->where('type', $this->user_type);
                });
            })
            ->latest()
            ->paginate(10);
    }

    public function confirm(Order $booking)
    {
        try {
            $booking->update([
                'status' => OrderStatus::Confirmed,
                'action_by' => auth()->user()->id,
            ]);
            $this->success('Order Confirmed!');
        } catch (\Throwable $th) {
            $this->error(env('APP_DEBUG', false) ? $th->getMessage() : 'Something went wrong');
        }
    }

    public function with(): array
    {
        return [
            'bookings' => $this->bookings(),
        ];
    }
}; ?>

<div>
    <x-header title="E-visa Booking List" size="text-xl" separator class="bg-white px-2 pt-2">
        <x-slot:actions class="!justify-end">
            <x-select wire:model.live="user_type" placeholder="Select Order By" :options="$user_types" />
            <x-select wire:model.live="status" placeholder="Select Order Status" :options="$statuses" />
            <x-datetime wire:model.live="search_date" />
        </x-slot>
    </x-header>
    <x-card>
        <x-table :headers="$headers" :rows="$bookings" with-pagination>
            @scope('cell_id', $booking, $bookings)
                {{ $loop->iteration + ($bookings->currentPage() - 1) * $bookings->perPage() }}
            @endscope

            @scope('cell_status', $booking)
                @if ($booking->status == \App\Enum\OrderStatus::Pending)
                    <x-badge value="{{ $booking->status->label() }}"
                        class="bg-yellow-100 text-yellow-700 p-3 text-xs font-semibold" />
                @elseif ($booking->status == \App\Enum\OrderStatus::OnHold)
                    <x-badge value="{{ $booking->status->label() }}"
                        class="bg-orange-100 text-orange-700 p-3 text-xs font-semibold" />
                @elseif ($booking->status == \App\Enum\OrderStatus::Delivered)
                    <x-badge value="{{ $booking->status->label() }}"
                        class="bg-green-100 text-green-700 p-3 text-xs font-semibold" />
                @elseif ($booking->status == \App\Enum\OrderStatus::Cancelled)
                    <x-badge value="{{ $booking->status->label() }}"
                        class="bg-red-100 text-red-700 p-3 text-xs font-semibold" />
                @elseif ($booking->status == \App\Enum\OrderStatus::Returned)
                    <x-badge value="{{ $booking->status->label() }}"
                        class="bg-purple-100 text-purple-700 p-3 text-xs font-semibold" />
                @elseif ($booking->status == \App\Enum\OrderStatus::Confirmed)
                    <x-badge value="{{ $booking->status->label() }}"
                        class="bg-green-100 text-green-700 p-3 text-xs font-semibold" />
                @elseif ($booking->status == \App\Enum\OrderStatus::Shipping)
                    <x-badge value="{{ $booking->status->label() }}"
                        class="bg-blue-100 text-blue-700 p-3 text-xs font-semibold" />
                @elseif ($booking->status == \App\Enum\OrderStatus::Failed)
                    <x-badge value="{{ $booking->status->label() }}"
                        class="bg-red-100 text-red-700 p-3 text-xs font-semibold" />
                @endif
            @endscope

            @scope('cell_booking_item', $booking)
                @php
                    $booking = $booking->sourceable;
                    $title = match (true) {
                        $booking instanceof App\Models\TourBooking => $booking->tour?->title,
                        $booking instanceof App\Models\VisaBooking => $booking->visa?->title,
                        $booking instanceof App\Models\CarBooking => $booking->car?->title,
                        $booking instanceof App\Models\HotelRoomBooking => $booking->hotelbookingitems?->first()?->room
                            ?->room_no,
                        default => null,
                    };
                @endphp

                {{ $title ?? '-' }}
            @endscope

            @scope('cell_payment_status', $booking)
                @if ($booking->payment_status == \App\Enum\PaymentStatus::Paid)
                    <x-badge value="{{ $booking->payment_status->label() }}"
                        class="bg-green-100 text-green-700 p-3 text-xs font-semibold" />
                @elseif ($booking->payment_status == \App\Enum\PaymentStatus::Unpaid)
                    <x-badge value="{{ $booking->payment_status->label() }}"
                        class="bg-yellow-100 text-yellow-700 p-3 text-xs font-semibold" />
                @elseif ($booking->payment_status == \App\Enum\PaymentStatus::Failed)
                    <x-badge value="{{ $booking->payment_status->label() }}"
                        class="bg-red-100 text-red-700 p-3 text-xs font-semibold" />
                @elseif ($booking->payment_status == \App\Enum\PaymentStatus::Cancelled)
                    <x-badge value="{{ $booking->payment_status->label() }}"
                        class="bg-red-100 text-red-700 p-3 text-xs font-semibold" />
                @endif
            @endscope

            @scope('cell_coupon_amount', $booking)
                @if ($booking->coupon_amount > 0)
                    BDT {{ number_format($booking->coupon_amount) }}
                @else
                    0
                @endif
            @endscope

            @scope('cell_purpose', $booking)
                @php
                    $purpose = optional($booking->sourceable->evisa_booking_detail)->purpose;
                @endphp
                @if ($purpose instanceof \App\Enum\VisaPurpose)
                    <x-badge value=" {{ $purpose->label() }}" class="bg-primary text-white p-3 text-xs font-semibold" />
                @else
                    {{ $purpose ?: '—' }}
                @endif
            @endscope

            @scope('cell_total_amount', $booking)
                BDT {{ number_format($booking->total_amount) }}
            @endscope

            @scope('cell_coupon', $booking)
                {{ $booking->coupon->code ?? 'N/A' }}
            @endscope

            @scope('cell_action_by', $booking)
                {{ $booking->actionby->name ?? 'N/A' }}
            @endscope

            @scope('actions', $booking)
                <div class="flex items-center gap-1">
                    <x-button icon="o-trash" wire:click="delete({{ $booking->id }})" wire:confirm="Are you sure?"
                        class="btn-action btn-error" spinner="delete({{ $booking->id }})" />
                    <x-button icon="fas.print" external link="/admin/order/{{ $booking->id }}/invoice"
                        class="btn-action btn-primary" />
                    <x-button icon="fas.eye" external link="/admin/{{ $booking->id }}/e-visa/documents"
                        class="btn-action bg-primary text-white" />
                    @if ($booking->payment_gateway_id == 4 && $booking->status == \App\Enum\OrderStatus::Pending)
                        <x-button icon="fas.check" wire:click="confirm({{ $booking->id }})" title="Confirm Order"
                            wire:confirm="Are you sure confirm this booking?" class="btn-info btn-action text-white"
                            spinner="confirm({{ $booking['id'] }})" />
                    @endif
                </div>
            @endscope
        </x-table>
    </x-card>
</div>
