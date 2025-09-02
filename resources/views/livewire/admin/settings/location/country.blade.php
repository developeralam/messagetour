<?php

use Mary\Traits\Toast;
use App\Models\Country;
use Livewire\Volt\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Rule;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;
use Illuminate\Database\Eloquent\Builder;

new #[Layout('components.layouts.admin')] #[Title('Country')] class extends Component {
    use WithPagination, Toast;

    public string $search = '';
    public Country $country;

    #[Rule('required')]
    public string $name = '';

    public array $sortBy = ['column' => 'id', 'direction' => 'desc'];

    public bool $createModal = false;
    public bool $editModal = false;

    public function storeCountry()
    {
        $this->validate();
        try {
            Country::create([
                'name' => $this->name,
            ]);
            $this->createModal = false;
            $this->success('Country Added Successfully');
            $this->reset();
        } catch (\Throwable $th) {
            $this->createModal = false;
            $this->error($th->getMessage());
        }
    }
    public function edit(Country $country)
    {
        $this->country = $country;
        $this->name = $country->name;
        $this->editModal = true;
    }
    public function updateCountry()
    {
        $this->validate();
        try {
            $this->country->update([
                'name' => $this->name,
            ]);
            $this->success('Country Updated Successfully');
            $this->editModal = false;
            $this->reset();
        } catch (\Throwable $th) {
            $this->error($th->getMessage());
        }
    }
    public function delete(Country $country)
    {
        try {
            $country->delete();
            $this->success('Country Deleted Successfully');
        } catch (\Throwable $th) {
            $this->error($th->getMessage());
        }
    }
    public function headers(): array
    {
        return [['key' => 'id', 'label' => '#'], ['key' => 'name', 'label' => 'Country Name'], ['key' => 'status', 'label' => 'Country Status']];
    }
    public function countries()
    {
        return Country::query()->when($this->search, fn(Builder $q) => $q->where('name', 'LIKE', "%$this->search%"))->orderBy(...array_values($this->sortBy))->paginate(10);
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
            'countries' => $this->countries(),
        ];
    }
}; ?>

<div>
    <x-header title="Country" size="text-xl" separator class="bg-white px-2 pt-2">
        <x-slot:middle class="!justify-end">
            <x-input icon="o-bolt" wire:model.live="search" placeholder="Search..." />
        </x-slot:middle>
        <x-slot:actions>
            <x-button icon="o-plus" @click="$wire.createModal = true" label="Add Country" class="btn-primary btn-sm" />
        </x-slot:actions>
    </x-header>
    <x-card>
        <x-table :headers="$headers" :rows="$countries" :sort-by="$sortBy" with-pagination>
            @scope('cell_id', $country, $countries)
                {{ $loop->iteration + ($countries->currentPage() - 1) * $countries->perPage() }}
            @endscope
            @scope('cell_status', $country)
                @if ($country->status == \App\Enum\CountryStatus::Active)
                    <x-badge value="{{ $country->status->label() }}"
                        class="bg-green-100 text-green-700 p-3 text-xs font-semibold" />
                @elseif ($country->status == \App\Enum\CountryStatus::Inactive)
                    <x-badge value="{{ $country->status->label() }}"
                        class="bg-red-100 text-red-700 p-3 text-xs font-semibold" />
                @endif
            @endscope
            @scope('actions', $country)
                <div class="flex items-center gap-1">
                    <x-button icon="o-trash" wire:click="delete({{ $country['id'] }})" wire:confirm="Are you sure?"
                        class="btn-error btn-action" />
                    <x-button icon="s-pencil-square" wire:click="edit({{ $country['id'] }})"
                        class="btn-neutral btn-action" />
                </div>
            @endscope
        </x-table>
    </x-card>
    <x-modal wire:model="createModal" title="Add New Country" separator>
        <x-form wire:submit="storeCountry">
            <x-input label="Country Name" wire:model="name" placeholder="Country Name" required />
            <x-slot:actions>
                <x-button label="Cancel" @click="$wire.createModal = false" class="btn-sm" />
                <x-button type="submit" label="Add Country" class="btn-primary btn-sm" />
            </x-slot:actions>
        </x-form>
    </x-modal>
    <x-modal wire:model="editModal" title="Update {{ $country->name ?? '' }}" separator>
        <x-form wire:submit="updateCountry">
            <x-input label="Country Name" wire:model="name" placeholder="Country Name" required />
            <x-slot:actions>
                <x-button label="Cancel" @click="$wire.editModal = false" class="btn-sm" />
                <x-button type="submit" label="Update Country" class="btn-primary btn-sm" />
            </x-slot:actions>
        </x-form>
    </x-modal>
</div>
