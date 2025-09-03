<?php

use Carbon\Carbon;
use App\Models\Hotel;
use Mary\Traits\Toast;
use App\Enum\HotelType;
use App\Models\HotelRoom;
use Livewire\Volt\Component;
use Livewire\Attributes\Rule;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;
use App\Models\HotelRoomBooking;
use Illuminate\Support\Facades\Auth;

new #[Layout('components.layouts.service-details')] #[Title('Hotel Details')] class extends Component {
    use Toast;
    public $hotel = [];
    public $selectedTab;
    public $reviews = [];
    public $availableRooms = [];
    public $hotel_types = [];
    public $hotel_keyword;
    public $id;
    public $type;
    public $hotel_checkin;
    public $hotel_checkout;
    public $hotel_type;
    public $averageRating;

    #[Rule('required')]
    public $rating;

    #[Rule('required')]
    public $comment;

    public function mount($slug)
    {
        $this->selectedTab = request()->query('selectedTab', 'hotel'); // default 'hotel'
        $this->hotel = Hotel::with(['rooms', 'country', 'division', 'district'])
            ->where('slug', $slug)
            ->firstOrFail();
        $this->averageRating = round($this->hotel->reviews()->avg('rating'), 1);

        $this->hotel_keyword = request()->query('keyword');
        $parts = explode('-', $this->hotel_keyword ?? '-');
        $this->id = $parts[0] ?? null;
        $this->type = $parts[1] ?? null;
        $this->hotel_checkin = request()->query('check_in') ?? Carbon::today()->format('Y-m-d');
        $this->hotel_checkout = request()->query('check_out') ?? Carbon::today()->addDays(2)->format('Y-m-d');
        $this->hotel_type = request()->query('hotel_type');
        $this->reviews = $this->hotel->reviews()->with('user')->latest()->get();

        // Check if there are any existing bookings
        $hasBookings = HotelRoomBooking::exists();

        if (!$hasBookings) {
            // No bookings exist, show all rooms
            $this->availableRooms = $this->hotel->rooms;
        } else {
            // Filter rooms: Exclude rooms that are booked for the selected dates
            $this->availableRooms = $this->hotel->rooms->filter(function ($room) {
                return !$room->isBookedForDates($this->hotel_checkin, $this->hotel_checkout);
            });
        }

        $this->hotel_types = HotelType::getHotelTypes();
    }

    /**
     * Automatically called by Livewire whenever a public property is updated.
     * Filters available rooms whenever the check-in or check-out date changes.
     *
     * @param string $property The name of the updated property
     */
    public function updated($property)
    {
        // React only when check-in or check-out is updated
        if (in_array($property, ['hotel_checkin', 'hotel_checkout'])) {
            $this->filterAvailableRooms();
        }
    }

    /**
     * Filters hotel rooms based on check-in and check-out dates.
     *
     * - If both dates are selected, exclude rooms already booked in that range.
     * - If either date is missing, return all rooms (no filter).
     */
    public function filterAvailableRooms()
    {
        // Validate both dates are set before filtering
        if ($this->hotel_checkin && $this->hotel_checkout) {
            $checkIn = Carbon::parse($this->hotel_checkin);
            $checkOut = Carbon::parse($this->hotel_checkout);

            // Fetch rooms fresh from DB each time to ensure latest data
            $this->availableRooms = HotelRoom::where('hotel_id', $this->hotel->id)
                ->get()
                ->filter(function ($room) use ($checkIn, $checkOut) {
                    return !$room->isBookedForDates($checkIn, $checkOut);
                });
        } else {
            // Show all rooms freshly from DB
            $this->availableRooms = HotelRoom::where('hotel_id', $this->hotel->id)->get();
        }
    }

    public function submitReview()
    {
        if (!Auth::check()) {
            $this->error('Please log in to submit a review.');
            return;
        }

        $this->validate([
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'required|string|max:1000',
        ]);
        try {
            $this->hotel->reviews()->create([
                'user_id' => Auth::id(),
                'rating' => $this->rating,
                'comment' => $this->comment,
            ]);
            $this->reset(['rating', 'comment']);
            $this->success('Thank you for your review!');
        } catch (\Throwable $th) {
            $this->error(env('APP_DEBUG', false) ? $th->getMessage() : 'Something went wrong');
        }
    }

    public function handleReserveClick($slug)
    {
        if (Auth::check()) {
            // User is authenticated, redirect to the reservation page
            return redirect()->route('frontend.hotel.booking', [
                'slug' => $slug,
                'check_in' => $this->hotel_checkin,
                'check_out' => $this->hotel_checkout,
            ]);
        } else {
            // User is not authenticated, dispatch event to show login modal
            $this->dispatch(
                'showLoginModal',
                route('frontend.hotel.booking', [
                    'slug' => $slug,
                    'check_in' => $this->hotel_checkin,
                    'check_out' => $this->hotel_checkout,
                ]),
            );
        }
    }
}; ?>

