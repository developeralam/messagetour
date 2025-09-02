<?php

use App\Models\GroupFlightBooking;
use Mary\Traits\Toast;
use Livewire\Volt\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;

new #[Layout('components.layouts.customer')] #[Title('Group Flight Booking')] class extends Component {
    use WithPagination, Toast;
    public array $headers;
    public string $search = '';
    public function mount()
    {
        $this->headers = $this->headers();
    }
    public function headers(): array
    {
        return [['key' => 'id', 'label' => '#'], ['key' => 'groupflight.title', 'label' => 'Title'], ['key' => 'type', 'label' => 'Flight Type'], ['key' => 'groupflight.journey_route', 'label' => 'Journey Route'], ['key' => 'journey_date', 'label' => 'Journey Date'], ['key' => 'groupflight.journey_transit', 'label' => 'Journey Transit'], ['key' => 'groupflight.return_route', 'label' => 'Return Route'], ['key' => 'return_date', 'label' => 'Return Date'], ['key' => 'groupflight.return_transit', 'label' => 'Return Transit'], ['key' => 'groupflight.airline_name', 'label' => 'Airline Name'], ['key' => 'groupflight.airline_code', 'label' => 'Airline code'], ['key' => 'created_at', 'label' => 'Booking Date']];
    }

    /**
     * Fetch groupflight bookings for the authenticated user.
     *
     * @return Livewire\WithPagination
     */
    public function groupflight()
    {
        return GroupFlightBooking::query()
            ->with(['groupflight'])
            ->where('user_id', auth()->user()->id)
            ->latest()
            ->paginate(10);
    }

    public function with(): array
    {
        return [
            'groupflight' => $this->groupflight(),
        ];
    }
}; ?>

<div>
    <x-header title="Group Flight Booking List" size="text-xl" separator class="bg-white px-2 pt-2" />
    <x-card>
        <x-table :headers="$headers" :rows="$groupflight" with-pagination>
            @scope('cell_id', $group, $groupflight)
                {{ $loop->iteration + ($groupflight->currentPage() - 1) * $groupflight->perPage() }}
            @endscope
            @scope('cell_type', $group)
                <x-badge value="{{ $group->groupflight->type->label() }}" class="bg-primary text-white p-3 text-xs" />
            @endscope
            @scope('cell_created_at', $group)
                {{ $group->created_at->format('d M, Y') }}
            @endscope
            @scope('cell_journey_date', $group)
                {{ $group->groupflight->journey_date->format('d M, Y') }}
            @endscope
            @scope('cell_return_date', $group)
                {{ $group->groupflight->return_date->format('d M, Y') }}
            @endscope
        </x-table>
    </x-card>
</div>
