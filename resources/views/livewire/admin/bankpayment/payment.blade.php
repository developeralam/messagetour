<?php

use App\Models\Bank;
use App\Models\User;
use App\Models\Agent;
use Mary\Traits\Toast;
use App\Models\Country;
use App\Models\Payment;
use Livewire\Volt\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Rule;
use Livewire\WithFileUploads;
use App\Models\PaymentGateway;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;
use App\Enum\WalletPaymentStatus;
use Illuminate\Support\Facades\DB;
use App\Traits\InteractsWithImageUploads;

new #[Layout('components.layouts.admin')] #[Title('Payments')] class extends Component {
    use Toast, WithPagination, InteractsWithImageUploads, WithFileUploads;

    public bool $createModal = false;
    public bool $editModal = false;
    public $agents = [];
    public $paymentGateways = [];
    public $banks = [];
    public $statuses = [];
    public Payment $payment;

    #[Rule('required')]
    public $agent_id;

    #[Rule('required')]
    public $depositBank_name;

    #[Rule('required')]
    public $receive_bank_id;

    #[Rule('required')]
    public $payment_gateway_id;

    #[Rule('nullable')]
    public $branch_name;

    #[Rule('required')]
    public $amount;

    #[Rule('required')]
    public $desposit_date;

    #[Rule('nullable')]
    public $image;

    #[Rule('nullable')]
    public $reference;

    #[Rule('required')]
    public $status;

    public function mount()
    {
        $this->agents = Agent::all();
        $this->paymentGateways = PaymentGateway::all();
        $this->banks = Bank::all();
        $this->statuses = WalletPaymentStatus::getStatuses();
    }
    public function storePayment()
    {
        $this->validate();
        DB::beginTransaction();
        try {
            $storedImagePath = null;
            if ($this->image) {
                $storedImagePath = $this->optimizeAndStoreImage(
                    $this->image, // The file from Livewire
                    'public', // The disk to store on
                    'payment', // The subdirectory within the disk
                    null, // Optional max width
                    null, // Optional max height
                    75, // WEBP quality
                );
            }
            Payment::create([
                'agent_id' => $this->agent_id,
                'payment_gateway_id' => $this->payment_gateway_id,
                'depositBank_name' => $this->depositBank_name,
                'receive_bank_id' => $this->receive_bank_id,
                'branch_name' => $this->branch_name,
                'amount' => $this->amount,
                'desposit_date' => $this->desposit_date,
                'reference' => $this->reference,
                'image' => $storedImagePath,
                'status' => $this->status,
            ]);

            Agent::where('id', $this->agent_id)->increment('wallet', $this->amount);

            DB::commit();

            $this->success('Payment Added Successfully');
            $this->reset(['agent_id', 'payment_gateway_id', 'depositBank_name', 'receive_bank_id', 'branch_name', 'amount', 'desposit_date', 'reference', 'image', 'status']);
            $this->createModal = false;
        } catch (\Throwable $th) {
            $this->createModal = false;
            DB::rollBack();
            $this->error(config('app.debug') ? $th->getMessage() : 'Something went wrong.');
        }
    }
    public function edit(Payment $payment)
    {
        $this->payment = $payment;
        $this->agent_id = $payment->agent_id;
        $this->payment_gateway_id = $payment->payment_gateway_id;
        $this->depositBank_name = $payment->depositBank_name;
        $this->receive_bank_id = $payment->receive_bank_id;
        $this->branch_name = $payment->branch_name;
        $this->amount = $payment->amount;
        $this->desposit_date = $payment->desposit_date;
        $this->reference = $payment->reference;
        $this->status = $payment->status;
        $this->editModal = true;
    }
    public function updatePayment()
    {
        $this->validate();
        DB::beginTransaction();
        try {
            $storedImagePath = null;
            if ($this->image) {
                $storedImagePath = $this->optimizeAndUpdateImage(
                    $this->image,
                    $this->payment->image, // old path
                    'public',
                    'payment',
                    null,
                    null,
                    75,
                );
            } else {
                $storedImagePath = $this->payment->image;
            }

            $this->payment->update([
                'agent_id' => $this->agent_id,
                'payment_gateway_id' => $this->payment_gateway_id,
                'depositBank_name' => $this->depositBank_name,
                'receive_bank_id' => $this->receive_bank_id,
                'branch_name' => $this->branch_name,
                'amount' => $this->amount,
                'desposit_date' => $this->desposit_date,
                'reference' => $this->reference,
                'image' => $storedImagePath,
                'status' => $this->status,
                'action_id' => auth()->user()->id,
            ]);

            DB::commit();
            $this->success('Payment Updated Successfully');
            $this->reset(['agent_id', 'payment_gateway_id', 'depositBank_name', 'receive_bank_id', 'branch_name', 'amount', 'desposit_date', 'reference', 'image', 'status']);
            $this->editModal = false;
        } catch (\Throwable $th) {
            $this->editModal = false;
            DB::rollBack();
            $this->error(config('app.debug') ? $th->getMessage() : 'Something went wrong.');
        }
    }
    public function delete(Payment $payment)
    {
        try {
            $payment->update([
                'action_id' => auth()->user()->id,
            ]);
            $payment->delete();
            $this->success('Payment Deleted Successfully');
        } catch (\Throwable $th) {
            $this->error(config('app.debug') ? $th->getMessage() : 'Something went wrong.');
        }
    }
    public function headers(): array
    {
        return [['key' => 'id', 'label' => '#'], ['key' => 'agent.user.name', 'label' => 'Agent'], ['key' => 'paymentgateway.name', 'label' => 'Payment Gateway'], ['key' => 'receivebank.name', 'label' => 'Receive Bank'], ['key' => 'amount', 'label' => 'Amount'], ['key' => 'desposit_date', 'label' => 'Deposit Date'], ['key' => 'status', 'label' => 'Status'], ['key' => 'action_id', 'label' => 'Last Action By']];
    }
    public function paymentMethods()
    {
        return Payment::query()
            ->with(['agent', 'paymentgateway', 'receivebank'])
            ->latest()
            ->paginate(20);
    }
    public function with(): array
    {
        return [
            'headers' => $this->headers(),
            'paymentMethods' => $this->paymentMethods(),
        ];
    }
}; ?>

