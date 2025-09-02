<?php

use App\Enum\TravelProductStatus;
use App\Models\TravelProduct;
use Mary\Traits\Toast;
use Livewire\Volt\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;
use Illuminate\Database\Eloquent\Builder;

new #[Layout('components.layouts.partner')] #[Title('Travel Product List')] class extends Component {
    use WithPagination;
    use Toast;
    public array $headers;
    public string $search = '';
    public array $sortBy = ['column' => 'id', 'direction' => 'desc'];

    public function mount()
    {
        $this->headers = $this->headers();
    }
    public function delete(TravelProduct $product)
    {
        try {
            $product->update([
                'action_id' => auth()->user()->id,
            ]);
            $product->delete();
            $this->success('Travel Product Deleted Successfully');
        } catch (\Throwable $th) {
            $this->error($th->getMessage());
        }
    }
    public function headers(): array
    {
        return [['key' => 'id', 'label' => '#'], ['key' => 'thumbnail', 'label' => 'Thumbnail'], ['key' => 'title', 'label' => 'Product Name'], ['key' => 'sku', 'label' => 'SKU'], ['key' => 'brand', 'label' => 'Brand'], ['key' => 'regular_price', 'label' => 'Regular Price'], ['key' => 'offer_price', 'label' => 'Offer Price'], ['key' => 'stock', 'label' => 'Stock Quantity'], ['key' => 'is_featured', 'label' => 'Is Featured'], ['key' => 'status', 'label' => 'Status']];
    }
    public function products()
    {
        return TravelProduct::query()
            ->where('created_by', auth()->user()->id)
            ->when($this->search, fn(Builder $q) => $q->whereAny(['title', 'stock', 'regular_price', 'offer_price'], 'LIKE', "%$this->search%"))
            ->orderBy(...array_values($this->sortBy))
            ->paginate(10);
    }
    public function with(): array
    {
        return [
            'products' => $this->products(),
        ];
    }
}; ?>

<div>
    <x-header title="Travel Product List" size="text-xl" separator class="bg-white px-2 pt-2">
        <x-slot:middle class="!justify-end">
            <x-input icon="o-bolt" wire:model.live="search" placeholder="Search..." />
        </x-slot:middle>
        <x-slot:actions>
            <x-button label="Add Travel Product" icon="o-plus" no-wire-navigate link="/partner/travel-product/create"
                class="btn-primary btn-sm" />
        </x-slot:actions>
    </x-header>
    <x-card>
        <x-table :headers="$headers" :rows="$products" :sort-by="$sortBy" with-pagination>
            @scope('cell_id', $product, $products)
                {{ $loop->iteration + ($products->currentPage() - 1) * $products->perPage() }}
            @endscope
            @scope('cell_is_featured', $product)
                @if ($product->is_featured == 1)
                    <x-badge value="Yes" class="bg-green-100 text-green-700 p-3 text-xs font-semibold" />
                @else
                    <x-badge value="No" class="bg-red-100 text-red-700 p-3 text-xs font-semibold" />
                @endif
            @endscope
            @scope('cell_thumbnail', $product)
                <x-avatar image="{{ $product->thumbnail_link ?? '/empty-user.jpg' }}" class="!w-10" />
            @endscope
            @scope('cell_status', $product)
                @if ($product->status == TravelProductStatus::Active)
                    <x-badge value="{{ $product->status->label() }}"
                        class="bg-green-100 text-green-700 p-3 text-xs font-semibold" />
                @elseif($product->status == TravelProductStatus::Pending)
                    <x-badge value="{{ $product->status->label() }}"
                        class="bg-yellow-100 text-yellow-700 p-3 text-xs font-semibold" />
                @else
                    <x-badge value="{{ $product->status->label() }}"
                        class="bg-red-100 text-red-700 p-3 text-xs font-semibold" />
                @endif
            @endscope
            @scope('actions', $product)
                <div class="flex items-center gap-1">
                    <x-button icon="o-trash" wire:click="delete({{ $product['id'] }})" wire:confirm="Are you sure?"
                        class="btn-error btn-action" spinner="delete({{ $product['id'] }})" />

                    <x-button icon="s-pencil-square" no-wire-navigate
                        link="/partner/travel-product/{{ $product['id'] }}/edit" class="btn-neutral btn-action " />
                </div>
            @endscope
        </x-table>

    </x-card>
</div>
