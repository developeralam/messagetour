<?php

use App\Models\GroupFlight;
use App\Enum\GroupFlightStatus;
use App\Traits\InteractsWithImageUploads;
use Illuminate\Support\Str;
use Livewire\Volt\Component;
use Livewire\Attributes\Rule;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;
use Illuminate\Support\Collection;
use Livewire\Features\SupportFileUploads\WithFileUploads;
use Mary\Traits\Toast;

new #[Layout('components.layouts.admin')] #[Title('Update Group Flight')] class extends Component {
    use Toast, WithFileUploads, InteractsWithImageUploads;

    #[Rule('required')]
    public $title;

    #[Rule('nullable')]
    public $thumbnail;

    #[Rule('nullable')]
    public $description;

    #[Rule('required')]
    public $type;

    #[Rule('required')]
    public $journey_route;

    #[Rule('required')]
    public $journey_transit;

    #[Rule('required')]
    public $return_route;

    #[Rule('required')]
    public $return_transit;

    #[Rule('required')]
    public $journey_date;

    #[Rule('required')]
    public $return_date;

    #[Rule('required')]
    public $airline_name;

    #[Rule('required')]
    public $airline_code;

    #[Rule('required')]
    public $baggage_weight;

    #[Rule('nullable')]
    public $is_food;

    #[Rule('required')]
    public $available_seat;

    #[Rule('required')]
    public $status;

    public GroupFlight $groupflight;

    public function mount($groupflight)
    {
        $this->groupflight = $groupflight;
        $this->title = $groupflight->title;
        $this->slug = $groupflight->slug;
        $this->description = $groupflight->description;
        $this->type = $groupflight->type;
        $this->journey_route = $groupflight->journey_route;
        $this->journey_transit = $groupflight->journey_transit;
        $this->return_route = $groupflight->return_route;
        $this->return_transit = $groupflight->return_transit;
        $this->journey_date = $groupflight->journey_date->format('Y-m-d');
        $this->return_date = $groupflight->return_date->format('Y-m-d');
        $this->airline_name = $groupflight->airline_name;
        $this->airline_code = $groupflight->airline_code;
        $this->baggage_weight = $groupflight->baggage_weight;
        $this->is_food = $groupflight->is_food;
        $this->available_seat = $groupflight->available_seat;
        $this->status = $groupflight->status;
    }
    public function updateGroupFlight()
    {
        $this->validate();
        try {
            $storedThumbnailPath = null;
            if ($this->thumbnail) {
                $storedThumbnailPath = $this->optimizeAndUpdateImage(
                    $this->thumbnail,
                    $this->groupflight->thumbnail, // old path
                    'public',
                    'group-flight',
                    null,
                    null,
                    75,
                );
            } else {
                $storedThumbnailPath = $this->groupflight->thumbnail;
            }

            $this->groupflight->update([
                'title' => $this->title,
                'slug' => Str::slug($this->title),
                'thumbnail' => $storedThumbnailPath,
                'description' => $this->description,
                'type' => $this->type,
                'journey_route' => $this->journey_route,
                'journey_transit' => $this->journey_transit,
                'return_route' => $this->return_route,
                'return_transit' => $this->return_transit,
                'journey_date' => $this->journey_date,
                'return_date' => $this->return_date,
                'airline_name' => $this->airline_name,
                'airline_code' => $this->airline_code,
                'baggage_weight' => $this->baggage_weight,
                'is_food' => $this->is_food,
                'available_seat' => $this->available_seat,
                'status' => $this->status,
            ]);
            $this->success('Group Flight Update Successfully', redirectTo: '/admin/group-flight/list');
        } catch (\Throwable $th) {
            $this->error(env('APP_DEBUG', false) ? $th->getMessage() : 'Something went wrong');
        }
    }
}; ?>

