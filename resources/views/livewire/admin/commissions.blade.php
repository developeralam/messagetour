<?php

use App\Enum\CommisionRole;
use App\Enum\AmountType;
use App\Enum\CommissionStatus;
use App\Enum\ProductType;
use App\Models\Commission;
use Mary\Traits\Toast;
use Livewire\Volt\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Rule;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;

new #[Layout('components.layouts.admin')] #[Title('Commission List')] class extends Component {
    use WithPagination, Toast;

    public array $headers;
    public $statuses = [];
    public $amountTypes = [];
    public $productTypes = [];
    public $commissionRoles = [];
    public bool $createModal = false;
    public bool $editModal = false;
    public Commission $commission;

    #[Rule('required')]
    public $commission_role;

    #[Rule('required')]
    public $amount;

    #[Rule('required')]
    public $product_type;

    #[Rule('required')]
    public $amount_type;

    #[Rule('required')]
    public $status;

    /**
     * Mount method is executed once the component is initialized.
     * It sets up the initial values for the properties.
     */
    public function mount()
    {
        // Initialize the headers, amount types, product types, and commission statuses
        $this->headers = $this->headers();
        $this->amountTypes = AmountType::getTypes();
        $this->productTypes = ProductType::getTypes();
        $this->statuses = CommissionStatus::getCommissionStatuses();
        $this->commissionRoles = CommisionRole::getCommissionRoles();

        // Set default values for amount_type and status
        $this->status = CommissionStatus::Active;
        $this->amount_type = AmountType::Percent;
    }

    /**
     * Store the commission data after validating the inputs.
     * If successful, it resets the form and closes the modal.
     */
    public function storeCommission()
    {
        // Validate the input data
        $this->validate();

        try {
            // Create a new commission record in the database
            Commission::create([
                'commission_role' => $this->commission_role,
                'amount' => $this->amount,
                'product_type' => $this->product_type,
                'amount_type' => $this->amount_type,
                'status' => $this->status,
            ]);

            // Show success message and reset form fields
            $this->success('Commission Added Successfully');
            $this->reset(['commission_role', 'amount', 'product_type', 'amount_type', 'status']);
            $this->createModal = false;
        } catch (\Throwable $th) {
            // Show error message if something goes wrong
            $this->createModal = false;
            $this->error(env('APP_DEBUG') ? $th->getMessage() : 'Something went wrong.');
        }
    }

    /**
     * Pre-fill the form with existing commission data for editing.
     *
     * @param Commission $commission
     */
    public function edit(Commission $commission)
    {
        // Reset previous data and fill the form with the commission data
        $this->reset(['commission_role', 'amount', 'product_type', 'amount_type', 'status']);
        $this->commission = $commission;
        $this->commission_role = $commission->commission_role ?? '';
        $this->amount = $commission->amount ?? '';
        $this->product_type = $commission->product_type ?? '';
        $this->amount_type = $commission->amount_type ?? '';
        $this->status = $commission->status ?? '';
        $this->editModal = true;
    }

    /**
     * Update the commission details in the database.
     * If successful, it resets the form and closes the modal.
     */
    public function updateCommission()
    {
        try {
            // Update the commission record with the new values
            $this->commission->update([
                'commission_role' => $this->commission_role,
                'amount' => $this->amount,
                'product_type' => $this->product_type,
                'amount_type' => $this->amount_type,
                'status' => $this->status,
                'action_by' => auth()->user()->id,
            ]);

            // Show success message and reset form fields
            $this->success('Commission Updated Successfully');
            $this->reset(['commission_role', 'amount', 'product_type', 'amount_type', 'status']);
            $this->editModal = false;
        } catch (\Throwable $th) {
            // Show error message if something goes wrong
            $this->editModal = false;
            $this->error(env('APP_DEBUG') ? $th->getMessage() : 'Something went wrong.');
        }
    }

    /**
     * Delete a commission record from the database.
     * If successful, it displays a success message.
     *
     * @param Commission $commission
     */
    public function delete(Commission $commission)
    {
        try {
            // Set action_by to the logged-in user and delete the commission
            $commission->update([
                'action_by' => auth()->user()->id,
            ]);
            $commission->delete();

            // Show success message after deletion
            $this->success('Commission Deleted Successfully');
        } catch (\Throwable $th) {
            // Show error message if something goes wrong
            $this->error($th->getMessage());
        }
    }

    /**
     * Define the headers for the commission table.
     * These headers are displayed on the frontend table.
     *
     * @return array
     */
    public function headers(): array
    {
        return [['key' => 'id', 'label' => '#'], ['key' => 'commission_role', 'label' => 'Commission Role'], ['key' => 'product_type', 'label' => 'Product Type'], ['key' => 'amount_type', 'label' => 'Amount Type'], ['key' => 'amount', 'label' => 'Amount'], ['key' => 'status', 'label' => 'Status'], ['key' => 'action_by', 'label' => 'Last Action By']];
    }

    /**
     * Fetch the list of commissions from the database, sorted by the latest.
     *
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function commissions()
    {
        return Commission::query()->with('actionby')->latest()->paginate(20);
    }

    /**
     * Return data that should be passed to the frontend when the component is rendered.
     * This data is available to the Blade view.
     *
     * @return array
     */
    public function with(): array
    {
        return [
            'commissions' => $this->commissions(),
        ];
    }
}; ?>

