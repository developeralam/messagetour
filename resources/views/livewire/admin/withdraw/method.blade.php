<?php

use Mary\Traits\Toast;
use App\Models\WithdrawMethod;
use Livewire\Volt\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Rule;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;
use Illuminate\Database\Eloquent\Builder;

new #[Layout('components.layouts.admin')] #[Title('Withdraw Method')] class extends Component {
    use WithPagination, Toast;

    public string $search = '';
    public array $headers;
    public WithdrawMethod $withdrawMethod;

    #[Rule('required')]
    public string $name = '';

    #[Rule('required')]
    public string $charge = '';

    #[Rule('nullable')]
    public string $status = '';

    public bool $createModal = false;
    public bool $editModal = false;

    public function mount()
    {
        $this->headers = $this->headers();
    }
    public function storeWithdrawMethod()
    {
        $this->validate();
        try {
            WithdrawMethod::create([
                'name' => $this->name,
                'charge' => $this->charge,
                'status' => true,
            ]);
            $this->createModal = false;
            $this->success('Withdraw Method Added Successfully');
            $this->reset(['name', 'charge']);
        } catch (\Throwable $th) {
            $this->createModal = false;
            $this->error(env('APP_DEBUG', false) ? $th->getMessage() : 'Something went wrong');
        }
    }
    public function edit(WithdrawMethod $withdrawMethod)
    {
        $this->withdrawMethod = $withdrawMethod;
        $this->name = $withdrawMethod->name;
        $this->charge = $withdrawMethod->charge;
        $this->status = $withdrawMethod->status;
        $this->editModal = true;
    }
    public function updateWithdrawMethod()
    {
        $this->validate();
        try {
            $this->withdrawMethod->update([
                'name' => $this->name,
                'charge' => $this->charge,
                'status' => $this->status,
                'action_by' => auth()->user()->id,
            ]);
            $this->success('Withdraw Method Updated Successfully');
            $this->editModal = false;
            $this->reset(['name', 'charge']);
        } catch (\Throwable $th) {
            $this->editModal = false;
            $this->error(env('APP_DEBUG', false) ? $th->getMessage() : 'Something went wrong');
        }
    }
    public function delete(WithdrawMethod $withdrawMethod)
    {
        try {
            $withdrawMethod->update([
                'action_by' => auth()->user()->id,
            ]);
            $withdrawMethod->delete();
            $this->success('Withdraw Method Deleted Successfully');
        } catch (\Throwable $th) {
            $this->error(env('APP_DEBUG', false) ? $th->getMessage() : 'Something went wrong');
        }
    }
    public function headers(): array
    {
        return [['key' => 'id', 'label' => '#'], ['key' => 'name', 'label' => 'Method Name'], ['key' => 'charge', 'label' => 'Charge(%)'], ['key' => 'action_by', 'label' => 'Last Action By']];
    }
    public function methods()
    {
        return WithdrawMethod::query()->with('actionBy')->when($this->search, fn(Builder $q) => $q->where('name', 'LIKE', "%$this->search%"))->latest()->paginate(10);
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
            'methods' => $this->methods(),
        ];
    }
}; ?>

<div>
    <x-header title="Withdraw Method" size="text-xl" separator class="bg-white px-2 pt-2">
        <x-slot:middle class="!justify-end">
            <x-input icon="o-bolt" wire:model.live="search" placeholder="Search..." />
        </x-slot:middle>
        <x-slot:actions>
            <x-button icon="o-plus" @click="$wire.createModal = true" label="Add Method" class="btn-primary btn-sm" />
        </x-slot:actions>
    </x-header>
    <x-card>
        <x-table :headers="$headers" :rows="$methods" with-pagination>
            @scope('cell_id', $method, $methods)
                {{ $loop->iteration + ($methods->currentPage() - 1) * $methods->perPage() }}
            @endscope
            @scope('cell_action_by', $method)
                {{ $method->actionBy->name ?? 'N/A' }}
            @endscope
            @scope('actions', $method)
                <div class="flex items-center gap-1">
                    <x-button icon="o-trash" wire:click="delete({{ $method->id }})" wire:confirm="Are you sure?"
                        class="btn-error btn-action" spinner="delete({{ $method->id }})" />
                    <x-button icon="s-pencil-square" wire:click="edit({{ $method->id }})" class="btn-neutral btn-action"
                        spinner="edit({{ $method->id }})" />
                </div>
            @endscope
        </x-table>
    </x-card>
    <x-modal wire:model="createModal" title="Add New Withdraw Method" size="text-md" separator>
        <x-form wire:submit="storeWithdrawMethod">
            <x-input label="Method Name" wire:model="name" placeholder="Method Name" required />
            <x-input label="Method Charge(%)" wire:model="charge" placeholder="Method Charge" required />
            <x-slot:actions>
                <x-button label="Cancel" @click="$wire.createModal = false" class="btn-sm" />
                <x-button type="submit" label="Add Method" class="btn-primary btn-sm" spinner="storeWithdrawMethod" />
            </x-slot:actions>
        </x-form>
    </x-modal>
    <x-modal wire:model="editModal" title="Update {{ $withdrawMethod->name ?? '' }}" size="text-md" separator>
        <x-form wire:submit="updateWithdrawMethod">
            <x-input label="Method Name" wire:model="name" placeholder="Method Name" required />
            <x-input label="Method Charge(%)" wire:model="charge" placeholder="Method Charge" required />
            <x-slot:actions>
                <x-button label="Cancel" @click="$wire.editModal = false" class="btn-sm" />
                <x-button type="submit" label="Update Method" class="btn-primary btn-sm"
                    spinner="updateWithdrawMethod" />
            </x-slot:actions>
        </x-form>
    </x-modal>
</div>
