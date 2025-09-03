<?php

use App\Models\Hotel;
use Mary\Traits\Toast;
use App\Enum\HotelType;
use App\Models\District;
use App\Models\Division;
use App\Enum\HotelStatus;
use Livewire\Volt\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;

new #[Layout('components.layouts.service-details')] #[Title('Hotel List')] class extends Component {
    use WithPagination, Toast;
    public $hotel_types = [];
    public $hotelList = [];
    public $hotel_keyword;
    public $hotel_keyword_input;
    public $selectedTab;
    public $id;
    public $type;
    public $hotel_checkin;
    public $hotel_checkout;
    public $hotel_type;
    public $hotel_rating;
    public $minPrice = 100;
    public $maxPrice = 900;
    public $averageRating;
    public $hotel_rating_1 = false;
    public $hotel_rating_2 = false;
    public $hotel_rating_3 = false;
    public $hotel_rating_4 = false;
    public $hotel_rating_5 = false;

    protected $queryString = [
        'hotel_keyword' => ['except' => ''],
        'hotel_type' => ['except' => ''],
    ];
    public function mount()
    {
        $this->hotel_keyword = request()->query('keyword');
        $this->selectedTab = request()->query('selectedTab', 'hotel'); // default 'hotel'

        if (!empty($this->hotel_keyword)) {
            $parts = explode('-', $this->hotel_keyword);
            $this->id = $parts[0] ?? null;
            $this->type = $parts[1] ?? null;
        } else {
            $this->id = null;
            $this->type = null;
        }

        $this->hotel_checkin = request()->query('check_in');
        $this->hotel_checkout = request()->query('check_out');
        $this->hotel_type = request()->query('hotel_type', '');

        $this->hotel_types = HotelType::getHotelTypes();
    }

    public function resetFilters()
    {
        // Reset the filter properties to their default values
        $this->hotel_type = ''; // Reset the hotel_type
        $this->hotel_keyword = ''; // Reset the hotel_keyword
        $this->hotel_keyword_input = ''; // Reset the hotel_keyword_input
        $this->hotel_rating_1 = null; // Reset the hotel_rating_1
        $this->hotel_rating_2 = null; // Reset the hotel_rating_2
        $this->hotel_rating_3 = null; // Reset the hotel_rating_3
        $this->hotel_rating_4 = null; // Reset the hotel_rating_4
        $this->hotel_rating_5 = null; // Reset the hotel_rating_5
    }

    public function with()
    {
        $typeMapping = [
            'hotel' => 'id',
            'district' => 'district_id',
            'division' => 'division_id',
        ];

        $query = Hotel::query()->with('rooms')->where('status', HotelStatus::Active);

        // ✅ If hotel_keyword is provided and valid
        if (!empty($this->hotel_keyword) && !empty($this->id) && !empty($this->type) && isset($typeMapping[$this->type])) {
            $columnName = $typeMapping[$this->type];
            $query->where($columnName, $this->id);
        }
        // ✅ Only apply hotel_type if no hotel_keyword
        if (!empty($this->hotel_type)) {
            $query->where('type', $this->hotel_type);
        }

        // ✅ (Other filters — unchanged)
        // rating filters
        $ratings = [];
        if ($this->hotel_rating_1) {
            $ratings[] = 1;
        }
        if ($this->hotel_rating_2) {
            $ratings[] = 2;
        }
        if ($this->hotel_rating_3) {
            $ratings[] = 3;
        }
        if ($this->hotel_rating_4) {
            $ratings[] = 4;
        }
        if ($this->hotel_rating_5) {
            $ratings[] = 5;
        }

        if (!empty($ratings)) {
            $query->whereHas('reviews', fn($q) => $q->whereIn('rating', $ratings));
        }

        // location search
        if ($this->hotel_keyword_input) {
            $division = Division::where('name', 'like', '%' . $this->hotel_keyword_input . '%')->first();
            if ($division) {
                $query->whereIn('district_id', $division->districts->pluck('id'));
            } else {
                $district = District::where('name', 'like', '%' . $this->hotel_keyword_input . '%')->first();
                if ($district) {
                    $query->where('district_id', $district->id);
                } else {
                    $query->where('name', 'like', '%' . $this->hotel_keyword_input . '%');
                }
            }
        }

        $hotels = $query->paginate(10)->withQueryString();

        foreach ($hotels as $hotel) {
            $hotel->averageRating = round($hotel->reviews()->avg('rating'));
        }

        return [
            'hotels' => $hotels,
            'totalHotels' => $query->count(),
        ];
    }
}; ?>

