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

new #[Layout('components.layouts.app')] #[Title('Customer Dashboard')] class extends Component {
    /**
     * Dashboard statistics (counts) for the logged-in user.
     */
    public int $ordersCount = 0;
    public int $groupFlightCount = 0;
    public int $hotelBookingCount = 0;
    public int $visaBookingCount = 0;
    public int $tourBookingCount = 0;
    public int $carBookingCount = 0;
    public int $corporateQueryCount = 0;

    public function mount(): void
    {
        $userId = auth()->id();

        $this->ordersCount = Order::query()->where('user_id', $userId)->count();
        $this->groupFlightCount = GroupFlightBooking::query()->where('user_id', $userId)->count();
        $this->hotelBookingCount = HotelRoomBooking::query()->whereHas('order', fn($q) => $q->where('user_id', $userId))->count();
        $this->visaBookingCount = VisaBooking::query()->whereHas('order', fn($q) => $q->where('user_id', $userId))->count();
        $this->tourBookingCount = TourBooking::query()->whereHas('order', fn($q) => $q->where('user_id', $userId))->count();
        $this->carBookingCount = CarBooking::query()->whereHas('order', fn($q) => $q->where('user_id', $userId))->count();
        $this->corporateQueryCount = CorporateQuery::query()->where('user_id', $userId)->count();
    }
}; ?>

