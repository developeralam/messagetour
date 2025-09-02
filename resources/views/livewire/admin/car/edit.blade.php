<?php

use Carbon\Carbon;
use App\Models\Car;
use App\Enum\CarType;
use Mary\Traits\Toast;
use App\Enum\CarStatus;
use App\Models\Country;
use App\Enum\RentalType;
use App\Models\District;
use App\Models\Division;
use Illuminate\Support\Str;
use Livewire\Volt\Component;
use Livewire\Attributes\Rule;
use Livewire\WithFileUploads;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;
use App\Traits\InteractsWithImageUploads;

new #[Layout('components.layouts.admin')] #[Title('Update Car')] class extends Component {
    use Toast, WithFileUploads, InteractsWithImageUploads;

    public $carTypes = [];
    public $carStatus = [];
    public $rentalTypes = [];
    public $countries = [];
    public $divisions = [];
    public $districts = [];
    public Car $car;

    #[Rule('required')]
    public $title;

    #[Rule('required')]
    public $model_year;

    #[Rule('nullable')]
    public $image;

    #[Rule('required')]
    public $seating_capacity;

    #[Rule('required')]
    public $car_cc;

    #[Rule('required')]
    public $car_type;

    #[Rule('required')]
    public $color;

    #[Rule('required')]
    public $country_id;

    #[Rule('required')]
    public $division_id;

    #[Rule('nullable')]
    public $district_id;

    #[Rule('nullable')]
    public $hour_rent;

    #[Rule('nullable')]
    public $include_with_pricing;

    #[Rule('nullable')]
    public $exclude_with_pricing;

    #[Rule('nullable')]
    public $area_limitation;

    #[Rule('nullable')]
    public $max_distance;

    #[Rule('required')]
    public $ac_facility;

    #[Rule('nullable')]
    public $rent;

    #[Rule('nullable')]
    public $extra_time_cost_by_hour;

    #[Rule('nullable')]
    public $extra_time_cost;

    #[Rule('required')]
    public $status;

    #[Rule('required')]
    public $price_2_hours;

    #[Rule('required')]
    public $price_4_hours;

    #[Rule('required')]
    public $price_half_day;

    #[Rule('required')]
    public $price_day;

    #[Rule('required')]
    public $price_per_day;

    #[Rule('required')]
    public $service_type;

    #[Rule('required')]
    public $with_driver = 1;

    public function mount($car)
    {
        $this->car = $car;
        $this->title = $car->title;
        $this->model_year = $car->model_year;
        $this->seating_capacity = $car->seating_capacity;
        $this->car_cc = $car->car_cc;
        $this->car_type = $car->car_type;
        $this->color = $car->color;
        $this->include_with_pricing = $car->include_with_pricing;
        $this->exclude_with_pricing = $car->exclude_with_pricing;
        $this->area_limitation = $car->area_limitation;
        $this->max_distance = $car->max_distance;
        $this->ac_facility = $car->ac_facility;
        $this->extra_time_cost_by_hour = $car->extra_time_cost_by_hour;
        $this->extra_time_cost = $car->extra_time_cost;
        $this->status = $car->status;
        $this->service_type = $car->service_type;
        $this->price_2_hours = $car->price_2_hours;
        $this->price_4_hours = $car->price_4_hours;
        $this->price_half_day = $car->price_half_day;
        $this->price_day = $car->price_day;
        $this->price_per_day = $car->price_per_day;
        $this->country_id = $car->country_id;
        $this->division_id = $car->division_id;
        $this->district_id = $car->district_id;
        $this->with_driver = $car->with_driver;
        $this->carTypes = CarType::getTypes();
        $this->carStatus = CarStatus::getStatuses();
        $this->rentalTypes = RentalType::getRentalTypes();
        $this->countries = Country::select(['id', 'name'])->get();
        $this->divisions();
        $this->districts();
    }

    public function updated($property)
    {
        match ($property) {
            'country_id' => $this->divisions(),
            'division_id' => $this->districts(),
            default => null, // No action for other properties
        };
    }

    public function divisions()
    {
        $this->divisions = Division::query()->when($this->country_id, fn($q) => $q->where('country_id', $this->country_id))->get();
    }

    public function districts()
    {
        $this->districts = District::query()->when($this->division_id, fn($q) => $q->where('division_id', $this->division_id))->get();
    }

    public function storeCar()
    {
        $this->validate();

        try {
            // Process and store updated images if provided, otherwise keep existing images
            $storedImagePath = $this->image ? $this->optimizeAndUpdateImage($this->image, $this->car->image, 'public', 'car', null, null, 75) : $this->car->image;

            $this->car->update([
                'title' => $this->title,
                'slug' => Str::slug($this->title),
                'model_year' => $this->model_year,
                'seating_capacity' => $this->seating_capacity,
                'car_cc' => $this->car_cc,
                'car_type' => $this->car_type,
                'color' => $this->color,
                'country_id' => $this->country_id,
                'division_id' => $this->division_id,
                'district_id' => $this->district_id,
                'exclude_with_pricing' => $this->exclude_with_pricing,
                'include_with_pricing' => $this->include_with_pricing,
                'area_limitation' => $this->area_limitation,
                'max_distance' => $this->max_distance,
                'ac_facility' => $this->ac_facility,
                'extra_time_cost_by_hour' => (int) $this->extra_time_cost_by_hour,
                'extra_time_cost' => (int) $this->extra_time_cost,
                'status' => $this->status,
                'image' => $storedImagePath,
                'service_type' => $this->service_type,
                'price_2_hours' => (int) $this->price_2_hours,
                'price_4_hours' => (int) $this->price_4_hours,
                'price_half_day' => (int) $this->price_half_day,
                'price_day' => (int) $this->price_day,
                'price_per_day' => (int) $this->price_per_day,
                'with_driver' => $this->with_driver,
                'action_id' => auth()->user()->id,
            ]);
            $this->success('Vehicle Update Successfully', redirectTo: '/admin/vehicle/list');
        } catch (\Throwable $th) {
            dd($th->getMessage());
            $this->error(env('APP_DEBUG', false) ? $th->getMessage() : 'Something went wrong');
        }
    }
}; ?>

