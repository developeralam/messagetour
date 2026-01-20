<?php

use Carbon\Carbon;
use App\Models\Agent;
use App\Models\Income;
use Mary\Traits\Toast;
use App\Models\Customer;
use Livewire\Volt\Component;
use Livewire\Attributes\Rule;
use App\Models\ChartOfAccount;
use Livewire\Attributes\Title;
use App\Enum\TransactionStatus;
use App\Enum\AccountPaymentType;
use Livewire\Attributes\Layout;
use App\Models\IncomeBreakdown;
use App\Services\TransactionService;
use Illuminate\Support\Facades\DB;

new #[Layout('components.layouts.admin')] #[Title('Create Income')] class extends Component {
    use Toast;

    #[Rule('nullable')]
    public $customer_id;

    #[Rule('nullable')]
    public $agent_id;

    #[Rule('required')]
    public $account_id;

    #[Rule('required|numeric|min:0.01')]
    public $amount;

    #[Rule('nullable')]
    public $income_date;

    #[Rule('nullable')]
    public $reference;

    #[Rule('nullable')]
    public $remarks;

    #[Rule('nullable')]
    public $payment_status;

    /**
     * Amount breakdown rows.
     * Each item: ['title' => string|null, 'amount' => float|int|string|null]
     */
    public array $breakdowns = [];

    public $customers = [];
    public $agents = [];
    public $accounts = [];
    public $payment_statuses = [];

    public function mount(): void
    {
        $this->customers = Customer::with('user')->get();
        $this->agents = Agent::with('user')->get();
        $this->accounts = ChartOfAccount::with('parent')
            ->where('type', 'asset') // Filter by 'asset' type
            ->whereHas('parent', function ($query) {
                // Filter by parent category (Cash or Bank)
                $query->whereIn('name', ['Cash', 'Bank']);
            })
            ->get();

        $this->payment_statuses = AccountPaymentType::getPaymentTypes();
        $this->income_date = Carbon::now()->format('Y-m-d');

        $this->breakdowns = [
            ['title' => null, 'amount' => null],
        ];
        $this->recalculateAmountFromBreakdowns();
    }

    public function addBreakdownRow(): void
    {
        $this->breakdowns[] = ['title' => null, 'amount' => null];
    }

    public function removeBreakdownRow(int $index): void
    {
        if (!isset($this->breakdowns[$index])) {
            return;
        }

        array_splice($this->breakdowns, $index, 1);

        if (count($this->breakdowns) === 0) {
            $this->breakdowns = [['title' => null, 'amount' => null]];
        }

        $this->recalculateAmountFromBreakdowns();
    }

    public function updatedBreakdowns(): void
    {
        $this->recalculateAmountFromBreakdowns();
    }

    private function recalculateAmountFromBreakdowns(): void
    {
        $total = 0.0;
        foreach ($this->breakdowns as $row) {
            $val = $row['amount'] ?? 0;
            if ($val === '' || $val === null) {
                continue;
            }
            if (is_string($val)) {
                $val = str_replace(',', '', $val);
            }
            $total += (float) $val;
        }
        $this->amount = $total;
    }

    public function storeIncome()
    {
        $this->validate([
            'breakdowns' => ['required', 'array', 'min:1'],
            'breakdowns.*.title' => ['nullable', 'string', 'max:255'],
            'breakdowns.*.amount' => ['required', 'numeric', 'min:0.01'],
        ]);

        $this->recalculateAmountFromBreakdowns();
        $this->validate(); // validate attribute-based rules incl. $amount

        if (!$this->customer_id && !$this->agent_id) {
            $this->error('Customer or Agent Must Be Selected');
            return;
        }
        try {
            DB::beginTransaction();

            $income = Income::create([
                'customer_id' => $this->customer_id,
                'agent_id' => $this->agent_id,
                'account_id' => $this->account_id,
                'amount' => $this->amount,
                'income_date' => $this->income_date,
                'reference' => $this->reference,
                'remarks' => $this->remarks,
                'payment_status' => $this->payment_status,
                'created_by' => auth()->user()->id,
            ]);

            foreach (array_values($this->breakdowns) as $idx => $row) {
                IncomeBreakdown::create([
                    'income_id' => $income->id,
                    'title' => $row['title'] ?? null,
                    'amount' => $row['amount'] ?? 0,
                ]);
            }

            // Check if user is Super Admin
            $isSuperAdmin = auth()->user()->hasRole('Super Admin');

            if ($isSuperAdmin) {
                // Super Admin creates → transaction happens immediately if payment_status is Paid
                if ($income->payment_status == AccountPaymentType::Paid) {
                    TransactionService::recordTransaction([
                        'source_type' => Income::class,
                        'source_id' => $income->id,
                        'date' => $this->income_date ?? now()->toDateString(),
                        'amount' => $this->amount,
                        'debit_account_id' => $this->account_id,
                        'credit_account_id' => ChartOfAccount::where('name', 'Revenue Income')->first()->id,
                        'description' => 'Income Transaction - ' . ($this->reference ?? 'No Reference'),
                    ]);

                    $income->update([
                        'status' => TransactionStatus::APPROVED,
                    ]);
                } else {
                    $income->update([
                        'status' => TransactionStatus::PENDING,
                    ]);
                }
            } else {
                // Non-Super Admin creates → always PENDING, needs approval (no transaction)
                $income->update([
                    'status' => TransactionStatus::PENDING,
                ]);
            }

            DB::commit();
            $this->success('Income Added Successfully');
            return redirect('/admin/income/list');
        } catch (\Throwable $th) {
            DB::rollBack();
            $this->error($th->getMessage());
        }
    }
}; ?>

