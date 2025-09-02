<?php

use App\Models\Blog;
use Livewire\Volt\Component;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;

new #[Layout('components.layouts.app')] #[Title('Blogs')] class extends Component {
    public $blogs;

    public function mount()
    {
        $this->blogs = Blog::latest()->get();
    }
}; ?>

<div>
    <section class="w-full py-14 bg-gray-100">
        <div class="text-center">
            <h1 class="text-3xl md:text-4xl lg:text-5xl">Our Latest <span class="text-[#22C55E]">Blogs</span></h1>
            <p class="text-[8px] md:text-[9px] lg:text-xs">Don’t miss out on your chance to stay up on the latest trends.
            </p>
        </div>
        <div class="max-w-6xl mx-auto grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 px-4 py-10">
            @foreach ($blogs as $blog)
                <div
                    class="bg-white rounded-xl shadow border hover:shadow-lg transition-all duration-300 group flex flex-col overflow-hidden">
                    {{-- Blog Image --}}
                    <a href="{{ url($blog->slug . '/blog-details') }}" class="block h-48 overflow-hidden">
                        <img src="{{ $blog->blog_image_link }}" alt="{{ $blog->title }}"
                            class="w-full h-full object-cover group-hover:scale-105 transition duration-300">
                    </a>

                    {{-- Blog Info --}}
                    <div class="p-4 flex flex-col flex-grow">
                        {{-- Blog Name + Date --}}
                        <div class="flex items-center text-sm mb-2 gap-1 italic">
                            <span class="text-gray-700 font-semibold">{{ $blog->name }}</span> |
                            <span class="text-green-500 text-xs">{{ $blog->created_at->format('d M, Y') }}</span>
                        </div>

                        {{-- Blog Title --}}
                        <h3 class="text-md font-semibold text-gray-900 mb-2 line-clamp-2">
                            {{ $blog->title }}
                        </h3>

                        {{-- Description --}}
                        <p class="text-xs">
                            {!! \Illuminate\Support\Str::limit($blog->description ?? '', 310, '...') !!}
                        </p>

                        {{-- Read More Button --}}
                        <div class="mt-4 text-right">
                            <a href="{{ url($blog->slug . '/blog-details') }}"
                                class="text-green-500 text-sm font-semibold hover:underline">
                                Read More →
                            </a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

    </section>

</div>
