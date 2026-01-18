<?php

use Carbon\Carbon;
use App\Models\Agent;
use App\Models\Deposit;
use App\Models\Withdraw;
use App\Models\Order;
use App\Models\Income;
use App\Models\Transactions;
use App\Enum\UserType;
use App\Enum\DepositStatus;
use App\Enum\WithdrawStatus;
use App\Enum\AccountPaymentType;
use Livewire\Volt\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;
use Illuminate\Database\Eloquent\Builder;

new #[Layout('components.layouts.admin')] #[Title('Agent Ledger Report')] class extends Component {
    use WithPagination;

    public Agent $agent;
    public $date_from;
    public $date_to;
    public $payment_status_filter = null;
    public $payment_statuses = [];
    public $opening_balance = 0;
    public $total_debit = 0;
    public $total_credit = 0;
    public $closing_balance = 0;
    public $total_paid = 0;
    public $total_unpaid = 0;
    public $total_sum = 0;
    public array $headers;

    public function mount(Agent $agent): void
    {
        $this->agent = $agent;
        $this->date_from = Carbon::now()->toDateString();
        $this->date_to = Carbon::now()->toDateString();
        $this->payment_statuses = AccountPaymentType::getPaymentTypes();
        $this->headers = $this->headers();
        $this->calculateBalances();
    }

    public function headers(): array
    {
        return [['key' => 'date', 'label' => 'Date'], ['key' => 'type', 'label' => 'Type'], ['key' => 'description', 'label' => 'Description'], ['key' => 'account', 'label' => 'Account'], ['key' => 'payment_status', 'label' => 'Payment Status'], ['key' => 'amount', 'label' => 'Amount']];
    }

    public function updatedDateFrom()
    {
        $this->resetPage();
        $this->calculateBalances();
    }

    public function updatedDateTo()
    {
        $this->resetPage();
        $this->calculateBalances();
    }

    public function updatedPaymentStatusFilter()
    {
        $this->resetPage();
        $this->calculateBalances();
    }

    public function calculateBalances()
    {
        $fromDate = Carbon::parse($this->date_from)->startOfDay();
        $toDate = Carbon::parse($this->date_to)->endOfDay();

        // Calculate opening balance (all income before date_from)
        $openingDeposits = Deposit::where('agent_id', $this->agent->id)->where('status', DepositStatus::Approved)->where('deposit_date', '<', $fromDate)->sum('amount');

        // Calculate orders where agent is buyer (admin receives payment)
        $openingOrderPayments = Order::where('user_id', $this->agent->user_id)
            ->where('payment_gateway_id', 5) // Wallet payment
            ->where('payment_status', \App\Enum\PaymentStatus::Paid)
            ->where('created_at', '<', $fromDate)
            ->sum('total_amount');

        // Calculate incomes from agent (filter by payment_status if selected)
        $openingIncomesQuery = Income::where('agent_id', $this->agent->id)->where('income_date', '<', $fromDate);

        if ($this->payment_status_filter) {
            $openingIncomesQuery->where('payment_status', $this->payment_status_filter);
        } else {
            // Default: only show Paid if no filter selected (for income calculation)
            $openingIncomesQuery->where('payment_status', AccountPaymentType::Paid);
        }

        $openingIncomes = $openingIncomesQuery->sum('amount');

        $this->opening_balance = $openingDeposits + $openingOrderPayments + $openingIncomes;

        // Calculate totals for the period (only income)
        $periodDeposits = Deposit::where('agent_id', $this->agent->id)
            ->where('status', DepositStatus::Approved)
            ->whereBetween('deposit_date', [$fromDate, $toDate])
            ->sum('amount');

        $periodOrderPayments = Order::where('user_id', $this->agent->user_id)
            ->where('payment_gateway_id', 5)
            ->where('payment_status', \App\Enum\PaymentStatus::Paid)
            ->whereBetween('created_at', [$fromDate, $toDate])
            ->sum('total_amount');

        $periodIncomesQuery = Income::where('agent_id', $this->agent->id)->whereBetween('income_date', [$fromDate, $toDate]);

        if ($this->payment_status_filter) {
            $periodIncomesQuery->where('payment_status', $this->payment_status_filter);
        } else {
            // Default: only show Paid if no filter selected (for income calculation)
            $periodIncomesQuery->where('payment_status', AccountPaymentType::Paid);
        }

        $periodIncomes = $periodIncomesQuery->sum('amount');

        $this->total_credit = $periodDeposits + $periodOrderPayments + $periodIncomes;
        $this->total_debit = 0; // No debits, only income
        $this->closing_balance = $this->opening_balance + $this->total_credit;

        // Calculate total paid and unpaid (always show all, not filtered)
        $this->total_paid = Income::where('agent_id', $this->agent->id)
            ->where('payment_status', AccountPaymentType::Paid)
            ->whereBetween('income_date', [$fromDate, $toDate])
            ->sum('amount');

        $this->total_unpaid = Income::where('agent_id', $this->agent->id)
            ->where('payment_status', AccountPaymentType::Unpaid)
            ->whereBetween('income_date', [$fromDate, $toDate])
            ->sum('amount');

        // Calculate total sum (paid + unpaid)
        $this->total_sum = $this->total_paid + $this->total_unpaid;
    }

    public function ledgerEntries()
    {
        $fromDate = Carbon::parse($this->date_from)->startOfDay();
        $toDate = Carbon::parse($this->date_to)->endOfDay();

        $entries = collect();

        // Get deposits (admin receives from agent)
        $deposits = Deposit::where('agent_id', $this->agent->id)
            ->where('status', DepositStatus::Approved)
            ->whereBetween('deposit_date', [$fromDate, $toDate])
            ->get()
            ->map(function ($deposit) {
                // Get transaction description
                $transaction = Transactions::where('source_type', Deposit::class)
                    ->where('source_id', $deposit->id)
                    ->first();
                
                return [
                    'date' => $deposit->deposit_date,
                    'type' => 'Deposit',
                    'description' => $transaction->description ?? ('Deposit - ' . ($deposit->trx_id ?? 'N/A')),
                    'account' => $transaction->debitAccount->name ?? 'N/A',
                    'amount' => $deposit->amount,
                    'payment_status' => null,
                    'raw' => $deposit,
                ];
            });

        // Get orders where agent is buyer (admin receives payment)
        $orderPayments = Order::where('user_id', $this->agent->user_id)
            ->where('payment_gateway_id', 5)
            ->where('payment_status', \App\Enum\PaymentStatus::Paid)
            ->whereBetween('created_at', [$fromDate, $toDate])
            ->get()
            ->map(function ($order) {
                // Get transaction description
                $transaction = Transactions::where('source_type', Order::class)
                    ->where('source_id', $order->id)
                    ->first();
                
                // Fallback description if no transaction
                if (!$transaction) {
                    $bookingType = class_basename($order->sourceable_type);
                    $itemName = match ($bookingType) {
                        'HotelRoomBooking' => optional(optional($order->sourceable->hotelbookingitems->first())->room?->hotel)->name ?? 'Hotel Booking',
                        'TourBooking' => optional($order->sourceable->tour)->title ?? 'Tour Booking',
                        'TravelProductBooking' => optional($order->sourceable->travelproduct)->title ?? 'Travel Product Booking',
                        default => 'Booking',
                    };
                    $fallbackDescription = 'Payment for ' . $itemName . ' (Order #' . $order->id . ')';
                } else {
                    $fallbackDescription = 'Order Payment (Order #' . $order->id . ')';
                }
                
                return [
                    'date' => $order->created_at,
                    'type' => 'Order Payment',
                    'description' => $transaction->description ?? $fallbackDescription,
                    'account' => $transaction->debitAccount->name ?? 'N/A',
                    'amount' => $order->total_amount,
                    'payment_status' => null,
                    'raw' => $order,
                ];
            });

        // Get incomes from agent (filter by payment_status if selected)
        $incomesQuery = Income::where('agent_id', $this->agent->id)->whereBetween('income_date', [$fromDate, $toDate]);

        if ($this->payment_status_filter) {
            $incomesQuery->where('payment_status', $this->payment_status_filter);
        } else {
            // Default: show all if no filter selected (for display)
            // But for calculation, we only count Paid
        }

        $incomes = $incomesQuery->get()->map(function ($income) {
            // Get transaction description
            $transaction = Transactions::where('source_type', Income::class)
                ->where('source_id', $income->id)
                ->first();
            
            // Fallback description if no transaction
            $fallbackDescription = 'Income - ' . ($income->reference ?? 'N/A') . ($income->remarks ? ' (' . $income->remarks . ')' : '');
            
            return [
                'date' => $income->income_date,
                'type' => 'Income',
                'description' => $transaction->description ?? $fallbackDescription,
                'account' => $transaction ? ($transaction->debitAccount->name ?? ($income->account->name ?? 'N/A')) : ($income->account->name ?? 'N/A'),
                'amount' => $income->amount,
                'payment_status' => $income->payment_status,
                'raw' => $income,
            ];
        });

        // Merge all entries (only income)
        $allEntries = $deposits->concat($orderPayments)->concat($incomes);

        // Sort by date
        $sortedEntries = $allEntries->sortBy('date');

        // Paginate manually
        $page = request()->get('page', 1);
        $perPage = 20;
        $offset = ($page - 1) * $perPage;
        $items = $sortedEntries->slice($offset, $perPage)->values();

        // Create paginator
        $paginator = new \Illuminate\Pagination\LengthAwarePaginator($items, $sortedEntries->count(), $perPage, $page, ['path' => request()->url(), 'query' => request()->query()]);

        return $paginator;
    }

    public function with(): array
    {
        return [
            'entries' => $this->ledgerEntries(),
        ];
    }
}; ?>

