<?php

use App\Models\GroupFlight;
use Mary\Traits\Toast;
use Livewire\Volt\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;
use Illuminate\Database\Eloquent\Builder;

new #[Layout('components.layouts.admin')] #[Title('Group Flight List')] class extends Component {
    use WithPagination;
    use Toast;
    public array $headers;
    public string $search = '';
    public array $sortBy = ['column' => 'id', 'direction' => 'desc'];
    public function mount()
    {
        $this->headers = $this->headers();
    }
    public function delete(GroupFlight $flight)
    {
        try {
            $flight->update([
                'action_by' => auth()->user()->id,
            ]);
            $flight->delete();
            $this->success('Group Flight deleted successfully');
        } catch (\Throwable $th) {
            $this->error($th->getMessage());
        }
    }
    public function headers(): array
    {
        return [['key' => 'id', 'label' => '#'], ['key' => 'thumbnail', 'label' => 'Thumbnail'], ['key' => 'title', 'label' => 'Title'], ['key' => 'type', 'label' => 'Type'], ['key' => 'journey_route', 'label' => 'Journey Route'], ['key' => 'journey_transit', 'label' => 'Journey Transit'], ['key' => 'return_route', 'label' => 'Return Route'], ['key' => 'return_transit', 'label' => 'Return Transit'], ['key' => 'status', 'label' => 'Status'], ['key' => 'action_id', 'label' => 'Last Action By']];
    }
    public function flights()
    {
        return GroupFlight::query()->when($this->search, fn(Builder $q) => $q->where('title', 'LIKE', "%$this->search%"))->orderBy(...array_values($this->sortBy))->paginate(10);
    }
    public function with(): array
    {
        return [
            'flights' => $this->flights(),
        ];
    }
}; ?>

<div>
    <x-header title="Group Flight List" size="text-xl" separator class="bg-white px-2 pt-2">
        <x-slot:middle class="!justify-end">
            <x-input icon="o-bolt" wire:model.live="search" placeholder="Search..." />
        </x-slot:middle>
        <x-slot:actions>
            <x-button label="Add Group Flight" icon="o-plus" no-wire-navigate link="/admin/group-flight/create"
                class="btn-primary btn-sm" />
        </x-slot:actions>
    </x-header>
    <x-card>
        <x-table :headers="$headers" :rows="$flights" :sort-by="$sortBy" with-pagination>
            @scope('cell_id', $flight, $flights)
                {{ $loop->iteration + ($flights->currentPage() - 1) * $flights->perPage() }}
            @endscope
            @scope('cell_thumbnail', $flight)
                <x-avatar image="{{ $flight->thumbnail_link ?? '/group-flight.png' }}" class="!w-10" />
            @endscope
            @scope('cell_action_id', $flight)
                {{ $flight->actionBy->name ?? 'N/A' }}
            @endscope
            @scope('cell_type', $flight)
                <x-badge value="{{ $flight->type->label() }}" class="bg-primary text-white p-3 text-xs" />
            @endscope
            @scope('cell_status', $flight)
                @if ($flight->status == \App\Enum\GroupFlightStatus::Active)
                    <x-badge value="{{ $flight->status->label() }}"
                        class="bg-green-100 text-green-700 p-3 text-xs font-semibold" />
                @elseif ($flight->status == \App\Enum\GroupFlightStatus::Inactive)
                    <x-badge value="{{ $flight->status->label() }}"
                        class="bg-red-100 text-red-700 p-3 text-xs font-semibold" />
                @endif
            @endscope
            @scope('actions', $flight)
                <div class="flex items-center gap-1">
                    <x-button icon="o-trash" wire:click="delete({{ $flight['id'] }})" wire:confirm="Are you sure?"
                        class="btn-error btn-action" spinner="delete({{ $flight['id'] }})" />

                    <x-button icon="s-pencil-square" no-wire-navigate link="/admin/group-flight/{{ $flight['id'] }}/edit"
                        class="btn-neutral btn-action" />
                </div>
            @endscope
        </x-table>

    </x-card>
</div>