<div class="bg-gray-100">
    <livewire:login-modal-component />

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
    <section class="mt-8 md:mt-12 lg:mt-16 px-4 md:px-8">
        <div class="max-w-6xl mx-auto bg-white md:p-4 rounded-md shadow-lg">
            <div class="grid grid-cols-12 gap-4 px-3 md:px-0 pb-2 md:pb-0">
                <div class="col-span-12 md:col-span-6">
                    @if ($hotel->images)
                        <div x-data="{ img: '{{ $hotel->images->first()['url'] }}' }" class="w-full">
                            <img loading="lazy" :src="img" alt="Gold Necklace"
                                class="w-full md:h-[500px] h-52 rounded-md pt-2 md:pt-0" />
                            <div class="flex space-x-2 mt-3 overflow-x-scroll">
                                @foreach ($hotel->images as $image)
                                    <img loading="lazy" src="{{ $image['url'] }}" @click="img = '{{ $image['url'] }}'"
                                        alt="Thumbnail" class="w-20 h-20 cursor-pointer rounded-md" />
                                @endforeach
                            </div>
                        </div>
                    @else
                        <div class="w-full md:h-[500px] h-auto rounded-md overflow-hidden">
                            <img loading="lazy" src="{{ $hotel->thumbnail_link }}" alt=""
                                class="h-full w-full object-cover" />
                        </div>
                    @endif
                </div>
                <div class="col-span-12 md:col-span-6 flex-col space-y-1.5 md:space-y-3">
                    <h2 class="text-2xl font-bold">{{ $hotel->name }}</h2>
                    @if ($averageRating)
                        <div class="border border-green-500 rounded-md inline-block px-1 py-[2px]">
                            <div class="flex gap-1">
                                <x-icon name="fas.star" class="text-[#f73] mt-1" />
                                <p class="text-sm">{{ $averageRating }} Star</p>
                            </div>
                        </div>
                    @endif
                    <p class="text-sm font-normal flex gap-x-2 items-center hover:text-[#009f51]">
                        <x-icon class="text-green-600 w-3 h-3" name="fas.location-dot" />
                        {{ $hotel->address }}
                    </p>
                    <p class="text-sm font-normal flex gap-x-2 items-center hover:text-[#009f51]">
                        <x-icon class="text-green-600 w-3 h-3" name="fas.phone" />
                        <a href="tel:+152534-468-854">{{ $hotel->phone }}</a>
                    </p>
                    <p class="text-sm font-normal flex gap-x-2 items-center hover:text-[#009f51]">
                        <x-icon class="text-green-600 w-3 h-3" name="fas.envelope" />
                        <a href="mailto:{{ $hotel->email }}">{{ $hotel->email }}</a>
                    </p>
                    <p class="text-sm font-normal flex gap-x-2 items-center hover:text-[#009f51]">
                        <x-icon class="text-green-600 w-3 h-3" name="fas.globe" />
                        <a href="{{ $hotel->website }}" target="_blank">{{ $hotel->website }}</a>
                    </p>
                    <table class="w-full border border-green-600 mt-5 text-left">
                        <tbody>
                            <tr>
                                <th scope="row" class="bg-gray-100 p-3 border-b font-semibold">Hotel Type</th>
                                <td class="p-3 border-b">{{ $hotel->type->name }}</td>
                            </tr>
                            <tr>
                                <th class="bg-gray-100 p-3 border-b font-semibold">Check In</th>
                                <td class="p-3 border-b">
                                    <input type="date" wire:model.lazy="hotel_checkin" class="form-input w-full" />
                                </td>
                            </tr>
                            <tr>
                                <th class="bg-gray-100 p-3 border-b font-semibold">Check Out</th>
                                <td class="p-3 border-b">
                                    <input type="date" wire:model.lazy="hotel_checkout" class="form-input w-full" />
                                </td>
                            </tr>

                            <tr>
                                <th scope="row" class="bg-gray-100 p-3 border-b font-semibold">Country</th>
                                <td class="p-3 border-b">{{ $hotel->country->name }}</td>
                            </tr>
                            <tr>
                                <th scope="row" class="bg-gray-100 p-3 border-b font-semibold">Division</th>
                                <td class="p-3 border-b">{{ $hotel->division->name }}</td>
                            </tr>
                            <tr>
                                <th scope="row" class="bg-gray-100 p-3 font-semibold">District</th>
                                <td class="p-3">{{ $hotel->district->name }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>

    <!-- Rooms Section Start -->
    <section class="mt-12 md:mt-26 px-4 md:px-8">
        <div class="max-w-6xl mx-auto flex flex-col gap-y-3">
            <h3 class="text-xl md:text-2xl font-semibold">Available Room's</h3>
            @foreach ($availableRooms as $room)
                <div
                    class="grid grid-cols-12 items-start gap-x-4 gap-y-3 border rounded-md shadow-lg mb-2 p-3 md:p-4 bg-white">
                    <div class="col-span-12 md:col-span-3 h-full rounded-md overflow-hidden shadow-md">
                        <img class="object-cover h-full w-full" src="{{ $room->thumbnail_link }}" alt="" />
                    </div>

                    <div class="col-span-12 md:col-span-9 lg:col-span-6">
                        <a class="cursor-pointer text-xl md:text-2xl font-semibold" wire:navigate
                            href="/hotel/{{ $hotel->slug }}?keyword={{ $hotel_keyword }}&check_in={{ $hotel_checkin }}&check_out={{ $hotel_checkout }}&type={{ $hotel_type }}">
                            {{ $room->name }}
                        </a>
                        <div class="w-full xs::w-2/4 flex xs:items-start justify-start flex-col gap-1">
                            <p class="text-xs md:text-sm font-medium" style="margin-top: 0">
                                <x-icon name="fas.location-dot" class="text-green-600" />
                                {{ $hotel->address }}
                            </p>
                            <div class="flex items-center gap-2">
                                <p class="font-semibold text-sm">Room No:</p>
                                <span class="inline-flex items-center py-1 rounded-full text-xs font-medium">
                                    {{ $room->room_no }}
                                </span>
                            </div>
                            <div class="flex items-center gap-2">
                                <p class="font-semibold text-sm">Room Type:</p>
                                <span
                                    class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    <x-icon name="fas.bed" class="w-3 h-3 me-1 text-green-600" />
                                    {{ $room->type->name }}
                                </span>
                            </div>
                            @if ($room->aminities->isNotEmpty())
                                <p class="font-semibold mt-2 text-sm">Facilities</p>
                                <div class="flex flex-wrap gap-1">
                                    @foreach ($room->aminities as $aminity)
                                        <span
                                            class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            <x-icon name="fas.check-circle" class="w-3 h-3 me-1 text-green-600" />
                                            {{ $aminity->name }}
                                        </span>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>
                    <div class="col-span-12 lg:col-span-3 rounded-lg shadow-lg border p-3 bg-gray-100">
                        <div class="w-full xs:w-2/4 flex flex-col items-center xs:items-end justify-end gap-y-1">
                            <small class="text-xs md:text-sm">Starts from</small>
                            @if ($room->offer_price && $room->offer_price < $room->regular_price)
                                <p class="flex items-center gap-x-1 text-sm md:text-base">
                                    @php
                                        $discountPercentage = round(
                                            (($room->regular_price - $room->offer_price) / $room->regular_price) * 100,
                                        );
                                    @endphp

                                    <del class="text-red-500 text-sm">BDT {{ $room->regular_price }}</del>
                                </p>
                            @endif

                            <div class="flex items-start">
                                @if ($room->offer_price && $room->offer_price < $room->regular_price)
                                    <img class="mt-[6px]" src="{{ asset('images/discount-mono.svg') }}"
                                        alt="" />
                                    <p class="text-sm md:text-base flex items-center whitespace-nowrap gap-x-1">
                                        <span class="text-[#f73] text-xs font-bold">{{ $discountPercentage ?? '' }}
                                            OFF</span>
                                        <span class="font-bold">BDT {{ $room->offer_price }}</span>
                                    </p>
                                @else
                                    <p class="text-sm md:text-base flex items-center whitespace-nowrap gap-x-1">
                                        <span class="font-bold">BDT {{ $room->regular_price }}</span>
                                    </p>
                                @endif
                            </div>
                            <p class="text-xs whitespace-nowrap text-[#f73] font-semibold">for 1 night, per room</p>
                            <a wire:click="handleReserveClick('{{ $room->slug }}')"
                                class="py-1 mt-3 px-4 text-xs md:text-sm font-bold bg-green-500 hover:bg-green-600 transition-all duration-100 text-white uppercase shadow-md rounded-md">
                                Book Now
                            </a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </section>
    <!-- Rooms Section End -->

    <!-- Hotel Details Section Start -->
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

                <button @click="tab = 'review'"
                    :class="tab === 'review' ? 'border-b-2 border-green-500 text-green-600 font-semibold' : 'text-gray-600'"
                    class="pb-1 transition duration-200 hover:text-green-600 text-sm md:text-base">
                    🌟 Reviews
                </button>
            </div>

            <!-- Tab Content -->
            <div x-show="tab === 'description'" class="space-y-4 text-gray-700">

                <p class="text-sm leading-relaxed">{!! $hotel->description !!}</p>
            </div>

            <!-- Review Section -->
            <div x-show="tab === 'review'" x-cloak>
                <!-- Review Section -->
                <section class="p-4">
                    <div class="max-w-6xl mx-auto space-y-8">

                        <!-- Section Header -->
                        <div class="flex items-center gap-2 border-b pb-2">
                            <svg width="22" height="22" fill="none" stroke="#22C55E" stroke-width="1.5"
                                viewBox="0 0 24 24" class="mt-[2px]">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.357 4.19a1 1 0 00.95.69h4.394c.969 0 1.371 1.24.588 1.81l-3.562 2.59a1 1 0 00-.364 1.118l1.357 4.19c.3.921-.755 1.688-1.538 1.118l-3.562-2.59a1 1 0 00-1.176 0l-3.562 2.59c-.783.57-1.838-.197-1.538-1.118l1.357-4.19a1 1 0 00-.364-1.118L2.56 9.617c-.783-.57-.38-1.81.588-1.81h4.394a1 1 0 00.95-.69l1.357-4.19z" />
                            </svg>
                            <h2 class="text-xl font-semibold text-gray-800">Reviews</h2>
                        </div>

                        @forelse ($reviews as $review)
                            <div class="bg-gray-50 border border-gray-200 rounded-lg p-4 shadow-sm">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-3">
                                        <img src="{{ $review->user->customer->image_link ?? '/empty-user.jpg' }}"
                                            class="w-10 h-10 rounded-full object-cover" />
                                        <div>
                                            <p class="text-sm font-semibold">{{ $review->user->name }}</p>
                                            <p class="text-xs text-gray-500">
                                                {{ $review->created_at->diffForHumans() }}
                                            </p>
                                        </div>
                                    </div>
                                    <!-- Dynamic Stars -->
                                    <div class="flex gap-1 text-sm">
                                        @for ($i = 1; $i <= 5; $i++)
                                            <span
                                                class="{{ $i <= $review->rating ? 'text-yellow-400' : 'text-gray-300' }}">★</span>
                                        @endfor
                                    </div>
                                </div>
                                <p class="text-sm text-gray-700 mt-3 leading-relaxed">
                                    {{ $review->comment }}
                                </p>
                            </div>
                        @empty
                            <p class="text-gray-500 text-sm">No reviews available for this hotel yet.</p>
                        @endforelse


                        <!-- Leave a Review -->
                        <div class="border-t pt-6">
                            <h3 class="text-base md:text-lg font-semibold mb-3">Leave a Review</h3>
                            <x-form wire:submit="submitReview">
                                <!-- Star Rating -->
                                <div class="flex items-center gap-1">
                                    <label class="text-sm font-semibold mr-1">Rating:</label>
                                    <x-rating wire:model="rating" class="bg-warning" />

                                </div>

                                <!-- Comment Box -->
                                <x-textarea label="Add a Comment" wire:model="comment"
                                    placeholder="Write your experience..." class="custome-input-field"
                                    row="10" />

                                <!-- Submit Button -->
                                <div>
                                    <x-button type="submit"
                                        class="bg-gradient-to-r from-green-500 to-green-400 hover:from-green-600 hover:to-green-500 font-semibold text-white px-6 py-2 rounded-md text-sm transition duration-200"
                                        label="Submit Review" spinner="submitReview" />
                                </div>
                            </x-form>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </section>

    <!-- Hotel Details Section End -->
</div>
