<?php

use App\Models\Transaction;
use Livewire\Volt\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;
use App\Enum\PaymentStatus;

new #[Layout('components.layouts.admin')] #[Title('Transactions History')] class extends Component {
    use WithPagination;

    public string $search = '';
    public array $headers;

    public function mount()
    {
        $this->headers = $this->headers();
    }

    public function headers(): array
    {
        return [['key' => 'id', 'label' => '#'], ['key' => 'order.user.name', 'label' => 'Customer Name'], ['key' => 'order.user.email', 'label' => 'Customer Email'], ['key' => 'order.phone', 'label' => 'Customer Mobile No'], ['key' => 'order.tran_id', 'label' => 'Transaction Id'], ['key' => 'order.paymentgateway.name', 'label' => 'Payment Gateway'], ['key' => 'payment_status', 'label' => 'Payment Status'], ['key' => 'total_amount', 'label' => 'Total Amount'], ['key' => 'action_by', 'label' => 'Last Action By']];
    }

    public function delete(Transaction $order)
    {
        try {
            $order->update([
                'action_by' => auth()->user()->id,
            ]);
            $order->delete();
            $this->success('Transaction Deleted Successfully');
        } catch (\Throwable $th) {
            $this->error(env('APP_DEBUG', false) ? $th->getMessage() : 'Something went wrong while deleting the order');
        }
    }

    public function transactions()
    {
        return Transaction::with(['order.user', 'actionBy'])
            ->whereHas('order', function ($query) {
                $query->where('payment_status', PaymentStatus::Paid);
            })
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->whereHas('order.user', function ($userQuery) {
                        $userQuery->where('name', 'like', '%' . $this->search . '%')->orWhere('email', 'like', '%' . $this->search . '%');
                    });
                });
            })
            ->latest()
            ->paginate(10);
    }

    public function with()
    {
        return [
            'transactions' => $this->transactions(),
        ];
    }
}; ?>

<div>
    <x-header title="Transactions History" size="text-xl" separator class="bg-white px-2 pt-2">
        <x-slot:middle class="!justify-end">
            <x-input icon="o-bolt" wire:model.live="search" placeholder="Search..." class="max-w-36" />
        </x-slot:middle>
    </x-header>
    <x-card>
        <x-table :headers="$headers" :rows="$transactions">
            @scope('cell_id', $transaction, $transactions)
                {{ $loop->iteration + ($transactions->currentPage() - 1) * $transactions->perPage() }}
            @endscope
            @scope('cell_action_by', $transaction)
                {{ $transaction->actionby->name ?? '' }}
            @endscope
            @scope('cell_payment_status', $transaction)
                @if ($transaction->order->payment_status == \App\Enum\PaymentStatus::Paid)
                    <x-badge value="{{ $transaction->order->payment_status->label() }}"
                        class="bg-green-100 text-green-700 p-3 text-xs font-semibold" />
                @elseif ($transaction->order->payment_status == \App\Enum\PaymentStatus::Unpaid)
                    <x-badge value="{{ $transaction->order->payment_status->label() }}"
                        class="bg-yellow-100 text-yellow-700 p-3 text-xs font-semibold" />
                @elseif ($transaction->order->payment_status == \App\Enum\PaymentStatus::Failed)
                    <x-badge value="{{ $transaction->order->payment_status->label() }}"
                        class="bg-red-100 text-red-700 p-3 text-xs font-semibold" />
                @elseif ($transaction->order->payment_status == \App\Enum\PaymentStatus::Cancelled)
                    <x-badge value="{{ $transaction->order->payment_status->label() }}"
                        class="bg-red-100 text-red-700 p-3 text-xs font-semibold" />
                @endif
            @endscope
            @scope('cell_total_amount', $transaction)
                BDT {{ $transaction->order->total_amount }}
            @endscope
            @scope('cell_tran_id', $transaction)
                {{ $transaction->order->tran_id }}
            @endscope
            @scope('actions', $transaction)
                <x-button icon="o-trash" wire:click="delete({{ $transaction->id }})" wire:confirm="Are you sure?"
                    class="btn-action btn-error" spinner="delete({{ $transaction->id }})" />
            @endscope
        </x-table>
    </x-card>
</div>
