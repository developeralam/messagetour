<?php

use Carbon\Carbon;
use App\Models\Agent;
use App\Models\Order;
use App\Enum\UserType;
use Livewire\Volt\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;
use Illuminate\Database\Eloquent\Builder;

new #[Layout('components.layouts.admin')] #[Title('Agent Sale Report')] class extends Component {
    use WithPagination;

    public $search_by_agent;
    public $search_by_booking_type;
    public $search_by_booking_date_from;
    public $search_by_booking_date_to;
    public $agent_total_booking_amount = 0;
    public array $headers;
    public $agents = [];
    public $bookingTypes = [];

    public function mount(): void
    {
        $this->headers = $this->headers();
        $this->agents = Agent::with('user')
            ->select(['id', 'user_id'])
            ->get();
        $this->search_by_booking_date_from = Carbon::today()->format('Y-m-d');
        $this->bookingTypes = [['id' => 'Hotel', 'name' => 'Hotel'], ['id' => 'Tour', 'name' => 'Tour'], ['id' => 'TravelProduct', 'name' => 'Travel Product']];
    }

    public function headers(): array
    {
        return [['key' => 'id', 'label' => '#'], ['key' => 'agent_name', 'label' => 'Agent'], ['key' => 'business_name', 'label' => 'Business'], ['key' => 'booking_type', 'label' => 'Booking Type'], ['key' => 'booking_item', 'label' => 'Booking Item'], ['key' => 'created_at', 'label' => 'Booking Date'], ['key' => 'total_amount', 'label' => 'Total Amount']];
    }

    public function bookingReports()
    {
        $query = Order::with(['user.agent', 'sourceable'])->whereHas('user', fn($q) => $q->where('type', UserType::Agent));

        // Apply agent filter
        if ($this->search_by_agent) {
            $query->where('user_id', $this->search_by_agent);

            // Calculate total amount for the filtered agent
            $this->agent_total_booking_amount = (clone $query)->sum('total_amount');
        } else {
            $this->agent_total_booking_amount = 0;
        }

        // Apply remaining filters
        $query->when($this->search_by_booking_type, function ($q) {
            $typeMap = [
                'Hotel' => \App\Models\HotelRoomBooking::class,
                'Tour' => \App\Models\TourBooking::class,
                'TravelProduct' => \App\Models\TravelProductBooking::class,
            ];
            if (isset($typeMap[$this->search_by_booking_type])) {
                $q->where('sourceable_type', $typeMap[$this->search_by_booking_type]);
            }
        });

        $query->when($this->search_by_booking_date_from && $this->search_by_booking_date_to, fn($q) => $q->whereBetween('created_at', [Carbon::parse($this->search_by_booking_date_from)->startOfDay(), Carbon::parse($this->search_by_booking_date_to)->endOfDay()]));

        $query->when($this->search_by_booking_date_from && !$this->search_by_booking_date_to, fn($q) => $q->whereDate('created_at', Carbon::parse($this->search_by_booking_date_from)));

        return $query->latest()->paginate(10);
    }

    public function with()
    {
        return [
            'reports' => $this->bookingReports(),
        ];
    }
}; ?>

<div>
    <x-header title="Agent Sale Report List" size="text-xl" separator class="bg-white px-2 pt-2">
        <x-slot:actions class="!justify-end">
            <x-select wire:model.live="search_by_agent" placeholder="Select Agent" :options="$agents"
                option-label="user.name" option-value="user_id" />
            <x-select wire:model.live="search_by_booking_type" placeholder="Booking Type" :options="$bookingTypes"
                option-label="name" option-value="id" />
            <x-datetime type="date" wire:model.live="search_by_booking_date_from" label="From Date" inline />
            <x-datetime type="date" wire:model.live="search_by_booking_date_to" label="To Date" inline />
        </x-slot:actions>
    </x-header>

    <x-card>
        <x-table :headers="$headers" :rows="$reports" with-pagination>
            @scope('cell_id', $report, $reports)
                {{ $loop->iteration + ($reports->currentPage() - 1) * $reports->perPage() }}
            @endscope

            @scope('cell_agent_name', $report)
                {{ $report->user->name }}
            @endscope

            @scope('cell_business_name', $report)
                {{ $report->user->agent->business_name ?? '-' }}
            @endscope

            @scope('cell_booking_type', $report)
                @php
                    $bookingType = class_basename($report->sourceable_type);

                    $label = match ($bookingType) {
                        'HotelRoomBooking' => 'Hotel',
                        'TourBooking' => 'Tour',
                        'TravelProductBooking' => 'Travel Product',
                        default => ucfirst($bookingType),
                    };
                @endphp
                {{ $label }}
            @endscope


            @scope('cell_booking_item', $report)
                @php
                    $bookingType = class_basename($report->sourceable_type);
                    $itemName = match ($bookingType) {
                        'HotelRoomBooking' => optional(
                            optional($report->sourceable->hotelbookingitems->first())->room?->hotel,
                        )->name,
                        'TourBooking' => optional($report->sourceable->tour)->title,
                        'TravelProductBooking' => optional($report->sourceable->travelProduct)->title,
                        default => '-',
                    };
                @endphp
                {{ $itemName ?? '-' }}
            @endscope

            @scope('cell_created_at', $report)
                {{ $report->created_at->format('d M Y') }}
            @endscope

            @scope('cell_total_amount', $report)
                BDT {{ number_format($report->total_amount) }}
            @endscope

        </x-table>
        @if ($search_by_agent)
            <div class="mr-[122px] text-xs text-right font-semibold">
                Total Booking Amount: <span class="ml-4">BDT {{ number_format($agent_total_booking_amount) }}</span>
            </div>
        @endif
    </x-card>

</div>
