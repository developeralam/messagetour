<?php

use Carbon\Carbon;
use App\Models\Car;
use App\Enum\CarStatus;
use Livewire\Volt\Component;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\Auth;

new #[Layout('components.layouts.app')] #[Title('Car List')] class extends Component {
    public $pickup_country_id;
    public $pickup_division_id;
    public $pickup_district_id;
    public $dropoff_district_id;
    public $pickup_datetime;
    public $return_datetime;
    public $rental_type;
    public $vehicle_type;
    public $cars = [];
    public $selectedTab;

    /**
     * Mount the component and fetch query parameters from the URL.
     *
     * This method is used to initialize the component by setting the necessary
     * properties from the URL query parameters. It is called when the component
     * is mounted.
     *
     * @return void
     */
    public function mount(): void
    {
        // Get parameters from URL
        $this->selectedTab = request()->query('selectedTab', 'car-rental'); // default 'car-rental'

        // Retrieve the query parameters for car search
        $this->pickup_country_id = request()->query('pickup_country_id');
        $this->pickup_division_id = request()->query('pickup_division_id');
        $this->pickup_district_id = request()->query('pickup_district_id');
        $this->dropoff_district_id = request()->query('dropoff_district_id');
        $this->pickup_datetime = request()->query('pickup_datetime');
        $this->return_datetime = request()->query('return_datetime');
        $this->rental_type = request()->query('rental_type');
        $this->vehicle_type = request()->query('vehicle_type');

        // Fetch the cars based on the provided parameters
        $this->cars = $this->carList();
    }

    /**
     * Get list of cars based on selected filters
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function carList()
    {
        // Parse pickup and return datetime
        $pickupDateTime = Carbon::parse($this->pickup_datetime);
        $dropoffDateTime = Carbon::parse($this->return_datetime);

        // Start building query
        $query = Car::query()->where('status', CarStatus::Available);

        // Apply vehicle type filter if selected
        if (!empty($this->rental_type)) {
            $query->where('with_driver', $this->rental_type);
        }

        // Apply vehicle type filter if selected
        if (!empty($this->vehicle_type)) {
            $query->where('car_type', $this->vehicle_type);
        }

        // Apply pickup country filter if selected
        if (!empty($this->pickup_country_id)) {
            $query->where('country_id', $this->pickup_country_id);
        }

        // Apply pickup division filter if selected
        if (!empty($this->pickup_division_id)) {
            $query->where('division_id', $this->pickup_division_id);
        }

        // Apply pickup district filter if selected and different from division
        if (!empty($this->pickup_district_id) && $this->pickup_district_id != $this->pickup_division_id) {
            $query->where('district_id', $this->pickup_district_id);
        }

        // Apply availability filter (based on pickup and return datetime)
        $query->availableBetween($pickupDateTime, $dropoffDateTime);

        return $query->get();
    }

    public function book($slug)
    {
        if (Auth::check()) {
            // User is authenticated, redirect to the reservation page
            return redirect()->route('frontend.car.booking', [
                'slug' => $slug,
                'pickup_country_id' => $this->pickup_country_id,
                'pickup_division_id' => $this->pickup_division_id,
                'pickup_district_id' => $this->pickup_district_id,
                'dropoff_district_id' => $this->dropoff_district_id,
                'pickup_datetime' => $this->pickup_datetime,
                'return_datetime' => $this->return_datetime,
                'rental_type' => $this->rental_type,
                'vehicle_type' => $this->vehicle_type,
            ]);
        } else {
            // User is not authenticated, dispatch event to show login modal
            $this->dispatch(
                'showLoginModal',
                route('frontend.car.booking', [
                    'slug' => $slug,
                    'pickup_country_id' => $this->pickup_country_id,
                    'pickup_division_id' => $this->pickup_division_id,
                    'pickup_district_id' => $this->pickup_district_id,
                    'dropoff_district_id' => $this->dropoff_district_id,
                    'pickup_datetime' => $this->pickup_datetime,
                    'return_datetime' => $this->return_datetime,
                    'rental_type' => $this->rental_type,
                    'vehicle_type' => $this->vehicle_type,
                ]),
            );
        }
    }
}; ?>

<div>
    <livewire:login-modal-component />

    <section class="bg-gray-100 py-12">
        <div class="max-w-6xl mx-auto">
            <h2 class="text-3xl font-bold text-center text-gray-800 mb-10">
                🚘 Our Exclusive Car Collection
            </h2>

            <div class="grid gap-4 sm:grid-cols-1 md:grid-cols-2 lg:grid-cols-3">
                @foreach ($cars as $car)
                    <div wire:click="book('{{ $car->slug }}')"
                        class="car-card animate-fade-in-up cursor-pointer bg-white rounded-xl shadow-lg hover:shadow-2xl hover:scale-[1.03] transform hover:-translate-y-1 overflow-hidden transition duration-300 ease-in-out">
                        <div class="relative">
                            <img src="{{ $car->image_link }}" alt="{{ $car->title }}"
                                class="w-full h-52 object-cover group-hover:scale-105 transition duration-500" />

                        </div>

                        <div class="p-5 space-y-3">
                            <p class="text-xl font-semibold text-gray-800">{{ $car->title }} ({{ $car->model_year }})
                            </p>

                            <div class="flex flex-wrap gap-3 text-sm text-gray-600">
                                <span><strong>Seats:</strong> {{ $car->seating_capacity }}</span>
                                <span><strong>Color:</strong> {{ $car->color }}</span>
                                @if ($car->ac_facility)
                                    <span>
                                        <strong>AC:</strong>
                                        <span class="{{ $car->ac_facility ? 'text-green-600' : 'text-red-500' }}">
                                            {{ $car->ac_facility ? 'Yes' : 'No' }}
                                        </span>
                                    </span>
                                @endif
                            </div>

                            <button
                                class="mt-4 w-full bg-green-500 text-white font-semibold py-2 rounded-lg shadow hover:bg-green-600 transition-all">
                                Book Now
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>
</div>
