<?php

use App\Models\Hotel;
use Mary\Traits\Toast;
use Illuminate\Support\Str;
use Livewire\Volt\Component;
use App\Enum\HotelRoomStatus;
use App\Enum\HotelRoomType;
use App\Models\Aminities;
use App\Traits\InteractsWithImageUploads;
use Illuminate\Support\Collection;
use Livewire\Attributes\Rule;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;
use Livewire\WithFileUploads;
use Mary\Traits\WithMediaSync;

new #[Layout('components.layouts.partner')] #[Title('Add New Room')] class extends Component {
    use WithFileUploads, Toast, InteractsWithImageUploads, WithMediaSync;
    public Collection $aminities;
    public $hotel = [];
    public $types = [];

    #[Rule('nullable')]
    public $aminitiesIds = [];

    #[Rule('required')]
    public $name;

    #[Rule('required')]
    public $room_no;

    #[Rule('required')]
    public $type;

    #[Rule('required')]
    public $room_size;

    #[Rule('nullable|integer')]
    public $max_occupancy;

    #[Rule('required|integer')]
    public $regular_price;

    #[Rule('nullable|integer')]
    public $offer_price;

    #[Rule('required')]
    public $thumbnail;

    #[Rule(['files.*' => 'image|max:1024'])]
    public array $files = [];

    // Library metadata (optional validation)
    #[Rule('required')]
    public Collection $library;

    public function mount($hotel)
    {
        $response = Hotel::find($hotel);
        if ($response) {
            $this->hotel = $response;
            $this->aminities();
        }
        $this->library = collect();

        $this->types = HotelRoomType::getHotelRoomTypes();
    }
    public function aminities()
    {
        $this->aminities = Aminities::all();
    }
    public function storeRoom()
    {
        $this->validate();
        try {
            $storedThumbnailPath = null;
            if ($this->thumbnail) {
                $storedThumbnailPath = $this->optimizeAndStoreImage(
                    $this->thumbnail, // The file from Livewire
                    'public', // The disk to store on
                    'room', // The subdirectory within the disk
                    null, // Optional max width
                    null, // Optional max height
                    75, // WEBP quality
                );
            }
            $room = $this->hotel->rooms()->create([
                'name' => $this->name,
                'room_no' => $this->room_no,
                'slug' => Str::slug($this->name),
                'type' => $this->type,
                'room_size' => $this->room_size,
                'max_occupancy' => $this->max_occupancy,
                'regular_price' => $this->regular_price,
                'offer_price' => $this->offer_price,
                'thumbnail' => $storedThumbnailPath,
                'status' => HotelRoomStatus::Available,
                'created_by' => auth()->user()->id,
            ]);

            //Upload Images
            $this->syncMedia(
                model: $room, // A model that has an image library
                library: 'library', // The library metadata property on component
                files: 'files', // Temp files property on component
                storage_subpath: 'hotel/room', // Sub path on storage. Ex: '/users'
                model_field: 'images', // The model column that represents the library metadata
                visibility: 'public', // Visibility on storage
                disk: 'public', // Storage disk. Also works with 's3'
            );

            //Store Aminities
            $room->aminities()->attach($this->aminitiesIds);
            $this->success('Room Added Successfully');
            return redirect('/partner/hotel/' . $this->hotel->id . '/room/list');
        } catch (\Throwable $th) {
            $this->error($th->getMessage());
        }
    }
}; ?>

<div>
    <x-header title="Add New Room In - {{ $hotel->name }} Hotel" separator size="text-xl" class="bg-white px-2 pt-2">
        <x-slot:actions>
            <x-button label="Room List" icon="fas.arrow-left" link="/partner/hotel/{{ $hotel->id }}/room/list"
                class="btn-primary btn-sm" />
        </x-slot:actions>
    </x-header>
    <x-form wire:submit="storeRoom">
        <div class="grid grid-cols-3 gap-4 items-start" x-cloak>
            <x-card class="col-span-2">
                <x-devider title="Room Information" />
                <div class="grid grid-cols-2 gap-4 pb-6">
                    <x-input label="Room Name" wire:model="name" placeholder="Room Name" required />
                    <x-input label="Room No" wire:model="room_no" placeholder="Room No" required />
                    <x-choices label="Room Type" :options="$types" single wire:model.live="type"
                        placeholder="Select One" required />
                    <x-input label="Room Size" wire:model="room_size" placeholder="Ex: 200 Squre feet" />
                    <x-input type="number" label=" Max Occupancy" wire:model="max_occupancy"
                        placeholder="Ex: 2 Person" />
                    <x-input type="number" label="Regular Price" wire:model="regular_price" placeholder="Regular Price"
                        required />
                    <x-input type="number" label="Offer Price" wire:model="offer_price" placeholder="Offer Price" />
                </div>
            </x-card>
            <div class="col-span-1">
                <x-card>
                    <x-devider title="Additional Information" />
                    <x-choices label="Aminities" class="mb-4 overflow-hidden" :options="$aminities"
                        wire:model="aminitiesIds" placeholder="Select Aminities" />
                    <x-file label="Thumbnail" wire:model="thumbnail" accept="image/png, image/jpeg" required>
                        <img src="{{ asset('empty-product.png') }}" alt="" class="h-20 rounded-lg" />
                    </x-file>
                </x-card>
                <x-card class="mt-4">
                    <x-devider title="More images" />
                    @php
                        $config = ['guides' => false];
                    @endphp

                    <x-image-library wire:model="files" :crop-config="$config" {{-- Temprary files --}} wire:library="library"
                        {{-- Library metadata property --}} :preview="$library" {{-- Preview control --}} label="Room Images"
                        hint="Max 100Kb" change-text="Change" crop-text="Crop" remove-text="Remove"
                        crop-title-text="Crop image" crop-cancel-text="Cancel" crop-save-text="Crop"
                        add-files-text="Add room images" />

                    <x-menu-separator />
                    <x-slot:actions>
                        <x-button label="Room List" link="/partner/hotel/{{ $hotel->id }}/room/list"
                            class="btn-sm" />
                        <x-button type="submit" label="Add Room" class="btn-primary btn-sm" spinner="storeRoom" />
                    </x-slot>
                </x-card>
            </div>
        </div>
    </x-form>
</div>
