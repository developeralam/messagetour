<?php

use App\Models\Agent;
use App\Models\Order;
use Mary\Traits\Toast;
use App\Enum\OrderStatus;
use Livewire\Volt\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Builder;

new #[Layout('components.layouts.partner')] #[Title('Booking List')] class extends Component {
    use WithPagination, Toast;
    public array $headers;

    public $statuses = [];
    public $types = [];

    public $status;
    public $type;

    public function mount()
    {
        $this->headers = $this->headers();
        $this->statuses = OrderStatus::getStatuses();
        $this->types = [['id' => \App\Models\HotelRoomBooking::class, 'name' => 'Hotel Room Booking'], ['id' => \App\Models\TourBooking::class, 'name' => 'Tour Booking'], ['id' => \App\Models\TravelProductBooking::class, 'name' => 'Gear Booking']];
    }

    /**
     * Delete an order and its associated user.
     *
     * This function ensures that both the order and user are deleted within a transaction
     * to maintain data integrity. If an error occurs, the deletion is rolled back.
     *
     * @param Order deleted.
     * @return void
     */
    public function delete(Order $order)
    {
        try {
            $order->update([
                'action_id' => auth()->user()->id,
            ]);
            $order->delete();

            $this->success('Order Deleted Successfully');
        } catch (\Throwable $th) {
            $this->error(env('APP_DEBUG', false) ? $th->getMessage() : 'Something went wrong while deleting the order');
        }
    }
    public function headers(): array
    {
        return [['key' => 'id', 'label' => '#'], ['key' => 'user.name', 'label' => 'Name'], ['key' => 'user.email', 'label' => 'Email'], ['key' => 'phone', 'label' => 'Phone Number'], ['key' => 'booking_item', 'label' => 'Booking Item'], ['key' => 'coupon.code', 'label' => 'Coupon'], ['key' => 'coupon_amount', 'label' => 'Coupon Amount'], ['key' => 'paymentgateway.name', 'label' => 'Payment Gateway'], ['key' => 'total_amount', 'label' => 'Total Amount'], ['key' => 'payment_status', 'label' => 'Payment Status'], ['key' => 'status', 'label' => 'Booking Status']];
    }

    /**
     * Retrieve a paginated list of orders with their associated users.
     *
     * This function allows searching by `business_name` and applies eager loading
     * to optimize query performance.
     *
     * @return \Livewire\WithPagination Paginated list of orders.
     */
    public function orders()
    {
        return Order::query()
            ->with(['user', 'coupon', 'paymentgateway', 'sourceable'])
            ->where(function ($query) {
                $query->where(function ($subQuery) {
                    $subQuery
                        ->whereHasMorph('sourceable', \App\Models\TourBooking::class, function ($q) {
                            $q->whereHas('tour', fn($q) => $q->where('created_by', auth()->id()));
                        })
                        ->orWhereHasMorph('sourceable', \App\Models\TravelProductBooking::class, function ($q) {
                            $q->whereHas('travelproduct', fn($q) => $q->where('created_by', auth()->id()));
                        })
                        ->orWhereHasMorph('sourceable', \App\Models\HotelRoomBooking::class, function ($q) {
                            $q->whereHas('hotelbookingitems.room.hotel', function ($hotelQuery) {
                                $hotelQuery->where('created_by', auth()->id());
                            });
                        });
                });
            })
            ->when($this->status, fn(Builder $q) => $q->where('status', $this->status))
            // 👇 Add this for type filtering
            ->when($this->type === \App\Models\TourBooking::class, function ($q) {
                $q->whereHasMorph('sourceable', \App\Models\TourBooking::class, function ($q) {
                    $q->whereHas('tour', fn($q) => $q->where('created_by', auth()->id()));
                });
            })
            ->when($this->type === \App\Models\TravelProductBooking::class, function ($q) {
                $q->whereHasMorph('sourceable', \App\Models\TravelProductBooking::class, function ($q) {
                    $q->whereHas('travelproduct', fn($q) => $q->where('created_by', auth()->id()));
                });
            })
            ->when($this->type === \App\Models\HotelRoomBooking::class, function ($q) {
                $q->whereHasMorph('sourceable', \App\Models\HotelRoomBooking::class, function ($q) {
                    $q->whereHas('hotelbookingitems.room.hotel', function ($q) {
                        $q->where('created_by', auth()->id());
                    });
                });
            })
            ->latest()
            ->paginate(10);
    }

    public function with(): array
    {
        return [
            'orders' => $this->orders(),
        ];
    }
}; ?>

<div>
    <x-header title="Booking List" size="text-xl" separator class="bg-white px-2 pt-2">
        <x-slot:actions class="!justify-end">
            <x-select wire:model.live="status" placeholder="Select Order Status" :options="$statuses" />
            <x-select wire:model.live="type" placeholder="Select Type" :options="$types" />
        </x-slot>
    </x-header>
    <x-card>
        <x-table :headers="$headers" :rows="$orders" with-pagination>
            @scope('cell_id', $order, $orders)
                {{ $loop->iteration + ($orders->currentPage() - 1) * $orders->perPage() }}
            @endscope

            @scope('cell_status', $order)
                {{ $order->status->label() }}
            @endscope

            @scope('cell_payment_status', $order)
                {{ $order->payment_status->label() }}
            @endscope

            @scope('cell_booking_item', $order)
                @php
                    $bookingTypes = [
                        \App\Models\TourBooking::class => 'Tour',
                        \App\Models\TravelProductBooking::class => 'Travel Product',
                        \App\Models\HotelRoomBooking::class => 'Hotel Room',
                    ];

                    // Get the booking type based on the sourceable_type
                    $bookingType = $bookingTypes[$order->sourceable_type] ?? 'Unknown';
                @endphp

                {{ $bookingType }}
            @endscope

            @scope('cell_business_logo', $order)
                <x-avatar image="{{ $order->business_logo_link ?? '/empty-user.jpg' }}" class="!w-10" />
            @endscope

            @scope('actions', $order)
                <div class="flex items-center gap-1">
                    <x-button icon="o-trash" wire:click="delete({{ $order->id }})" wire:confirm="Are you sure?"
                        class="btn-action btn-error" spinner="delete({{ $order->id }})" />
                    <x-button icon="fas.eye" external link="/partner/order/{{ $order->id }}/invoice"
                        class="btn-action btn-neutral" />
                </div>
            @endscope
        </x-table>
    </x-card>
</div>
