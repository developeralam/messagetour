<?php

use Mary\Traits\Toast;
use App\Models\Expense;
use App\Models\Customer;
use Livewire\Volt\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Rule;
use App\Models\ChartOfAccount;
use Livewire\Attributes\Title;
use App\Enum\TransactionStatus;
use Livewire\Attributes\Layout;
use App\Services\TransactionService;

new #[Layout('components.layouts.admin')] #[Title('Expense List')] class extends Component {
    use Toast, WithPagination;
    public array $headers;
    public $expense_for_search;
    public $account_for_search;
    public $date_for_search;
    public string $search = '';
    public $expenseHeads = [];
    public $accounts = [];
    public array $sortBy = ['column' => 'id', 'direction' => 'desc'];
    public Expense $expense;

    #[Rule('required')]
    public $expenses_head_id;

    #[Rule('required')]
    public $account_id;

    #[Rule('required')]
    public $amount;

    #[Rule('nullable')]
    public $expense_date;

    #[Rule('nullable')]
    public $remarks;

    public bool $createModal = false;
    public bool $editModal = false;

    public function mount(): void
    {
        $this->headers = $this->headers();
        $this->expenseHeads = ChartOfAccount::with('parent')
            ->where('type', 'expense') // Filter by 'expense'
            ->whereNotNull('parent_id')
            ->get();
        $this->accounts = ChartOfAccount::with('parent')
            ->where('type', 'asset') // Filter by 'asset' type
            ->whereHas('parent', function ($query) {
                // Filter by parent category (Cash or Bank)
                $query->whereIn('name', ['Cash', 'Bank']);
            })
            ->get();
    }

    public function delete(Expense $expense): void
    {
        try {
            $expense->update([
                'action_id' => auth()->user()->id,
            ]);
            $expense->delete();
            $this->success('Expense Deleted successfully');
        } catch (\Throwable $th) {
            $this->error($th->getMessage());
        }
    }

    public function headers(): array
    {
        return [['key' => 'id', 'label' => '#'], ['key' => 'expense_head', 'label' => 'Expense Head'], ['key' => 'account', 'label' => 'Account'], ['key' => 'amount', 'label' => 'Amount'], ['key' => 'expense_date', 'label' => 'Expense Date'], ['key' => 'remarks', 'label' => 'Remarks'], ['key' => 'created_at', 'label' => 'Created At'], ['key' => 'status', 'label' => 'Status'], ['key' => 'action_by', 'label' => 'Last Action By']];
    }

    public function expenses()
    {
        return Expense::query()
            ->with(['actionBy', 'account', 'expenseHead'])
            ->when($this->expense_for_search, function ($query) {
                $query->where('expenses_head_id', $this->expense_for_search);
            })
            ->when($this->account_for_search, function ($query) {
                $query->where('account_id', $this->account_for_search);
            })
            ->when($this->date_for_search, function ($query) {
                $query->whereDate('created_at', $this->date_for_search);
            })
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('amount', 'like', '%' . $this->search . '%')->orWhere('remarks', 'like', '%' . $this->search . '%');
                });
            })
            ->orderBy(...array_values($this->sortBy))
            ->paginate(10);
    }

    public function storeExpense()
    {
        $this->validate();
        try {
            $expense = Expense::create([
                'expenses_head_id' => $this->expenses_head_id,
                'account_id' => $this->account_id,
                'amount' => $this->amount,
                'expense_date' => $this->expense_date,
                'remarks' => $this->remarks,
                'status' => TransactionStatus::PENDING,
                'created_by' => auth()->user()->id,
            ]);
            // TransactionService::recordTransaction([
            //     'source_type' => Expense::class,
            //     'source_id' => $expense->id,
            //     'date' => now(),
            //     'amount' => $this->amount,
            //     'debit_account_id' => $this->expenses_head_id,
            //     'credit_account_id' => $this->account_id,
            //     'description' => 'Expense Transaction Information Record',
            // ]);
            $this->createModal = false;
            $this->success('Expense Added Successfully');
        } catch (\Throwable $th) {
            $this->createModal = false;
            $this->error($th->getMessage());
        }
    }
    public function edit(Expense $expense)
    {
        $this->expense = $expense;
        $this->expenses_head_id = $expense->expenses_head_id;
        $this->account_id = $expense->account_id;
        $this->amount = $expense->amount;
        $this->expense_date = $expense->expense_date ? $expense->expense_date->format('Y-m-d') : null;
        $this->remarks = $expense->remarks ?? null;
        $this->editModal = true;
    }
    public function updateExpense()
    {
        $this->validate();
        try {
            $this->expense->update([
                'expenses_head_id' => $this->expenses_head_id,
                'account_id' => $this->account_id,
                'amount' => $this->amount,
                'expense_date' => $this->expense_date,
                'remarks' => $this->remarks ?? null,
                'action_by' => auth()->user()->id,
            ]);
            $this->success('Expense Updated Successfully');
            $this->editModal = false;
        } catch (\Throwable $th) {
            $this->error($th->getMessage());
        }
    }

    public function with(): array
    {
        return [
            'expenses' => $this->expenses(),
        ];
    }
}; ?>