<div class="bg-gray-100">
    <section
        class="relative items-center bg-[url('https://massagetourtravels.com/assets/images/bg/home-bg.png')] bg-no-repeat bg-cover bg-center lg:py-20 md:py-10 z-10">
        <div class="absolute inset-0 bg-slate-900/40"></div>
        <div class="relative py-12 md:py-20">
            <div class="max-w-6xl mx-auto">
                <livewire:home-search-component />
            </div>
            <!--end container-->
        </div>
    </section>
    <section class="mt-8 md:mt-12 lg:mt-16 px-4 md:px-8">
        <div class="max-w-6xl mx-auto">
            <div class="grid grid-cols-1 md:grid-cols-12 gap-4 mt-6 pb-6 items-start">
                <div class="col-span-1 md:col-span-4 lg:col-span-3 border p-3 rounded-md shadow-md bg-white">
                    <div class="flex justify-between my-4 border-b-2 border-green-400 pb-3">
                        <span class="font-semibold text-base">Filter By</span>
                        <x-button class="btn-primary custom-reset-button btn-sm" label="RESET"
                            wire:click="resetFilters" />
                    </div>
                    <div class="my-5">
                        <h3 class="border-b-2 border-green-400 pb-3 mb-2 font-semibold">Search Hotel</h3>
                        <div>
                            <x-input wire:model.live="hotel_keyword_input"
                                placeholder="Search by hotel, district, or division"
                                class="w-full custome-input-field" />
                        </div>
                    </div>
                    <div class="my-5">
                        <h3 class="border-b-2 border-green-400 pb-3 mb-2 font-semibold">Hotel Type</h3>
                        @foreach ($hotel_types as $type)
                            <label class="mb-2 flex items-center gap-2 cursor-pointer">
                                <input type="radio" class="custom-radio-dot" wire:model.live="hotel_type"
                                    value="{{ $type['id'] }}" />
                                <span class="text-sm font-medium">{{ $type['name'] }}</span>
                            </label>
                        @endforeach

                    </div>
                    <div class="my-3">
                        <h3 class="border-t-2 border-green-500 pt-3 mb-2 font-semibold">Property Rating</h3>
                        <div class="mb-2 flex items-center gap-1">
                            <input type="checkbox" wire:model.live="hotel_rating_1" id="hotel_rating_1"
                                class="custom-checkbox" />
                            <label for="hotel_rating_1" class="text-sm">1 Star</label>
                        </div>
                        <div class="mb-2 flex items-center gap-1">
                            <input type="checkbox" wire:model.live="hotel_rating_2" id="hotel_rating_2"
                                class="custom-checkbox" />
                            <label for="hotel_rating_2" class="text-sm">2 Star</label>
                        </div>
                        <div class="mb-2 flex items-center gap-1">
                            <input type="checkbox" wire:model.live="hotel_rating_3" id="hotel_rating_3"
                                class="custom-checkbox" />
                            <label for="hotel_rating_3" class="text-sm">3 Star</label>
                        </div>
                        <div class="mb-2 flex items-center gap-1">
                            <input type="checkbox" wire:model.live="hotel_rating_4" id="hotel_rating_4"
                                class="custom-checkbox" />
                            <label for="hotel_rating_4" class="text-sm">4 Star</label>
                        </div>
                        <div class="mb-2 flex items-center gap-1">
                            <input type="checkbox" wire:model.live="hotel_rating_5" id="hotel_rating_5"
                                class="custom-checkbox" />
                            <label for="hotel_rating_5" class="text-sm">5 Star</label>
                        </div>
                    </div>

                </div>
                <div class="col-span-1 md:col-span-8 lg:col-span-9">
                    @foreach ($hotels as $hotel)
                        <div
                            class="grid grid-cols-12 items-start gap-x-4 gap-y-3 border rounded-md shadow-lg mb-2 p-3 bg-white">
                            <div
                                class="col-span-12 md:col-span-3 h-44 md:h-32 lg:h-44 rounded-md overflow-hidden shadow-md">
                                <img class="object-cover h-full w-full" src="{{ $hotel->thumbnail_link }}"
                                    alt="" />
                            </div>

                            <div class="col-span-12 md:col-span-9 lg:col-span-6">
                                <a class="cursor-pointer text-xl md:text-2xl font-semibold" wire:navigate
                                    href="/hotel/{{ $hotel->slug }}?keyword={{ $hotel_keyword }}&check_in={{ $hotel_checkin }}&check_out={{ $hotel_checkout }}&type={{ $hotel_type }}">
                                    {{ $hotel->name }}
                                </a>
                                <div
                                    class="w-full xs::w-2/4 flex xs:items-start justify-start flex-col gap-2 space-y-3">

                                    @if ($hotel->averageRating)
                                        <div
                                            class="border border-green-500 rounded-md px-1 py-[2px] w-16 my-1 bg-gray-100">
                                            <div class="flex items-center gap-1">
                                                <x-icon name="fas.star" class="text-green-500" />
                                                <p class="text-sm text-gray-800">{{ $hotel->averageRating }} Star</p>
                                            </div>
                                        </div>
                                    @endif
                                    <p class="text-xs md:text-sm font-medium" style="margin-top: 0">
                                        <x-icon name="fas.location-dot" class="text-green-500" />
                                        {{ $hotel->address }}
                                    </p>
                                    @php
                                        $availableRooms = $hotel->rooms
                                            ->filter(function ($room) use ($hotel_checkin, $hotel_checkout) {
                                                return !$room->isBookedForDates($hotel_checkin, $hotel_checkout);
                                            })
                                            ->count();
                                    @endphp

                                    <div class="flex flex-col xs:flex-row items-start xs:items-center gap-1 xs:gap-2"
                                        style="margin-top: 0">
                                        <p class="text-xs md:text-sm font-semibold text-gray-700">
                                            Available Room's:
                                            <span
                                                class="inline-flex items-center justify-center text-green-500 font-bold text-xs md:text-sm">
                                                {{ $availableRooms }}
                                            </span>
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <div
                                class="col-span-12 my-auto mx-auto lg:col-span-3 rounded-lg p-3 flex flex-col items-center">
                                <a wire:navigate
                                    href="/hotel/{{ $hotel->slug }}?keyword={{ $hotel_keyword ?? $hotel->id }}&check_in={{ $hotel_checkin }}&check_out={{ $hotel_checkout }}&type={{ $hotel_type }}&selectedTab={{ $selectedTab }}"
                                    class="py-2 px-6 text-sm font-semibold bg-gradient-to-r from-green-400 to-green-600 hover:from-green-500 hover:to-green-700 transition-all duration-500 text-white uppercase shadow-lg scale-100 hover:scale-110 rounded-full flex items-center gap-2">
                                    <x-icon name="fas.arrow-right" class="w-4 h-4" />
                                    Select
                                </a>
                            </div>
                        </div>
                    @endforeach

                    <div class="mary-table-pagination">
                        {{-- <div class="border border-x-0 border-t-0 border-b-1 border-b-base-300 mb-5"></div> --}}
                        {{ $hotels->onEachSide(1)->links('pagination::tailwind') }}
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
