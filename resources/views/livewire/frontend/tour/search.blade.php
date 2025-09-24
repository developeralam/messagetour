<?php

use Carbon\Carbon;
use App\Models\Tour;
use App\Enum\TourType;
use Mary\Traits\Toast;
use App\Enum\TourStatus;
use App\Models\District;
use App\Models\Division;
use Livewire\Volt\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;

new #[Layout('components.layouts.service-details')] #[Title('Tour List')] class extends Component {
    use WithPagination, Toast;
    public $tour_types = [];
    public $tourList = [];
    public $tour_keyword;
    public $tour_keyword_input;
    public $selectedTab;
    public $id;
    public $type;
    public $tour_type;
    public $min_price = 0;
    public $max_price = 10000;

    protected $queryString = [
        'tour_keyword' => ['except' => ''],
        'tour_type' => ['except' => ''],
    ];
    public function mount($selectedTab = 'tour')
    {
        // Initialize the tour keyword from the query string
        $this->tour_keyword = request()->query('keyword', '');

        // If there is a keyword, split it into 'id' and 'type'
        if (!empty($this->tour_keyword)) {
            $parts = explode('-', $this->tour_keyword);
            $this->id = $parts[0];
            $this->type = $parts[1];
        }

        // Initialize the tour_type from query string or default to empty
        $this->tour_type = request()->query('tour_type', '');

        // Load the tour types
        $this->tour_types = TourType::getTourTypes();

        // Initialize selectedTab
        $this->selectedTab = $selectedTab;
    }

    public function resetFilters()
    {
        // Reset the filter properties to their default values
        $this->tour_type = ''; // Reset the tour_type
        $this->tour_keyword = ''; // Reset the tour_keyword
        $this->tour_keyword_input = ''; // Reset the tour_keyword_input
    }

    public function with()
    {
        $query = Tour::query();

        // If keyword is selected (tour, district, or division)
        if (!empty($this->tour_keyword)) {
            $parts = explode('-', $this->tour_keyword);
            $this->id = $parts[0];
            $this->type = $parts[1] ?? 'tour';

            // Match based on the selected item type
            $columnName = match ($this->type) {
                'tour' => 'id',
                'district' => 'district_id',
                'division' => 'division_id',
                default => 'id',
            };

            // 💡 Apply ONLY the keyword filter and return early (skip other filters)
            $query->where($columnName, $this->id);
        } else {
            // 🔁 When keyword is NOT selected, fallback to free-text or type search

            if ($this->tour_keyword_input) {
                $division = Division::where('name', 'like', '%' . $this->tour_keyword_input . '%')->first();
                if ($division) {
                    $districtIds = $division->districts->pluck('id');
                    $query->whereIn('district_id', $districtIds);
                } else {
                    $district = District::where('name', 'like', '%' . $this->tour_keyword_input . '%')->first();
                    if ($district) {
                        $query->where('district_id', $district->id);
                    } else {
                        $query->where('title', 'like', '%' . $this->tour_keyword_input . '%');
                    }
                }
            }

            // ✅ Apply tour_type filter only if keyword was not used
            if (!empty($this->tour_type)) {
                $query->where('type', $this->tour_type);
            }
        }

        // ✅ Price filter still applies globally
        $query->where(function ($q) {
            $q->whereBetween('offer_price', [$this->min_price, $this->max_price])->orWhere(function ($q2) {
                $q2->whereNull('offer_price')->whereBetween('regular_price', [$this->min_price, $this->max_price]);
            });
        });

        $query->where('status', TourStatus::Active);
        $query->whereDate('start_date', '>=', Carbon::now());
        $query->whereDate('validity', '>=', Carbon::now());

        return [
            'tours' => $query->latest()->paginate(10)->withQueryString(),
        ];
    }
}; ?>

