<?php

use App\Models\Faq;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Volt\Component;
use Livewire\WithPagination;
use Mary\Traits\Toast;

new #[Layout('components.layouts.admin')] #[Title('FAQ')] class extends Component {
    use Toast, WithPagination;

    public string $search = '';

    public $header = [];

    public array $sortBy = ['column' => 'id', 'direction' => 'asc'];

    /**
     * Mount the component with initial data.
     * Initializes header columns used for listing FAQs.
     */
    public function mount(): void
    {
        $this->header = $this->headers();
    }

    /**
     * Delete the given FAQ entry.
     * Shows success or error message depending on outcome.
     */
    public function delete(Faq $faq): void
    {
        try {
            $faq->update([
                'action_by' => auth()->user()->id,
            ]);
            $faq->delete();
            $this->success('Faq Deleted Successfully');
        } catch (\Throwable $th) {
            // Show debug message if APP_DEBUG is enabled, else show a generic message
            $this->error(env('APP_DEBUG', false) ? $th->getMessage() : 'Something went wrong');
        }
    }

    /**
     * Returns an array defining table headers.
     */
    public function headers(): array
    {
        return [['key' => 'id', 'label' => '#'], ['key' => 'offer', 'label' => 'Offer'], ['key' => 'question', 'label' => 'Question'], ['key' => 'answer', 'label' => 'Answer'], ['key' => 'action_by', 'label' => 'Last Action By']];
    }

    /**
     * Retrieves the paginated list of FAQs, filtered by search and ordered by sortBy.
     *
     * @return Livewire\WithPagination
     */
    public function faqs()
    {
        return Faq::query()->with('actionBy')->when($this->search, fn(Builder $query) => $query->whereAny(['question', 'answer'], 'LIKE', "%{$this->search}%"))->orderBy(...array_values($this->sortBy))->paginate(20);
    }

    /**
     * Resets the pagination when a searchable property is updated.
     *
     * @param  string  $property
     */
    public function updated($property): void
    {
        if (!is_array($property) && $property !== '') {
            $this->resetPage();
        }
    }

    /**
     * Makes data available to the component view.
     */
    public function with(): array
    {
        return [
            'faqs' => $this->faqs(),
        ];
    }
}; ?>


<div>
    <x-header title="Frequently Asked Question List" size="text-xl" separator class="bg-white px-2 pt-2">
        <x-slot:middle class="!justify-end">
            <x-input icon="o-bolt" wire:model.live="search" class="custom-input max-w-36" placeholder="Search..." />
        </x-slot>
        <x-slot:actions>
            <x-button icon="o-plus" no-wire-navigate link="/admin/faq/create" label="Add FAQ"
                class="btn-primary btn-sm" />
        </x-slot>
    </x-header>
    <x-card>
        <x-table :headers="$header" :rows="$faqs" :sort-by="$sortBy" with-pagination>
            @scope('cell_id', $faq, $faqs)
                {{ $loop->iteration + ($faqs->currentPage() - 1) * $faqs->perPage() }}
            @endscope

            @scope('cell_offer', $faq)
                {{ $faq->offer->title ?? '' }}
            @endscope

            @scope('cell_question', $faq)
                <p title="{{ $faq->question ?? '' }}">
                    {!! \Illuminate\Support\Str::limit(
                        $faq->question ?? '',
                        50,
                        ' <span class="text-blue-500 cursor-pointer">..</span>',
                    ) !!}
                </p>
            @endscope

            @scope('cell_answer', $faq)
                <p title="{{ $faq->answer ?? '' }}">
                    {!! \Illuminate\Support\Str::limit(
                        $faq->answer ?? '',
                        100,
                        ' <span class="text-blue-500 cursor-pointer">..</span>',
                    ) !!}
                </p>
            @endscope

            @scope('cell_action_by', $faq)
                {{ $faq->actionBy->name ?? '' }}
            @endscope

            @scope('actions', $faq)
                <div class="flex items-center gap-1">
                    <x-button icon="o-trash" wire:click="delete({{ $faq['id'] }})" wire:confirm="Are you sure?"
                        class="btn-primary btn-action text-white" />
                    <x-button icon="s-pencil-square" no-wire-navigate link="/admin/faq/{{ $faq['id'] }}/edit"
                        no-wire-navigate class="btn-primary btn-action text-white" />
                </div>
            @endscope
        </x-table>
    </x-card>
</div>
