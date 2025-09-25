<?php

use Mary\Traits\Toast;
use App\Models\Expense;
use App\Models\Income;
use Livewire\Volt\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Title;
use App\Enum\TransactionStatus;
use Livewire\Attributes\Layout;
use App\Services\TransactionService;
use App\Models\ChartOfAccount;
use Illuminate\Support\Facades\DB;

new #[Layout('components.layouts.admin')] #[Title('Income Expense Approvals')] class extends Component {
    use Toast, WithPagination;

    public array $headers;
    public string $search = '';
    public array $sortBy = ['column' => 'id', 'direction' => 'desc'];
    public $selectedTab = 'income'; // 'income' or 'expense'

    public function mount(): void
    {
        $this->headers = $this->headers();
    }

    public function updatedSelectedTab(): void
    {
        $this->headers = $this->headers();
    }

    public function headers(): array
    {
        if ($this->selectedTab === 'income') {
            return [['key' => 'id', 'label' => '#'], ['key' => 'amount', 'label' => 'Amount'], ['key' => 'customer_agent', 'label' => 'Customer/Agent'], ['key' => 'account', 'label' => 'Account'], ['key' => 'reference', 'label' => 'Reference'], ['key' => 'remarks', 'label' => 'Remarks'], ['key' => 'payment_slip', 'label' => 'Payment Slip'], ['key' => 'created_by', 'label' => 'Created By'], ['key' => 'created_at', 'label' => 'Created At'], ['key' => 'actions', 'label' => 'Actions']];
        } else {
            return [['key' => 'id', 'label' => '#'], ['key' => 'amount', 'label' => 'Amount'], ['key' => 'expense_head', 'label' => 'Expense Head'], ['key' => 'account', 'label' => 'Account'], ['key' => 'remarks', 'label' => 'Remarks'], ['key' => 'created_by', 'label' => 'Created By'], ['key' => 'created_at', 'label' => 'Created At'], ['key' => 'actions', 'label' => 'Actions']];
        }
    }

    public function pendingIncomes()
    {
        return Income::query()
            ->with(['customer.user', 'agent.user', 'account', 'createdBy'])
            ->where('status', TransactionStatus::PENDING)
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('amount', 'like', '%' . $this->search . '%')
                        ->orWhere('remarks', 'like', '%' . $this->search . '%')
                        ->orWhere('reference', 'like', '%' . $this->search . '%');
                });
            })
            ->orderBy(...array_values($this->sortBy))
            ->paginate(10);
    }

    public function pendingExpenses()
    {
        return Expense::query()
            ->with(['expenseHead', 'account', 'createdBy'])
            ->where('status', TransactionStatus::PENDING)
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('amount', 'like', '%' . $this->search . '%')->orWhere('remarks', 'like', '%' . $this->search . '%');
                });
            })
            ->orderBy(...array_values($this->sortBy))
            ->paginate(10);
    }

    public function approveIncome(Income $income)
    {
        try {
            DB::transaction(function () use ($income) {
                // Update income status
                $income->update([
                    'status' => TransactionStatus::APPROVED,
                    'action_by' => auth()->user()->id,
                ]);

                // Create transaction
                TransactionService::recordTransaction([
                    'source_type' => Income::class,
                    'source_id' => $income->id,
                    'date' => now(),
                    'amount' => $income->amount,
                    'debit_account_id' => $income->account_id,
                    'credit_account_id' => ChartOfAccount::where('name', 'Revenue Income')->first()->id,
                    'description' => 'Income Transaction - ' . ($income->reference ?? 'No Reference'),
                ]);
            });

            $this->success('Income approved successfully');
        } catch (\Throwable $th) {
            $this->error($th->getMessage());
        }
    }

    public function rejectIncome(Income $income)
    {
        try {
            $income->update([
                'status' => TransactionStatus::REJECTED,
                'action_by' => auth()->user()->id,
            ]);
            $this->success('Income rejected successfully');
        } catch (\Throwable $th) {
            $this->error($th->getMessage());
        }
    }

    public function approveExpense(Expense $expense)
    {
        try {
            DB::transaction(function () use ($expense) {
                // Update expense status
                $expense->update([
                    'status' => TransactionStatus::APPROVED,
                    'action_by' => auth()->user()->id,
                ]);

                // Create transaction
                TransactionService::recordTransaction([
                    'source_type' => Expense::class,
                    'source_id' => $expense->id,
                    'date' => now(),
                    'amount' => $expense->amount,
                    'debit_account_id' => $expense->expenses_head_id,
                    'credit_account_id' => $expense->account_id,
                    'description' => 'Expense Transaction - ' . ($expense->remarks ?? 'No Remarks'),
                ]);
            });

            $this->success('Expense approved successfully');
        } catch (\Throwable $th) {
            $this->error($th->getMessage());
        }
    }

    public function rejectExpense(Expense $expense)
    {
        try {
            $expense->update([
                'status' => TransactionStatus::REJECTED,
                'action_by' => auth()->user()->id,
            ]);
            $this->success('Expense rejected successfully');
        } catch (\Throwable $th) {
            $this->error($th->getMessage());
        }
    }

    public function with(): array
    {
        return [
            'pendingIncomes' => $this->pendingIncomes(),
            'pendingExpenses' => $this->pendingExpenses(),
        ];
    }
}; ?>

