<?php

use App\Models\Blog;
use Livewire\Volt\Component;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;
use Livewire\WithPagination;
use Mary\Traits\Toast;

new #[Layout('components.layouts.admin')] #[Title('Blog List')] class extends Component {
    use Toast, WithPagination;

    public string $search = '';
    public $headers = [];
    public array $sortBy = ['column' => 'id', 'direction' => 'asc'];

    public function mount()
    {
        $this->headers = $this->headers();
    }
    public function headers(): array
    {
        return [['key' => 'id', 'label' => '#'], ['key' => 'blog_image', 'label' => 'Blog'], ['key' => 'title', 'label' => 'Blog Title'], ['key' => 'description', 'label' => 'Description'], ['key' => 'created_at', 'label' => 'Date'], ['key' => 'action_by', 'label' => 'Last Action By']];
    }
    public function blogs()
    {
        return Blog::query()->when($this->search, fn($q) => $q->whereAny(['name', 'post', 'title'], 'LIKE', "%$this->search%"))->orderBy(...array_values($this->sortBy))->paginate(20);
    }
    public function delete(Blog $blog)
    {
        try {
            $blog->update([
                'action_by' => auth()->user()->id,
            ]);
            $blog->delete();
            $this->success('Blog Deleted Successfully');
        } catch (\Throwable $th) {
            $this->error($th->getMessage());
        }
    }
    public function with(): array
    {
        return [
            'blogs' => $this->blogs(),
        ];
    }
}; ?>

<div>
    <x-header title="Blog List" size="text-xl" separator class="bg-white px-2 pt-2">
        <x-slot:middle class="!justify-end">
            <x-input wire:model.live="search" class="custom-input max-w-36" placeholder="Search..." />
        </x-slot:middle>
        <x-slot:actions>
            <x-button icon="o-plus" no-wire-navigate link="/admin/blog/create" label="Add Blog"
                class="btn-primary btn-sm" />
        </x-slot:actions>
    </x-header>
    <x-card>
        <x-table :headers="$headers" :rows="$blogs" :sort-by="$sortBy" with-pagination>
            @scope('cell_id', $blog, $blogs)
                {{ $loop->iteration + ($blogs->currentPage() - 1) * $blogs->perPage() }}
            @endscope
            @scope('cell_blog_image', $blog)
                <img src="{{ $blog->blog_image_link ?? '/documents.jpg' }}" class="w-12  h-12 rounded-full">
            @endscope
            @scope('cell_created_at', $blog)
                {{ $blog->created_at->format('d M, Y') }}
            @endscope
            @scope('cell_action_by', $blog)
                {{ $blog->actionby->name ?? '' }}
            @endscope
            @scope('cell_description', $blog)
                {!! \Illuminate\Support\Str::limit(
                    $blog->description ?? '',
                    30,
                    ' <span class="text-green-500 cursor-pointer">..</span>',
                ) !!}
            @endscope
            @scope('actions', $blog)
                <div class="flex items-center gap-1">
                    <x-button icon="o-trash" class="btn-action btn-error" wire:confirm="Are you sure?"
                        wire:click="delete({{ $blog->id }})" />
                    <x-button icon="s-pencil-square" class="btn-action btn-neutral" no-wire-navigate
                        link="/admin/blog/{{ $blog->id }}/edit" />
                </div>
            @endscope
        </x-table>
    </x-card>
</div>
