<?php

use App\Models\Review;
use Mary\Traits\Toast;
use Livewire\Volt\Component;
use Livewire\WithPagination;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;

new #[Layout('components.layouts.admin')] #[Title('Review List')] class extends Component {
    use WithPagination, Toast;
    public string $search = '';
    public Review $review;

    public function delete(Review $review)
    {
        try {
            $review->update([
                'action_by' => auth()->user()->id,
            ]);
            $review->delete();
            $this->success('Review Deleted Successfully');
        } catch (\Throwable $th) {
            $this->error(env('APP_DEBUG', false) ? $th->getMessage() : 'Something went wrong');
        }
    }
    public function headers(): array
    {
        return [['key' => 'id', 'label' => '#'], ['key' => 'source', 'label' => 'Source'], ['key' => 'source_title', 'label' => 'Source Title'], ['key' => 'rating', 'label' => 'Rating'], ['key' => 'comment', 'label' => 'Comment'], ['key' => 'action_by', 'label' => 'Last Action By']];
    }
    public function reviews()
    {
        return Review::query()
            ->with(['reviewable', 'actionBy'])
            ->when($this->search, fn(Builder $q) => $q->where(['comment'], 'LIKE', "%$this->search%"))
            ->latest()
            ->paginate(20)
            ->withQueryString();
    }

    public function updated($property)
    {
        if (!is_array($property) && $property != '') {
            $this->resetPage();
        }
    }
    public function with(): array
    {
        return [
            'headers' => $this->headers(),
            'reviews' => $this->reviews(),
        ];
    }
}; ?>

<div>
    <x-header title="Review List" size="text-xl" separator class="bg-white px-2 pt-2">
        <x-slot:middle class="!justify-end">
            <x-input icon="o-bolt" wire:model.live="search" class="custom-input max-w-36" placeholder="Search..." />
        </x-slot:middle>
    </x-header>
    <x-card>
        <x-table :headers="$headers" :rows="$reviews" with-pagination>
            @scope('cell_id', $review, $reviews)
                {{ $loop->iteration + ($reviews->currentPage() - 1) * $reviews->perPage() }}
            @endscope
            @scope('cell_source', $review)
                @php
                    $sourceType = class_basename($review->reviewable_type);
                    $sourceTitle = match ($sourceType) {
                        'Tour' => $review->reviewable?->title,
                        'Hotel' => $review->reviewable?->name,
                    };
                @endphp
                {{ $sourceType }}
            @endscope
            @scope('cell_source_title', $review)
                @php
                    $source = $review->reviewable;
                    $title = $source?->title ?? ($source?->name ?? '');
                @endphp
                <span class="text-sm text-gray-800">
                    {{ \Illuminate\Support\Str::limit($title, 40) }}
                </span>
            @endscope

            @scope('cell_action_by', $review)
                {{ $review->actionBy->name ?? 'N/A' }}
            @endscope

            @scope('cell_subject', $review)
                <p class="text-sm font-medium mt-2 hover:text-green-600 hover:underline">
                    {!! \Illuminate\Support\Str::limit(
                        $review->comment ?? '',
                        60,
                        ' <span class="text-green-500 cursor-pointer">..</span>',
                    ) !!}
                </p>
            @endscope
            @scope('actions', $review)
                <div class="flex items-center">
                    <x-button icon="o-trash" wire:click="delete({{ $review['id'] }})" wire:confirm="Are you sure?"
                        class="btn-error btn-action" />
                </div>
            @endscope
        </x-table>
    </x-card>
</div>
