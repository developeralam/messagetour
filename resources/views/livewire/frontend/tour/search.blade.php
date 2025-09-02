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
        class="relative items-center bg-[url('https://flyvaly.com/assets/images/bg/home-bg.png')] bg-no-repeat bg-cover bg-center py-20 z-10">
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
                        <x-button class="btn-primary custom-reset-button btn-sm" label="RESET"
                            wire:click="resetFilters" />
                    </div>
                    <div class="border-t-2 border-green-400 my-3 font-semibold">
                        <h3 class="py-2 mt-2">Price Range</h3>
                        <div class="mb-2 px-2 mx-1">
                            <div class="flex justify-center items-center">
                                <div class="relative max-w-xl w-full" x-data="rangeSlider()" x-init="init()"
                                    id="range-slider">
                                    <!-- Slider Track -->
                                    <div class="relative z-10 h-1">
                                        <div class="absolute z-10 left-0 right-0 bottom-0 top-0 rounded-md bg-green-50">
                                        </div>
                                        <div class="highlight rounded-md bg-gray-300 absolute top-0 h-1"
                                            :style="`left: ${minPercent}%; width: ${maxPercent - minPercent}%`"></div>

                                        <!-- Min Thumb -->
                                        <div x-ref="minThumb" @mousedown="startDrag('min', $event)"
                                            @touchstart.prevent="startDrag('min', $event)"
                                            class="absolute z-30 w-4 h-4 border-2 border-green-500 rounded-full -mt-1.5 -ml-4 bg-white cursor-pointer"
                                            :style="`left: ${minPercent}%`">
                                        </div>

                                        <!-- Max Thumb -->
                                        <div x-ref="maxThumb" @mousedown="startDrag('max', $event)"
                                            @touchstart.prevent="startDrag('max', $event)"
                                            class="absolute z-30 w-4 h-4 border-2 border-green-500 rounded-full -mt-1.5 -mr-4 bg-white cursor-pointer"
                                            :style="`left: ${maxPercent}%`">
                                        </div>
                                    </div>

                                    <!-- Labels -->
                                    <div class="flex justify-between items-center py-2">
                                        <div>
                                            <label class="text-xs font-normal">Minimum Price</label>
                                            <p class="font-bold text-center">BDT <span
                                                    x-text="currentMin.toLocaleString()"></span></p>
                                        </div>
                                        <div>
                                            <label class="text-xs font-normal">Maximum Price</label>
                                            <p class="font-bold text-center">BDT <span
                                                    x-text="currentMax.toLocaleString()"></span></p>
                                        </div>
                                    </div>

                                    <!-- Livewire Reactive Hidden Inputs -->
                                    <input type="hidden" wire:model.debounce.500ms="min_price"
                                        x-effect="$wire.set('min_price', currentMin)">
                                    <input type="hidden" wire:model.debounce.500ms="max_price"
                                        x-effect="$wire.set('max_price', currentMax)">

                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="my-5">
                        <h3 class="border-b-2 border-green-400 pb-3 mb-2 font-semibold">Search Tour</h3>
                        <div>
                            <x-input wire:model.live="tour_keyword_input"
                                placeholder="Search by tour, district, or division"
                                class="w-full custome-input-field" />
                        </div>
                    </div>
                    <div class="my-5">
                        <h3 class="border-b-2 border-green-400 pb-3 mb-2 font-semibold">Tour Type</h3>
                        @foreach ($tour_types as $type)
                            <label class="mb-2 flex items-center gap-2 cursor-pointer">
                                <input type="radio" class="custom-radio-dot" wire:model.live="tour_type"
                                    value="{{ $type['id'] }}" />
                                <span class="text-sm font-medium">{{ $type['name'] }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>
                <div class="col-span-1 md:col-span-9">
                    @foreach ($tours as $tour)
                        <div
                            class="grid grid-cols-12 items-start gap-x-4 gap-y-3 border rounded-md shadow-lg mb-2 p-3 bg-white">
                            <div class="col-span-12 md:col-span-3 h-44 rounded-md overflow-hidden shadow-md">
                                <img class="object-cover h-full w-full" src="{{ $tour->thumbnail_link }}"
                                    alt="" />
                            </div>

                            <div class="col-span-12 md:col-span-9 lg:col-span-6">
                                <a class="cursor-pointer text-md font-semibold hover:text-green-600" wire:navigate
                                    href="/tour/{{ $tour->slug }}?keyword={{ $tour_keyword ?: $tour->id . '-tours' }}&tour_type={{ $tour_type ?: $tour->type }}">
                                    {{ $tour->title }} <p class="flex items-center gap-x-1 md:text-base text-red-500">
                                    </p>
                                </a>


                                <div class="w-full xs::w-2/4 flex gap-3 mt-2">
                                    <p class="text-xs md:text-sm" style="margin-top: 0">
                                        <x-icon name="fas.location-dot" class="text-green-500" />
                                        <span>{{ $tour->location ?? '' }}</span>
                                    </p>
                                    <ul class="flex items-center" style="margin-top: 0">
                                        <li class="flex items-center gap-1 text-xs rounded-full">
                                            <x-icon name="fas.calendar-days" class="text-green-500" />
                                            <span>
                                                {{ Carbon::parse($tour->start_date)->diffInDays(Carbon::parse($tour->end_date)) }}
                                                Day{{ Carbon::parse($tour->start_date)->diffInDays(Carbon::parse($tour->end_date)) > 1 ? 's' : '' }}
                                            </span>
                                        </li>
                                    </ul>
                                </div>
                                <div class="flex text-sm text-justify mt-3">
                                    📝
                                    <p>
                                        {!! \Illuminate\Support\Str::limit($tour->description ?? '', 255, ' <span class="text-green-500">..</span>') !!}
                                    </p>
                                </div>

                            </div>
                            <div class="col-span-12 my-auto lg:col-span-3 rounded-lg shadow-lg border p-3 bg-gray-100">
                                <div
                                    class="w-full xs:w-2/4 flex flex-col items-center xs:items-end justify-end gap-y-1">
                                    <small class="text-xs text-gray-600 font-semibold">Price starts from (per
                                        person)</small>
                                    @if ($tour->offer_price && $tour->offer_price < $tour->regular_price)
                                        <p class="flex items-center gap-x-1 md:text-base text-red-500">
                                            <del class="text-sm">BDT {{ number_format($tour->regular_price) }}</del>
                                        </p>
                                    @endif

                                    <div class="flex items-start">
                                        @if ($tour->offer_price && $tour->offer_price < $tour->regular_price)
                                            <img class="mt-[6px]" src="{{ asset('images/discount-mono.svg') }}"
                                                alt="" />
                                            <p class="text-sm md:text-base flex items-center whitespace-nowrap gap-x-1">
                                                @php
                                                    $discountPercentage = round(
                                                        (($tour->regular_price - $tour->offer_price) /
                                                            $tour->regular_price) *
                                                            100,
                                                    );
                                                @endphp

                                                <span class="text-[#f73] text-xs font-bold">{{ $discountPercentage }}%
                                                    OFF</span>
                                                <span class="font-bold">BDT
                                                    {{ number_format($tour->offer_price) }}</span>

                                            </p>
                                        @else
                                            <p class="text-sm md:text-base flex items-center whitespace-nowrap gap-x-1">
                                                <span class="font-bold">BDT
                                                    {{ number_format($tour->regular_price) }}</span>
                                            </p>
                                        @endif
                                    </div>
                                    <a wire:navigate
                                        href="/tour/{{ $tour->slug }}?keyword={{ $tour_keyword ?: $tour->id . '-tours' }}&tour_type={{ $tour_type ?: $tour->type }}"
                                        class="py-1 px-4 text-xs md:text-sm mt-4 font-bold bg-green-500 hover:bg-green-600 transition-all duration-100 text-white border border-green-500 uppercase shadow-md rounded-md">
                                        Book Now
                                    </a>
                                </div>
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
    @endpush
    <livewire:corporate-query-component />

</div>
