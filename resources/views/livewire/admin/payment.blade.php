<?php

use App\Models\User;
use App\Enum\UserType;
use Mary\Traits\Toast;
use App\Models\Country;
use App\Enum\UserStatus;
use App\Models\Customer;
use App\Models\District;
use App\Models\Division;
use App\Models\PaymentGateway;
use Livewire\Volt\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Rule;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\Hash;

new #[Layout('components.layouts.admin')] #[Title('Payment Gateway List')] class extends Component {
    use WithPagination, Toast;
    public array $headers;
    public string $search = '';
    public bool $createModal = false;
    public bool $editModal = false;
    public PaymentGateway $paymentGateway;

    #[Rule('required')]
    public $name;

    #[Rule('required')]
    public $charge;

    #[Rule('nullable')]
    public $is_active;

    public function mount()
    {
        $this->headers = $this->headers();
    }
    public function storePaymentGateway()
    {
        $this->validate();
        try {
            PaymentGateway::create([
                'name' => $this->name,
                'charge' => $this->charge,
                'is_active' => $this->is_active,
            ]);
            $this->success('New Payment Gatway Added Successfully');
            $this->reset(['name', 'charge', 'is_active']);
            $this->createModal = false;
        } catch (\Throwable $th) {
            $this->createModal = false;
            $this->error(env('APP_DEBUG') ? $th->getMessage() : 'Something went wrong.');
        }
    }
    public function edit(PaymentGateway $paymentGateway)
    {
        $this->paymentGateway = $paymentGateway;
        $this->name = $paymentGateway->name ?? '';
        $this->charge = $paymentGateway->charge ?? '';
        $this->is_active = $paymentGateway->is_active ?? '';
        $this->editModal = true;
    }
    public function updatePaymentGateway()
    {
        $this->validate();
        try {
            $this->paymentGateway->update([
                'name' => $this->name,
                'charge' => $this->charge,
                'is_active' => $this->is_active,
            ]);
            $this->success('Payment Gateway Updated Successfully');
            $this->reset(['name', 'charge', 'is_active']);
            $this->editModal = false;
        } catch (\Throwable $th) {
            $this->editModal = false;
            $this->error(env('APP_DEBUG') ? $th->getMessage() : 'Something went wrong.');
        }
    }
    public function delete(PaymentGateway $paymentGateway)
    {
        try {
            $paymentGateway->update([
                'action_by' => auth()->user()->id,
            ]);
            $paymentGateway->delete();
            $this->success('Payment Gateway Deleted Successfully');
        } catch (\Throwable $th) {
            $this->error($th->getMessage());
        }
    }
    public function headers(): array
    {
        return [['key' => 'id', 'label' => '#'], ['key' => 'name', 'label' => 'Payment Gateway'], ['key' => 'charge', 'label' => 'Charge'], ['key' => 'is_active', 'label' => 'Is Active?'], ['key' => 'action_by', 'label' => 'Last Action By']];
    }
    public function paymentGateways()
    {
        return PaymentGateway::query()->with('actionBy')->latest()->paginate(20);
    }
    public function with(): array
    {
        return [
            'paymentGateways' => $this->paymentGateways(),
        ];
    }
}; ?>

<div>
    <x-header title="Payment Gateway List" size="text-xl" separator class="bg-white px-2 pt-2">
        <x-slot:middle class="!justify-end">
            <x-input icon="o-bolt" wire:model.live="search" placeholder="Search..." />
        </x-slot:middle>
        <x-slot:actions>
            <x-button label="Add Gateway" icon="o-plus" @click="$wire.createModal = true" class="btn-primary btn-sm" />
        </x-slot:actions>
    </x-header>
    <x-card>
        <x-table :headers="$headers" :rows="$paymentGateways" with-pagination>
            @scope('cell_id', $paymentGateway, $paymentGateways)
                {{ $loop->iteration + ($paymentGateways->currentPage() - 1) * $paymentGateways->perPage() }}
            @endscope
            @scope('cell_is_active', $paymentGateway)
                @if ($paymentGateway->is_active == 1)
                    Yes
                @else
                    No
                @endif
            @endscope
            @scope('cell_action_by', $fapaymentGatewayq)
                {{ $paymentGateway->actionBy->name ?? '' }}
            @endscope
            @scope('actions', $paymentGateway)
                <div class="flex items-center gap-1">
                    <x-button icon="o-trash" wire:click="delete({{ $paymentGateway['id'] }})" wire:confirm="Are you sure?"
                        class="btn-error btn-action" spinner="delete({{ $paymentGateway['id'] }})" />
                    <x-button icon="s-pencil-square" wire:click="edit({{ $paymentGateway['id'] }})"
                        spinner="edit({{ $paymentGateway['id'] }})" class="btn-neutral btn-action" />
                </div>
            @endscope
        </x-table>
    </x-card>
    <x-modal wire:model="createModal" title="Add New Payment Gateway" size="text-xl" separator>
        <x-form wire:submit="storePaymentGateway">
            <x-input label="Payment Gateway Name" placeholder="Payment Gateway Name" wire:model="name" required />
            <x-input label="Charge" placeholder="Charge" wire:model="charge" required />
            <x-checkbox label="Is Active" wire:model="is_active" />
            <x-slot:actions>
                <x-button label="Close" @click="$wire.createModal = false" class="btn-sm" />
                <x-button type="submit" label="Add Gateway" class="btn-primary btn-sm" spinner="storePaymentGateway" />
            </x-slot:actions>
        </x-form>
    </x-modal>
    <x-modal wire:model="editModal" title="Update Gateway - {{ $paymentGateway->name ?? '' }}" size="text-xl" separator>
        <x-form wire:submit="updatePaymentGateway">
            <x-input label="Payment Gateway Name" placeholder="Payment Gateway Name" wire:model="name" required />
            <x-input label="Charge" placeholder="Charge" wire:model="charge" required />
            <x-checkbox label="Is Active" wire:model="is_active" />
            <x-slot:actions>
                <x-button label="Close" @click="$wire.editModal = false" class="btn-sm" />
                <x-button type="submit" label="Update Gateway" spinner="updatePaymentGateway"
                    class="btn-primary btn-sm" />
            </x-slot:actions>
        </x-form>
    </x-modal>
</div>
