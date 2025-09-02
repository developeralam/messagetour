<?php

use Livewire\Volt\Component;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;

new #[Layout('components.layouts.app')] #[Title('Customer Dashboard')] class extends Component {
    //
}; ?>

<div>
    <div class="w-full min-h-screen py-12 bg-gray-100">
        <div class="w-11/12 max-w-7xl mx-auto flex flex-col lg:flex-row gap-8">

            <!-- Sidebar -->
            <aside
                class="w-full lg:w-1/4 bg-white/40 backdrop-blur-lg border border-gray-200 shadow-2xl rounded-3xl p-6">
                <!-- Profile Card -->
                <div
                    class="relative bg-white/70 backdrop-blur-lg border border-gray-200 rounded-2xl p-6 shadow-md text-center overflow-hidden">
                    <!-- Banner -->

                    <!-- Profile Image -->
                    <div class="relative z-10">
                        <img src="{{ auth()->user()->customer->image_link ?? '/empty-user.jpg' }}"
                            class="w-24 h-24 mx-auto rounded-full border-4 border-white shadow-lg object-cover"
                            alt="User Image">
                    </div>

                    <!-- Name & Email -->
                    <div class="relative z-10 mt-2">
                        <h2 class="text-lg font-bold text-gray-800">{{ auth()->user()->name }}</h2>
                        <p class="text-sm text-gray-500 truncate">{{ auth()->user()->email }}</p>
                    </div>

                    <div class="mt-2 mb-2 border-t border-green-200"></div>

                    <!-- Meta -->
                    <div class="text-sm text-gray-600 mt-2 space-y-1">
                        <p><span class="font-semibold">Joined:</span> {{ auth()->user()->created_at->format('M d, Y') }}
                        </p>
                    </div>
                </div>

                <!-- Sidebar Menu -->
                <div class="mt-8 space-y-3">
                    @php
                        $menu = [
                            ['label' => 'Dashboard', 'icon' => 'fas.gauge'],
                            ['label' => 'My Bookings', 'icon' => 'fas.calendar-check'],
                            ['label' => 'My Profile', 'icon' => 'fas.user'],
                            ['label' => 'Change Password', 'icon' => 'fas.lock'],
                            ['label' => 'Support', 'icon' => 'fas.headset'],
                        ];
                    @endphp

                    @foreach ($menu as $item)
                        <button
                            class="flex items-center gap-3 w-full px-4 py-3 rounded-xl bg-white/40 hover:bg-white/70 text-gray-700 font-medium transition-all backdrop-blur-md shadow-md hover:shadow-lg border border-white">
                            <x-icon name="{{ $item['icon'] }}" class="w-5 h-5 text-green-500" />
                            {{ $item['label'] }}
                        </button>
                    @endforeach

                    <button
                        class="flex items-center gap-3 w-full px-4 py-3 mt-2 rounded-xl text-red-600 font-medium hover:bg-red-100 transition-all border border-red-100 bg-white/40 backdrop-blur-md shadow-md hover:shadow-lg">
                        <x-icon name="fas.right-from-bracket" class="w-5 h-5 text-red-500" />
                        Logout
                    </button>
                </div>
            </aside>

            <!-- Main Content -->
            <main class="w-full lg:w-3/4 bg-white rounded-3xl shadow-2xl overflow-hidden">

                <!-- Top Bar -->
                <div
                    class="px-6 md:px-8 py-5 border-b border-green-100 flex flex-col md:flex-row md:items-center gap-4">
                    <div>
                        <h1 class="text-xl md:text-2xl font-bold tracking-tight text-green-800">
                            Welcome back, John
                        </h1>
                    </div>

                    <div class="md:ml-auto flex items-center gap-2">
                        <div class="hidden md:block">
                            <input type="text" placeholder="Search bookings…"
                                class="w-64 rounded-xl border border-green-200 bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-green-400">
                        </div>
                    </div>
                </div>

                <!-- Stat Cards -->
                <div class="p-6 md:p-8 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">

                    <div
                        class="group bg-white rounded-2xl shadow-xl border border-green-100 hover:shadow-2xl transition-all hover:-translate-y-0.5">
                        <div class="p-6 flex flex-col gap-3">
                            <div class="flex items-center justify-between">
                                <div class="bg-green-100 p-3 rounded-xl">
                                    <svg class="text-green-600 w-6 h-6" viewBox="0 0 24 24" fill="currentColor">
                                        <path d="M7 11h10v2H7zM5 7h14v2H5zM9 15h6v2H9z" />
                                    </svg>
                                </div>
                            </div>
                            <h4 class="text-sm font-semibold text-green-500">Total Bookings</h4>
                            <p class="text-3xl font-bold group-hover:text-green-600 transition">5</p>
                            <div
                                class="h-10 rounded-lg bg-gradient-to-r from-green-50 to-green-50 overflow-hidden relative">
                                <div
                                    class="absolute inset-0 opacity-60 [background:repeating-linear-gradient(90deg,transparent_0_8px,rgba(0,0,0,0.03)_8px_9px)]">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div
                        class="group bg-white rounded-2xl shadow-xl border border-green-100 hover:shadow-2xl transition-all hover:-translate-y-0.5">
                        <div class="p-6 flex flex-col gap-3">
                            <div class="flex items-center justify-between">
                                <div class="bg-green-100 p-3 rounded-xl">
                                    <svg class="text-green-600 w-6 h-6" viewBox="0 0 24 24" fill="currentColor">
                                        <path d="M7 11h10v2H7zM5 7h14v2H5zM9 15h6v2H9z" />
                                    </svg>
                                </div>
                            </div>
                            <h4 class="text-sm font-semibold text-green-500">Total Orders</h4>
                            <p class="text-3xl font-bold group-hover:text-green-600 transition">8</p>
                            <div
                                class="h-10 rounded-lg bg-gradient-to-r from-green-50 to-green-50 overflow-hidden relative">
                                <div
                                    class="absolute inset-0 opacity-60 [background:repeating-linear-gradient(90deg,transparent_0_8px,rgba(0,0,0,0.03)_8px_9px)]">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div
                        class="group bg-white rounded-2xl shadow-xl border border-green-100 hover:shadow-2xl transition-all hover:-translate-y-0.5">
                        <div class="p-6 flex flex-col gap-3">
                            <div class="flex items-center justify-between">
                                <div class="bg-green-100 p-3 rounded-xl">
                                    <svg class="text-green-600 w-6 h-6" viewBox="0 0 24 24" fill="currentColor">
                                        <path d="M7 11h10v2H7zM5 7h14v2H5zM9 15h6v2H9z" />
                                    </svg>
                                </div>
                            </div>
                            <h4 class="text-sm font-semibold text-green-500">Total Query</h4>
                            <p class="text-3xl font-bold group-hover:text-green-600 transition">2</p>
                            <div
                                class="h-10 rounded-lg bg-gradient-to-r from-green-50 to-green-50 overflow-hidden relative">
                                <div
                                    class="absolute inset-0 opacity-60 [background:repeating-linear-gradient(90deg,transparent_0_8px,rgba(0,0,0,0.03)_8px_9px)]">
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

                <!-- Quick Actions -->
                <div class="px-6 md:px-8 pb-2">
                    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3">
                        <a href="#" class="action-tile">
                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M3 11l9-7 9 7v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-9z" />
                            </svg>
                            Book Hotel
                        </a>
                        <a href="#" class="action-tile">
                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M12 2a10 10 0 1 0 8.94 5.56L12 13 3.06 7.56A10 10 0 0 0 12 2z" />
                            </svg>
                            Book Tour
                        </a>
                        <a href="#" class="action-tile">
                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M5 11h14l1 5H4l1-5zm2-7h10l1 4H6l1-4z" />
                            </svg>
                            Gear Order
                        </a>
                        <a href="#" class="action-tile">
                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M5 11h14l1 5H4l1-5zm2-7h10l1 4H6l1-4z" />
                            </svg>
                            Rent Car
                        </a>
                        <a href="#" class="action-tile">
                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor">
                                <path
                                    d="M12 12a5 5 0 1 0-5-5 5 5 0 0 0 5 5Zm0 2c-4.33 0-8 2.17-8 5v1h16v-1c0-2.83-3.67-5-8-5Z" />
                            </svg>
                            Corporate Query
                        </a>
                    </div>
                </div>

                <!-- Recent Bookings -->
                <div class="p-6 md:p-8">
                    <h3 class="text-base font-semibold text-green-800 mb-3">Recent Bookings</h3>

                    <div class="overflow-hidden rounded-2xl border border-green-100 bg-white">
                        <table class="min-w-full text-sm">
                            <thead class="bg-green-50 text-green-700">
                                <tr>
                                    <th class="px-4 py-3 text-left font-medium">Item</th>
                                    <th class="px-4 py-3 text-left font-medium">Type</th>
                                    <th class="px-4 py-3 text-left font-medium">Date</th>
                                    <th class="px-4 py-3 text-left font-medium">Amount</th>
                                    <th class="px-4 py-3 text-left font-medium">Status</th>
                                    <th class="px-4 py-3"></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-green-50">
                                <tr class="hover:bg-green-50 transition">
                                    <td class="px-4 py-3 font-medium ">Hotel Lakeshore</td>
                                    <td class="px-4 py-3">
                                        <span
                                            class="px-2.5 py-1 rounded-lg text-xs font-semibold bg-primary text-white">Hotel</span>
                                    </td>
                                    <td class="px-4 py-3 ">Aug 10, 2025</td>
                                    <td class="px-4 py-3 ">৳12,500</td>
                                    <td class="px-4 py-3">
                                        <span
                                            class="px-2.5 py-1 rounded-lg text-xs font-semibold bg-green-100 text-green-700">Paid</span>
                                    </td>
                                    <td class="px-4 py-3 text-right">
                                        <a href="#"
                                            class="inline-flex items-center gap-1 text-green-700 hover:underline">
                                            View <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 24 24">
                                                <path d="M9 6l6 6-6 6" />
                                            </svg>
                                        </a>
                                    </td>
                                </tr>
                                <tr class="hover:bg-green-50 transition">
                                    <td class="px-4 py-3 font-medium ">Cox’s Bazar Tour</td>
                                    <td class="px-4 py-3">
                                        <span
                                            class="px-2.5 py-1 rounded-lg text-xs font-semibold bg-primary text-white">Tour</span>
                                    </td>
                                    <td class="px-4 py-3 ">Jul 28, 2025</td>
                                    <td class="px-4 py-3 ">৳8,900</td>
                                    <td class="px-4 py-3">
                                        <span
                                            class="px-2.5 py-1 rounded-lg text-xs font-semibold bg-amber-100 text-amber-700">Pending</span>
                                    </td>
                                    <td class="px-4 py-3 text-right">
                                        <a href="#"
                                            class="inline-flex items-center gap-1 text-green-700 hover:underline">
                                            View <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 24 24">
                                                <path d="M9 6l6 6-6 6" />
                                            </svg>
                                        </a>
                                    </td>
                                </tr>
                                <tr class="hover:bg-green-50 transition">
                                    <td class="px-4 py-3 font-medium ">Airport Drop</td>
                                    <td class="px-4 py-3">
                                        <span
                                            class="px-2.5 py-1 rounded-lg text-xs font-semibold bg-primary text-white">Car</span>
                                    </td>
                                    <td class="px-4 py-3 ">Jul 14, 2025</td>
                                    <td class="px-4 py-3 ">৳2,000</td>
                                    <td class="px-4 py-3">
                                        <span
                                            class="px-2.5 py-1 rounded-lg text-xs font-semibold bg-rose-100 text-rose-700">Canceled</span>
                                    </td>
                                    <td class="px-4 py-3 text-right">
                                        <a href="#"
                                            class="inline-flex items-center gap-1 text-green-700 hover:underline">
                                            View <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 24 24">
                                                <path d="M9 6l6 6-6 6" />
                                            </svg>
                                        </a>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                </div>
            </main>

        </div>
    </div>
</div>