<div class="space-y-4">
    <!-- Top bar -->
    <x-header title="Create New Income" size="text-xl" class="bg-white px-3 py-3">
        <x-slot:actions>
            <x-button label="Back to Income List" wire:navigate href="/admin/income/list" icon="o-arrow-left"
                class="btn-primary btn-sm" />
        </x-slot:actions>
    </x-header>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 items-start">
        <!-- Main form (compact, 2 cols) -->
        <x-card class="lg:col-span-2 space-y-4">
            <x-form wire:submit="storeIncome" class="space-y-5">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-green-600">New Income Entry</p>
                        <p class="text-xs text-gray-500">Fill in the minimal details and add breakdown lines.</p>
                    </div>
                    <span class="inline-flex items-center rounded-full bg-emerald-50 px-2.5 py-1 text-[10px] font-medium text-emerald-700 border border-emerald-100">
                        Auto-calculated total
                    </span>
                </div>

                <p class="text-[11px] text-red-500 font-semibold">
                    Customer or Agent must be selected.
                </p>

                <!-- Party & account -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                    <x-choices label="Customer" wire:model="customer_id" placeholder="Select Customer" single
                        option-label="user.name" option-value="id" :options="$customers" />

                    <x-choices label="Agent" wire:model="agent_id" placeholder="Select Agent" single
                        option-label="user.name" option-value="id" :options="$agents" />

                    <x-choices label="Account" wire:model="account_id" placeholder="Select Account" single required
                        option-label="name" option-value="id" :options="$accounts" />
                </div>

                <!-- Breakdown block -->
                <div class="border border-gray-100 rounded-xl bg-slate-50/70 p-3 space-y-2">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs font-semibold text-gray-700">Amount breakdown</p>
                            <p class="text-[11px] text-gray-500">Add small parts instead of one big amount.</p>
                        </div>
                        <x-button type="button" label="Add More" icon="o-plus"
                            wire:click="addBreakdownRow"
                            class="btn-primary btn-xs" />
                    </div>

                    <div class="space-y-1.5">
                        @foreach ($breakdowns as $i => $row)
                            <div class="grid grid-cols-12 gap-3 items-end">
                                <div class="col-span-7">
                                    <x-input
                                        label="{{ $i === 0 ? 'Title' : '' }}"
                                        wire:model.live="breakdowns.{{ $i }}.title"
                                        placeholder="e.g. Service charge, Package price"
                                        class="w-full [&>input]:h-10 [&>input]:text-sm [&>input]:px-3" />
                                </div>
                                <div class="col-span-4">
                                    <x-input type="number" step="0.01" min="0"
                                        label="{{ $i === 0 ? 'Amount (BDT)' : '' }}"
                                        wire:model.live="breakdowns.{{ $i }}.amount"
                                        placeholder="0.00"
                                        class="w-full [&>input]:h-10 [&>input]:text-sm [&>input]:px-3" />
                                </div>
                                <div class="col-span-1 flex justify-end">
                                    <x-button type="button" icon="o-trash"
                                        wire:click="removeBreakdownRow({{ $i }})"
                                        class="btn-ghost btn-xs text-red-500" />
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="flex justify-end pt-2 border-t border-dashed border-gray-200 mt-2">
                        <div class="flex items-center gap-2 text-sm">
                            <span class="text-gray-500 text-xs">Total</span>
                            <x-input type="number" wire:model="amount" readonly
                                class="w-40 text-right text-sm font-semibold [&>input]:h-10 [&>input]:text-sm [&>input]:px-3"
                                suffix="BDT" />
                        </div>
                    </div>
                </div>

                <!-- Meta info -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                    <x-datetime wire:model="income_date" label="Income Date" />

                    <x-choices label="Payment Status" wire:model="payment_status"
                        :options="$payment_statuses" option-label="name" single
                        option-value="id" placeholder="Select Status" />

                    <x-input label="Reference" wire:model="reference"
                        placeholder="Invoice / Ref / Note" />
                </div>

                <x-input label="Remarks" wire:model="remarks"
                    placeholder="Short note about this income (optional)" />

                <!-- Actions -->
                <div class="flex justify-end gap-2 pt-3 border-t border-gray-100">
                    <x-button label="Cancel" wire:navigate href="/admin/income/list"
                        class="btn-outline btn-sm" />
                    <x-button type="submit" label="Create Income"
                        class="btn-primary btn-sm" spinner="storeIncome" />
                </div>
            </x-form>
        </x-card>

        <!-- Compact side summary -->
        <x-card class="lg:col-span-1 space-y-3 sticky top-20">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold text-gray-700">Income summary</p>
                    <p class="text-[11px] text-gray-500">Quick view of what you are creating.</p>
                </div>
            </div>

            <div class="space-y-2 text-xs">
                <div class="flex justify-between">
                    <span class="text-gray-500">Customer</span>
                    <span class="font-medium truncate max-w-[55%] text-right">
                        {{ optional(optional($customers->firstWhere('id', $customer_id))->user)->name ?? 'Not selected' }}
                    </span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">Agent</span>
                    <span class="font-medium truncate max-w-[55%] text-right">
                        {{ optional(optional($agents->firstWhere('id', $agent_id))->user)->name ?? 'Not selected' }}
                    </span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">Account</span>
                    <span class="font-medium truncate max-w-[55%] text-right">
                        {{ optional($accounts->firstWhere('id', $account_id))->name ?? 'Not selected' }}
                    </span>
                </div>
            </div>

            <div class="border-t border-gray-100 pt-3 space-y-2">
                <div class="flex items-center justify-between">
                    <span class="text-xs text-gray-500">Income Date</span>
                    <span class="text-xs font-medium">
                        {{ $income_date ?: 'Today' }}
                    </span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-xs text-gray-500">Payment Status</span>
                    <span class="text-[11px] font-semibold">
                        @if ($payment_status)
                            {{ collect($payment_statuses)->firstWhere('id', $payment_status)['name'] ?? 'Selected' }}
                        @else
                            Not set
                        @endif
                    </span>
                </div>
            </div>

            <div class="border-t border-gray-100 pt-3">
                <div class="flex items-baseline justify-between">
                    <span class="text-xs text-gray-500">Total Amount</span>
                    <span class="text-lg font-semibold text-emerald-600">
                        BDT {{ number_format($amount ?? 0, 2) }}
                    </span>
                </div>
            </div>
        </x-card>
    </div>
</div>