<div>
    <x-header title="Commission List" size="text-xl" separator class="bg-white px-2 pt-2">
        <x-slot:actions>
            <x-button label="Add Commission" icon="o-plus" @click="$wire.createModal = true" class="btn-primary btn-sm" />
        </x-slot:actions>
    </x-header>
    <x-card>
        <x-table :headers="$headers" :rows="$commissions" with-pagination>
            @scope('cell_id', $commission, $commissions)
                {{ $loop->iteration + ($commissions->currentPage() - 1) * $commissions->perPage() }}
            @endscope
            @scope('cell_commission_role', $commission)
                <x-badge value="{{ $commission->commission_role->label() }}" class="bg-primary text-white p-3 text-xs" />
            @endscope
            @scope('cell_product_type', $commission)
                {{ $commission->product_type->label() }}
            @endscope
            @scope('cell_amount_type', $commission)
                {{ $commission->amount_type->label() }}
            @endscope
            @scope('cell_status', $commission)
                @if ($commission->status == \App\Enum\CommissionStatus::Active)
                    <x-badge value="{{ $commission->status->label() }}"
                        class="bg-green-100 text-green-700 p-3 text-xs font-semibold" />
                @elseif ($commission->status == \App\Enum\CommissionStatus::Inactive)
                    <x-badge value="{{ $commission->status->label() }}"
                        class="bg-red-100 text-red-700 p-3 text-xs font-semibold" />
                @endif
            @endscope
            @scope('cell_action_by', $commission)
                {{ $commission->actionby->name ?? '' }}
            @endscope
            @scope('cell_amount', $commission)
                @if ($commission->amount_type == \App\Enum\AmountType::Fixed)
                    BDT {{ number_format($commission->amount) }}
                @elseif ($commission->amount_type == \App\Enum\AmountType::Percent)
                    {{ number_format($commission->amount) }}%
                @endif
            @endscope
            @scope('actions', $commission)
                <div class="flex items-center gap-1">
                    <x-button icon="o-trash" wire:click="delete({{ $commission['id'] }})" wire:confirm="Are you sure?"
                        class="btn-error btn-action" spinner="delete({{ $commission['id'] }})" />
                    <x-button icon="s-pencil-square" wire:click="edit({{ $commission['id'] }})"
                        spinner="edit({{ $commission['id'] }})" class="btn-neutral btn-action" />
                </div>
            @endscope
        </x-table>
    </x-card>
    <x-modal wire:model="createModal" title="Add New Commission" size="text-xl" separator>
        <x-form wire:submit="storeCommission">
            <div class="grid grid-cols-1 gap-2">
                <x-choices label="Commission Role" wire:model="commission_role" placeholder="Select Role"
                    :options="$commissionRoles" single required />
                <x-choices label="Product Type" wire:model="product_type" placeholder="Select Product Type"
                    :options="$productTypes" single required />
                <x-choices label="Amount Type" wire:model="amount_type" :options="$amountTypes" single required />
                <x-input type="number" label="Amount" placeholder="Amount" wire:model="amount" required />
                <x-radio label="Commission Status" :options="$statuses" wire:model="status" required />
            </div>
            <x-slot:actions>
                <x-button label="Close" @click="$wire.createModal = false" class="btn-sm" />
                <x-button type="submit" label="Add Commission" class="btn-primary btn-sm" spinner="storeCommission" />
            </x-slot:actions>
        </x-form>
    </x-modal>
    <x-modal wire:model="editModal" title="Update Commission" size="text-xl" separator>
        <x-form wire:submit="updateCommission">
            <div class="grid grid-cols-1 gap-2">
                <x-choices label="Commission Role" wire:model="commission_role" placeholder="Select Role"
                    :options="$commissionRoles" single required />
                <x-choices label="Product Type" wire:model="product_type" placeholder="Select Product Type"
                    :options="$productTypes" single required />
                <x-choices label="Amount Type" wire:model="amount_type" :options="$amountTypes" single required />
                <x-input type="number" label="Amount" placeholder="Amount" wire:model="amount" required />
                <x-radio label="Commission Status" :options="$statuses" wire:model="status" required />
            </div>
            <x-slot:actions>
                <x-button label="Close" @click="$wire.editModal = false" class="btn-sm" />
                <x-button type="submit" label="Update Commission" class="btn-primary btn-sm"
                    spinner="updateCommission" />
            </x-slot:actions>
        </x-form>
    </x-modal>
</div>
