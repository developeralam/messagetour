<?php

use App\Models\Order;
use App\Models\Customer;
use App\Models\Hotel;
use App\Models\Tour;
use App\Models\GroupFlight;
use App\Models\Car;
use App\Models\Offer;
use App\Models\Visa;
use App\Models\TravelProduct;
use App\Models\Income;
use App\Models\Expense;
use App\Models\ChartOfAccount;
use App\Enum\OrderStatus;
use App\Enum\PaymentStatus;
use App\Enum\HotelStatus;
use App\Enum\TourStatus;
use App\Enum\OfferStatus;
use Livewire\Volt\Component;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Computed;

new #[Layout('components.layouts.admin')] #[Title('Admin Dashboard')] class extends Component {
    #[Computed]
    public function dashboardStats()
    {
        $currentMonth = now()->month;
        $currentYear = now()->year;
        $lastMonth = now()->subMonth();

        return [
            // Orders
            'total_orders' => Order::count(),
            'pending_orders' => Order::where('status', OrderStatus::Pending)->count(),
            'confirmed_orders' => Order::where('status', OrderStatus::Confirmed)->count(),
            'cancelled_orders' => Order::where('status', OrderStatus::Cancelled)->count(),
            'today_orders' => Order::whereDate('created_at', today())->count(),
            'monthly_orders' => Order::whereMonth('created_at', $currentMonth)->whereYear('created_at', $currentYear)->count(),
            'last_month_orders' => Order::whereMonth('created_at', $lastMonth->month)->whereYear('created_at', $lastMonth->year)->count(),

            // Revenue
            'total_revenue' => Order::where('payment_status', PaymentStatus::Paid)->sum('total_amount'),
            'monthly_revenue' => Order::where('payment_status', PaymentStatus::Paid)->whereMonth('created_at', $currentMonth)->whereYear('created_at', $currentYear)->sum('total_amount'),
            'last_month_revenue' => Order::where('payment_status', PaymentStatus::Paid)->whereMonth('created_at', $lastMonth->month)->whereYear('created_at', $lastMonth->year)->sum('total_amount'),
            'today_revenue' => Order::where('payment_status', PaymentStatus::Paid)->whereDate('created_at', today())->sum('total_amount'),
            'average_order_value' => Order::where('payment_status', PaymentStatus::Paid)->avg('total_amount'),

            // Customers
            'total_customers' => Customer::count(),
            'new_customers_this_month' => Customer::whereMonth('created_at', $currentMonth)->whereYear('created_at', $currentYear)->count(),
            'active_customers' => Customer::whereHas('user', function ($query) {
                $query->whereHas('orders');
            })->count(),

            // Products & Services
            'total_hotels' => Hotel::count(),
            'active_hotels' => Hotel::where('status', HotelStatus::Active)->count(),
            'total_tours' => Tour::count(),
            'active_tours' => Tour::where('status', TourStatus::Active)->count(),
            'total_flights' => GroupFlight::count(),
            'total_cars' => Car::count(),
            'total_offers' => Offer::count(),
            'active_offers' => Offer::where('status', OfferStatus::Active)->count(),
            'total_visas' => Visa::count(),
            'total_products' => TravelProduct::count(),

            // Financial
            'total_income' => Income::sum('amount'),
            'monthly_income' => Income::whereMonth('created_at', $currentMonth)->whereYear('created_at', $currentYear)->sum('amount'),
            'total_expense' => Expense::sum('amount'),
            'monthly_expense' => Expense::whereMonth('created_at', $currentMonth)->whereYear('created_at', $currentYear)->sum('amount'),
            'net_profit' => Income::sum('amount') - Expense::sum('amount'),
            'monthly_profit' => Income::whereMonth('created_at', $currentMonth)->whereYear('created_at', $currentYear)->sum('amount') - Expense::whereMonth('created_at', $currentMonth)->whereYear('created_at', $currentYear)->sum('amount'),

            // Growth Rates
            'revenue_growth' => $this->calculateGrowthRate(Order::where('payment_status', PaymentStatus::Paid)->whereMonth('created_at', $lastMonth->month)->whereYear('created_at', $lastMonth->year)->sum('total_amount'), Order::where('payment_status', PaymentStatus::Paid)->whereMonth('created_at', $currentMonth)->whereYear('created_at', $currentYear)->sum('total_amount')),
            'order_growth' => $this->calculateGrowthRate(Order::whereMonth('created_at', $lastMonth->month)->whereYear('created_at', $lastMonth->year)->count(), Order::whereMonth('created_at', $currentMonth)->whereYear('created_at', $currentYear)->count()),
        ];
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
        return Order::with(['user', 'sourceable'])
            ->latest()
            ->take(6)
            ->get();
    }

    #[Computed]
    public function topCustomers()
    {
        return Customer::with(['user'])
            ->whereHas('user.orders')
            ->get()
            ->map(function ($customer) {
                $customer->orders_count = $customer->user->orders()->count();
                return $customer;
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
            $revenues[] = Order::where('payment_status', PaymentStatus::Paid)->whereMonth('created_at', $date->month)->whereYear('created_at', $date->year)->sum('total_amount');
        }

        return ['months' => $months, 'revenues' => $revenues];
    }

    #[Computed]
    public function orderStatusData()
    {
        return [
            'pending' => Order::where('status', OrderStatus::Pending)->count(),
            'confirmed' => Order::where('status', OrderStatus::Confirmed)->count(),
            'cancelled' => Order::where('status', OrderStatus::Cancelled)->count(),
        ];
    }
}; ?>

