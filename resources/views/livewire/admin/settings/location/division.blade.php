<?php

use App\Models\Country;
use Mary\Traits\Toast;
use App\Models\Division;
use Livewire\Volt\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Rule;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Builder;

new #[Layout('components.layouts.admin')] #[Title('Division')] class extends Component {
    use WithPagination;
    use Toast;

    public string $search = '';
    public Division $division;
    public Collection $countries;
    public array $headers;

    #[Rule('required')]
    public $country_id = '';

    #[Rule('required')]
    public string $name = '';

    public array $sortBy = ['column' => 'id', 'direction' => 'desc'];
    public bool $createModal = false;
    public bool $editModal = false;

    public function mount()
    {
        $this->headers = $this->headers();
        $this->countries = Country::all();
    }
    public function storeDivision()
    {
        $this->validate();
        try {
            Division::create([
                'country_id' => $this->country_id,
                'name' => $this->name,
            ]);
            $this->createModal = false;
            $this->success('Division Added Successfully');
            $this->reset(['name', 'country_id']);
        } catch (\Throwable $th) {
            $this->createModal = false;
            $this->error($th->getMessage());
        }
    }
    public function edit(Division $division)
    {
        $this->division = $division;
        $this->country_id = $division->country_id;
        $this->name = $division->name;
        $this->editModal = true;
    }
    public function updateDivision()
    {
        $this->validate();
        try {
            $this->division->update([
                'country_id' => $this->country_id,
                'name' => $this->name,
            ]);
            $this->success('Division Updated Successfully');
            $this->editModal = false;
            $this->reset(['name', 'country_id']);
        } catch (\Throwable $th) {
            $this->error($th->getMessage());
        }
    }
    public function delete(Division $division)
    {
        try {
            $division->delete();
            $this->success('Division Deleted Successfully');
        } catch (\Throwable $th) {
            $this->error($th->getMessage());
        }
    }
    public function headers(): array
    {
        return [['key' => 'id', 'label' => '#'], ['key' => 'name', 'label' => 'Division Name'], ['key' => 'country.name', 'label' => 'Country Name'], ['key' => 'status', 'label' => 'District Status']];
    }
    public function divisions()
    {
        return Division::query()
            ->with('country')
            ->when($this->search, function (Builder $q) {
                $q->where('name', 'LIKE', "%$this->search%")->orWhereHas('country', function (Builder $query) {
                    $query->where('name', 'LIKE', "%$this->search%");
                });
            })
            ->orderBy(...array_values($this->sortBy))
            ->paginate(10);
    }

    public function countrySearch(string $search = '')
    {
        $searchTerm = '%' . $search . '%';

        $citizen = Country::where('name', 'like', $searchTerm)->get();

        $this->countries = $citizen;
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
            'divisions' => $this->divisions(),
        ];
    }
}; ?>

<div>
    <x-header title="Division List" size="text-xl" separator class="bg-white px-2 pt-2">
        <x-slot:actions class="!justify-end">
            <x-input icon="o-bolt" wire:model.live="search" placeholder="Search..." />
            <x-button icon="o-plus" @click="$wire.createModal = true" label="Add Division" class="btn-primary btn-sm" />
        </x-slot:actions>
    </x-header>
    <x-card>
        <x-table :headers="$headers" :rows="$divisions" :sort-by="$sortBy" with-pagination>
            @scope('cell_id', $division, $divisions)
                {{ $loop->iteration + ($divisions->currentPage() - 1) * $divisions->perPage() }}
            @endscope
            @scope('cell_status', $division)
                @if ($division->status == \App\Enum\DivisionStatus::Active)
                    <x-badge value="{{ $division->status->label() }}" class="bg-green-100 text-green-700 p-3 text-xs font-semibold" />
                @elseif ($division->status == \App\Enum\DivisionStatus::Inactive)
                    <x-badge value="{{ $division->status->label() }}" class="bg-red-100 text-red-700 p-3 text-xs font-semibold" />
                @endif
            @endscope
            @scope('actions', $division)
                <div class="flex items-center gap-1">
                    <x-button icon="o-trash" wire:click="delete({{ $division['id'] }})" wire:confirm="Are you sure?" class="btn-error btn-action"
                        spinner="delete({{ $division['id'] }})" />
                    <x-button icon="s-pencil-square" wire:click="edit({{ $division['id'] }})" class="btn-neutral btn-action" />
                </div>
            @endscope
        </x-table>
    </x-card>

    <x-modal wire:model="createModal" title="Add New Division" separator>
        <x-form wire:submit="storeDivision">
            <x-choices wire:model.live="country_id" :options="$countries" label="Country" placeholder="Select Country" single required
                search-function="countrySearch" searchable />
            <x-input label="Division Name" wire:model="name" placeholder="Division Name" required />
            <x-slot:actions>
                <x-button label="Cancel" @click="$wire.createModal = false" class="btn-sm" />
                <x-button type="submit" label="Add Division" class="btn-primary btn-sm" />
            </x-slot>
        </x-form>
    </x-modal>
    <x-modal wire:model="editModal" title="Update {{ $division->name ?? '' }}" separator>
        <x-form wire:submit="updateDivision">
            <x-choices wire:model.live="country_id" :options="$countries" label="Country" placeholder="Select Country" single required
                search-function="countrySearch" searchable />
            <x-input label="Division Name" wire:model="name" placeholder="Division Name" required />

            <x-slot:actions>
                <x-button label="Cancel" @click="$wire.editModal = false" class="btn-sm" />
                <x-button type="submit" label="Update Division" class="btn-primary btn-sm" />
            </x-slot>
        </x-form>
    </x-modal>
</div>
