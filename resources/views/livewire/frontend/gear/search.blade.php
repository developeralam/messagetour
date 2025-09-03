<?php

use App\Models\Tour;
use Mary\Traits\Toast;
use Livewire\Volt\Component;
use Livewire\WithPagination;
use App\Models\TravelProduct;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;
use App\Enum\TravelProductStatus;

new #[Layout('components.layouts.service-details')] #[Title('Gear List')] class extends Component {
    use WithPagination, Toast;
    public $gearList = [];
    public $gear_keyword;
    public $gear_keyword_input;
    public $id;
    public $selectedTab;
    public $min_price = 0;
    public $max_price = 10000;

    protected $queryString = [
        'gear_keyword' => ['except' => ''],
    ];

    public function mount()
    {
        // Initialize gear keyword from the query string or set as empty string
        $this->gear_keyword = request()->query('keyword', ''); // Default to empty string
        $this->selectedTab = request()->query('selectedTab', 'gear'); // Default 'gear'
        $this->id = request()->query('id', null); // Get 'id' from the query string (if provided)

        // If there is a gear keyword, extract the ID from the keyword
        if (!empty($this->gear_keyword)) {
            $parts = explode('-', $this->gear_keyword);
            $this->id = $parts[0];
        }
    }

    public function updated($property)
    {
        // Trigger the gear search when the gear keyword input is updated
        if ($property == 'gear_keyword_input') {
            $this->gearSearch();
        }
    }

    public function resetFilters()
    {
        // Reset the filter properties to their default values
        $this->gear_keyword = '';
        $this->gear_keyword_input = '';
        $this->id = null;
    }

    public function gearSearch()
    {
        // Initialize the query for searching TravelProduct
        $query = TravelProduct::where('status', TravelProductStatus::Active)->where('is_featured', 1);

        if (!empty($this->gear_keyword)) {
            $parts = explode('-', $this->gear_keyword);
            $this->id = $parts[0];

            // Match based on the selected item type
            $columnName = match ($this->id) {
                'gear' => 'id',
                default => 'id',
            };

            // 💡 Apply ONLY the keyword filter and return early (skip other filters)
            $query->where($columnName, $this->id);
        } else {
            // 🔁 When keyword is NOT selected, fallback to free-text or type search

            if (!empty($this->gear_keyword_input)) {
                $query->where('title', 'like', '%' . $this->gear_keyword_input . '%');
            }
        }

        // price filter here
        $query->whereBetween('offer_price', [$this->min_price, $this->max_price])->orWhere(function ($q) {
            $q->whereNull('offer_price')->whereBetween('regular_price', [$this->min_price, $this->max_price]);
        });

        // If an id is set (e.g., when selecting a specific gear), filter by id
        if ($this->id) {
            $query->where('id', $this->id);
        }

        // Paginate the result (show 12 products per page)
        return $query->latest()->paginate(12);
    }

    public function with()
    {
        $gears = $this->gearSearch() ?? collect();

        return [
            'gears' => $gears,
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
    <section class="pt-10">
        <div class="max-w-6xl mx-auto px-3 md:px-0">
            <div class="grid grid-cols-1 md:grid-cols-12 gap-4 mt-6 pb-6 items-start">
                <div class="col-span-1 md:col-span-3 border p-3 rounded-md shadow-md bg-white md:h-96">
                    <div class="flex justify-between my-4">
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
                    <!-- Property Name Search Input -->
                    <div class="mb-4 mx-1 border-t-2 border-green-400 py-2">
                        <label for="gear_keyword_input" class="block text-sm font-semibold mb-1">Search Gear</label>
                        <div class="relative flex items-center">
                            <input type="text" id="gear_keyword_input" placeholder="Product Name"
                                wire:model.live="gear_keyword_input"
                                class="w-full px-3 py-2 border rounded-md focus:outline-none text-sm" />
                        </div>
                    </div>
                </div>
                <div
                    class="col-span-1 md:col-span-9 grid grid-cols-2 md:grid-cols-3 xl:grid-cols-3 gap-x-2 gap-y-4 items-start">

                    @if ($gears->count() > 0)
                        @if ($gears->count() === 1 && $gear_keyword)
                            @foreach ($gears as $gear)
                                <div
                                    class="col-span-2 md:col-span-1 xl:col-span-1 flex flex-col bg-white rounded-xl shadow-lg overflow-hidden transition-transform duration-300 hover:scale-105 hover:shadow-2xl h-full">
                                    <div class="relative">
                                        <img src="{{ $gear->thumbnail_link }}" alt="{{ $gear->title }}"
                                            class="w-full h-40 object-cover transition-transform duration-300" />
                                        @if ($gear->offer_price)
                                            <span
                                                class="absolute top-3 left-3 bg-gradient-to-r from-green-700 to-green-500 text-white text-xs font-bold px-3 py-1 rounded-full shadow">
                                                {{ round((($gear->regular_price - $gear->offer_price) / $gear->regular_price) * 100) }}%
                                                OFF
                                            </span>
                                        @endif
                                    </div>
                                    <div class="flex flex-col flex-1 p-4 gap-1">
                                        <p class="text-sm text-green-700 font-medium truncate">{{ $gear->brand }}</p>
                                        <a href="/gear/{{ $gear->slug }}?keyword={{ $gear_keyword }}" wire:navigate
                                            class="text-base md:text-sm font-semibold text-gray-800 hover:text-green-600 line-clamp-2 transition-colors duration-200">
                                            {!! \Illuminate\Support\Str::limit(
                                                $gear->title ?? '',
                                                32,
                                                ' <span class="text-green-500 cursor-pointer">..</span>',
                                            ) !!}
                                        </a>
                                        <div class="flex items-center gap-3">
                                            @if ($gear->offer_price)
                                                <span class="text-red-500 font-semibold text-sm line-through">৳
                                                    {{ $gear->regular_price }}</span>
                                                <span class="text-green-600 font-bold text-base">৳
                                                    {{ $gear->offer_price }}</span>
                                            @else
                                                <span class="text-green-600 font-bold text-base">৳
                                                    {{ $gear->regular_price }}</span>
                                            @endif
                                        </div>
                                        <a href="/gear/{{ $gear->slug }}?keyword={{ $gear_keyword }}&selectedTab={{ $selectedTab }}"
                                            wire:navigate
                                            class="inline-block text-center bg-green-600 hover:bg-green-700 text-white font-semibold rounded-md mt-2 px-4 py-1 transition-colors duration-200 shadow">
                                            View Details
                                        </a>
                                    </div>
                                </div>
                            @endforeach
                        @else
                            @foreach ($gears as $gear)
                                <div
                                    class="flex flex-col bg-white rounded-xl shadow-lg overflow-hidden transition-transform duration-300 hover:scale-105 hover:shadow-2xl h-full">
                                    <div class="relative">
                                        <img src="{{ $gear->thumbnail_link }}" alt="{{ $gear->title }}"
                                            class="w-full h-40 object-cover transition-transform duration-300" />
                                        @if ($gear->offer_price)
                                            <span
                                                class="absolute top-3 left-3 bg-gradient-to-r from-green-700 to-green-500 text-white text-xs font-bold px-3 py-1 rounded-full shadow">
                                                {{ round((($gear->regular_price - $gear->offer_price) / $gear->regular_price) * 100) }}%
                                                OFF
                                            </span>
                                        @endif
                                    </div>
                                    <div class="flex flex-col flex-1 p-4 gap-1">
                                        <p class="text-sm text-green-700 font-medium truncate">{{ $gear->brand }}</p>
                                        <a href="/gear/{{ $gear->slug }}?keyword={{ $gear_keyword }}" wire:navigate
                                            class="text-base md:text-sm font-semibold text-gray-800 hover:text-green-600 line-clamp-2 transition-colors duration-200">
                                            {!! \Illuminate\Support\Str::limit(
                                                $gear->title ?? '',
                                                32,
                                                ' <span class="text-green-500 cursor-pointer">..</span>',
                                            ) !!}
                                        </a>
                                        <div class="flex items-center gap-3">
                                            @if ($gear->offer_price)
                                                <span class="text-red-500 font-semibold text-sm line-through">৳
                                                    {{ $gear->regular_price }}</span>
                                                <span class="text-green-600 font-bold text-base">৳
                                                    {{ $gear->offer_price }}</span>
                                            @else
                                                <span class="text-green-600 font-bold text-base">৳
                                                    {{ $gear->regular_price }}</span>
                                            @endif
                                        </div>
                                        <a href="/gear/{{ $gear->slug }}?keyword={{ $gear_keyword }}" wire:navigate
                                            class="inline-block text-center bg-green-600 hover:bg-green-700 text-white font-semibold rounded-md mt-2 px-4 py-1 transition-colors duration-200 shadow">
                                            View Details
                                        </a>
                                    </div>
                                </div>
                            @endforeach
                        @endif
                    @else
                        <p class="text-center text-gray-500">No gears found.</p>
                    @endif
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
</div>