<div>
    @push('custom-script')
        <script src="https://cdn.ckeditor.com/ckeditor5/39.0.1/classic/ckeditor.js"></script>
    @endpush
    <x-header title="Update Group Flight - {{ $groupflight->title }}" size="text-xl" separator class="bg-white px-2 pt-2">
        <x-slot:actions>
            <x-button label="Group Flight List" icon="fas.arrow-left" link="/admin/group-flight/list"
                class="btn-primary btn-sm" />
        </x-slot:actions>
    </x-header>
    <x-form wire:submit="updateGroupFlight">
        <div class="grid grid-cols-3 gap-4" x-cloak>
            <div class="col-span-2 flex flex-col h-full">
                <x-card class="flex-grow">
                    <x-devider title="Group Flight Information" />
                    <div class="grid grid-cols-2 gap-2">
                        <x-input label="Title" wire:model="title" placeholder="Dac-Kul" required />
                        @php
                            $types = [['id' => 0, 'name' => 'Regular'], ['id' => 1, 'name' => 'Umrah']];
                        @endphp
                        <x-choices label="Type" class="mb-2" single :options="$types" wire:model="type"
                            placeholder="Select One" required />
                    </div>
                    <div wire:ignore>
                        <label for="description" class="font-normal text-sm">Description</label>
                        <textarea wire:model="description" id="description" cols="30" rows="10">{{ $groupflight->description }}</textarea>
                    </div>
                </x-card>
                <x-card class="mt-3 flex-grow">
                    <x-devider title="Route & Transit Information" />
                    <div class="grid grid-cols-4 gap-2">
                        <x-input label="Journey Route" class="mb-4" wire:model="journey_route" placeholder="Dac-Kul"
                            required />
                        <x-input label="Journey Transit" class="mb-4" wire:model="journey_transit"
                            placeholder="Dac-Kul" required />
                        <x-input label="Return Route" class="mb-4" wire:model="return_route" placeholder="Kul-Dac"
                            required />
                        <x-input label="Return Transit" class="mb-4" wire:model="return_transit" placeholder="Kul-Dac"
                            required />
                    </div>
                </x-card>
                <x-card class="mt-3 flex-grow">
                    <x-devider title="Date & Ariline Information" />
                    <div class="grid grid-cols-4 gap-2">
                        <x-datetime label="Journey Date" class="mb-4" wire:model="journey_date" required />
                        <x-datetime label="Return Date" class="mb-4" wire:model="return_date" required />
                        <x-input label="Airline Name" class="mb-4" wire:model="airline_name" required
                            placeholder="Us Bangla" />
                        <x-input label="Airline Code" class="mb-4" wire:model="airline_code" required
                            placeholder="76776" />
                    </div>
                </x-card>
            </div>
            <div class="col-span-1 flex flex-col h-full">
                <x-card class="flex-grow">
                    <x-devider title="Additional Information" size="text-xl" />
                    <x-input label="Baggage Weight" class="mb-4" wire:model="baggage_weight" placeholder="76776"
                        required />
                    <x-input label="Available Seat" class="mb-4" wire:model="available_seat" placeholder="60" required
                        type="number" />

                    <x-checkbox label="Is Food ?" wire:model="is_food" />
                    <div class="mt-4">
                        <x-file class="mb-4" label="Thumbnail" wire:model="thumbnail" accept="image/png, image/jpeg">
                            <img src="{{ $groupflight->thumbnail_link ?? asset('/group-flight.png') }}" alt=""
                                class="h-20 rounded-lg">
                        </x-file>
                    </div>
                    @php
                        $statuses = [['id' => 0, 'name' => 'Inactive'], ['id' => 1, 'name' => 'Active']];
                    @endphp
                    <x-choices label="Status" class="mb-4" single :options="$statuses" wire:model="status"
                        placeholder="Select One" />

                    <x-slot:actions>
                        <x-button label="Group Flight List" link="/admin/travel-product/list" class="btn-sm" />
                        <x-button type="submit" label="Update Group Flight" class="btn-primary btn-sm"
                            spinner="updateGroupFlight" />
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
