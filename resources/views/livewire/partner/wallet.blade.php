<?php

use App\Enum\WithdrawStatus;
use App\Models\Withdraw;
use App\Models\WithdrawMethod;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Rule;
use Livewire\Attributes\Title;
use Livewire\Volt\Component;
use Livewire\WithPagination;
use Mary\Traits\Toast;

new #[Layout('components.layouts.partner')] #[Title('Wallet')] class extends Component {
    use Toast, WithPagination;

    public string $search = '';

    public array $headers;

    public Collection $methods;

    public Withdraw $withdraw;

    #[Rule('required')]
    public $withdraw_method_id;

    #[Rule('required|integer')]
    public $amount;

    #[Rule('required')]
    public $description;

    public bool $createModal = false;

    public bool $editModal = false;

    public function mount()
    {
        $this->headers = $this->headers();
        $this->methods = WithdrawMethod::all();
    }

    public function headers(): array
    {
        return [['key' => 'id', 'label' => '#'], ['key' => 'method.name', 'label' => 'Withdraw Method'], ['key' => 'amount', 'label' => 'Amount'], ['key' => 'description', 'label' => 'Description'], ['key' => 'trx_id', 'label' => 'Trx ID'], ['key' => 'status', 'label' => 'Status']];
    }

    public function delete(Withdraw $withdraw)
    {
        // Check if status is not pending
        if ($withdraw->status !== WithdrawStatus::Pending) {
            $this->error('Withdraw request cannot be deleted as it is not in pending.');

            return;
        }
        try {
            $withdraw->delete();
            $this->success('Withdraw Request Deleted Successfully');
        } catch (\Throwable $th) {
            $this->error($th->getMessage());
        }
    }

    public function withdraws()
    {
        return Withdraw::query()
            ->with(['agent.user', 'method'])
            ->when($this->search, fn(Builder $q) => $q->where('trx_id', 'LIKE', "%$this->search%"))
            ->latest()
            ->paginate(10);
    }

    public function storeWithdraw()
    {
        $this->validate();

        $agent = auth()->user()->agent;

        $error = match (true) {
            $agent->wallet <= 0 => 'You do not have any balance in your wallet.',
            $this->amount > $agent->wallet => 'Requested amount exceeds your wallet balance.',
            default => null,
        };

        if ($error) {
            $this->error($error);
            $this->createModal = false;
            return;
        }

        try {
            Withdraw::create([
                'agent_id' => $agent->id,
                'withdraw_method_id' => $this->withdraw_method_id,
                'amount' => $this->amount,
                'description' => $this->description,
                'status' => WithdrawStatus::Pending,
            ]);

            $this->reset(['withdraw_method_id', 'amount', 'description']);
            $this->createModal = false;
            $this->success('Withdraw Request Added Successfully');
        } catch (\Throwable $th) {
            $this->createModal = false;
            $this->error(env('APP_DEBUG', false) ? $th->getMessage() : 'Something went wrong');
        }
    }

    public function edit(Withdraw $withdraw)
    {
        // Check if status is not pending
        if ($withdraw->status !== WithdrawStatus::Pending) {
            $this->error('Withdraw request cannot be updated as it is not in pending.');

            return;
        }
        $this->withdraw = $withdraw;
        $this->name = $withdraw->name;
        $this->charge = $withdraw->charge;
        $this->status = $withdraw->status;
        $this->editModal = true;
    }

    public function updateWithdraw()
    {
        $this->validate();

        try {
            $this->withdraw->update([
                'withdraw_method_id' => $this->withdraw_method_id,
                'amount' => $this->amount,
                'description' => $this->description,
            ]);
            $this->success('Withdraw Request Updated Successfully');
            $this->editModal = false;
            $this->reset(['withdraw_method_id', 'amount', 'description']);
        } catch (\Throwable $th) {
            $this->editModal = false;
            $this->error(env('APP_DEBUG', false) ? $th->getMessage() : 'Something went wrong');
        }
    }

    public function with(): array
    {
        return [
            'withdraws' => $this->withdraws(),
        ];
    }
}; ?>


<div>
    <x-header title="Withdraw List" size="text-xl" separator class="bg-white px-2 pt-2">
        <x-slot:middle class="!justify-end">
            <h4 class="text-2xl font-extrabold">Wallet: BDT {{ number_format(auth()->user()->agent->wallet) }}</h4>
        </x-slot>
        <x-slot:actions>
            <x-button label="Add Request" icon="o-plus" @click="$wire.createModal = true" class="btn-primary btn-sm" />
        </x-slot>
    </x-header>
    <x-card>
        <x-table :headers="$headers" :rows="$withdraws" with-pagination>
            @scope('cell_id', $withdraw, $withdraws)
                {{ $loop->iteration + ($withdraws->currentPage() - 1) * $withdraws->perPage() }}
            @endscope

            @scope('cell_status', $withdraw)
                <x-badge value="{{ $withdraw->status->label() }}" class="bg-primary text-white p-3" />
            @endscope

            @scope('actions', $withdraw)
                <div class="flex items-center gap-1">
                    <x-button icon="o-trash" wire:click="delete({{ $withdraw['id'] }})" wire:confirm="Are you sure?"
                        spinner="delete({{ $withdraw['id'] }})" class="btn-error btn-action" />
                    <x-button icon="s-pencil-square" wire:click="edit({{ $withdraw['id'] }})"
                        spinner="edit({{ $withdraw['id'] }})" class="btn-neutral btn-action" />
                </div>
            @endscope
        </x-table>
    </x-card>
    <x-modal wire:model="createModal" title="Add New Withdraw Request" separator>
        <x-form wire:submit="storeWithdraw">
            <x-choices label="Withdraw Method" :options="$methods" single wire:model="withdraw_method_id"
                placeholder="Select Method" required />
            <x-input type="number" label="Withdraw Amount" wire:model="amount" placeholder="Withdraw Amount"
                required />
            <x-input label="Withdraw Method Description" wire:model="description"
                placeholder="Withdraw Method Description" required />
            <x-slot:actions>
                <x-button label="Cancel" @click="$wire.createModal = false" class="btn-sm" />
                <x-button type="submit" label="Add Request" class="btn-primary btn-sm" spinner="storeWithdraw" />
            </x-slot>
        </x-form>
    </x-modal>
    <x-modal wire:model="editModal" title="Update Withdraw Request" separator>
        <x-form wire:submit="updateWithdraw">
            <x-choices label="Withdraw Method" :options="$methods" single wire:model="withdraw_method_id"
                placeholder="Select Method" required />
            <x-input type="number" label="Withdraw Amount" wire:model="amount" placeholder="Withdraw Amount"
                required />
            <x-input label="Withdraw Method Description" wire:model="description"
                placeholder="Withdraw Method Description" required />
            <x-slot:actions>
                <x-button label="Cancel" @click="$wire.editModal = false" class="btn-sm" />
                <x-button type="submit" label="Add Request" class="btn-primary btn-sm" spinner="updateWithdraw" />
            </x-slot>
        </x-form>
    </x-modal>
</div>
