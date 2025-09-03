<?php

use Carbon\Carbon;
use App\Models\Tour;
use App\Enum\TourType;
use Mary\Traits\Toast;
use Livewire\Volt\Component;
use Livewire\Attributes\Rule;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\Auth;

new #[Layout('components.layouts.service-details')] #[Title('Tour Details')] class extends Component {
    use Toast;
    public $tour = [];
    public $reviews = [];
    public $tour_types = [];
    public $tourList = [];
    public $tour_keyword;
    public $id;
    public $type;
    public $hotel_checkin;
    public $hotel_checkout;
    public $tour_type;
    public $averageRating;

    #[Rule('required')]
    public $rating;

    #[Rule('required')]
    public $comment;

    public function mount($slug)
    {
        $this->tour = Tour::with(['country', 'division', 'district'])
            ->where('slug', $slug)
            ->first();
        $this->tour_keyword = request()->query('keyword');
        $parts = explode('-', $this->tour_keyword);
        $this->id = $parts[0];
        $this->type = $parts[1];
        $this->tour_type = request()->query('tour_type');

        $this->tour_types = TourType::getTourTypes();
        $this->averageRating = round($this->tour->reviews()->avg('rating'), 1);

        $this->reviews = $this->tour->reviews()->with('user')->latest()->get();
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
            $this->tour->reviews()->create([
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
            return redirect()->route('frontend.tour.booking', [
                'slug' => $slug,
                'tour_keyword' => $this->tour_keyword,
            ]);
        } else {
            // User is not authenticated, dispatch event to show login modal
            $this->dispatch(
                'showLoginModal',
                route('frontend.tour.booking', [
                    'slug' => $slug,
                    'tour_keyword' => $this->tour_keyword,
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

    <section class="mt-10 px-3" x-cloak>
        <div class="max-w-6xl mx-auto bg-white p-4 rounded-lg shadow-md">
            <div class="md:grid md:grid-cols-12 gap-4">
                {{-- Image Gallery --}}
                <div class="md:col-span-4">
                    <div x-data="{ img: '{{ $tour->images->first()['url'] ?? $tour->thumbnail_link }}' }">
                        <img :src="img" class="w-full h-[280px] object-cover rounded-md shadow-sm mb-2" />
                        <div class="flex gap-2 overflow-x-auto">
                            @foreach ($tour->images as $image)
                                <img src="{{ $image['url'] }}" @click="img = '{{ $image['url'] }}'"
                                    class="w-16 h-16 object-cover rounded-md cursor-pointer border hover:border-green-500" />
                            @endforeach
                        </div>
                    </div>
                </div>

                {{-- Tour Info --}}
                <div class="md:col-span-8">
                    <h1 class="text-base md:text-xl mt-2 md:mt-0 leading-none md:leading-normal font-semibold">
                        {{ $tour->title }}</h1>

                    {{-- Upper Info --}}
                    <div>
                        @if ($averageRating)
                            <div class="border border-green-500 rounded-md px-2 py-1 w-fit my-2 bg-gray-100">
                                <div class="flex items-center gap-1">
                                    <x-icon name="fas.star" class="text-[#f9a825]" />
                                    <p class="text-sm text-gray-800">{{ $averageRating }} Star</p>
                                </div>
                            </div>
                        @endif
                        <div class="flex flex-row sm:flex-col gap-4 md:gap-0 text-sm text-gray-600 space-y-1.5 mt-2">
                            <div class="flex items-center gap-1">
                                <x-icon name="fas.location-dot" class="text-green-600 w-3 h-3" />
                                <span>{{ $tour->location }}</span>
                            </div>
                            @if ($tour->createdBy && ($tour->createdBy->email || $globalSettings->contact_email))
                                <div class="flex items-center gap-1">
                                    <x-icon name="fas.envelope" class="text-green-500 w-3 h-3" />
                                    <a href="mailto:{{ $tour->createdBy->email ?: $globalSettings->contact_email }}">
                                        {{ $tour->createdBy->email ?: $globalSettings->contact_email }}
                                    </a>

                                </div>
                            @endif

                        </div>
                        @if ($tour->createdBy && $tour->createdBy->agent?->phone)
                            <div class="text-sm flex items-center text-gray-600 gap-2">
                                <x-icon name="fas.phone" class="text-green-500 w-4 h-4" />
                                <a
                                    href="tel:{{ $tour->createdBy->agent->phone }}">{{ $tour->createdBy->agent->phone }}</a>
                            </div>
                        @endif
                    </div>

                    {{-- Bottom: Info Table + Price card --}}
                    <div class="flex flex-col md:flex-row md:items-start gap-4 mt-3">
                        {{-- Info Table --}}
                        <div class="w-full flex-1 md:max-w-[61%] border border-green-500 rounded-sm">
                            <table class="w-full text-sm border">
                                <tbody>
                                    <tr>
                                        <th class="p-2 border font-semibold w-32">Tour Type</th>
                                        <td class="p-2 border bg-gray-100">{{ $tour->type->name }}</td>
                                    </tr>
                                    <tr>
                                        <th class="p-2 border font-semibold">Start Date</th>
                                        <td class="p-2 border bg-gray-100">
                                            {{ \Carbon\Carbon::parse($tour->start_date)->format('j F, Y') }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <th class="p-2 border font-semibold">End Date</th>
                                        <td class="p-2 border bg-gray-100">
                                            {{ \Carbon\Carbon::parse($tour->end_date)->format('j F, Y') }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <th class="p-2 border font-semibold">Country</th>
                                        <td class="p-2 border bg-gray-100">{{ $tour->country->name }}</td>
                                    </tr>
                                    <tr>
                                        <th class="p-2 border font-semibold">Division</th>
                                        <td class="p-2 border bg-gray-100">{{ $tour->division->name }}</td>
                                    </tr>
                                    <tr>
                                        <th class="p-2 border font-semibold">District</th>
                                        <td class="p-2 border bg-gray-100">{{ $tour->district->name }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        {{-- Price Card --}}
                        <div class="w-full md:w-[39%]">
                            <div class="bg-gray-100 border rounded-sm p-4 text-center shadow-md">
                                <small class="text-xs text-gray-600 font-semibold">Price starts from (per
                                    person)</small>

                                @if ($tour->offer_price && $tour->offer_price < $tour->regular_price)
                                    @php
                                        $discountPercentage = round(
                                            (($tour->regular_price - $tour->offer_price) / $tour->regular_price) * 100,
                                        );
                                    @endphp
                                    <p class="text-red-500 text-sm mt-2 line-through">BDT {{ $tour->regular_price }}
                                    </p>
                                    <div class="flex items-center justify-center gap-1 mb-4">
                                        <img src="{{ asset('images/discount-mono.svg') }}" class="w-4 h-4" />
                                        <span class="text-[#f73] text-xs font-bold">{{ $discountPercentage }}
                                            OFF</span>
                                        <span class="font-bold">BDT {{ $tour->offer_price }}</span>
                                    </div>
                                @else
                                    <p class="text-sm md:text-base font-bold mt-2 mb-4">BDT {{ $tour->regular_price }}
                                    </p>
                                @endif

                                <a wire:click="handleReserveClick('{{ $tour->slug }}')"
                                    class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600 text-sm font-bold shadow-md hover:cursor-pointer">
                                    RESERVE
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Reserve Section End -->

    <!-- Tour Details and Review Section Start -->
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

                <p class="text-sm leading-relaxed">{!! $tour->description !!}</p>
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
                                                {{ $review->created_at->diffForHumans() }}</p>
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
                            <p class="text-gray-500 text-sm">No reviews available for this tour yet.</p>
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
    <!-- Tour Details and Review Section End -->
</div>
