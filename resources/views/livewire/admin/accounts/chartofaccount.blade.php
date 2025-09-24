<?php

use Mary\Traits\Toast;
use App\Enum\AccountType;
use Livewire\Volt\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Rule;
use Livewire\WithFileUploads;
use App\Models\ChartOfAccount;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Computed;
use App\Services\TransactionService;

new #[Layout('components.layouts.admin')] #[Title('Chart Of Accounts')] class extends Component {
    use Toast, WithFileUploads, WithPagination;

    public string $search = '';

    public $category_id;

    public ChartOfAccount $account;

    public $types = [];

    public $categories = [];

    public $header = [];

    #[Rule('required')]
    public $name;

    #[Rule('required')]
    public $parent_id;

    #[Rule('required')]
    public $type;

    #[Rule('required')]
    public $opening_balance;

    public $from_date;

    public $to_date;

    public bool $createModal = false;

    public bool $editModal = false;

    public bool $isPrintShow = false;

    public function mount()
    {
        $this->types = AccountType::getTypes();
        $this->getCategories();
        $this->header = $this->headers();
    }

    public function updated($property)
    {
        if ($property == 'type') {
            $this->getCategories();
        }
    }

    public function storeChartOfAccount()
    {
        $this->validate();
        try {
            $account = ChartOfAccount::create([
                'name' => $this->name,
                'parent_id' => $this->parent_id,
                'type' => $this->type,
                'opening_balance' => $this->opening_balance,
            ]);

            // Record opening balance transaction with proper debit/credit logic
            $this->recordOpeningBalanceTransaction($account, $this->opening_balance);

            $this->reset(['name', 'type', 'parent_id', 'opening_balance']);
            $this->success('Chart Of Account Added Successfully');
            $this->createModal = false;
        } catch (\Throwable $th) {
            $this->createModal = false;
            $this->error(env('APP_DEBUG', false) ? $th->getMessage() : 'Something went wrong');
        }
    }

    public function edit(ChartOfAccount $account)
    {
        $this->reset(['name', 'type', 'parent_id', 'opening_balance']);
        $this->account = $account;
        $this->name = $account->name;
        $this->parent_id = $account->parent_id;
        $this->type = $account->type;
        $this->opening_balance = $account->opening_balance;
        $this->editModal = true;
    }

    public function updateChartOfAccount()
    {
        $this->validate();
        try {
            $oldOpeningBalance = $this->account->opening_balance;

            $this->account->update([
                'name' => $this->name,
                'parent_id' => $this->parent_id,
                'type' => $this->type,
                'opening_balance' => $this->opening_balance,
            ]);

            // If opening balance changed, record the adjustment transaction
            if ($oldOpeningBalance != $this->opening_balance) {
                $difference = $this->opening_balance - $oldOpeningBalance;
                $this->recordOpeningBalanceTransaction($this->account, $difference, 'Opening Balance Adjustment');
            }

            $this->reset(['name', 'type', 'parent_id', 'opening_balance']);
            $this->success('Chart Of Account Updated Successfully');
            $this->editModal = false;
        } catch (\Throwable $th) {
            $this->error(env('APP_DEBUG', false) ? $th->getMessage() : 'Something went wrong');
        }
    }

    public function delete(ChartOfAccount $account)
    {
        try {
            $account->delete();
            $this->success('Chart Of Account Deleted Successfully');
        } catch (\Throwable $th) {
            $this->error(env('APP_DEBUG', false) ? $th->getMessage() : 'Something went wrong');
        }
    }

    /**
     * Record opening balance transaction with proper debit/credit logic
     * Following accounting equation: Assets = Liabilities + Equity
     */
    private function recordOpeningBalanceTransaction(ChartOfAccount $account, $amount, $description = 'Opening Balance')
    {
        // Get or create the equity account for opening balance contra-entry
        $equityAccount = ChartOfAccount::where('type', 'equity')->whereNull('parent_id')->first();

        if (!$equityAccount) {
            // Create a default equity account if it doesn't exist
            ChartOfAccount::$skipCodeGeneration = true;
            $equityAccount = ChartOfAccount::create([
                'code' => 300,
                'name' => 'Owner\'s Equity',
                'type' => 'equity',
                'opening_balance' => 0,
                'current_balance' => 0,
            ]);
            ChartOfAccount::$skipCodeGeneration = false;
        }

        // Determine debit/credit based on account type and amount
        if (in_array($account->type, ['asset', 'expense'])) {
            // Assets and Expenses are DEBIT accounts
            // Positive amount = DEBIT the account, CREDIT equity
            // Negative amount = CREDIT the account, DEBIT equity
            if ($amount >= 0) {
                $debitAccountId = $account->id;
                $creditAccountId = $equityAccount->id;
            } else {
                $debitAccountId = $equityAccount->id;
                $creditAccountId = $account->id;
                $amount = abs($amount); // Make amount positive for transaction
            }
        } else {
            // Liabilities, Equity, Revenue are CREDIT accounts
            // Positive amount = CREDIT the account, DEBIT equity
            // Negative amount = DEBIT the account, CREDIT equity
            if ($amount >= 0) {
                $debitAccountId = $equityAccount->id;
                $creditAccountId = $account->id;
            } else {
                $debitAccountId = $account->id;
                $creditAccountId = $equityAccount->id;
                $amount = abs($amount); // Make amount positive for transaction
            }
        }

        // Record the transaction using the existing TransactionService
        TransactionService::recordTransaction([
            'source_type' => ChartOfAccount::class,
            'source_id' => $account->id,
            'amount' => $amount,
            'debit_account_id' => $debitAccountId,
            'credit_account_id' => $creditAccountId,
            'date' => now(),
            'description' => $description,
        ]);
    }

    public function headers(): array
    {
        return [['key' => 'id', 'label' => '#'], ['key' => 'code', 'label' => 'Account Code', 'sortable' => false], ['key' => 'parent.name', 'label' => 'Account Category', 'sortable' => false], ['key' => 'name', 'label' => 'Account Name'], ['key' => 'type', 'label' => 'Account Type'], ['key' => 'opening_balance', 'label' => 'Opening Balance', 'sortable' => false], ['key' => 'current_balance', 'label' => 'Current Balance', 'sortable' => false]];
    }

    public function accounts()
    {
        return ChartOfAccount::query()->whereNotNull('parent_id')->paginate(20);
    }

    #[Computed]
    public function getCategories()
    {
        // List of codes to exclude from the query
        $excludeCodes = ['103', '104', '105', '106', '201', '202', '203', '205', '301', '401', '501', '502', '503', '504'];

        // Optimized query with direct model usage
        $this->categories = ChartOfAccount::whereNull('parent_id') // Filter out accounts with parent_id
            ->when($this->type, fn($q) => $q->where('type', $this->type))
            ->whereNotIn('code', $excludeCodes) // Filter out specific account codes
            ->get(); // Paginate the results, fetching 20 per page
    }

    public function print($category_id)
    {
        $this->category_id = $category_id;
        $this->isPrintShow = true;
    }

    public function redirectToPrint()
    {
        return redirect()->route('admin.ledger-print-report', [
            'category_id' => $this->category_id,
            'from_date' => $this->from_date,
            'to_date' => $this->to_date,
        ]);
    }

    public function with(): array
    {
        return [
            'accounts' => $this->accounts(),
        ];
    }
}; ?>


