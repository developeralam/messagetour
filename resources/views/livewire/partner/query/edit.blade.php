<?php

use Carbon\Carbon;
use Mary\Traits\Toast;
use App\Enum\HotelType;
use App\Models\Country;
use App\Enum\HotelRoomType;
use Livewire\Volt\Component;
use Livewire\Attributes\Rule;
use App\Models\CorporateQuery;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;
use App\Enum\CorporateQueryStatus;

new #[Layout('components.layouts.partner')] #[Title('Corporate Query Update')] class extends Component {
    use Toast;

    // Dropdown and selectable option data
    public $hotelTypes = [];
    public $hotelRoomTypes = [];
    public $destinationCountries = [];

    // Bound CorporateQuery model instance
    public CorporateQuery $query;

    // Config for date picker (e.g., range selection)
    public $config2 = ['mode' => 'range'];

    // Form fields with validation rules

    #[Rule('required')]
    public $destination_country;

    #[Rule('required')]
    public $name;

    #[Rule('required')]
    public $email;

    #[Rule('required')]
    public $phone;

    #[Rule('required')]
    public $group_size;

    #[Rule('required')]
    public $travel_date;

    #[Rule('required')]
    public $program;

    #[Rule('required')]
    public $hotel_type;

    #[Rule('nullable')]
    public $hotel_room_type;

    #[Rule('required')]
    public $meals;

    #[Rule('nullable')]
    public $meals_choices;

    #[Rule('nullable')]
    public $recommend_places;

    #[Rule('nullable')]
    public $activities;

    #[Rule('nullable')]
    public $visa_service;

    #[Rule('nullable')]
    public $air_ticket;

    #[Rule('nullable')]
    public $tour_guide;

    /**
     * Livewire mount method.
     * Loads hotel types, room types, countries, and populates form fields from the given query.
     *
     * @param CorporateQuery $query
     * @return void
     */
    public function mount(CorporateQuery $query): void
    {
        $this->query = $query;

        // Load dropdown options
        $this->hotelTypes = HotelType::getHotelTypes();
        $this->hotelRoomTypes = HotelRoomType::getHotelRoomTypes();
        $this->destinationCountries = Country::select(['id', 'name'])->get();

        // Format travel_date as Y-m-d to Y-m-d if it contains range
        if ($query->travel_date && str_contains($query->travel_date, ' to ')) {
            [$start, $end] = explode(' to ', $query->travel_date);
            $this->travel_date = Carbon::parse($start)->format('Y-m-d') . ' to ' . Carbon::parse($end)->format('Y-m-d');
        } else {
            $this->travel_date = $query->travel_date;
        }

        // Populate form fields from existing data
        $this->destination_country = $query->destination_country;
        $this->name = $query->name;
        $this->email = $query->email;
        $this->phone = $query->phone;
        $this->group_size = $query->group_size;
        $this->program = $query->program;
        $this->hotel_type = $query->hotel_type;
        $this->hotel_room_type = $query->hotel_room_type;
        $this->meals = $query->meals;
        $this->meals_choices = $query->meals_choices;
        $this->recommend_places = $query->recommend_places;
        $this->activities = $query->activities;
        $this->visa_service = $query->visa_service;
        $this->air_ticket = $query->air_ticket;
        $this->tour_guide = $query->tour_guide;
    }

    /**
     * Update the corporate query in the database.
     * Validates input and saves changes to the bound CorporateQuery model.
     *
     * @return void
     */
    public function updateQuery(): void
    {
        // Step 1: Validate input
        $this->validate();

        try {
            // Step 2: Check status before updating
            if ($this->query->status !== CorporateQueryStatus::Pending) {
                $this->error('Only pending queries can be updated.');
                return;
            }

            // Step 3: Update the corporate query record
            $this->query->update([
                'destination_country' => $this->destination_country,
                'name' => $this->name,
                'email' => $this->email,
                'phone' => $this->phone,
                'group_size' => $this->group_size,
                'travel_date' => $this->travel_date,
                'program' => $this->program,
                'hotel_type' => $this->hotel_type,
                'hotel_room_type' => $this->hotel_room_type,
                'meals' => $this->meals,
                'meals_choices' => $this->meals_choices,
                'recommend_places' => $this->recommend_places,
                'activities' => $this->activities,
                'visa_service' => $this->visa_service,
                'air_ticket' => $this->air_ticket,
                'tour_guide' => $this->tour_guide,
            ]);

            // Step 4: Success message and redirect query list
            $this->success('Query updated successfully.', redirectTo:'/partner/corporate-query/list');
        } catch (\Throwable $th) {
            // Step 5: Error message
            $this->error(env('APP_DEBUG', false) ? $th->getMessage() : 'Something went wrong');
        }
    }
}; ?>

<div>
    <x-header title="Update Corporate Query" size="text-xl" separator class="bg-white px-2 pt-2">
        <x-slot:actions>
            <x-button label="Back" link="/partner/corporate-query/list" class="btn-sm btn-primary"
                icon="fas.arrow-left" />
        </x-slot:actions>
    </x-header>
    <x-form wire:submit="updateQuery">
        <x-card>
            <div class="grid grid-cols-3 gap-4" x-cloak>
                <x-input wire:model="name" placeholder="Person/Company Name" label="Name" required />
                <x-input wire:model="email" placeholder="Email" label="Communication Email Address " required />
                <x-input wire:model="phone" placeholder="Whatsapp/Call Mobile No" label="Mobile No" required />
                <x-choices wire:model="destination_country" label="Destination" placeholder="Select Destination"
                    :options="$destinationCountries" single required />
                <x-datepicker label="Travel Start/End Date" wire:model="travel_date" icon="o-calendar" :config="$config2"
                    required />
                <x-input wire:model="program" label="Program" placeholder="Program" required />
                <x-choices wire:model="hotel_type" label="Hotel Type" placeholder="Select Hotel Type" :options="$hotelTypes"
                    single required />
                <x-choices wire:model="hotel_room_type" label="Room Type" placeholder="Select Room Type"
                    :options="$hotelRoomTypes" single />
                <x-input wire:model="meals" placeholder="Meals" label="Meals" required />
            </div>
            <div class="grid grid-cols-4 gap-4 mt-4">
                <x-textarea wire:model="group_size" placeholder="Group Size" label="Group Size" cols="1"
                    rows="1" required />
                <x-textarea wire:model="meals_choices" placeholder="Meals Choices (Optional)" label="Meals Choice"
                    cols="1" rows="1" />
                <x-textarea wire:model="recommend_places" placeholder="Recommend Places (Optional)"
                    label="Recommend Places" cols="1" rows="1" />
                <x-textarea wire:model="activities" placeholder="Activities (Optional)" label="Activities"
                    cols="1" rows="1" />
            </div>
            <div class="flex gap-4 mt-2">
                <x-checkbox wire:model="visa_service" label="Visa Service" class="w-4 h-4" />
                <x-checkbox wire:model="air_ticket" label="Air Ticket" class="w-4 h-4" />
                <x-checkbox wire:model="tour_guide" label="Tour Guide" class="w-4 h-4" />
                <x-slot:actions>
                    <x-button type="submit" label="Update Query" class="btn-primary btn-sm" spinner="updateQuery" />
                </x-slot:actions>
            </div>
        </x-card>
    </x-form>
</div>
