<?php

use App\Enum\AccountType;
use App\Models\ChartOfAccount;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Rule;
use Livewire\Attributes\Title;
use Livewire\Volt\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use Mary\Traits\Toast;

new #[Layout('components.layouts.admin')] #[Title('Chart Of Accounts')] class extends Component {
    use Toast;
    use WithFileUploads;
    use WithPagination;

    public string $search = '';

    public $category_id;

    public ChartOfAccount $account;

    public $types = [];

    public $categories = [];

    public $header = [];

    #[Rule('required')]
    public $name;

    #[Rule('required')]
    public $parent_id;

    #[Rule('required')]
    public $type;

    public $from_date;

    public $to_date;

    public bool $createModal = false;

    public bool $editModal = false;

    public bool $isPrintShow = false;

    public function mount()
    {
        $this->types = AccountType::getTypes();
        $this->getCategories();
        $this->header = $this->headers();
    }

    public function updated($property)
    {
        if ($property == 'type') {
            $this->getCategories();
        }
    }

    public function storeChartOfAccount()
    {
        $this->validate();
        try {
            ChartOfAccount::create([
                'name' => $this->name,
                'parent_id' => $this->parent_id,
                'type' => $this->type,
            ]);
            $this->reset(['name', 'type', 'parent_id']);
            $this->success('Chart Of Account Added Successfully');
            $this->createModal = false;
        } catch (\Throwable $th) {
            $this->createModal = false;
            $this->error(env('APP_DEBUG', false) ? $th->getMessage() : 'Something went wrong');
        }
    }

    public function edit(ChartOfAccount $account)
    {
        $this->reset(['name', 'type', 'parent_id']);
        $this->account = $account;
        $this->name = $account->name;
        $this->parent_id = $account->parent_id;
        $this->type = $account->type;
        $this->editModal = true;
    }

    public function updateChartOfAccount()
    {
        $this->validate();
        try {
            $this->account->update([
                'name' => $this->name,
                'parent_id' => $this->parent_id,
                'type' => $this->type,
            ]);
            $this->reset(['name', 'type', 'parent_id']);
            $this->success('Chart Of Account Updated Successfully');
            $this->editModal = false;
        } catch (\Throwable $th) {
            $this->error(env('APP_DEBUG', false) ? $th->getMessage() : 'Something went wrong');
        }
    }

    public function delete(ChartOfAccount $account)
    {
        try {
            $account->delete();
            $this->success('Chart Of Account Deleted Successfully');
        } catch (\Throwable $th) {
            $this->error(env('APP_DEBUG', false) ? $th->getMessage() : 'Something went wrong');
        }
    }

    public function headers(): array
    {
        return [['key' => 'id', 'label' => '#'], ['key' => 'code', 'label' => 'Code', 'sortable' => false], ['key' => 'parent.name', 'label' => 'Category', 'sortable' => false], ['key' => 'name', 'label' => 'Name'], ['key' => 'type', 'label' => 'Type'], ['key' => 'current_balance', 'label' => 'Current Balance', 'sortable' => false]];
    }

    public function accounts()
    {
        return ChartOfAccount::query()->whereNotNull('parent_id')->paginate(20);
    }

    #[Computed]
    public function getCategories()
    {
        // List of codes to exclude from the query
        $excludeCodes = ['103', '104', '105', '106', '201', '202', '203', '205', '301', '401', '501', '502', '503', '504'];

        // Optimized query with direct model usage
        $this->categories = ChartOfAccount::whereNull('parent_id') // Filter out accounts with parent_id
            ->when($this->type, fn($q) => $q->where('type', $this->type))
            ->whereNotIn('code', $excludeCodes) // Filter out specific account codes
            ->get(); // Paginate the results, fetching 20 per page
    }

    public function print($category_id)
    {
        $this->category_id = $category_id;
        $this->isPrintShow = true;
    }

    public function redirectToPrint()
    {
        return redirect()->route('admin.ledger-print-report', [
            'category_id' => $this->category_id,
            'from_date' => $this->from_date,
            'to_date' => $this->to_date,
        ]);
    }

    public function with(): array
    {
        return [
            'accounts' => $this->accounts(),
        ];
    }
}; ?>


<div>
    <x-header title="Chart Of Account List" size="text-xl" separator class="bg-white px-2 pt-3">
        <x-slot:actions>
            <x-button icon="fas.plus" @click="$wire.createModal = true" label="Add Chart Of Account"
                class="btn-primary btn-sm" />
        </x-slot>
    </x-header>
    <x-card>
        <x-table :headers="$header" :rows="$accounts" with-pagination>
            @scope('cell_id', $account, $accounts)
                {{ $loop->iteration + ($accounts->currentPage() - 1) * $accounts->perPage() }}
            @endscope

            @scope('actions', $account)
                <div class="flex items-center gap-1">
                    <x-button icon="fas.print" wire:click="print({{ $account['id'] }})"
                        class="btn-primary btn-action text-white" />
                    <x-button icon="o-trash" wire:click="delete({{ $account['id'] }})" wire:confirm="Are you sure?"
                        class="btn-primary btn-action text-white" />
                    <x-button icon="s-pencil-square" wire:click="edit({{ $account['id'] }})"
                        class="btn-primary btn-action text-white" />
                </div>
            @endscope
        </x-table>
    </x-card>
    <x-modal wire:model="createModal" title="Add New Chart Of Account" separator>
        <x-form wire:submit="storeChartOfAccount">
            <x-select label="Type" wire:model.live="type" :options="$types" required placeholder="Select Type" />
            <x-select label="Category" wire:model="parent_id" :options="$categories" required
                placeholder="Select Category" />
            <x-input label="Account Name" wire:model="name" placeholder="Category Name" required />
            <x-slot:actions>
                <x-button label="Cancel" class="btn-sm" @click="$wire.createModal = false" />
                <x-button type="submit" label="Add Account" class="btn-sm btn-primary" spinner="storeChartOfAccount" />
            </x-slot>
        </x-form>
    </x-modal>
    <x-modal wire:model="editModal" title="Update {{ $account->name ?? '' }} Account" separator>
        <x-form wire:submit="updateChartOfAccount">
            <x-select label="Type" wire:model.live="type" :options="$types" required placeholder="Select Type" />
            <x-select label="Category" wire:model="parent_id" :options="$categories" required
                placeholder="Select Category" />
            <x-input label="Account Name" wire:model="name" placeholder="Category Name" required />
            <x-slot:actions>
                <x-button label="Cancel" class="btn-sm" @click="$wire.editModal = false" />
                <x-button type="submit" label="Add Account" class="btn-sm btn-primary"
                    spinner="updateChartOfAccount" />
            </x-slot>
        </x-form>
    </x-modal>
    <x-modal wire:model="isPrintShow" title="Print Report" separator>
        <x-form wire:submit="redirectToPrint">
            <x-datetime label="From Date" wire:model="from_date" required />
            <x-datetime label="To Date" wire:model="to_date" required />
            <x-slot:actions>
                <x-button label="Cancel" class="btn-sm" @click="$wire.isPrintShow = false" />
                <x-button type="submit" label="Print" class="btn-sm btn-primary" spinner="redirectToPrint" />
            </x-slot>
        </x-form>
    </x-modal>
</div>
