<?php

use App\Models\Income;
use App\Models\Customer;
use Livewire\Volt\Component;
use Livewire\Attributes\Rule;
use App\Models\ChartOfAccount;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;
use Livewire\WithPagination;
use Mary\Traits\Toast;

new #[Layout('components.layouts.admin')] #[Title('Income List')] class extends Component {
    use Toast, WithPagination;
    public array $headers;
    public string $search = '';
    public $customers = [];
    public $accounts = [];
    public array $sortBy = ['column' => 'id', 'direction' => 'desc'];
    public Income $income;

    #[Rule('required')]
    public $customer_id;

    #[Rule('required')]
    public $account_id;

    #[Rule('required')]
    public $amount;

    #[Rule('nullable')]
    public $reference;

    #[Rule('nullable')]
    public $remarks;

    public bool $createModal = false;
    public bool $editModal = false;

    public function mount(): void
    {
        $this->headers = $this->headers();
        $this->customers = Customer::with('user')->get();
        $this->accounts = ChartOfAccount::with('parent')
            ->where('type', 'asset') // Filter by 'asset' type
            ->whereHas('parent', function ($query) {
                // Filter by parent category (Cash or Bank)
                $query->whereIn('name', ['Cash', 'Bank']);
            })
            ->get();
    }

    public function delete(Income $income): void
    {
        try {
            $income->update([
                'action_id' => auth()->user()->id,
            ]);
            $income->delete();
            $this->success('Income Deleted successfully');
        } catch (\Throwable $th) {
            $this->error($th->getMessage());
        }
    }

    public function headers(): array
    {
        return [['key' => 'id', 'label' => '#'], ['key' => 'customer', 'label' => 'Customer'], ['key' => 'account', 'label' => 'Account'], ['key' => 'amount', 'label' => 'Amount'], ['key' => 'remarks', 'label' => 'Remarks'], ['key' => 'action_by', 'label' => 'Last Action By']];
    }

    public function incomes()
    {
        return Income::query()
            ->with(['actionBy', 'account', 'customer'])
            ->orderBy(...array_values($this->sortBy))
            ->paginate(10);
    }

    public function storeIncome()
    {
        $this->validate();
        try {
            Income::create([
                'customer_id' => $this->customer_id,
                'account_id' => $this->account_id,
                'amount' => $this->amount,
                'remarks' => $this->remarks,
            ]);
            $this->createModal = false;
            $this->success('Income Added Successfully');
        } catch (\Throwable $th) {
            $this->createModal = false;
            $this->error($th->getMessage());
        }
    }
    public function edit(Income $income)
    {
        $this->income = $income;
        $this->customer_id = $income->customer_id;
        $this->account_id = $income->account_id;
        $this->amount = $income->amount;
        $this->reference = $income->reference ?? '';
        $this->remarks = $income->remarks ?? '';
        $this->editModal = true;
    }
    public function udpateIncome()
    {
        $this->validate();
        try {
            $this->income->update([
                'customer_id' => $this->customer_id,
                'account_id' => $this->account_id,
                'amount' => $this->amount,
                'reference' => $this->reference,
                'remarks' => $this->remarks,
            ]);
            $this->success('Income Updated Successfully');
            $this->editModal = false;
        } catch (\Throwable $th) {
            $this->error($th->getMessage());
        }
    }

    public function with(): array
    {
        return [
            'incomes' => $this->incomes(),
        ];
    }
}; ?>

<div>
    <x-header title="Income List" size="text-xl" separator class="bg-white px-2 pt-2">
        <x-slot:actions>
            <x-button label="Add Income" @click="$wire.createModal = true" icon="o-plus" class="btn-primary btn-sm" />
        </x-slot:actions>
    </x-header>

    <x-card>
        <x-table :headers="$headers" :rows="$incomes" :sort-by="$sortBy" with-pagination>
            @scope('cell_id', $income, $incomes)
                {{ $loop->iteration + ($incomes->currentPage() - 1) * $incomes->perPage() }}
            @endscope
            @scope('cell_customer', $income)
                {{ $income->customer->name ?? 'N/A' }}
            @endscope
            @scope('cell_account', $income)
                {{ $income->account->name ?? 'N/A' }}
            @endscope
            @scope('cell_action_by', $income)
                {{ $income->actionBy->name ?? 'N/A' }}
            @endscope
            @scope('cell_remarks', $income)
                {{ $income->remarks ?? 'N/A' }}
            @endscope
            @scope('cell_reference', $income)
                {{ $income->reference ?? 'N/A' }}
            @endscope
            @scope('actions', $income)
                <div class="flex items-center gap-1">
                    <x-button icon="o-trash" wire:click="delete({{ $income['id'] }})" wire:confirm="Are you sure?"
                        class="btn-error btn-action" spinner="delete({{ $income['id'] }})" />

                    <x-button icon="s-pencil-square" class="btn-neutral btn-action"
                        wire:click="edit({{ $income['id'] }})" />
                </div>
            @endscope
        </x-table>

    </x-card>

    <x-modal wire:model="createModal" title="Add New Income" separator>
        <x-form wire:submit="storeIncome">
            <x-choices label="Customers" wire:model="customer_id" placeholder="Select Customer" single required
                option-label="user.name" option-value="id" :options="$customers" />
            <x-choices label="Accounts" wire:model="account_id" placeholder="Select Account" single required
                option-label="name" option-value="id" :options="$accounts" />
            <x-input type="number" label="Amount" wire:model="amount" placeholder="Amount" required />
            <x-input label="Remarks" wire:model="remarks" placeholder="Remarks" />
            <x-slot:actions>
                <x-button label="Cancel" @click="$wire.createModal = false" class="btn-sm" />
                <x-button type="submit" label="Add Income" class="btn-primary btn-sm" spinner="storeIncome" />
            </x-slot:actions>
        </x-form>
    </x-modal>

    <x-modal wire:model="editModal" title="Update Income" separator>
        <x-form wire:submit="udpateIncome">
            <x-choices label="Customers" wire:model="customer_id" placeholder="Select Customer" single required
                option-label="user.name" option-value="id" :options="$customers" />
            <x-choices label="Accounts" wire:model="account_id" placeholder="Select Account" single required
                option-label="name" option-value="id" :options="$accounts" />
            <x-input type="number" label="Amount" wire:model="amount" placeholder="Amount" required />
            <x-input label="Reference" wire:model="reference" placeholder="Reference" />
            <x-input label="Remarks" wire:model="remarks" placeholder="Remarks" />
            <x-slot:actions>
                <x-button label="Cancel" @click="$wire.editModal = false" class="btn-sm" />
                <x-button type="submit" label="Save" class="btn-primary btn-sm" spinner="udpateIncome" />
            </x-slot:actions>
        </x-form>
    </x-modal>
</div>
