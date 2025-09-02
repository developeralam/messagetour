<?php

use App\Models\Tour;
use App\Models\Hotel;
use App\Models\Offer;
use App\Models\Division;
use Mary\Traits\Toast;
use Livewire\Volt\Component;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;
use Illuminate\Support\Collection;

new #[Layout('components.layouts.app')] #[Title('Home')] class extends Component {
    use Toast;
    public Collection $tours;
    public Collection $hotels;
    public Collection $offers;
    public $sylhetHotelCount = 0;
    public $coxsBazarHotelCount = 0;
    public $chittagongHotelCount = 0;
    public $kuakataHotelCount = 0;
    public $totalTours = 0;
    public $averageTourRating;
    public $averageHotelRating;

    public function mount()
    {
        // Fetch Offers, Featured Tours, and Featured Hotels
        $this->offers = Offer::take(8)->get();
        $this->tours = Tour::featured()
            ->checkValidity()
            ->active()
            ->withCount('reviews')
            ->withAvg('reviews', 'rating')
            ->latest()
            ->take(8)
            ->get()
            ->map(function ($tour) {
                $tour->reviews_avg_rating = $tour->reviews_avg_rating ? round($tour->reviews_avg_rating, 1) : 0;
                return $tour;
            });

        $this->hotels = Hotel::with(['country:id,name', 'division:id,name', 'district:id,name'])
            ->withCount('reviews')
            ->withAvg('reviews', 'rating')
            ->featured()
            ->latest()
            ->take(8)
            ->get()
            ->map(function ($hotel) {
                $hotel->reviews_avg_rating = $hotel->reviews_avg_rating ? round($hotel->reviews_avg_rating, 1) : 0;
                return $hotel;
            });

        // Total number of tours
        $this->totalTours = Tour::count();

        // Fetch Sylhet Division Hotel Count
        $this->sylhetHotelCount = $this->getHotelCountByDivision('Sylhet');

        // Fetch Chittagong Division Hotel Count
        $this->chittagongHotelCount = $this->getHotelCountByDivision('Chittagong');

        // Count hotels in Cox's Bazar
        $this->coxsBazarHotelCount = $this->getHotelCountByDistrict("Cox's Bazar");

        // Count hotels in Kuakata
        $this->kuakataHotelCount = $this->getHotelCountByDistrict('Kuakata');
    }

    /**
     * Get the hotel count by division.
     *
     * @param string $divisionName
     * @return int
     */
    private function getHotelCountByDivision(string $divisionName): int
    {
        $division = Division::where('name', $divisionName)->first();

        if ($division) {
            $districtIds = $division->districts()->pluck('id');
            return Hotel::whereIn('district_id', $districtIds)->count();
        }

        return 0;
    }

    /**
     * Get the hotel count by district name.
     *
     * @param string $districtName
     * @return int
     */
    private function getHotelCountByDistrict(string $districtName): int
    {
        return Hotel::whereHas('district', function ($query) use ($districtName) {
            $query->where('name', $districtName);
        })->count();
    }
}; ?>

