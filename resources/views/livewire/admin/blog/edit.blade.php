<?php

use Carbon\Carbon;
use App\Models\Blog;
use Mary\Traits\Toast;
use App\Enum\BlogStatus;
use App\Traits\InteractsWithImageUploads;
use Illuminate\Support\Str;
use Livewire\Volt\Component;
use Livewire\Attributes\Rule;
use Livewire\WithFileUploads;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;

new #[Layout('components.layouts.admin')] #[Title('Update Blog')] class extends Component {
    use Toast, WithFileUploads, InteractsWithImageUploads;
    public $statuses;
    public $image_link;
    public $blog_image_link;

    #[Rule('nullable')]
    public $name;

    #[Rule('nullable')]
    public $post;

    #[Rule('required')]
    public $title;

    #[Rule('nullable')]
    public $image;

    #[Rule('required')]
    public $blog_image;

    #[Rule('required')]
    public $description;

    #[Rule('required')]
    public $body;

    #[Rule('nullable')]
    public $meta_title;

    #[Rule('nullable')]
    public $meta_description;

    #[Rule('nullable')]
    public $keywords;

    #[Rule('nullable')]
    public $canonical_url;

    public Blog $blog;

    public function mount($blog)
    {
        $this->blog = $blog;
        $this->name = $blog->name;
        $this->post = $blog->post;
        $this->title = $blog->title;
        $this->description = $blog->description;
        $this->body = $blog->body;
        $this->meta_title = $blog->meta_title;
        $this->meta_description = $blog->meta_description;
        $this->keywords = $blog->keywords;
        $this->canonical_url = $blog->canonical_url;
        $this->status = $blog->status;
        $this->image = $blog->image_link;
        $this->blog_image = $blog->blog_image_link;
        $this->statuses = BlogStatus::getStatuses();
    }
    public function updateBlog()
    {
        dd('here');
        $this->validate();

        try {
            // Process and store updated images if provided, otherwise keep existing images
            $storedImagePath = $this->image ? $this->optimizeAndUpdateImage($this->image, $this->blog->image, 'public', 'blog', null, null, 75) : $this->blog->image;
            $storedBlogImagePath = $this->blog_image ? $this->optimizeAndUpdateImage($this->blog_image, $this->blog->blog_image, 'public', 'blog', null, null, 75) : $this->blog->blog_image;

            $this->blog->update([
                'name' => $this->name,
                'title' => $this->title,
                'slug' => Str::slug($this->title),
                'post' => $this->post,
                'body' => $this->body,
                'description' => $this->description,
                'date' => Carbon::now(),
                'meta_title' => $this->meta_title,
                'meta_description' => $this->meta_description,
                'keywords' => $this->keywords,
                'canonical_url' => $this->canonical_url,
                'image' => $storedImagePath,
                'blog_image' => $storedBlogImagePath,
                'action_by' => auth()->user()->id,
            ]);

            $this->success('Blog Updated Successfully', redirectTo: '/admin/blog/list');
        } catch (\Throwable $th) {
            $this->error(env('APP_DEBUG', false) ? $th->getMessage() : 'Something went wrong');
        }
    }
}; ?>

<div>
    @push('custom-script')
        <script src="https://cdn.ckeditor.com/ckeditor5/39.0.1/classic/ckeditor.js"></script>
    @endpush
    <x-header title="Update Blog - {{ $blog->title }}" size="text-xl" separator class="bg-white px-2 pt-2">
        <x-slot:actions>
            <x-button label="Back" link="/admin/blog/list" class="btn-sm btn-primary" icon="fas.arrow-left" />
        </x-slot:actions>
    </x-header>
    <x-form wire:submit="updateBlog">
        <div class="grid grid-cols-3 gap-2">
            <div class="col-span-2 flex flex-col">
                <x-card>
                    <x-devider title="Blog, Blogger & Meta Information" />
                    <div class="grid grid-cols-3 gap-4 mb-4">
                        <x-input label="Blogger Name" wire:model="name" placeholder="Blogger Name" />
                        <x-input label="Blogger Post" wire:model="post" placeholder="Blogger Post" />
                        <x-input label="Blog Title" wire:model="title" placeholder="Blog Title" required />
                        <x-input label="Meta Title" wire:model="meta_title" placeholder="Blog Meta Title" />
                        <x-input label="Meta Keywords" wire:model="keywords" placeholder="Blog Meta Keywords" />
                        <x-input label="Meta Canonical Url" wire:model="canonical_url"
                            placeholder="Blog Meta Canonical Url" />
                    </div>
                    <x-devider title="Descriptions" />
                    <div wire:ignore>
                        <label for="description" class="font-semibold text-sm">Blog Description</label>
                        <textarea wire:model="description" id="description" cols="30" rows="10">{{ $blog->description }}</textarea>
                    </div>
                    <div wire:ignore class="mt-2">
                        <label for="body" class="font-semibold text-sm">Blog Body</label>
                        <textarea wire:model="body" id="body" cols="30" rows="10">{{ $blog->body }}</textarea>
                    </div>
                    <div wire:ignore class="mt-2">
                        <label for="meta_description" class="font-semibold text-sm">Meta Description</label>
                        <textarea wire:model="meta_description" id="meta_description" cols="30" rows="10">{{ $blog->meta_description }}</textarea>
                    </div>
                </x-card>
            </div>
            <div class="col-span-1 flex flex-col">
                <x-card>
                    <div class="grid grid-cols-2 gap-4 border-b-2 pb-2">
                        <x-file wire:model="image" label="Blogger Image">
                            <img src="{{ $blog->image_link ?? '/men-avatar.png' }}" class="h-36 w-full rounded-lg" />
                        </x-file>
                        <x-file wire:model="blog_image" label="Blog Image" required>
                            <img src="{{ $blog->blog_image_link ?? '/documents.jpg' }}"
                                class="h-36 w-full rounded-lg" />
                        </x-file>
                    </div>
                    <x-slot:actions>
                        <x-button label="Blog List" class="btn-sm" link="/admin/blog/list" />
                        <x-button type="submit" label="Update Blog" class="btn-primary btn-sm" spinner="updateBlog" />
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
            ClassicEditor
                .create(document.querySelector('#body'))
                .then(editor => {
                    editor.model.document.on('change:data', () => {
                        @this.set('body', editor.getData());
                    })
                })
                .catch(error => {
                    console.error(error);
                });
            ClassicEditor
                .create(document.querySelector('#meta_description'))
                .then(editor => {
                    editor.model.document.on('change:data', () => {
                        @this.set('meta_description', editor.getData());
                    })
                })
                .catch(error => {
                    console.error(error);
                });
        </script>
    @endpush
</div>
