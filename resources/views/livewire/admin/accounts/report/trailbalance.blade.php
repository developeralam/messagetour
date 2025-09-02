<?php

use App\Models\ChartOfAccount;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Volt\Component;
use Livewire\WithPagination;
use Mary\Traits\Toast;

new #[Layout('components.layouts.admin')] #[Title('Cash Payments Report')] class extends Component {
    use Toast, WithPagination;

    public $start;

    public $end;

    public function mount()
    {
        $this->start = now()->startOfMonth()->toDateString();
        $this->end = now()->endOfMonth()->toDateString();
    }

    public function with(): array
    {
        return [
            'trialBalance' => ChartOfAccount::whereNotIn('code', ['101', '102'])
                ->with(['ledgers' => fn($q) => $q->whereBetween('date', [$this->start, $this->end])])
                ->get()
                ->map(function ($acc) {
                    $acc->total_debit = $acc->ledgers->sum('debit');
                    $acc->total_credit = $acc->ledgers->sum('credit');

                    return $acc;
                }),
        ];
    }
}; ?>


<div>
    <div>
        <x-header title="Reports" size="text-xl" separator class="bg-white px-2 pt-2" />
        <x-button label="Trail Balance" link="/admin/accounts/reports/trail-balance"
            class="btn-sm {{ url()->current() == url('/admin/accounts/reports/trail-balance') ? 'bg-green-500' : 'btn-primary' }}" />

        <x-button label="Balance Sheet" link="/admin/accounts/reports/balance-sheet"
            class="btn-sm {{ url()->current() == url('/admin/accounts/reports/balance-sheet') ? 'bg-green-500' : 'btn-primary' }}" />
    </div>

    <x-card title="Trail Balance Report" separator class="mt-2 mb-0">
        <div class="grid grid-cols-2 gap-2">
            <x-datetime label="Start Date" wire:model.live="start" />
            <x-datetime label="End Date" wire:model.live="end" />
        </div>
        <div class="mt-2">
            <table class="w-full table-auto border border-gray-200">
                <thead>
                    <tr class="bg-gray-100">
                        <th class="border px-4 py-2 text-left">Account</th>
                        <th class="border px-4 py-2 text-right">Debit</th>
                        <th class="border px-4 py-2 text-right">Credit</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $totalDebit = $totalCredit = 0;
                    @endphp

                    @foreach ($trialBalance as $account)
                        <tr>
                            <td class="border px-4 py-2">{{ $account->name }}</td>
                            <td class="border px-4 py-2 text-right">{{ number_format($account->total_debit, 2) }}</td>
                            <td class="border px-4 py-2 text-right">{{ number_format($account->total_credit, 2) }}</td>
                        </tr>
                        @php
                            $totalDebit += $account->total_debit;
                            $totalCredit += $account->total_credit;
                        @endphp
                    @endforeach

                    <tr class="font-bold">
                        <td class="border px-4 py-2 text-right">Total</td>
                        <td class="border px-4 py-2 text-right">{{ number_format($totalDebit, 2) }}</td>
                        <td class="border px-4 py-2 text-right">{{ number_format($totalCredit, 2) }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </x-card>
</div>
