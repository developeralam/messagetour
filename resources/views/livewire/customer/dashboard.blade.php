<?php

use App\Models\Order;
use App\Models\CarBooking;
use App\Models\TourBooking;
use App\Models\VisaBooking;
use Livewire\Volt\Component;
use App\Models\CorporateQuery;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;
use App\Models\HotelRoomBooking;
use App\Models\GroupFlightBooking;

new #[Layout('components.layouts.customer')] #[Title('Dashboard')] class extends Component {
    /**
     * Dashboard statistics (counts) for the logged-in user.
     * Each property holds the total count of respective bookings/queries.
     */
    public int $ordersCount = 0; // Total Orders placed by the user
    public int $groupFlightCount = 0; // Total Group Flight Bookings made by the user
    public int $hotelBookingCount = 0; // Total Hotel Room Bookings linked to user’s orders
    public int $visaBookingCount = 0; // Total Visa Bookings linked to user’s orders
    public int $tourBookingCount = 0; // Total Tour Package Bookings linked to user’s orders
    public int $carBookingCount = 0; // Total Car Bookings linked to user’s orders
    public int $corporateQueryCount = 0; // Total Corporate Queries submitted by the user

    /**
     * Mount lifecycle hook.
     *
     * Runs once when the component is initialized.
     * Loads and assigns all booking/query counts for the authenticated user.
     */
    public function mount(): void
    {
        // Get the currently authenticated user’s ID
        $userId = auth()->id();

        /**
         * Orders Count
         *
         * Fetches total orders created by the logged-in user.
         */
        $this->ordersCount = Order::query()->where('user_id', $userId)->count();

        /**
         * Group Flight Bookings Count
         *
         * Directly linked to `user_id` column in the `group_flight_bookings` table.
         */
        $this->groupFlightCount = GroupFlightBooking::query()->where('user_id', $userId)->count();

        /**
         * Hotel Room Bookings Count
         *
         * Related via `order`. Only counts hotel bookings that belong to user’s orders.
         */
        $this->hotelBookingCount = HotelRoomBooking::query()->whereHas('order', fn($q) => $q->where('user_id', $userId))->count();

        /**
         * Visa Bookings Count
         *
         * Related via `order`. Counts all visa bookings belonging to user’s orders.
         */
        $this->visaBookingCount = VisaBooking::query()->whereHas('order', fn($q) => $q->where('user_id', $userId))->count();

        /**
         * Tour Package Bookings Count
         *
         * Related via `order`. Counts all tour bookings belonging to user’s orders.
         */
        $this->tourBookingCount = TourBooking::query()->whereHas('order', fn($q) => $q->where('user_id', $userId))->count();

        /**
         * Car Bookings Count
         *
         * Related via `order`. Counts all car bookings belonging to user’s orders.
         */
        $this->carBookingCount = CarBooking::query()->whereHas('order', fn($q) => $q->where('user_id', $userId))->count();

        /**
         * Corporate Queries Count
         *
         * Directly linked to `user_id` in the `corporate_queries` table.
         */
        $this->corporateQueryCount = CorporateQuery::query()->where('user_id', $userId)->count();
    }
}; ?>