<div>
    @push('custom-script')
        <script src="https://cdn.ckeditor.com/ckeditor5/39.0.1/classic/ckeditor.js"></script>
    @endpush
    <x-header title="Update Car - {{ $car->title }}" size="text-xl" separator class="bg-white px-2 pt-2">
        <x-slot:actions>
            <x-button label="Back" link="/admin/vehicle/list" class="btn-sm btn-primary" icon="fas.arrow-left" />
        </x-slot:actions>
    </x-header>
    <x-form wire:submit="storeCar">
        <div class="grid grid-cols-3 gap-2" x-cloak>
            <div class="col-span-2">
                <x-card>
                    <x-devider title="Vehicle Information" />
                    <div class="grid grid-cols-3 gap-4">
                        <x-input label="Vehicle Title" wire:model="title" placeholder="Vehicle Title" required />
                        <x-choices label="Vehicle Type" wire:model="car_type" placeholder="Select Vehicle Type" single
                            :options="$carTypes" required />
                        <x-input type="number" label="Model Year" wire:model="model_year"
                            placeholder="Vehicle Model Year" required />
                        <x-input type="number" label="Seating Capacity" wire:model="seating_capacity"
                            placeholder="Vehicle Seating Capacity required" required />
                        <x-input type="number" label="Vehicle Cc" wire:model="car_cc" placeholder="Vehicle CC"
                            required />
                        <x-input label="Vehicle Color" wire:model="color" placeholder="Vehicle Color" required />
                        <x-input label="Area Limitation" wire:model="area_limitation" placeholder="Area Limitation" />
                        <x-input label="Max Distance" wire:model="max_distance" placeholder="100 KM" />
                        <x-input label="Service Type" wire:model="service_type" placeholder="Service Type" />
                    </div>
                    <x-devider title="Locations" />
                    <div class="grid grid-cols-3 gap-4">
                        <x-choices label="Country" wire:model.live="country_id" placeholder="Select Country" single
                            :options="$countries" required />
                        <x-choices label="Division/State" wire:model.live="division_id" placeholder="Select Division"
                            single :options="$divisions" required wire:change="divisions" />
                        <x-choices label="District/City" wire:model="district_id" placeholder="Select District" single
                            :options="$districts" wire:change="districts" />
                    </div>
                    <x-devider title="Pricing Information" />
                    <div wire:ignore>
                        <label for="include_with_pricing" class="font-normal text-sm">Include With Pricing</label>
                        <textarea wire:model="include_with_pricing" id="include_with_pricing" cols="30" rows="10"></textarea>
                    </div>
                    <div wire:ignore class="mt-2">
                        <label for="exclude_with_pricing" class="font-normal text-sm">Exclude With Pricing</label>
                        <textarea wire:model="exclude_with_pricing" id="exclude_with_pricing" cols="30" rows="10"></textarea>
                    </div>
                </x-card>
            </div>
            <div class="col-span-1">
                <x-card>
                    <x-devider title="Additional Information" />
                    <div class="grid grid-cols-2 gap-4 mb-4">
                        <x-input type="number" label="Price 2 Hours" wire:model="price_2_hours"
                            placeholder="Price 2 Hours" required />
                        <x-input type="number" label="Price 4 Hours" wire:model="price_4_hours"
                            placeholder="Price 4 Hours" required />
                        <x-input type="number" label="Price Half Day" wire:model="price_half_day"
                            placeholder="Price Half Day" required />
                        <x-input type="number" label="Price Day" wire:model="price_day" placeholder="Price Day"
                            required />
                        <x-input type="number" label="Price Per Day" wire:model="price_per_day"
                            placeholder="Price Per Day" required />
                        <x-input type="number" label="Extra Time Cost By Hour" wire:model="extra_time_cost"
                            placeholder="Extra Time Cost By Hour" />
                        <x-input type="number" label="Extra Time Cost" wire:model="extra_time_cost_by_hour"
                            placeholder="Extra Time Cost" />
                        <x-choices label="Rental Type" wire:model="with_driver" placeholder="Select Status" single
                            :options="$rentalTypes" required />
                        <x-choices label="Status" wire:model="status" placeholder="Select Status" single
                            :options="$carStatus" required />
                    </div>
                    <x-file wire:model="image" label="Image" accept="images/png, images/jpeg,images/jpg"
                        class="mb-2" />
                    <x-checkbox label="Ac Facility" wire:model="ac_facility" class="w-4 h-4" />

                    <x-slot:actions>
                        <x-button label="Car List" link="/admin/vehicle/list" class="btn-sm" />
                        <x-button type="submit" label="Add Car" class="btn-primary btn-sm" spinner="storeCar" />
                    </x-slot:actions>
                </x-card>
            </div>
        </div>
    </x-form>
    @push('custom-script')
        <script>
            ClassicEditor
                .create(document.querySelector('#include_with_pricing'))
                .then(editor => {
                    editor.model.document.on('change:data', () => {
                        @this.set('include_with_pricing', editor.getData());
                    })
                })
                .catch(error => {
                    console.error(error);
                });
            ClassicEditor
                .create(document.querySelector('#exclude_with_pricing'))
                .then(editor => {
                    editor.model.document.on('change:data', () => {
                        @this.set('exclude_with_pricing', editor.getData());
                    })
                })
                .catch(error => {
                    console.error(error);
                });
        </script>
    @endpush
</div>
