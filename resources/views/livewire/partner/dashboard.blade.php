<?php

use Livewire\Volt\Component;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;

new #[Layout('components.layouts.partner')] #[Title('Partner Dashboard')] class extends Component {
    //
}; ?>

<div>
    <x-header title="Dashboard" separator size="text-xl" class="bg-white px-2 pt-2"/>
    <div class="grid grid-cols-5 gap-2 p-4">
        <div class="border-t-2 border-orange-400 bg-white rounded shadow p-4 flex items-center space-x-2">
            <div class="bg-orange-400 rounded-sm p-1 inline-flex items-center justify-center">
                <x-fas-list class="text-white w-4 h-4" />
            </div>
            <a href="/partner/order/list" class="font-semibold">Order List</a>
        </div>

        <div class="border-t-2 border-blue-500 bg-white rounded shadow p-4 flex items-center space-x-2">
            <div class="bg-blue-500 rounded-sm p-1 inline-flex items-center justify-center">
                <x-fas-building class="text-white w-4 h-4" />
            </div>
            
            <a href="/partner/hotel/list" class="font-semibold">Hotel List</a>
        </div>

        <div class="border-t-2 border-green-500 bg-white rounded shadow p-4 flex items-center space-x-2">
            <div class="bg-green-500 rounded-sm p-1 inline-flex items-center justify-center">
                <x-fas-plus class="text-white w-4 h-4" />
            </div>
            <a href="/partner/hotel/create" class="font-semibold">Add Hotel</a>
        </div>

        <div class="border-t-2 border-purple-400 bg-white rounded shadow p-4 flex items-center space-x-2">
            <div class="bg-purple-400 rounded-sm p-1 inline-flex items-center justify-center">
                <x-fas-globe class="text-white w-4 h-4" />
            </div>
            <a href="/partner/tour/list" class="font-semibold">Tour Package List</a>
        </div>

        <div class="border-t-2 border-cyan-500 bg-white rounded shadow p-4 flex items-center space-x-2">
            <div class="bg-cyan-500 rounded-sm p-1 inline-flex items-center justify-center">
                <x-fas-plus class="text-white w-4 h-4" />
            </div>
            <a href="/partner/tour/create" class="font-semibold">Add Tour Package</a>
        </div>

        <div class="border-t-2 border-lime-500 bg-white rounded shadow p-4 flex items-center space-x-2">
            <div class="bg-lime-500 rounded-sm p-1 inline-flex items-center justify-center">
                <x-fas-p class="text-white w-4 h-4" />
            </div>
            <a href="/partner/travel-product/list" class="font-semibold">Travel Product List</a>
        </div>

        <div class="border-t-2 border-yellow-400 bg-white rounded shadow p-4 flex items-center space-x-2">
            <div class="bg-yellow-400 rounded-sm p-1 inline-flex items-center justify-center">
                <x-fas-plus class="text-white w-4 h-4" />
            </div>
            <a href="/partner/travel-product/create" class="font-semibold">Add Travel Product</a>
        </div>

        <div class="border-t-2 border-blue-600 bg-white rounded shadow p-4 flex items-center space-x-2">
            <div class="bg-blue-600 rounded-sm p-1 inline-flex items-center justify-center">
                <x-fas-list class="text-white w-4 h-4" />
            </div>
            <a href="/partner/markup" class="font-semibold">Markup</a>
        </div>

        <div class="border-t-2 border-red-400 bg-white rounded shadow p-4 flex items-center space-x-2">
            <div class="bg-red-400 rounded-sm p-1 inline-flex items-center justify-center">
                <x-fas-money-bill class="text-white w-4 h-4" />
            </div>
            <a href="/partner/wallet" class="font-semibold">Wallet</a>
        </div>

        <div class="border-t-2 border-sky-700 bg-white rounded shadow p-4 flex items-center space-x-2">
            <div class="bg-sky-700 rounded-sm p-1 inline-flex items-center justify-center">
                <x-fas-user class="text-white w-4 h-4" />
            </div>
            <a href="/partner/profile" class="font-semibold">Profile</a>
        </div>
    </div>

</div>
