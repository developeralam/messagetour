<?php

use App\Models\Hotel;
use Mary\Traits\Toast;
use App\Models\HotelRoom;
use Livewire\Volt\Component;
use Livewire\WithPagination;
use App\Enum\HotelRoomStatus;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Builder;

new #[Layout('components.layouts.admin')] #[Title('Hotel Room List')] class extends Component {
    use WithPagination;
    use Toast;
    public array $headers;
    public string $search = '';
    public $hotel = [];
    public array $sortBy = ['column' => 'id', 'direction' => 'desc'];

    public function mount($hotel)
    {
        $this->hotel = Hotel::with('rooms')->findOrFail($hotel);
        $this->headers = $this->headers();
    }
    public function delete(HotelRoom $room)
    {
        try {
            $room->update([
                'action_id' => auth()->user()->id,
            ]);
            $room->delete();
            $this->success('Room Deleted Successfully');
        } catch (\Throwable $th) {
            $this->error($th->getMessage());
        }
    }
    public function book(HotelRoom $room)
    {
        if ($room->isBookedForDates(now()->toDateString(), now()->addDay()->toDateString())) {
            return $this->error('Room is already booked for the selected date.');
        }

        try {
            $room->update([
                'status' => HotelRoomStatus::Booked,
                'action_id' => auth()->user()->id,
            ]);
            $this->success('Room Booked Successfully');
        } catch (\Throwable $th) {
            $this->error($th->getMessage());
        }
    }

    public function headers(): array
    {
        return [['key' => 'id', 'label' => '#'], ['key' => 'thumbnail', 'label' => 'Thumbnail'], ['key' => 'name', 'label' => 'Room Name'], ['key' => 'room_no', 'label' => 'Room No'], ['key' => 'type', 'label' => 'Room Type'], ['key' => 'regular_price', 'label' => 'Regular Price'], ['key' => 'offer_price', 'label' => 'Offer Price'], ['key' => 'status', 'label' => 'Status'], ['key' => 'action_id', 'label' => 'Last Action By']];
    }
    public function rooms()
    {
        return HotelRoom::query()
            ->where('hotel_id', $this->hotel->id)
            ->when($this->search, fn(Builder $q) => $q->where('name', 'LIKE', "%$this->search%"))
            ->orderBy(...array_values($this->sortBy))
            ->paginate(10)
            ->through(function ($room) {
                $room->is_booked = $room->isBookedForDates(now()->toDateString(), now()->addDay()->toDateString());
                return $room;
            });
    }

    public function with(): array
    {
        return [
            'rooms' => $this->rooms(),
        ];
    }
}; ?>

<div>
    <x-header title="Room List Of - {{ $hotel->name }} Hotel" size="text-xl" separator class="bg-white px-2 pt-2">
        <x-slot:middle class="!justify-end">
            <x-input icon="o-bolt" wire:model.live="search" placeholder="Search..." />
        </x-slot:middle>
        <x-slot:actions>
            <x-button label="Add Room" icon="o-plus" link="/admin/hotel/{{ $hotel->id }}/room/create"
                class="btn-primary btn-sm" />
        </x-slot:actions>
    </x-header>
    <x-card>
        <x-table :headers="$headers" :rows="$rooms" :sort-by="$sortBy" with-pagination>
            @scope('cell_id', $room, $rooms)
                {{ $loop->iteration + ($rooms->currentPage() - 1) * $rooms->perPage() }}
            @endscope
            @scope('cell_thumbnail', $room)
                <x-avatar image="{{ $room->thumbnail_link ?? '/empty-product.png' }}" class="!w-10" />
            @endscope
            @scope('cell_type', $room)
                <x-badge value="{{ $room->type->label() }}" class="bg-green-100 text-green-700 p-3 text-xs font-semibold" />
            @endscope
            @scope('cell_status', $room)
                @if ($room->status == HotelRoomStatus::Booked)
                    <x-badge value="{{ $room->status->label() }}"
                        class="bg-red-100 text-red-700 p-3 text-xs font-semibold" />
                @else
                    <x-badge value="{{ $room->status->label() }}"
                        class="bg-green-100 text-green-700 p-3 text-xs font-semibold" />
                @endif
            @endscope
            @scope('cell_action_id', $room)
                {{ $room->actionBy->name ?? '' }}
            @endscope
            @scope('actions', $room)
                <div class="flex items-center gap-1">
                    <x-button icon="o-trash" wire:click="delete({{ $room['id'] }})" wire:confirm="Are you sure?"
                        class="btn-error btn-action" spinner="delete({{ $room['id'] }})" />
                    <x-button icon="s-pencil-square"
                        link="/admin/hotel/{{ $room['hotel_id'] }}/room/{{ $room['id'] }}/edit"
                        class="btn-neutral btn-action" />
                    @if (!$room['is_booked'])
                        <x-button icon="fas.check" wire:click="book({{ $room['id'] }})"
                            class="btn-primary btn-action text-white" spinner="book({{ $room['id'] }})" />
                    @endif

                </div>
            @endscope
        </x-table>

    </x-card>
</div>
