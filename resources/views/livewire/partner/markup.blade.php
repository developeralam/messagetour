<?php

use App\Models\Order;
use Mary\Traits\Toast;
use Livewire\Volt\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;
use Illuminate\Database\Eloquent\Builder;

new #[Layout('components.layouts.partner')] #[Title('Partner Markup')] class extends Component {
    use WithPagination, Toast;
    public array $headers;
    public bool $markup_voucher = false;

    // Dropdown booking types (polymorphic)
    public $types = [];

    // Optional filter for order type (HotelRoomBooking, TourBooking, etc.)
    public $type;
    public $total_amount;
    public $custom_amount;

    // Selected order used for viewing voucher or details
    public Order $selectedOrder;

    /**
     * Livewire mount method.
     * Initializes table headers and polymorphic booking types.
     *
     * @return void
     */
    public function mount(): void
    {
        // Set up headers for the order table
        $this->headers = $this->headers();

        // Define polymorphic types for filtering
        $this->types = [['id' => \App\Models\HotelRoomBooking::class, 'name' => 'Hotel Room Booking'], ['id' => \App\Models\TourBooking::class, 'name' => 'Tour Booking'], ['id' => \App\Models\TravelProductBooking::class, 'name' => 'Gear Booking']];
    }

    /**
     * Defines the headers used in the table display.
     *
     * @return array
     */
    public function headers(): array
    {
        return [['key' => 'id', 'label' => '#'], ['key' => 'booking_item', 'label' => 'Booking Item'], ['key' => 'paymentgateway.name', 'label' => 'Payment Gateway'], ['key' => 'total_amount', 'label' => 'Amount'], ['key' => 'status', 'label' => 'Order Status'], ['key' => 'created_at', 'label' => 'Order Date']];
    }

    /**
     * Retrieve a paginated list of orders with their related models.
     * Filters by authenticated user and optionally by booking type.
     *
     * @return \Livewire\WithPagination
     */
    public function orders()
    {
        return Order::query()
            ->with(['user', 'coupon', 'paymentgateway', 'sourceable']) // Eager load related models
            ->where('user_id', auth()->id()) // Limit to authenticated user's orders
            ->when($this->type, fn(Builder $q) => $q->where('sourceable_type', $this->type))
            ->latest()
            ->paginate(10);
    }

    /**
     * Opens voucher modal for a selected order.
     * Stores the selected order and its amount.
     *
     * @param Order $order
     * @return void
     */
    public function voucher(Order $order): void
    {
        $this->selectedOrder = $order;
        $this->total_amount = $order->total_amount;
        $this->markup_voucher = true; // Flag to show voucher modal
    }
    public function downloadVoucher(Order $order)
    {
        // Redirect to the PDF route with the custom amount parameters
        return redirect()->route('order.markup-invoice', [
            'order' => $order->id, // Pass only the order id here
            'total_amount' => $this->total_amount,
            'custom_amount' => (int) $this->custom_amount,
        ]);
    }

    /**
     * Provides data to the Livewire view.
     *
     * @return array
     */
    public function with(): array
    {
        return [
            'orders' => $this->orders(),
        ];
    }
}; ?>

<div>
    <x-header title="Markup List" size="text-xl" separator class="bg-white px-2 pt-2">
        <x-slot:actions class="!justify-end">
            <x-select wire:model.live="type" placeholder="Select Type" :options="$types" />
        </x-slot:actions>
    </x-header>
    <x-card>
        <x-table :headers="$headers" :rows="$orders" with-pagination>
            @scope('cell_id', $order, $orders)
                {{ $loop->iteration + ($orders->currentPage() - 1) * $orders->perPage() }}
            @endscope
            @scope('cell_status', $order)
                {{ $order->status->label() }}
            @endscope
            @scope('cell_created_at', $order)
                {{ $order->created_at->format('d M, Y') }}
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
            @scope('actions', $order)
                <x-button label="Voucher" wire:click="voucher({{ $order->id }})" class="btn-primary btn-sm text-white"
                    spinner="voucher({{ $order->id }})" />
            @endscope
        </x-table>
    </x-card>

    <x-modal wire:model="markup_voucher" title="Markup Custom Price">
        <x-input type="number" label="Custom Sub Total Amount" wire:model="total_amount" class="mb-4" required
            readonly />
        <x-input type="number" label="Enter Custom Total Amount" wire:model="custom_amount"
            placeholder="Enter Custom Total Amount" required />
        <x-slot:actions>
            <x-button label="Close" @click="$wire.markup_voucher = false" class="btn btn-sm" />
            <x-button label="Download Voucher" wire:click="downloadVoucher({{ $selectedOrder ?? '' }})"
                class="btn btn-sm btn-primary" external />
        </x-slot:actions>
    </x-modal>
</div>