<div>
    <section class="md:pb-16 md:pt-10 py-5 md:py-8">

        <div class="w-11/12 md:max-w-6xl mx-auto">
            <div class="grid grid-cols-1 pb-4 md:pb-8 text-center">
                <h3 class="mb-1 md:mb-3 md:text-3xl text-2xl leading-normal font-semibold">
                    Explore Bangladesh
                </h3>
                <p class="text-slate-500 max-w-xl mx-auto text-xs md:text-sm">
                    Prepare to experience Bangladesh's rich culture and explore the majestic beauties of Cox’s
                    Bazar, Sylhet, Bandarban, Sajek Valley, Rangamati etc. Plan your trip now!
                </p>
            </div>

            @php
                $places = ['Cox\'s Bazar', 'Sylhet', 'Chittagong', 'Kuakata'];
                $images = ['coxsbazar', 'sylhet', 'chittagong', 'kuakata'];
                $counts = [$coxsBazarHotelCount, $sylhetHotelCount, $chittagongHotelCount, $kuakataHotelCount];
            @endphp

            {{-- Owl Carousel for mobile to large screens --}}
            <div class="xl:hidden owl-carousel explore-carousel">
                @foreach ($places as $i => $place)
                    <div class="group relative overflow-hidden rounded-lg shadow">
                        <img loading="lazy" src="{{ asset('assets/images/explore/' . $images[$i] . '.webp') }}"
                            class="scale-125 group-hover:scale-100 duration-500 h-64 md:h-full w-full object-cover"
                            alt="{{ $place }}">
                        <div
                            class="absolute inset-0 bg-gradient-to-b to-slate-900 from-transparent opacity-0 group-hover:opacity-100 duration-500">
                        </div>
                        <div class="absolute p-4 bottom-0 start-0">
                            <a href=""
                                class="text-lg font-medium text-white hover:text-green-500 duration-500 ease-in-out">
                                {{ $place }}
                            </a>
                            <p class="text-white/70 group-hover:text-white text-sm duration-500">
                                {{ $counts[$i] }} Hotels
                            </p>
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- Static grid layout for xl and above --}}
            <div class="hidden xl:grid lg:grid-cols-4 md:grid-cols-3 sm:grid-cols-2 grid-cols-1 relative gap-3">
                @foreach ($places as $i => $place)
                    <div class="group relative overflow-hidden rounded-lg shadow">
                        <img loading="lazy" src="{{ asset('assets/images/explore/' . $images[$i] . '.webp') }}"
                            class="scale-125 group-hover:scale-100 duration-500 h-full w-full object-cover"
                            alt="{{ $place }}">
                        <div
                            class="absolute inset-0 bg-gradient-to-b to-slate-900 from-transparent opacity-0 group-hover:opacity-100 duration-500">
                        </div>
                        <div class="absolute p-4 bottom-0 start-0">
                            <a href=""
                                class="text-lg font-medium text-white hover:text-green-500 duration-500 ease-in-out">
                                {{ $place }}
                            </a>
                            <p class="text-white/70 group-hover:text-white text-sm duration-500">
                                {{ $counts[$i] }} Hotels
                            </p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
        <!--Explore Bangladesh container end-->

        <div class="w-11/12 md:max-w-6xl mx-auto lg:mt-16 mt-10">
            <div class="grid md:grid-cols-12 grid-cols-1 items-center gap-6 relative">
                <!-- Left section with image and counters -->
                <div class="md:col-span-5">
                    <div class="relative">
                        <img loading="lazy" src="{{ asset('assets/images/about.jpg') }}"
                            class="mx-auto md:mx-0 rounded-xl shadow overflow-hidden" alt="">

                        <!-- Visitor Counter -->
                        <div
                            class="absolute flex items-center bottom-7 md:bottom-16 md:-start-10 start-5 p-4 rounded-lg border shadow-md w-56 m-3 bg-white z-10">
                            <div
                                class="flex items-center justify-center h-[65px] min-w-[65px] bg-red-500/5 text-red-500 text-center rounded-xl me-3">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                    viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                    stroke-linecap="round" stroke-linejoin="round" class="feather feather-users size-6">
                                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                                    <circle cx="9" cy="7" r="4"></circle>
                                    <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                                    <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                                </svg>
                            </div>
                            <div class="flex-1" x-data="{
                                count: 0,
                                target: 4589,
                                duration: 800,
                                start() {
                                    const stepTime = Math.max(1, Math.floor(this.duration / this.target));
                                    let interval = setInterval(() => {
                                        if (this.count < this.target) {
                                            this.count++;
                                        } else {
                                            clearInterval(interval);
                                        }
                                    }, stepTime);
                                }
                            }" x-init="start">
                                <span class="text-slate-400">Visitor</span>
                                <p class="text-xl font-bold">
                                    <span x-text="count + '+'"></span>
                                </p>
                            </div>


                        </div>

                        <!-- Travel Packages Counter -->
                        <div
                            class="absolute flex items-center top-8 md:top-16 md:-end-10 end-5 p-4 rounded-lg shadow-md border bg-white w-60 m-3 z-10">
                            <div
                                class="flex items-center justify-center h-[65px] min-w-[65px] bg-red-500/5 text-red-500 text-center rounded-xl me-3">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                    viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                    stroke-linecap="round" stroke-linejoin="round" class="feather feather-globe size-6">
                                    <circle cx="12" cy="12" r="10"></circle>
                                    <line x1="2" y1="12" x2="22" y2="12"></line>
                                    <path
                                        d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z">
                                    </path>
                                </svg>
                            </div>
                            <div class="flex-1" x-data="{
                                count: 0,
                                target: {{ $totalTours }},
                                duration: 1500,
                                start() {
                                    const stepTime = Math.abs(Math.floor(this.duration / this.target));
                                    let interval = setInterval(() => {
                                        if (this.count < this.target) {
                                            this.count++;
                                        } else {
                                            clearInterval(interval);
                                        }
                                    }, stepTime);
                                }
                            }" x-init="start">
                                <span class="text-slate-400">Travel Packages</span>
                                <p class="text-xl font-bold"><span x-text="count + '+'"></span></p>
                            </div>

                        </div>
                    </div>
                </div>

                <!-- Right section with content -->
                <div class="w-11/12 mx-auto md:col-span-7 relative z-10">
                    <div class="lg:ms-8">
                        <h3 class="mb-3 md:mb-5 md:text-3xl text-2xl leading-normal font-semibold">
                            World Best
                            Travel <br> Agency: Flyvaly</h3>

                        <p class="text-slate-400 max-w-xl mb-3 md:mb-5 text-sm md:text-base">Get instant helpful
                            resources about anything on the go,
                            easily implement secure money transfer solutions, boost your daily efficiency, connect to
                            other app users
                            and create your own Flyvaly network, and much more with just a few taps. commodo consequat.
                            Duis aute irure.</p>

                        <a href=""
                            class="py-1 px-4 inline-block tracking-wide align-middle duration-500 text-sm md:text-base text-center bg-red-500 text-white rounded-md hover:bg-red-600">Read
                            More <i class="mdi mdi-chevron-right align-middle ms-0.5"></i></a>
                    </div>
                </div>

                <!-- Background image -->
                <div class="absolute bottom-0 start-1/3 -z-1">
                    <img loading="lazy" src="{{ asset('assets/images/map-plane-big.png') }}" class="lg:w-[600px] w-96"
                        alt="">
                </div>
            </div>
        </div>
        <!--Best Travel Agency container end-->

        {{-- Tour Section container start --}}
        <div class="w-11/12 md:max-w-6xl mx-auto lg:mt-16 mt-10">
            <h3 class="mb-1 md:mb-2 md:text-3xl text-2xl leading-normal font-semibold text-center">
                Our Tour Packages
                for You
            </h3>
            <div class="h-[2px] w-24 bg-gradient-to-r from-[#009f51] to-[#851001] rounded mx-auto mb-4"></div>
            <p class="text-slate-500 max-w-3xl mx-auto text-sm md:text-base text-center">Plan your dream
                gateway
                and choose from uncountable tour
                packages at Flyvaly. Book
                our holiday packages for the best deals on any international trip.</p>
            <div class="relative w-full max-w-6xl overflow-hidden">
                <div class="owl-carousel tour-carousel">
                    @foreach ($tours as $tour)
                        <div class="p-2 rounded-lg overflow-hidden group">
                            <div class="md:h-[320px] h-72 group relative overflow-hidden rounded-lg shadow">
                                <a
                                    href="/tour/{{ $tour->slug }}?keyword={{ $tour->id . '-tours' }}&tour_type={{ $tour->type }}">
                                    <img src="{{ $tour->thumbnail_link }}"
                                        class="h-full w-full object-cover rounded-md group-hover:scale-125 scale-100 duration-500" />
                                </a>
                                <div
                                    class="absolute inset-0 bg-gradient-to-b to-slate-900 from-transparent opacity-0 group-hover:opacity-100 duration-500">
                                </div>
                            </div>
                            <a href="/tour/{{ $tour->slug }}?keyword={{ $tour->id . '-tours' }}&tour_type={{ $tour->type }}"
                                class="text-sm font-medium mt-2 hover:text-green-600 hover:underline">
                                {!! \Illuminate\Support\Str::limit(
                                    $tour->title ?? '',
                                    30,
                                    ' <span class="text-green-500 cursor-pointer">..</span>',
                                ) !!}
                            </a>
                            @if ($tour->reviews_count > 0 && $tour->reviews_avg_rating > 0)
                                <div class="flex items-center gap-1">
                                    <x-icon name="fas.star" class="text-[#f9a825]" />
                                    <span class="font-semibold">
                                        {{ $tour->reviews_avg_rating }}
                                    </span>
                                    <span class="text-sm text-[#009f51]">
                                        ({{ $tour->reviews_count }}
                                        {{ \Illuminate\Support\Str::plural('review', $tour->reviews_count) }})
                                    </span>
                                </div>
                            @endif

                        </div>
                    @endforeach
                </div>
            </div>
        </div><!--Tour Section end container-->

        {{-- Hotel Section container start --}}
        <div class="w-11/12 md:max-w-6xl mx-auto lg:mt-16 mt-10">
            <h3 class="mb-1 md:mb-2 md:text-3xl text-2xl leading-normal font-semibold text-center">
                Best
                Hotels for
                Your Next Trip
            </h3>
            <div class="h-[2px] w-24 bg-gradient-to-r from-[#009f51] to-[#851001] rounded mx-auto mb-4"></div>
            <p class="text-slate-500 max-w-3xl mx-auto text-sm md:text-base text-center">Luxurious or
                budget-friendly hotels,
                villas or resorts,
                browse accommodations at Flyvaly
                that meet the need. Book Long-term or short-term accommodation from our hotel deals</p>
            <div class="relative w-full max-w-6xl overflow-hidden">
                <!-- Slider Track -->
                <div class="owl-carousel tour-carousel">
                    @foreach ($hotels as $hotel)
                        <div class="p-2 rounded-lg overflow-hidden group">
                            <div class="md:h-[320px] h-72 group relative overflow-hidden rounded-lg shadow">
                                <a wire:navigate
                                    href="/hotel/{{ $hotel->slug }}?keyword={{ $hotel->id . '-hotel' }}&type={{ $hotel->type }}">
                                    <img src="{{ $hotel->thumbnail_link }}"
                                        class="h-full w-full object-cover rounded-md group-hover:scale-125 scale-100 duration-500" />
                                </a>
                                <div
                                    class="absolute inset-0 bg-gradient-to-b to-slate-900 from-transparent opacity-0 group-hover:opacity-100 duration-500">
                                </div>
                            </div>
                            <a wire:navigate
                                href="/hotel/{{ $hotel->slug }}?keyword={{ $hotel->id . '-hotel' }}&type={{ $hotel->type }}"
                                class="text-sm font-medium mt-2 hover:text-green-600 hover:underline">{!! \Illuminate\Support\Str::limit(
                                    $hotel->name ?? '',
                                    30,
                                    ' <span class="text-green-500 cursor-pointer">..</span>',
                                ) !!}
                            </a>
                            @if ($hotel->reviews_count > 0 && $hotel->reviews_avg_rating > 0)
                                <div class="flex items-center gap-1">
                                    <x-icon name="fas.star" class="text-[#f9a825]" />
                                    <span class="font-semibold">
                                        {{ $hotel->reviews_avg_rating }} </span>
                                    <span class="text-sm text-[#009f51]">
                                        ({{ $hotel->reviews_count }}
                                        {{ \Illuminate\Support\Str::plural('review', $hotel->reviews_count) }})
                                    </span>
                                </div>
                            @endif

                        </div>
                    @endforeach
                </div>
            </div>
        </div>
        <!--Hotel Section end container-->

        <!--Become a Partne Section Start-->
        <div class="w-full py-10 md:py-16 bg-gradient-to-r from-red-200 via-green-200 to-blue-100 mt-10 md:mt-16">
            <div
                class="max-w-6xl mx-auto flex flex-col md:flex-row items-center justify-between gap-6 px-4 text-center md:text-left">

                <!-- Left Text Content -->
                <div class="w-full md:w-2/3">
                    <h2 class="text-2xl lg:text-3xl font-bold text-gray-900 shining-effect">
                        Grow Your Business with FLYVALY
                    </h2>
                    <p class="mt-2 text-gray-700 text-sm md:text-[16px] italic font-semibold">
                        Partner with us to reach more customers and expand your brand effortlessly.
                    </p>
                </div>

                <!-- Button -->
                <div class="w-full md:w-auto">
                    <a href="/partner/register"
                        class="inline-block bg-green-500 hover:bg-green-600 text-white font-semibold px-6 py-3 rounded-full shadow hover:shadow-lg scale-100 hover:scale-110 transition-all duration-500">
                        Become a Partner
                    </a>
                </div>

            </div>
        </div>

        <!--Become a Partne Section End-->

        <!-- Exclusive Section Start-->
        <div class="w-11/12 md:max-w-6xl mx-auto md:mt-16 mt-10">
            <div class="text-center pb-3">
                <h3 class="mb-3 text-2xl md:text-3xl font-semibold">Exclusive Offers</h3>
                <div class="h-[2px] w-24 bg-gradient-to-r from-[#009f51] to-[#851001] rounded mx-auto mb-4"></div>
            </div>

            @php
                $offerCount = $offers->count(); // Get the number of offers
            @endphp
            <div class="owl-carousel offer-carousel" data-items="{{ $offerCount }}">
                @foreach ($offers as $offer)
                    <div class="relative group overflow-hidden rounded-md shadow-md h-40">
                        <img src="{{ $offer->thumbnail_link }}" alt="Offer Image"
                            class="w-full h-full object-cover transform transition-transform duration-300 group-hover:scale-105">

                        <div
                            class="absolute inset-0 bg-[#009f51] bg-opacity-60 text-white flex flex-col justify-end p-4 transform translate-y-full group-hover:translate-y-0 transition-all duration-500 ease-in-out">
                            <!-- Title with responsive typography and truncation -->
                            <h3 class="text-base md:text-lg font-bold truncate mb-4">{!! \Illuminate\Support\Str::limit($offer->title ?? '', 50, '...') !!}
                            </h3>

                            <div class="flex flex-row justify-between gap-2">
                                <span class="text-lg font-semibold block sm:inline">BDT
                                    {{ number_format($offer->coupon->amount ?? 0) }}</span>
                                <span class="text-lg text-white block sm:inline">
                                    {{ optional($offer->coupon)->expiry_date?->format('d M, Y') ?? '' }}
                                </span>
                            </div>
                            <a href="/offer/{{ $offer->slug }}"
                                class="mt-2 bg-white text-green-700 text-sm text-center py-2 rounded hover:underline transition font-semibold">
                                View Details
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
        <!-- Exclusive Section End-->

        <!-- Exclusive Section Start-->
    </section>
</div>
