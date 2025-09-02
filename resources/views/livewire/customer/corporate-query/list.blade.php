<?php

use Mary\Traits\Toast;
use App\Enum\HotelType;
use App\Models\Country;
use App\Enum\HotelRoomType;
use Livewire\Volt\Component;
use Livewire\WithPagination;
use App\Models\CorporateQuery;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;
use Illuminate\Database\Eloquent\Builder;

new #[Layout('components.layouts.customer')] #[Title('Corporate Query')] class extends Component {
    use WithPagination, Toast;

    public array $headers;
    public string $search = '';
    public array $sortBy = ['column' => 'id', 'direction' => 'desc'];

    /**
     * Livewire mount method.
     * Initializes table headers for the corporate query listing.
     *
     * @return void
     */
    public function mount(): void
    {
        $this->headers = $this->headers();
        $this->hotelTypes = HotelType::getHotelTypes(); // All available hotel types
        $this->hotelRoomTypes = HotelRoomType::getHotelRoomTypes(); // Room types
        $this->destinationCountries = Country::select(['id', 'name'])->get(); // Country options
    }

    /**
     * Deletes a specific corporate query entry.
     *
     * @param CorporateQuery $query
     * @return void
     */
    public function delete(CorporateQuery $query): void
    {
        try {
            $query->delete();
            $this->success('Query Deleted Successfully');
        } catch (\Throwable $th) {
            // Show debug error message if exception occurs
            $this->error($th->getMessage());
        }
    }

    /**
     * Defines table column headers for corporate query listing.
     *
     * @return array
     */
    public function headers(): array
    {
        return [['key' => 'id', 'label' => '#'], ['key' => 'name', 'label' => 'Name'], ['key' => 'email', 'label' => 'Email'], ['key' => 'phone', 'label' => 'Mobile No'], ['key' => 'destination.name', 'label' => 'Destination'], ['key' => 'formatted_travel_date', 'label' => 'Travel Start/End Date'], ['key' => 'program', 'label' => 'Program'], ['key' => 'hotel_type', 'label' => 'Hotel Type'], ['key' => 'hotel_room_type', 'label' => 'Room Type'], ['key' => 'visa_service', 'label' => 'Visa Service'], ['key' => 'air_ticket', 'label' => 'Air Ticket'], ['key' => 'tour_guide', 'label' => 'Tour Guide'], ['key' => 'status', 'label' => 'Status']];
    }

    /**
     * Fetches and filters paginated list of corporate queries.
     *
     * @return Livewire\WithPagination
     */
    public function queries()
    {
        return CorporateQuery::query()
            ->with(['destination']) // Eager-load destination country
            ->where('user_id', auth()->id())
            ->when($this->search, fn(Builder $q) => $q->whereAny(['name', 'email', 'phone', 'program'], 'LIKE', "%{$this->search}%"))
            ->orderBy(...array_values($this->sortBy))
            ->paginate(10);
    }

    /**
     * Provides data to be used inside the Livewire view.
     *
     * @return array
     */
    public function with(): array
    {
        return [
            'queries' => $this->queries(),
        ];
    }
}; ?>

<div>
    <x-header title="Corporate Query List" size="text-xl" separator class="bg-white px-2 pt-2">
        <x-slot:middle class="!justify-end">
            <x-input icon="o-bolt" wire:model.live="search" placeholder="Search..." />
        </x-slot:middle>
    </x-header>
    <x-card>
        <x-table :headers="$headers" :rows="$queries" :sort-by="$sortBy" with-pagination>
            @scope('cell_id', $query, $queries)
                {{ $loop->iteration + ($queries->currentPage() - 1) * $queries->perPage() }}
            @endscope
            @scope('cell_visa_service', $query)
                {{ $query->visa_service == 1 ? 'Yes' : 'No' }}
            @endscope
            @scope('cell_air_ticket', $query)
                {{ $query->air_ticket == 1 ? 'Yes' : 'No' }}
            @endscope
            @scope('cell_tour_guide', $query)
                {{ $query->tour_guide == 1 ? 'Yes' : 'No' }}
            @endscope
            @scope('cell_hotel_type', $query)
                {{ $query->hotel_type->label() }}
            @endscope
            @scope('cell_hotel_room_type', $query)
                {{ $query->hotel_room_type->label() }}
            @endscope
            @scope('cell_status', $query)
                {{ $query->status->label() }}
            @endscope
            @scope('cell_status', $query)
                @if ($query->status == \App\Enum\CorporateQueryStatus::Answered)
                    <x-badge value="{{ $query->status->label() }}"
                        class="bg-green-100 text-green-700 p-3 text-xs font-semibold" />
                @elseif ($query->status == \App\Enum\CorporateQueryStatus::Pending)
                    <x-badge value="{{ $query->status->label() }}"
                        class="bg-yellow-100 text-yellow-700 p-3 text-xs font-semibold" />
                @elseif ($query->status == \App\Enum\CorporateQueryStatus::Inreview)
                    <x-badge value="{{ $query->status->label() }}"
                        class="bg-blue-100 text-blue-700 p-3 text-xs font-semibold" />
                @endif
            @endscope
            @scope('actions', $query)
                <div class="flex items-center gap-1">
                    <x-button icon="o-trash" wire:click="delete({{ $query['id'] }})" wire:confirm="Are you sure?"
                        class="btn-action btn-error" spinner="delete({{ $query['id'] }})" />
                    <x-button icon="s-pencil-square" link="/my-corporate-query/{{ $query['id'] }}/edit"
                        class="btn-action btn-primary" />
                    <x-button icon="fas.print" external link="/my-corporate-query/{{ $query['id'] }}"
                        class="btn-action btn-primary text-white" />
                </div>
            @endscope
        </x-table>
    </x-card>
</div>
