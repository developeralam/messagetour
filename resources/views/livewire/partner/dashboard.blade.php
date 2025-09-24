<?php

use App\Models\Order;
use App\Models\Hotel;
use App\Models\Tour;
use App\Models\TravelProduct;
use App\Models\Agent;
use App\Models\Deposit;
use App\Models\Withdraw;
use App\Models\CorporateQuery;
use App\Models\HotelRoomBooking;
use App\Models\TourBooking;
use App\Models\TravelProductBooking;
use App\Enum\OrderStatus;
use App\Enum\PaymentStatus;
use App\Enum\HotelStatus;
use App\Enum\TourStatus;
use App\Enum\AgentType;
use Livewire\Volt\Component;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Computed;

new #[Layout('components.layouts.partner')] #[Title('Partner Dashboard')] class extends Component {
    #[Computed]
    public function partnerStats()
    {
        $currentMonth = now()->month;
        $currentYear = now()->year;
        $lastMonth = now()->subMonth();
        $agentId = auth()->user()->agent->id;

        return [
            // Partner Orders (orders for partner's products)
            'total_orders' => $this->getPartnerOrders()->count(),
            'pending_orders' => $this->getPartnerOrders()->where('status', OrderStatus::Pending)->count(),
            'confirmed_orders' => $this->getPartnerOrders()->where('status', OrderStatus::Confirmed)->count(),
            'cancelled_orders' => $this->getPartnerOrders()->where('status', OrderStatus::Cancelled)->count(),
            'today_orders' => $this->getPartnerOrders()->whereDate('created_at', today())->count(),
            'monthly_orders' => $this->getPartnerOrders()->whereMonth('created_at', $currentMonth)->whereYear('created_at', $currentYear)->count(),

            // Partner Revenue
            'total_revenue' => $this->getPartnerOrders()->where('payment_status', PaymentStatus::Paid)->sum('total_amount'),
            'monthly_revenue' => $this->getPartnerOrders()->where('payment_status', PaymentStatus::Paid)->whereMonth('created_at', $currentMonth)->whereYear('created_at', $currentYear)->sum('total_amount'),
            'today_revenue' => $this->getPartnerOrders()->where('payment_status', PaymentStatus::Paid)->whereDate('created_at', today())->sum('total_amount'),
            'average_order_value' => $this->getPartnerOrders()->where('payment_status', PaymentStatus::Paid)->avg('total_amount'),

            // Partner Products
            'total_hotels' => Hotel::where('created_by', auth()->id())->count(),
            'active_hotels' => Hotel::where('created_by', auth()->id())
                ->where('status', HotelStatus::Active)
                ->count(),
            'total_tours' => Tour::where('created_by', auth()->id())->count(),
            'active_tours' => Tour::where('created_by', auth()->id())
                ->where('status', TourStatus::Active)
                ->count(),
            'total_products' => TravelProduct::where('created_by', auth()->id())->count(),

            // Partner Wallet
            'wallet_balance' => auth()->user()->agent->wallet ?? 0,
            'total_deposits' => Deposit::where('agent_id', $agentId)->sum('amount'),
            'total_withdrawals' => Withdraw::where('agent_id', $agentId)->sum('amount'),
            'pending_withdrawals' => Withdraw::where('agent_id', $agentId)->where('status', 'pending')->sum('amount'),

            // Corporate Queries (all queries - partners can see all)
            'total_queries' => CorporateQuery::count(),
            'pending_queries' => CorporateQuery::where('status', 'pending')->count(),
            'completed_queries' => CorporateQuery::where('status', 'completed')->count(),

            // Growth Rates
            'revenue_growth' => $this->calculateGrowthRate($this->getPartnerOrders()->where('payment_status', PaymentStatus::Paid)->whereMonth('created_at', $lastMonth->month)->whereYear('created_at', $lastMonth->year)->sum('total_amount'), $this->getPartnerOrders()->where('payment_status', PaymentStatus::Paid)->whereMonth('created_at', $currentMonth)->whereYear('created_at', $currentYear)->sum('total_amount')),
            'order_growth' => $this->calculateGrowthRate($this->getPartnerOrders()->whereMonth('created_at', $lastMonth->month)->whereYear('created_at', $lastMonth->year)->count(), $this->getPartnerOrders()->whereMonth('created_at', $currentMonth)->whereYear('created_at', $currentYear)->count()),
        ];
    }

    private function getPartnerOrders()
    {
        $userId = auth()->id();
        return Order::whereHas('sourceable', function ($query) use ($userId) {
            $query->where('created_by', $userId);
        });
    }

    private function calculateGrowthRate($oldValue, $newValue)
    {
        if ($oldValue == 0) {
            return $newValue > 0 ? 100 : 0;
        }
        return round((($newValue - $oldValue) / $oldValue) * 100, 1);
    }

    #[Computed]
    public function recentOrders()
    {
        return $this->getPartnerOrders()
            ->with(['user', 'sourceable'])
            ->latest()
            ->take(6)
            ->get();
    }

    #[Computed]
    public function topCustomers()
    {
        return $this->getPartnerOrders()
            ->with('user')
            ->get()
            ->groupBy('user_id')
            ->map(function ($orders, $userId) {
                $user = $orders->first()->user;
                return (object) [
                    'user' => $user,
                    'orders_count' => $orders->count(),
                    'total_spent' => $orders->sum('total_amount'),
                ];
            })
            ->sortByDesc('orders_count')
            ->take(5)
            ->values();
    }

    #[Computed]
    public function monthlyRevenueData()
    {
        $months = [];
        $revenues = [];

        for ($i = 5; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $months[] = $date->format('M');
            $revenues[] = $this->getPartnerOrders()->where('payment_status', PaymentStatus::Paid)->whereMonth('created_at', $date->month)->whereYear('created_at', $date->year)->sum('total_amount');
        }

        return ['months' => $months, 'revenues' => $revenues];
    }

    #[Computed]
    public function orderStatusData()
    {
        return [
            'pending' => $this->getPartnerOrders()->where('status', OrderStatus::Pending)->count(),
            'confirmed' => $this->getPartnerOrders()->where('status', OrderStatus::Confirmed)->count(),
            'cancelled' => $this->getPartnerOrders()->where('status', OrderStatus::Cancelled)->count(),
        ];
    }

    #[Computed]
    public function agentInfo()
    {
        return auth()->user()->agent;
    }
}; ?>

