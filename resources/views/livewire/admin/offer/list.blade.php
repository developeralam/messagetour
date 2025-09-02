<?php

use App\Models\Offer;
use Mary\Traits\Toast;
use App\Enum\OfferStatus;
use Livewire\Volt\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;
use Illuminate\Database\Eloquent\Builder;

new #[Layout('components.layouts.admin')] #[Title('Offer List')] class extends Component {
    use WithPagination;
    use Toast;
    public array $headers;
    public string $search = '';
    public array $sortBy = ['column' => 'id', 'direction' => 'desc'];
    public function mount()
    {
        $this->headers = $this->headers();
    }
    public function delete(Offer $offer)
    {
        try {
            $offer->update([
                'action_id' => auth()->user()->id,
            ]);
            $offer->delete();
            $this->success('Offer deleted successfully');
        } catch (\Throwable $th) {
            $this->error($th->getMessage());
        }
    }
    public function headers(): array
    {
        return [['key' => 'id', 'label' => '#'], ['key' => 'thumbnail', 'label' => 'Thumbnail'], ['key' => 'title', 'label' => 'Offer Title'], ['key' => 'type', 'label' => 'Offer Type'], ['key' => 'coupon', 'label' => 'Coupon Code'], ['key' => 'validaty', 'label' => 'Offer Validaty'], ['key' => 'status', 'label' => 'Offer Status'], ['key' => 'action_id', 'label' => 'Last Action By']];
    }
    public function offers()
    {
        return Offer::query()->when($this->search, fn(Builder $q) => $q->where('title', 'LIKE', "%$this->search%"))->orderBy(...array_values($this->sortBy))->paginate(10);
    }
    public function with(): array
    {
        return [
            'offers' => $this->offers(),
        ];
    }
}; ?>

<div>
    <x-header title="Offer List" size="text-xl" separator class="bg-white px-2 pt-2">
        <x-slot:middle class="!justify-end">
            <x-input icon="o-bolt" wire:model.live="search" placeholder="Search..." />
        </x-slot>
        <x-slot:actions>
            <x-button label="Add Offer" icon="o-plus" no-wire-navigate link="/admin/offer/create"
                class="btn-primary btn-sm" />
        </x-slot>
    </x-header>
    <x-card>
        <x-table :headers="$headers" :rows="$offers" :sort-by="$sortBy" with-pagination>
            @scope('cell_id', $offer, $offers)
                {{ $loop->iteration + ($offers->currentPage() - 1) * $offers->perPage() }}
            @endscope

            @scope('cell_coupon', $offer)
                {{ $offer->coupon->code ?? '' }}
            @endscope

            @scope('cell_type', $offer)
                @if ($offer->type)
                    <x-badge value="{{ $offer->type->label() }}" class="bg-primary text-white p-3 text-xs" />
                @endif
            @endscope

            @scope('cell_thumbnail', $offer)
                <x-avatar image="{{ $offer->thumbnail_link ?? '/empty-user.jpg' }}" class="!w-10" />
            @endscope

            @scope('cell_validaty', $offer)
                {{ optional($offer->coupon)->expiry_date?->timezone('Asia/Dhaka')->format('d M, Y') ?? '' }}
            @endscope

            @scope('cell_action_id', $offer)
                {{ $offer->actionBy->name ?? '' }}
            @endscope

            @scope('cell_status', $offer)
                @if ($offer->status == OfferStatus::Active)
                    <x-badge value="{{ $offer->status->label() }}"
                        class="bg-green-100 text-green-700 p-3 text-xs font-semibold" />
                @else
                    <x-badge value="{{ $offer->status->label() }}"
                        class="bg-red-100 text-red-700 p-3 text-xs font-semibold" />
                @endif
            @endscope

            @scope('actions', $offer)
                <div class="flex items-center gap-1">
                    <x-button icon="o-trash" wire:click="delete({{ $offer['id'] }})" wire:confirm="Are you sure?"
                        class="btn-error btn-action" spinner="delete({{ $offer['id'] }})" />

                    <x-button icon="s-pencil-square" no-wire-navigate link="/admin/offer/{{ $offer['id'] }}/edit"
                        class="btn-neutral btn-action" />
                </div>
            @endscope
        </x-table>
    </x-card>
</div>
