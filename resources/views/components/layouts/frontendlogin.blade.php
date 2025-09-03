<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="light scroll-smooth" dir="ltr">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <link rel="icon" type="image/x-icon" href="{{ $globalSettings->logo_link ?? '/logo.png' }}">
    <title>{{ isset($title) ? $title . ' - ' . config('app.name') : config('app.name') }}</title>

    {{-- <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css"> --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">

    @vite('resources/css/app.css')

</head>

<body>

    <!-- TAGLINE START-->
    <div class="bg-slate-900">
        <div class="w-11/12 md:max-w-6xl mx-auto py-3 md:px-2 lg:px-3 xl:px-0">
            <div class="grid grid-cols-1">
                <div class="flex items-center justify-between">
                    <ul class="list-none flex xl:items-center gap-x-3 gap-y-1 flex-col md:flex-row">
                        <li class="inline-flex items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round" class="feather feather-clock text-green-500 size-4">
                                <circle cx="12" cy="12" r="10"></circle>
                                <polyline points="12 6 12 12 16 14"></polyline>
                            </svg>
                            <span class="ms-2 text-slate-300 text-xs md:text-sm">Mon-Sat: 9am to 6pm</span>
                        </li>
                        <li class="inline-flex items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round" class="feather feather-map-pin text-green-500 size-4">
                                <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                                <circle cx="12" cy="10" r="3"></circle>
                            </svg>
                            <span
                                class="ms-2 text-slate-300 text-xs md:text-sm">{{ $globalSettings->address ?? '' }}</span>
                        </li>
                    </ul>

                    <ul class="list-none flex xl:items-center gap-x-3 gap-y-1 flex-col md:flex-row">
                        <li class="inline-flex items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round" class="feather feather-mail text-green-500 size-4">
                                <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z">
                                </path>
                                <polyline points="22,6 12,13 2,6"></polyline>
                            </svg>
                            <a href="mailto:admin@massagetourtravels.com"
                                class="ms-2 text-slate-300 hover:text-slate-200 text-xs md:text-sm">admin@massagetourtravels.com</a>
                        </li>
                        <li class="inline-flex items-center self-end">
                            <ul class="list-none flex items-center">
                                <li class="inline-flex mb-0"><a href="{{ $globalSettings->facebook_url ?? '' }}"
                                        class="text-slate-300 hover:text-green-500"><svg
                                            xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                            stroke-linecap="round" stroke-linejoin="round"
                                            class="feather feather-facebook size-4 align-middle" title="facebook">
                                            <path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z">
                                            </path>
                                        </svg></a></li>
                                <li class="inline-flex ms-2 mb-0"><a href="{{ $globalSettings->instagram_url ?? '' }}"
                                        class="text-slate-300 hover:text-green-500"><svg
                                            xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                            stroke-linecap="round" stroke-linejoin="round"
                                            class="feather feather-instagram size-4 align-middle" title="instagram">
                                            <rect x="2" y="2" width="20" height="20" rx="5"
                                                ry="5"></rect>
                                            <path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"></path>
                                            <line x1="17.5" y1="6.5" x2="17.51" y2="6.5">
                                            </line>
                                        </svg></a></li>
                                <li class="inline-flex ms-2 mb-0"><a href="{{ $globalSettings->twitter_url ?? '' }}"
                                        class="text-slate-300 hover:text-green-500"><svg
                                            xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                            viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                            class="feather feather-twitter size-4 align-middle" title="twitter">
                                            <path
                                                d="M23 3a10.9 10.9 0 0 1-3.14 1.53 4.48 4.48 0 0 0-7.86 3v1A10.66 10.66 0 0 1 3 4s-4 9 5 13a11.64 11.64 0 0 1-7 2c9 5 20 0 20-11.5a4.5 4.5 0 0 0-.08-.83A7.72 7.72 0 0 0 23 3z">
                                            </path>
                                        </svg></a></li>
                                <li class="inline-flex ms-2 mb-0"><a href="{{ $globalSettings->phone ?? '' }}"
                                        class="text-slate-300 hover:text-green-500"><svg
                                            xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                            viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                            class="feather feather-phone size-4 align-middle" title="phone">
                                            <path
                                                d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z">
                                            </path>
                                        </svg></a></li>
                            </ul><!--end icon-->
                        </li>
                    </ul>
                </div>
            </div>
        </div><!--end container-->
    </div><!--end tagline-->
    <!-- TAGLINE END-->

    <nav x-data="{ isOpen: false }" class="bg-white shadow-lg sticky top-0 z-30 border-b border-gray-100">
        <div class="w-11/12 md:max-w-6xl mx-auto flex items-center justify-between py-1 md:py-3">
            <!-- Logo -->
            <a href="/" class="flex items-center">
                <img src="{{ $globalSettings->logo_link ?? '/logo.png' }}"
                    class="h-9 md:h-10 lg:h-12 rounded-lg shadow-none" alt=" Logo" />
            </a>

            <!-- Desktop Nav -->
            <div class="hidden md:flex flex-1 items-center justify-center">
                <ul class="flex gap-2 md:gap-3 lg:gap-4 items-center font-semibold text-gray-600">
                    <li>
                        <a href="/"
                            class="relative px-2 md:px-1.5 lg:px-3 py-1.5 md:py-2 rounded-lg hover:bg-green-50 hover:text-green-600 transition text-sm md:text-base lg:text-[15px] {{ request()->is('/') ? 'text-green-600 font-bold bg-green-50' : '' }}">
                            Home
                            @if (request()->is('/'))
                                <span class="absolute left-1/2 -bottom-1.5 -translate-x-1/2 rounded-full"></span>
                            @endif
                        </a>
                    </li>
                    <li>
                        <a href="/about-us"
                            class="relative px-2 md:px-1.5 lg:px-3 py-1.5 md:py-2 rounded-lg hover:bg-green-50 hover:text-green-600 transition text-sm md:text-base lg:text-[15px] {{ request()->is('about-us') ? 'text-green-600 font-bold bg-green-50' : '' }}">
                            About Us
                            @if (request()->is('about-us'))
                                <span class="absolute left-1/2 -bottom-1.5 -translate-x-1/2 rounded-full"></span>
                            @endif
                        </a>
                    </li>
                    <li>
                        <a href="/blogs"
                            class="relative px-2 md:px-1.5 lg:px-3 py-1.5 md:py-2 rounded-lg hover:bg-green-50 hover:text-green-600 transition text-sm md:text-base lg:text-[15px] {{ request()->is('blogs') ? 'text-green-600 font-bold bg-green-50' : '' }}">
                            Blog
                            @if (request()->is('blogs'))
                                <span class="absolute left-1/2 -bottom-1.5 -translate-x-1/2  rounded-full"></span>
                            @endif
                        </a>
                    </li>
                    <li>
                        <a href="/contact-us"
                            class="relative px-2 md:px-1.5 lg:px-3 py-1.5 md:py-2 rounded-lg hover:bg-green-50 hover:text-green-600 transition text-sm md:text-base lg:text-[15px] {{ request()->is('contact-us') ? 'text-green-600 font-bold bg-green-50' : '' }}">
                            Contact Us
                            @if (request()->is('contact-us'))
                                <span class="absolute left-1/2 -bottom-1.5 -translate-x-1/2 rounded-full"></span>
                            @endif
                        </a>
                    </li>
                    <li>
                        <a @click="$dispatch('openModel')"
                            class="relative px-2 md:px-1.5 lg:px-3 py-1.5 md:py-2 rounded-lg hover:bg-green-50 hover:text-green-600 transition text-sm md:text-base lg:text-[15px] {{ request()->is('contact-us') ? 'text-green-600 font-bold bg-green-50' : '' }}">
                            Corporate Query
                        </a>
                    </li>
                </ul>
            </div>

            <!-- Right Side Button/Auth -->
            <div class="flex items-center gap-2 md:gap-3 lg:gap-4">
                @auth
                    <div class="relative" x-data="{ userMenu: false }">
                        <button @click="userMenu = !userMenu"
                            class="flex items-center gap-2 focus:outline-none group border border-green-400 py-1 pl-1 pr-5 rounded-full">
                            @php
                                $user = auth()->user();
                                $image = match ($user->type) {
                                    \App\Enum\UserType::Customer => $user->customer->image_link ?? null,
                                    \App\Enum\UserType::Agent => $user->agent->propiter_image_link ?? null,
                                    default => null,
                                };
                            @endphp
                            <img src="{{ $image ?? '/empty-user.jpg' }}"
                                class="h-8 w-8 md:h-9 md:w-9 rounded-full shadow" alt="User" />
                            <svg class="w-4 h-4 text-gray-600 group-hover:text-green-600 transition" fill="none"
                                stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                        <div x-show="userMenu" @click.away="userMenu = false"
                            x-transition:enter="transition ease-out duration-200"
                            x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                            x-transition:leave="transition ease-in duration-100"
                            x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
                            class="absolute right-0 mt-2 w-44 bg-white rounded-lg shadow-lg py-2 z-50 border border-gray-100"
                            style="display: none;">
                            @if (auth()->user()->type == \App\Enum\UserType::Admin)
                                <a href="/admin/dashboard" target="_blank"
                                    class="block px-4 py-2 text-gray-700 hover:bg-green-50 hover:font-semibold hover:text-green-600 text-sm">Dashboard</a>
                            @elseif (auth()->user()->type == \App\Enum\UserType::Agent)
                                <a href="/partner/dashboard" target="_blank"
                                    class="block px-4 py-2 text-gray-700 hover:bg-green-50 hover:font-semibold hover:text-green-600 text-sm">Dashboard</a>
                            @else
                                <a href="/dashboard" target="_blank"
                                    class="block px-4 py-2 text-gray-700 hover:bg-green-50 hover:font-semibold hover:text-green-600 text-sm">Dashboard</a>
                            @endif
                            <div class="border-t my-1"></div>
                            <a href="/logout"
                                class="block px-4 py-2 text-red-600 hover:bg-red-50 hover:font-semibold hover:text-red-700 text-sm">Logout</a>
                        </div>
                    </div>
                @else
                    <!-- Premium Login/Register Button (Desktop Only) -->
                    <a href="/customer/login"
                        class="hidden md:inline-flex items-center gap-2 text-center py-2.5 px-6 rounded-full bg-white border border-green-400 text-green-600 font-semibold text-sm shadow-sm hover:bg-green-50 hover:text-green-700 transition-all duration-200 justify-center mx-auto md:w-auto md:py-1.5 md:px-4 md:rounded-full md:bg-white md:border md:border-green-400 md:text-green-600 md:font-semibold md:text-sm md:shadow-sm md:hover:bg-green-50 md:hover:text-green-700 md:transition-all md:duration-200"">
                        <svg class="w-4 h-4 text-green-600 hover:text-green-700 opacity-80" fill="none"
                            stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M5.121 17.804A13.937 13.937 0 0112 15c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        <span class="tracking-wide">Sign In</span>
                    </a>
                @endauth
                <!-- Hamburger (Mobile) -->
                <button @click="isOpen = !isOpen"
                    class="md:hidden flex items-center justify-center w-10 h-10 rounded-full hover:bg-green-50 focus:outline-none transition">
                    <svg x-show="!isOpen" class="w-7 h-7 text-green-600" fill="none" stroke="currentColor"
                        stroke-width="2" viewBox="0 0 24 24">
                        <line x1="4" y1="6" x2="20" y2="6" stroke-linecap="round" />
                        <line x1="4" y1="12" x2="20" y2="12" stroke-linecap="round" />
                        <line x1="4" y1="18" x2="20" y2="18" stroke-linecap="round" />
                    </svg>
                </button>
            </div>
        </div>

        <!-- Mobile Nav Overlay -->
        <div x-show="isOpen" x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 -translate-y-8" x-transition:enter-end="opacity-100 translate-y-0"
            x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0"
            x-transition:leave-end="opacity-0 -translate-y-8"
            class="fixed inset-0 z-40 bg-white/95 backdrop-blur-sm flex flex-col items-center justify-start pt-8 px-6 space-y-2 md:hidden"
            @click.away="isOpen = false" style="display: none;">
            <button @click="isOpen = !isOpen"
                class="md:hidden flex justify-end mr-auto w-7 h-7 rounded-full hover:bg-green-50 focus:outline-none transition">
                <svg x-show="isOpen" class="w-5 h-5 text-green-600" fill="none" stroke="currentColor"
                    stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>

            <a href="/" class="mb-6 flex items-center gap-2" @click="isOpen = false">
                <img src="{{ $globalSettings->logo_link ?? '/logo.png' }}" class="h-12 rounded-lg shadow-none"
                    alt=" Logo" />
            </a>
            <ul class="flex flex-col items-center w-full gap-1">
                <li>
                    <a href="/" @click="isOpen = false"
                        class="block w-full text-center py-3 px-6 rounded-lg font-bold text-sm text-gray-700 hover:bg-green-100 hover:text-green-600 transition {{ request()->is('/') ? 'bg-green-50 text-green-600' : '' }}">
                        Home
                    </a>
                </li>
                <li>
                    <a href="/about-us" @click="isOpen = false"
                        class="block w-full text-center py-3 px-6 rounded-lg font-bold text-sm text-gray-700 hover:bg-green-100 hover:text-green-600 transition {{ request()->is('about-us') ? 'bg-green-50 text-green-600' : '' }}">
                        About Us
                    </a>
                </li>
                <li>
                    <a href="/blogs" @click="isOpen = false"
                        class="block w-full text-center py-3 px-6 rounded-lg font-bold text-sm text-gray-700 hover:bg-green-100 hover:text-green-600 transition {{ request()->is('blogs') ? 'bg-green-50 text-green-600' : '' }}">
                        Blog
                    </a>
                </li>
                <li>
                    <a href="/contact-us" @click="isOpen = false"
                        class="block w-full text-center py-3 px-6 rounded-lg font-bold text-sm text-gray-700 hover:bg-green-100 hover:text-green-600 transition {{ request()->is('contact-us') ? 'bg-green-50 text-green-600' : '' }}">
                        Contact Us
                    </a>
                </li>
                <li>
                    <a @click="$dispatch('openModel'); isOpen = false"
                        class="block w-full text-center py-3 px-6 rounded-lg font-bold text-sm text-gray-700 hover:bg-green-100 hover:text-green-600 transition {{ request()->is('contact-us') ? 'bg-green-50 text-green-600' : '' }}">
                        Corporate Query
                    </a>
                </li>
                @guest
                    <li>
                        <a href="/customer/login" @click="isOpen = false"
                            class="block w-full text-center py-2.5 px-6 rounded-full bg-white border border-green-400 text-green-600 font-semibold text-sm shadow-sm hover:bg-green-50 hover:text-green-700 transition-all duration-200 flex items-center justify-center gap-2 mx-auto md:inline-flex md:w-auto md:py-1.5 md:px-4 md:rounded-full md:bg-white md:border md:border-green-400 md:text-green-600 md:font-semibold md:text-sm md:shadow-sm md:hover:bg-green-50 md:hover:text-green-700 md:transition-all md:duration-200"
                            style="max-width: 180px;">
                            <svg class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" stroke-width="2"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M5.121 17.804A13.937 13.937 0 0112 15c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            <span>Sign In</span>
                        </a>
                    </li>
                @endguest
            </ul>
        </div>
    </nav>
    <!-- Hero End-->

    {{ $slot }}

    <!-- Footer Start -->
    <footer class="bg-black text-white">
        <div class="w-11/12 md:max-w-6xl mx-auto">
            <div class="grid grid-cols-12">
                <div class="col-span-12">
                    <div class="lg:pt-10 pb-5 px-0">
                        <div class="grid md:grid-cols-12 grid-cols-1 gap-6">

                            <!-- Logo and Description -->
                            <div class="lg:col-span-2 md:col-span-12 text-center">
                                <a href="/" class="text-[22px] focus:outline-none">
                                    <img class="w-auto md:mx-auto lg:mx-0"
                                        src="{{ $globalSettings->logo_link ?? '/logo.png' }}">
                                </a>
                                <p class="mt-4 text-gray-300">Planning for a trip? We will organize
                                    your trip with the
                                    best places and within best budget!</p>
                            </div><!--end col-->

                            <!-- Discover & Contact Us: Wrapped for Small Screens -->
                            <div class="md:col-span-7 grid grid-cols-2 gap-6">

                                <!-- Discover -->
                                <div class="lg:ms-8">
                                    <h5 class="tracking-[1px] text-gray-100 font-semibold">Discover</h5>
                                    <ul class="list-none footer-list mt-4">
                                        <li><a href="/"
                                                class="text-gray-300 hover:text-green-400 duration-300 hover:underline ease-in-out text-sm lg:text-base"><i
                                                    class="mdi mdi-chevron-right"></i> Home</a></li>
                                        <li class="mt-1"><a href="/about-us"
                                                class="text-gray-300 hover:text-green-400 duration-300 hover:underline ease-in-out text-sm lg:text-base"><i
                                                    class="mdi mdi-chevron-right"></i> About us</a></li>
                                        <li class="mt-1"><a href="/blogs"
                                                class="text-gray-300 hover:text-green-400 duration-300 hover:underline ease-in-out text-sm lg:text-base"><i
                                                    class="mdi mdi-chevron-right"></i> Blog</a></li>
                                        <li class="mt-1"><a href="team.html"
                                                class="text-gray-300 hover:text-green-400 duration-300 hover:underline ease-in-out text-sm lg:text-base"><i
                                                    class="mdi mdi-chevron-right"></i> Teams & Conditions</a></li>
                                        <li class="mt-1"><a href="/privacy/policy"
                                                class="text-gray-300 hover:text-green-400 duration-300 hover:underline ease-in-out text-sm lg:text-base"><i
                                                    class="mdi mdi-chevron-right"></i> Privacy Policy</a></li>
                                        <li class="mt-1"><a href="/customer/login"
                                                class="text-gray-300 hover:text-green-400 duration-300 hover:underline ease-in-out text-sm lg:text-base"><i
                                                    class="mdi mdi-chevron-right"></i> Login / Register</a></li>
                                        <li class="mt-1"><a href="/partner/register"
                                                class="text-gray-300 hover:text-green-400 duration-300 hover:underline ease-in-out text-sm lg:text-base"><i
                                                    class="mdi mdi-chevron-right"></i> Become a Partner</a></li>
                                    </ul>
                                </div>

                                <!-- Contact Us -->
                                <div>
                                    <h5 class="tracking-[1px] text-gray-100 font-bold">Contact Us</h5>
                                    <h5
                                        class="tracking-[1px] text-gray-100 mt-4 text-sm lg:text-base whitespace-nowrap">
                                        {{ $globalSettings->application_name ?? 'Flyvaly Tour &amp; Travels' }}
                                    </h5>

                                    <div class="flex mt-2">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                            viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                            class="feather feather-map-pin size-4 text-green-500 me-2 mt-1">
                                            <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                                            <circle cx="12" cy="10" r="3"></circle>
                                        </svg>
                                        <div>
                                            <h6 class="text-xs lg:text-base text-gray-300 hover:text-slate-400">
                                                {{ $globalSettings->address ?? '' }}</h6>

                                        </div>
                                    </div>

                                    <div class="flex mt-2">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                            viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                            class="feather feather-mail size-4 text-green-500 me-2 mt-1">
                                            <path
                                                d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z">
                                            </path>
                                            <polyline points="22,6 12,13 2,6"></polyline>
                                        </svg>
                                        <div>
                                            <a href="mailto:{{ $globalSettings->contact_email ?? '' }}"
                                                class="text-xs lg:text-base text-slate-300 hover:text-slate-400 duration-500 ease-in-out">
                                                {{ $globalSettings->contact_email ?? '' }}
                                            </a>
                                        </div>
                                    </div>
                                    <div class="flex mt-2">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                            viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                            class="feather feather-phone size-4 text-green-500 me-2 mt-1">
                                            <path
                                                d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z">
                                            </path>
                                        </svg>
                                        <div class="">
                                            <a href="tel:{{ $globalSettings->phone ?? '' }}"
                                                class="text-xs lg:text-base text-slate-300 hover:text-slate-400 duration-500 ease-in-out">
                                                {{ $globalSettings->phone ?? '' }}
                                            </a>
                                        </div>
                                    </div>

                                    <!-- Social Icons -->
                                    <ul class="list-none mt-2 flex flex-wrap md:flex-nowrap gap-1 md:gap-0.5 lg:gap-1">
                                        <li class="inline"><a href="https://1.envato.market/travosy" target="_blank"
                                                class="size-5 md:size-7 lg:size-8 inline-flex items-center justify-center tracking-wide align-middle text-base border border-green-500 rounded-md hover:bg-green-500 hover:text-white text-slate-300"><svg
                                                    xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                                    viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                                    class="feather feather-shopping-cart size-3 md:size-4 align-middle"
                                                    title="Buy Now">
                                                    <circle cx="9" cy="21" r="1"></circle>
                                                    <circle cx="20" cy="21" r="1"></circle>
                                                    <path
                                                        d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6">
                                                    </path>
                                                </svg></a></li>
                                        <li class="inline"><a href="https://dribbble.com/shreethemes" target="_blank"
                                                class="size-5 md:size-7 lg:size-8 inline-flex items-center justify-center tracking-wide align-middle text-base border border-green-500 rounded-md hover:bg-green-500 hover:text-white text-slate-300"><svg
                                                    xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                                    viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                                    class="feather feather-dribbble size-3 md:size-4 align-middle"
                                                    title="dribbble">
                                                    <circle cx="12" cy="12" r="10"></circle>
                                                    <path
                                                        d="M8.56 2.75c4.37 6.03 6.02 9.42 8.03 17.72m2.54-15.38c-3.72 4.35-8.94 5.66-16.88 5.85m19.5 1.9c-3.5-.93-6.63-.82-8.94 0-2.58.92-5.01 2.86-7.44 6.32">
                                                    </path>
                                                </svg></a></li>
                                        <li class="inline"><a href="{{ $globalSettings->linkedin_url ?? '' }}"
                                                target="_blank"
                                                class="size-5 md:size-7 lg:size-8 inline-flex items-center justify-center tracking-wide align-middle text-base border border-green-500 rounded-md hover:bg-green-500 hover:text-white text-slate-300"><svg
                                                    xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                                    viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                                    class="feather feather-linkedin size-3 md:size-4 align-middle"
                                                    title="Linkedin">
                                                    <path
                                                        d="M16 8a6 6 0 0 1 6 6v7h-4v-7a2 2 0 0 0-2-2 2 2 0 0 0-2 2v7h-4v-7a6 6 0 0 1 6-6z">
                                                    </path>
                                                    <rect x="2" y="9" width="4" height="12"></rect>
                                                    <circle cx="4" cy="4" r="2"></circle>
                                                </svg></a></li>
                                        <li class="inline"><a href="{{ $globalSettings->facebook_url ?? '' }}"
                                                target="_blank"
                                                class="size-5 md:size-7 lg:size-8 inline-flex items-center justify-center tracking-wide align-middle text-base border border-green-500 rounded-md hover:bg-green-500 hover:text-white text-slate-300"><svg
                                                    xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                                    viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                                    class="feather feather-facebook size-3 md:size-4 align-middle"
                                                    title="facebook">
                                                    <path
                                                        d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z">
                                                    </path>
                                                </svg></a></li>
                                        <li class="inline"><a href="{{ $globalSettings->instagram_url ?? '' }}"
                                                target="_blank"
                                                class="size-5 md:size-7 lg:size-8 inline-flex items-center justify-center tracking-wide align-middle text-base border border-green-500 rounded-md hover:bg-green-500 hover:text-white text-slate-300"><svg
                                                    xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                                    viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                                    class="feather feather-instagram size-3 md:size-4 align-middle"
                                                    title="instagram">
                                                    <rect x="2" y="2" width="20" height="20" rx="5"
                                                        ry="5"></rect>
                                                    <path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z">
                                                    </path>
                                                    <line x1="17.5" y1="6.5" x2="17.51"
                                                        y2="6.5">
                                                    </line>
                                                </svg></a></li>
                                        <li class="inline">
                                            <a href="{{ $globalSettings->twitter_url ?? '' }}" target="_blank"
                                                class="size-5 md:size-7 lg:size-8 inline-flex items-center justify-center tracking-wide align-middle text-base border border-green-500 rounded-md hover:bg-green-500 hover:text-white text-slate-300"><svg
                                                    xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                                    viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                                    class="feather feather-twitter size-3 md:size-4 align-middle"
                                                    title="twitter">
                                                    <path
                                                        d="M23 3a10.9 10.9 0 0 1-3.14 1.53 4.48 4.48 0 0 0-7.86 3v1A10.66 10.66 0 0 1 3 4s-4 9 5 13a11.64 11.64 0 0 1-7 2c9 5 20 0 20-11.5a4.5 4.5 0 0 0-.08-.83A7.72 7.72 0 0 0 23 3z">
                                                    </path>
                                                </svg>
                                            </a>
                                        </li>
                                    </ul>
                                </div>

                            </div><!--end discover + contact-->

                            <!-- Newsletter -->
                            <div class="lg:col-span-3 md:col-span-5">
                                <h5 class="tracking-[1px] text-gray-100 font-semibold text-center md:text-left">
                                    Newsletter</h5>
                                <p class="mt-2 md:mt-4 text-center md:text-left">Sign up and receive the latest tips
                                    via email.</p>
                                <livewire:subscribe-component />
                            </div><!--end col-->

                        </div><!--end grid-->

                        <div class="mt-4 h-[2px] bg-gradient-to-r from-green-500 to-red-400"></div>
                        <div class="flex justify-center items-center mt-2">
                            <p class="text-gray-300 text-xs sm:text-base">Copyright &copy; {{ date('Y') }}. <a
                                    href="/"
                                    class="text-sm font-bold text-transparent bg-clip-text bg-gradient-to-r from-green-500 to-green-400 italic">MassageTourTravels.</a>
                                All Rights Reserved.
                            </p>
                        </div>
                    </div><!--end inner content-->
                </div>
            </div><!--end grid-->
        </div><!--end container-->
    </footer><!--end footer-->

    {{-- <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script> --}}
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

    <!-- JAVASCRIPTS -->
    @vite('resources/js/app.js')

    <!-- JAVASCRIPTS -->
    <x-toast />

</body>


</html>
