<?php

use Mary\Traits\Toast;
use App\Models\Aminities;
use App\Models\HotelRoom;
use App\Enum\HotelRoomType;
use Illuminate\Support\Str;
use Livewire\Volt\Component;
use Livewire\Attributes\Rule;
use Livewire\WithFileUploads;
use Livewire\Attributes\Title;
use Mary\Traits\WithMediaSync;
use Livewire\Attributes\Layout;
use Illuminate\Support\Collection;
use App\Traits\InteractsWithImageUploads;

new #[Layout('components.layouts.partner')] #[Title('Update Room')] class extends Component {
    use WithFileUploads, Toast, InteractsWithImageUploads, WithMediaSync;
    public Collection $aminities;
    public $room = [];
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

    #[Rule('nullable')]
    public $thumbnail;

    #[Rule('nullable')]
    public array $aminities_ids;

    #[Rule(['files.*' => 'image|max:1024'])]
    public array $files = [];

    // Library metadata (optional validation)
    #[Rule('nullable')]
    public Collection $library;

    public function mount($hotel, $room)
    {
        $response = HotelRoom::with('hotel')->find($room);
        if ($response) {
            $this->room = $response;
            $this->name = $response->name;
            $this->room_no = $response->room_no;
            $this->slug = Str::slug($this->name);
            $this->type = $response->type;
            $this->room_size = $response->room_size;
            $this->max_occupancy = $response->max_occupancy;
            $this->regular_price = $response->regular_price;
            $this->offer_price = $response->offer_price;
            $this->library = $response->images ?? collect();
            $this->refreshExistingImages();
            $this->aminitiesIds = $response->aminities->pluck('id')->toArray();
            $this->aminities();
        }
        $this->types = HotelRoomType::getHotelRoomTypes();
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
    public function aminities()
    {
        $this->aminities = Aminities::all();
    }
    public function updateRoom()
    {
        $this->validate();
        try {
            $storedThumbnailPath = null;
            if ($this->thumbnail) {
                $storedThumbnailPath = $this->optimizeAndUpdateImage(
                    $this->thumbnail,
                    $this->room->thumbnail, // old path
                    'public',
                    'room',
                    null,
                    null,
                    75,
                );
            } else {
                $storedThumbnailPath = $this->room->thumbnail;
            }
            $this->room->update([
                'name' => $this->name,
                'room_no' => $this->room_no,
                'slug' => Str::slug($this->name),
                'type' => $this->type,
                'room_size' => $this->room_size,
                'max_occupancy' => $this->max_occupancy,
                'regular_price' => $this->regular_price,
                'offer_price' => $this->offer_price,
                'thumbnail' => $storedThumbnailPath,
                'action_id' => auth()->user()->id,
            ]);
            //store aminities
            $this->room->aminities()->sync($this->aminitiesIds);

            //Upload Images
            $this->syncMedia(
                model: $this->room, // A model that has an image library
                library: 'library', // The library metadata property on component
                files: 'files', // Temp files property on component
                storage_subpath: 'hotel/room', // Sub path on storage. Ex: '/users'
                model_field: 'images', // The model column that represents the library metadata
                visibility: 'public', // Visibility on storage
                disk: 'public', // Storage disk. Also works with 's3'
            );
            $this->success('Room Updated Successfully');
            return redirect('/partner/hotel/' . $this->room->hotel_id . '/room/list');
        } catch (\Throwable $th) {
            $this->error($th->getMessage());
        }
    }
}; ?>

<div>
    <x-header title="Update {{ $room->room_no }} In {{ $room->hotel->name }} Hotel" separator size="text-xl" class="bg-white px-2 pt-2">
        <x-slot:actions>
            <x-button label="Room List" icon="fas.arrow-left" link="/partner/hotel/{{ $room->hotel_id }}/room/list" class="btn-primary btn-sm" />
        </x-slot>
    </x-header>
    <x-form wire:submit="updateRoom">
        <div class="grid grid-cols-3 gap-4 items-start" x-cloak>
            <x-card class="col-span-2">
                <x-devider title="Room Information" />
                <div class="grid grid-cols-2 gap-4 pb-6">
                    <x-input label="Room Name" wire:model="name" placeholder="Room Name" />
                    <x-input label="Room No" wire:model="room_no" placeholder="Room No" />
                    <x-choices label="Room type" :options="$types" single wire:model.live="type" placeholder="Select One" />
                    <x-input label="Room Size" wire:model="room_size" placeholder="Ex: 200 Squre feet" />
                    <x-input type="number" label=" Max Occupancy" wire:model="max_occupancy" placeholder="Ex: 2 Person" />
                    <x-input type="number" label="Regular Price" wire:model="regular_price" placeholder="Regular Price" />
                    <x-input type="number" label="Offer Price" wire:model="offer_price" placeholder="Offer Price" />
                </div>
            </x-card>
            <div class="col-span-1">
                <x-card>
                    <x-devider title="Additional Information" />
                    <x-choices label="Aminities" class="mb-4 overflow-hidden" :options="$aminities" wire:model="aminitiesIds"
                        placeholder="Select Aminities" />
                    <x-file label="Thumbnail" wire:model="thumbnail" accept="image/png, image/jpeg">
                        <img src="{{ $room->thumbnail_link }}" alt="" class="h-20 rounded-lg" />
                    </x-file>
                </x-card>
                <x-card class="mt-4">
                    <x-devider title="More images" />
                    @php
                        $config = ['guides' => false];
                    @endphp

                    <x-image-library wire:model="files" :crop-config="$config" {{-- Temprary files --}} wire:library="library" {{-- Library metadata property --}}
                        :preview="$library" {{-- Preview control --}} label="Room Images" hint="Max 100Kb" change-text="Change" crop-text="Crop"
                        remove-text="Remove" crop-title-text="Crop image" crop-cancel-text="Cancel" crop-save-text="Crop"
                        add-files-text="Add room images" />

                    <x-menu-separator />
                    <x-slot:actions>
                        <x-button label="Room List" link="/partner/hotel/{{ $room->hotel->id }}/room/list" class="btn-sm" />
                        <x-button type="submit" label="Update Room" class="btn-primary btn-sm" spinner="updateRoom" />
                    </x-slot>
                </x-card>
            </div>
        </div>
    </x-form>
</div>
