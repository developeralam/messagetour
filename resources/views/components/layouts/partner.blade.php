<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" type="image/x-icon" href="{{ $globalSettings->favicon_link ?? '/logo.png' }}">
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
            <a href="/partner/dashboard" wire:navigate class="">
                <!-- Hidden when collapsed -->
                <div {{ $attributes->class(['hidden-when-collapsed']) }}>
                    <div class="flex items-center gap-2">
                        <img class="w-48 h-10" src="{{ $globalSettings->favicon_link ?? '/logo.png' }}" alt="Logo">
                    </div>
                </div>
                <!-- Display when collapsed -->
                <div class="display-when-collapsed hidden mx-5 mt-4 lg:mb-6 h-[28px]">
                    <img src="{{ $globalSettings->favicon_link ?? '/logo.png' }}" alt="Logo">
                </div>
            </a>
            <div class="ml-14">
                <strong>Reservation:</strong>: {{ $globalSettings->reservation ?? '' }} <br>
                <strong>Email:</strong>: {{ $globalSettings->reservation_email ?? '' }}
            </div>
            <div class="ml-14">
                <strong>Account:</strong>: {{ $globalSettings->account ?? '' }} <br>
                <strong>Email:</strong>: {{ $globalSettings->account_email ?? '' }}
            </div>
            <h4 class="text-2xl font-extrabold ml-8">Wallet:
                {{ auth()->user()->agent->wallet > 0 ? 'BDT ' . number_format(auth()->user()->agent->wallet) : '0' }}
            </h4>

        </x-slot:brand>
        {{-- Right side actions --}}
        <x-slot:actions>

            <livewire:admin-notification-component />
            <img src="{{ auth()->user()->agent->business_logo_link ?? '/empty-user.jpg' }}"
                class="w-8 h-8 rounded-full">
            <x-dropdown label="{{ auth()->user()->agent->business_name ?? '' }}" class="btn-sm btn-primary" right>
                <x-menu-item title="Profile" icon="fas.user" link="/partner/profile" />
                <x-menu-item title="Logout" icon="o-power" link="/partner/logout" />
            </x-dropdown>
        </x-slot:actions>
    </x-nav>


    {{-- The main content with `full-width` --}}
    <x-main full-width>

        {{-- This is a sidebar that works also as a drawer on small screens --}}
        {{-- Notice the `main-drawer` reference here --}}
        <x-slot:sidebar drawer="main-drawer" collapsible class="bg-base-200 lg:bg-inherit">

            {{-- Activates the menu item when a route matches the `link` property --}}
            <x-menu activate-by-route>
                <x-menu-item title="Dashboard" icon="o-home" link="/partner/dashboard" />
                <x-menu-separator />

                <x-menu-item title="Orders" icon="fab.first-order-alt" link="/partner/order/list" />
                <x-menu-separator />

                <x-menu-item title="Bookings" icon="fab.first-order-alt" link="/partner/booking/list" />
                <x-menu-separator />

                @if (auth()->user()->agent->agent_type == \App\Enum\AgentType::General)
                    {{-- Hotel --}}
                    <x-menu-sub title="Hotel" icon="fas.hotel" :open="request()->is('partner/hotel/*')">
                        <x-menu-item title="Hotel List" icon="fas.list" link="/partner/hotel/list" />
                        <x-menu-item title="Add Hotel" icon="fas.plus" no-wire-navigate link="/partner/hotel/create" />
                    </x-menu-sub>
                    <x-menu-separator />
                @endif
                @if (auth()->user()->agent->agent_type == \App\Enum\AgentType::General)
                    {{-- Aminities --}}
                    <x-menu-item title="Aminities" icon="fas.list" link="/partner/aminities" />
                    <x-menu-separator />
                @endif
                @if (auth()->user()->agent->agent_type == \App\Enum\AgentType::General)
                    {{-- Tour Package --}}
                    <x-menu-sub title="Tour Package" icon="fas.globe" :open="request()->is('partner/tour/*')">
                        <x-menu-item title="Tour Package List" icon="fas.list" link="/partner/tour/list" />
                        <x-menu-item title="Add Tour Package" icon="fas.plus" no-wire-navigate
                            link="/partner/tour/create" />
                    </x-menu-sub>
                    <x-menu-separator />
                @endif
                @if (auth()->user()->agent->agent_type == \App\Enum\AgentType::General)
                    {{-- Travel Product --}}
                    <x-menu-sub title="Travel Product" icon="fab.product-hunt" :open="request()->is('partner/travel-product/*')">
                        <x-menu-item title="Travel Product List" icon="fas.list" link="/partner/travel-product/list" />
                        <x-menu-item title="Add Travel Product" icon="fas.plus" no-wire-navigate
                            link="/partner/travel-product/create" />
                    </x-menu-sub>
                    <x-menu-separator />

                    {{-- Corporate Query --}}
                    <x-menu-item title="Corporate Query" icon="fas.list" link="/partner/corporate-query/list" />
                    <x-menu-separator />
                @endif
                @if (auth()->user()->agent->agent_type == \App\Enum\AgentType::General)
                    {{-- Vehicle --}}
                    <x-menu-sub title="Vehicle" icon="fas.car" :open="request()->is('partner/vehicle/*')">
                        <x-menu-item title="Vehicle List" icon="fas.list" link="/partner/vehicle/list" />
                        <x-menu-item title="Add Vehicle" icon="fas.plus" no-wire-navigate
                            link="/partner/vehicle/create" />
                    </x-menu-sub>
                    <x-menu-separator />
                @endif

                {{-- Markup --}}
                <x-menu-item title="Markup" icon="fas.m" link="/partner/markup" />
                <x-menu-separator />

                {{-- Wallet --}}
                <x-menu-item title="Deposit Request" icon="fas.money-bill-transfer" link="/partner/deposit-request" />
                <x-menu-separator />

                {{-- Wallet --}}
                <x-menu-item title="Wallet Withdraw" icon="fas.money-bill-transfer" link="/partner/wallet" />
                <x-menu-separator />

                {{-- Profile --}}
                <x-menu-item title="Profile" icon="fas.user" link="/partner/profile" />

            </x-menu>
        </x-slot:sidebar>

        {{-- The `$slot` goes here --}}
        <x-slot:content>
            {{ $slot }}
        </x-slot:content>
    </x-main>
    <x-toast />

    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    @stack('custom-script')
</body>

</html>
