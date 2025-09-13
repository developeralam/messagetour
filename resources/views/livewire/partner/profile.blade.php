<?php

use App\Models\User;
use Mary\Traits\Toast;
use App\Models\Country;
use App\Models\District;
use App\Models\Division;
use App\Traits\InteractsWithImageUploads;
use Livewire\Volt\Component;
use Livewire\Attributes\Rule;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Features\SupportFileUploads\WithFileUploads;

new #[Layout('components.layouts.partner')] #[Title('Business Profile')] class extends Component {
    use Toast, WithFileUploads, InteractsWithImageUploads;

    public Collection $countries;
    public Collection $divisions;
    public Collection $districts;
    public $agent = [];

    #[Rule('required')]
    public $propiter_name;

    #[Rule('required')]
    public $propiter_email;

    #[Rule('nullable')]
    public $password;

    #[Rule('nullable')]
    public $agent_image;

    #[Rule('nullable')]
    public $business_name;

    #[Rule('nullable')]
    public $business_phone;

    #[Rule('nullable')]
    public $business_email;

    #[Rule('nullable')]
    public $business_logo;

    #[Rule('required')]
    public $propiter_nid;

    #[Rule('required')]
    public $propiter_etin_no;

    #[Rule('nullable')]
    public $trade_licence;

    #[Rule('required')]
    public $business_address;

    #[Rule('required')]
    public $primary_contact_address;

    #[Rule('required')]
    public $secondary_contact_address;

    #[Rule('nullable')]
    public $zipcode;

    #[Rule('nullable')]
    public $country_id;

    #[Rule('nullable')]
    public $division_id;

    #[Rule('nullable')]
    public $district_id;

    #[Rule('nullable')]
    public $credit_limit;

    public function mount()
    {
        $agentRes = auth()->user()->agent;
        if ($agentRes) {
            $this->agent = $agentRes;
            $this->propiter_name = $agentRes->user->name;
            $this->propiter_email = $agentRes->user->email;
            $this->business_name = $agentRes->business_name;
            $this->business_phone = $agentRes->business_phone;
            $this->business_email = $agentRes->business_email;
            $this->propiter_nid = $agentRes->propiter_nid;
            $this->propiter_etin_no = $agentRes->propiter_etin_no;
            $this->business_address = $agentRes->business_address;
            $this->primary_contact_address = $agentRes->primary_contact_address;
            $this->secondary_contact_address = $agentRes->secondary_contact_address;
            $this->zipcode = $agentRes->zipcode;
            $this->country_id = $agentRes->country_id;
            $this->divisions();
            $this->division_id = $agentRes->division_id;
            $this->districts();
            $this->district_id = $agentRes->district_id;
            $this->credit_limit = $agentRes->credit_limit;
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
    public function updateAgent()
    {
        $this->validate();
        try {
            // Process and store updated images if provided, otherwise keep existing images
            $storedAgentImagePath = $this->agent_image ? $this->optimizeAndUpdateImage($this->agent_image, $this->agent->agent_image, 'public', 'agent', null, null, 75) : $this->agent->agent_image;

            $storedBusinessLogoPath = $this->business_logo ? $this->optimizeAndUpdateImage($this->business_logo, $this->agent->business_logo, 'public', 'agent', null, null, 75) : $this->agent->business_logo;

            $storedTradeLicencePath = $this->trade_licence ? $this->optimizeAndUpdateImage($this->trade_licence, $this->agent->trade_licence, 'public', 'agent', null, null, 75) : $this->agent->trade_licence;

            $this->agent->user->update([
                'name' => $this->propiter_name,
                'email' => $this->propiter_email,
                'password' => $this->password ? Hash::make($this->password) : $this->agent->user->password,
            ]);
            $this->agent->update([
                'agent_image' => $storedAgentImagePath,
                'business_name' => $this->business_name,
                'business_phone' => $this->business_phone,
                'business_email' => $this->business_email,
                'business_logo' => $storedBusinessLogoPath,
                'propiter_nid' => $this->propiter_nid,
                'propiter_etin_no' => $this->propiter_etin_no,
                'trade_licence' => $storedTradeLicencePath,
                'business_address' => $this->business_address,
                'primary_contact_address' => $this->primary_contact_address,
                'secondary_contact_address' => $this->secondary_contact_address,
                'zipcode' => $this->zipcode,
                'country_id' => $this->country_id,
                'division_id' => $this->division_id,
                'district_id' => $this->district_id,
                'credit_limit' => $this->credit_limit,
            ]);
            $this->success('Profile Updated Successfully');
        } catch (\Throwable $th) {
            $this->error($th->getMessage());
        }
    }
}; ?>

<div>
    <x-header title="Update - Business Profile" size="text-xl" separator class="bg-white px-2 pt-2" />
    <x-form wire:submit="updateAgent">
        <div class="grid grid-cols-3 gap-4 items-start">
            <div class="col-span-2">
                <x-card class="mb-4">
                    <x-devider title="Propiter Information" />
                    <div class="grid grid-cols-3 gap-2">
                        <x-input label="Propiter Name" wire:model="propiter_name" placeholder="Propiter Name" />
                        <x-input label="Propiter Email" wire:model="propiter_email" placeholder="Propiter Email" />
                        <x-input label="Password" wire:model="password" placeholder="Password" />
                        <x-input label="Re-type Password" wire:model="" placeholder="Re-type Password" />
                        <x-input label="Propiter NID" wire:model="propiter_nid" placeholder="Propiter NID" type="number" />
                        <x-input label="Propiter eTin" wire:model="propiter_etin_no" placeholder="Propiter eTin" type="number" />
                    </div>
                </x-card>
                <x-card class="mb-4">
                    <x-devider title="Business Information" />
                    <div class="grid grid-cols-2 gap-2">
                        <x-input label="Busienss Name" wire:model="business_name" placeholder="Busienss Name" />
                        <x-input label="Busienss Phone" wire:model="business_phone" placeholder="Propiter Phone" />
                        <x-input label="Busienss Email" wire:model="business_email" placeholder="Propiter Email" type="email" />
                        <x-input label="Busienss Address" wire:model="business_address" placeholder="Propiter Address" />
                    </div>
                </x-card>
                <x-card class="pb-4">
                    <x-devider title="Upload Business Documents" />
                    <div class="grid grid-cols-3 gap-4">
                        @php
                            $config = ['guides' => false];
                        @endphp
                        <x-file label="Propiter Imager" wire:model="agent_image" :crop-config="$config" />
                        <x-file label="Business Logo" wire:model="business_logo" :crop-config="$config" />
                        <x-file label="Trade Licence" wire:model="trade_licence" :crop-config="$config" />
                    </div>
                </x-card>
            </div>
            <x-card class="col-span-1" x-cloak>
                <x-devider title="Additional Information" />
                <div class="grid grid-cols-2 gap-2">
                    <x-choices label="Country" :options="$countries" wire:model.live="country_id" single placeholder="Select One" />
                    <x-choices label="Division" :options="$divisions" wire:model.live="division_id" single placeholder="Select One" />
                    <x-choices label="District" :options="$districts" wire:model="district_id" single placeholder="Select One" />
                    <x-input label="Zip Code" wire:model="zipcode" placeholder="Zip Code" />
                    <x-input label="Primary Contact Address" wire:model="primary_contact_address" placeholder="Primary Contact Address" />
                    <x-input label="Secondary Contact Address" wire:model="secondary_contact_address" placeholder="Secondary Contact Address" />
                </div>
                <x-slot:actions>
                    <x-button type="submit" label="Save" class="btn-primary btn-sm mt-4" spinner="updateAgent" />
                </x-slot:actions>
            </x-card>
        </div>
    </x-form>
</div>
