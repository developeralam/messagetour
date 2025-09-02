<?php

use Livewire\Volt\Component;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;

new #[Layout('components.layouts.admin')] #[Title('Admin Dashboard')] class extends Component {
    //
}; ?>

<div>
    <x-header title="Dashboard" separator size="text-xl" class="bg-white px-2 pt-2">

        <x-slot:actions>
            <x-select icon="o-user" :options="[]" placeholder="Select A User" placeholder-value="0" />

        </x-slot:actions>
    </x-header>
    <div class="grid grid-cols-4 gap-2 p-4">
        <div class="border-t-2 border-orange-400 bg-white rounded shadow p-4 flex items-center space-x-2">
            <div class="bg-orange-400 rounded-sm p-1 inline-flex items-center justify-center">
                <x-fas-list class="text-white w-4 h-4" />
            </div>
            <a href="/admin/order/list" class="font-semibold">Order List</a>
        </div>

        <div class="border-t-2 border-blue-500 bg-white rounded shadow p-4 flex items-center space-x-2">
            <div class="bg-blue-500 rounded-sm p-1 inline-flex items-center justify-center">
                <x-fas-building class="text-white w-4 h-4" />
            </div>

            <a href="/admin/hotel/list" class="font-semibold">Hotel List</a>
        </div>

        <div class="border-t-2 border-blue-500 bg-white rounded shadow p-4 flex items-center space-x-2">
            <div class="bg-blue-500 rounded-sm p-1 inline-flex items-center justify-center">
                <x-fas-plus class="text-white w-4 h-4" />
            </div>
            <a href="/admin/hotel/create" class="font-semibold">Add Hotel</a>
        </div>

        <div class="border-t-2 border-cyan-500 bg-white rounded shadow p-4 flex items-center space-x-2">
            <div class="bg-cyan-500 rounded-sm p-1 inline-flex items-center justify-center">
                <x-fas-globe class="text-white w-4 h-4" />
            </div>
            <a href="/admin/tour/list" class="font-semibold">Tour Package List</a>
        </div>

        <div class="border-t-2 border-cyan-500 bg-white rounded shadow p-4 flex items-center space-x-2">
            <div class="bg-cyan-500 rounded-sm p-1 inline-flex items-center justify-center">
                <x-fas-plus class="text-white w-4 h-4" />
            </div>
            <a href="/admin/tour/create" class="font-semibold">Add Tour Package</a>
        </div>

        <div class="border-t-2 border-lime-500 bg-white rounded shadow p-4 flex items-center space-x-2">
            <div class="bg-lime-500 rounded-sm p-1 inline-flex items-center justify-center">
                <x-fas-p class="text-white w-4 h-4" />
            </div>
            <a href="/admin/travel-product/list" class="font-semibold">Travel Product List</a>
        </div>

        <div class="border-t-2 border-lime-400 bg-white rounded shadow p-4 flex items-center space-x-2">
            <div class="bg-lime-400 rounded-sm p-1 inline-flex items-center justify-center">
                <x-fas-plus class="text-white w-4 h-4" />
            </div>
            <a href="/admin/travel-product/create" class="font-semibold">Add Travel Product</a>
        </div>

        <div class="border-t-2 border-red-400 bg-white rounded shadow p-4 flex items-center space-x-2">
            <div class="bg-red-400 rounded-sm p-1 inline-flex items-center justify-center">
                <x-fab-cc-visa class="text-white w-4 h-4" />
            </div>
            <a href="/admin/visa/list" class="font-semibold">Visa List</a>
        </div>

        <div class="border-t-2 border-red-400 bg-white rounded shadow p-4 flex items-center space-x-2">
            <div class="bg-red-400 rounded-sm p-1 inline-flex items-center justify-center">
                <x-fas-plus class="text-white w-4 h-4" />
            </div>
            <a href="/admin/visa/create" class="font-semibold">Add Visa</a>
        </div>

        <div class="border-t-2 border-blue-500 bg-white rounded shadow p-4 flex items-center space-x-2">
            <div class="bg-blue-500 rounded-sm p-1 inline-flex items-center justify-center">
                <x-fas-plane class="text-white w-4 h-4" />
            </div>
            <a href="/admin/group-flight/list" class="font-semibold">Group Flight List</a>
        </div>

        <div class="border-t-2 border-blue-500 bg-white rounded shadow p-4 flex items-center space-x-2">
            <div class="bg-blue-500 rounded-sm p-1 inline-flex items-center justify-center">
                <x-fas-plus class="text-white w-4 h-4" />
            </div>

            <a href="/admin/group-flight/create" class="font-semibold">Add Group Flight</a>
        </div>

        <div class="border-t-2 border-green-500 bg-white rounded shadow p-4 flex items-center space-x-2">
            <div class="bg-green-500 rounded-sm p-1 inline-flex items-center justify-center">
                <x-fas-car class="text-white w-4 h-4" />
            </div>
            <a href="/admin/car/list" class="font-semibold">Car List</a>
        </div>

        <div class="border-t-2 border-green-500 bg-white rounded shadow p-4 flex items-center space-x-2">
            <div class="bg-green-500 rounded-sm p-1 inline-flex items-center justify-center">
                <x-fas-plus class="text-white w-4 h-4" />
            </div>
            <a href="/admin/car/create" class="font-semibold">Add Car</a>
        </div>

        <div class="border-t-2 border-purple-500 bg-white rounded shadow p-4 flex items-center space-x-2">
            <div class="bg-purple-500 rounded-sm p-1 inline-flex items-center justify-center">
                <x-fab-buffer class="text-white w-4 h-4" />
            </div>
            <a href="/admin/offer/list" class="font-semibold">Offer List</a>
        </div>

        <div class="border-t-2 border-purple-500 bg-white rounded shadow p-4 flex items-center space-x-2">
            <div class="bg-purple-500 rounded-sm p-1 inline-flex items-center justify-center">
                <x-fas-plus class="text-white w-4 h-4" />
            </div>
            <a href="/admin/offer/create" class="font-semibold">Add Offer</a>
        </div>

        <div class="border-t-2 border-yellow-400 bg-white rounded shadow p-4 flex items-center space-x-2">
            <div class="bg-yellow-400 rounded-sm p-1 inline-flex items-center justify-center">
                <x-fab-amazon-pay class="text-white w-4 h-4" />
            </div>
            <a href="/admin/payment-gateways" class="font-semibold">Payment Gateway</a>
        </div>

        <div class="border-t-2 border-blue-600 bg-white rounded shadow p-4 flex items-center space-x-2">
            <div class="bg-blue-600 rounded-sm p-1 inline-flex items-center justify-center">
                <x-fas-percent class="text-white w-4 h-4" />
            </div>
            <a href="/admin/coupon-codes" class="font-semibold">Coupon Code</a>
        </div>

        <div class="border-t-2 border-sky-700 bg-white rounded shadow p-4 flex items-center space-x-2">
            <div class="bg-sky-700 rounded-sm p-1 inline-flex items-center justify-center">
                <x-fas-user class="text-white w-4 h-4" />
            </div>
            <a href="/admin/profile" class="font-semibold">Profile</a>
        </div>
    </div>
</div>