<div class="min-h-screen bg-gradient-to-br from-slate-50 via-blue-50 to-green-50">
    <!-- Compact Header -->
    <div class="bg-white/80 backdrop-blur-sm shadow-lg border-b border-gray-200/50">
        <div class="px-4 py-3">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-xl font-bold text-gray-900">Partner Dashboard</h1>
                    <p class="text-xs text-gray-600">{{ $this->agentInfo->business_name ?? auth()->user()->name }} • {{ now()->format('l, F d, Y') }}
                    </p>
                </div>
                <div class="flex items-center space-x-4 text-sm">
                    <div class="text-right">
                        <p class="text-gray-500">Agent Type</p>
                        <p class="font-medium text-gray-900">{{ $this->agentInfo->agent_type->label() }}</p>
                    </div>
                    <div class="text-right">
                        <p class="text-gray-500">Last updated</p>
                        <p class="font-medium text-gray-900">{{ now()->format('H:i') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="p-4 space-y-4">
        <!-- Primary Metrics - Compact Cards -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
            <!-- Total Revenue -->
            <div
                class="bg-gradient-to-br from-green-500 to-green-600 rounded-xl shadow-xl p-4 text-white transform hover:scale-105 transition-all duration-300 hover:shadow-2xl">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-green-100 text-xs font-medium">Total Revenue</p>
                        <p class="text-2xl font-bold">৳{{ number_format($this->partnerStats['total_revenue']) }}</p>
                        <div class="flex items-center mt-1">
                            <span class="text-xs {{ $this->partnerStats['revenue_growth'] >= 0 ? 'text-green-200' : 'text-red-200' }}">
                                {{ $this->partnerStats['revenue_growth'] >= 0 ? '↗' : '↘' }} {{ abs($this->partnerStats['revenue_growth']) }}%
                            </span>
                        </div>
                    </div>
                    <div class="bg-green-400/30 rounded-lg p-2">
                        <x-fas-chart-line class="w-5 h-5" />
                    </div>
                </div>
            </div>

            <!-- Monthly Revenue -->
            <div
                class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl shadow-xl p-4 text-white transform hover:scale-105 transition-all duration-300 hover:shadow-2xl">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-blue-100 text-xs font-medium">This Month</p>
                        <p class="text-2xl font-bold">৳{{ number_format($this->partnerStats['monthly_revenue']) }}</p>
                        <p class="text-blue-100 text-xs mt-1">{{ now()->format('M Y') }}</p>
                    </div>
                    <div class="bg-blue-400/30 rounded-lg p-2">
                        <x-fas-calendar class="w-5 h-5" />
                    </div>
                </div>
            </div>

            <!-- Total Orders -->
            <div
                class="bg-gradient-to-br from-yellow-500 to-yellow-600 rounded-xl shadow-xl p-4 text-white transform hover:scale-105 transition-all duration-300 hover:shadow-2xl">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-yellow-100 text-xs font-medium">Total Orders</p>
                        <p class="text-2xl font-bold">{{ number_format($this->partnerStats['total_orders']) }}</p>
                        <div class="flex items-center mt-1">
                            <span class="text-xs {{ $this->partnerStats['order_growth'] >= 0 ? 'text-yellow-200' : 'text-red-200' }}">
                                {{ $this->partnerStats['order_growth'] >= 0 ? '↗' : '↘' }} {{ abs($this->partnerStats['order_growth']) }}%
                            </span>
                        </div>
                    </div>
                    <div class="bg-yellow-400/30 rounded-lg p-2">
                        <x-fas-shopping-cart class="w-5 h-5" />
                    </div>
                </div>
            </div>

            <!-- Wallet Balance -->
            <div
                class="bg-gradient-to-br from-purple-500 to-purple-600 rounded-xl shadow-xl p-4 text-white transform hover:scale-105 transition-all duration-300 hover:shadow-2xl">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-purple-100 text-xs font-medium">Wallet Balance</p>
                        <p class="text-2xl font-bold">৳{{ number_format($this->partnerStats['wallet_balance']) }}</p>
                        <p class="text-purple-100 text-xs mt-1">Available</p>
                    </div>
                    <div class="bg-purple-400/30 rounded-lg p-2">
                        <x-fas-wallet class="w-5 h-5" />
                    </div>
                </div>
            </div>
        </div>

        <!-- Secondary Metrics - Ultra Compact -->
        <div class="grid grid-cols-3 md:grid-cols-6 lg:grid-cols-12 gap-2">
            <!-- Today's Orders -->
            <div class="bg-white rounded-lg shadow-md p-3 border-l-4 border-orange-400 hover:shadow-lg transition-all duration-300">
                <div class="text-center">
                    <div class="bg-orange-100 rounded-full p-2 w-8 h-8 mx-auto mb-2 flex items-center justify-center">
                        <x-fas-clock class="w-3 h-3 text-orange-600" />
                    </div>
                    <p class="text-lg font-bold text-gray-900">{{ $this->partnerStats['today_orders'] }}</p>
                    <p class="text-xs text-gray-600">Today</p>
                </div>
            </div>

            <!-- Pending Orders -->
            <div class="bg-white rounded-lg shadow-md p-3 border-l-4 border-yellow-400 hover:shadow-lg transition-all duration-300">
                <div class="text-center">
                    <div class="bg-yellow-100 rounded-full p-2 w-8 h-8 mx-auto mb-2 flex items-center justify-center">
                        <x-fas-hourglass-half class="w-3 h-3 text-yellow-600" />
                    </div>
                    <p class="text-lg font-bold text-gray-900">{{ $this->partnerStats['pending_orders'] }}</p>
                    <p class="text-xs text-gray-600">Pending</p>
                </div>
            </div>

            <!-- Hotels -->
            <div class="bg-white rounded-lg shadow-md p-3 border-l-4 border-green-400 hover:shadow-lg transition-all duration-300">
                <div class="text-center">
                    <div class="bg-green-100 rounded-full p-2 w-8 h-8 mx-auto mb-2 flex items-center justify-center">
                        <x-fas-building class="w-3 h-3 text-green-600" />
                    </div>
                    <p class="text-lg font-bold text-gray-900">{{ $this->partnerStats['total_hotels'] }}</p>
                    <p class="text-xs text-gray-600">Hotels</p>
                </div>
            </div>

            <!-- Tours -->
            <div class="bg-white rounded-lg shadow-md p-3 border-l-4 border-cyan-400 hover:shadow-lg transition-all duration-300">
                <div class="text-center">
                    <div class="bg-cyan-100 rounded-full p-2 w-8 h-8 mx-auto mb-2 flex items-center justify-center">
                        <x-fas-globe class="w-3 h-3 text-cyan-600" />
                    </div>
                    <p class="text-lg font-bold text-gray-900">{{ $this->partnerStats['total_tours'] }}</p>
                    <p class="text-xs text-gray-600">Tours</p>
                </div>
            </div>

            <!-- Products -->
            <div class="bg-white rounded-lg shadow-md p-3 border-l-4 border-teal-400 hover:shadow-lg transition-all duration-300">
                <div class="text-center">
                    <div class="bg-teal-100 rounded-full p-2 w-8 h-8 mx-auto mb-2 flex items-center justify-center">
                        <x-fas-box class="w-3 h-3 text-teal-600" />
                    </div>
                    <p class="text-lg font-bold text-gray-900">{{ $this->partnerStats['total_products'] }}</p>
                    <p class="text-xs text-gray-600">Products</p>
                </div>
            </div>

            <!-- Queries -->
            <div class="bg-white rounded-lg shadow-md p-3 border-l-4 border-indigo-400 hover:shadow-lg transition-all duration-300">
                <div class="text-center">
                    <div class="bg-indigo-100 rounded-full p-2 w-8 h-8 mx-auto mb-2 flex items-center justify-center">
                        <x-fas-question-circle class="w-3 h-3 text-indigo-600" />
                    </div>
                    <p class="text-lg font-bold text-gray-900">{{ $this->partnerStats['total_queries'] }}</p>
                    <p class="text-xs text-gray-600">Queries</p>
                </div>
            </div>

            <!-- Deposits -->
            <div class="bg-white rounded-lg shadow-md p-3 border-l-4 border-emerald-400 hover:shadow-lg transition-all duration-300">
                <div class="text-center">
                    <div class="bg-emerald-100 rounded-full p-2 w-8 h-8 mx-auto mb-2 flex items-center justify-center">
                        <x-fas-arrow-up class="w-3 h-3 text-emerald-600" />
                    </div>
                    <p class="text-lg font-bold text-gray-900">৳{{ number_format($this->partnerStats['total_deposits']) }}</p>
                    <p class="text-xs text-gray-600">Deposits</p>
                </div>
            </div>

            <!-- Withdrawals -->
            <div class="bg-white rounded-lg shadow-md p-3 border-l-4 border-red-400 hover:shadow-lg transition-all duration-300">
                <div class="text-center">
                    <div class="bg-red-100 rounded-full p-2 w-8 h-8 mx-auto mb-2 flex items-center justify-center">
                        <x-fas-arrow-down class="w-3 h-3 text-red-600" />
                    </div>
                    <p class="text-lg font-bold text-gray-900">৳{{ number_format($this->partnerStats['total_withdrawals']) }}</p>
                    <p class="text-xs text-gray-600">Withdrawals</p>
                </div>
            </div>

            <!-- Active Hotels -->
            <div class="bg-white rounded-lg shadow-md p-3 border-l-4 border-green-400 hover:shadow-lg transition-all duration-300">
                <div class="text-center">
                    <div class="bg-green-100 rounded-full p-2 w-8 h-8 mx-auto mb-2 flex items-center justify-center">
                        <x-fas-check-circle class="w-3 h-3 text-green-600" />
                    </div>
                    <p class="text-lg font-bold text-gray-900">{{ $this->partnerStats['active_hotels'] }}</p>
                    <p class="text-xs text-gray-600">Active Hotels</p>
                </div>
            </div>

            <!-- Active Tours -->
            <div class="bg-white rounded-lg shadow-md p-3 border-l-4 border-cyan-400 hover:shadow-lg transition-all duration-300">
                <div class="text-center">
                    <div class="bg-cyan-100 rounded-full p-2 w-8 h-8 mx-auto mb-2 flex items-center justify-center">
                        <x-fas-check-circle class="w-3 h-3 text-cyan-600" />
                    </div>
                    <p class="text-lg font-bold text-gray-900">{{ $this->partnerStats['active_tours'] }}</p>
                    <p class="text-xs text-gray-600">Active Tours</p>
                </div>
            </div>

            <!-- Pending Withdrawals -->
            <div class="bg-white rounded-lg shadow-md p-3 border-l-4 border-orange-400 hover:shadow-lg transition-all duration-300">
                <div class="text-center">
                    <div class="bg-orange-100 rounded-full p-2 w-8 h-8 mx-auto mb-2 flex items-center justify-center">
                        <x-fas-clock class="w-3 h-3 text-orange-600" />
                    </div>
                    <p class="text-lg font-bold text-gray-900">৳{{ number_format($this->partnerStats['pending_withdrawals']) }}</p>
                    <p class="text-xs text-gray-600">Pending WD</p>
                </div>
            </div>

            <!-- Average Order -->
            <div class="bg-white rounded-lg shadow-md p-3 border-l-4 border-purple-400 hover:shadow-lg transition-all duration-300">
                <div class="text-center">
                    <div class="bg-purple-100 rounded-full p-2 w-8 h-8 mx-auto mb-2 flex items-center justify-center">
                        <x-fas-calculator class="w-3 h-3 text-purple-600" />
                    </div>
                    <p class="text-lg font-bold text-gray-900">৳{{ number_format($this->partnerStats['average_order_value']) }}</p>
                    <p class="text-xs text-gray-600">Avg Order</p>
                </div>
            </div>
        </div>

        <!-- Charts and Data - Compact Layout -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
            <!-- Revenue Chart -->
            <div class="lg:col-span-2 bg-white rounded-xl shadow-xl p-4 hover:shadow-2xl transition-all duration-300">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">Revenue Trend</h3>
                    <div class="flex items-center space-x-2">
                        <div class="w-2 h-2 bg-green-500 rounded-full"></div>
                        <span class="text-sm text-gray-600">6 Months</span>
                    </div>
                </div>
                <div class="h-48 flex items-end justify-between space-x-1">
                    @foreach ($this->monthlyRevenueData['revenues'] as $index => $revenue)
                        <div class="flex flex-col items-center space-y-2 flex-1">
                            <div class="bg-gradient-to-t from-green-500 to-green-400 rounded-t-lg transition-all duration-500 hover:from-green-600 hover:to-green-500 w-full"
                                style="height: {{ $revenue > 0 ? max(20, ($revenue / max($this->monthlyRevenueData['revenues'])) * 180) : 20 }}px;"
                                title="৳{{ number_format($revenue) }}">
                            </div>
                            <span class="text-xs text-gray-600 font-medium">{{ $this->monthlyRevenueData['months'][$index] }}</span>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Order Status Chart -->
            <div class="bg-white rounded-xl shadow-xl p-4 hover:shadow-2xl transition-all duration-300">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Order Status</h3>
                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-2">
                            <div class="w-3 h-3 bg-yellow-500 rounded-full"></div>
                            <span class="text-sm text-gray-600">Pending</span>
                        </div>
                        <span class="font-semibold text-gray-900">{{ $this->orderStatusData['pending'] }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-2">
                            <div class="w-3 h-3 bg-green-500 rounded-full"></div>
                            <span class="text-sm text-gray-600">Confirmed</span>
                        </div>
                        <span class="font-semibold text-gray-900">{{ $this->orderStatusData['confirmed'] }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-2">
                            <div class="w-3 h-3 bg-red-500 rounded-full"></div>
                            <span class="text-sm text-gray-600">Cancelled</span>
                        </div>
                        <span class="font-semibold text-gray-900">{{ $this->orderStatusData['cancelled'] }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Activity - Compact -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
            <!-- Recent Orders -->
            <div class="bg-white rounded-xl shadow-xl p-4 hover:shadow-2xl transition-all duration-300">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">Recent Orders</h3>
                    <a href="/partner/order/list" class="text-blue-600 hover:text-blue-800 text-sm font-medium">View All</a>
                </div>
                <div class="space-y-3">
                    @forelse($this->recentOrders as $order)
                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors">
                            <div class="flex items-center space-x-3">
                                <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                                    <x-fas-shopping-cart class="w-3 h-3 text-blue-600" />
                                </div>
                                <div>
                                    <p class="font-medium text-gray-900 text-sm">#{{ $order->id }}</p>
                                    <p class="text-xs text-gray-600">{{ $order->user->name ?? 'Guest' }}</p>
                                </div>
                            </div>
                            <div class="text-right">
                                <p class="font-semibold text-gray-900 text-sm">৳{{ number_format($order->total_amount) }}</p>
                                <span
                                    class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium
                                    @if ($order->status->value === 'confirmed') bg-green-100 text-green-800
                                    @elseif($order->status->value === 'pending') bg-yellow-100 text-yellow-800
                                    @else bg-gray-100 text-gray-800 @endif">
                                    {{ ucfirst($order->status->value) }}
                                </span>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-6 text-gray-500">
                            <x-fas-shopping-cart class="w-8 h-8 mx-auto mb-2 text-gray-300" />
                            <p class="text-sm">No recent orders</p>
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- Top Customers -->
            <div class="bg-white rounded-xl shadow-xl p-4 hover:shadow-2xl transition-all duration-300">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">Top Customers</h3>
                    <a href="/partner/order/list" class="text-blue-600 hover:text-blue-800 text-sm font-medium">View All</a>
                </div>
                <div class="space-y-3">
                    @forelse($this->topCustomers as $customer)
                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors">
                            <div class="flex items-center space-x-3">
                                <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center">
                                    <x-fas-user class="w-3 h-3 text-green-600" />
                                </div>
                                <div>
                                    <p class="font-medium text-gray-900 text-sm">{{ $customer->user->name ?? 'N/A' }}</p>
                                    <p class="text-xs text-gray-600">{{ $customer->user->email ?? 'N/A' }}</p>
                                </div>
                            </div>
                            <div class="text-right">
                                <p class="font-semibold text-gray-900 text-sm">{{ $customer->orders_count }}</p>
                                <p class="text-xs text-gray-600">orders</p>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-6 text-gray-500">
                            <x-fas-users class="w-8 h-8 mx-auto mb-2 text-gray-300" />
                            <p class="text-sm">No customers yet</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Quick Actions - Ultra Compact -->
        <div class="bg-white rounded-xl shadow-xl p-4 hover:shadow-2xl transition-all duration-300">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Quick Actions</h3>
            <div class="grid grid-cols-3 md:grid-cols-6 lg:grid-cols-12 gap-3">
                <a href="/partner/order/list"
                    class="group flex flex-col items-center p-3 bg-gradient-to-br from-blue-50 to-blue-100 rounded-lg hover:from-blue-100 hover:to-blue-200 transition-all duration-300 transform hover:scale-105">
                    <div class="w-10 h-10 bg-blue-500 rounded-lg flex items-center justify-center mb-2 group-hover:bg-blue-600 transition-colors">
                        <x-fas-list class="w-4 h-4 text-white" />
                    </div>
                    <span class="text-xs font-medium text-gray-900 text-center">Orders</span>
                </a>

                @if (auth()->user()->agent->agent_type == AgentType::General)
                    <a href="/partner/hotel/create"
                        class="group flex flex-col items-center p-3 bg-gradient-to-br from-green-50 to-green-100 rounded-lg hover:from-green-100 hover:to-green-200 transition-all duration-300 transform hover:scale-105">
                        <div
                            class="w-10 h-10 bg-green-500 rounded-lg flex items-center justify-center mb-2 group-hover:bg-green-600 transition-colors">
                            <x-fas-plus class="w-4 h-4 text-white" />
                        </div>
                        <span class="text-xs font-medium text-gray-900 text-center">Add Hotel</span>
                    </a>

                    <a href="/partner/tour/create"
                        class="group flex flex-col items-center p-3 bg-gradient-to-br from-yellow-50 to-yellow-100 rounded-lg hover:from-yellow-100 hover:to-yellow-200 transition-all duration-300 transform hover:scale-105">
                        <div
                            class="w-10 h-10 bg-yellow-500 rounded-lg flex items-center justify-center mb-2 group-hover:bg-yellow-600 transition-colors">
                            <x-fas-plus class="w-4 h-4 text-white" />
                        </div>
                        <span class="text-xs font-medium text-gray-900 text-center">Add Tour</span>
                    </a>
                @endif

                <a href="/partner/wallet"
                    class="group flex flex-col items-center p-3 bg-gradient-to-br from-purple-50 to-purple-100 rounded-lg hover:from-purple-100 hover:to-purple-200 transition-all duration-300 transform hover:scale-105">
                    <div
                        class="w-10 h-10 bg-purple-500 rounded-lg flex items-center justify-center mb-2 group-hover:bg-purple-600 transition-colors">
                        <x-fas-wallet class="w-4 h-4 text-white" />
                    </div>
                    <span class="text-xs font-medium text-gray-900 text-center">Wallet</span>
                </a>

                <a href="/partner/deposit-request"
                    class="group flex flex-col items-center p-3 bg-gradient-to-br from-emerald-50 to-emerald-100 rounded-lg hover:from-emerald-100 hover:to-emerald-200 transition-all duration-300 transform hover:scale-105">
                    <div
                        class="w-10 h-10 bg-emerald-500 rounded-lg flex items-center justify-center mb-2 group-hover:bg-emerald-600 transition-colors">
                        <x-fas-arrow-up class="w-4 h-4 text-white" />
                    </div>
                    <span class="text-xs font-medium text-gray-900 text-center">Deposit</span>
                </a>

                <a href="/partner/markup"
                    class="group flex flex-col items-center p-3 bg-gradient-to-br from-orange-50 to-orange-100 rounded-lg hover:from-orange-100 hover:to-orange-200 transition-all duration-300 transform hover:scale-105">
                    <div
                        class="w-10 h-10 bg-orange-500 rounded-lg flex items-center justify-center mb-2 group-hover:bg-orange-600 transition-colors">
                        <x-fas-percent class="w-4 h-4 text-white" />
                    </div>
                    <span class="text-xs font-medium text-gray-900 text-center">Markup</span>
                </a>

                @if (auth()->user()->agent->agent_type == AgentType::General)
                    <a href="/partner/hotel/list"
                        class="group flex flex-col items-center p-3 bg-gradient-to-br from-green-50 to-green-100 rounded-lg hover:from-green-100 hover:to-green-200 transition-all duration-300 transform hover:scale-105">
                        <div
                            class="w-10 h-10 bg-green-500 rounded-lg flex items-center justify-center mb-2 group-hover:bg-green-600 transition-colors">
                            <x-fas-building class="w-4 h-4 text-white" />
                        </div>
                        <span class="text-xs font-medium text-gray-900 text-center">Hotels</span>
                    </a>

                    <a href="/partner/tour/list"
                        class="group flex flex-col items-center p-3 bg-gradient-to-br from-yellow-50 to-yellow-100 rounded-lg hover:from-yellow-100 hover:to-yellow-200 transition-all duration-300 transform hover:scale-105">
                        <div
                            class="w-10 h-10 bg-yellow-500 rounded-lg flex items-center justify-center mb-2 group-hover:bg-yellow-600 transition-colors">
                            <x-fas-globe class="w-4 h-4 text-white" />
                        </div>
                        <span class="text-xs font-medium text-gray-900 text-center">Tours</span>
                    </a>
                @endif

                <a href="/partner/booking/list"
                    class="group flex flex-col items-center p-3 bg-gradient-to-br from-cyan-50 to-cyan-100 rounded-lg hover:from-cyan-100 hover:to-cyan-200 transition-all duration-300 transform hover:scale-105">
                    <div class="w-10 h-10 bg-cyan-500 rounded-lg flex items-center justify-center mb-2 group-hover:bg-cyan-600 transition-colors">
                        <x-fas-calendar-check class="w-4 h-4 text-white" />
                    </div>
                    <span class="text-xs font-medium text-gray-900 text-center">Bookings</span>
                </a>

                <a href="/partner/query/list"
                    class="group flex flex-col items-center p-3 bg-gradient-to-br from-indigo-50 to-indigo-100 rounded-lg hover:from-indigo-100 hover:to-indigo-200 transition-all duration-300 transform hover:scale-105">
                    <div
                        class="w-10 h-10 bg-indigo-500 rounded-lg flex items-center justify-center mb-2 group-hover:bg-indigo-600 transition-colors">
                        <x-fas-question-circle class="w-4 h-4 text-white" />
                    </div>
                    <span class="text-xs font-medium text-gray-900 text-center">Queries</span>
                </a>

                <a href="/partner/profile"
                    class="group flex flex-col items-center p-3 bg-gradient-to-br from-gray-50 to-gray-100 rounded-lg hover:from-gray-100 hover:to-gray-200 transition-all duration-300 transform hover:scale-105">
                    <div class="w-10 h-10 bg-gray-500 rounded-lg flex items-center justify-center mb-2 group-hover:bg-gray-600 transition-colors">
                        <x-fas-user class="w-4 h-4 text-white" />
                    </div>
                    <span class="text-xs font-medium text-gray-900 text-center">Profile</span>
                </a>
            </div>
        </div>
    </div>
</div>
