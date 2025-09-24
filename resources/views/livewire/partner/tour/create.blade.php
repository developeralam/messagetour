<?php

use App\Models\Tour;
use App\Enum\TourType;
use Mary\Traits\Toast;
use App\Models\Country;
use App\Enum\TourStatus;
use App\Models\District;
use App\Models\Division;
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

new #[Layout('components.layouts.partner')] #[Title('Add New Tour')] class extends Component {
    use Toast, WithFileUploads, InteractsWithImageUploads, WithMediaSync;

    public Collection $countries;
    public Collection $divisions;
    public Collection $districts;
    public array $tour_types;
    public array $tour_status;

    #[Rule('required')]
    public $title;

    #[Rule('required')]
    public $location;

    #[Rule('required')]
    public $start_date;

    #[Rule('required')]
    public $end_date;

    #[Rule('required')]
    public $validity;

    #[Rule('required')]
    public $member_range;

    #[Rule('required')]
    public $minimum_passenger;

    #[Rule('nullable')]
    public $description;

    #[Rule('required')]
    public $country_id;

    #[Rule('required')]
    public $division_id;

    #[Rule('required')]
    public $district_id;

    #[Rule('nullable')]
    public $is_featured = false;

    #[Rule('required')]
    public $type = TourType::Tour;

    #[Rule('required')]
    public $status = TourStatus::Active;

    #[Rule('required')]
    public $regular_price;

    #[Rule('nullable')]
    public $offer_price;

    #[Rule('required')]
    public $thumbnail;

    // Temporary files
    #[Rule(['files.*' => 'image|max:1024'])]
    public array $files = [];

    // Library metadata (optional validation)
    #[Rule('required')]
    public Collection $library;

    public function mount()
    {
        $this->countries = Country::where('status', CountryStatus::Active)->get();
        $this->divisions = collect();
        $this->districts = collect();
        $this->library = collect();
        $this->tour_types = TourType::getTourTypes();
        $this->tour_status = TourStatus::getTourStatuses();
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

        $countries = Country::where('status', CountryStatus::Active)->where('name', 'like', $searchTerm)->limit(5)->get();

        $this->countries = $countries;
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
    public function storeTour()
    {
        $this->validate();
        try {
            $storedThumbnailPath = null;
            if ($this->thumbnail) {
                $storedThumbnailPath = $this->optimizeAndStoreImage(
                    $this->thumbnail, // The file from Livewire
                    'public', // The disk to store on
                    'tour', // The subdirectory within the disk
                    null, // Optional max width
                    null, // Optional max height
                    75, // WEBP quality
                );
            }
            $tour = Tour::create([
                'title' => $this->title,
                'slug' => Str::slug($this->title),
                'location' => $this->location,
                'start_date' => $this->start_date,
                'end_date' => $this->end_date,
                'validity' => $this->validity,
                'member_range' => $this->member_range,
                'minimum_passenger' => $this->minimum_passenger,
                'description' => $this->description,
                'country_id' => $this->country_id,
                'division_id' => $this->division_id,
                'district_id' => $this->district_id,
                'is_featured' => $this->is_featured,
                'type' => $this->type,
                'regular_price' => $this->regular_price,
                'offer_price' => $this->offer_price,
                'thumbnail' => $storedThumbnailPath,
                'created_by' => auth()->user()->id,
                'status' => TourStatus::Pending,
            ]);
            //Upload Images
            $this->syncMedia(
                model: $tour, // A model that has an image library
                library: 'library', // The library metadata property on component
                files: 'files', // Temp files property on component
                storage_subpath: 'tour', // Sub path on storage. Ex: '/users'
                model_field: 'images', // The model column that represents the library metadata
                visibility: 'public', // Visibility on storage
                disk: 'public', // Storage disk. Also works with 's3'
            );
            $this->success('Tour Added Successfully', redirectTo: '/partner/tour/list');
        } catch (\Throwable $th) {
            $this->error($th->getMessage());
        }
    }
}; ?>

