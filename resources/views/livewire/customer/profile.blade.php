<?php

use App\Models\User;
use App\Models\Agent;
use App\Enum\UserType;
use Mary\Traits\Toast;
use App\Models\Country;
use App\Enum\UserStatus;
use App\Models\District;
use App\Models\Division;
use App\Enum\AgentStatus;
use App\Traits\InteractsWithImageUploads;
use Illuminate\Support\Str;
use Livewire\Volt\Component;
use Livewire\Attributes\Rule;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Features\SupportFileUploads\WithFileUploads;

new #[Layout('components.layouts.customer')] #[Title('Customer Profile')] class extends Component {
    use Toast, WithFileUploads, InteractsWithImageUploads;

    public Collection $countries;
    public Collection $divisions;
    public Collection $districts;
    public $customer = [];

    #[Rule('required')]
    public $customer_name;

    #[Rule('required')]
    public $customer_email;

    #[Rule('nullable')]
    public $password;

    #[Rule('nullable')]
    public $confirmation_password;

    #[Rule('nullable')]
    public $address;

    #[Rule('nullable')]
    public $secondary_address;

    #[Rule('nullable')]
    public $country_id;

    #[Rule('nullable')]
    public $division_id;

    #[Rule('nullable')]
    public $district_id;

    #[Rule('nullable')]
    public $image;

    public function mount()
    {
        $customerRes = auth()->user()->customer;
        if ($customerRes) {
            $this->customer = $customerRes;
            $this->customer_name = $customerRes->user->name;
            $this->customer_email = $customerRes->user->email;
            $this->address = $customerRes->address;
            $this->secondary_address = $customerRes->secondary_address;
            $this->country_id = $customerRes->country_id;
            $this->divisions();
            $this->division_id = $customerRes->division_id;
            $this->districts();
            $this->district_id = $customerRes->district_id;
            $this->countries = Country::all();
        }
    }
    public function divisions()
    {
        $this->divisions = Division::query()->when($this->country_id, fn(Builder $q) => $q->where('country_id', $this->country_id))->get();
    }
    public function districts()
    {
        $this->districts = District::query()->when($this->division_id, fn(Builder $q) => $q->where('division_id', $this->division_id))->get();
    }
    public function updated($property)
    {
        if ($property == 'country_id') {
            $this->divisions();
        }
        if ($property == 'division_id') {
            $this->districts();
        }
    }
    public function updateCustomer()
    {
        $this->validate();
        try {
            // Process and store updated images if provided, otherwise keep existing images
            $storedImagePath = $this->image ? $this->optimizeAndUpdateImage($this->image, $this->customer->image, 'public', 'customer', null, null, 75) : $this->customer->image;

            $this->customer->user->update([
                'name' => $this->customer_name,
                'email' => $this->customer_email,
                'password' => $this->password ? Hash::make($this->password) : $this->customer->user->password,
            ]);
            $this->customer->update([
                'address' => $this->address,
                'secondary_address' => $this->secondary_address,
                'country_id' => $this->country_id,
                'division_id' => $this->division_id,
                'district_id' => $this->district_id,
                'image' => $storedImagePath,
            ]);
            $this->success('Custome Updated Successfully');
        } catch (\Throwable $th) {
            $this->error($th->getMessage());
        }
    }
}; ?>

<div>
    <x-header title="Update Profile - {{ auth()->user()->name }}" size="text-xl" separator class="bg-white px-2 pt-2" />
    <x-form wire:submit="updateCustomer">
        <div class="grid grid-cols-3 gap-4 items-start">
            <div class="col-span-2 gap-4">
                <x-card>
                    <x-devider title="Profile Information" />
                    <div class="grid grid-cols-2 gap-2">
                        <x-input label="Customer Name" class="mb-2" wire:model="customer_name"
                            placeholder="Customer Name" />
                        <x-input label="Customer Email" class="mb-2" wire:model="customer_email"
                            placeholder="Customer Email" />
                        <x-input label="Password" class="mb-2" wire:model="password" placeholder="Password" />
                        <x-input label="Re-type Password" class="mb-2" wire:model="confirmation_password"
                            placeholder="Re-type Password" />
                        <x-file label="Image" wire:model="image" />
                    </div>
                </x-card>
            </div>
            <div class="col-span-1">
                <x-card x-cloak>
                    <x-devider title="Additional Information" />
                    <x-choices label="Country" :options="$countries" wire:model.live="country_id" single
                        placeholder="Select One" class="mb-4" />
                    <div class="grid grid-cols-2 gap-2">
                        <x-choices label="Division" :options="$divisions" wire:model.live="division_id" single
                            placeholder="Select One" class="mb-4" />
                        <x-choices label="District" :options="$districts" wire:model="district_id" single
                            placeholder="Select One" class="mb-4" />
                        <x-input label="Primary Address" class="mb-4" wire:model="address"
                            placeholder="Primary Address" />
                        <x-input label="Secondary Address" class="mb-4" wire:model="secondary_address"
                            placeholder="Secondary Address" />
                    </div>
                    <x-slot:actions>
                        <x-button type="submit" label="Update Profile" class="btn-primary btn-sm"
                            spinner="updateCustomer" />
                    </x-slot:actions>
                </x-card>
            </div>
        </div>

    </x-form>
</div>
