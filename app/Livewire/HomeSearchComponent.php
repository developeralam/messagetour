<?php

namespace App\Livewire;

use Carbon\Carbon;
use App\Models\Tour;
use App\Enum\CarType;
use App\Models\Hotel;
use App\Enum\TourType;
use App\Enum\HotelType;
use App\Models\Country;
use Livewire\Component;
use App\Enum\RentalType;
use App\Enum\TourStatus;
use App\Models\District;
use App\Models\Division;
use App\Enum\HotelStatus;
use App\Enum\CountryStatus;
use Illuminate\Support\Str;
use App\Enum\DistrictStatus;
use App\Models\TravelProduct;
use App\Enum\TravelProductStatus;

class HomeSearchComponent extends Component
{
    // List Properties
    public $hotelList = [];
    public $tourList = [];
    public $gearList = [];
    public $visaCountryList = [];
    public $visas = [];
    public $hotel_types = [];
    public $countryList = [];
    public $divisionList = [];
    public $districtList = [];
    public bool $use_different_dropoff = false;

    // Hotel Search Properties
    public $hotel_keyword;
    public $hotel_checkin;
    public $hotel_checkout;
    public $hotel_type;

    // Tour Search Properties
    public $tour_keyword;
    public $tour_type;
    public $tour_types;

    // Gear Search Property
    public $gear_keyword;

    // Visa Search Property
    public $origin_country;
    public $destination_country;
    public $type;

    // Car Search Property
    public $pickup_country_id;
    public $pickup_division_id;
    public $pickup_district_id;
    public $dropoff_district_id;
    public $pickup_datetime;
    public $return_datetime;
    public $rental_type;
    public $vehicle_type;
    public $rentalTypes;
    public $vehicleTypes;

    public $selectedTab = 'tour';

    public function mount()
    {
        $this->selectedTab = request()->query('selectedTab', 'tour'); // override from URL if available
        //Tour
        $this->tour_types = TourType::getTourTypes();
        $this->tour_type = TourType::Tour;
        $this->tour_keyword = null;
        $this->tourSearch('');
        //Hotel
        $this->hotel_types = HotelType::getHotelTypes();
        $this->hotel_type = HotelType::Hotel;
        $this->hotel_keyword = null;
        $this->hotelSearch('');
        $this->hotel_checkin = Carbon::today();
        $this->hotel_checkout = Carbon::today()->addDays(2);
        //Gear
        $this->gearSearch();
        //Country
        $this->countrySearch();
        $this->visaCountrySearch();
        // Car
        $this->pickup_datetime = Carbon::today()->setTime(10, 0)->format('Y-m-d\TH:i');
        $this->return_datetime = Carbon::today()->addDays(3)->setTime(10, 0)->format('Y-m-d\TH:i');
        $this->rentalTypes = RentalType::getRentalTypes();
        $this->vehicleTypes = CarType::getTypes();
    }

    public function updatedTourType(): void
    {
        $this->tourSearch();
    }

    public function updatedTourKeyword($value): void
    {
        if (!$value) {
            // Cleared selection — show by current type
            $this->tourSearch();
            return;
        }

        if (Str::endsWith($value, '-tour')) {
            $tourId = explode('-', $value)[0];

            $tour = Tour::find($tourId);
            if ($tour) {
                $this->tour_type = $tour->type; // ✅ Update type
                $this->tourSearch();            // ✅ Show only that type
            }
        }
    }

    public function updatedHotelType(): void
    {
        $this->hotelSearch();
    }

    public function updatedHotelKeyword($value): void
    {
        if (!$value) {
            // Cleared selection — show by current type
            $this->hotelSearch();
            return;
        }

        if (Str::endsWith($value, '-hotel')) {
            $hotelId = explode('-', $value)[0];

            $hotel = Hotel::find($hotelId);
            if ($hotel) {
                $this->hotel_type = $hotel->type; // ✅ Update type
                $this->hotelSearch();            // ✅ Show only that type
            }
        }
    }

