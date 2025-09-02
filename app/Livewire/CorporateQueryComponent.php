<?php

namespace App\Livewire;

use App\Models\User;
use App\Enum\UserType;
use Mary\Traits\Toast;
use App\Enum\HotelType;
use App\Models\Country;
use Livewire\Component;
use App\Enum\CountryStatus;
use App\Enum\HotelRoomType;
use Livewire\Attributes\On;
use Livewire\Attributes\Rule;
use App\Models\CorporateQuery;
use App\Enum\CorporateQueryStatus;
use Illuminate\Support\Facades\Notification;
use App\Notifications\CorporateQueryNotification;

class CorporateQueryComponent extends Component
{
    use Toast;

    // Dropdown and selectable option data
    public $hotelTypes = [];
    public $hotelRoomTypes = [];
    public $destinationCountries = [];

    public bool $openModal = false;

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
        $this->countrySearch(); // Country options

        // Pre-fill user name and email if logged in
        $this->name = auth()->user()->name ?? '';
        $this->email = auth()->user()->email ?? '';
    }

    public function countrySearch(string $search = '')
    {
        $searchTerm = '%' . $search . '%';

        $citizen = Country::where('status', CountryStatus::Active)
            ->where('name', 'like', $searchTerm)
            ->limit(5)
            ->get();

        $this->destinationCountries = $citizen->toArray();
    }

    // Open modal
    #[On('openModel')]
    public function openModel()
    {
        $this->openModal = true;
    }

    /**
     * Store the corporate travel query in the database.
     * Validates input and stores query with "Pending" status.
     *
     * @return void
     */
    public function storeQuery(): void
    {
        // Step 0: Check if the user is authenticated
        if (!auth()->check()) {
            $this->error('Please login first to submit your query.');
            $this->reset(['name', 'email', 'phone', 'group_size', 'travel_date', 'program', 'hotel_type', 'destination_country', 'hotel_room_type', 'meals', 'meals_choices', 'recommend_places', 'activities', 'visa_service', 'air_ticket', 'tour_guide']);
            $this->openModal = false;
            return;
        }

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

            // Send notifications
            $admins = User::where('type', UserType::Admin)->get();

            // Notify all admins
            Notification::send($admins, new CorporateQueryNotification($query));

            // Step 3: Reset form fields after successful submission
            $this->reset(['name', 'email', 'phone', 'group_size', 'travel_date', 'program', 'hotel_type', 'destination_country', 'hotel_room_type', 'meals', 'meals_choices', 'recommend_places', 'activities', 'visa_service', 'air_ticket', 'tour_guide']);

            // Step 4: Notify success to user
            $this->success('Your query has been collected. The authorities will contact you shortly.');
            $this->openModal = false;
        } catch (\Throwable $th) {
            $this->openModal = false;
            // Step 5: Handle and show error
            $this->error(env('APP_DEBUG', false) ? $th->getMessage() : 'Something went wrong');
        }
    }
    public function render()
    {
        return view('livewire.corporate-query-component');
    }
}
