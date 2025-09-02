<?php

use App\Models\Agent;
use Mary\Traits\Toast;
use App\Enum\AgentType;
use App\Models\Country;
use App\Enum\UserStatus;
use App\Models\District;
use App\Models\Division;
use App\Enum\AgentStatus;
use Livewire\Volt\Component;
use Livewire\Attributes\Rule;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use App\Traits\InteractsWithImageUploads;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Features\SupportFileUploads\WithFileUploads;

new #[Layout('components.layouts.admin')] #[Title('Update Agent')] class extends Component {
    use Toast, WithFileUploads, InteractsWithImageUploads;

    public Collection $countries;
    public Collection $divisions;
    public Collection $districts;
    public $agent = [];
    public $agentTypes;
    public $statusOptions;
    public $businessStatus;

    #[Rule('required')]
    public $propiter_name;

    #[Rule('required')]
    public $agent_type;

    #[Rule('required')]
    public $propiter_status;

    #[Rule('required')]
    public $propiter_email;

    #[Rule('nullable')]
    public $password;

    #[Rule('nullable|same:password')]
    public $confirmation_password;

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

    #[Rule('nullable')]
    public $propiter_nid;

    #[Rule('nullable')]
    public $propiter_etin_no;

    #[Rule('nullable')]
    public $trade_licence;

    #[Rule('nullable')]
    public $business_address;

    #[Rule('nullable')]
    public $primary_contact_address;

    #[Rule('nullable')]
    public $secondary_contact_address;

    #[Rule('nullable')]
    public $zipcode;

    #[Rule('nullable')]
    public $validity;

    #[Rule('nullable')]
    public $country_id;

    #[Rule('nullable')]
    public $division_id;

    #[Rule('nullable')]
    public $district_id;

    #[Rule('nullable')]
    public $credit_limit;

    #[Rule('required')]
    public $status;

    public function mount($agent)
    {
        $agentRes = Agent::with('user')->find($agent);
        if ($agentRes) {
            $this->agent = $agentRes;
            $this->propiter_name = $agentRes->user->name;
            $this->propiter_email = $agentRes->user->email;
            $this->propiter_status = $agentRes->user->status;

            $this->business_name = $agentRes->business_name;
            $this->agent_type = $agentRes->agent_type;
            $this->status = $agentRes->status;
            $this->business_phone = $agentRes->business_phone;
            $this->business_email = $agentRes->business_email;
            $this->propiter_nid = $agentRes->propiter_nid;
            $this->propiter_etin_no = $agentRes->propiter_etin_no;
            $this->business_address = $agentRes->business_address;
            $this->primary_contact_address = $agentRes->primary_contact_address;
            $this->secondary_contact_address = $agentRes->secondary_contact_address;
            $this->zipcode = $agentRes->zipcode;
            $this->validity = optional($agentRes->validity)->format('d M, Y');
            $this->country_id = $agentRes->country_id;
            $this->divisions();
            $this->division_id = $agentRes->division_id;
            $this->districts();
            $this->district_id = $agentRes->district_id;
            $this->credit_limit = $agentRes->credit_limit;
        }
        $this->countries = Country::all();
        $this->agentTypes = AgentType::getTypes();
        $this->statusOptions = UserStatus::getStatuses();
        $this->businessStatus = AgentStatus::getStatuses();
    }
    /**
     * Fetch divisions based on the selected country.
     *
     * This function updates the `divisions` property by querying the database
     * for divisions that belong to the currently selected `country_id`.
     *
     * @return void
     */
    public function divisions()
    {
        $this->divisions = Division::query()->when($this->country_id, fn(Builder $q) => $q->where('country_id', $this->country_id))->get();
    }

    /**
     * Fetch districts based on the selected division.
     *
     * This function updates the `districts` property by querying the database
     * for districts that belong to the currently selected `division_id`.
     *
     * @return void
     */
    public function districts()
    {
        $this->districts = District::query()->when($this->division_id, fn(Builder $q) => $q->where('division_id', $this->division_id))->get();
    }

    /**
     * Automatically update dependent dropdowns when a property changes.
     *
     * @param string $property The name of the updated property.
     * @return void
     */
    public function updated($property)
    {
        match ($property) {
            'country_id' => $this->divisions(),
            'division_id' => $this->districts(),
            default => null,
        };
    }
    /**
     * Update an existing agent and related user data with transaction handling.
     *
     * @return void
     */
    public function updateAgent()
    {
        $this->validate();
        try {
            // Process and store updated images if provided, otherwise keep existing images
            $storedAgentImagePath = $this->agent_image ? $this->optimizeAndUpdateImage($this->agent_image, $this->agent->agent_image, 'public', 'agent', null, null, 75) : $this->agent->agent_image;

            $storedBusinessLogoPath = $this->business_logo ? $this->optimizeAndUpdateImage($this->business_logo, $this->agent->business_logo, 'public', 'agent', null, null, 75) : $this->agent->business_logo;

            $storedTradeLicencePath = $this->trade_licence ? $this->optimizeAndUpdateImage($this->trade_licence, $this->agent->trade_licence, 'public', 'agent', null, null, 75) : $this->agent->trade_licence;

            // Update user details
            $this->agent->user->update([
                'name' => $this->propiter_name,
                'email' => $this->propiter_email,
                'password' => $this->password ? Hash::make($this->password) : $this->agent->user->password,
                'status' => $this->propiter_status,
            ]);

            // Update agent details
            $this->agent->update([
                'agent_image' => $storedAgentImagePath,
                'agent_type' => $this->agent_type,
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
                'validity' => $this->validity,
                'country_id' => $this->country_id,
                'division_id' => $this->division_id,
                'district_id' => $this->district_id,
                'credit_limit' => $this->credit_limit,
                'status' => $this->status,
                'action_by' => auth()->user()->id,
            ]);
            $this->success('Agent Updated Successfully', redirectTo: '/admin/agent/list');
        } catch (\Throwable $th) {
            $this->error(env('APP_DEBUG', false) ? $th->getMessage() : 'Something went wrong');
        }
    }
}; ?>

