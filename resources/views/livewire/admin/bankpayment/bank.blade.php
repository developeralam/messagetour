<?php

use App\Models\Bank;
use Mary\Traits\Toast;
use App\Models\Country;
use Livewire\Volt\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Rule;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;
use Illuminate\Database\Eloquent\Builder;

new #[Layout('components.layouts.admin')] #[Title('Bank')] class extends Component {
    use WithPagination, Toast;

    public string $search = '';
    public $countries = [];
    public Bank $bank;

    #[Rule('required')]
    public $name;

    #[Rule('required')]
    public $ac_no;

    #[Rule('nullable')]
    public $branch;

    #[Rule('required')]
    public $address;

    #[Rule('required')]
    public $swift_code;

    #[Rule('required')]
    public $routing_no;

    #[Rule('required')]
    public $country_id;

    public bool $createModal = false;
    public bool $editModal = false;

    public function mount()
    {
        $this->countries = Country::select(['id', 'name'])->get();
    }
    public function storeBank()
    {
        try {
            $this->validate();
            Bank::create([
                'name' => $this->name,
                'ac_no' => $this->ac_no,
                'branch' => $this->branch,
                'address' => $this->address,
                'swift_code' => $this->swift_code,
                'routing_no' => $this->routing_no,
                'country_id' => $this->country_id,
            ]);
            $this->reset(['name', 'ac_no', 'branch', 'address', 'swift_code', 'routing_no', 'country_id']);
            $this->success('Bank Added Successfully');
            $this->createModal = false;
        } catch (\Throwable $th) {
            $this->createModal = false;
            $this->error(env('APP_DEBUG', false) ? $th->getMessage() : 'Something went wrong');
        }
    }
    public function edit(Bank $bank)
    {
        $this->reset(['name', 'ac_no', 'branch', 'address', 'swift_code', 'routing_no', 'country_id']);
        $this->bank = $bank;
        $this->name = $bank->name;
        $this->ac_no = $bank->ac_no;
        $this->branch = $bank->branch ?? '';
        $this->address = $bank->address;
        $this->swift_code = $bank->swift_code;
        $this->routing_no = $bank->routing_no;
        $this->country_id = $bank->country_id;
        $this->editModal = true;
    }
    public function updateBank()
    {
        try {
            $this->validate();
            $this->bank->update([
                'name' => $this->name,
                'country_id' => $this->country_id,
                'action_by' => auth()->user()->id,
            ]);
            $this->reset(['name', 'ac_no', 'branch', 'address', 'swift_code', 'routing_no', 'country_id']);
            $this->success('Bank Updated Successfully');
            $this->editModal = false;
        } catch (\Throwable $th) {
            $this->error(env('APP_DEBUG', false) ? $th->getMessage() : 'Something went wrong');
        }
    }
    public function delete(Bank $bank)
    {
        try {
            $bank->update([
                'action_by' => auth()->user()->id,
            ]);
            $bank->delete();
            $this->success('Bank Deleted Successfully');
        } catch (\Throwable $th) {
            $this->error(env('APP_DEBUG', false) ? $th->getMessage() : 'Something went wrong');
        }
    }
    public function headers(): array
    {
        return [['key' => 'id', 'label' => '#'], ['key' => 'name', 'label' => 'Bank Name'], ['key' => 'branch', 'label' => 'Branch'], ['key' => 'address', 'label' => 'Bank Address'], ['key' => 'country.name', 'label' => 'Country'], ['key' => 'ac_no', 'label' => 'A/C No'], ['key' => 'swift_code', 'label' => 'Swift Code'], ['key' => 'routing_no', 'label' => 'Routing No'], ['key' => 'action_by', 'label' => 'Last Action By']];
    }
    public function banks()
    {
        return Bank::query()->with('country')->latest()->paginate(20);
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
            'headers' => $this->headers(),
            'banks' => $this->banks(),
        ];
    }
}; ?>

<div>
    <x-header title="Bank List" size="text-xl" separator class="bg-white px-2 pt-2">
        <x-slot:actions>
            <x-button icon="fas.plus" @click="$wire.createModal = true" label="Add Bank" class="btn-primary btn-sm" />
        </x-slot:actions>
    </x-header>
    <x-card>
        <x-table :headers="$headers" :rows="$banks" with-pagination>
            @scope('cell_id', $bank, $banks)
                {{ $loop->iteration + ($banks->currentPage() - 1) * $banks->perPage() }}
            @endscope
            @scope('cell_branch', $bank)
                {{ $bank->branch->name ?? 'N/A' }}
            @endscope
            @scope('cell_action_by', $bank)
                {{ $bank->actionBy->name ?? 'N/A' }}
            @endscope
            @scope('actions', $bank)
                <div class="flex items-center gap-1">
                    <x-button icon="o-trash" wire:click="delete({{ $bank['id'] }})" wire:confirm="Are you sure?" class="btn-error btn-action" />
                    <x-button icon="s-pencil-square" wire:click="edit({{ $bank['id'] }})" class="btn-neutral btn-action" />
                </div>
            @endscope
        </x-table>
    </x-card>
    <x-modal wire:model="createModal" title="Add New Bank" separator>
        <x-form wire:submit="storeBank">
            <x-input label="Bank Name" wire:model="name" placeholder="Bank Name" required />
            <x-choices label="Country" wire:model="country_id" single placeholder="Select Country" :options="$countries" required />
            <x-slot:actions>
                <x-button label="Cancel" @click="$wire.createModal = false" class="btn-sm" />
                <x-button type="submit" label="Add Bank" class="btn-primary btn-sm" spinner="storeBank" />
            </x-slot:actions>
        </x-form>
    </x-modal>
    <x-modal wire:model="editModal" title="Update {{ $bank->name ?? '' }}" separator>
        <x-form wire:submit="updateBank">
            <x-input label="Bank Name" wire:model="name" placeholder="Bank Name" required />
            <x-choices label="Country" wire:model="country_id" single placeholder="Select Country" :options="$countries" required />
            <x-slot:actions>
                <x-button label="Cancel" @click="$wire.editModal = false" class="btn-sm" />
                <x-button type="submit" label="Update Bank" class="btn-primary btn-sm" spinner="updateBank" />
            </x-slot:actions>
        </x-form>
    </x-modal>
</div>
