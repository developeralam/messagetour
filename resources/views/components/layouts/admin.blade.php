<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" type="image/x-icon" href="{{ $globalSettings->logo_link ?? '/logo.png' }}">
    <title>{{ isset($title) ? $title . ' - ' . config('app.name') : config('app.name') }}</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/summernote/0.8.18/summernote-lite.min.css" rel="stylesheet">
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
            <a href="/admin/dashboard" wire:navigate class="">
                <!-- Hidden when collapsed -->
                <div {{ $attributes->class(['hidden-when-collapsed']) }}>
                    <div class="flex items-center gap-2">
                        <img class="w-48 h-10" src="{{ asset('logo.png') }}" alt="Logo">
                    </div>
                </div>
                <!-- Display when collapsed -->
                <div class="display-when-collapsed hidden mx-5 mt-4 lg:mb-6 h-[28px]">
                    <img src="{{ asset('logo.png') }}" alt="Logo">
                </div>
            </a>
        </x-slot:brand>
        {{-- Right side actions --}}
        <x-slot:actions>
            <livewire:admin-notification-component />
            <x-dropdown label="{{ auth()->user()->name ?? '' }}" class="btn-sm btn-primary" right>
                <x-menu-item title="Profile" icon="fas.user" />
                <x-menu-item title="Logout" icon="o-power" link="/admin/logout" />
            </x-dropdown>
        </x-slot:actions>
    </x-nav>

    {{-- MAIN --}}
    <x-main full-width>

        {{-- This is a sidebar that works also as a drawer on small screens --}}
        {{-- Notice the `main-drawer` reference here --}}
        <x-slot:sidebar drawer="main-drawer" collapsible class="bg-base-100 lg:bg-inherit">

            {{-- Activates the menu item when a route matches the `link` property --}}
            <x-menu activate-by-route>
                <x-menu-item title="Dashboard" icon="o-home" link="/admin/dashboard" />
                <x-menu-separator />

                @can('hotel')
                    {{-- Hotel --}}
                    <x-menu-sub title="Hotel" icon="fas.hotel" :open="request()->is('admin/hotel/*', 'admin/agent/hotel/list')">
                        <x-menu-item title="Hotel List" icon="fas.list" link="/admin/hotel/list" />
                        <x-menu-item title="Agnet Hotel List" icon="fas.list" link="/admin/agent/hotel/list" />
                        <x-menu-item title="Add Hotel" icon="fas.plus" no-wire-navigate link="/admin/hotel/create" />
                    </x-menu-sub>
                    <x-menu-separator />
                @endcan

                @can('aminities')
                    {{-- Aminities --}}
                    <x-menu-item title="Aminities" icon="fas.list" link="/admin/aminities" />
                    <x-menu-separator />
                @endcan

                @can('visa')
                    {{-- Visa --}}
                    <x-menu-sub title="Visa" icon="fab.cc-visa" :open="request()->is('admin/visa/*')">
                        <x-menu-item title="Visa List" icon="fas.list" link="/admin/visa/list" />
                        <x-menu-item title="Add Visa" icon="fas.plus" no-wire-navigate link="/admin/visa/create" />
                    </x-menu-sub>
                    <x-menu-separator />
                @endcan

                @can('tour-package')
                    {{-- Tour Package --}}
                    <x-menu-sub title="Tour Package" icon="fas.globe" :open="request()->is('admin/tour/*', 'admin/agent/tour/list')">
                        <x-menu-item title="Tour Package List" icon="fas.list" link="/admin/tour/list" />
                        <x-menu-item title="Agnet Tour Package List" icon="fas.list" link="/admin/agent/tour/list" />
                        <x-menu-item title="Add Tour Package" icon="fas.plus" no-wire-navigate link="/admin/tour/create" />
                    </x-menu-sub>
                    <x-menu-separator />
                @endcan

                @can('travel-product')
                    {{-- Travel Product --}}
                    <x-menu-sub title="Travel Product" icon="fab.product-hunt" :open="request()->is('admin/travel-product/*', 'admin/agent/travel-product/list')">
                        <x-menu-item title="Product List" icon="fas.list" link="/admin/travel-product/list" />
                        <x-menu-item title="Agent Product List" icon="fas.list" link="/admin/agent/travel-product/list" />
                        <x-menu-item title="Add Product" icon="fas.plus" no-wire-navigate
                            link="/admin/travel-product/create" />
                    </x-menu-sub>
                    <x-menu-separator />
                @endcan

                @can('group-flight')
                    {{-- Group Flight --}}
                    <x-menu-sub title="Group Flight" icon="fas.plane-departure" :open="request()->is('admin/group-flight/*')">
                        <x-menu-item title="Group Flight List" icon="fas.list" link="/admin/group-flight/list" />
                        <x-menu-item title="Add Group Flight" icon="fas.plus" no-wire-navigate
                            link="/admin/group-flight/create" />
                    </x-menu-sub>
                    <x-menu-separator />
                @endcan

                @can('vehicle')
                    {{-- Vehicle --}}
                    <x-menu-sub title="Vehicle" icon="fas.car" :open="request()->is('admin/vehicle/*', 'admin/agent/vehicle/list')">
                        <x-menu-item title="Vehicle List" icon="fas.list" link="/admin/vehicle/list" />
                        <x-menu-item title="Agent Vehicle List" icon="fas.list" link="/admin/agent/vehicle/list" />
                        <x-menu-item title="Add Vehicle" icon="fas.plus" no-wire-navigate link="/admin/vehicle/create" />
                    </x-menu-sub>
                    <x-menu-separator />
                @endcan

                @canany(['orders', 'bookings', 'corporate-query'])
                    <x-menu-sub title="Booking Management" icon="fas.b" :open="request()->is(
                        'admin/order/list',
                        'admin/booking/list',
                        'admin/group-flight/booking/list',
                        'admin/e-visa/booking/list',
                        'admin/corporate-queries',
                    )">
                        @can('orders')
                            <x-menu-item title="Orders" icon="fas.list" link="/admin/order/list" />
                        @endcan

                        @can('bookings')
                            <x-menu-item title="Group Flight Bookings" icon="fas.list"
                                link="/admin/group-flight/booking/list" />
                            <x-menu-item title="E-visa Bookings" icon="fas.list" link="/admin/e-visa/booking/list" />
                            <x-menu-item title="Other Bookings List" icon="fas.list" link="/admin/booking/list" />
                        @endcan

                        @can('corporate-query')
                            <x-menu-item title="Corporate Query" icon="fas.list" link="/admin/corporate-queries" />
                        @endcan
                    </x-menu-sub>
                    <x-menu-separator />
                @endcanany


                @can('customer')
                    {{-- Customer --}}
                    <x-menu-item title="Customers" icon="fas.users" link="/admin/customers" />
                    <x-menu-separator />
                @endcan

                @canany(['agent', 'commission', 'agent-report'])
                    <x-menu-sub title="Agent Management" icon="fas.a" :open="request()->is('admin/agent/*', 'admin/commission/manage', 'admin/agent/sale/report')">
                        @can('agent')
                            <x-menu-item title="Agents" icon="fas.handshake" link="/admin/agent/list" />
                        @endcan

                        @can('commission')
                            <x-menu-item title="Agent Commissions" icon="fas.percent" link="/admin/commission/manage" />
                        @endcan

                        @can('agent-report')
                            <x-menu-item title="Agent Sales Report" icon="fas.file" link="/admin/agent/sale/report" />
                        @endcan
                    </x-menu-sub>
                    <x-menu-separator />
                @endcanany


                {{-- @can('transaction-history')
                    Transactions
                    <x-menu-item title="Transactions" icon="fas.money-bill-transfer" link="/admin/transaction/history" />
                    <x-menu-separator />
                @endcan --}}

                @can('bank-payment')
                    {{-- Bank & Payment --}}
                    <x-menu-sub title="Bank & Payment" icon="fas.building-columns" :open="request()->is('admin/banks', 'admin/payments')">
                        <x-menu-item title="Bank List" icon="fas.list" link="/admin/banks" />
                        <x-menu-item title="Payment List" icon="fas.list" link="/admin/payments" />
                    </x-menu-sub>
                    <x-menu-separator />
                @endcan

                @can('payment-gateway')
                    {{-- Payment Gateway --}}
                    <x-menu-item title="Payment Gateway" icon="fab.cc-amazon-pay" link="/admin/payment-gateways" />
                    <x-menu-separator />
                @endcan

                @can('location')
                    {{-- Location --}}
                    <x-menu-sub title="Locations" icon="o-cog-6-tooth" :open="request()->is('admin/settings/location/*')">
                        <x-menu-item title="Country" icon="fas.flag-usa" link="/admin/settings/location/country" />
                        <x-menu-item title="Division" icon="fas.d" link="/admin/settings/location/division" />
                        <x-menu-item title="District" icon="fas.d" link="/admin/settings/location/district" />
                    </x-menu-sub>
                    <x-menu-separator />
                @endcan

                @canany(['offer', 'faq', 'coupon', 'blog', 'subscriber', 'contactus', 'aboutus', 'globalsettings',
                    'reviews'])
                    <x-menu-sub title="Website Management" icon="fas.w" :open="request()->is(
                        'admin/blog/*',
                        'admin/subscribers',
                        'admin/contact-us',
                        'admin/about-us',
                        'admin/global-settings',
                        'admin/reviews',
                        'admin/offer/*',
                        'admin/faq/*',
                        'admin/coupon-codes',
                    )">
                        @can('offer')
                            <x-menu-item title="Offer List" icon="fas.list" link="/admin/offer/list" />
                        @endcan

                        @can('faq')
                            <x-menu-item title="Faq List" icon="fas.list" link="/admin/faq/list" />
                        @endcan

                        @can('coupon')
                            <x-menu-item title="Coupon Code" icon="fas.gift" link="/admin/coupon-codes" />
                        @endcan

                        @can('blog')
                            <x-menu-item title="Blog List" icon="fas.list" link="/admin/blog/list" />
                        @endcan

                        @can('subscriber')
                            <x-menu-item title="Subscriber" icon="fas.users" link="/admin/subscribers" />
                        @endcan

                        @can('contactus')
                            <x-menu-item title="Contact Us" icon="fas.address-book" link="/admin/contact-us" />
                        @endcan

                        @can('aboutus')
                            <x-menu-item title="About Us" icon="fas.list" no-wire-navigate link="/admin/about-us" />
                        @endcan

                        @can('globalsettings')
                            <x-menu-item title="Global Settings" icon="fas.globe" link="/admin/global-settings" />
                        @endcan

                        @can('reviews')
                            <x-menu-item title="Review" icon="fas.star" link="/admin/reviews" />
                        @endcan
                    </x-menu-sub>
                    <x-menu-separator />
                @endcanany

                {{-- Other Transactions --}}
                @php
                    $showOtherTransactionsMenu =
                        auth()->user()->can('voucher.list') || auth()->user()->can('voucher.create');
                @endphp

                @if ($showOtherTransactionsMenu)
                    <x-menu-sub title="Other Transactions" icon="fas.money-bill-transfer" :open="request()->is('admin/others-transcaiton/create', 'admin/others-transcaiton/list')">
                        @can('voucher.list')
                            <x-menu-item title="Add New Voucher" icon="fas.plus"
                                link="/admin/others-transcaiton/create" />
                        @endcan

                        @can('voucher.create')
                            <x-menu-item title="Voucher List" icon="fas.list" link="/admin/others-transcaiton/list" />
                        @endcan
                    </x-menu-sub>
                    <x-menu-separator />
                @endif

                @can('chart-of-account')
                    {{-- Chart Of Account --}}
                    <x-menu-sub title="Chart Of Account" icon="fas.book" :open="request()->is(
                        'admin/accounts/chart-of-account-category',
                        'admin/accounts/chart-of-account',
                    )">
                        <x-menu-item title="Category" icon="fas.list" link="/admin/accounts/chart-of-account-category" />
                        <x-menu-item title="Account" icon="fas.list" link="/admin/accounts/chart-of-account" />
                    </x-menu-sub>
                    <x-menu-separator />
                @endcan

                @can('trial-balance')
                    <x-menu-item title="Report" icon="far.file-lines" link="/admin/accounts/reports/trail-balance" />
                    <x-menu-separator />
                @endcan

                @can('income-expense')
                    <x-menu-sub title="Income & Expense" icon="fas.list" :open="request()->is('admin/income/list', 'admin/expense/list')">
                        <x-menu-item title="Incomes" icon="fas.list" link="/admin/income/list" />
                        <x-menu-item title="Expenses" icon="fas.list" link="/admin/expense/list" />
                    </x-menu-sub>
                    <x-menu-separator />
                @endcan

                @can('deposit_request')
                    {{-- Deposit Request --}}
                    <x-menu-item title="Deposit Request" icon="fas.money-bill-transfer" link="/admin/deposit-request" />
                    <x-menu-separator />
                @endcan

                @can('withdraw')
                    {{-- Withdraw --}}
                    <x-menu-sub title="Withdraw" icon="fas.money-bill-transfer" :open="request()->is('admin/withdraw/*')">
                        <x-menu-item title="Method" icon="fas.list" link="/admin/withdraw/method" />
                        <x-menu-item title="Withdraw List" icon="fas.list" link="/admin/withdraw/list" />
                    </x-menu-sub>
                    <x-menu-separator />
                @endcan

                @can('system-user-manage')
                    {{-- System User Management --}}
                    <x-menu-sub title="System User Management" icon="fas.user" :open="request()->is('admin/role/list', 'admin/system-user/list')">
                        <x-menu-item no-wire-navigate title="Role Management" icon="fas.list" link="/admin/role/list" />
                        <x-menu-item no-wire-navigate title="User Management" icon="fas.list"
                            link="/admin/system-user/list" />
                    </x-menu-sub>
                    <x-menu-separator />
                @endcan

            </x-menu>
        </x-slot:sidebar>

        {{-- The `$slot` goes here --}}
        <x-slot:content>
            {{ $slot }}
        </x-slot:content>
    </x-main>
    <x-toast />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/summernote/0.8.18/summernote-lite.min.js"></script>
    @stack('custom-script')
</body>

</html>
