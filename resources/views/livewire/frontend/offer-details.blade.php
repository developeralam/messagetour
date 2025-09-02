<?php

use App\Models\Offer;
use Livewire\Volt\Component;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;

new #[Layout('components.layouts.service-details')] #[Title('Offer Details')] class extends Component {
    public Offer $offer;

    public function mount($slug)
    {
        $this->offer = Offer::with('faqs')->where('slug', $slug)->firstOrFail();
    }
}; ?>

<div>
    <!-- Mobile: show <img> -->
    <div class="block md:hidden">
        <img src="{{ $offer->thumbnail_link }}" alt="Offer Banner" class="w-full h-auto" />
    </div>

    <!-- Desktop: show background section -->
    <section class="hidden md:block relative bg-cover bg-center bg-no-repeat md:py-60 z-10"
        style="background-image: url('{{ $offer->thumbnail_link }}')">
    </section>




    <section class="bg-white py-8 md:py-12">
        <div class="max-w-6xl mx-auto px-4 text-center">
            <!-- Top Highlights with Animation -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-10">
                <div
                    class="bg-green-100 p-6 rounded-lg shadow-md transform transition duration-300 hover:scale-105 hover:shadow-xl animate-fade-in-up delay-100">
                    <div class="text-green-500 text-3xl mb-1">
                        <!-- Gift SVG Icon -->
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 inline" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M20 12v7a2 2 0 01-2 2H6a2 2 0 01-2-2v-7m16 0H4m16 0V7a2 2 0 00-2-2h-3.28a2 2 0 01-1.72-1c-.6-1-2-1-2.6 0a2 2 0 01-1.72 1H6a2 2 0 00-2 2v5" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold">Offer</h3>
                    <p class="text-sm">{{ $offer->title ?? '' }}</p>
                </div>
                <div
                    class="bg-green-100 p-6 rounded-lg shadow-md transform transition duration-300 hover:scale-105 hover:shadow-xl animate-fade-in-up delay-400">
                    <div class="text-green-500 text-3xl mb-1">
                        <!-- Coupon SVG Icon -->
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 inline" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor">
                            <rect x="3" y="7" width="18" height="10" rx="2" stroke-width="2"
                                stroke="currentColor" fill="none" />
                            <path stroke="currentColor" stroke-width="2" d="M7 7v10M17 7v10" />
                            <circle cx="12" cy="12" r="1.5" fill="currentColor" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold mb-1">Coupon Code</h3>
                    <p class="font-semibold text-green-700 px-2 py-1 rounded text-sm">
                        {{ $offer->coupon->code ?? '' }}
                    </p>
                </div>
                <div
                    class="bg-green-100 p-6 rounded-lg shadow-md transform transition duration-300 hover:scale-105 hover:shadow-xl animate-fade-in-up delay-300">
                    <div class="text-green-500 text-3xl mb-1">
                        <!-- Calendar SVG Icon -->
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 inline" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor">
                            <rect width="18" height="18" x="3" y="4" rx="2" stroke-width="2"
                                stroke="currentColor" fill="none" />
                            <path stroke="currentColor" stroke-width="2" d="M16 2v4M8 2v4M3 10h18" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold mb-1">Offer Validity</h3>
                    <p>
                        Till
                        <span class="font-semibold">
                            {{ $offer->validaty ? $offer->validaty->format('d M, Y') : '' }}
                        </span>
                    </p>
                </div>
                <div
                    class="bg-green-100 p-6 rounded-lg shadow-md transform transition duration-300 hover:scale-105 hover:shadow-xl animate-fade-in-up delay-200">
                    <div class="text-green-500 text-3xl mb-1">
                        <!-- Users/Group SVG Icon -->
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 inline" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87m9-5.13a4 4 0 11-8 0 4 4 0 018 0zm6 8v2a2 2 0 01-2 2h-6a2 2 0 01-2-2v-2a6 6 0 0112 0z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold mb-1">Applicable Users</h3>
                    <p>{{ $offer->applicable_users ?? '' }}</p>
                </div>
            </div>

            <!-- Steps to Avail Offer with Timeline Animation -->
            <h2 class="text-2xl md:text-3xl font-bold mb-6 animate-fade-in">How to Avail the Offer</h2>
            <div class="relative grid md:grid-cols-3 gap-6 text-left px-3">
                @foreach (['step1', 'step2', 'step3'] as $i => $step)
                    <div
                        class="p-5 border-l-4 border-green-500 bg-gray-50 rounded shadow relative animate-fade-in-up delay-{{ ($i + 1) * 100 }}">
                        <div
                            class="absolute -left-7 top-1/2 transform -translate-y-1/2 w-6 h-6 bg-green-500 text-white flex items-center justify-center rounded-full shadow-lg animate-pulse">
                            {{ $i + 1 }}
                        </div>
                        <p>
                            @switch($step)
                                @case('step1')
                                    {!! $offer->avail_this_offer_step_1 ?? '' !!}
                                @break

                                @case('step2')
                                    {!! $offer->avail_this_offer_step_2 ?? '' !!}
                                @break

                                @case('step3')
                                    {!! $offer->avail_this_offer_step_3 ?? '' !!}
                                @break
                            @endswitch
                        </p>
                    </div>
                @endforeach
                <!-- Timeline vertical line for desktop -->
                <div class="hidden md:block absolute left-3 top-8 bottom-8 w-1 bg-green-200 z-0"></div>
            </div>

            <!-- CTA Button with Animation -->
            <div class="mt-10 animate-fade-in-up delay-500">
                <a href="#"
                    class="bg-gradient-to-r from-green-400 to-green-600 hover:from-green-500 hover:to-green-700 text-white px-10 py-5 rounded-full font-semibold shadow-lg transition-all duration-300 transform hover:scale-105 animate-bounce-slow">
                    Book Now
                </a>
            </div>
        </div>
    </section>

    <!-- Terms and Conditions with Fade Animation -->
    <section class="bg-gray-50 py-10 mt-8 animate-fade-in">
        <div class="max-w-6xl mx-auto px-4">
            <h3 class="text-xl font-bold mb-4 text-green-600">Terms & Conditions</h3>
            {!! $offer->description !!}
        </div>
    </section>

    <!-- FAQs Accordion with Animation -->
    @if ($offer->faqs->isNotEmpty())
        <section class="bg-white py-10 animate-fade-in-up delay-200">
            <div class="max-w-6xl mx-auto px-4">
                <h3 class="text-xl font-bold mb-6 text-green-600">Frequently Asked Questions</h3>

                <div class="space-y-4">
                    @foreach ($offer->faqs as $faq)
                        <div x-data="{ open: false }"
                            class="border border-gray-300 rounded-lg transition-all duration-300 shadow-md animate-fade-in-up delay-300 overflow-hidden">

                            <button @click="open = !open"
                                class="w-full text-left font-semibold text-gray-700 cursor-pointer p-4 flex justify-between items-center focus:outline-none">
                                <span>{!! $faq->question !!}</span>
                                <svg :class="{ 'rotate-180': open }"
                                    class="h-5 w-5 text-gray-500 transition-transform duration-200" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>

                            <div x-ref="answer" x-show="open" x-transition:enter="transition-all ease-out duration-300"
                                x-transition:enter-start="opacity-0 max-h-0"
                                x-transition:enter-end="opacity-100 max-h-[500px]"
                                x-transition:leave="transition-all ease-in duration-200"
                                x-transition:leave-start="opacity-100 max-h-[500px]"
                                x-transition:leave-end="opacity-0 max-h-0"
                                class="px-4 pb-4 text-sm text-gray-600 overflow-hidden" x-cloak>
                                {!! $faq->answer !!}
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

</div>
