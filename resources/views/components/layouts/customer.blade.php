<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" type="image/x-icon" href="{{ $globalSettings->logo_link ?? '/logo.png' }}">
    <title>{{ isset($title) ? $title . ' - ' . config('app.name') : config('app.name') }}</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    @vite(['resources/css/app.css', 'resources/js/app.js'])

</head>

<body class="min-h-screen font-sans antialiased bg-base-200/50">
    {{-- The navbar with `sticky` and `full-width` --}}
    <x-nav sticky full-width separator progress-indicator>

        <x-slot:brand>
            {{-- Drawer toggle for "main-drawer" --}}
            <label for="main-drawer" class="lg:hidden mr-3">
                <x-icon name="o-bars-3" class="cursor-pointer" />
            </label>

            {{-- BRAND --}}
            <a href="/dashboard" wire:navigate class="">
                <!-- Hidden when collapsed -->
                <div {{ $attributes->class(['hidden-when-collapsed']) }}>
                    <div class="flex items-center gap-2">
                        <img class="w-48 h-10" src="{{ $globalSettings->logo_link ?? '/logo.png' }}" alt="Logo">
                    </div>
                </div>
                <!-- Display when collapsed -->
                <div class="display-when-collapsed hidden mx-5 mt-4 lg:mb-6 h-[28px]">
                    <img src="{{ $globalSettings->logo_link ?? '/logo.png' }}" alt="Logo">
                </div>
            </a>
        </x-slot:brand>
        {{-- Right side actions --}}
        <x-slot:actions>
            <livewire:admin-notification-component />
            <img src="{{ auth()->user()->customer->image_link ?? '/empty-user.jpg' }}" class="w-8 h-8 rounded-full">
            <x-button class="btn-primary btn-sm" responsive label="{{ auth()->user()->name ?? '' }}" />
            <x-button link="/" label="Back To Website" class="btn-sm btn-primary" />
        </x-slot:actions>
    </x-nav>

    {{-- MAIN --}}
    <x-main full-width>

        {{-- This is a sidebar that works also as a drawer on small screens --}}
        {{-- Notice the `main-drawer` reference here --}}
        <x-slot:sidebar drawer="main-drawer" collapsible class="bg-base-100 lg:bg-inherit">

            {{-- Activates the menu item when a route matches the `link` property --}}
            <x-menu activate-by-route>
                <x-menu-item title="Dashboard" icon="o-home" link="/dashboard" />
                <x-menu-separator />

                <x-menu-item title="Orders" icon="fab.first-order-alt" link="/my-orders" />
                <x-menu-separator />

                <x-menu-item title="Group Flight Booking" icon="fas.plane-departure" link="/my-group-flight-booking" />
                <x-menu-separator />

                <x-menu-item title="Hotel Booking" icon="fas.hotel" link="/my-hotel-booking" />
                <x-menu-separator />

                <x-menu-item title="Visa Booking" icon="fab.cc-visa" link="/my-visa-booking" />
                <x-menu-separator />

                <x-menu-item title="Tour Package Booking" icon="fas.globe" link="/my-tour-booking" />
                <x-menu-separator />

                <x-menu-item title="Car Booking" icon="fas.car" link="/my-car-booking" />
                <x-menu-separator />

                <x-menu-item title="Corporate Query" icon="fas.list" link="/my-corporate-query" />
                <x-menu-separator />

                {{-- <x-menu-item title="Insurance Booking" icon="fas.money-bill" />
                <x-menu-separator /> --}}

                <x-menu-item title="Profile" icon="fas.user" link="/profile" />
                <x-menu-separator />

                <x-menu-item title="Logout" icon="o-power" link="/logout" />

            </x-menu>
        </x-slot:sidebar>

        {{-- The `$slot` goes here --}}
        <x-slot:content>
            {{ $slot }}
        </x-slot:content>
    </x-main>
    <x-toast />
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

</body>

</html>
