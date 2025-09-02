<?php

use App\Models\User;
use App\Enum\UserType;
use Mary\Traits\Toast;
use App\Enum\AgentType;
use App\Models\Country;
use App\Models\District;
use App\Models\Division;
use App\Enum\AgentStatus;
use App\Enum\UserStatus;
use Livewire\Volt\Component;
use Livewire\Attributes\Rule;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Traits\InteractsWithImageUploads;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Features\SupportFileUploads\WithFileUploads;

new #[Layout('components.layouts.admin')] #[Title('Add New Agent')] class extends Component {
    use Toast, WithFileUploads, InteractsWithImageUploads;

    public Collection $countries;
    public Collection $divisions;
    public Collection $districts;
    public $agentTypes;
    public $statusOptions;
    public $businessStatus;

    #[Rule('required')]
    public $propiter_name;

    #[Rule('required')]
    public $propiter_email;

    #[Rule('required')]
    public $propiter_status = 1;

    #[Rule('required')]
    public $password;

    #[Rule('required|same:password')]
    public $confirmation_password;

    #[Rule('required')]
    public $agent_image;

    #[Rule('required')]
    public $agent_type;

    #[Rule('required')]
    public $business_name;

    #[Rule('required')]
    public $business_phone;

    #[Rule('required')]
    public $business_email;

    #[Rule('required')]
    public $business_logo;

    #[Rule('required')]
    public $propiter_nid;

    #[Rule('required')]
    public $propiter_etin_no;

    #[Rule('required')]
    public $trade_licence;

    #[Rule('required')]
    public $business_address;

    #[Rule('required')]
    public $primary_contact_address;

    #[Rule('required')]
    public $secondary_contact_address;

    #[Rule('nullable')]
    public $zipcode;

    #[Rule('required')]
    public $validity;

    #[Rule('required')]
    public $country_id;

    #[Rule('required')]
    public $division_id;

    #[Rule('required')]
    public $district_id;

    #[Rule('nullable')]
    public $credit_limit;

    #[Rule('required')]
    public $status = 1;

    public function mount()
    {
        $this->countries = Country::all();
        $this->divisions = collect();
        $this->districts = collect();
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
     * Store a new agent and related data.
     *
     * @return void
     */
    public function storeAgent()
    {
        $this->validate();

        DB::beginTransaction();
        try {
            // Initialize storage paths for uploaded images
            $storedAgentImagePath = null;
            $storedBusinessLogoPath = null;
            $storedTradeLicencePath = null;

            // Process and store agent image if provided
            $storedAgentImagePath = $this->agent_image ? $this->optimizeAndStoreImage($this->agent_image, 'public', 'agent', null, null, 75) : null;

            // Process and store business logo if provided
            $storedBusinessLogoPath = $this->business_logo ? $this->optimizeAndStoreImage($this->business_logo, 'public', 'agent', null, null, 75) : null;

            // Process and store trade licence if provided
            $storedTradeLicencePath = $this->trade_licence ? $this->optimizeAndStoreImage($this->trade_licence, 'public', 'agent', null, null, 75) : null;

            // Create a new user for the agent
            $user = User::create([
                'name' => $this->propiter_name,
                'email' => $this->propiter_email,
                'password' => Hash::make($this->password),
                'status' => $this->propiter_status,
                'type' => UserType::Agent,
            ]);

            // Create associated agent record linked to the user
            $user->agent()->create([
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
            ]);

            DB::commit();
            $this->success('Agent Added Successfully', redirectTo: '/admin/agent/list');
        } catch (\Throwable $th) {
            DB::rollBack();
            $this->error(env('APP_DEBUG', false) ? $th->getMessage() : 'Something went wrong');
        }
    }
}; ?>

<div>
    <x-header title="Add New Agent" size="text-xl" separator class="bg-white px-2 pt-2">
        <x-slot:actions>
            <x-button label="Agent List" icon="fas.arrow-left" link="/admin/agent/list" class="btn-primary btn-sm" />
        </x-slot:actions>
    </x-header>
    <x-form wire:submit="storeAgent">
        <div class="grid grid-cols-3 gap-4 h-full" x-cloak>
            <div class="col-span-2">
                <x-card class="mb-2">
                    <x-devider title="Propiter Information" />
                    <div class="grid grid-cols-3 gap-2">
                        <x-input label="Propiter Name" class="mb-4" wire:model="propiter_name" required
                            placeholder="Propiter Name" />
                        <x-input label="Propiter Email" class="mb-4" wire:model="propiter_email" required
                            placeholder="Propiter Email" />
                        <x-password label="Password" wire:model="password" placeholder="Password" required right />
                        <x-password label="Re-type Password" wire:model="confirmation_password" required
                            placeholder="Re-type Password" right />
                        <x-input label="Propiter NID" class="mb-4" wire:model="propiter_nid" required
                            placeholder="Propiter NID" type="number" />
                        <x-input label="Propiter eTin" class="mb-4" wire:model="propiter_etin_no" required
                            placeholder="Propiter eTin" type="number" />
                    </div>
                </x-card>
                <x-card class="mb-2">
                    <x-devider title="Business Information" />
                    <div class="grid grid-cols-3 gap-2">
                        <x-input label="Business Name" wire:model="business_name" required
                            placeholder="Business Name" />
                        <x-choices label="Business Type" wire:model="agent_type" :options="$agentTypes" single required
                            placeholder="Business Type" />
                        <x-input label="Business Phone" wire:model="business_phone" required
                            placeholder="Business Phone" />
                        <x-input label="Business Email" wire:model="business_email" required
                            placeholder="Business Email" type="email" />
                        <x-input label="Business Address" wire:model="business_address" required
                            placeholder="Business Address" />
                        <x-datetime label="Business Validity" wire:model="validity" required />
                    </div>
                </x-card>
                <x-card class="pb-10">
                    <x-devider title="Upload Business Documents" />
                    <div class="grid grid-cols-3 gap-2">
                        <x-file label="Propiter Imager" wire:model="agent_image" required />
                        <x-file label="Business Logo" wire:model="business_logo" required />
                        <x-file label="Trade Licence" wire:model="trade_licence" required />
                    </div>
                </x-card>
            </div>
            <div class="col-span-1">
                <x-card>
                    <x-devider title="Additional Information" />
                    <x-choices label="Country" :options="$countries" single wire:model.live="country_id" required
                        placeholder="Select One" class="mb-4" />
                    <div class="grid grid-cols-2 gap-2 mb-4">
                        <x-choices label="Division" :options="$divisions" single wire:model.live="division_id" required
                            placeholder="Select One" />
                        <x-choices label="District" :options="$districts" single wire:model="district_id" required
                            placeholder="Select One" />
                    </div>
                    <x-input label="Zip Code" class="mb-4" wire:model="zipcode" required placeholder="Zip Code" />
                    <div class="grid grid-cols-2 gap-2 mb-4">
                        <x-input label="Primary Address" wire:model="primary_contact_address" required
                            placeholder="Primary Contact Address" />
                        <x-input label="Secondary Address" wire:model="secondary_contact_address" required
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
                        <x-button type="submit" label="Add Agent" class="btn-primary btn-sm"
                            spinner="storeAgent" />
                    </x-slot:actions>
                </x-card>
            </div>
        </div>

    </x-form>
</div>
