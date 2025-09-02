<?php

use App\Models\Tour;
use Mary\Traits\Toast;
use App\Enum\TourStatus;
use Livewire\Volt\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;
use Illuminate\Database\Eloquent\Builder;

new #[Layout('components.layouts.admin')] #[Title('Tour List')] class extends Component {
    use WithPagination;
    use Toast;
    public array $headers;
    public string $search = '';
    public array $sortBy = ['column' => 'id', 'direction' => 'desc'];
    public function mount()
    {
        $this->headers = $this->headers();
    }
    public function delete(Tour $tour)
    {
        try {
            $tour->update([
                'action_id' => auth()->user()->id,
            ]);
            $tour->delete();
            $this->success('Tour deleted successfully');
        } catch (\Throwable $th) {
            $this->error($th->getMessage());
        }
    }
    public function approve(Tour $tour)
    {
        try {
            $tour->update([
                'action_id' => auth()->user()->id,
                'status' => TourStatus::Active,
            ]);
            $this->success('Tour Approve Successfully');
        } catch (\Throwable $th) {
            $this->error(env('APP_DEBUG', false) ? $th->getMessage() : 'Something went wrong');
        }
    }
    public function headers(): array
    {
        return [['key' => 'id', 'label' => '#'], ['key' => 'thumbnail', 'label' => 'Thumbnail'], ['key' => 'title', 'label' => 'Title'], ['key' => 'type', 'label' => 'Tour Type'], ['key' => 'location', 'label' => 'Location'], ['key' => 'country.name', 'label' => 'Country'], ['key' => 'start_date', 'label' => 'Start Date'], ['key' => 'end_date', 'label' => 'End Date'], ['key' => 'validity', 'label' => 'Tour Validity'], ['key' => 'is_featured', 'label' => 'Is Featured ?'], ['key' => 'status', 'label' => 'Status'], ['key' => 'action_id', 'label' => 'Last Action By']];
    }
    public function tours()
    {
        return Tour::query()
            ->with(['country', 'actionBy'])
            ->whereNull('created_by')
            ->when($this->search, fn(Builder $q) => $q->where('title', 'LIKE', "%$this->search%"))
            ->orderBy(...array_values($this->sortBy))
            ->paginate(10);
    }
    public function with(): array
    {
        return [
            'tours' => $this->tours(),
        ];
    }
}; ?>

<div>
    <x-header title="Tour List" size="text-xl" separator class="bg-white px-2 pt-2">
        <x-slot:middle class="!justify-end">
            <x-input icon="o-bolt" wire:model.live="search" placeholder="Search..." />
        </x-slot:middle>
        <x-slot:actions>
            <x-button label="Add Tour" icon="o-plus" no-wire-navigate link="/admin/tour/create"
                class="btn-primary btn-sm" />
        </x-slot:actions>
    </x-header>
    <x-card>
        <x-table :headers="$headers" :rows="$tours" :sort-by="$sortBy" with-pagination>
            @scope('cell_id', $tour, $tours)
                {{ $loop->iteration + ($tours->currentPage() - 1) * $tours->perPage() }}
            @endscope
            @scope('cell_is_featured', $tour)
                @if ($tour->is_featured == 1)
                    <x-badge value="Yes" class="bg-green-100 text-green-700 p-3 text-xs font-semibold" />
                @else
                    <x-badge value="No" class="bg-red-100 text-red-700 p-3 text-xs font-semibold" />
                @endif
            @endscope
            @scope('cell_start_date', $tour)
                {{ $tour->start_date->format('d M, Y') }}
            @endscope
            @scope('cell_end_date', $tour)
                {{ $tour->end_date->format('d M, Y') }}
            @endscope
            @scope('cell_validity', $tour)
                {{ optional($tour->validity)?->format('d M, Y') ?? '' }}
            @endscope
            @scope('cell_thumbnail', $tour)
                <x-avatar image="{{ $tour->thumbnail_link ?? '/empty-product.png' }}" class="!w-10" />
            @endscope
            @scope('cell_status', $tour)
                @if ($tour->status == \App\Enum\TourStatus::Active)
                    <x-badge value="{{ $tour->status->label() }}"
                        class="bg-green-100 text-green-700 p-3 text-xs font-semibold" />
                @elseif ($tour->status == \App\Enum\TourStatus::Pending)
                    <x-badge value="{{ $tour->status->label() }}"
                        class="bg-yellow-100 text-yellow-700 p-3 text-xs font-semibold" />
                @elseif ($tour->status == \App\Enum\TourStatus::Inactive)
                    <x-badge value="{{ $tour->status->label() }}"
                        class="bg-red-100 text-red-700 p-3 text-xs font-semibold" />
                @endif
            @endscope
            @scope('cell_type', $tour)
                <x-badge value="{{ $tour->type->label() }}" class="bg-primary text-white p-3 text-xs" />
            @endscope
            @scope('cell_action_id', $tour)
                {{ $tour->actionBy->name ?? '' }}
            @endscope
            @scope('actions', $tour)
                <div class="flex items-center gap-1">
                    @if ($tour->status == \App\Enum\TourStatus::Pending)
                        <x-button icon="fas.check" wire:click="approve({{ $tour->id }})"
                            wire:confirm="Are you sure approve this tour?" class="btn-primary btn-action text-white"
                            spinner="approve({{ $tour['id'] }})" />
                    @endif
                    <x-button icon="o-trash" wire:click="delete({{ $tour['id'] }})" wire:confirm="Are you sure?"
                        class="btn-error btn-action" spinner="delete({{ $tour['id'] }})" />
                    <x-button icon="s-pencil-square" no-wire-navigate link="/admin/tour/{{ $tour['id'] }}/edit"
                        class="btn-neutral btn-action" />
                </div>
            @endscope
        </x-table>

    </x-card>
</div>