<div>
    <x-header title="Payment List" size="text-xl" separator class="bg-white px-2 pt-2">
        <x-slot:actions>
            <x-button icon="fas.plus" @click="$wire.createModal = true" label="Add Payment" class="btn-primary btn-sm" />
        </x-slot:actions>
    </x-header>
    <x-card>
        <x-table :headers="$headers" :rows="$paymentMethods" with-pagination>
            @scope('cell_id', $payment, $paymentMethods)
                {{ $loop->iteration + ($paymentMethods->currentPage() - 1) * $paymentMethods->perPage() }}
            @endscope
            @scope('cell_status', $payment)
                {{ $payment->status->label() }}
            @endscope
            @scope('cell_action_id', $payment)
                {{ $payment->actionby->name ?? '' }}
            @endscope
            @scope('actions', $payment)
                <div class="flex items-center gap-1">
                    <x-button icon="o-trash" wire:click="delete({{ $payment['id'] }})" wire:confirm="Are you sure?"
                        class="btn-error btn-action" />
                    <x-button icon="s-pencil-square" wire:click="edit({{ $payment['id'] }})"
                        class="btn-neutral btn-action" />
                </div>
            @endscope
        </x-table>
    </x-card>
    <x-modal wire:model="createModal" title="Add New Payment" separator boxClass="max-w-4xl">
        <x-form wire:submit="storePayment">
            <div class="grid grid-cols-3 gap-4">
                <x-choices label="Agent" wire:model="agent_id" :options="$agents" single placeholder="Select Agent"
                    required option-label="user.name" option-value="id" />
                <x-choices label="Payment Method" wire:model="payment_gateway_id" placeholder="Payment Method"
                    :options="$paymentGateways" single required />
                <x-input label="Deposited From" wire:model="depositBank_name" placeholder="Deposited From" />
                <x-choices label="Deposited To" wire:model="receive_bank_id" placeholder="Deposited To"
                    :options="$banks" single required />
                <x-input label="Branch Name (Optional)" wire:model="branch_name" placeholder="Branch Name" />
                <x-input type="number" label="Amount" wire:model="amount" placeholder="Amount" required />
                <x-datetime label="Deposit Date" wire:model="desposit_date" required />
                <x-input label="Reference Number" wire:model="reference" placeholder="Reference Number" />
                <x-choices label="Payment Status" wire:model="status" placeholder="Payment Status" :options="$statuses"
                    single required />
                <x-file label="Payment Slip/Photo" wire:model="image" />
            </div>
            <x-slot:actions>
                <x-button class="btn-sm" label="Cancel" @click="$wire.createModal = false" />
                <x-button type="submit" label="Add Payment" spinner="storePayment" class="btn-sm btn-primary" />
            </x-slot:actions>
        </x-form>
    </x-modal>
    <x-modal wire:model="editModal" title="Update Payment" separator boxClass="max-w-4xl">
        <x-form wire:submit="updatePayment">
            <div class="grid grid-cols-3 gap-4">
                <x-choices label="Agent" wire:model="agent_id" :options="$agents" single placeholder="Select Agent"
                    required option-label="user.name" option-value="id" />
                <x-choices label="Payment Method" wire:model="payment_gateway_id" placeholder="Payment Method"
                    :options="$paymentGateways" single required />
                <x-input label="Deposited From" wire:model="depositBank_name" placeholder="Deposited From" />
                <x-choices label="Deposited To" wire:model="receive_bank_id" placeholder="Deposited To"
                    :options="$banks" single required />
                <x-input label="Branch Name (Optional)" wire:model="branch_name" placeholder="Branch Name" />
                <x-input type="number" label="Amount" wire:model="amount" placeholder="Amount" required />
                <x-datetime label="Deposit Date" wire:model="desposit_date" required />
                <x-input label="Reference Number" wire:model="reference" placeholder="Reference Number" />
                <x-choices label="Payment Status" wire:model="status" placeholder="Payment Status" :options="$statuses"
                    single required />
                <x-file label="Payment Slip/Photo" wire:model="image" />
            </div>
            <x-slot:actions>
                <x-button class="btn-sm" label="Cancel" @click="$wire.createModal = false" />
                <x-button type="submit" label="Update Payment Method" spinner="updatePayment"
                    class="btn-sm btn-primary" />
            </x-slot:actions>
        </x-form>
    </x-modal>
</div>