<div>
    <x-header title="Update - {{ $agent->business_name ?? '' }}" size="text-xl" separator class="bg-white px-2 pt-2">
        <x-slot:actions>
            <x-button label="Agent List" icon="fas.arrow-left" link="/admin/agent/list" class="btn-primary btn-sm" />
        </x-slot:actions>
    </x-header>
    <x-form wire:submit="updateAgent">
        <div class="grid grid-cols-3 gap-4 h-full" x-cloak>
            <div class="col-span-2 flex flex-col">
                <x-card class="mb-2">
                    <x-devider title="Propiter Information" />
                    <div class="grid grid-cols-3 gap-2">
                        <x-input label="Propiter Name" class="mb-4" wire:model="propiter_name" required
                            placeholder="Propiter Name" />
                        <x-input label="Propiter Email" class="mb-4" wire:model="propiter_email" required
                            placeholder="Propiter Email" />
                        <x-password label="Password" wire:model="password" placeholder="Password" right />
                        <x-password label="Re-type Password" wire:model="confirmation_password"
                            placeholder="Re-type Password" right />
                        <x-input label="Propiter NID" class="mb-4" wire:model="propiter_nid"
                            placeholder="Propiter NID" type="number" />
                        <x-input label="Propiter eTin" class="mb-4" wire:model="propiter_etin_no"
                            placeholder="Propiter eTin" type="number" />
                    </div>
                </x-card>
                <x-card class="mb-2">
                    <x-devider title="Business Information" />
                    <div class="grid grid-cols-3 gap-2">
                        <x-input label="Business Name" wire:model="business_name" placeholder="Business Name" />
                        <x-choices label="Business Type" wire:model="agent_type" :options="$agentTypes" single required
                            placeholder="Business Type" />
                        <x-input label="Business Phone" wire:model="business_phone" placeholder="Business Phone" />
                        <x-input label="Business Email" wire:model="business_email" placeholder="Business Email"
                            type="email" />
                        <x-input label="Business Address" wire:model="business_address"
                            placeholder="Business Address" />
                        <x-datetime label="Business Validity" wire:model="validity" />
                    </div>
                </x-card>
                <x-card>
                    <x-devider title="Upload Business Documents" />
                    <div class="grid grid-cols-3 gap-2 pb-10">
                        <x-file label="Propiter Imager" wire:model="agent_image" />
                        <x-file label="Business Logo" wire:model="business_logo" />
                        <x-file label="Trade Licence" wire:model="trade_licence" />
                    </div>
                </x-card>
            </div>
            <div class="col-span-1">
                <x-card>
                    <x-devider title="Additional Information" />
                    <x-choices label="Country" :options="$countries" single wire:model.live="country_id"
                        placeholder="Select One" class="mb-4" />
                    <div class="grid grid-cols-2 gap-2 mb-4">
                        <x-choices label="Division" :options="$divisions" single wire:model.live="division_id"
                            placeholder="Select One" />
                        <x-choices label="District" :options="$districts" single wire:model="district_id"
                            placeholder="Select One" />
                    </div>
                    <x-input label="Zip Code" class="mb-4" wire:model="zipcode" placeholder="Zip Code" />
                    <div class="grid grid-cols-2 gap-2 mb-4">
                        <x-input label="Primary Address" wire:model="primary_contact_address"
                            placeholder="Primary Contact Address" />
                        <x-input label="Secondary Address" wire:model="secondary_contact_address"
                            placeholder="Secondary Contact Address" />
                    </div>
                    <x-input label="Credit Limit" class="mb-4" wire:model="credit_limit" placeholder="Credit Limit"
                        type="number" />
                    <div class="flex gap-6 mb-3">
                        <x-radio label="Agent Status" :options="$statusOptions" wire:model="propiter_status" required />
                        <x-radio label="Business Status" :options="$businessStatus" wire:model="status" required />
                    </div>
                    <x-slot:actions>
                        <x-button label="Agent List" link="/admin/agent/list" class="btn-sm" />
                        <x-button type="submit" label="Update Agent" class="btn-primary btn-sm"
                            spinner="storeAgent" />
                    </x-slot:actions>
                </x-card>
            </div>
        </div>
    </x-form>
</div>
