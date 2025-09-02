<?php

use Carbon\Carbon;
use App\Models\User;
use App\Models\Agent;
use App\Models\Order;
use App\Enum\UserType;
use Mary\Traits\Toast;
use App\Enum\OrderStatus;
use Livewire\Volt\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Builder;

new #[Layout('components.layouts.admin')] #[Title('Order List')] class extends Component {
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
     * Delete an order and its associated user.
     *
     * This function ensures that both the order and user are deleted within a transaction
     * to maintain data integrity. If an error occurs, the deletion is rolled back.
     *
     * @param User $user The user associated with the order to be deleted.
     * @return void
     */
    public function delete(Order $order)
    {
        // Start transaction to ensure both deletions occur safely

        try {
            $order->update([
                'action_by' => auth()->user()->id,
            ]);
            $order->delete();
            $this->success('Order Deleted Successfully');
        } catch (\Throwable $th) {
            $this->error(env('APP_DEBUG', false) ? $th->getMessage() : 'Something went wrong while deleting the order');
        }
    }
    public function headers(): array
    {
        return [['key' => 'id', 'label' => '#'], ['key' => 'user.name', 'label' => 'Customer Name'], ['key' => 'user.email', 'label' => 'Customer Email'], ['key' => 'phone', 'label' => 'Customer Mobile No'], ['key' => 'booking_item', 'label' => 'Order Item'], ['key' => 'coupon.code', 'label' => 'Coupon'], ['key' => 'paymentgateway.name', 'label' => 'Payment Gateway'], ['key' => 'coupon_amount', 'label' => 'Discount Amount'], ['key' => 'total_amount', 'label' => 'Total Amount'], ['key' => 'payment_status', 'label' => 'Payment Status'], ['key' => 'status', 'label' => 'Order Status'], ['key' => 'action_by', 'label' => 'Last Action By']];
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
            ->with(['user', 'coupon', 'paymentgateway'])
            ->where('sourceable_type', \App\Models\TravelProductBooking::class) // ✅ Only TravelProductBooking
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

    public function confirm(Order $order)
    {
        try {
            $order->update([
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
            'orders' => $this->orders(),
        ];
    }
}; ?>

<div>
    <x-header title="Order List" size="text-xl" separator class="bg-white px-2 pt-2">
        <x-slot:actions class="!justify-end">
            <x-select wire:model.live="user_type" placeholder="Select Order By" :options="$user_types" />
            <x-select wire:model.live="status" placeholder="Select Order Status" :options="$statuses" />
            <x-datetime wire:model.live="search_date" />
        </x-slot>
    </x-header>
    <x-card>
        <x-table :headers="$headers" :rows="$orders" with-pagination>
            @scope('cell_id', $order, $orders)
                {{ $loop->iteration + ($orders->currentPage() - 1) * $orders->perPage() }}
            @endscope

            @scope('cell_status', $order)
                @if ($order->status == \App\Enum\OrderStatus::Pending)
                    <x-badge value="{{ $order->status->label() }}"
                        class="bg-yellow-100 text-yellow-700 p-3 text-xs font-semibold" />
                @elseif ($order->status == \App\Enum\OrderStatus::OnHold)
                    <x-badge value="{{ $order->status->label() }}"
                        class="bg-orange-100 text-orange-700 p-3 text-xs font-semibold" />
                @elseif ($order->status == \App\Enum\OrderStatus::Delivered)
                    <x-badge value="{{ $order->status->label() }}"
                        class="bg-green-100 text-green-700 p-3 text-xs font-semibold" />
                @elseif ($order->status == \App\Enum\OrderStatus::Cancelled)
                    <x-badge value="{{ $order->status->label() }}"
                        class="bg-red-100 text-red-700 p-3 text-xs font-semibold" />
                @elseif ($order->status == \App\Enum\OrderStatus::Returned)
                    <x-badge value="{{ $order->status->label() }}"
                        class="bg-purple-100 text-purple-700 p-3 text-xs font-semibold" />
                @elseif ($order->status == \App\Enum\OrderStatus::Confirmed)
                    <x-badge value="{{ $order->status->label() }}"
                        class="bg-green-100 text-green-700 p-3 text-xs font-semibold" />
                @elseif ($order->status == \App\Enum\OrderStatus::Shipping)
                    <x-badge value="{{ $order->status->label() }}"
                        class="bg-blue-100 text-blue-700 p-3 text-xs font-semibold" />
                @elseif ($order->status == \App\Enum\OrderStatus::Failed)
                    <x-badge value="{{ $order->status->label() }}"
                        class="bg-red-100 text-red-700 p-3 text-xs font-semibold" />
                @endif
            @endscope

            @scope('cell_booking_item', $order)
                @php
                    $booking = $order->sourceable;
                    $title = $booking?->travelproduct?->title ?? '-';
                @endphp

                {{ $title ?? '-' }}
            @endscope

            @scope('cell_payment_status', $order)
                @if ($order->payment_status == \App\Enum\PaymentStatus::Paid)
                    <x-badge value="{{ $order->payment_status->label() }}"
                        class="bg-green-100 text-green-700 p-3 text-xs font-semibold" />
                @elseif ($order->payment_status == \App\Enum\PaymentStatus::Unpaid)
                    <x-badge value="{{ $order->payment_status->label() }}"
                        class="bg-yellow-100 text-yellow-700 p-3 text-xs font-semibold" />
                @elseif ($order->payment_status == \App\Enum\PaymentStatus::Failed)
                    <x-badge value="{{ $order->payment_status->label() }}"
                        class="bg-red-100 text-red-700 p-3 text-xs font-semibold" />
                @elseif ($order->payment_status == \App\Enum\PaymentStatus::Cancelled)
                    <x-badge value="{{ $order->payment_status->label() }}"
                        class="bg-red-100 text-red-700 p-3 text-xs font-semibold" />
                @endif
            @endscope

            @scope('cell_coupon_amount', $order)
                @if ($order->coupon_amount > 0)
                    BDT {{ $order->coupon_amount }}
                @else
                    0
                @endif
            @endscope

            @scope('cell_total_amount', $order)
                BDT {{ $order->total_amount }}
            @endscope

            @scope('cell_action_by', $order)
                {{ $order->actionby->name ?? '' }}
            @endscope

            @scope('actions', $order)
                <div class="flex items-center gap-1">
                    <x-button icon="o-trash" wire:click="delete({{ $order->user->id }})" wire:confirm="Are you sure?"
                        class="btn-action btn-error" spinner="delete({{ $order->user['id'] }})" />
                    <x-button icon="fas.print" external link="/admin/order/{{ $order->id }}/invoice"
                        class="btn-action btn-primary" />
                    @if ($order->payment_gateway_id == 4 && $order->status == \App\Enum\OrderStatus::Pending)
                        <x-button icon="fas.check" wire:click="confirm({{ $order->id }})" title="Confirm Order"
                            wire:confirm="Are you sure confirm this order?" class="btn-info btn-action text-white"
                            spinner="confirm({{ $order['id'] }})" />
                    @endif
                </div>
            @endscope
        </x-table>
    </x-card>
</div>
