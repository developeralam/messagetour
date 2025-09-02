<?php

use Mary\Traits\Toast;
use App\Models\Deposit;
use App\Models\Bank;
use App\Enum\DepositType;
use App\Enum\DepositStatus;
use Livewire\Volt\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Rule;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;
use Illuminate\Database\Eloquent\Builder;

new #[Layout('components.layouts.partner')] #[Title('Deposit Request')] class extends Component {
    use WithPagination, Toast;

    #[Rule('required')]
    public $amount;

    #[Rule('nullable')]
    public $trx_id;

    #[Rule('required')]
    public $payment_type;

    #[Rule('nullable')]
    public $deposit_form;

    #[Rule('nullable')]
    public $deposit_to;

    #[Rule('nullable')]
    public $branch;

    #[Rule('nullable')]
    public $payment_slip;

    #[Rule('required')]
    public $deposit_date;

    public $depositTypes = [];
    public $depositToBanks = [];
    public array $headers;
    public bool $createModal = false;
    public bool $editModal = false;
    public Deposit $deposit;
    public string $search = '';

    public function mount(): void
    {
        $this->depositTypes = DepositType::getDepositTypes();
        $this->depositToBanks = Bank::select(['id', 'name'])->get();
        $this->headers = $this->headers();
    }

    public function headers(): array
    {
        return [['key' => 'id', 'label' => '#'], ['key' => 'deposit_date', 'label' => 'Deposit Date'], ['key' => 'payment_type', 'label' => 'Payment Type'], ['key' => 'deposit_form', 'label' => 'Deposit Form'], ['key' => 'deposit_to', 'label' => 'Deposit To'], ['key' => 'trx_id', 'label' => 'Transaction ID'], ['key' => 'amount', 'label' => 'Amount'], ['key' => 'status', 'label' => 'Status']];
    }

    public function createDeposit(): void
    {
        $this->validate([
            'deposit_form' => $this->payment_type == DepositType::Bank->value ? 'required' : 'nullable',
            'deposit_to' => $this->payment_type == DepositType::Bank->value ? 'required' : 'nullable',

            'trx_id' => match ($this->payment_type) {
                DepositType::Bkash->value, DepositType::Rocket->value => 'required',
                default => 'nullable',
            },
        ]);
        try {
            Deposit::create([
                'agent_id' => auth()->user()->agent->id,
                'amount' => $this->amount,
                'trx_id' => $this->trx_id,
                'payment_type' => $this->payment_type,
                'deposit_form' => $this->deposit_form,
                'deposit_to' => $this->deposit_to,
                'branch' => $this->branch,
                'payment_slip' => $this->payment_slip,
                'deposit_date' => $this->deposit_date,
                'status' => DepositStatus::Pending,
            ]);
            $this->createModal = false;
            $this->success('Deposit Request Created Successfully');
            $this->reset('amount', 'trx_id', 'payment_type', 'payment_slip', 'deposit_date', 'deposit_form', 'deposit_to', 'branch');
        } catch (\Throwable $th) {
            $this->reset('amount', 'trx_id', 'payment_type', 'payment_slip', 'deposit_date', 'deposit_form', 'deposit_to', 'branch');
            $this->createModal = false;
            $this->error(env('APP_DEBUG', false) ? $th->getMessage() : 'Something went wrong');
        }
    }

    public function edit(Deposit $deposit): void
    {
        $this->deposit = $deposit;
        $this->amount = $deposit->amount;
        $this->trx_id = $deposit->trx_id;
        $this->payment_type = $deposit->payment_type;
        $this->deposit_form = $deposit->deposit_form;
        $this->deposit_to = $deposit->deposit_to;
        $this->branch = $deposit->branch;
        $this->payment_slip = $deposit->payment_slip;
        $this->deposit_date = $deposit->deposit_date->format('Y-m-d');
        $this->editModal = true;
    }

    public function updateDeposit(): void
    {
        $this->validate([
            'deposit_form' => $this->payment_type == DepositType::Bank->value ? 'required' : 'nullable',
            'deposit_to' => $this->payment_type == DepositType::Bank->value ? 'required' : 'nullable',

            'trx_id' => match ($this->payment_type) {
                DepositType::Bkash->value, DepositType::Rocket->value => 'required',
                default => 'nullable',
            },
        ]);
        try {
            $this->deposit->update([
                'amount' => $this->amount,
                'trx_id' => $this->trx_id,
                'payment_type' => $this->payment_type,
                'deposit_form' => $this->deposit_form,
                'deposit_to' => $this->deposit_to,
                'branch' => $this->branch,
                'payment_slip' => $this->payment_slip,
                'deposit_date' => $this->deposit_date,
            ]);
            $this->success('Deposit Request Updated Successfully');
            $this->editModal = false;
            $this->reset('amount', 'trx_id', 'payment_type', 'payment_slip', 'deposit_date', 'deposit_form', 'deposit_to', 'branch');
        } catch (\Throwable $th) {
            $this->reset('amount', 'trx_id', 'payment_type', 'payment_slip', 'deposit_date', 'deposit_form', 'deposit_to', 'branch');
            $this->editModal = false;
            $this->error(env('APP_DEBUG', false) ? $th->getMessage() : 'Something went wrong');
        }
    }

    public function delete(Deposit $deposit): void
    {
        try {
            $deposit->update([
                'action_by' => auth()->user()->id,
            ]);
            $deposit->delete();
            $this->success('Deposit Request Deleted Successfully');
        } catch (\Throwable $th) {
            $this->error(env('APP_DEBUG', false) ? $th->getMessage() : 'Something went wrong');
        }
    }

    public function updated($property): void
    {
        if (!is_array($property) && $property != '') {
            $this->resetPage();
        }
    }

    public function deposits()
    {
        return Deposit::query()
            ->where('agent_id', auth()->user()->agent->id)
            ->when($this->search, function (Builder $query) {
                $query->where('trx_id', 'like', '%' . $this->search . '%');
            })
            ->latest()
            ->paginate(10);
    }

    public function with(): array
    {
        return [
            'deposits' => $this->deposits(),
        ];
    }
}; ?>

