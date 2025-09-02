<?php

use Carbon\Carbon;
use App\Enum\TourType;
use App\Enum\TourStatus;
use App\Models\District;
use App\Models\Division;
use Livewire\Volt\Component;
use App\Models\TravelProduct;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;
use App\Enum\TravelProductStatus;
use Illuminate\Support\Facades\Auth;

new #[Layout('components.layouts.service-details')] #[Title('Gear Details')] class extends Component {
    public $gear = [];
    public $gearList = [];
    public $gear_keyword;
    public $id;
    public $selectedTab;

    public function mount($slug)
    {
        $this->gear = TravelProduct::where('slug', $slug)->first();
        $this->selectedTab = request()->query('selectedTab', 'gear'); // default 'gear'
        $this->gear_keyword = request()->query('keyword');
        $parts = explode('-', $this->gear_keyword);
        $this->id = $parts[0];

        $this->gearSearch();
    }

    public function gearSearch(string $search = '')
    {
        $searchTerm = '%' . $search . '%';

        $gears = TravelProduct::where('status', TravelProductStatus::Active)
            ->where('is_featured', 1)
            ->where(function ($query) use ($searchTerm) {
                $query->where('title', 'like', $searchTerm)->orWhere('brand', 'like', $searchTerm);
            })
            ->select('id', 'title', 'brand')
            ->latest()
            ->limit(5)
            ->get()
            ->map(
                fn($item) => [
                    'id' => $item->id . '-gear',
                    'name' => $item->title,
                    'subname' => $item->brand ?? '',
                    'icon' => asset('hotel-icon-avatar.svg'),
                ],
            );

        $this->gearList = $gears->toArray(); // Assign search results
    }

    public function searchGear()
    {
        $routeParams = [];

        // Include keyword only if it exists
        if (!empty($this->gear_keyword)) {
            $routeParams['keyword'] = $this->gear_keyword;
        }

        return redirect()->back('frontend.gear.search', $routeParams);
    }

    public function handleReserveClick($slug)
    {
        if (Auth::check()) {
            // User is authenticated, redirect to the reservation page
            return redirect()->route('frontend.gear.booking', [
                'slug' => $slug,
            ]);
        } else {
            // User is not authenticated, dispatch event to show login modal
            $this->dispatch(
                'showLoginModal',
                route('frontend.gear.booking', [
                    'slug' => $slug,
                ]),
            );
        }
    }
}; ?>

<div class="bg-gray-100">
    <livewire:login-modal-component />
    <section
        class="relative items-center bg-[url('https://flyvaly.com/assets/images/bg/home-bg.png')] bg-no-repeat bg-cover bg-center py-20 z-10">
        <div class="absolute inset-0 bg-slate-900/40"></div>
        <div class="relative py-12 md:py-20">
            <div class="max-w-6xl mx-auto">
                <livewire:home-search-component />
            </div>
            <!--end container-->
        </div>
    </section>

    <!-- Rooms Section Start -->
    <section class="pt-10">
        <div class="max-w-6xl mx-auto">
            <div class="grid grid-cols-12 items-start gap-x-4 gap-y-3 border rounded-md shadow-lg mb-2 p-3 bg-white">
                <div class="col-span-12 md:col-span-3 h-44 rounded-md overflow-hidden shadow-md">
                    <img class="object-cover h-full w-full" src="{{ $gear->thumbnail_link ?? '' }}" alt="">
                </div>

                <div class="col-span-12 md:col-span-9 lg:col-span-6">

                    <a class="cursor-pointer text-lg md:text-xl font-semibold"
                        wire:click="handleReserveClick('{{ $gear->slug }}')">{{ $gear->title ?? '' }}</a>

                    <div class="w-full xs::w-2/4 flex xs:items-start justify-start flex-col gap-1">
                        <p class="text-sm text-gray-600">Brand: {{ $gear->brand ?? 'N/A' }}</p>
                        <p class="text-sm text-gray-600">SKU: {{ $gear->sku ?? 'N/A' }}</p>
                    </div>

                </div>
                <div class="col-span-12 my-auto lg:col-span-3 rounded-lg shadow-lg border p-3 bg-gray-100">
                    <div class="w-full xs:w-2/4 flex flex-col items-center xs:items-end justify-end gap-y-1">
                        <small class="text-xs md:text-sm">Starts from</small>
                        <p class="flex items-center gap-x-1 md:text-base text-red-500">
                            @if ($gear->offer_price && $gear->offer_price < $gear->regular_price)
                                <del class="text-sm">BDT {{ $gear->regular_price ?? '' }}</del>
                            @endif
                        </p>
                        <div class="flex items-start">
                            @if ($gear->offer_price && $gear->offer_price < $gear->regular_price)
                                <p class="text-sm md:text-base flex items-center whitespace-nowrap gap-x-1">
                                    <img class="mt-[6px]" src="{{ asset('images/discount-mono.svg') }}" alt="">
                                    @php
                                        $discountPercentage = round(
                                            (($gear->regular_price - $gear->offer_price) / $gear->regular_price) * 100,
                                        );
                                    @endphp
                                    <span class="text-[#f73] text-xs font-bold">{{ $discountPercentage }}%
                                        OFF</span>
                                    <span class="font-bold">BDT {{ $gear->offer_price ?? '' }}</span>
                                    <span class="text-xs"> / Per Pcs</span>
                                </p>
                            @else
                                <p class="text-sm md:text-base flex items-center whitespace-nowrap gap-x-1">
                                    <span class="font-bold">BDT {{ $gear->regular_price ?? '' }}</span>
                                    <span class="text-xs"> / Per Pcs</span>
                                </p>

                            @endif
                        </div>
                        <a wire:click="handleReserveClick('{{ $gear->slug }}')"
                            class="cursor-pointer py-1 px-4 text-xs md:text-sm font-bold bg-green-500 hover:bg-green-600 transition-all duration-100 text-white border uppercase shadow-md rounded-md">Purchase</a>
                    </div>
                </div>
            </div>

        </div>
    </section>
    <!-- Rooms Section End -->

    <!-- Gear Details Section Start -->
    @if ($gear->description)
        <section class="py-6 px-4" x-data="{ tab: 'description' }">
            <div class="max-w-6xl mx-auto bg-white p-6 rounded-md shadow-lg">

                <!-- Tab Buttons -->
                <div class="flex gap-4 border-b pb-2 mb-4">
                    <button @click="tab = 'description'"
                        :class="tab === 'description' ? 'border-b-2 border-green-500 text-green-600 font-semibold' :
                            'text-gray-600'"
                        class="pb-1 transition duration-200 hover:text-green-600 text-sm md:text-base">
                        📝 Description
                    </button>
                </div>

                <!-- Tab Content -->
                <div x-show="tab === 'description'" class="space-y-4 text-gray-700">

                    <p class="text-sm leading-relaxed">{!! $gear->description !!}</p>
                </div>
            </div>
        </section>
    @endif

    <!-- Gear Details Section End -->
</div>
