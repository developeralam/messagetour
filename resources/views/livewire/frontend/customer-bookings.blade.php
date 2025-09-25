<?php

use Livewire\Volt\Component;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;

new #[Layout('components.layouts.app')] #[Title('My Bookings')] class extends Component {
    //
}; ?>

<div class="min-h-screen bg-gradient-to-br from-emerald-50 via-green-50 to-teal-50">
    <!-- Header -->
    <header class="bg-white/80 backdrop-blur-md border-b border-green-100 sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <div class="flex items-center space-x-4">
                    <div class="w-10 h-10 bg-gradient-to-r from-green-500 to-emerald-600 rounded-xl flex items-center justify-center">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                        </svg>
                    </div>
                    <h1 class="text-xl font-bold text-gray-800">My Bookings</h1>
                </div>

                <div class="flex items-center space-x-4">
                    <div class="hidden md:block relative">
                        <input type="text" placeholder="Search bookings..."
                            class="w-64 pl-10 pr-4 py-2 bg-white/70 border border-green-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent">
                        <svg class="absolute left-3 top-2.5 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z">
                            </path>
                        </svg>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">

            <!-- Sidebar -->
            <aside class="lg:col-span-1">
                <!-- Profile Card -->
                <div class="bg-white/70 backdrop-blur-lg rounded-2xl p-6 shadow-lg border border-green-100 mb-6">
                    <div class="text-center">
                        <div class="relative inline-block">
                            <img src="{{ auth()->user()->customer->image_link ?? '/empty-user.jpg' }}"
                                class="w-20 h-20 rounded-full border-4 border-white shadow-lg object-cover" alt="Profile">
                            <div class="absolute -bottom-1 -right-1 w-6 h-6 bg-green-500 rounded-full border-2 border-white"></div>
                        </div>
                        <h3 class="mt-4 text-lg font-semibold text-gray-800">{{ auth()->user()->name }}</h3>
                        <p class="text-sm text-gray-500">{{ auth()->user()->email }}</p>
                        <div class="mt-3 text-xs text-gray-600">
                            Member since {{ auth()->user()->created_at->format('M Y') }}
                        </div>
                    </div>
                </div>

                <!-- Navigation -->
                <nav class="space-y-2">
                    @php
                        $currentRoute = request()->route()->getName();
                        $menu = [
                            [
                                'label' => 'Dashboard',
                                'icon' => 'M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2z',
                                'route' => '.customerdashboard',
                                'active' => $currentRoute === '.customerdashboard',
                            ],
                            [
                                'label' => 'My Bookings',
                                'icon' => 'M8 7V3a2 2 0 012-2h4a2 2 0 012 2v4m-6 0h6m-6 0l-4 4m4-4l4 4m-4-4v12',
                                'route' => '.customerbookings',
                                'active' => $currentRoute === '.customerbookings',
                            ],
                            [
                                'label' => 'My Profile',
                                'icon' => 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z',
                                'route' => '.customerprofile',
                                'active' => $currentRoute === '.customerprofile',
                            ],
                            [
                                'label' => 'Change Password',
                                'icon' => 'M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z',
                                'route' => '.customerchange-password',
                                'active' => $currentRoute === '.customerchange-password',
                            ],
                        ];
                    @endphp

                    @foreach ($menu as $item)
                        <a href="{{ route($item['route']) }}"
                            class="flex items-center space-x-3 px-4 py-3 rounded-xl transition-all duration-200 {{ $item['active'] ? 'bg-green-500 text-white shadow-lg' : 'text-gray-600 hover:bg-white/70 hover:text-green-600' }}">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $item['icon'] }}"></path>
                            </svg>
                            <span class="font-medium">{{ $item['label'] }}</span>
                        </a>
                    @endforeach

                    <form method="POST" class="mt-4">
                        @csrf
                        <button type="submit"
                            class="flex items-center space-x-3 px-4 py-3 rounded-xl text-red-600 hover:bg-red-50 transition-all duration-200 w-full">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                            </svg>
                            <span class="font-medium">Logout</span>
                        </button>
                    </form>
                </nav>
            </aside>

            <!-- Main Content -->
            <main class="lg:col-span-3">
                <!-- Welcome Section -->
                <div class="mb-8">
                    <h2 class="text-2xl font-bold text-gray-800 mb-2">My Bookings 📋</h2>
                    <p class="text-gray-600">View and manage all your travel bookings in one place.</p>
                </div>

                <!-- Bookings Content -->
                <div class="bg-white/70 backdrop-blur-lg rounded-2xl shadow-lg border border-green-100 overflow-hidden">
                    <div class="p-6 border-b border-green-100">
                        <h3 class="text-lg font-semibold text-gray-800">All Bookings</h3>
                    </div>

                    <div class="p-8 text-center">
                        <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2">
                                </path>
                            </svg>
                        </div>
                        <h3 class="text-lg font-medium text-gray-900 mb-2">Bookings page coming soon!</h3>
                        <p class="text-gray-500 mb-4">We're working on a comprehensive bookings management system.</p>
                        <a href="{{ route('.customerdashboard') }}"
                            class="inline-flex items-center px-4 py-2 bg-green-500 text-white rounded-lg hover:bg-green-600 transition-colors">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                            </svg>
                            Back to Dashboard
                        </a>
                    </div>
                </div>
            </main>
        </div>
    </div>
</div>