<div>
    <x-header title="Deposit Request List" separator size="text-xl" class="bg-white px-2 pt-2">
        <x-slot:middle class="!justify-end">
            <x-input icon="o-bolt" wire:model.live="search" placeholder="Search..." />
        </x-slot:middle>
        <x-slot:actions>
            <x-button icon="o-plus" @click="$wire.createModal = true" label="Add Request" class="btn-primary btn-sm" />
        </x-slot:actions>
    </x-header>
    <x-card>
        <x-table :headers="$headers" :rows="$deposits" with-pagination>
            @scope('cell_id', $deposit, $deposits)
                {{ $loop->iteration + ($deposits->currentPage() - 1) * $deposits->perPage() }}
            @endscope
            @scope('cell_payment_type', $deposit)
                <x-badge value="{{ $deposit->payment_type->label() }}" class="bg-primary text-white p-3 text-xs" />
            @endscope
            @scope('cell_deposit_date', $deposit)
                {{ $deposit->deposit_date->format('d M Y') }}
            @endscope
            @scope('cell_deposit_form', $deposit)
                {{ $deposit->deposit_form ?? 'N/A' }}
            @endscope
            @scope('cell_trx_id', $deposit)
                {{ $deposit->trx_id ?? 'N/A' }}
            @endscope
            @scope('cell_deposit_to', $deposit)
                {{ $deposit->depositTo->name ?? 'N/A' }}
            @endscope
            @scope('cell_amount', $deposit)
                BDT {{ number_format($deposit->amount) }}
            @endscope
            @scope('cell_status', $deposit)
                @if ($deposit->status == \App\Enum\DepositStatus::Approved)
                    <x-badge value="{{ $deposit->status->label() }}"
                        class="bg-green-100 text-green-700 p-3 text-xs font-semibold" />
                @elseif ($deposit->status == \App\Enum\DepositStatus::Pending)
                    <x-badge value="{{ $deposit->status->label() }}"
                        class="bg-yellow-100 text-yellow-700 p-3 text-xs font-semibold" />
                @elseif ($deposit->status == \App\Enum\DepositStatus::Declined)
                    <x-badge value="{{ $deposit->status->label() }}"
                        class="bg-red-100 text-red-700 p-3 text-xs font-semibold" />
                @endif
            @endscope
            @scope('actions', $deposit)
                <div class="flex items-center gap-1">
                    <x-button icon="o-trash" wire:click="delete({{ $deposit['id'] }})" wire:confirm="Are you sure?"
                        class="btn-error btn-action" />
                    <x-button icon="s-pencil-square" wire:click="edit({{ $deposit['id'] }})"
                        class="btn-neutral btn-action" />
                </div>
            @endscope
        </x-table>
    </x-card>
    <x-modal wire:model="createModal" title="Add New Deposit Request" separator boxClass="max-w-5xl">
        <x-form wire:submit="createDeposit">
            <div class="grid grid-cols-3 gap-2">
                <x-choices label="Payment Type" wire:model.live="payment_type" :options="$depositTypes" single
                    placeholder="Select Payment Type" required />
                @if ($payment_type == 1)
                    <x-input label="Deposit Form" wire:model="deposit_form" placeholder="Deposit Form" required />
                    <x-choices label="Deposit To" wire:model="deposit_to" placeholder="Select Deposit To"
                        :options="$depositToBanks" required single />
                    <x-input label="Branch Name" wire:model="branch" placeholder="Branch Name" />
                @endif
                <x-input label="Transaction ID" wire:model="trx_id" placeholder="Transaction ID" />
                <x-input label="Amount" type="number" wire:model="amount" placeholder="Enter amount" required />
                <x-datetime label="Deposit Date" wire:model="deposit_date" required />
                <x-file label="Payment Slip" wire:model="payment_slip" />
            </div>
            <x-slot:actions>
                <x-button label="Cancel" @click="$wire.createModal = false" class="btn-sm" />
                <x-button type="submit" label="Add Request" class="btn-primary btn-sm" spinner="createDeposit" />
            </x-slot:actions>
        </x-form>
    </x-modal>
    <x-modal wire:model="editModal" title="Update Deposit Request" separator boxClass="max-w-5xl">
        <x-form wire:submit="updateDeposit">
            <div class="grid grid-cols-3 gap-2">
                <x-choices label="Payment Type" wire:model.live="payment_type" :options="$depositTypes" single required />
                @if ($payment_type == \App\Enum\DepositType::Bank)
                    <x-input label="Deposit Form" wire:model="deposit_form" placeholder="Deposit Form" required />
                    <x-choices label="Deposit To" wire:model="deposit_to" placeholder="Select Deposit To"
                        :options="$depositToBanks" required single />
                    <x-input label="Branch Name" wire:model="branch" placeholder="Branch Name" />
                @endif
                <x-input label="Transaction ID" wire:model="trx_id" placeholder="Transaction ID" />
                <x-input label="Amount" type="number" wire:model="amount" placeholder="Enter amount" required />
                <x-datetime label="Deposit Date" wire:model="deposit_date" />
                <x-file label="Payment Slip" wire:model="payment_slip" />
            </div>

            <x-slot:actions>
                <x-button label="Cancel" @click="$wire.editModal = false" class="btn-sm" />
                <x-button type="submit" label="Save" class="btn-primary btn-sm" spinner="updateDeposit" />
            </x-slot:actions>
        </x-form>
    </x-modal>

</div>