<div class="min-h-screen bg-gradient-to-br from-emerald-50 via-green-50 to-teal-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">

            <!-- Enhanced Sidebar -->
            <aside class="lg:col-span-1">
                <!-- Profile Card -->
                <div class="bg-white/80 backdrop-blur-xl rounded-3xl p-6 shadow-2xl border border-green-200/50 mb-6 relative overflow-hidden">
                    <div class="absolute inset-0 bg-gradient-to-br from-green-500/10 to-emerald-600/10"></div>
                    <div class="relative z-10 text-center">
                        <div class="relative inline-block">
                            <img src="{{ auth()->user()->customer->image_link ?? '/empty-user.jpg' }}"
                                class="w-24 h-24 rounded-full border-4 border-white shadow-xl object-cover" alt="Profile">
                            <div
                                class="absolute -bottom-1 -right-1 w-7 h-7 bg-gradient-to-r from-green-500 to-emerald-600 rounded-full border-3 border-white flex items-center justify-center">
                                <div class="w-2 h-2 bg-white rounded-full"></div>
                            </div>
                        </div>
                        <h3 class="mt-4 text-xl font-bold text-gray-800">{{ auth()->user()->name }}</h3>
                        <p class="text-sm text-gray-600">{{ auth()->user()->email }}</p>
                        <div class="mt-3 text-xs text-gray-500 bg-white/50 rounded-full px-3 py-1 inline-block">
                            Member since {{ auth()->user()->created_at->format('M Y') }}
                        </div>
                    </div>
                </div>

                <!-- Enhanced Navigation -->
                <nav class="space-y-3">
                    @php
                        $currentRoute = request()->route()->getName();
                        $menu = [
                            [
                                'label' => 'Dashboard',
                                'icon' => 'M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2z',
                                'route' => '.customerdashboard',
                                'active' => $currentRoute === '.customerdashboard',
                                'color' => 'green',
                            ],
                            [
                                'label' => 'My Orders',
                                'icon' =>
                                    'M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2',
                                'route' => '.customerorder',
                                'active' => $currentRoute === '.customerorder',
                                'color' => 'blue',
                            ],
                            [
                                'label' => 'Group Flight Booking',
                                'icon' => 'M12 19l9 2-9-18-9 18 9-2zm0 0v-8',
                                'route' => '.customergroup.flight',
                                'active' => $currentRoute === '.customergroup.flight',
                                'color' => 'purple',
                            ],
                            [
                                'label' => 'Hotel Booking',
                                'icon' => 'M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2z',
                                'route' => '.customerhotel.booking',
                                'active' => $currentRoute === '.customerhotel.booking',
                                'color' => 'orange',
                            ],
                            [
                                'label' => 'Visa Booking',
                                'icon' =>
                                    'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z',
                                'route' => '.customervisa.booking',
                                'active' => $currentRoute === '.customervisa.booking',
                                'color' => 'indigo',
                            ],
                            [
                                'label' => 'Tour Package Booking',
                                'icon' => 'M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z',
                                'route' => '.customertour.booking',
                                'active' => $currentRoute === '.customertour.booking',
                                'color' => 'teal',
                            ],
                            [
                                'label' => 'Car Booking',
                                'icon' => 'M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4',
                                'route' => '.customercar.booking',
                                'active' => $currentRoute === '.customercar.booking',
                                'color' => 'red',
                            ],
                            [
                                'label' => 'Corporate Query',
                                'icon' =>
                                    'M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2',
                                'route' => '.customercorporate.query.list',
                                'active' => $currentRoute === '.customercorporate.query.list',
                                'color' => 'pink',
                            ],
                            [
                                'label' => 'My Profile',
                                'icon' => 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z',
                                'route' => '.customerprofile',
                                'active' => $currentRoute === '.customerprofile',
                                'color' => 'gray',
                            ],
                        ];
                    @endphp

                    @foreach ($menu as $item)
                        <a href="{{ route($item['route']) }}"
                            class="group flex items-center space-x-4 px-4 py-3 rounded-2xl transition-all duration-300 {{ $item['active'] ? 'bg-gradient-to-r from-green-500 to-emerald-600 text-white shadow-xl transform scale-105' : 'text-gray-600 hover:bg-white/80 hover:text-green-700 hover:shadow-lg hover:transform hover:scale-105' }}">
                            <div
                                class="flex-shrink-0 w-10 h-10 rounded-xl flex items-center justify-center transition-all duration-300 {{ $item['active'] ? 'bg-white/20' : 'bg-' . $item['color'] . '-100 group-hover:bg-' . $item['color'] . '-200' }}">
                                <svg class="w-5 h-5 {{ $item['active'] ? 'text-white' : 'text-' . $item['color'] . '-600' }}" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $item['icon'] }}"></path>
                                </svg>
                            </div>
                            <span class="font-semibold text-sm">{{ $item['label'] }}</span>
                        </a>
                    @endforeach

                    <form method="POST" action="{{ route('logout') }}" class="mt-6">
                        @csrf
                        <button type="submit"
                            class="group flex items-center space-x-4 px-4 py-3 rounded-2xl text-red-600 hover:bg-red-50 hover:shadow-lg transition-all duration-300 w-full hover:transform hover:scale-105">
                            <div
                                class="flex-shrink-0 w-10 h-10 rounded-xl bg-red-100 group-hover:bg-red-200 flex items-center justify-center transition-all duration-300">
                                <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                                </svg>
                            </div>
                            <span class="font-semibold text-sm">Logout</span>
                        </button>
                    </form>
                </nav>
            </aside>

            <!-- Main Content -->
            <main class="lg:col-span-3">
                <!-- Welcome Section -->
                <div class="mb-8">
                    <div class="bg-white/80 backdrop-blur-xl rounded-3xl p-8 shadow-2xl border border-green-200/50 relative overflow-hidden">
                        <div class="absolute inset-0 bg-gradient-to-br from-green-500/5 to-emerald-600/5"></div>
                        <div class="relative z-10">
                            <h1 class="text-4xl font-bold text-gray-800 mb-3">Welcome back, {{ auth()->user()->name }}! 👋</h1>
                            <p class="text-lg text-gray-600 mb-4">Here's what's happening with your travel bookings today.</p>
                            <div class="flex items-center space-x-4 text-sm text-gray-500">
                                <div class="flex items-center space-x-2">
                                    <div class="w-2 h-2 bg-green-500 rounded-full"></div>
                                    <span>Last login: {{ auth()->user()->updated_at->format('M d, Y H:i') }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Enhanced Statistics Cards -->
                <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6 mb-8">
                    <!-- Total Orders Card -->
                    <a href="{{ route('.customerorder') }}"
                        class="group bg-white/80 backdrop-blur-xl rounded-3xl p-6 shadow-xl border border-green-200/50 hover:shadow-2xl transition-all duration-300 hover:transform hover:scale-105 relative overflow-hidden">
                        <div
                            class="absolute inset-0 bg-gradient-to-br from-blue-500/10 to-blue-600/10 opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                        </div>
                        <div class="relative z-10">
                            <div class="flex items-center justify-between mb-4">
                                <div
                                    class="w-14 h-14 bg-gradient-to-br from-blue-500 to-blue-600 rounded-2xl flex items-center justify-center shadow-lg">
                                    <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2">
                                        </path>
                                    </svg>
                                </div>
                                <div class="text-right">
                                    <p class="text-sm font-medium text-gray-600">Total Orders</p>
                                    <p class="text-3xl font-bold text-gray-900">{{ $ordersCount }}</p>
                                </div>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-blue-600 font-medium">View Orders</span>
                                <svg class="w-4 h-4 text-blue-600 group-hover:translate-x-1 transition-transform duration-300" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                </svg>
                            </div>
                        </div>
                    </a>

                    <!-- Group Flight Bookings Card -->
                    <a href="{{ route('.customergroup.flight') }}"
                        class="group bg-white/80 backdrop-blur-xl rounded-3xl p-6 shadow-xl border border-green-200/50 hover:shadow-2xl transition-all duration-300 hover:transform hover:scale-105 relative overflow-hidden">
                        <div
                            class="absolute inset-0 bg-gradient-to-br from-purple-500/10 to-purple-600/10 opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                        </div>
                        <div class="relative z-10">
                            <div class="flex items-center justify-between mb-4">
                                <div
                                    class="w-14 h-14 bg-gradient-to-br from-purple-500 to-purple-600 rounded-2xl flex items-center justify-center shadow-lg">
                                    <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8">
                                        </path>
                                    </svg>
                                </div>
                                <div class="text-right">
                                    <p class="text-sm font-medium text-gray-600">Group Flights</p>
                                    <p class="text-3xl font-bold text-gray-900">{{ $groupFlightCount }}</p>
                                </div>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-purple-600 font-medium">View Flights</span>
                                <svg class="w-4 h-4 text-purple-600 group-hover:translate-x-1 transition-transform duration-300" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                </svg>
                            </div>
                        </div>
                    </a>

                    <!-- Hotel Bookings Card -->
                    <a href="{{ route('.customerhotel.booking') }}"
                        class="group bg-white/80 backdrop-blur-xl rounded-3xl p-6 shadow-xl border border-green-200/50 hover:shadow-2xl transition-all duration-300 hover:transform hover:scale-105 relative overflow-hidden">
                        <div
                            class="absolute inset-0 bg-gradient-to-br from-orange-500/10 to-orange-600/10 opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                        </div>
                        <div class="relative z-10">
                            <div class="flex items-center justify-between mb-4">
                                <div
                                    class="w-14 h-14 bg-gradient-to-br from-orange-500 to-orange-600 rounded-2xl flex items-center justify-center shadow-lg">
                                    <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2z"></path>
                                    </svg>
                                </div>
                                <div class="text-right">
                                    <p class="text-sm font-medium text-gray-600">Hotel Bookings</p>
                                    <p class="text-3xl font-bold text-gray-900">{{ $hotelBookingCount }}</p>
                                </div>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-orange-600 font-medium">View Hotels</span>
                                <svg class="w-4 h-4 text-orange-600 group-hover:translate-x-1 transition-transform duration-300" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                </svg>
                            </div>
                        </div>
                    </a>

                    <!-- Visa Bookings Card -->
                    <a href="{{ route('.customervisa.booking') }}"
                        class="group bg-white/80 backdrop-blur-xl rounded-3xl p-6 shadow-xl border border-green-200/50 hover:shadow-2xl transition-all duration-300 hover:transform hover:scale-105 relative overflow-hidden">
                        <div
                            class="absolute inset-0 bg-gradient-to-br from-indigo-500/10 to-indigo-600/10 opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                        </div>
                        <div class="relative z-10">
                            <div class="flex items-center justify-between mb-4">
                                <div
                                    class="w-14 h-14 bg-gradient-to-br from-indigo-500 to-indigo-600 rounded-2xl flex items-center justify-center shadow-lg">
                                    <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                                        </path>
                                    </svg>
                                </div>
                                <div class="text-right">
                                    <p class="text-sm font-medium text-gray-600">Visa Bookings</p>
                                    <p class="text-3xl font-bold text-gray-900">{{ $visaBookingCount }}</p>
                                </div>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-indigo-600 font-medium">View Visas</span>
                                <svg class="w-4 h-4 text-indigo-600 group-hover:translate-x-1 transition-transform duration-300" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                </svg>
                            </div>
                        </div>
                    </a>

                    <!-- Tour Bookings Card -->
                    <a href="{{ route('.customertour.booking') }}"
                        class="group bg-white/80 backdrop-blur-xl rounded-3xl p-6 shadow-xl border border-green-200/50 hover:shadow-2xl transition-all duration-300 hover:transform hover:scale-105 relative overflow-hidden">
                        <div
                            class="absolute inset-0 bg-gradient-to-br from-teal-500/10 to-teal-600/10 opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                        </div>
                        <div class="relative z-10">
                            <div class="flex items-center justify-between mb-4">
                                <div
                                    class="w-14 h-14 bg-gradient-to-br from-teal-500 to-teal-600 rounded-2xl flex items-center justify-center shadow-lg">
                                    <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                    </svg>
                                </div>
                                <div class="text-right">
                                    <p class="text-sm font-medium text-gray-600">Tour Bookings</p>
                                    <p class="text-3xl font-bold text-gray-900">{{ $tourBookingCount }}</p>
                                </div>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-teal-600 font-medium">View Tours</span>
                                <svg class="w-4 h-4 text-teal-600 group-hover:translate-x-1 transition-transform duration-300" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                </svg>
                            </div>
                        </div>
                    </a>

                    <!-- Car Bookings Card -->
                    <a href="{{ route('.customercar.booking') }}"
                        class="group bg-white/80 backdrop-blur-xl rounded-3xl p-6 shadow-xl border border-green-200/50 hover:shadow-2xl transition-all duration-300 hover:transform hover:scale-105 relative overflow-hidden">
                        <div
                            class="absolute inset-0 bg-gradient-to-br from-red-500/10 to-red-600/10 opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                        </div>
                        <div class="relative z-10">
                            <div class="flex items-center justify-between mb-4">
                                <div
                                    class="w-14 h-14 bg-gradient-to-br from-red-500 to-red-600 rounded-2xl flex items-center justify-center shadow-lg">
                                    <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path>
                                    </svg>
                                </div>
                                <div class="text-right">
                                    <p class="text-sm font-medium text-gray-600">Car Bookings</p>
                                    <p class="text-3xl font-bold text-gray-900">{{ $carBookingCount }}</p>
                                </div>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-red-600 font-medium">View Cars</span>
                                <svg class="w-4 h-4 text-red-600 group-hover:translate-x-1 transition-transform duration-300" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                </svg>
                            </div>
                        </div>
                    </a>

                    <!-- Corporate Queries Card -->
                    <a href="{{ route('.customercorporate.query.list') }}"
                        class="group bg-white/80 backdrop-blur-xl rounded-3xl p-6 shadow-xl border border-green-200/50 hover:shadow-2xl transition-all duration-300 hover:transform hover:scale-105 relative overflow-hidden">
                        <div
                            class="absolute inset-0 bg-gradient-to-br from-pink-500/10 to-pink-600/10 opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                        </div>
                        <div class="relative z-10">
                            <div class="flex items-center justify-between mb-4">
                                <div
                                    class="w-14 h-14 bg-gradient-to-br from-pink-500 to-pink-600 rounded-2xl flex items-center justify-center shadow-lg">
                                    <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z">
                                        </path>
                                    </svg>
                                </div>
                                <div class="text-right">
                                    <p class="text-sm font-medium text-gray-600">Corporate Queries</p>
                                    <p class="text-3xl font-bold text-gray-900">{{ $corporateQueryCount }}</p>
                                </div>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-pink-600 font-medium">View Queries</span>
                                <svg class="w-4 h-4 text-pink-600 group-hover:translate-x-1 transition-transform duration-300" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                </svg>
                            </div>
                        </div>
                    </a>

                    <!-- Quick Actions Card -->
                    <div
                        class="group bg-white/80 backdrop-blur-xl rounded-3xl p-6 shadow-xl border border-green-200/50 hover:shadow-2xl transition-all duration-300 hover:transform hover:scale-105 relative overflow-hidden">
                        <div
                            class="absolute inset-0 bg-gradient-to-br from-green-500/10 to-emerald-600/10 opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                        </div>
                        <div class="relative z-10">
                            <div class="flex items-center justify-between mb-4">
                                <div
                                    class="w-14 h-14 bg-gradient-to-br from-green-500 to-emerald-600 rounded-2xl flex items-center justify-center shadow-lg">
                                    <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z">
                                        </path>
                                    </svg>
                                </div>
                                <div class="text-right">
                                    <p class="text-sm font-medium text-gray-600">Quick Actions</p>
                                    <p class="text-lg font-bold text-gray-900">New Booking</p>
                                </div>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-green-600 font-medium">Start Booking</span>
                                <svg class="w-4 h-4 text-green-600 group-hover:translate-x-1 transition-transform duration-300" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Bookings Section -->
                <div class="bg-white/80 backdrop-blur-xl rounded-3xl shadow-2xl border border-green-200/50 overflow-hidden mb-8">
                    <div class="p-6 border-b border-green-100/50 bg-gradient-to-r from-green-50/50 to-emerald-50/50">
                        <div class="flex items-center justify-between">
                            <h3 class="text-xl font-bold text-gray-800">Recent Bookings</h3>
                            <a href="{{ route('.customerorder') }}"
                                class="text-sm text-green-600 hover:text-green-700 font-medium flex items-center space-x-1">
                                <span>View All</span>
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                </svg>
                            </a>
                        </div>
                    </div>

                    @php
                        // Get recent orders with their bookings
                        $recentOrders = auth()
                            ->user()
                            ->orders()
                            ->with([
                                'hotelRoomBookings.hotelbookingitems.hotelRoom.hotel',
                                'tourBookings.tour',
                                'carBookings.car',
                                'visaBookings.visa',
                                'travelProductBookings.travelproduct',
                            ])
                            ->latest()
                            ->take(5)
                            ->get();
                    @endphp

                    @if ($recentOrders->count() > 0)
                        <div class="divide-y divide-green-100/50">
                            @foreach ($recentOrders as $order)
                                @php
                                    $bookingType = '';
                                    $bookingName = '';
                                    $bookingDescription = '';
                                    $iconColor = '';
                                    $badgeColor = '';

                                    // Check for different booking types
                                    if ($order->hotelRoomBookings->count() > 0) {
                                        $bookingType = 'Hotel';
                                        $hotelBooking = $order->hotelRoomBookings->first();
                                        $hotelRoom = $hotelBooking->hotelbookingitems->first()->hotelRoom ?? null;
                                        $bookingName = $hotelRoom->hotel->name ?? 'Hotel Booking';
                                        $bookingDescription = $hotelRoom->room_type ?? 'Room';
                                        $iconColor = 'text-orange-600';
                                        $badgeColor = 'bg-orange-100 text-orange-800';
                                    } elseif ($order->tourBookings->count() > 0) {
                                        $bookingType = 'Tour';
                                        $tourBooking = $order->tourBookings->first();
                                        $bookingName = $tourBooking->tour->name ?? 'Tour Package';
                                        $bookingDescription = $tourBooking->tour->duration ?? 'Package';
                                        $iconColor = 'text-teal-600';
                                        $badgeColor = 'bg-teal-100 text-teal-800';
                                    } elseif ($order->carBookings->count() > 0) {
                                        $bookingType = 'Car';
                                        $carBooking = $order->carBookings->first();
                                        $bookingName = $carBooking->car->name ?? 'Car Rental';
                                        $bookingDescription = $carBooking->car->type ?? 'Vehicle';
                                        $iconColor = 'text-red-600';
                                        $badgeColor = 'bg-red-100 text-red-800';
                                    } elseif ($order->visaBookings->count() > 0) {
                                        $bookingType = 'Visa';
                                        $visaBooking = $order->visaBookings->first();
                                        $bookingName = $visaBooking->visa->name ?? 'Visa Service';
                                        $bookingDescription = 'Visa Application';
                                        $iconColor = 'text-indigo-600';
                                        $badgeColor = 'bg-indigo-100 text-indigo-800';
                                    } elseif ($order->travelProductBookings->count() > 0) {
                                        $bookingType = 'Product';
                                        $productBooking = $order->travelProductBookings->first();
                                        $bookingName = $productBooking->travelproduct->name ?? 'Travel Product';
                                        $bookingDescription = 'Travel Product';
                                        $iconColor = 'text-purple-600';
                                        $badgeColor = 'bg-purple-100 text-purple-800';
                                    } else {
                                        $bookingType = 'Order';
                                        $bookingName = 'General Order';
                                        $bookingDescription = 'Order #' . $order->id;
                                        $iconColor = 'text-gray-600';
                                        $badgeColor = 'bg-gray-100 text-gray-800';
                                    }

                                    $statusColor = match ($order->status->value ?? 'pending') {
                                        'confirmed', 'paid', 'completed' => 'bg-green-100 text-green-800',
                                        'pending' => 'bg-yellow-100 text-yellow-800',
                                        'cancelled' => 'bg-red-100 text-red-800',
                                        default => 'bg-gray-100 text-gray-800',
                                    };
                                @endphp
                                <div class="p-6 hover:bg-green-50/30 transition-colors">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center space-x-4">
                                            <div
                                                class="w-12 h-12 bg-gradient-to-br from-green-100 to-emerald-100 rounded-2xl flex items-center justify-center">
                                                @if ($bookingType == 'Hotel')
                                                    <svg class="w-6 h-6 {{ $iconColor }}" fill="none" stroke="currentColor"
                                                        viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                            d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2z"></path>
                                                    </svg>
                                                @elseif($bookingType == 'Tour')
                                                    <svg class="w-6 h-6 {{ $iconColor }}" fill="none" stroke="currentColor"
                                                        viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                            d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z">
                                                        </path>
                                                    </svg>
                                                @elseif($bookingType == 'Car')
                                                    <svg class="w-6 h-6 {{ $iconColor }}" fill="none" stroke="currentColor"
                                                        viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                            d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path>
                                                    </svg>
                                                @elseif($bookingType == 'Visa')
                                                    <svg class="w-6 h-6 {{ $iconColor }}" fill="none" stroke="currentColor"
                                                        viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                                                        </path>
                                                    </svg>
                                                @else
                                                    <svg class="w-6 h-6 {{ $iconColor }}" fill="none" stroke="currentColor"
                                                        viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                            d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                                                    </svg>
                                                @endif
                                            </div>
                                            <div>
                                                <h4 class="text-lg font-semibold text-gray-900">{{ $bookingName }}</h4>
                                                <p class="text-sm text-gray-600">{{ $bookingDescription }}</p>
                                                <div class="flex items-center space-x-2 mt-1">
                                                    <span
                                                        class="inline-flex px-2 py-1 text-xs font-semibold rounded-full {{ $badgeColor }}">{{ $bookingType }}</span>
                                                    <span class="text-xs text-gray-500">{{ $order->created_at->format('M d, Y') }}</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="text-right">
                                            <div class="text-lg font-bold text-gray-900">৳{{ number_format($order->total_amount ?? 0) }}</div>
                                            <span
                                                class="inline-flex px-2 py-1 text-xs font-semibold rounded-full {{ $statusColor }}">{{ ucfirst($order->status->value ?? 'Pending') }}</span>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="p-12 text-center">
                            <div class="w-20 h-20 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-6">
                                <svg class="w-10 h-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2">
                                    </path>
                                </svg>
                            </div>
                            <h3 class="text-xl font-semibold text-gray-900 mb-3">No bookings yet</h3>
                            <p class="text-gray-600 mb-6">Start exploring our amazing travel options and make your first booking!</p>
                            <a href="#"
                                class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-green-500 to-emerald-600 text-white rounded-xl hover:from-green-600 hover:to-emerald-700 transition-all duration-300 shadow-lg hover:shadow-xl">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                </svg>
                                Start Booking
                            </a>
                        </div>
                    @endif
                </div>
            </main>
        </div>
    </div>
</div>
