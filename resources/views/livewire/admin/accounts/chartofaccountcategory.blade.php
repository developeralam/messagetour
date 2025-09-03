<?php

use App\Enum\AccountType;
use App\Models\ChartOfAccount;
use App\Models\Country;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Rule;
use Livewire\Attributes\Title;
use Livewire\Volt\Component;
use Livewire\WithPagination;
use Mary\Traits\Toast;

new #[Layout('components.layouts.admin')] #[Title('Chart Of Account Category')] class extends Component {
    use Toast;
    use WithPagination;

    public string $search = '';

    public $category_id;

    public ChartOfAccount $account;

    public $types = [];

    public $header = [];

    #[Rule('required')]
    public $name;

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
        $this->header = $this->headers();
    }

    public function storeCategory()
    {
        $this->validate();
        try {
            ChartOfAccount::create([
                'name' => $this->name,
                'type' => $this->type,
            ]);
            $this->reset(['name', 'type']);
            $this->success('Chart Of Account category added successfully');
            $this->createModal = false;
        } catch (\Throwable $th) {
            $this->createModal = false;
            $this->error(env('APP_DEBUG', false) ? $th->getMessage() : 'Something went wrong');
        }
    }

    public function edit(ChartOfAccount $account)
    {
        $this->reset(['name', 'type']);
        $this->account = $account;
        $this->name = $account->name;
        $this->type = $account->type;
        $this->editModal = true;
    }

    public function updateCategory()
    {
        $this->validate();
        try {
            $this->account->update([
                'name' => $this->name,
                'type' => $this->type,
            ]);
            $this->reset(['name', 'type']);
            $this->success('Category Updated Successfully');
            $this->editModal = false;
        } catch (\Throwable $th) {
            $this->editModal = false;
            $this->error(env('APP_DEBUG', false) ? $th->getMessage() : 'Something went wrong');
        }
    }

    public function delete(ChartOfAccount $account)
    {
        try {
            $account->delete();
            $this->success('Category Deleted Successfully');
        } catch (\Throwable $th) {
            $this->error(env('APP_DEBUG', false) ? $th->getMessage() : 'Something went wrong');
        }
    }

    public function headers(): array
    {
        return [['key' => 'id', 'label' => '#'], ['key' => 'code', 'label' => 'Code', 'sortable' => false], ['key' => 'name', 'label' => 'Category'], ['key' => 'type', 'label' => 'Type'], ['key' => 'current_balance', 'label' => 'Current Balance', 'sortable' => false]];
    }

    public function accounts()
    {
        // List of codes to exclude from the query
        $excludeCodes = ['103', '104', '105', '106', '201', '202', '203', '205', '206', '207', '208', '301', '302', '303', '304', '305', '401', '501', '502', '503', '504'];

        // Optimized query with direct model usage
        return ChartOfAccount::whereNull('parent_id') // Filter out accounts with parent_id
            ->whereNotIn('code', $excludeCodes) // Filter out specific account codes
            ->paginate(20); // Paginate the results, fetching 20 per page
    }

    public function searchCountry($search = '')
    {
        $this->countries = Country::where('name', 'like', '%' . $search . '%')->get();
    }

    public function updated($property)
    {
        if (!is_array($property) && $property != '') {
            $this->resetPage();
        }
    }

    public function with(): array
    {
        return [
            'accounts' => $this->accounts(),
        ];
    }

    public function print($category_id)
    {
        $this->category_id = $category_id;
        $this->isPrintShow = true;
    }

    public function redirectToPrint()
    {
        return redirect()->route('admin.ledger-category-print-report', [
            'category_id' => $this->category_id,
            'from_date' => $this->from_date,
            'to_date' => $this->to_date,
        ]);
    }
}; ?>


<div>
    <x-header title="Chart Of Account Category List" size="text-xl" separator class="bg-white px-2 pt-3">
        <x-slot:actions>
            <x-button icon="fas.plus" @click="$wire.createModal = true" label="Add Category" class="btn-primary btn-sm" />
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
                    @if (!in_array($account['code'], ['100', '101']))
                        <x-button icon="o-trash" wire:click="delete({{ $account['id'] }})" wire:confirm="Are you sure?"
                            class="btn-error btn-action text-white" />
                        <x-button icon="s-pencil-square" wire:click="edit({{ $account['id'] }})"
                            class="btn-neutral btn-action text-white" />
                    @endif
                </div>
            @endscope
        </x-table>
    </x-card>
    <x-modal wire:model="createModal" title="Add New Category" separator>
        <x-form wire:submit="storeCategory">
            <x-choices label="Type" wire:model="type" :options="$types" single required placeholder="Select Type" />
            <x-input label="Category Name" wire:model="name" placeholder="Category Name" required />
            <x-slot:actions>
                <x-button label="Cancel" class="btn-sm" @click="$wire.createModal = false" />
                <x-button type="submit" label="Add Category" class="btn-sm btn-primary" spinner="storeCategory" />
            </x-slot>
        </x-form>
    </x-modal>
    <x-modal wire:model="editModal" title="Update Category {{ $account->name ?? '' }}" separator>
        <x-form wire:submit="updateCategory">
            <x-choices label="Type" wire:model="type" :options="$types" single required placeholder="Select Type" />
            <x-input label="Category Name" wire:model="name" placeholder="Category Name" required />
            <x-slot:actions>
                <x-button label="Cancel" class="btn-sm" @click="$wire.editModal = false" />
                <x-button type="submit" label="Save" class="btn-sm btn-primary" spinner="updateCategory" />
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
