<?php

use App\Models\Blog;
use Livewire\Volt\Component;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;

new #[Layout('components.layouts.app')] #[Title('Blog Details')] class extends Component {
    public $blogDetails;
    public $blogs;

    public function mount($slug)
    {
        $this->blogDetails = Blog::where('slug', $slug)->firstOrFail();
        $this->blogs = Blog::where('id', '!=', $this->blogDetails->id)->latest()->get();
    }
}; ?>

<div class="bg-gray-100 lg:py-10 py-4 px-4 md:px-6 xl:px-0">
    <!-- Blog Header -->
    <section>
        <div class="max-w-6xl mx-auto px-4 grid grid-cols-1 md:grid-cols-2 gap-6 bg-white rounded-md shadow-sm p-6">
            <div class="flex flex-col gap-2 my-auto pl-4">
                <h1 class="font-[teko] text-3xl md:text-4xl font-semibold tracking-wide leading-tight">
                    {{ $blogDetails->title }}
                </h1>
                <p class="text-base text-gray-700 leading-relaxed">{!! $blogDetails->description !!}</p>
            </div>
            <div>
                <img src="{{ $blogDetails->blog_image_link }}" alt="Blog Image"
                    class="w-full h-full rounded-md object-cover">
            </div>
        </div>
    </section>

    <!-- Author + Blog Body -->
    <section class="max-w-6xl mx-auto py-4">
        <div class="bg-white rounded-md shadow-sm p-6 leading-relaxed text-gray-800 text-base flex flex-col gap-6">
            {{-- Author Section --}}
            <div class="flex flex-wrap justify-between items-center gap-4 border-b border-dotted border-gray-300 pb-4">
                <div class="flex items-center gap-4 flex-1 min-w-0">
                    <div class="h-14 w-14 rounded-full overflow-hidden flex-shrink-0">
                        <img src="{{ $blogDetails->image_link }}" class="h-full w-full object-cover" alt="Author Image">
                    </div>
                    <div class="flex flex-col min-w-0">
                        <h4 class="text-sm font-semibold text-gray-800 truncate">{{ $blogDetails->name }}</h4>
                        <p class="text-xs font-medium text-gray-500 truncate">{{ $blogDetails->post }}</p>
                    </div>
                </div>
                <div class="text-sm text-green-500 font-medium whitespace-nowrap">
                    {{ $blogDetails->created_at->format('d M, Y') }}
                </div>
            </div>

            {{-- Blog Body --}}
            <div>
                {!! $blogDetails->body !!}
            </div>
        </div>
    </section>


    <!-- Suggested Blogs -->
    <section class="max-w-6xl mx-auto px-4 py-10 bg-white">
        <div class="text-center mb-8">
            <h2 class="text-3xl md:text-4xl lg:text-5xl font-[teko]">Suggested for <span
                    class="text-green-500">You</span></h2>
            <p class="text-sm text-gray-500">Don’t miss out on your chance to stay up on the latest trends.</p>
        </div>
        @php
            $blogCount = $blogs->count(); // Get the number of offers
        @endphp
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4 owl-carousel offer-carousel"
            data-items="{{ $blogCount }}">
            @foreach ($blogs as $blog)
                <div
                    class="bg-white border rounded-xl shadow hover:shadow-md transition overflow-hidden flex flex-col justify-between">
                    {{-- Blog Image --}}
                    <a href="{{ url($blog->slug . '/blog-details') }}" class="block aspect-[4/3] overflow-hidden">
                        <img src="{{ $blog->blog_image_link }}" alt="{{ $blog->title }}"
                            class="w-full h-full object-cover transition-transform duration-300 hover:scale-105">
                    </a>

                    {{-- Blog Info --}}
                    <div class="p-4 flex flex-col flex-grow">
                        <div class="flex items-center text-sm mb-2 gap-1 italic">
                            <span class="text-gray-700 font-semibold">{{ $blog->name }}</span> |
                            <span class="text-green-500 text-xs">{{ $blog->created_at->format('d M, Y') }}</span>
                        </div>

                        <h3 class="text-base font-semibold text-gray-900 line-clamp-2 mb-2">
                            {{ $blog->title }}
                        </h3>

                        <p class="text-sm text-gray-600 line-clamp-3 flex-grow">
                            {!! \Illuminate\Support\Str::limit($blog->description ?? '', 310, '...') !!}
                        </p>

                        <a href="{{ url($blog->slug . '/blog-details') }}"
                            class="mt-4 text-green-500 text-sm font-medium hover:underline text-right">
                            Read More →
                        </a>
                    </div>
                </div>
            @endforeach
        </div>
    </section>
</div>
