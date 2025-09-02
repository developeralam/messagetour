<?php

use App\Models\ChartOfAccount;
use App\Models\OtherTransaction;
use App\Services\TransactionService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Rule;
use Livewire\Attributes\Title;
use Livewire\Volt\Component;
use Mary\Traits\Toast;

new #[Layout('components.layouts.admin')] #[Title('Receive Customer Payment')] class extends Component {
    use Toast;

    public $receives = [];

    public $froms = [];

    #[Rule('required')]
    public $payment_date;

    #[Rule('required')]
    public $receive_from;

    #[Rule('required')]
    public $post_to;

    #[Rule('required')]
    public $amount;

    #[Rule('nullable')]
    public $note;

    public function mount()
    {
        $this->payment_date = Carbon::now()->format('Y-m-d');
        $this->getReceives();
        $this->getFroms();
    }
    public function getReceives($search = '')
    {
        $this->receives = ChartOfAccount::account()->when($search, fn($q) => $q->where('name', 'LIKE', '%' . $search . '%'))->take(10)->get();
    }
    public function getFroms($search = '')
    {
        $this->froms = ChartOfAccount::account()->when($search, fn($q) => $q->where('name', 'LIKE', '%' . $search . '%'))->take(10)->get();
    }

    public function updated($property) {}

    /**
     * Fetches references (like chassis IDs) related to the selected customer
     * if the payment is being made for a vehicle. The references are
     * extracted from the customer's vehicle sales and set to the `refs` property.
     */
    public function storePayment(): void
    {
        // Validate input data based on defined validation rules
        $this->validate();

        try {
            // Create a new customer payment record
            $payment = OtherTransaction::create([
                'payment_date' => $this->payment_date,
                'receive_from' => $this->receive_from,
                'post_to' => $this->post_to,
                'amount' => $this->amount,
                'note' => $this->note,
            ]);
            TransactionService::recordTransaction([
                'source_type' => OtherTransaction::class,
                'source_id' => $payment->id,
                'date' => now(),
                'amount' => $this->amount, // The same price as the sale
                'debit_account_id' => $this->post_to, // Or use 'Bank' if payment is via bank
                'credit_account_id' => $this->receive_from,
                'description' => $payment->receiveFrom->name,
            ]);

            // Notify the user of success and redirect
            $this->success('Payment Added Successfully', redirectTo: '/admin/others-transcaiton/list');
        } catch (\Throwable $th) {
            dd($th->getMessage());
            // Log the error for debugging
            Log::error('Error in storePayment: ' . $th->getMessage(), [
                'trace' => $th->getTraceAsString(),
            ]);

            // Show a generic or detailed error message depending on APP_DEBUG
            $this->error(config('app.debug') ? $th->getMessage() : 'Something went wrong.');
        }
    }
}; ?>


<div>
    <x-header title="Other Transaction Voucher" size="text-xl" separator class="bg-white px-2 pt-3">
        <x-slot:actions>
            <x-button icon="fas.arrow-left" link="/admin/others-transcaiton/list" label="Payment List"
                class="btn-primary btn-sm" />
        </x-slot>
    </x-header>
    <x-form wire:submit="storePayment">
        <x-card class="w-6/12" x-cloak>
            <x-devider title="Other Transaction Voucher" />
            <x-datetime wire:model="payment_date" required class="mb-2" label="Payment Date" />
            <x-choices wire:model.live="receive_from" label="Receive From" :options="$receives" single searchable
                search-function="getReceives" placeholder="Select Account" class="mb-2" required />
            <x-choices wire:model.live="post_to" label="Post To" :options="$froms" single searchable
                search-function="getForms" placeholder="Select Account" class="mb-2" required />

            <x-input type="number" label="Amount" wire:model="amount" required placeholder="Amount" class="mb-2" />
            <x-input label="Note" wire:model="note" placeholder="Note" class="mb-2" />
            <x-slot:actions>
                <x-button class="btn-sm" label="Payment List" link="/admin/others-transcaiton/list" />
                <x-button type="submit" label="Add Payment" spinner="storePayment" class="btn-sm btn-primary" />
            </x-slot>
        </x-card>
    </x-form>
</div>