<div>
    <x-header title="Chart Of Account List" size="text-xl" separator class="bg-white px-2 pt-3">
        <x-slot:actions>
            <x-button icon="fas.plus" @click="$wire.createModal = true" label="Add Chart Of Account" class="btn-primary btn-sm" />
        </x-slot>
    </x-header>
    <x-card>
        <x-table :headers="$header" :rows="$accounts" with-pagination>
            @scope('cell_id', $account, $accounts)
                {{ $loop->iteration + ($accounts->currentPage() - 1) * $accounts->perPage() }}
            @endscope

            @scope('cell_type', $account)
                <x-badge value="{{ $account->type }}" class="badge-primary text-white py-1 text-xs" />
            @endscope

            @scope('actions', $account)
                <div class="flex items-center gap-1">
                    <x-button icon="fas.print" wire:click="print({{ $account['id'] }})" class="btn-primary btn-action text-white" />
                    <x-button icon="o-trash" wire:click="delete({{ $account['id'] }})" wire:confirm="Are you sure?"
                        class="btn-error btn-action text-white" />
                    <x-button icon="s-pencil-square" wire:click="edit({{ $account['id'] }})" class="btn-neutral btn-action text-white" />
                </div>
            @endscope
        </x-table>
    </x-card>
    <x-modal wire:model="createModal" title="Add New Chart Of Account" separator>
        <x-form wire:submit="storeChartOfAccount">
            <x-choices label="Type" wire:model.live="type" :options="$types" single required placeholder="Select Type" />
            <x-choices label="Category" wire:model="parent_id" :options="$categories" single required placeholder="Select Category" />
            <x-input label="Account Name" wire:model="name" placeholder="Category Name" required />
            <x-input label="Opening Balance" wire:model="opening_balance" placeholder="Opening Balance" required />
            <x-slot:actions>
                <x-button label="Cancel" class="btn-sm" @click="$wire.createModal = false" />
                <x-button type="submit" label="Add Account" class="btn-sm btn-primary" spinner="storeChartOfAccount" />
            </x-slot>
        </x-form>
    </x-modal>
    <x-modal wire:model="editModal" title="Update {{ $account->name ?? '' }} Account" separator>
        <x-form wire:submit="updateChartOfAccount">
            <x-choices label="Type" wire:model.live="type" :options="$types" single required placeholder="Select Type" />
            <x-choices label="Category" wire:model="parent_id" :options="$categories" single required placeholder="Select Category" />
            <x-input label="Account Name" wire:model="name" placeholder="Category Name" required />
            <x-input label="Opening Balance" wire:model="opening_balance" placeholder="Opening Balance" required />
            <x-slot:actions>
                <x-button label="Cancel" class="btn-sm" @click="$wire.editModal = false" />
                <x-button type="submit" label="Add Account" class="btn-sm btn-primary" spinner="updateChartOfAccount" />
            </x-slot>
        </x-form>
    </x-modal>
    <x-modal wire:model="isPrintShow" title="Print Report" separator>
        <x-form wire:submit="redirectToPrint">
            <x-datetime label="From Date" wire:model="from_date" required />
            <x-datetime label="To Date" wire:model="to_date" required />
            <x-slot:actions>
                <x-button label="Cancel" class="btn-sm" @click="$wire.isPrintShow = false" />
                <x-button type="submit" label="Print" class="btn-sm btn-primary" spinner="redirectToPrint" />
            </x-slot>
        </x-form>
    </x-modal>
</div>