<div>
    <div class="min-h-screen w-full p-6 md:p-10">
        <!-- Grid -->
        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-4 mt-6">
            <!-- Card: Orders -->
            <a
                class="group relative overflow-hidden rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm ring-1 ring-black/5 transition hover:shadow-md">
                <div class="absolute -right-10 -top-10 h-28 w-28 rounded-full bg-primary/10 blur-2xl"></div>
                <div class="flex items-center gap-4">
                    <div class="shrink-0 rounded-xl bg-primary/10 px-3 py-2 ring-1 ring-primary/20">
                        <!-- icon -->
                        <x-icon name="fab.first-order-alt" class="w-5 h-5" />

                    </div>
                    <div class="min-w-0">
                        <p class="text-sm text-zinc-500">Total Orders</p>
                        <p class="text-3xl font-extrabold tracking-tight text-zinc-900">{{ $ordersCount ?? 52 }}</p>
                    </div>
                </div>
            </a>

            <!-- Card: Group Flight Booking -->
            <a
                class="group relative overflow-hidden rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm ring-1 ring-black/5 transition hover:shadow-md">
                <div class="absolute -right-10 -top-10 h-28 w-28 rounded-full bg-primary/10 blur-2xl"></div>
                <div class="flex items-center gap-4">
                    <div class="shrink-0 rounded-xl bg-primary/10 px-3 py-2 ring-1 ring-primary/20">
                        <x-icon name="fas.plane-departure" class="w-5 h-5" />
                    </div>
                    <div>
                        <p class="text-sm text-zinc-500">Group Flight Bookings</p>
                        <p class="text-3xl font-extrabold tracking-tight text-zinc-900">{{ $groupFlightCount ?? 18 }}
                        </p>
                    </div>
                </div>
            </a>

            <!-- Card: Hotel Booking -->
            <a
                class="group relative overflow-hidden rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm ring-1 ring-black/5 transition hover:shadow-md">
                <div class="absolute -right-10 -top-10 h-28 w-28 rounded-full bg-primary/10 blur-2xl"></div>
                <div class="flex items-center gap-4">
                    <div class="shrink-0 rounded-xl bg-primary/10 px-3 py-2 ring-1 ring-primary/20">
                        <x-icon name="fas.hotel" class="w-5 h-5" />

                    </div>
                    <div>
                        <p class="text-sm text-zinc-500">Hotel Bookings</p>
                        <p class="text-3xl font-extrabold tracking-tight text-zinc-900">{{ $hotelBookingCount ?? 24 }}
                        </p>
                    </div>
                </div>
            </a>

            <!-- Card: Visa Booking -->
            <a
                class="group relative overflow-hidden rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm ring-1 ring-black/5 transition hover:shadow-md">
                <div class="absolute -right-10 -top-10 h-28 w-28 rounded-full bg-primary/10 blur-2xl"></div>
                <div class="flex items-center gap-4">
                    <div class="shrink-0 rounded-xl bg-primary/10 px-3 py-2 ring-1 ring-primary/20">
                        <x-icon name="fab.cc-visa" class="w-5 h-5" />

                    </div>
                    <div>
                        <p class="text-sm text-zinc-500">Visa Bookings</p>
                        <p class="text-3xl font-extrabold tracking-tight text-zinc-900">{{ $visaBookingCount ?? 11 }}
                        </p>
                    </div>
                </div>
            </a>

            <!-- Card: Tour Package Booking -->
            <a
                class="group relative overflow-hidden rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm ring-1 ring-black/5 transition hover:shadow-md">
                <div class="absolute -right-10 -top-10 h-28 w-28 rounded-full bg-primary/10 blur-2xl"></div>
                <div class="flex items-center gap-4">
                    <div class="shrink-0 rounded-xl bg-primary/10 px-3 py-2 ring-1 ring-primary/20">
                        <x-icon name="fas.globe" class="w-5 h-5" />

                    </div>
                    <div>
                        <p class="text-sm text-zinc-500">Tour Package Bookings</p>
                        <p class="text-3xl font-extrabold tracking-tight text-zinc-900">{{ $tourBookingCount ?? 9 }}
                        </p>
                    </div>
                </div>
            </a>

            <!-- Card: Car Booking -->
            <a
                class="group relative overflow-hidden rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm ring-1 ring-black/5 transition hover:shadow-md">
                <div class="absolute -right-10 -top-10 h-28 w-28 rounded-full bg-primary/10 blur-2xl"></div>
                <div class="flex items-center gap-4">
                    <div class="shrink-0 rounded-xl bg-primary/10 px-3 py-2 ring-1 ring-primary/20">
                        <x-icon name="fas.car" class="w-5 h-5" />

                    </div>
                    <div>
                        <p class="text-sm text-zinc-500">Car Bookings</p>
                        <p class="text-3xl font-extrabold tracking-tight text-zinc-900">{{ $carBookingCount ?? 7 }}</p>
                    </div>
                </div>
            </a>

            <!-- Card: Corporate Query -->
            <a
                class="group relative overflow-hidden rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm ring-1 ring-black/5 transition hover:shadow-md">
                <div class="absolute -right-10 -top-10 h-28 w-28 rounded-full bg-primary/10 blur-2xl"></div>
                <div class="flex items-center gap-4">
                    <div class="shrink-0 rounded-xl bg-primary/10 px-3 py-2 ring-1 ring-primary/20">
                        <x-icon name="fas.list" class="w-5 h-5" />

                    </div>
                    <div>
                        <p class="text-sm text-zinc-500">Corporate Queries</p>
                        <p class="text-3xl font-extrabold tracking-tight text-zinc-900">
                            {{ $corporateQueryCount ?? 14 }}
                        </p>
                    </div>
                </div>
            </a>

            <!-- Card: Insurance Booking -->
        </div>
    </div>

</div>
