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
        $accounts = ChartOfAccount::with(['ledgers' => fn($q) => $q->whereBetween('date', [$this->start, $this->end])])
            ->get()
            ->map(function ($acc) {
                $acc->total_debit = $acc->ledgers->sum('debit');
                $acc->total_credit = $acc->ledgers->sum('credit');

                return $acc;
            });

        $grouped = $accounts->groupBy('type')->map(function ($items) {
            return [
                'accounts' => $items,
                'total' => $items->sum(fn($acc) => $acc->total_debit - $acc->total_credit),
            ];
        });

        return [
            'grouped' => $grouped,
        ];
    }
}; ?>


<div>
    <div>
        <x-header title="Reports" size="text-xl" separator class="bg-white px-2 pt-2" />
        <x-button label="Trail Balance" link="/admin/accounts/reports/trail-balance"
            class="btn-sm mx-2 {{ url()->current() == url('/admin/accounts/reports/trail-balance') ? 'bg-green-500' : 'btn-primary' }}" />

        <x-button label="Balance Sheet" link="/admin/accounts/reports/balance-sheet"
            class="btn-sm mx-2 {{ url()->current() == url('/admin/accounts/reports/balance-sheet') ? 'bg-green-500' : 'btn-primary' }}" />
    </div>
    <x-card title="Balance Sheet Report" separator class="mt-2">
        <div class="grid grid-cols-2 gap-2">
            <x-datetime label="Start Date" wire:model.live="start" />
            <x-datetime label="End Date" wire:model.live="end" />
        </div>
        <div>
            @foreach ($grouped as $type => $group)
                <h3 class="text-xl font-semibold mt-4 mb-2 capitalize">{{ $type }}</h3>
                <table class="w-full border mt-2 table-auto">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="border px-4 py-2 text-left">Account</th>
                            <th class="border px-4 py-2 text-right">Balance</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($group['accounts'] as $acc)
                            <tr>
                                <td class="border px-4 py-2">{{ $acc->name }}</td>
                                <td class="border px-4 py-2 text-right">
                                    {{ number_format($acc->total_debit - $acc->total_credit, 2) }}
                                </td>
                            </tr>
                        @endforeach

                        <tr class="font-bold">
                            <td class="border px-4 py-2 text-right">Total {{ ucfirst($type) }}</td>
                            <td class="border px-4 py-2 text-right">
                                {{ number_format($group['total'], 2) }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            @endforeach
        </div>
    </x-card>
</div>