<div>
    @push('custom-script')
        <script src="https://cdn.ckeditor.com/ckeditor5/39.0.1/classic/ckeditor.js"></script>
    @endpush
    <x-header title="Add New Tour" size="text-xl" separator class="bg-white px-2 pt-2">
        <x-slot:actions>
            <x-button label="Tour List" icon="fas.arrow-left" link="/partner/tour/list" class="btn-primary btn-sm" />
        </x-slot:actions>
    </x-header>
    <x-form wire:submit="storeTour">
        <div class="grid grid-cols-3 gap-4 items-start" x-cloak>
            <x-card class="col-span-2">
                <x-devider title="Tour Information" />
                <div class="grid grid-cols-2 gap-2 mb-4">
                    <x-input label="Tour Title" wire:model="title" placeholder="Tour Title" required />
                    <x-input label="Tour Location" wire:model="location" placeholder="Tour Location" required />
                </div>
                <div class="grid grid-cols-3 gap-2 mb-4">
                    <x-choices wire:model.live="country_id" :options="$countries" label="Country" placeholder="Select Country" single required
                        search-function="countrySearch" searchable />
                    <x-choices wire:model.live="division_id" :options="$divisions" label="Division" placeholder="Select Division" single required
                        search-function="divisionSearch" searchable />
                    <x-choices wire:model.live="district_id" :options="$districts" label="District" placeholder="Select District" single required
                        search-function="districtSearch" searchable />
                    <x-datetime label="Start Date" type="date" wire:model="start_date" required />
                    <x-datetime label="End Date" type="date" wire:model="end_date" required />
                    <x-datetime label="Tour Validity" type="date" wire:model="validity" required />
                </div>
                <div class="grid grid-cols-2 gap-2 mb-4">
                    <x-input label="Regular Price" wire:model="regular_price" placeholder="Regular Price" type="number" required />
                    <x-input label="Offer Price" wire:model="offer_price" placeholder="Offer Price" type="number" />
                </div>
                <div wire:ignore class="pb-6">
                    <label for="description" class="font-normal text-sm">Tour Description</label>
                    <textarea wire:model="description" id="description" cols="30" rows="10"></textarea>
                </div>
            </x-card>
            <div class="col-span-1">
                <x-card>
                    <x-devider title="Additional Information" />
                    <div class="grid grid-cols-2 gap-2 mb-4">
                        <x-choices label="Tour Type" :options="$tour_types" wire:model="type" single placeholder="Select Type" required />
                        <x-choices label="Tour Status" :options="$tour_status" wire:model="status" single placeholder="Select Status" required />
                    </div>

                    <div class="mb-4">
                        <x-checkbox label="Is Featured?" wire:model="is_featured" class="w-4 h-4" />
                    </div>

                    <div class="grid grid-cols-2 gap-2 mb-4">
                        <x-input label="Member Range" wire:model="member_range" placeholder="Member Range" type="number" required />
                        <x-input label="Minimum Passenger" wire:model="minimum_passenger" placeholder="Minimum Passenger" type="number" required />
                    </div>

                    <x-file label="Thumbnail" wire:model="thumbnail" accept="image/png, image/jpeg" required>
                        <img src="{{ asset('empty-product.png') }}" alt="" class="h-20 rounded-lg" />
                    </x-file>
                </x-card>
                <x-card class="mt-4">
                    <x-devider title="More images" />
                    @php
                        $config = ['guides' => false];
                    @endphp

                    <x-image-library wire:model="files" :crop-config="$config" {{-- Temprary files --}} wire:library="library" {{-- Library metadata property --}}
                        :preview="$library" {{-- Preview control --}} label="Tour images" hint="Max 100Kb" change-text="Change" crop-text="Crop"
                        remove-text="Remove" crop-title-text="Crop image" crop-cancel-text="Cancel" crop-save-text="Crop"
                        add-files-text="Add tour images" />

                    <x-slot:actions>
                        <x-button label="Tour List" link="/partner/tour/list" class="btn-sm" />
                        <x-button type="submit" label="Save Tour" class="btn-primary btn-sm" />
                    </x-slot>
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
