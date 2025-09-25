<?php

use App\Models\Hotel;
use Mary\Traits\Toast;
use App\Enum\HotelType;
use App\Models\Country;
use App\Models\District;
use App\Models\Division;
use App\Enum\HotelStatus;
use App\Enum\CountryStatus;
use Illuminate\Support\Str;
use Livewire\Volt\Component;
use Livewire\Attributes\Rule;
use Livewire\Attributes\Title;
use Mary\Traits\WithMediaSync;
use Livewire\Attributes\Layout;
use Illuminate\Support\Collection;
use App\Traits\InteractsWithImageUploads;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Features\SupportFileUploads\WithFileUploads;

new #[Layout('components.layouts.partner')] #[Title('Hotel Edit')] class extends Component {
    use Toast, WithFileUploads, WithMediaSync, InteractsWithImageUploads;

    public Collection $countries;
    public Collection $divisions;
    public Collection $districts;
    public $types = [];
    public $hotel_status = [];

    public Hotel $hotel;

    public $hotel_id;

    #[Rule('required')]
    public string $name = '';

    #[Rule('required')]
    public string $address = '';

    #[Rule('required')]
    public $country_id;

    #[Rule('required')]
    public $division_id;

    #[Rule('required')]
    public $district_id;

    #[Rule('nullable')]
    public $zipcode;

    #[Rule('required')]
    public $phone;

    #[Rule('required')]
    public $email;

    #[Rule('required')]
    public $website;

    #[Rule('required')]
    public $checkin_time;

    #[Rule('required')]
    public $checkout_time;

    #[Rule('required')]
    public $is_featured;

    #[Rule('nullable')]
    public $description;

    #[Rule('required')]
    public $type;

    #[Rule('required')]
    public $status = HotelStatus::Pending;

    #[Rule('nullable')]
    public $thumbnail;

    #[Rule('required')]
    public $google_map_iframe;

    // Temporary files
    #[Rule(['files.*' => 'image|max:1024'])]
    public array $files = [];

    #[Rule('nullable')]
    public Collection $library;

    public function mount()
    {
        if ($this->hotel) {
            $this->countries = Country::where('status', CountryStatus::Active)->get();
            $this->name = $this->hotel->name;
            $this->address = $this->hotel->address;
            $this->country_id = $this->hotel->country_id;
            $this->divisions();
            $this->division_id = $this->hotel->division_id;
            $this->districts();
            $this->district_id = $this->hotel->district_id;
            $this->zipcode = $this->hotel->zipcode;
            $this->phone = $this->hotel->phone;
            $this->email = $this->hotel->email;
            $this->website = $this->hotel->website;
            $this->checkin_time = $this->hotel->checkin_time->format('H:i');
            $this->checkout_time = $this->hotel->checkout_time->format('H:i');
            $this->is_featured = $this->hotel->is_featured;
            $this->description = $this->hotel->description;
            $this->type = $this->hotel->type;
            $this->google_map_iframe = $this->hotel->google_map_iframe;
            $this->status = $this->hotel->status;
            $this->library = $this->hotel->images ?? collect();
            $this->refreshExistingImages();
            $this->types = HotelType::getHotelTypes();
            $this->hotel_status = HotelStatus::getHotelStatuses();
        }
    }

    public function refreshExistingImages()
    {
        if ($this->library && $this->library->isNotEmpty()) {
            $this->library = $this->library->map(function ($image) {
                if (isset($image['url'])) {
                    // Ensure URL is properly formatted
                    if (!str_starts_with($image['url'], 'http')) {
                        $image['url'] = url('storage/' . $image['url']);
                    }
                }
                return $image;
            });
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

    public function countrySearch(string $search = '')
    {
        $searchTerm = '%' . $search . '%';

        $citizen = Country::where('status', CountryStatus::Active)->where('name', 'like', $searchTerm)->limit(5)->get();

        $this->countries = $citizen;
    }

    public function divisionSearch(string $search = '')
    {
        $searchTerm = '%' . $search . '%';
        $divisions = Division::where('country_id', $this->country_id)->where('name', 'like', $searchTerm)->limit(5)->get();

        $this->divisions = $divisions;
    }

    public function districtSearch(string $search = '')
    {
        $searchTerm = '%' . $search . '%';
        $districts = District::where('division_id', $this->division_id)->where('name', 'like', $searchTerm)->limit(5)->get();

        $this->districts = $districts;
    }
    public function updated($property)
    {
        match ($property) {
            'country_id' => $this->divisions(),
            'division_id' => $this->districts(),
            default => null,
        };
    }
    public function updateHotel()
    {
        $this->validate();
        try {
            $storedThumbnailPath = null;
            if ($this->thumbnail) {
                $storedThumbnailPath = $this->optimizeAndUpdateImage(
                    $this->thumbnail,
                    $this->hotel->thumbnail, // old path
                    'public',
                    'hotel',
                    null,
                    null,
                    75,
                );
            } else {
                $storedThumbnailPath = $this->hotel->thumbnail;
            }
            $this->hotel->update([
                'name' => $this->name,
                'slug' => Str::slug($this->name),
                'address' => $this->address,
                'country_id' => $this->country_id,
                'division_id' => $this->division_id,
                'district_id' => $this->district_id,
                'zipcode' => $this->zipcode,
                'phone' => $this->phone,
                'email' => $this->email,
                'website' => $this->website,
                'checkin_time' => $this->hotel->checkin_time,
                'checkout_time' => $this->hotel->checkout_time,
                'checkout_time' => $this->checkout_time,
                'is_featured' => $this->is_featured,
                'description' => $this->description,
                'type' => $this->type,
                'thumbnail' => $storedThumbnailPath,
                'google_map_iframe' => $this->google_map_iframe,
                'status' => $this->status,
                'action_id' => auth()->user()->id,
            ]);
            //Upload Images
            $this->syncMedia(
                model: $this->hotel, // A model that has an image library
                library: 'library', // The library metadata property on component
                files: 'files', // Temp files property on component
                storage_subpath: 'hotel', // Sub path on storage. Ex: '/users'
                model_field: 'images', // The model column that represents the library metadata
                visibility: 'public', // Visibility on storage
                disk: 'public', // Storage disk. Also works with 's3'
            );
            $this->success('Hotel Updated Successfully', redirectTo: '/partner/hotel/list');
        } catch (\Throwable $th) {
            $this->error(env('APP_DEBUG', false) ? $th->getMessage() : 'Something went wrong');
        }
    }
}; ?>

<div>
    @push('custom-script')
        <script src="https://cdn.ckeditor.com/ckeditor5/39.0.1/classic/ckeditor.js"></script>
    @endpush

    <x-header title="Update Hotel Details" size="text-xl" separator class="bg-white px-2 pt-2">
        <x-slot:actions>
            <x-button label="Hotel List" icon="fas.arrow-left" link="/partner/hotel/list" class="btn-primary btn-sm" />
        </x-slot>
    </x-header>
    <x-form wire:submit="updateHotel">
        <div class="grid grid-cols-3 gap-4 items-start" x-cloak>
            <div class="col-span-2">
                <x-card>
                    <x-devider title="Hotel Information" />
                    <div class="grid grid-cols-3 gap-2">
                        <x-input label="Hotel Name" class="mb-4" wire:model="name" placeholder="Hotel Name" required />
                        <x-input label="Hotel Address" class="mb-4" wire:model="address" placeholder="Ex:Cox's Bazar" required />
                        <x-input label="Phone" class="mb-4" wire:model="phone" placeholder="Phone" required />
                        <x-input label="Email" class="mb-4" wire:model="email" placeholder="Email" required />
                        <x-input label="Zip Code" class="mb-4" wire:model="zipcode" placeholder="Ex: 1703" />
                        <x-input label="Website" class="mb-4" wire:model="website" placeholder="Ex: https://example.com" />

                    </div>
                </x-card>
                <x-card class="mt-2">
                    <x-devider title="Hotel Location, Description, Map" />
                    <div class="grid grid-cols-3 gap-2 mb-4">
                        <x-choices wire:model.live="country_id" :options="$countries" label="Country" placeholder="Select Country" single required
                            search-function="countrySearch" searchable />
                        <x-choices wire:model.live="division_id" :options="$divisions" label="Division" placeholder="Select Division" single required
                            search-function="divisionSearch" searchable />
                        <x-choices wire:model.live="district_id" :options="$districts" label="District" placeholder="Select District"
                            search-function="districtSearch" searchable single required />

                    </div>
                    <div wire:ignore class="mb-4">
                        <label for="description" class="font-normal text-sm">Hotel Description</label>
                        <textarea wire:model="description" id="description" cols="30" rows="10"></textarea>
                    </div>
                    <x-textarea label="Google Map Iframe" wire:model="google_map_iframe" required />
                </x-card>
            </div>
            <div class="col-span-1">
                <x-card>
                    <x-devider title="Additional Information" />
                    <div class="{{ $status != HotelStatus::Pending ? 'grid grid-cols-2 gap-2 mb-4' : 'grid grid-cols-1 mb-4' }}">

                        <x-choices label="Hotel Type" :options="$types" required wire:model="type" single class="mb-4" />

                        @if ($status != HotelStatus::Pending)
                            <x-choices label="Hotel Status" :options="collect($hotel_status)
                                ->filter(fn($status) => $status['name'] != 'Pending' || $status['id'] == $status)
                                ->values()
                                ->all()" wire:model="status" single placeholder="Select Status" required />
                        @endif
                    </div>
                    <div class="grid grid-cols-2 gap-2 mb-4">
                        <x-datetime label="Check In Time" type="time" wire:model="checkin_time" required />
                        <x-datetime label="Check Out Time" type="time" wire:model="checkout_time" required />
                    </div>
                    <x-checkbox label="Is Featured?" wire:model="is_featured" class="w-4 h-4" />
                </x-card>
                <x-card>
                    <x-devider title="Hotel Images" />
                    <x-file class="mb-4" label="Thumbnail" wire:model="thumbnail" required>
                        <img src="{{ asset('empty-hotel.png') }}" alt="" class="h-20 rounded-lg">
                    </x-file>
                    <x-image-library wire:model="files" {{-- Temprary files --}} wire:library="library" {{-- Library metadata property --}} :preview="$library"
                        {{-- Preview control --}} label="Hotel Images" hint="Max 100Kb" change-text="Change" remove-text="Remove"
                        add-files-text="Add Hotel Images" />
                    <x-slot:actions>
                        <x-button label="Hotel List" link="/partner/hotel/list" class="btn-sm" />
                        <x-button type="submit" label="Save Hotel" class="btn-primary btn-sm" />
                    </x-slot:actions>
                </x-card>

            </div>
        </div>
    </x-form>
    @push('custom-script')
        <script>
            ClassicEditor
                .create(document.querySelector('#description'))
                .then(editor => {
                    editor.model.document.on('change:data', () => {
                        @this.set('description', editor.getData());
                    })
                })
                .catch(error => {
                    console.error(error);
                });
        </script>
    @endpush
</div>
