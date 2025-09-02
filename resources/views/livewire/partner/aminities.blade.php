<?php

use App\Models\Aminities;
use Mary\Traits\Toast;
use App\Models\Country;
use Livewire\Volt\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Rule;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;
use Illuminate\Database\Eloquent\Builder;

new #[Layout('components.layouts.partner')] #[Title('Aminities')] class extends Component {
    use WithPagination, Toast;

    public string $search = '';
    public Aminities $aminity;

    #[Rule('required')]
    public $name;

    public bool $createModal = false;
    public bool $editModal = false;

    public function storeAminity()
    {
        try {
            $this->validate();
            Aminities::create([
                'name' => $this->name,
                'created_by' => auth()->user()->id,
            ]);
            $this->reset(['name']);
            $this->success('Aminities Added Successfully');
            $this->createModal = false;
        } catch (\Throwable $th) {
            $this->createModal = false;
            $this->error(env('APP_DEBUG', false) ? $th->getMessage() : 'Something went wrong');
        }
    }
    public function edit(Aminities $aminities)
    {
        $this->reset(['name']);
        $this->aminity = $aminities;
        $this->name = $aminities->name;
        $this->editModal = true;
    }
    public function updateAminity()
    {
        try {
            $this->validate();
            $this->aminity->update([
                'name' => $this->name,
                'action_by' => auth()->user()->id,
            ]);
            $this->reset(['name']);
            $this->success('Aminities Updated Successfully');
            $this->editModal = false;
        } catch (\Throwable $th) {
            $this->error(env('APP_DEBUG', false) ? $th->getMessage() : 'Something went wrong');
        }
    }
    public function delete(Aminities $aminities)
    {
        try {
            $aminities->update([
                'action_by' => auth()->user()->id,
            ]);
            $aminities->delete();
            $this->success('Aminities Deleted Successfully');
        } catch (\Throwable $th) {
            $this->error(env('APP_DEBUG', false) ? $th->getMessage() : 'Something went wrong');
        }
    }
    public function headers(): array
    {
        return [['key' => 'id', 'label' => '#'], ['key' => 'name', 'label' => 'Aminities Name'], ['key' => 'action_by', 'label' => 'Last Action By']];
    }
    public function aminities()
    {
        return Aminities::query()
            ->with('actionBy')
            ->where('created_by', auth()->user()->id)
            ->latest()
            ->paginate(20);
    }
    public function updated($property)
    {
        if (!is_array($property) && $property != '') {
            $this->resetPage();
        }
    }
    public function with(): array
    {
        return [
            'headers' => $this->headers(),
            'aminities' => $this->aminities(),
        ];
    }
}; ?>

<div>
    <x-header title="Aminities List" size="text-xl" separator class="bg-white px-2 pt-2">
        <x-slot:actions>
            <x-button icon="fas.plus" @click="$wire.createModal = true" label="Add Aminities" class="btn-primary btn-sm" />
        </x-slot:actions>
    </x-header>
    <x-card>
        <x-table :headers="$headers" :rows="$aminities" with-pagination>
            @scope('cell_id', $aminity, $aminities)
                {{ $loop->iteration + ($aminities->currentPage() - 1) * $aminities->perPage() }}
            @endscope
            @scope('cell_action_by', $aminity)
                {{ $aminity->actionBy->name ?? '' }}
            @endscope
            @scope('actions', $aminity)
                <div class="flex items-center gap-1">
                    <x-button icon="o-trash" wire:click="delete({{ $aminity['id'] }})" wire:confirm="Are you sure?"
                        class="btn-error btn-action" />
                    <x-button icon="s-pencil-square" wire:click="edit({{ $aminity['id'] }})"
                        class="btn-neutral btn-action" />
                </div>
            @endscope
        </x-table>
    </x-card>
    <x-modal wire:model="createModal" title="Add New Aminities" separator>
        <x-form wire:submit="storeAminity">
            <x-input label="Aminities Name" wire:model="name" placeholder="Aminities Name" required />
            <x-slot:actions>
                <x-button label="Cancel" @click="$wire.createModal = false" class="btn-sm" />
                <x-button type="submit" label="Add Aminities" class="btn-primary btn-sm" spinner="storeAminity" />
            </x-slot:actions>
        </x-form>
    </x-modal>
    <x-modal wire:model="editModal" title="Update {{ $aminities->name ?? '' }} Aminities" separator>
        <x-form wire:submit="updateAminity">
            <x-input label="Aminities Name" wire:model="name" placeholder="Aminities Name" required />
            <x-slot:actions>
                <x-button label="Cancel" @click="$wire.editModal = false" class="btn-sm" />
                <x-button type="submit" label="Update Aminities" class="btn-primary btn-sm" spinner="updateAminity" />
            </x-slot:actions>
        </x-form>
    </x-modal>
</div>