<div>
    <x-header title="Agent Ledger Report" size="text-xl" separator class="bg-white px-2 pt-2">
        <x-slot:middle class="!justify-end">
        </x-slot:middle>
        <x-slot:actions>
            <x-datetime type="date" wire:model.live="date_from" label="From Date" inline />
            <x-datetime type="date" wire:model.live="date_to" label="To Date" inline />
            <x-select wire:model.live="payment_status_filter" placeholder="All Payment Status" :options="$payment_statuses"
                option-label="name" option-value="id" inline />
            <x-button label="Agent List" icon="fas.arrow-left" link="/admin/agent/list" class="btn-primary btn-sm" />
        </x-slot:actions>
    </x-header>

    <x-card>
        <div class="mb-4">
            <div class="bg-gradient-to-r from-blue-50 to-indigo-50 rounded-lg p-4 border border-blue-100">
                <div class="flex items-center justify-between mb-3">
                    <div>
                        <h3 class="text-lg font-bold text-gray-800">{{ $agent->business_name }}</h3>
                        <p class="text-xs text-gray-600">Agent: {{ $agent->user->name ?? 'N/A' }}</p>
                    </div>
                </div>
                
                <div class="grid grid-cols-3 gap-3">
                    <div class="bg-white rounded-lg p-3 border-l-3 border-green-500">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-xs text-gray-500 mb-0.5">Total Paid</p>
                                <p class="text-lg font-bold text-green-600">BDT {{ number_format($total_paid, 2) }}</p>
                            </div>
                            <div class="bg-green-100 rounded-full p-2">
                                <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-white rounded-lg p-3 border-l-3 border-yellow-500">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-xs text-gray-500 mb-0.5">Total Unpaid</p>
                                <p class="text-lg font-bold text-yellow-600">BDT {{ number_format($total_unpaid, 2) }}</p>
                            </div>
                            <div class="bg-yellow-100 rounded-full p-2">
                                <svg class="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-lg p-3 border-l-3 border-blue-500">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-xs text-gray-500 mb-0.5">Total Sum</p>
                                <p class="text-lg font-bold text-blue-600">BDT {{ number_format($total_sum, 2) }}</p>
                            </div>
                            <div class="bg-blue-100 rounded-full p-2">
                                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <x-table :headers="$headers" :rows="$entries" with-pagination>
            @scope('cell_date', $entry)
                {{ Carbon::parse($entry['date'])->format('d M Y') }}
            @endscope

            @scope('cell_type', $entry)
                @php
                    $badgeClass = match ($entry['type']) {
                        'Deposit' => 'bg-green-100 text-green-700',
                        'Order Payment' => 'bg-blue-100 text-blue-700',
                        'Income' => 'bg-purple-100 text-purple-700',
                        default => 'bg-gray-100 text-gray-700',
                    };
                @endphp
                <x-badge value="{{ $entry['type'] }}" class="{{ $badgeClass }} p-2 text-xs font-semibold" />
            @endscope

            @scope('cell_description', $entry)
                {{ $entry['description'] }}
            @endscope

            @scope('cell_account', $entry)
                <span class="font-medium">{{ $entry['account'] ?? 'N/A' }}</span>
            @endscope

            @scope('cell_payment_status', $entry)
                @if (isset($entry['payment_status']) && $entry['payment_status'])
                    <x-badge value="{{ $entry['payment_status']->label() }}"
                        class="{{ $entry['payment_status'] == \App\Enum\AccountPaymentType::Paid ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700' }} p-2 text-xs font-semibold" />
                @else
                    <span class="text-gray-400">-</span>
                @endif
            @endscope

            @scope('cell_amount', $entry)
                <span>BDT {{ number_format($entry['amount'], 2) }}</span>
            @endscope
        </x-table>
    </x-card>
</div>
