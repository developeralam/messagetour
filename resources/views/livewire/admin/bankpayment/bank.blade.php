<?php

use App\Models\Bank;
use Mary\Traits\Toast;
use App\Models\Country;
use App\Enum\BankStatus;
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

    #[Rule('required')]
    public $status;

    public bool $createModal = false;
    public bool $editModal = false;
    public $statuses = [];

    public function mount()
    {
        $this->countries = Country::select(['id', 'name'])->limit(10)->get();
        $this->statuses = BankStatus::getStatuses();
        $this->status = BankStatus::Active;
    }

    public function countrySearch(string $search = '')
    {
        $searchTerm = '%' . $search . '%';
        $this->countries = Country::where('name', 'like', $searchTerm)
            ->select(['id', 'name'])
            ->limit(10)
            ->get();
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
                'status' => $this->status,
                'action_by' => auth()->user()->id,
            ]);
            $this->reset(['name', 'ac_no', 'branch', 'address', 'swift_code', 'routing_no', 'country_id', 'status']);
            $this->success('Bank Added Successfully');
            $this->createModal = false;
        } catch (\Throwable $th) {
            dd($th->getMessage());
            $this->createModal = false;
            $this->error(env('APP_DEBUG', false) ? $th->getMessage() : 'Something went wrong');
        }
    }
    public function edit(Bank $bank)
    {
        $this->reset(['name', 'ac_no', 'branch', 'address', 'swift_code', 'routing_no', 'country_id', 'status']);
        $this->bank = $bank;
        $this->name = $bank->name;
        $this->ac_no = $bank->ac_no;
        $this->branch = $bank->branch ?? '';
        $this->address = $bank->address;
        $this->swift_code = $bank->swift_code;
        $this->routing_no = $bank->routing_no;
        $this->country_id = $bank->country_id;
        $this->status = $bank->status;
        $this->editModal = true;
    }
    public function updateBank()
    {
        try {
            $this->validate();
            $this->bank->update([
                'name' => $this->name,
                'ac_no' => $this->ac_no,
                'branch' => $this->branch,
                'address' => $this->address,
                'swift_code' => $this->swift_code,
                'routing_no' => $this->routing_no,
                'country_id' => $this->country_id,
                'status' => $this->status,
                'action_by' => auth()->user()->id,
            ]);
            $this->reset(['name', 'ac_no', 'branch', 'address', 'swift_code', 'routing_no', 'country_id', 'status']);
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

<div class="min-h-screen">
    <!-- Modern Glassy Header -->
    <div class="bg-white/80 backdrop-blur-xl border-b border-white/20 shadow-lg shadow-green-500/5 mb-4">
        <div class="px-6 py-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <div class="bg-gradient-to-r from-green-500 to-green-600 p-2.5 rounded-xl shadow-lg">
                        <x-fas-university class="w-5 h-5 text-white" />
                    </div>
                    <div>
                        <h1 class="text-xl font-bold text-gray-900 font-poppins">Bank List</h1>
                        <p class="text-xs sm:text-sm text-gray-800">Manage banking information and accounts</p>
                    </div>
                </div>
                <div class="flex items-center space-x-3">
                    <x-button label="Add Bank" icon="o-plus" @click="$wire.createModal = true"
                        class="bg-gradient-to-r from-green-600 to-green-700 hover:from-green-700 hover:to-green-800 text-white shadow-lg hover:shadow-xl transition-all duration-300 transform hover:-translate-y-0.5 w-full btn-sm sm:w-auto" />
                </div>
            </div>
        </div>
    </div>

    <!-- Modern Glassy Card -->
    <x-card class="modern-scrollbar">
        <x-table :headers="$headers" :rows="$banks" with-pagination>
            @scope('cell_id', $bank, $banks)
                {{ $loop->iteration + ($banks->currentPage() - 1) * $banks->perPage() }}
            @endscope
            @scope('cell_branch', $bank)
                {{ $bank->branch ?? 'N/A' }}
            @endscope
            @scope('cell_action_by', $bank)
                {{ $bank->actionBy->name ?? 'N/A' }}
            @endscope
            @scope('actions', $bank)
                <div class="flex items-center gap-2">
                    <x-button icon="o-trash" wire:click="delete({{ $bank['id'] }})" wire:confirm="Are you sure?"
                        class="bg-gradient-to-r from-red-500 to-red-600 hover:from-red-600 hover:to-red-700 text-white shadow-lg hover:shadow-xl transition-all duration-300 transform hover:-translate-y-0.5 btn-sm"
                        spinner="delete({{ $bank['id'] }})" />
                    <x-button icon="s-pencil-square" wire:click="edit({{ $bank['id'] }})"
                        class="bg-gradient-to-r from-yellow-500 to-yellow-600 hover:from-yellow-600 hover:to-yellow-700 text-white shadow-lg hover:shadow-xl transition-all duration-300 transform hover:-translate-y-0.5 btn-sm" />
                </div>
            @endscope
        </x-table>
    </x-card>
    <x-modal wire:model="createModal" title="Add New Bank" separator boxClass="max-w-4xl">
        <x-form wire:submit="storeBank">
            <div class="grid grid-cols-3 gap-4">
                <x-input label="Bank Name" wire:model="name" placeholder="Bank Name" required />
                <x-input label="Branch" wire:model="branch" placeholder="Branch" />
                <x-input label="Bank Address" wire:model="address" placeholder="Bank Address" required />
                <x-input label="Swift Code" wire:model="swift_code" placeholder="Swift Code" required />
                <x-input label="Routing No" wire:model="routing_no" placeholder="Routing No" required />
                <x-input label="A/C No" wire:model="ac_no" placeholder="A/C No" required />
            </div>
            <div class="grid grid-cols-3 gap-4">
                <x-choices label="Country" wire:model="country_id" single placeholder="Select Country" :options="$countries" searchable search-function="countrySearch" required />
                <x-choices label="Status" wire:model="status" single placeholder="Select Status" :options="$statuses" required />
            </div>
            <x-slot:actions>
                <x-button label="Cancel" @click="$wire.createModal = false"
                    class="bg-gray-500 hover:bg-gray-600 text-white shadow-lg hover:shadow-xl transition-all duration-300 transform hover:-translate-y-0.5 btn-sm" />
                <x-button type="submit" label="Add Bank" spinner="storeBank"
                    class="bg-gradient-to-r from-green-600 to-green-700 hover:from-green-700 hover:to-green-800 text-white shadow-lg hover:shadow-xl transition-all duration-300 transform hover:-translate-y-0.5 btn-sm" />
            </x-slot:actions>
        </x-form>
    </x-modal>
    <x-modal wire:model="editModal" title="Update {{ $bank->name ?? '' }}" separator boxClass="max-w-4xl">
        <x-form wire:submit="updateBank">
            <div class="grid grid-cols-3 gap-4">
                <x-input label="Bank Name" wire:model="name" placeholder="Bank Name" required />
                <x-input label="Branch" wire:model="branch" placeholder="Branch" />
                <x-input label="Bank Address" wire:model="address" placeholder="Bank Address" required />
                <x-input label="Swift Code" wire:model="swift_code" placeholder="Swift Code" required />
                <x-input label="Routing No" wire:model="routing_no" placeholder="Routing No" required />
                <x-input label="A/C No" wire:model="ac_no" placeholder="A/C No" required />
            </div>
            <div class="grid grid-cols-3 gap-4">
                <x-choices label="Country" wire:model="country_id" single placeholder="Select Country" :options="$countries" searchable search-function="countrySearch" required />
                <x-choices label="Status" wire:model="status" single placeholder="Select Status" :options="$statuses" required />
            </div>
            <x-slot:actions>
                <x-button label="Cancel" @click="$wire.editModal = false"
                    class="bg-gray-500 hover:bg-gray-600 text-white shadow-lg hover:shadow-xl transition-all duration-300 transform hover:-translate-y-0.5 btn-sm" />
                <x-button type="submit" label="Update Bank" spinner="updateBank"
                    class="bg-gradient-to-r from-green-600 to-green-700 hover:from-green-700 hover:to-green-800 text-white shadow-lg hover:shadow-xl transition-all duration-300 transform hover:-translate-y-0.5 btn-sm" />
            </x-slot:actions>
        </x-form>
    </x-modal>
</div>