    public function hotelSearch(string $search = '')
    {
        $searchTerm = '%' . $search . '%';

        // Districts
        $districts = District::with(['division.country:name,id'])
            ->where('name', 'like', $searchTerm)
            ->where('status', 1)
            ->select('name', 'id', 'division_id')
            ->latest()
            ->limit(5)
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id . '-district',
                    'name' => $item->name,
                    'subname' => $item->name . ', ' . (optional(optional($item->division)->country)->name ?? ''),
                    'icon' => asset('location-icon-avatar.svg'),
                ];
            });

        // Hotel query with conditional type filtering
        $hotelQuery = Hotel::where('status', HotelStatus::Active);

        if (trim($search) === '') {
            // If no search, show hotels from selected hotel type ONLY
            $hotelQuery->where('type', $this->hotel_type);
        } else {
            // If search, show hotels from all types that match title
            $hotelQuery->where('name', 'like', $searchTerm);
        }

        $hotels = $hotelQuery
            ->select('name', 'id', 'address') // Assuming the hotel has an 'address' field
            ->latest()
            ->limit(5)
            ->get()
            ->map(fn($item) => [
                'id' => $item->id . '-hotel',
                'name' => $item->name,
                'subname' => $item->address ?? '',
                'icon' => asset('hotel-icon-avatar.svg'),
                'type' => $item->type,
            ]);

        // Divisions
        $divisions = Division::with(['country:name,id'])
            ->where('name', 'like', $searchTerm)
            ->where('status', 1)
            ->select('name', 'id', 'country_id')
            ->latest()
            ->limit(5)
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id . '-division',
                    'name' => $item->name,
                    'subname' => $item->name . ', ' . optional($item->country)->name ?? '',
                    'icon' => asset('location-icon-avatar.svg'),
                ];
            });

        // Merge results into hotelList
        $this->hotelList = collect()
            ->merge($districts)
            ->merge($hotels)
            ->merge($divisions);
    }

    public function tourSearch(string $search = '')
    {
        $searchTerm = '%' . $search . '%';

        // Fetch and transform districts
        $districts = District::with('division.country:id,name')
            ->where('name', 'like', $searchTerm)
            ->where('status', DistrictStatus::Active)
            ->select('id', 'name', 'division_id')
            ->latest()
            ->limit(5)
            ->get()
            ->map(fn($item) => [
                'id' => $item->id . '-district',
                'name' => $item->name,
                'subname' => $item->name . ', ' . optional($item->division->country)->name ?? '',
                'icon' => asset('location-icon-avatar.svg'),
            ]);

        // Fetch and transform tours

        // Tour query with conditional type filtering
        $toursQuery = Tour::query()
            ->where('status', TourStatus::Active)
            ->whereDate('start_date', '>=', Carbon::now())
            ->whereDate('validity', '>=', Carbon::now());

        if (trim($search) === '') {
            // If no search, show tours from selected tour type ONLY
            $toursQuery->where('type', $this->tour_type);
        } else {
            // If search, show tours from all types that match title
            $toursQuery->where('title', 'like', $searchTerm);
        }

        $tours = $toursQuery
            ->latest()
            ->limit(10)
            ->get()
            ->map(fn($item) => [
                'id' => $item->id . '-tour',
                'name' => $item->title,
                'subname' => $item->location ?? '',
                'icon' => $item->thumbnail_link ?? '',
                'type' => $item->type,
            ]);

        // Fetch and transform divisions
        $divisions = Division::with('country:id,name')
            ->where('name', 'like', $searchTerm)
            ->where('status', 1)
            ->select('id', 'name', 'country_id')
            ->latest()
            ->limit(5)
            ->get()
            ->map(fn($item) => [
                'id' => $item->id . '-division',
                'name' => $item->name,
                'subname' => $item->name . ', ' . optional($item->country)->name ?? '',
                'icon' => asset('location-icon-avatar.svg'),
            ]);

        // Merge results into tourList
        $this->tourList = collect()
            ->merge($districts)
            ->merge($tours)
            ->merge($divisions);
    }

    public function gearSearch(string $search = '')
    {
        $searchTerm = '%' . $search . '%';

        $gears = TravelProduct::where('status', TravelProductStatus::Active)
            ->where('is_featured', 1)
            ->where(function ($query) use ($searchTerm) {
                $query->where('title', 'like', $searchTerm)
                    ->orWhere('brand', 'like', $searchTerm);
            })
            ->select('id', 'title', 'brand', 'thumbnail')
            ->latest()
            ->limit(5)
            ->get()
            ->map(fn($item) => [
                'id' => $item->id . '-gear',
                'name' => $item->title,
                'subname' => $item->brand ?? '',
                'icon' => $item->thumbnail_link ?? '',
            ]);

        $this->gearList = $gears->toArray(); // Assign search results
    }

    public function countrySearch(string $search = '')
    {
        $searchTerm = '%' . $search . '%';

        $citizen = Country::where('status', CountryStatus::Active)
            ->where('name', 'like', $searchTerm)
            ->limit(5)
            ->get();

        $this->countryList = $citizen->toArray();
    }

    public function divisionSearch(string $search = '')
    {
        $searchTerm = '%' . $search . '%';

        // Ensure the country is selected before searching divisions
        if ($this->pickup_country_id) {
            $divisions = Division::where('country_id', $this->pickup_country_id)
                ->where('name', 'like', $searchTerm)
                ->limit(5)
                ->get();

            $this->divisionList = $divisions->toArray();
        }
    }

    public function districtSearch(string $search = '')
    {
        $searchTerm = '%' . $search . '%';

        // Ensure the division is selected before searching districts
        if ($this->pickup_division_id) {
            $districts = District::where('division_id', $this->pickup_division_id)
                ->where('name', 'like', $searchTerm)
                ->limit(5)
                ->get();

            $this->districtList = $districts->toArray();
        }
    }
    public function updated($property): void
    {
        match ($property) {
            'pickup_country_id' => $this->updateDivisionList(),
            'pickup_division_id' => $this->updateDistrictList(),
            default => $this->handleUnhandledProperty($property), // This will handle unhandled cases
        };
    }

    public function updateDivisionList()
    {
        // Fetch divisions based on the selected country
        $this->divisionList = Division::where('country_id', $this->pickup_country_id)
            ->get()
            ->toArray();

        // Reset the district list when country changes
        $this->districtList = [];
    }

    public function updateDistrictList()
    {
        // Ensure that division is selected before fetching districts
        if ($this->pickup_division_id) {
            // Fetch districts based on the selected division
            $this->districtList = District::where('division_id', $this->pickup_division_id)
                ->get()
                ->toArray();
        } else {
            // If no division is selected, reset the district list
            $this->districtList = [];
        }
    }

    public function handleUnhandledProperty($property)
    {
        // Handle any unhandled properties here
        // For debugging purposes, you can log or display the unhandled property
        \Log::warning("Unhandled property: $property");
    }

    public function visaCountrySearch(string $search = '')
    {
        $searchTerm = '%' . $search . '%';

        $citizen = Country::where('status', CountryStatus::Active)
            ->where('name', 'like', $searchTerm)
            ->limit(5)
            ->get();

        $this->visaCountryList = $citizen->toArray();
    }

    public function searchHotel()
    {
        return redirect()->route('frontend.hotel.search', [
            'keyword' => $this->hotel_keyword,
            'check_in' => Carbon::parse($this->hotel_checkin)->format('Y-m-d'),
            'check_out' => Carbon::parse($this->hotel_checkout)->format('Y-m-d'),
            'hotel_type' => $this->hotel_type,
            'selectedTab' => 'hotel',
        ]);
    }

    public function searchTour()
    {
        return redirect()->route('frontend.tour.search', [
            'keyword' => $this->tour_keyword,
            'tour_type' => $this->tour_type,
            'selectedTab' => 'tour',
        ]);
    }

    public function searchGear()
    {
        return redirect()->route('frontend.gear.search', [
            'keyword' => $this->gear_keyword,
            'selectedTab' => 'gear',
        ]);
    }

    public function searchVisa()
    {
        return redirect()->route('frontend.visa.search', [
            'origin' => $this->origin_country,
            'destination' => $this->destination_country,
            'type' => $this->type,
            'selectedTab' => 'visa',
        ]);
    }

    public function searchCar()
    {
        return redirect()->route('frontend.car.search', [
            'pickup_country_id' => $this->pickup_country_id,
            'pickup_division_id' => $this->pickup_division_id,
            'pickup_district_id' => $this->pickup_district_id,
            'dropoff_district_id' => $this->dropoff_district_id,
            'pickup_datetime' => Carbon::parse($this->pickup_datetime)->format('Y-m-d\TH:i'),
            'return_datetime' => Carbon::parse($this->return_datetime)->format('Y-m-d\TH:i'),
            'rental_type' => $this->rental_type,
            'vehicle_type' => $this->vehicle_type,
            'selectedTab' => 'car-rental',

        ]);
    }

    public function render()
    {
        return view('livewire.home-search-component');
    }
}