<div class="min-h-screen bg-gradient-to-br from-slate-50 via-blue-50 to-green-50">
    <!-- Compact Header -->
    <div class="bg-white/80 backdrop-blur-sm shadow-lg border-b border-gray-200/50">
        <div class="px-4 py-3">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-xl font-bold text-gray-900">Dashboard</h1>
                    <p class="text-xs text-gray-600">{{ now()->format('l, F d, Y') }}</p>
                </div>
                <div class="flex items-center space-x-4 text-sm">
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
                        <p class="text-2xl font-bold">৳{{ number_format($this->dashboardStats['total_revenue']) }}</p>
                        <div class="flex items-center mt-1">
                            <span class="text-xs {{ $this->dashboardStats['revenue_growth'] >= 0 ? 'text-green-200' : 'text-red-200' }}">
                                {{ $this->dashboardStats['revenue_growth'] >= 0 ? '↗' : '↘' }} {{ abs($this->dashboardStats['revenue_growth']) }}%
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
                        <p class="text-2xl font-bold">৳{{ number_format($this->dashboardStats['monthly_revenue']) }}</p>
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
                        <p class="text-2xl font-bold">{{ number_format($this->dashboardStats['total_orders']) }}</p>
                        <div class="flex items-center mt-1">
                            <span class="text-xs {{ $this->dashboardStats['order_growth'] >= 0 ? 'text-yellow-200' : 'text-red-200' }}">
                                {{ $this->dashboardStats['order_growth'] >= 0 ? '↗' : '↘' }} {{ abs($this->dashboardStats['order_growth']) }}%
                            </span>
                        </div>
                    </div>
                    <div class="bg-yellow-400/30 rounded-lg p-2">
                        <x-fas-shopping-cart class="w-5 h-5" />
                    </div>
                </div>
            </div>

            <!-- Net Profit -->
            <div
                class="bg-gradient-to-br from-purple-500 to-purple-600 rounded-xl shadow-xl p-4 text-white transform hover:scale-105 transition-all duration-300 hover:shadow-2xl">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-purple-100 text-xs font-medium">Net Profit</p>
                        <p class="text-2xl font-bold">৳{{ number_format($this->dashboardStats['net_profit']) }}</p>
                        <p class="text-purple-100 text-xs mt-1">All time</p>
                    </div>
                    <div class="bg-purple-400/30 rounded-lg p-2">
                        <x-fas-chart-pie class="w-5 h-5" />
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
                    <p class="text-lg font-bold text-gray-900">{{ $this->dashboardStats['today_orders'] }}</p>
                    <p class="text-xs text-gray-600">Today</p>
                </div>
            </div>

            <!-- Pending Orders -->
            <div class="bg-white rounded-lg shadow-md p-3 border-l-4 border-yellow-400 hover:shadow-lg transition-all duration-300">
                <div class="text-center">
                    <div class="bg-yellow-100 rounded-full p-2 w-8 h-8 mx-auto mb-2 flex items-center justify-center">
                        <x-fas-hourglass-half class="w-3 h-3 text-yellow-600" />
                    </div>
                    <p class="text-lg font-bold text-gray-900">{{ $this->dashboardStats['pending_orders'] }}</p>
                    <p class="text-xs text-gray-600">Pending</p>
                </div>
            </div>

            <!-- Customers -->
            <div class="bg-white rounded-lg shadow-md p-3 border-l-4 border-blue-400 hover:shadow-lg transition-all duration-300">
                <div class="text-center">
                    <div class="bg-blue-100 rounded-full p-2 w-8 h-8 mx-auto mb-2 flex items-center justify-center">
                        <x-fas-users class="w-3 h-3 text-blue-600" />
                    </div>
                    <p class="text-lg font-bold text-gray-900">{{ $this->dashboardStats['total_customers'] }}</p>
                    <p class="text-xs text-gray-600">Customers</p>
                </div>
            </div>

            <!-- Hotels -->
            <div class="bg-white rounded-lg shadow-md p-3 border-l-4 border-green-400 hover:shadow-lg transition-all duration-300">
                <div class="text-center">
                    <div class="bg-green-100 rounded-full p-2 w-8 h-8 mx-auto mb-2 flex items-center justify-center">
                        <x-fas-building class="w-3 h-3 text-green-600" />
                    </div>
                    <p class="text-lg font-bold text-gray-900">{{ $this->dashboardStats['total_hotels'] }}</p>
                    <p class="text-xs text-gray-600">Hotels</p>
                </div>
            </div>

            <!-- Tours -->
            <div class="bg-white rounded-lg shadow-md p-3 border-l-4 border-cyan-400 hover:shadow-lg transition-all duration-300">
                <div class="text-center">
                    <div class="bg-cyan-100 rounded-full p-2 w-8 h-8 mx-auto mb-2 flex items-center justify-center">
                        <x-fas-globe class="w-3 h-3 text-cyan-600" />
                    </div>
                    <p class="text-lg font-bold text-gray-900">{{ $this->dashboardStats['total_tours'] }}</p>
                    <p class="text-xs text-gray-600">Tours</p>
                </div>
            </div>

            <!-- Flights -->
            <div class="bg-white rounded-lg shadow-md p-3 border-l-4 border-indigo-400 hover:shadow-lg transition-all duration-300">
                <div class="text-center">
                    <div class="bg-indigo-100 rounded-full p-2 w-8 h-8 mx-auto mb-2 flex items-center justify-center">
                        <x-fas-plane class="w-3 h-3 text-indigo-600" />
                    </div>
                    <p class="text-lg font-bold text-gray-900">{{ $this->dashboardStats['total_flights'] }}</p>
                    <p class="text-xs text-gray-600">Flights</p>
                </div>
            </div>

            <!-- Cars -->
            <div class="bg-white rounded-lg shadow-md p-3 border-l-4 border-emerald-400 hover:shadow-lg transition-all duration-300">
                <div class="text-center">
                    <div class="bg-emerald-100 rounded-full p-2 w-8 h-8 mx-auto mb-2 flex items-center justify-center">
                        <x-fas-car class="w-3 h-3 text-emerald-600" />
                    </div>
                    <p class="text-lg font-bold text-gray-900">{{ $this->dashboardStats['total_cars'] }}</p>
                    <p class="text-xs text-gray-600">Cars</p>
                </div>
            </div>

            <!-- Offers -->
            <div class="bg-white rounded-lg shadow-md p-3 border-l-4 border-pink-400 hover:shadow-lg transition-all duration-300">
                <div class="text-center">
                    <div class="bg-pink-100 rounded-full p-2 w-8 h-8 mx-auto mb-2 flex items-center justify-center">
                        <x-fas-tag class="w-3 h-3 text-pink-600" />
                    </div>
                    <p class="text-lg font-bold text-gray-900">{{ $this->dashboardStats['total_offers'] }}</p>
                    <p class="text-xs text-gray-600">Offers</p>
                </div>
            </div>

            <!-- Visas -->
            <div class="bg-white rounded-lg shadow-md p-3 border-l-4 border-violet-400 hover:shadow-lg transition-all duration-300">
                <div class="text-center">
                    <div class="bg-violet-100 rounded-full p-2 w-8 h-8 mx-auto mb-2 flex items-center justify-center">
                        <x-fas-passport class="w-3 h-3 text-violet-600" />
                    </div>
                    <p class="text-lg font-bold text-gray-900">{{ $this->dashboardStats['total_visas'] }}</p>
                    <p class="text-xs text-gray-600">Visas</p>
                </div>
            </div>

            <!-- Products -->
            <div class="bg-white rounded-lg shadow-md p-3 border-l-4 border-teal-400 hover:shadow-lg transition-all duration-300">
                <div class="text-center">
                    <div class="bg-teal-100 rounded-full p-2 w-8 h-8 mx-auto mb-2 flex items-center justify-center">
                        <x-fas-box class="w-3 h-3 text-teal-600" />
                    </div>
                    <p class="text-lg font-bold text-gray-900">{{ $this->dashboardStats['total_products'] }}</p>
                    <p class="text-xs text-gray-600">Products</p>
                </div>
            </div>

            <!-- Income -->
            <div class="bg-white rounded-lg shadow-md p-3 border-l-4 border-green-400 hover:shadow-lg transition-all duration-300">
                <div class="text-center">
                    <div class="bg-green-100 rounded-full p-2 w-8 h-8 mx-auto mb-2 flex items-center justify-center">
                        <x-fas-arrow-up class="w-3 h-3 text-green-600" />
                    </div>
                    <p class="text-lg font-bold text-gray-900">৳{{ number_format($this->dashboardStats['total_income']) }}</p>
                    <p class="text-xs text-gray-600">Income</p>
                </div>
            </div>

            <!-- Expense -->
            <div class="bg-white rounded-lg shadow-md p-3 border-l-4 border-red-400 hover:shadow-lg transition-all duration-300">
                <div class="text-center">
                    <div class="bg-red-100 rounded-full p-2 w-8 h-8 mx-auto mb-2 flex items-center justify-center">
                        <x-fas-arrow-down class="w-3 h-3 text-red-600" />
                    </div>
                    <p class="text-lg font-bold text-gray-900">৳{{ number_format($this->dashboardStats['total_expense']) }}</p>
                    <p class="text-xs text-gray-600">Expense</p>
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
                    <a href="/admin/order/list" class="text-blue-600 hover:text-blue-800 text-sm font-medium">View All</a>
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
                    <a href="/admin/customers" class="text-blue-600 hover:text-blue-800 text-sm font-medium">View All</a>
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
                <a href="/admin/order/list"
                    class="group flex flex-col items-center p-3 bg-gradient-to-br from-blue-50 to-blue-100 rounded-lg hover:from-blue-100 hover:to-blue-200 transition-all duration-300 transform hover:scale-105">
                    <div class="w-10 h-10 bg-blue-500 rounded-lg flex items-center justify-center mb-2 group-hover:bg-blue-600 transition-colors">
                        <x-fas-list class="w-4 h-4 text-white" />
                    </div>
                    <span class="text-xs font-medium text-gray-900 text-center">Orders</span>
                </a>

                <a href="/admin/hotel/create"
                    class="group flex flex-col items-center p-3 bg-gradient-to-br from-green-50 to-green-100 rounded-lg hover:from-green-100 hover:to-green-200 transition-all duration-300 transform hover:scale-105">
                    <div class="w-10 h-10 bg-green-500 rounded-lg flex items-center justify-center mb-2 group-hover:bg-green-600 transition-colors">
                        <x-fas-plus class="w-4 h-4 text-white" />
                    </div>
                    <span class="text-xs font-medium text-gray-900 text-center">Add Hotel</span>
                </a>

                <a href="/admin/tour/create"
                    class="group flex flex-col items-center p-3 bg-gradient-to-br from-yellow-50 to-yellow-100 rounded-lg hover:from-yellow-100 hover:to-yellow-200 transition-all duration-300 transform hover:scale-105">
                    <div
                        class="w-10 h-10 bg-yellow-500 rounded-lg flex items-center justify-center mb-2 group-hover:bg-yellow-600 transition-colors">
                        <x-fas-plus class="w-4 h-4 text-white" />
                    </div>
                    <span class="text-xs font-medium text-gray-900 text-center">Add Tour</span>
                </a>

                <a href="/admin/income/list"
                    class="group flex flex-col items-center p-3 bg-gradient-to-br from-emerald-50 to-emerald-100 rounded-lg hover:from-emerald-100 hover:to-emerald-200 transition-all duration-300 transform hover:scale-105">
                    <div
                        class="w-10 h-10 bg-emerald-500 rounded-lg flex items-center justify-center mb-2 group-hover:bg-emerald-600 transition-colors">
                        <x-fas-arrow-up class="w-4 h-4 text-white" />
                    </div>
                    <span class="text-xs font-medium text-gray-900 text-center">Income</span>
                </a>

                <a href="/admin/expense/list"
                    class="group flex flex-col items-center p-3 bg-gradient-to-br from-red-50 to-red-100 rounded-lg hover:from-red-100 hover:to-red-200 transition-all duration-300 transform hover:scale-105">
                    <div class="w-10 h-10 bg-red-500 rounded-lg flex items-center justify-center mb-2 group-hover:bg-red-600 transition-colors">
                        <x-fas-arrow-down class="w-4 h-4 text-white" />
                    </div>
                    <span class="text-xs font-medium text-gray-900 text-center">Expense</span>
                </a>

                <a href="/admin/accounts/chart-of-account"
                    class="group flex flex-col items-center p-3 bg-gradient-to-br from-purple-50 to-purple-100 rounded-lg hover:from-purple-100 hover:to-purple-200 transition-all duration-300 transform hover:scale-105">
                    <div
                        class="w-10 h-10 bg-purple-500 rounded-lg flex items-center justify-center mb-2 group-hover:bg-purple-600 transition-colors">
                        <x-fas-chart-bar class="w-4 h-4 text-white" />
                    </div>
                    <span class="text-xs font-medium text-gray-900 text-center">Accounts</span>
                </a>

                <a href="/admin/customers"
                    class="group flex flex-col items-center p-3 bg-gradient-to-br from-cyan-50 to-cyan-100 rounded-lg hover:from-cyan-100 hover:to-cyan-200 transition-all duration-300 transform hover:scale-105">
                    <div class="w-10 h-10 bg-cyan-500 rounded-lg flex items-center justify-center mb-2 group-hover:bg-cyan-600 transition-colors">
                        <x-fas-users class="w-4 h-4 text-white" />
                    </div>
                    <span class="text-xs font-medium text-gray-900 text-center">Customers</span>
                </a>

                <a href="/admin/hotel/list"
                    class="group flex flex-col items-center p-3 bg-gradient-to-br from-green-50 to-green-100 rounded-lg hover:from-green-100 hover:to-green-200 transition-all duration-300 transform hover:scale-105">
                    <div class="w-10 h-10 bg-green-500 rounded-lg flex items-center justify-center mb-2 group-hover:bg-green-600 transition-colors">
                        <x-fas-building class="w-4 h-4 text-white" />
                    </div>
                    <span class="text-xs font-medium text-gray-900 text-center">Hotels</span>
                </a>

                <a href="/admin/tour/list"
                    class="group flex flex-col items-center p-3 bg-gradient-to-br from-yellow-50 to-yellow-100 rounded-lg hover:from-yellow-100 hover:to-yellow-200 transition-all duration-300 transform hover:scale-105">
                    <div
                        class="w-10 h-10 bg-yellow-500 rounded-lg flex items-center justify-center mb-2 group-hover:bg-yellow-600 transition-colors">
                        <x-fas-globe class="w-4 h-4 text-white" />
                    </div>
                    <span class="text-xs font-medium text-gray-900 text-center">Tours</span>
                </a>

                <a href="/admin/offer/list"
                    class="group flex flex-col items-center p-3 bg-gradient-to-br from-pink-50 to-pink-100 rounded-lg hover:from-pink-100 hover:to-pink-200 transition-all duration-300 transform hover:scale-105">
                    <div class="w-10 h-10 bg-pink-500 rounded-lg flex items-center justify-center mb-2 group-hover:bg-pink-600 transition-colors">
                        <x-fas-tag class="w-4 h-4 text-white" />
                    </div>
                    <span class="text-xs font-medium text-gray-900 text-center">Offers</span>
                </a>

                <a href="/admin/visa/list"
                    class="group flex flex-col items-center p-3 bg-gradient-to-br from-violet-50 to-violet-100 rounded-lg hover:from-violet-100 hover:to-violet-200 transition-all duration-300 transform hover:scale-105">
                    <div
                        class="w-10 h-10 bg-violet-500 rounded-lg flex items-center justify-center mb-2 group-hover:bg-violet-600 transition-colors">
                        <x-fas-passport class="w-4 h-4 text-white" />
                    </div>
                    <span class="text-xs font-medium text-gray-900 text-center">Visas</span>
                </a>

                <a href="/admin/car/list"
                    class="group flex flex-col items-center p-3 bg-gradient-to-br from-emerald-50 to-emerald-100 rounded-lg hover:from-emerald-100 hover:to-emerald-200 transition-all duration-300 transform hover:scale-105">
                    <div
                        class="w-10 h-10 bg-emerald-500 rounded-lg flex items-center justify-center mb-2 group-hover:bg-emerald-600 transition-colors">
                        <x-fas-car class="w-4 h-4 text-white" />
                    </div>
                    <span class="text-xs font-medium text-gray-900 text-center">Cars</span>
                </a>
            </div>
        </div>
    </div>
</div>
