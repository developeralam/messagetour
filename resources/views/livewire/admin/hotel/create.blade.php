<?php

use App\Models\Hotel;
use Mary\Traits\Toast;
use App\Models\Country;
use App\Models\District;
use App\Models\Division;
use App\Enum\HotelStatus;
use App\Enum\HotelType;
use App\Traits\InteractsWithImageUploads;
use Illuminate\Support\Str;
use Livewire\Volt\Component;
use Livewire\Attributes\Rule;
use Livewire\Attributes\Title;
use Mary\Traits\WithMediaSync;
use Livewire\Attributes\Layout;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Features\SupportFileUploads\WithFileUploads;

new #[Layout('components.layouts.admin')] #[Title('Add New Hotel')] class extends Component {
    use Toast, WithFileUploads, WithMediaSync, InteractsWithImageUploads;

    public Collection $countries;
    public Collection $divisions;
    public Collection $districts;
    public $types = [];
    public $statuses = [];

    #[Rule('required')]
    public $name;

    #[Rule('required')]
    public $address;

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

    #[Rule('nullable')]
    public $website;

    #[Rule('required')]
    public $checkin_time;

    #[Rule('required')]
    public $checkout_time;

    #[Rule('nullable')]
    public $is_featured;

    #[Rule('nullable')]
    public $description;

    #[Rule('required')]
    public $type = HotelType::Hotel;

    #[Rule('required')]
    public $thumbnail;

    #[Rule('required')]
    public $google_map_iframe;

    #[Rule('required')]
    public $status = HotelStatus::Active;

    // Temporary files
    #[Rule(['files.*' => 'image|max:1024'])]
    public array $files = [];

    // Library metadata (optional validation)
    #[Rule('required')]
    public Collection $library;

    public function mount()
    {
        $this->countries = Country::all();
        $this->divisions = collect();
        $this->districts = collect();
        $this->library = collect();
        $this->types = HotelType::getHotelTypes();
        $this->statuses = HotelStatus::getHotelStatuses();
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
    public function storeHotel()
    {
        $this->validate();
        try {
            $storedThumbnailPath = null;
            if ($this->thumbnail) {
                $storedThumbnailPath = $this->optimizeAndStoreImage(
                    $this->thumbnail, // The file from Livewire
                    'public', // The disk to store on
                    'hotel', // The subdirectory within the disk
                    null, // Optional max width
                    null, // Optional max height
                    75, // WEBP quality
                );
            }
            $hotel = Hotel::create([
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
                'checkin_time' => $this->checkin_time,
                'checkout_time' => $this->checkout_time,
                'is_featured' => $this->is_featured,
                'description' => $this->description,
                'type' => $this->type,
                'thumbnail' => $storedThumbnailPath,
                'google_map_iframe' => $this->google_map_iframe,
                'status' => $this->status,
            ]);
            //Upload Images
            $this->syncMedia(
                model: $hotel, // A model that has an image library
                library: 'library', // The library metadata property on component
                files: 'files', // Temp files property on component
                storage_subpath: 'hotel', // Sub path on storage. Ex: '/users'
                model_field: 'images', // The model column that represents the library metadata
                visibility: 'public', // Visibility on storage
                disk: 'public', // Storage disk. Also works with 's3'
            );
            $this->success('Hotel Added Successfully', redirectTo: '/admin/hotel/list');
        } catch (\Throwable $th) {
            $this->error(env('APP_DEBUG', false) ? $th->getMessage() : 'Something went wrong');
        }
    }
}; ?>

<div>
    @push('custom-script')
        <script src="https://cdn.ckeditor.com/ckeditor5/39.0.1/classic/ckeditor.js"></script>
    @endpush
    <x-header title="Add New Hotel" separator size="text-xl" class="bg-white px-2 pt-2">
        <x-slot:actions>
            <x-button label="Hotel List" icon="fas.arrow-left" link="/admin/hotel/list" class="btn-primary btn-sm" />
        </x-slot:actions>
    </x-header>
    <x-form wire:submit="storeHotel">
        <div class="grid grid-cols-3 gap-4 items-start" x-cloak>
            <div class="col-span-2">
                <x-card>
                    <x-devider title="Hotel Information" />
                    <div class="grid grid-cols-3 gap-2">
                        <x-input label="Hotel Name" class="mb-4" wire:model="name" placeholder="Hotel Name"
                            required />
                        <x-input label="Hotel Address" class="mb-4" wire:model="address" placeholder="Ex:Cox's Bazar"
                            required />
                        <x-input label="Phone" class="mb-4" wire:model="phone" placeholder="Phone" required />
                        <x-input label="Email" class="mb-4" wire:model="email" placeholder="Email" required />
                        <x-input label="Zip Code" class="mb-4" wire:model="zipcode" placeholder="Ex: 1703" />
                        <x-input label="Website" class="mb-4" wire:model="website"
                            placeholder="Ex: https://example.com" />

                    </div>
                </x-card>
                <x-card class="mt-2">
                    <x-devider title="Hotel Location, Description, Map" />
                    <div class="grid grid-cols-3 gap-2 mb-4">
                        <x-choices label="Country" :options="$countries" single required wire:model.live="country_id"
                            placeholder="Select One" />
                        <x-choices label="Division" :options="$divisions" single required wire:model.live="division_id"
                            placeholder="Select One" />
                        <x-choices label="District" :options="$districts" single required wire:model="district_id"
                            placeholder="Select One" />

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
                    <div class="grid grid-cols-2 gap-2 mb-4">
                        <x-choices label="Hotel Type" :options="$types" required wire:model="type" single />
                        <x-choices label="Hotel Status" :options="$statuses" required wire:model="status" single
                            placeholder="Select Status" />
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
                    <x-image-library wire:model="files" {{-- Temprary files --}} wire:library="library"
                        {{-- Library metadata property --}} :preview="$library" {{-- Preview control --}} label="Hotel Images"
                        hint="Max 100Kb" change-text="Change" remove-text="Remove" add-files-text="Add Hotel Images" />
                    <x-slot:actions>
                        <x-button label="Hotel List" link="/admin/hotel/list" class="btn-sm" />
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
