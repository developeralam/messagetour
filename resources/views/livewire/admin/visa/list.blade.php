<?php

use App\Models\Visa;
use Mary\Traits\Toast;
use Livewire\Volt\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;
use Illuminate\Database\Eloquent\Builder;

new #[Layout('components.layouts.admin')] #[Title('Visa List')] class extends Component {
    use WithPagination;
    use Toast;
    public array $headers;
    public string $search = '';
    public array $sortBy = ['column' => 'id', 'direction' => 'desc'];
    public function mount()
    {
        $this->headers = $this->headers();
    }
    public function delete(Visa $visa)
    {
        try {
            $visa->update([
                'action_id' => auth()->user()->id,
            ]);
            $visa->delete();
            $this->success('Visa deleted successfully');
        } catch (\Throwable $th) {
            $this->error($th->getMessage());
        }
    }
    public function headers(): array
    {
        return [['key' => 'id', 'label' => '#'], ['key' => 'title', 'label' => 'Visa Title'], ['key' => 'type', 'label' => 'Type'], ['key' => 'origin.name', 'label' => 'Origin'], ['key' => 'destination.name', 'label' => 'Destination'], ['key' => 'processing_time', 'label' => 'Processing Time'], ['key' => 'convenient_fee', 'label' => 'Convenient Fee'], ['key' => 'status', 'label' => 'Status'], ['key' => 'action_id', 'label' => 'Last Action By']];
    }
    public function visas()
    {
        return Visa::query()
            ->with(['origin:id,name', 'destination:id,name'])
            ->when($this->search, fn(Builder $q) => $q->where('title', 'LIKE', "%$this->search%"))
            ->orderBy(...array_values($this->sortBy))
            ->paginate(10);
    }
    public function with(): array
    {
        return [
            'visas' => $this->visas(),
        ];
    }
}; ?>

<div>
    <x-header title="Visa List" size="text-xl" separator class="bg-white px-2 pt-2">
        <x-slot:middle class="!justify-end">
            <x-input icon="o-bolt" wire:model.live="search" placeholder="Search..." />
        </x-slot:middle>
        <x-slot:actions>
            <x-button label="Add Visa" icon="o-plus" no-wire-navigate link="/admin/visa/create"
                class="btn-primary btn-sm" />
        </x-slot:actions>
    </x-header>
    <x-card>
        <x-table :headers="$headers" :rows="$visas" :sort-by="$sortBy" with-pagination>
            @scope('cell_id', $visa, $visas)
                {{ $loop->iteration + ($visas->currentPage() - 1) * $visas->perPage() }}
            @endscope
            @scope('cell_type', $visa)
                <x-badge value="{{ $visa->type->label() }}" class="bg-primary text-white p-3 text-xs" />
            @endscope
            @scope('cell_action_id', $visa)
                {{ $visa->actionBy->name ?? '' }}
            @endscope
            @scope('cell_processing_time', $visa)
                {{ $visa->processing_time }} Days
            @endscope
            @scope('cell_convenient_fee', $visa)
                BDT {{ $visa->convenient_fee }}
            @endscope
            @scope('cell_status', $visa)
                @if ($visa->status == \App\Enum\VisaStatus::Active)
                    <x-badge value="{{ $visa->status->label() }}"
                        class="bg-green-100 text-green-700 p-3 text-xs font-semibold" />
                @elseif ($visa->status == \App\Enum\VisaStatus::Inactive)
                    <x-badge value="{{ $visa->status->label() }}"
                        class="bg-red-100 text-red-700 p-3 text-xs font-semibold" />
                @endif
            @endscope
            @scope('actions', $visa)
                <div class="flex items-center gap-1">
                    <x-button icon="o-trash" wire:click="delete({{ $visa['id'] }})" wire:confirm="Are you sure?"
                        class="btn-error btn-action" spinner="delete({{ $visa['id'] }})" />
                    <x-button icon="s-pencil-square" no-wire-navigate link="/admin/visa/{{ $visa['id'] }}/edit"
                        class="btn-neutral btn-action" />
                </div>
            @endscope
        </x-table>

    </x-card>
</div>