<div>
    <x-header title="Income & Expense Approvals" size="text-xl" separator class="bg-white px-2 pt-2">
        <x-slot:actions>
            <x-input placeholder="Search..." wire:model.live="search" icon="o-bolt" inline />
        </x-slot:actions>
    </x-header>

    <x-card>
        <!-- Tab Navigation -->
        <div class="tabs tabs-boxed mb-4">
            <button class="tab {{ $selectedTab === 'income' ? 'tab-active' : '' }}" wire:click="$set('selectedTab', 'income')">
                Pending Incomes ({{ $pendingIncomes->total() }})
            </button>
            <button class="tab {{ $selectedTab === 'expense' ? 'tab-active' : '' }}" wire:click="$set('selectedTab', 'expense')">
                Pending Expenses ({{ $pendingExpenses->total() }})
            </button>
        </div>

        @if ($selectedTab === 'income')
            <x-table :headers="$headers" :rows="$pendingIncomes" :sort-by="$sortBy" with-pagination>
                @scope('cell_id', $income, $pendingIncomes)
                    {{ $loop->iteration + ($pendingIncomes->currentPage() - 1) * $pendingIncomes->perPage() }}
                @endscope
                @scope('cell_amount', $income)
                    <span class="font-semibold text-green-600">BDT {{ number_format($income->amount) }}</span>
                @endscope
                @scope('cell_customer_agent', $income)
                    @if ($income->customer)
                        <div>
                            <div class="font-semibold">{{ $income->customer->user->name ?? 'N/A' }}</div>
                            <div class="text-xs text-gray-500">Customer</div>
                        </div>
                    @elseif($income->agent)
                        <div>
                            <div class="font-semibold">{{ $income->agent->user->name ?? 'N/A' }}</div>
                            <div class="text-xs text-gray-500">Agent</div>
                        </div>
                    @else
                        <span class="text-gray-500">N/A</span>
                    @endif
                @endscope
                @scope('cell_account', $income)
                    {{ $income->account->name ?? 'N/A' }}
                @endscope
                @scope('cell_reference', $income)
                    {{ $income->reference ?? 'N/A' }}
                @endscope
                @scope('cell_remarks', $income)
                    {{ $income->remarks ?? 'N/A' }}
                @endscope
                @scope('cell_payment_slip', $income)
                    @if ($income->payment_slip)
                        <div class="flex items-center space-x-2">
                            <img src="{{ asset('storage/' . $income->payment_slip) }}" alt="Payment Slip"
                                class="w-12 h-12 object-cover rounded-lg border border-gray-300 cursor-pointer hover:scale-110 transition-transform"
                                onclick="window.open('{{ asset('storage/' . $income->payment_slip) }}', '_blank')">
                            <button onclick="window.open('{{ asset('storage/' . $income->payment_slip) }}', '_blank')"
                                class="text-blue-600 hover:text-blue-800 text-sm">
                                View
                            </button>
                        </div>
                    @else
                        <span class="text-gray-500">N/A</span>
                    @endif
                @endscope
                @scope('cell_created_by', $income)
                    {{ $income->createdBy->name ?? 'N/A' }}
                @endscope
                @scope('cell_created_at', $income)
                    {{ $income->created_at->format('d M, Y H:i') }}
                @endscope
                @scope('actions', $income)
                    <div class="flex items-center gap-2">
                        <x-button icon="o-check" wire:click="approveIncome({{ $income->id }})"
                            wire:confirm="Are you sure you want to approve this income?" class="btn-success btn-sm"
                            spinner="approveIncome({{ $income->id }})" />
                        <x-button icon="o-x-mark" wire:click="rejectIncome({{ $income->id }})"
                            wire:confirm="Are you sure you want to reject this income?" class="btn-error btn-sm"
                            spinner="rejectIncome({{ $income->id }})" />
                    </div>
                @endscope
            </x-table>
        @else
            <x-table :headers="$headers" :rows="$pendingExpenses" :sort-by="$sortBy" with-pagination>
                @scope('cell_id', $expense, $pendingExpenses)
                    {{ $loop->iteration + ($pendingExpenses->currentPage() - 1) * $pendingExpenses->perPage() }}
                @endscope
                @scope('cell_amount', $expense)
                    <span class="font-semibold text-red-600">BDT {{ number_format($expense->amount) }}</span>
                @endscope
                @scope('cell_expense_head', $expense)
                    <div>
                        <div class="font-semibold">{{ $expense->expenseHead->name ?? 'N/A' }}</div>
                        <div class="text-xs text-gray-500">Expense Head</div>
                    </div>
                @endscope
                @scope('cell_account', $expense)
                    {{ $expense->account->name ?? 'N/A' }}
                @endscope
                @scope('cell_remarks', $expense)
                    {{ $expense->remarks ?? 'N/A' }}
                @endscope
                @scope('cell_created_by', $expense)
                    {{ $expense->createdBy->name ?? 'N/A' }}
                @endscope
                @scope('cell_created_at', $expense)
                    {{ $expense->created_at->format('d M, Y H:i') }}
                @endscope
                @scope('actions', $expense)
                    <div class="flex items-center gap-2">
                        <x-button icon="o-check" wire:click="approveExpense({{ $expense->id }})"
                            wire:confirm="Are you sure you want to approve this expense?" class="btn-success btn-sm"
                            spinner="approveExpense({{ $expense->id }})" />
                        <x-button icon="o-x-mark" wire:click="rejectExpense({{ $expense->id }})"
                            wire:confirm="Are you sure you want to reject this expense?" class="btn-error btn-sm"
                            spinner="rejectExpense({{ $expense->id }})" />
                    </div>
                @endscope
            </x-table>
        @endif
    </x-card>
</div>
