<?php

use Mary\Traits\Toast;
use App\Models\Country;
use App\Models\District;
use App\Models\Division;
use Livewire\Volt\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Rule;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Builder;

new #[Layout('components.layouts.admin')] #[Title('Districts')] class extends Component {
    use WithPagination, Toast;

    public string $search = '';
    public District $district;
    public Collection $countries;
    public Collection $divisions;
    public array $headers;

    #[Rule('required')]
    public $country_id = '';

    #[Rule('required')]
    public $division_id = '';

    #[Rule('required')]
    public string $name = '';

    public array $sortBy = ['column' => 'id', 'direction' => 'desc'];

    public bool $createModal = false;
    public bool $editModal = false;

    public function mount()
    {
        $this->headers = $this->headers();
        $this->countries = Country::all();
        $this->divisions = collect();
    }
    public function updated($property)
    {
        if ($property == 'country_id') {
            $this->divisions();
        }
    }
    public function storeDistrict()
    {
        $this->validate();
        try {
            District::create([
                'division_id' => $this->division_id,
                'name' => $this->name,
            ]);
            $this->createModal = false;
            $this->success('District Added Successfully');
            $this->reset(['name', 'country_id', 'division_id']);
        } catch (\Throwable $th) {
            $this->createModal = false;
            $this->error($th->getMessage());
        }
    }
    public function edit(District $district)
    {
        $this->district = $district;
        $this->country_id = $district->division->country_id;
        $this->divisions();
        $this->division_id = $district->division_id;
        $this->name = $district->name;
        $this->editModal = true;
    }
    public function updateDistrict()
    {
        $this->validate();
        try {
            $this->district->update([
                'division_id' => $this->division_id,
                'name' => $this->name,
            ]);
            $this->success('District Updated Successfully');
            $this->editModal = false;
            $this->reset(['name', 'country_id', 'division_id']);
        } catch (\Throwable $th) {
            $this->error($th->getMessage());
        }
    }
    public function delete(District $district)
    {
        try {
            $district->delete();
            $this->success('District Deleted Successfully');
        } catch (\Throwable $th) {
            $this->error($th->getMessage());
        }
    }
    public function headers(): array
    {
        return [['key' => 'id', 'label' => '#'], ['key' => 'name', 'label' => 'District Name'], ['key' => 'division.name', 'label' => 'Division Name'], ['key' => 'division.country.name', 'label' => 'Country Name'], ['key' => 'status', 'label' => 'District Status']];
    }
    public function divisions()
    {
        $this->divisions = Division::query()->when($this->country_id, fn(Builder $q) => $q->where('country_id', $this->country_id))->get();
    }
    public function districts()
    {
        return District::query()
            ->with(['division.country'])
            ->when($this->search, function (Builder $q) {
                $q->where('name', 'LIKE', "%$this->search%")
                    ->orWhereHas('division', function (Builder $query) {
                        $query->where('name', 'LIKE', "%$this->search%");
                    })
                    ->orWhereHas('division.country', function (Builder $query) {
                        $query->where('name', 'LIKE', "%$this->search%");
                    });
            })
            ->orderBy(...array_values($this->sortBy))
            ->paginate(10);
    }

    public function with(): array
    {
        return [
            'districts' => $this->districts(),
        ];
    }
}; ?>

<div>
    <x-header title="District List" size="text-xl" separator class="bg-white px-2 pt-2">
        <x-slot:middle class="!justify-end">
            <x-input icon="o-bolt" wire:model.live="search" placeholder="Search..." />
        </x-slot>
        <x-slot:actions>
            <x-button icon="o-plus" @click="$wire.createModal = true" label="Add District" class="btn-primary btn-sm" />
        </x-slot>
    </x-header>
    <x-card>
        <x-table :headers="$headers" :rows="$districts" :sort-by="$sortBy" with-pagination>
            @scope('cell_id', $district, $districts)
                {{ $loop->iteration + ($districts->currentPage() - 1) * $districts->perPage() }}
            @endscope
            @scope('cell_status', $district)
                @if ($district->status == \App\Enum\DistrictStatus::Active)
                    <x-badge value="{{ $district->status->label() }}"
                        class="bg-green-100 text-green-700 p-3 text-xs font-semibold" />
                @elseif ($district->status == \App\Enum\DistrictStatus::Inactive)
                    <x-badge value="{{ $district->status->label() }}"
                        class="bg-red-100 text-red-700 p-3 text-xs font-semibold" />
                @endif
            @endscope
            @scope('actions', $district)
                <div class="flex items-center gap-1">
                    <x-button icon="o-trash" wire:click="delete({{ $district['id'] }})" wire:confirm="Are you sure?"
                        class="btn-error btn-action" spinner="delete({{ $district['id'] }})" />
                    <x-button icon="s-pencil-square" wire:click="edit({{ $district['id'] }})"
                        class="btn-neutral btn-action" />
                </div>
            @endscope
        </x-table>
    </x-card>

    <x-modal wire:model="createModal" title="Add New District" separator>
        <x-form wire:submit="storeDistrict">
            <x-choices label="Country" :options="$countries" single wire:model.live="country_id"
                placeholder="Select Country" required />
            <x-choices label="Division" :options="$divisions" single wire:model="division_id" placeholder="Select Division"
                required />
            <x-input label="District Name" wire:model="name" placeholder="District Name" required />
            <x-slot:actions>
                <x-button label="Cancel" @click="$wire.createModal = false" class="btn-sm" />
                <x-button type="submit" label="Add District" class="btn-primary btn-sm" />
            </x-slot>
        </x-form>
    </x-modal>

    <x-modal wire:model="editModal" title="Update {{ $district->name ?? '' }}" separator>
        <x-form wire:submit="updateDistrict">
            <x-choices label="Country" :options="$countries" single wire:model.live="country_id"
                placeholder="Select Country" required />
            <x-choices label="Division" :options="$divisions" single wire:model="division_id" placeholder="Select Division"
                required />
            <x-input label="District Name" wire:model="name" placeholder="District Name" required />
            <x-slot:actions>
                <x-button label="Cancel" @click="$wire.editModal = false" class="btn-sm" />
                <x-button type="submit" label="Update District" class="btn-primary btn-sm" />
            </x-slot>
        </x-form>
    </x-modal>
</div>
