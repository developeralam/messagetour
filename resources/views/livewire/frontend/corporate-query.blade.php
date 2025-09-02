<?php

use App\Models\User;
use App\Enum\UserType;
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
use App\Jobs\CorporateQueryMailJob;
use Illuminate\Support\Facades\Notification;
use App\Notifications\CorporateQueryNotification;

new #[Layout('components.layouts.app')] #[Title('Corporate Query')] class extends Component {
    use Toast;

    // Dropdown and selectable option data
    public $hotelTypes = [];
    public $hotelRoomTypes = [];
    public $destinationCountries = [];

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
     * Prepares dropdown data and pre-fills user name/email if logged in.
     *
     * @return void
     */
    public function mount(): void
    {
        $this->hotelTypes = HotelType::getHotelTypes(); // All available hotel types
        $this->hotelRoomTypes = HotelRoomType::getHotelRoomTypes(); // Room types
        $this->destinationCountries = Country::select(['id', 'name'])->get(); // Country options

        // Pre-fill user name and email if logged in
        $this->name = auth()->user()->name ?? '';
        $this->email = auth()->user()->email ?? '';
    }

    /**
     * Store the corporate travel query in the database.
     * Validates input and stores query with "Pending" status.
     *
     * @return void
     */
    public function storeQuery(): void
    {
        // Step 1: Validate the form input
        $this->validate();

        try {
            // Step 2: Create a new corporate query entry
            $query = CorporateQuery::create([
                'user_id' => auth()->id(),
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
                'status' => CorporateQueryStatus::Pending,
            ]);

            CorporateQueryMailJob::dispatch($this->email, $query);

            // Send notifications
            $admins = User::where('type', UserType::Admin)->get();

            // Notify all admins
            Notification::send($admins, new CorporateQueryNotification($query));

            // Step 3: Reset form fields after successful submission
            $this->reset(['name', 'email', 'phone', 'group_size', 'travel_date', 'program', 'hotel_type', 'hotel_room_type', 'meals', 'meals_choices', 'recommend_places', 'activities', 'visa_service', 'air_ticket', 'tour_guide']);

            // Step 4: Notify success to user
            $this->success('Your query has been collected. The authorities will contact you shortly.');
        } catch (\Throwable $th) {
            // Step 5: Handle and show error
            $this->error(env('APP_DEBUG', false) ? $th->getMessage() : 'Something went wrong');
        }
    }
}; ?>

<div>
    <h3 class="font-semibold text-xl text-center mt-4">Corporate Query</h3>

    <x-form wire:submit="storeQuery">
        <x-card class="mx-8 my-4 shadow-xl p-8">
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
                    <x-button type="submit" label="Store Corporate Query" class="btn-primary btn-sm"
                        spinner="storeQuery" />
                </x-slot:actions>
            </div>
        </x-card>
    </x-form>
</div>