<div class="bg-gray-100">
    <section
        class="relative items-center bg-[url('https://massagetourtravels.com/assets/images/bg/home-bg.png')] bg-no-repeat bg-cover bg-center py-20 z-10">
        <div class="absolute inset-0 bg-slate-900/40"></div>
        <div class="relative py-12 md:py-20">
            <div class="max-w-6xl mx-auto">
                <livewire:home-search-component />
            </div>
            <!--end container-->
        </div>
    </section>
    <div class="bg-white mt-10 max-w-6xl mx-auto px-3 md:px-0">
        <div class="flex justify-between items-center bg-white border rounded-lg py-6 px-3 shadow-md">
            <h3 class="text-xl font-bold">Need a Customized Tour?</h3>
            <a @click="$dispatch('openModel')"
                class="bg-green-500 text-white font-semibold py-2 px-6 rounded-md shadow-lg hover:bg-green-600 transition-all duration-300 ease-in-out">
                Request Now
            </a>
        </div>
    </div>

    <section>
        <div class="max-w-6xl mx-auto px-3 md:px-0">
            <div class="grid grid-cols-1 md:grid-cols-12 gap-4 mt-6 pb-6 items-start">
                <div class="col-span-1 md:col-span-3 border p-3 rounded-md shadow-md bg-white md:h-[620px]">
                    <div class="flex justify-between mt-4 mb-4">
                        <span class="font-semibold text-md">Filter By</span>
                        <x-button class="btn-primary custom-reset-button btn-sm" label="RESET" wire:click="resetFilters" />
                    </div>
                    <div class="border-t-2 border-green-400 my-3 font-semibold">
                        <h3 class="py-2 mt-2">Price Range</h3>
                        <div class="mb-2 px-2 mx-1">
                            <div class="flex justify-center items-center">
                                <div class="relative max-w-xl w-full" x-data="rangeSlider()" x-init="init()" id="range-slider">
                                    <!-- Slider Track -->
                                    <div class="relative z-10 h-1">
                                        <div class="absolute z-10 left-0 right-0 bottom-0 top-0 rounded-md bg-green-50">
                                        </div>
                                        <div class="highlight rounded-md bg-gray-300 absolute top-0 h-1"
                                            :style="`left: ${minPercent}%; width: ${maxPercent - minPercent}%`"></div>

                                        <!-- Min Thumb -->
                                        <div x-ref="minThumb" @mousedown="startDrag('min', $event)" @touchstart.prevent="startDrag('min', $event)"
                                            class="absolute z-30 w-4 h-4 border-2 border-green-500 rounded-full -mt-1.5 -ml-4 bg-white cursor-pointer"
                                            :style="`left: ${minPercent}%`">
                                        </div>

                                        <!-- Max Thumb -->
                                        <div x-ref="maxThumb" @mousedown="startDrag('max', $event)" @touchstart.prevent="startDrag('max', $event)"
                                            class="absolute z-30 w-4 h-4 border-2 border-green-500 rounded-full -mt-1.5 -mr-4 bg-white cursor-pointer"
                                            :style="`left: ${maxPercent}%`">
                                        </div>
                                    </div>

                                    <!-- Labels -->
                                    <div class="flex justify-between items-center py-2">
                                        <div>
                                            <label class="text-xs font-normal">Minimum Price</label>
                                            <p class="font-bold text-center">BDT <span x-text="currentMin.toLocaleString()"></span></p>
                                        </div>
                                        <div>
                                            <label class="text-xs font-normal">Maximum Price</label>
                                            <p class="font-bold text-center">BDT <span x-text="currentMax.toLocaleString()"></span></p>
                                        </div>
                                    </div>

                                    <!-- Livewire Reactive Hidden Inputs -->
                                    <input type="hidden" wire:model.debounce.500ms="min_price" x-effect="$wire.set('min_price', currentMin)">
                                    <input type="hidden" wire:model.debounce.500ms="max_price" x-effect="$wire.set('max_price', currentMax)">

                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="my-5">
                        <h3 class="border-b-2 border-green-400 pb-3 mb-2 font-semibold">Search Tour</h3>
                        <div>
                            <x-input wire:model.live="tour_keyword_input" placeholder="Search by tour, district, or division"
                                class="w-full custome-input-field" />
                        </div>
                    </div>
                    <div class="my-5">
                        <h3 class="border-b-2 border-green-400 pb-3 mb-2 font-semibold">Tour Type</h3>
                        @foreach ($tour_types as $type)
                            <label class="mb-2 flex items-center gap-2 cursor-pointer">
                                <input type="radio" class="custom-radio-dot" wire:model.live="tour_type" value="{{ $type['id'] }}" />
                                <span class="text-sm font-medium">{{ $type['name'] }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>
                <div class="col-span-1 md:col-span-9">
                    @foreach ($tours as $tour)
                        <div
                            class="group relative bg-white rounded-2xl shadow-lg hover:shadow-2xl transition-all duration-500 mb-6 overflow-hidden border border-gray-100 hover:border-green-200">
                            <!-- Gradient overlay for image -->
                            <div class="relative">
                                <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-transparent z-10"></div>
                                <img class="w-full h-64 md:h-72 object-cover group-hover:scale-105 transition-transform duration-700"
                                    src="{{ $tour->thumbnail_link }}" alt="{{ $tour->title }}" />

                                <!-- Floating badge for tour type -->
                                <div class="absolute top-4 left-4 z-20">
                                    <span
                                        class="bg-gradient-to-r from-green-500 to-emerald-600 text-white px-3 py-1 rounded-full text-xs font-semibold shadow-lg">
                                        {{ $tour->type ?? 'Tour' }}
                                    </span>
                                </div>

                                <!-- Discount badge -->
                                @if ($tour->offer_price && $tour->offer_price < $tour->regular_price)
                                    @php
                                        $discountPercentage = round((($tour->regular_price - $tour->offer_price) / $tour->regular_price) * 100);
                                    @endphp
                                    <div class="absolute top-4 right-4 z-20">
                                        <div
                                            class="bg-gradient-to-r from-red-500 to-pink-600 text-white px-3 py-1 rounded-full text-xs font-bold shadow-lg animate-pulse">
                                            {{ $discountPercentage }}% OFF
                                        </div>
                                    </div>
                                @endif

                                <!-- Tour title overlay -->
                                <div class="absolute bottom-4 left-4 right-4 z-20">
                                    <h3 class="text-white text-lg md:text-xl font-bold mb-2 drop-shadow-lg">
                                        {{ $tour->title }}
                                    </h3>
                                </div>
                            </div>

                            <!-- Content section -->
                            <div class="p-6">
                                <!-- Location and duration info -->
                                <div class="flex flex-wrap items-center gap-4 mb-4">
                                    <div class="flex items-center gap-2 text-gray-600">
                                        <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center">
                                            <x-icon name="fas.location-dot" class="text-green-600 text-sm" />
                                        </div>
                                        <span class="text-sm font-medium">{{ $tour->location ?? 'Location not specified' }}</span>
                                    </div>
                                    <div class="flex items-center gap-2 text-gray-600">
                                        <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                                            <x-icon name="fas.calendar-days" class="text-blue-600 text-sm" />
                                        </div>
                                        <span class="text-sm font-medium">
                                            {{ Carbon::parse($tour->start_date)->diffInDays(Carbon::parse($tour->end_date)) }}
                                            Day{{ Carbon::parse($tour->start_date)->diffInDays(Carbon::parse($tour->end_date)) > 1 ? 's' : '' }}
                                        </span>
                                    </div>
                                </div>

                                <!-- Description -->
                                <div class="mb-6">
                                    <p class="text-gray-700 text-sm leading-relaxed line-clamp-3">
                                        {!! \Illuminate\Support\Str::limit($tour->description ?? '', 200, '...') !!}
                                    </p>
                                </div>

                                <!-- Price and booking section -->
                                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 pt-4 border-t border-gray-100">
                                    <!-- Price section -->
                                    <div class="flex flex-col">
                                        <span class="text-xs text-gray-500 font-medium mb-1">Starting from (per person)</span>
                                        <div class="flex items-center gap-2">
                                            @if ($tour->offer_price && $tour->offer_price < $tour->regular_price)
                                                <span class="text-lg text-gray-400 line-through">BDT {{ number_format($tour->regular_price) }}</span>
                                                <span class="text-2xl font-bold text-green-600">BDT {{ number_format($tour->offer_price) }}</span>
                                            @else
                                                <span class="text-2xl font-bold text-green-600">BDT {{ number_format($tour->regular_price) }}</span>
                                            @endif
                                        </div>
                                    </div>

                                    <!-- Action buttons -->
                                    <div class="flex gap-3">
                                        <a wire:navigate
                                            href="/tour/{{ $tour->slug }}?keyword={{ $tour_keyword ?: $tour->id . '-tours' }}&tour_type={{ $tour_type ?: $tour->type }}"
                                            class="flex-1 md:flex-none px-6 py-3 bg-gradient-to-r from-green-500 to-emerald-600 hover:from-green-600 hover:to-emerald-700 text-white font-semibold rounded-xl transition-all duration-300 transform hover:scale-105 shadow-lg hover:shadow-xl text-center">
                                            View Details
                                        </a>
                                        <a wire:navigate
                                            href="/tour/{{ $tour->slug }}?keyword={{ $tour_keyword ?: $tour->id . '-tours' }}&tour_type={{ $tour_type ?: $tour->type }}"
                                            class="flex-1 md:flex-none px-6 py-3 bg-white border-2 border-green-500 text-green-600 hover:bg-green-500 hover:text-white font-semibold rounded-xl transition-all duration-300 transform hover:scale-105 shadow-lg hover:shadow-xl text-center">
                                            Book Now
                                        </a>
                                    </div>
                                </div>
                            </div>

                            <!-- Hover effect overlay -->
                            <div
                                class="absolute inset-0 bg-gradient-to-r from-green-500/0 to-emerald-600/0 group-hover:from-green-500/5 group-hover:to-emerald-600/5 transition-all duration-500 pointer-events-none">
                            </div>
                        </div>
                    @endforeach

                    <div class="mary-table-pagination">
                        {{-- <div class="border border-x-0 border-t-0 border-b-1 border-b-base-300 mb-5"></div> --}}
                        {{ $tours->onEachSide(1)->links('pagination::tailwind') }}
                    </div>
                </div>
            </div>
        </div>
    </section>
    @push('custom-script')
        <script>
            function rangeSlider() {
                return {
                    minValue: 0,
                    maxValue: 10000,
                    currentMin: 0,
                    currentMax: 10000,
                    dragging: null,
                    minPercent: 0,
                    maxPercent: 100,

                    init() {
                        this.updatePercents();
                        window.addEventListener('mouseup', this.stopDrag.bind(this));
                        window.addEventListener('touchend', this.stopDrag.bind(this));
                        window.addEventListener('mousemove', this.handleMove.bind(this));
                        window.addEventListener('touchmove', this.handleMove.bind(this));
                    },

                    startDrag(type, event) {
                        this.dragging = type;
                    },

                    stopDrag() {
                        this.dragging = null;
                    },

                    handleMove(e) {
                        if (!this.dragging) return;

                        const clientX = e.touches ? e.touches[0].clientX : e.clientX;
                        const rect = document.getElementById('range-slider').getBoundingClientRect();
                        const percent = Math.max(0, Math.min((clientX - rect.left) / rect.width, 1));
                        const value = Math.round(percent * (this.maxValue - this.minValue) + this.minValue);

                        if (this.dragging == 'min') {
                            this.currentMin = Math.min(value, this.currentMax - 500);
                        } else if (this.dragging == 'max') {
                            this.currentMax = Math.max(value, this.currentMin + 500);
                        }

                        this.updatePercents();
                    },

                    updatePercents() {
                        this.minPercent = ((this.currentMin - this.minValue) / (this.maxValue - this.minValue)) * 100;
                        this.maxPercent = ((this.currentMax - this.minValue) / (this.maxValue - this.minValue)) * 100;
                    },
                };
            }
        </script>

        <style>
            /* Custom styles for the new tour card design */
            .line-clamp-3 {
                display: -webkit-box;
                -webkit-line-clamp: 3;
                -webkit-box-orient: vertical;
                overflow: hidden;
            }

            /* Enhanced hover effects */
            .group:hover .group-hover\:scale-105 {
                transform: scale(1.05);
            }

            /* Smooth transitions for all elements */
            .transition-all {
                transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            }

            /* Custom shadow effects */
            .shadow-lg {
                box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            }

            .hover\:shadow-2xl:hover {
                box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
            }

            /* Gradient text effects */
            .bg-gradient-to-r {
                background-image: linear-gradient(to right, var(--tw-gradient-stops));
            }

            /* Custom animation for discount badge */
            @keyframes pulse {

                0%,
                100% {
                    opacity: 1;
                }

                50% {
                    opacity: 0.8;
                }
            }

            .animate-pulse {
                animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
            }

            /* Responsive improvements */
            @media (max-width: 768px) {
                .group .absolute.top-4 {
                    top: 1rem;
                }

                .group .absolute.bottom-4 {
                    bottom: 1rem;
                }

                .group .absolute.left-4 {
                    left: 1rem;
                }

                .group .absolute.right-4 {
                    right: 1rem;
                }
            }
        </style>
    @endpush
    <livewire:corporate-query-component />

</div>
