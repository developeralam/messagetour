<?php

use App\Models\Hotel;
use Mary\Traits\Toast;
use Livewire\Volt\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;
use Illuminate\Database\Eloquent\Builder;

new #[Layout('components.layouts.partner')] #[Title('Hotel List')] class extends Component {
    use WithPagination;
    use Toast;
    public array $headers;
    public string $search = '';
    public function mount()
    {
        $this->headers = $this->headers();
    }
    public function delete(Hotel $hotel)
    {
        try {
            $hotel->update([
                'action_id' => auth()->user()->id,
            ]);
            $hotel->delete();
            $this->success('Hotel Deleted Successfully');
        } catch (\Throwable $th) {
            $this->error($th->getMessage());
        }
    }
    public function headers(): array
    {
        return [['key' => 'id', 'label' => '#'], ['key' => 'thumbnail', 'label' => 'Thumbnail'], ['key' => 'name', 'label' => 'Hotel Name'], ['key' => 'country.name', 'label' => 'Country'], ['key' => 'address', 'label' => 'Address'], ['key' => 'rooms_count', 'label' => 'Total Room'], ['key' => 'is_featured', 'label' => 'Is Featured'], ['key' => 'status', 'label' => 'Status']];
    }
    public function hotels()
    {
        return Hotel::query()
            ->withCount('rooms')
            ->with('country')
            ->where('created_by', auth()->user()->id)
            ->when($this->search, fn(Builder $q) => $q->where('name', 'LIKE', "%$this->search%"))
            ->latest()
            ->paginate(10);
    }
    public function with(): array
    {
        return [
            'hotels' => $this->hotels(),
        ];
    }
}; ?>

<div>
    <x-header title="Hotel List" separator size="text-xl" class="bg-white px-2 pt-2">
        <x-slot:middle class="!justify-end">
            <x-input icon="o-bolt" wire:model.live="search" placeholder="Search..." class="max-w-32" />
        </x-slot:middle>
        <x-slot:actions>
            <x-button label="Add Hotel" icon="o-plus" no-wire-navigate link="/partner/hotel/create"
                class="btn-primary btn-sm" />
        </x-slot:actions>
    </x-header>
    <x-card>
        <x-table :headers="$headers" :rows="$hotels" with-pagination>
            @scope('cell_id', $hotel, $hotels)
                {{ $loop->iteration + ($hotels->currentPage() - 1) * $hotels->perPage() }}
            @endscope
            @scope('cell_thumbnail', $hotel)
                <x-avatar image="{{ $hotel->thumbnail_link ?? '/empty-hotel.png' }}" class="!w-10" />
            @endscope
            @scope('cell_is_featured', $hotel)
                @if ($hotel->is_featured == 1)
                    <x-badge value="Yes" class="bg-green-100 text-green-700 p-3 text-xs font-semibold" />
                @else
                    <x-badge value="No" class="bg-red-100 text-red-700 p-3 text-xs font-semibold" />
                @endif
            @endscope
            @scope('cell_action_id', $hotel)
                {{ $hotel->actionBy->name ?? '' }}
            @endscope
            @scope('cell_status', $hotel)
                @if ($hotel->status == \App\Enum\HotelStatus::Active)
                    <x-badge value="{{ $hotel->status->label() }}"
                        class="bg-green-100 text-green-700 p-3 text-xs font-semibold" />
                @elseif ($hotel->status == \App\Enum\HotelStatus::Pending)
                    <x-badge value="{{ $hotel->status->label() }}"
                        class="bg-yellow-100 text-yellow-700 p-3 text-xs font-semibold" />
                @elseif ($hotel->status == \App\Enum\HotelStatus::Inactive)
                    <x-badge value="{{ $hotel->status->label() }}"
                        class="bg-red-100 text-red-700 p-3 text-xs font-semibold" />
                @endif
            @endscope
            @scope('actions', $hotel)
                <div class="flex items-center gap-1">
                    <x-button icon="o-trash" wire:click="delete({{ $hotel['id'] }})" wire:confirm="Are you sure?"
                        class="btn-error btn-action" spinner="delete({{ $hotel['id'] }})" />
                    <x-button icon="s-pencil-square" no-wire-navigate link="/partner/hotel/{{ $hotel['id'] }}/edit"
                        class="btn-neutral btn-action" />
                    <x-button icon="o-eye" link="/partner/hotel/{{ $hotel['id'] }}/room/list"
                        class="btn-primary btn-action text-white" />
                </div>
            @endscope
        </x-table>

    </x-card>
</div>
