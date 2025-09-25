<?php

use App\Models\TravelProduct;
use App\Enum\TravelProductStatus;
use App\Traits\InteractsWithImageUploads;
use Illuminate\Support\Str;
use Livewire\Volt\Component;
use Livewire\Attributes\Rule;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;
use Illuminate\Support\Collection;
use Livewire\Features\SupportFileUploads\WithFileUploads;
use Mary\Traits\Toast;

new #[Layout('components.layouts.admin')] #[Title('Add New Travel Product')] class extends Component {
    use Toast, WithFileUploads, InteractsWithImageUploads;
    public $product = [];

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

    #[Rule('nullable|image|max:1024')]
    public $thumbnail;

    #[Rule('required')]
    public $stock;

    #[Rule('required')]
    public $status;

    #[Rule('nullable')]
    public $is_featured;

    public array $product_status;

    public function mount($product)
    {
        $productRes = TravelProduct::find($product);
        if ($productRes) {
            $this->product = $productRes;
            $this->title = $productRes->title;
            $this->sku = $productRes->sku;
            $this->brand = $productRes->brand;
            $this->description = $productRes->description;
            $this->regular_price = $productRes->regular_price;
            $this->offer_price = $productRes->offer_price;
            $this->stock = $productRes->stock;
            $this->is_featured = $productRes->is_featured;
            $this->status = $productRes->status;
        }
        $this->product_status = TravelProductStatus::getStatus();
    }
    public function updateTravelProduct()
    {
        $this->validate();
        try {
            $storedThumbnailPath = null;
            if ($this->thumbnail) {
                $storedThumbnailPath = $this->optimizeAndUpdateImage(
                    $this->thumbnail,
                    $this->product->thumbnail, // old path
                    'public',
                    'travelproduct',
                    null,
                    null,
                    75,
                );
            } else {
                $storedThumbnailPath = $this->product->thumbnail;
            }
            $this->product->update([
                'title' => $this->title,
                'slug' => Str::slug($this->title),
                'sku' => $this->sku,
                'brand' => $this->brand,
                'description' => $this->description,
                'regular_price' => $this->regular_price,
                'offer_price' => $this->offer_price ?: null,
                'thumbnail' => $storedThumbnailPath,
                'stock' => $this->stock,
                'is_featured' => $this->is_featured,
                'status' => $this->status,
                'action_id' => auth()->user()->id,
            ]);
            $this->success('Travel Product Update Successfully', redirectTo: '/admin/travel-product/list');
        } catch (\Throwable $th) {
            $this->error(env('APP_DEBUG', false) ? $th->getMessage() : 'Something went wrong');
        }
    }
}; ?>

<div>
    @push('custom-script')
        <script src="https://cdn.ckeditor.com/ckeditor5/39.0.1/classic/ckeditor.js"></script>
    @endpush
    <x-header title="Update Product - {{ $product->title }}" size="text-xl" separator class="bg-white px-2 pt-2">
        <x-slot:actions>
            <x-button label="Travel Product List" icon="fas.arrow-left" link="/admin/travel-product/list" class="btn-primary btn-sm" />
        </x-slot:actions>
    </x-header>
    <x-form wire:submit="updateTravelProduct" x-cloak>
        <div class="grid grid-cols-3 gap-4 items-start">
            <div class="col-span-2">
                <x-card>
                    <x-devider title="Travel Product Information" />
                    <x-input label="Travel Product Title" class="mb-4" wire:model="title" placeholder="Travel Product Title" />
                    <div class="grid grid-cols-2 gap-2">
                        <x-input label="SKU" class="mb-4" wire:model="sku" placeholder="SKU" />
                        <x-input label="Brand" class="mb-4" wire:model="brand" placeholder="Brand" />
                        <x-input label="Regular Price" class="mb-4" wire:model="regular_price" placeholder="Regular Price" type="number" />
                        <x-input label="Offer Price" class="mb-4" wire:model="offer_price" placeholder="Offer Price" type="number" />
                    </div>
                </x-card>
                <x-card class="mt-4">
                    <x-devider title="Travel Product Description" />
                    <div wire:ignore class="mt-2">
                        <label for="description" class="font-normal text-sm">Description</label>
                        <textarea wire:model="description" id="description" cols="30" rows="10">{{ $product->description }}</textarea>
                    </div>
                </x-card>
            </div>
            <x-card class="col-span-1">
                <x-devider title="Additional Information" />
                <div class="grid grid-cols-2 gap-2">
                    <x-input label="Stock Quantity" class="mb-4" wire:model="stock" placeholder="Stock Quantity" type="number" required />
                    <x-choices label="Product Status" wire:model="status" :options="$product_status" single placeholder="Select Status" required />
                </div>
                <div class="mb-4">
                    <x-checkbox label="Is Featured?" wire:model="is_featured" class="w-4 h-4" />
                </div>
                <x-file label="Thumbnail" wire:model="thumbnail" accept="image/png, image/jpeg" class="border-b-2">
                    <img src="{{ $product->thumbnail_link ?? asset('/empty-product.png') }}" class="h-20 rounded-lg mb-2">
                </x-file>
                <x-slot:actions>
                    <x-button label="Product List" link="/admin/travel-product/list" class="btn-sm" />
                    <x-button type="submit" label="Update Product" class="btn-primary btn-sm" spinner="updateTravelProduct" />
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
