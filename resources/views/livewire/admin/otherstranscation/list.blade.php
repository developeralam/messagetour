<?php

use Mary\Traits\Toast;
use Livewire\Volt\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Title;
use App\Models\CustomerPayment;
use Livewire\Attributes\Layout;
use App\Models\OtherTransaction;

new #[Layout('components.layouts.admin')] #[Title('Others Transcation List')] class extends Component {
    use Toast, WithPagination;

    public $headers = [];

    public string $search = '';

    public $customer_for_search;

    public $payment_for_search;

    public $status_for_search;

    public $chassis_id_for_search;

    public $paymentFors = [];

    public $statuses = [];

    public $customers = [];

    public function mount()
    {
        $this->headers = $this->headers();
    }

    /**
     * Deletes a customer payment record.
     *
     * @param  CustomerPayment  $payment  The payment instance to be deleted
     */
    public function delete($payment): void
    {
        try {
            // Soft deletes or permanently removes the payment record
            OtherTransaction::find($payment)->delete();
            // Show success notification
            $this->success('Payment Deleted Successfully');
        } catch (\Throwable $th) {
            // Show error notification with the exception message
            $this->error(config('app.debug') ? $th->getMessage() : 'Something went wrong while deleting payment.');
        }
    }

    /**
     * Returns an array of table headers used for rendering the payment list table.
     *
     * @return array<int, array<string, string>>
     */
    public function headers(): array
    {
        return [['key' => 'id', 'label' => '#'], ['key' => 'payment_date', 'label' => 'Date'], ['key' => 'receiveFrom.name', 'label' => 'Receive From'], ['key' => 'postTo.name', 'label' => 'Post To'], ['key' => 'amount', 'label' => 'Amount'], ['key' => 'actions', 'label' => 'Action']];
    }

    /**
     * Retrieves a paginated list of customer payments filtered by payment type or customer.
     *
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function payments()
    {
        return OtherTransaction::query()
            ->when($this->payment_for_search, fn($query) => $query->where('payment_for', $this->payment_for_search))
            ->when($this->customer_for_search, fn($query) => $query->whereHas('customer', fn($q) => $q->where('id', $this->customer_for_search)))
            ->latest() // Order by latest date
            ->paginate(20); // Paginate results (20 per page)
    }

    /**
     * Shares component data with the Livewire Volt view layer.
     *
     * @return array<string, mixed>
     */
    public function with(): array
    {
        return [
            'payments' => $this->payments(),
        ];
    }
}; ?>


<div>
    <x-header title="Other Transaction List" size="text-xl" separator class="bg-white px-2 pt-3">
        <x-slot:actions>
            <x-button label="Add Voucher" icon="fas.plus" link="/admin/others-transcaiton/create"
                class="btn-primary btn-sm" />
        </x-slot>
    </x-header>
    <x-card>
        <x-table :headers="$headers" :rows="$payments" with-pagination>
            @scope('cell_id', $payment, $payments)
                {{ $loop->iteration + ($payments->currentPage() - 1) * $payments->perPage() }}
            @endscope

            @scope('cell_payment_date', $payment)
                {{ $payment->payment_date }}
            @endscope

            @scope('cell_actions', $payment)
                <div class="flex items-center gap-1">
                    <x-button icon="o-trash" wire:click="delete({{ $payment['id'] }})" wire:confirm="Are you sure?"
                        class="btn-primary btn-action text-white" spinner="delete({{ $payment['id'] }})" />
                </div>
            @endscope
        </x-table>
    </x-card>
</div>
