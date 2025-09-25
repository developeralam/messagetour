<?php

use App\Models\TravelProduct;
use App\Enum\TravelProductStatus;
use App\Traits\InteractsWithImageUploads;
use Illuminate\Support\Str;
use Livewire\Volt\Component;
use Livewire\Attributes\Rule;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;
use Livewire\Features\SupportFileUploads\WithFileUploads;
use Mary\Traits\Toast;

new #[Layout('components.layouts.partner')] #[Title('Add New Travel Product')] class extends Component {
    use Toast, WithFileUploads, InteractsWithImageUploads;

    #[Rule('required')]
    public $title;

    #[Rule('required')]
    public $sku;

    #[Rule('required')]
    public $brand;

    #[Rule('nullable')]
    public $description;

    #[Rule('required')]
    public $regular_price;

    #[Rule('nullable')]
    public $offer_price;

    #[Rule('required|image|max:1024')]
    public $thumbnail;

    #[Rule('required')]
    public $stock;

    #[Rule('nullable')]
    public $is_featured;

    public function storeTravelProduct()
    {
        $this->validate();
        try {
            $storedThumbnailPath = null;
            if ($this->thumbnail) {
                $storedThumbnailPath = $this->optimizeAndStoreImage(
                    $this->thumbnail, // The file from Livewire
                    'public', // The disk to store on
                    'travelproduct', // The subdirectory within the disk
                    null, // Optional max width
                    null, // Optional max height
                    75, // WEBP quality
                );
            }
            TravelProduct::create([
                'title' => $this->title,
                'slug' => Str::slug($this->title),
                'sku' => $this->sku,
                'brand' => $this->brand,
                'description' => $this->description,
                'regular_price' => $this->regular_price,
                'offer_price' => $this->offer_price,
                'thumbnail' => $storedThumbnailPath,
                'stock' => $this->stock,
                'is_featured' => $this->is_featured ?? false,
                'created_by' => auth()->user()->id,
                'status' => TravelProductStatus::Pending,
            ]);
            $this->success('Travel Product Added Successfully', redirectTo: '/partner/travel-product/list');
        } catch (\Throwable $th) {
            $this->error(env('APP_DEBUG', false) ? $th->getMessage() : 'Something went wrong');
        }
    }
}; ?>

<div>
    @push('custom-script')
        <script src="https://cdn.ckeditor.com/ckeditor5/39.0.1/classic/ckeditor.js"></script>
    @endpush
    <x-header title="Add New Travel Product" size="text-xl" separator class="bg-white px-2 pt-2">
        <x-slot:actions>
            <x-button label="Travel Product List" icon="fas.arrow-left" link="/partner/travel-product/list" class="btn-primary btn-sm" />
        </x-slot:actions>
    </x-header>
    <x-form wire:submit="storeTravelProduct">
        <div class="grid grid-cols-3 gap-4 items-start">
            <div class="col-span-2">
                <x-card>
                    <x-devider title="Travel Product Information" />
                    <x-input label="Travel Product Title" class="mb-4" wire:model="title" placeholder="Travel Product Title" required />
                    <div class="grid grid-cols-2 gap-2">
                        <x-input label="SKU" class="mb-4" wire:model="sku" placeholder="SKU" required />
                        <x-input label="Brand" class="mb-4" wire:model="brand" placeholder="Brand" required />
                        <x-input label="Regular Price" class="mb-4" wire:model="regular_price" placeholder="Regular Price" type="number"
                            required />
                        <x-input label="Offer Price" class="mb-4" wire:model="offer_price" placeholder="Offer Price" type="number" />
                    </div>
                </x-card>
                <x-card class="mt-4">
                    <x-devider title="Travel Product Description" />
                    <div wire:ignore class="mt-2">
                        <label for="description" class="font-normal text-sm">Description</label>
                        <textarea wire:model="description" id="description" cols="30" rows="10"></textarea>
                    </div>
                </x-card>
            </div>
            <x-card class="col-span-1">
                <x-devider title="Additional Information" />
                <x-input label="Stock Quantity" class="mb-4" wire:model="stock" placeholder="Stock Quantity" type="number" required />
                <div class="mb-4">
                    <x-checkbox label="Is Featured?" wire:model="is_featured" class="w-4 h-4" />
                </div>
                <x-file label="Thumbnail" wire:model="thumbnail" accept="image/png, image/jpeg" required class="border-b-2">
                    <img src="{{ asset('empty-product.png') }}" alt="" class="h-20 rounded-lg mb-2">
                </x-file>
                <x-slot:actions>
                    <x-button label="Product List" link="/partner/travel-product/list" class="btn-sm" />
                    <x-button type="submit" label="Save Product" class="btn-primary btn-sm" spinner="storeTravelProduct" />
                </x-slot:actions>

            </x-card>
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