<div>
    <x-header title="Expense List" size="text-xl" separator class="bg-white px-2 pt-2">
        <x-slot:actions>
            <x-input placeholder="Search..." wire:model.live="search" icon="o-bolt" inline />
            <x-select placeholder="Select Expense" wire:model.live="expense_for_search" :options="$expenseHeads" option-label="name" option-value="id" />
            <x-select placeholder="Select Account" wire:model.live="account_for_search" :options="$accounts" />
            <x-datetime wire:model.live="date_for_search" />
            <x-button label="Add Expense" @click="$wire.createModal = true" icon="o-plus" class="btn-primary btn-sm" />
        </x-slot:actions>
    </x-header>

    <x-card>
        <x-table :headers="$headers" :rows="$expenses" :sort-by="$sortBy" with-pagination>
            @scope('cell_id', $expense, $expenses)
                {{ $loop->iteration + ($expenses->currentPage() - 1) * $expenses->perPage() }}
            @endscope
            @scope('cell_expense_head', $expense)
                {{ $expense->expenseHead->name ?? 'N/A' }}
            @endscope
            @scope('cell_account', $expense)
                {{ $expense->account->name ?? 'N/A' }}
            @endscope
            @scope('cell_status', $expense)
                {{ $expense->status->label() ?? 'N/A' }}
            @endscope
            @scope('cell_action_by', $expense)
                {{ $expense->actionBy->name ?? 'N/A' }}
            @endscope
            @scope('cell_expense_date', $expense)
                {{ $expense->expense_date ? $expense->expense_date->format('d M, Y') : 'N/A' }}
            @endscope
            @scope('cell_remarks', $expense)
                {{ $expense->remarks ?? 'N/A' }}
            @endscope
            @scope('cell_created_at', $expense)
                {{ $expense->created_at->format('d M, Y') }}
            @endscope
            @scope('cell_amount', $expense)
                BDT {{ number_format($expense->amount) }}
            @endscope
            @scope('actions', $expense)
                <div class="flex items-center gap-1">
                    <x-button icon="o-trash" wire:click="delete({{ $expense['id'] }})" wire:confirm="Are you sure?" class="btn-error btn-action"
                        spinner="delete({{ $expense['id'] }})" />

                    <x-button icon="s-pencil-square" class="btn-neutral btn-action" wire:click="edit({{ $expense['id'] }})" />
                </div>
            @endscope
        </x-table>

    </x-card>

    <x-modal wire:model="createModal" title="Add New Expense" separator>
        <x-form wire:submit="storeExpense">
            <x-choices label="Expense Head" wire:model="expenses_head_id" placeholder="Select Expense" single required option-label="name"
                option-value="id" :options="$expenseHeads" />
            <x-choices label="Accounts" wire:model="account_id" placeholder="Select Account" single required option-label="name" option-value="id"
                :options="$accounts" />
            <x-input type="number" label="Amount" wire:model="amount" placeholder="Amount" required />
            <x-input label="Remarks" wire:model="remarks" placeholder="Remarks" />
            <x-slot:actions>
                <x-button label="Cancel" @click="$wire.createModal = false" class="btn-sm" />
                <x-button type="submit" label="Add Expense" class="btn-primary btn-sm" spinner="storeExpense" />
            </x-slot:actions>
        </x-form>
    </x-modal>

    <x-modal wire:model="editModal" title="Update Expense" separator>
        <x-form wire:submit="updateExpense">
            <x-choices label="Expense Head" wire:model="expenses_head_id" placeholder="Select Expense" single required option-label="name"
                option-value="id" :options="$expenseHeads" />
            <x-choices label="Accounts" wire:model="account_id" placeholder="Select Account" single required option-label="name" option-value="id"
                :options="$accounts" />
            <x-input type="number" label="Amount" wire:model="amount" placeholder="Amount" required />
            <x-input label="Remarks" wire:model="remarks" placeholder="Remarks" />
            <x-slot:actions>
                <x-button label="Cancel" @click="$wire.editModal = false" class="btn-sm" />
                <x-button type="submit" label="Save" class="btn-primary btn-sm" spinner="updateExpense" />
            </x-slot:actions>
        </x-form>
    </x-modal>
</div>
