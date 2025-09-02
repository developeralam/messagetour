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

new #[Layout('components.layouts.admin')] #[Title('Add Blog')] class extends Component {
    use Toast, WithFileUploads, InteractsWithImageUploads;

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

    public function storeBlog()
    {
        $this->validate();
        try {
            // Initialize storage paths for uploaded images
            $storedImagePath = null;
            $storedBlogImagePath = null;

            // Process and store image if provided
            $storedImagePath = $this->image ? $this->optimizeAndStoreImage($this->image, 'public', 'blog', null, null, 75) : null;
            $storedBlogImagePath = $this->blog_image ? $this->optimizeAndStoreImage($this->blog_image, 'public', 'blog', null, null, 75) : null;

            Blog::create([
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
                'status' => BlogStatus::Active,
                'image' => $storedImagePath,
                'blog_image' => $storedBlogImagePath,
            ]);
            $this->success('Blog Added Successfully', redirectTo: '/admin/blog/list');
        } catch (\Throwable $th) {
            $this->error(env('APP_DEBUG', false) ? $th->getMessage() : 'Something went wrong');
        }
    }
}; ?>

<div>
    @push('custom-script')
        <script src="https://cdn.ckeditor.com/ckeditor5/39.0.1/classic/ckeditor.js"></script>
    @endpush
    <x-header title="Add New Blog" size="text-xl" separator class="bg-white px-2 pt-2">
        <x-slot:actions>
            <x-button label="Back" link="/admin/blog/list" class="btn-sm btn-primary" icon="fas.arrow-left" />
        </x-slot:actions>
    </x-header>
    <x-form wire:submit="storeBlog">
        <div class="grid grid-cols-3 gap-2">
            <div class="col-span-2 flex flex-col">
                <x-card>
                    <x-devider title="Blog, Blogger & Meta Information" />
                    <div class="grid grid-cols-3 gap-4 mb-4">
                        <x-input label="Blogger Name" wire:model="name" placeholder="Blogger Name" />
                        <x-input label="Blogger Post" wire:model="post" placeholder="Blogger Post" />
                        <x-input label="Blog Title" wire:model="title" required placeholder="Blog Title" />
                        <x-input label="Meta Title" wire:model="meta_title" placeholder="Blog Meta Title" />
                        <x-input label="Meta Keywords" wire:model="keywords" placeholder="Blog Meta Keywords" />
                        <x-input label="Meta Canonical Url" wire:model="canonical_url"
                            placeholder="Blog Meta Canonical Url" />
                    </div>
                    <x-devider title="Descriptions" />
                    <div wire:ignore>
                        <label for="description" class="text-sm">Blog Description</label>
                        <textarea wire:model="description" id="description" cols="30" rows="10"></textarea>
                    </div>
                    <div wire:ignore class="mt-2">
                        <label for="body" class="text-sm">Blog Body</label>
                        <textarea wire:model="body" id="body" cols="30" rows="10"></textarea>
                    </div>
                    <div wire:ignore class="mt-2">
                        <label for="meta_description" class="text-sm">Meta Description</label>
                        <textarea wire:model="meta_description" id="meta_description" cols="30" rows="10"></textarea>
                    </div>
                </x-card>
            </div>
            <div class="col-span-1 flex flex-col">
                <x-card>
                    <div class="grid grid-cols-2 gap-4 border-b-2 pb-2">
                        <x-file wire:model="image" label="Blogger Image">
                            <img src="{{ '/men-avatar.png' }}" class="h-20 w-full rounded-lg" />
                        </x-file>
                        <x-file wire:model="blog_image" label="Blog Image" required>
                            <img src="{{ '/documents.jpg' }}" class="h-20 w-full rounded-lg" />
                        </x-file>
                    </div>
                    <x-slot:actions>
                        <x-button label="Blog List" class="btn-sm" link="/admin/blog/list" />
                        <x-button type="submit" label="Add Blog" class="btn-primary btn-sm" spinner="storeBlog" />
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
