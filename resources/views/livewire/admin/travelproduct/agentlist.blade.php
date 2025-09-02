<?php

use App\Enum\UserType;
use Mary\Traits\Toast;
use Livewire\Volt\Component;
use Livewire\WithPagination;
use App\Models\TravelProduct;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;
use App\Enum\TravelProductStatus;
use Illuminate\Database\Eloquent\Builder;

new #[Layout('components.layouts.admin')] #[Title('Travel Product List')] class extends Component {
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
    public function approve(TravelProduct $product)
    {
        try {
            $product->update([
                'action_id' => auth()->user()->id,
                'status' => TravelProductStatus::Active,
            ]);
            $this->success('Travel Product Approve Successfully');
        } catch (\Throwable $th) {
            $this->error(env('APP_DEBUG', false) ? $th->getMessage() : 'Something went wrong');
        }
    }
    public function headers(): array
    {
        return [['key' => 'id', 'label' => '#'], ['key' => 'thumbnail', 'label' => 'Thumbnail'], ['key' => 'title', 'label' => 'Product Name'], ['key' => 'sku', 'label' => 'SKU'], ['key' => 'brand', 'label' => 'Brand'], ['key' => 'regular_price', 'label' => 'Regular Price'], ['key' => 'offer_price', 'label' => 'Offer Price'], ['key' => 'stock', 'label' => 'Stock Quantity'], ['key' => 'is_featured', 'label' => 'Is Featured'], ['key' => 'status', 'label' => 'Status'], ['key' => 'action_id', 'label' => 'Last Action By']];
    }
    public function products()
    {
        return TravelProduct::query()->with('actionBy')->whereHas('createdBy', fn($q) => $q->where('type', UserType::Agent))->when($this->search, fn(Builder $q) => $q->whereAny(['title', 'stock', 'regular_price', 'offer_price'], 'LIKE', "%$this->search%"))->orderBy(...array_values($this->sortBy))->paginate(10);
    }
    public function with(): array
    {
        return [
            'products' => $this->products(),
        ];
    }
}; ?>

<div>
    <x-header title="Agent Travel Product List" size="text-xl" separator class="bg-white px-2 pt-2">
        <x-slot:middle class="!justify-end">
            <x-input icon="o-bolt" wire:model.live="search" placeholder="Search..." />
        </x-slot:middle>
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
            @scope('cell_action_id', $product)
                {{ $product->actionBy->name ?? '' }}
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
                    @if ($product->status == TravelProductStatus::Pending)
                        <x-button icon="fas.check" wire:click="approve({{ $product->id }})"
                            wire:confirm="Are you sure approve this product?" class="btn-primary btn-action text-white"
                            spinner="approve({{ $product['id'] }})" />
                    @endif
                    <x-button icon="o-trash" wire:click="delete({{ $product['id'] }})" wire:confirm="Are you sure?"
                        class="btn-error btn-action" spinner="delete({{ $product['id'] }})" />
                </div>
            @endscope
        </x-table>

    </x-card>
</div>
