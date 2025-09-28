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
use App\Enum\UserType;
use Livewire\Volt\Component;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Computed;

new #[Layout('components.layouts.admin')] #[Title('Admin Dashboard')] class extends Component {
    /**
     * Check if user has permission to view specific dashboard sections
     */
    public function canView($permission)
    {
        return auth()->user()->can($permission) || auth()->user()->hasRole('Super Admin');
    }

    /**
     * Get user's role-based dashboard data
     */
    #[Computed]
    public function userRole()
    {
        return auth()->user()->type;
    }

    /**
     * Get permission-based dashboard widgets
     */
    #[Computed]
    public function dashboardWidgets()
    {
        $widgets = [];
        $user = auth()->user();

        // Financial widgets - only for users with financial permissions
        if ($this->canView('income-expense') || $this->canView('chart-of-account') || $this->canView('reports')) {
            $widgets['financial'] = [
                'total_income' => Income::sum('amount'),
                'total_expense' => Expense::sum('amount'),
                'net_profit' => Income::sum('amount') - Expense::sum('amount'),
                'monthly_income' => Income::whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->sum('amount'),
                'monthly_expense' => Expense::whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->sum('amount'),
            ];
        }

        // Order widgets - only for users with order permissions
        if ($this->canView('orders') || $this->canView('bookings')) {
            $widgets['orders'] = [
                'total_orders' => Order::count(),
                'pending_orders' => Order::where('status', OrderStatus::Pending)->count(),
                'confirmed_orders' => Order::where('status', OrderStatus::Confirmed)->count(),
                'today_orders' => Order::whereDate('created_at', today())->count(),
                'total_revenue' => Order::where('payment_status', PaymentStatus::Paid)->sum('total_amount'),
                'monthly_revenue' => Order::where('payment_status', PaymentStatus::Paid)->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->sum('total_amount'),
            ];
        }

        // Customer widgets - only for users with customer permissions
        if ($this->canView('customer')) {
            $widgets['customers'] = [
                'total_customers' => Customer::count(),
                'new_customers_today' => Customer::whereDate('created_at', today())->count(),
                'new_customers_this_month' => Customer::whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->count(),
            ];
        }

        // Product widgets - only for users with product permissions
        if ($this->canView('hotel') || $this->canView('tour-package') || $this->canView('travel-product')) {
            $widgets['products'] = [
                'total_hotels' => Hotel::count(),
                'active_hotels' => Hotel::where('status', HotelStatus::Active)->count(),
                'total_tours' => Tour::count(),
                'active_tours' => Tour::where('status', TourStatus::Active)->count(),
                'total_products' => TravelProduct::count(),
                'total_offers' => Offer::count(),
                'active_offers' => Offer::where('status', OfferStatus::Active)->count(),
            ];
        }

        // Agent widgets - only for users with agent permissions
        if ($this->canView('agent') || $this->canView('agent-report')) {
            $widgets['agents'] = [
                'total_agents' => \App\Models\Agent::count(),
                'active_agents' => \App\Models\Agent::where('status', \App\Enum\AgentStatus::Approve)->count(),
            ];
        }

        return $widgets;
    }

    #[Computed]
    public function dashboardStats()
    {
        $currentMonth = now()->month;
        $currentYear = now()->year;
        $lastMonth = now()->subMonth();
        $today = now()->toDateString();
        $yesterday = now()->subDay()->toDateString();

        return [
            // Orders
            'total_orders' => Order::count(),
            'pending_orders' => Order::where('status', OrderStatus::Pending)->count(),
            'confirmed_orders' => Order::where('status', OrderStatus::Confirmed)->count(),
            'cancelled_orders' => Order::where('status', OrderStatus::Cancelled)->count(),
            'today_orders' => Order::whereDate('created_at', today())->count(),
            'yesterday_orders' => Order::whereDate('created_at', $yesterday)->count(),
            'monthly_orders' => Order::whereMonth('created_at', $currentMonth)->whereYear('created_at', $currentYear)->count(),
            'last_month_orders' => Order::whereMonth('created_at', $lastMonth->month)->whereYear('created_at', $lastMonth->year)->count(),

            // Revenue
            'total_revenue' => Order::where('payment_status', PaymentStatus::Paid)->sum('total_amount'),
            'monthly_revenue' => Order::where('payment_status', PaymentStatus::Paid)->whereMonth('created_at', $currentMonth)->whereYear('created_at', $currentYear)->sum('total_amount'),
            'last_month_revenue' => Order::where('payment_status', PaymentStatus::Paid)->whereMonth('created_at', $lastMonth->month)->whereYear('created_at', $lastMonth->year)->sum('total_amount'),
            'today_revenue' => Order::where('payment_status', PaymentStatus::Paid)->whereDate('created_at', today())->sum('total_amount'),
            'yesterday_revenue' => Order::where('payment_status', PaymentStatus::Paid)->whereDate('created_at', $yesterday)->sum('total_amount'),
            'average_order_value' => Order::where('payment_status', PaymentStatus::Paid)->avg('total_amount'),

            // Customers
            'total_customers' => Customer::count(),
            'new_customers_this_month' => Customer::whereMonth('created_at', $currentMonth)->whereYear('created_at', $currentYear)->count(),
            'new_customers_today' => Customer::whereDate('created_at', today())->count(),
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

            // Financial - Enhanced with daily tracking
            'total_income' => Income::sum('amount'),
            'monthly_income' => Income::whereMonth('created_at', $currentMonth)->whereYear('created_at', $currentYear)->sum('amount'),
            'daily_income' => Income::whereDate('created_at', today())->sum('amount'),
            'yesterday_income' => Income::whereDate('created_at', $yesterday)->sum('amount'),
            'last_month_income' => Income::whereMonth('created_at', $lastMonth->month)->whereYear('created_at', $lastMonth->year)->sum('amount'),

            'total_expense' => Expense::sum('amount'),
            'monthly_expense' => Expense::whereMonth('created_at', $currentMonth)->whereYear('created_at', $currentYear)->sum('amount'),
            'daily_expense' => Expense::whereDate('created_at', today())->sum('amount'),
            'yesterday_expense' => Expense::whereDate('created_at', $yesterday)->sum('amount'),
            'last_month_expense' => Expense::whereMonth('created_at', $lastMonth->month)->whereYear('created_at', $lastMonth->year)->sum('amount'),

            'net_profit' => Income::sum('amount') - Expense::sum('amount'),
            'monthly_profit' => Income::whereMonth('created_at', $currentMonth)->whereYear('created_at', $currentYear)->sum('amount') - Expense::whereMonth('created_at', $currentMonth)->whereYear('created_at', $currentYear)->sum('amount'),
            'daily_profit' => Income::whereDate('created_at', today())->sum('amount') - Expense::whereDate('created_at', today())->sum('amount'),

            // Growth Rates
            'revenue_growth' => $this->calculateGrowthRate(Order::where('payment_status', PaymentStatus::Paid)->whereMonth('created_at', $lastMonth->month)->whereYear('created_at', $lastMonth->year)->sum('total_amount'), Order::where('payment_status', PaymentStatus::Paid)->whereMonth('created_at', $currentMonth)->whereYear('created_at', $currentYear)->sum('total_amount')),
            'order_growth' => $this->calculateGrowthRate(Order::whereMonth('created_at', $lastMonth->month)->whereYear('created_at', $lastMonth->year)->count(), Order::whereMonth('created_at', $currentMonth)->whereYear('created_at', $currentYear)->count()),
            'income_growth' => $this->calculateGrowthRate(Income::whereMonth('created_at', $lastMonth->month)->whereYear('created_at', $lastMonth->year)->sum('amount'), Income::whereMonth('created_at', $currentMonth)->whereYear('created_at', $currentYear)->sum('amount')),
            'expense_growth' => $this->calculateGrowthRate(Expense::whereMonth('created_at', $lastMonth->month)->whereYear('created_at', $lastMonth->year)->sum('amount'), Expense::whereMonth('created_at', $currentMonth)->whereYear('created_at', $currentYear)->sum('amount')),
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

    #[Computed]
    public function dailyIncomeExpenseData()
    {
        $days = [];
        $incomeData = [];
        $expenseData = [];
        $profitData = [];

        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $days[] = $date->format('M d');

            $dailyIncome = Income::whereDate('created_at', $date->toDateString())->sum('amount');
            $dailyExpense = Expense::whereDate('created_at', $date->toDateString())->sum('amount');

            $incomeData[] = $dailyIncome;
            $expenseData[] = $dailyExpense;
            $profitData[] = $dailyIncome - $dailyExpense;
        }

        return [
            'days' => $days,
            'income' => $incomeData,
            'expense' => $expenseData,
            'profit' => $profitData,
        ];
    }

    #[Computed]
    public function monthlyIncomeExpenseData()
    {
        $months = [];
        $incomeData = [];
        $expenseData = [];
        $profitData = [];

        for ($i = 5; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $months[] = $date->format('M Y');

            $monthlyIncome = Income::whereMonth('created_at', $date->month)->whereYear('created_at', $date->year)->sum('amount');
            $monthlyExpense = Expense::whereMonth('created_at', $date->month)->whereYear('created_at', $date->year)->sum('amount');

            $incomeData[] = $monthlyIncome;
            $expenseData[] = $monthlyExpense;
            $profitData[] = $monthlyIncome - $monthlyExpense;
        }

        return [
            'months' => $months,
            'income' => $incomeData,
            'expense' => $expenseData,
            'profit' => $profitData,
        ];
    }

    #[Computed]
    public function incomeExpenseCategories()
    {
        $incomeCategories = Income::with('account')
            ->selectRaw('account_id, SUM(amount) as total')
            ->groupBy('account_id')
            ->get()
            ->map(function ($item) {
                return [
                    'name' => $item->account->name ?? 'Unknown',
                    'amount' => $item->total,
                ];
            });

        $expenseCategories = Expense::with('expenseHead')
            ->selectRaw('expenses_head_id, SUM(amount) as total')
            ->groupBy('expenses_head_id')
            ->get()
            ->map(function ($item) {
                return [
                    'name' => $item->expenseHead->name ?? 'Unknown',
                    'amount' => $item->total,
                ];
            });

        return [
            'income' => $incomeCategories,
            'expense' => $expenseCategories,
        ];
    }
}; ?>


<!-- Chart.js CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<div class="min-h-screen bg-gradient-to-br from-gray-50 to-white">
    <!-- Modern Header -->
    <div class="bg-white shadow-lg border-b border-gray-200">
        <div class="px-6 py-4">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Admin Dashboard</h1>
                    <p class="text-gray-600 text-sm">{{ now()->format('l, F d, Y') }}</p>
                </div>
                <div class="flex items-center space-x-6">
                    <div class="text-right">
                        <p class="text-gray-500 text-sm">Last updated</p>
                        <p class="font-bold text-gray-900 text-lg">{{ now()->format('H:i') }}</p>
                    </div>
                    <div class="w-12 h-12 bg-gradient-to-r from-green-500 to-emerald-600 rounded-full flex items-center justify-center">
                        <x-fas-chart-line class="w-6 h-6 text-white" />
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="p-4 space-y-4">
        <!-- Primary Metrics - Permission-based -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
            <!-- Total Revenue - Only show if user has orders permission -->
            @if ($this->canView('orders') || $this->canView('bookings'))
                <div
                    class="group relative overflow-hidden bg-white rounded-2xl border border-gray-200 shadow-lg hover:shadow-xl transition-all duration-300 hover:scale-105">
                    <div
                        class="absolute inset-0 bg-gradient-to-br from-green-50 to-emerald-50 opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                    </div>
                    <div class="relative p-4">
                        <div class="flex items-center justify-between mb-3">
                            <div
                                class="w-10 h-10 bg-gradient-to-r from-emerald-500 to-green-600 rounded-lg flex items-center justify-center shadow-lg">
                                <x-fas-chart-line class="w-5 h-5 text-white" />
                            </div>
                            <div class="text-right">
                                <span
                                    class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium {{ $this->dashboardStats['revenue_growth'] >= 0 ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                    {{ $this->dashboardStats['revenue_growth'] >= 0 ? '↗' : '↘' }} {{ abs($this->dashboardStats['revenue_growth']) }}%
                                </span>
                            </div>
                        </div>
                        <div>
                            <p class="text-gray-600 text-xs font-medium mb-1">Total Revenue</p>
                            <p class="text-2xl font-bold text-gray-900 mb-1">৳{{ number_format($this->dashboardStats['total_revenue']) }}</p>
                            <p class="text-gray-500 text-xs">All time earnings</p>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Monthly Revenue - Only show if user has orders permission -->
            @if ($this->canView('orders') || $this->canView('bookings'))
                <div
                    class="group relative overflow-hidden bg-white rounded-2xl border border-gray-200 shadow-lg hover:shadow-xl transition-all duration-300 hover:scale-105">
                    <div
                        class="absolute inset-0 bg-gradient-to-br from-blue-50 to-cyan-50 opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                    </div>
                    <div class="relative p-4">
                        <div class="flex items-center justify-between mb-3">
                            <div class="w-10 h-10 bg-gradient-to-r from-blue-500 to-cyan-600 rounded-lg flex items-center justify-center shadow-lg">
                                <x-fas-calendar class="w-5 h-5 text-white" />
                            </div>
                            <div class="text-right">
                                <span class="text-gray-500 text-xs">{{ now()->format('M Y') }}</span>
                            </div>
                        </div>
                        <div>
                            <p class="text-gray-600 text-xs font-medium mb-1">This Month</p>
                            <p class="text-2xl font-bold text-gray-900 mb-1">৳{{ number_format($this->dashboardStats['monthly_revenue']) }}</p>
                            <p class="text-gray-500 text-xs">Current month earnings</p>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Total Orders - Only show if user has orders permission -->
            @if ($this->canView('orders') || $this->canView('bookings'))
                <div
                    class="group relative overflow-hidden bg-white rounded-2xl border border-gray-200 shadow-lg hover:shadow-xl transition-all duration-300 hover:scale-105">
                    <div
                        class="absolute inset-0 bg-gradient-to-br from-green-50 to-emerald-50 opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                    </div>
                    <div class="relative p-4">
                        <div class="flex items-center justify-between mb-3">
                            <div
                                class="w-10 h-10 bg-gradient-to-r from-green-500 to-emerald-600 rounded-lg flex items-center justify-center shadow-lg">
                                <x-fas-shopping-cart class="w-5 h-5 text-white" />
                            </div>
                            <div class="text-right">
                                <span
                                    class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium {{ $this->dashboardStats['order_growth'] >= 0 ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                    {{ $this->dashboardStats['order_growth'] >= 0 ? '↗' : '↘' }} {{ abs($this->dashboardStats['order_growth']) }}%
                                </span>
                            </div>
                        </div>
                        <div>
                            <p class="text-gray-600 text-xs font-medium mb-1">Total Orders</p>
                            <p class="text-2xl font-bold text-gray-900 mb-1">{{ number_format($this->dashboardStats['total_orders']) }}</p>
                            <p class="text-gray-500 text-xs">All time orders</p>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Net Profit - Only show if user has financial permissions -->
            @if ($this->canView('income-expense') || $this->canView('chart-of-account') || $this->canView('reports'))
                <div
                    class="group relative overflow-hidden bg-white rounded-2xl border border-gray-200 shadow-lg hover:shadow-xl transition-all duration-300 hover:scale-105">
                    <div
                        class="absolute inset-0 bg-gradient-to-br from-green-50 to-emerald-50 opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                    </div>
                    <div class="relative p-4">
                        <div class="flex items-center justify-between mb-3">
                            <div
                                class="w-10 h-10 bg-gradient-to-r from-green-500 to-emerald-600 rounded-lg flex items-center justify-center shadow-lg">
                                <x-fas-chart-pie class="w-5 h-5 text-white" />
                            </div>
                            <div class="text-right">
                                <span class="text-gray-500 text-xs">All time</span>
                            </div>
                        </div>
                        <div>
                            <p class="text-gray-600 text-xs font-medium mb-1">Net Profit</p>
                            <p class="text-2xl font-bold text-gray-900 mb-1">৳{{ number_format($this->dashboardStats['net_profit']) }}</p>
                            <p class="text-gray-500 text-xs">Income - Expenses</p>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        <!-- Daily & Monthly Financial Overview - Only show if user has financial permissions -->
        @if ($this->canView('income-expense') || $this->canView('chart-of-account') || $this->canView('reports'))
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                <!-- Daily Financial Summary -->
                <div class="bg-white rounded-xl border border-gray-200 shadow-lg p-4">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-bold text-gray-900">Daily Financial Overview</h3>
                        <div class="w-8 h-8 bg-gradient-to-r from-green-500 to-emerald-600 rounded-lg flex items-center justify-center">
                            <x-fas-calendar-day class="w-4 h-4 text-white" />
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <!-- Today's Income -->
                        <div class="bg-gradient-to-br from-green-50 to-emerald-50 rounded-lg p-3 border border-green-200">
                            <div class="flex items-center justify-between mb-2">
                                <div class="w-6 h-6 bg-green-500 rounded flex items-center justify-center">
                                    <x-fas-arrow-up class="w-3 h-3 text-white" />
                                </div>
                                <span class="text-green-600 text-xs font-medium">Today</span>
                            </div>
                            <p class="text-lg font-bold text-gray-900">৳{{ number_format($this->dashboardStats['daily_income']) }}</p>
                            <p class="text-green-600 text-xs">Daily Income</p>
                        </div>

                        <!-- Today's Expense -->
                        <div class="bg-gradient-to-br from-red-50 to-pink-50 rounded-lg p-3 border border-red-200">
                            <div class="flex items-center justify-between mb-2">
                                <div class="w-6 h-6 bg-red-500 rounded flex items-center justify-center">
                                    <x-fas-arrow-down class="w-3 h-3 text-white" />
                                </div>
                                <span class="text-red-600 text-xs font-medium">Today</span>
                            </div>
                            <p class="text-lg font-bold text-gray-900">৳{{ number_format($this->dashboardStats['daily_expense']) }}</p>
                            <p class="text-red-600 text-xs">Daily Expense</p>
                        </div>

                        <!-- Today's Profit -->
                        <div class="bg-gradient-to-br from-green-50 to-emerald-50 rounded-lg p-3 border border-green-200 col-span-2">
                            <div class="flex items-center justify-between mb-2">
                                <div class="w-6 h-6 bg-green-500 rounded flex items-center justify-center">
                                    <x-fas-chart-pie class="w-3 h-3 text-white" />
                                </div>
                                <span class="text-green-600 text-xs font-medium">Today</span>
                            </div>
                            <p class="text-lg font-bold text-gray-900">৳{{ number_format($this->dashboardStats['daily_profit']) }}</p>
                            <p class="text-green-600 text-xs">Daily Profit</p>
                        </div>
                    </div>
                </div>

                <!-- Monthly Financial Summary -->
                <div class="bg-white rounded-xl border border-gray-200 shadow-lg p-4">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-bold text-gray-900">Monthly Financial Overview</h3>
                        <div class="w-8 h-8 bg-gradient-to-r from-green-500 to-emerald-600 rounded-lg flex items-center justify-center">
                            <x-fas-calendar-alt class="w-4 h-4 text-white" />
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <!-- Monthly Income -->
                        <div class="bg-gradient-to-br from-green-50 to-emerald-50 rounded-lg p-3 border border-green-200">
                            <div class="flex items-center justify-between mb-2">
                                <div class="w-6 h-6 bg-green-500 rounded flex items-center justify-center">
                                    <x-fas-arrow-up class="w-3 h-3 text-white" />
                                </div>
                                <span
                                    class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium {{ $this->dashboardStats['income_growth'] >= 0 ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                    {{ $this->dashboardStats['income_growth'] >= 0 ? '↗' : '↘' }} {{ abs($this->dashboardStats['income_growth']) }}%
                                </span>
                            </div>
                            <p class="text-lg font-bold text-gray-900">৳{{ number_format($this->dashboardStats['monthly_income']) }}</p>
                            <p class="text-green-600 text-xs">Monthly Income</p>
                        </div>

                        <!-- Monthly Expense -->
                        <div class="bg-gradient-to-br from-red-50 to-pink-50 rounded-lg p-3 border border-red-200">
                            <div class="flex items-center justify-between mb-2">
                                <div class="w-6 h-6 bg-red-500 rounded flex items-center justify-center">
                                    <x-fas-arrow-down class="w-3 h-3 text-white" />
                                </div>
                                <span
                                    class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium {{ $this->dashboardStats['expense_growth'] >= 0 ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800' }}">
                                    {{ $this->dashboardStats['expense_growth'] >= 0 ? '↗' : '↘' }}
                                    {{ abs($this->dashboardStats['expense_growth']) }}%
                                </span>
                            </div>
                            <p class="text-lg font-bold text-gray-900">৳{{ number_format($this->dashboardStats['monthly_expense']) }}</p>
                            <p class="text-red-600 text-xs">Monthly Expense</p>
                        </div>

                        <!-- Monthly Profit -->
                        <div class="bg-gradient-to-br from-green-50 to-emerald-50 rounded-lg p-3 border border-green-200 col-span-2">
                            <div class="flex items-center justify-between mb-2">
                                <div class="w-6 h-6 bg-green-500 rounded flex items-center justify-center">
                                    <x-fas-chart-pie class="w-3 h-3 text-white" />
                                </div>
                                <span class="text-green-600 text-xs font-medium">{{ now()->format('M Y') }}</span>
                            </div>
                            <p class="text-lg font-bold text-gray-900">৳{{ number_format($this->dashboardStats['monthly_profit']) }}</p>
                            <p class="text-green-600 text-xs">Monthly Profit</p>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <!-- Quick Stats Grid - Permission-based -->
        <div class="grid grid-cols-4 md:grid-cols-6 lg:grid-cols-8 gap-3">
            <!-- Today's Orders - Only show if user has orders permission -->
            @if ($this->canView('orders') || $this->canView('bookings'))
                <div class="bg-white rounded-lg border border-gray-200 p-3 hover:bg-gray-50 transition-all duration-300 shadow-sm hover:shadow-md">
                    <div class="text-center">
                        <div class="w-8 h-8 bg-green-500 rounded-lg mx-auto mb-2 flex items-center justify-center">
                            <x-fas-clock class="w-4 h-4 text-white" />
                        </div>
                        <p class="text-lg font-bold text-gray-900">{{ $this->dashboardStats['today_orders'] }}</p>
                        <p class="text-gray-600 text-xs">Today</p>
                    </div>
                </div>
            @endif

            <!-- Pending Orders - Only show if user has orders permission -->
            @if ($this->canView('orders') || $this->canView('bookings'))
                <div class="bg-white rounded-lg border border-gray-200 p-3 hover:bg-gray-50 transition-all duration-300 shadow-sm hover:shadow-md">
                    <div class="text-center">
                        <div class="w-8 h-8 bg-yellow-500 rounded-lg mx-auto mb-2 flex items-center justify-center">
                            <x-fas-hourglass-half class="w-4 h-4 text-white" />
                        </div>
                        <p class="text-lg font-bold text-gray-900">{{ $this->dashboardStats['pending_orders'] }}</p>
                        <p class="text-gray-600 text-xs">Pending</p>
                    </div>
                </div>
            @endif

            <!-- Customers - Only show if user has customer permission -->
            @if ($this->canView('customer'))
                <div class="bg-white rounded-lg border border-gray-200 p-3 hover:bg-gray-50 transition-all duration-300 shadow-sm hover:shadow-md">
                    <div class="text-center">
                        <div class="w-8 h-8 bg-green-500 rounded-lg mx-auto mb-2 flex items-center justify-center">
                            <x-fas-users class="w-4 h-4 text-white" />
                        </div>
                        <p class="text-lg font-bold text-gray-900">{{ $this->dashboardStats['total_customers'] }}</p>
                        <p class="text-gray-600 text-xs">Customers</p>
                    </div>
                </div>
            @endif

            <!-- New Customers Today - Only show if user has customer permission -->
            @if ($this->canView('customer'))
                <div class="bg-white rounded-lg border border-gray-200 p-3 hover:bg-gray-50 transition-all duration-300 shadow-sm hover:shadow-md">
                    <div class="text-center">
                        <div class="w-8 h-8 bg-green-500 rounded-lg mx-auto mb-2 flex items-center justify-center">
                            <x-fas-user-plus class="w-4 h-4 text-white" />
                        </div>
                        <p class="text-lg font-bold text-gray-900">{{ $this->dashboardStats['new_customers_today'] }}</p>
                        <p class="text-gray-600 text-xs">New Today</p>
                    </div>
                </div>
            @endif

            <!-- Hotels - Only show if user has hotel permission -->
            @if ($this->canView('hotel'))
                <div class="bg-white rounded-lg border border-gray-200 p-3 hover:bg-gray-50 transition-all duration-300 shadow-sm hover:shadow-md">
                    <div class="text-center">
                        <div class="w-8 h-8 bg-green-500 rounded-lg mx-auto mb-2 flex items-center justify-center">
                            <x-fas-building class="w-4 h-4 text-white" />
                        </div>
                        <p class="text-lg font-bold text-gray-900">{{ $this->dashboardStats['total_hotels'] }}</p>
                        <p class="text-gray-600 text-xs">Hotels</p>
                    </div>
                </div>
            @endif

            <!-- Tours - Only show if user has tour-package permission -->
            @if ($this->canView('tour-package'))
                <div class="bg-white rounded-lg border border-gray-200 p-3 hover:bg-gray-50 transition-all duration-300 shadow-sm hover:shadow-md">
                    <div class="text-center">
                        <div class="w-8 h-8 bg-green-500 rounded-lg mx-auto mb-2 flex items-center justify-center">
                            <x-fas-globe class="w-4 h-4 text-white" />
                        </div>
                        <p class="text-lg font-bold text-gray-900">{{ $this->dashboardStats['total_tours'] }}</p>
                        <p class="text-gray-600 text-xs">Tours</p>
                    </div>
                </div>
            @endif

            <!-- Flights - Only show if user has group-flight permission -->
            @if ($this->canView('group-flight'))
                <div class="bg-white rounded-lg border border-gray-200 p-3 hover:bg-gray-50 transition-all duration-300 shadow-sm hover:shadow-md">
                    <div class="text-center">
                        <div class="w-8 h-8 bg-green-500 rounded-lg mx-auto mb-2 flex items-center justify-center">
                            <x-fas-plane class="w-4 h-4 text-white" />
                        </div>
                        <p class="text-lg font-bold text-gray-900">{{ $this->dashboardStats['total_flights'] }}</p>
                        <p class="text-gray-600 text-xs">Flights</p>
                    </div>
                </div>
            @endif

            <!-- Offers - Only show if user has offer permission -->
            @if ($this->canView('offer'))
                <div class="bg-white rounded-lg border border-gray-200 p-3 hover:bg-gray-50 transition-all duration-300 shadow-sm hover:shadow-md">
                    <div class="text-center">
                        <div class="w-8 h-8 bg-green-500 rounded-lg mx-auto mb-2 flex items-center justify-center">
                            <x-fas-tag class="w-4 h-4 text-white" />
                        </div>
                        <p class="text-lg font-bold text-gray-900">{{ $this->dashboardStats['total_offers'] }}</p>
                        <p class="text-gray-600 text-xs">Offers</p>
                    </div>
                </div>
            @endif
        </div>

        <!-- Interactive Charts Section - Only show if user has financial permissions -->
        @if ($this->canView('income-expense') || $this->canView('chart-of-account') || $this->canView('reports'))
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                <!-- Daily Income vs Expense Chart -->
                <div class="bg-white rounded-xl border border-gray-200 shadow-lg p-4">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-bold text-gray-900">Daily Income vs Expense</h3>
                        <div class="w-8 h-8 bg-gradient-to-r from-green-500 to-emerald-500 rounded-lg flex items-center justify-center">
                            <x-fas-chart-line class="w-4 h-4 text-white" />
                        </div>
                    </div>
                    <div class="h-48">
                        <canvas id="dailyIncomeExpenseChart"></canvas>
                    </div>
                </div>

                <!-- Monthly Income vs Expense Chart -->
                <div class="bg-white rounded-xl border border-gray-200 shadow-lg p-4">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-bold text-gray-900">Monthly Income vs Expense</h3>
                        <div class="w-8 h-8 bg-gradient-to-r from-green-500 to-emerald-500 rounded-lg flex items-center justify-center">
                            <x-fas-chart-bar class="w-4 h-4 text-white" />
                        </div>
                    </div>
                    <div class="h-48">
                        <canvas id="monthlyIncomeExpenseChart"></canvas>
                    </div>
                </div>
            </div>
        @endif

        <!-- Revenue Trend & Order Status - Permission-based -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
            <!-- Revenue Trend Chart - Only show if user has orders permission -->
            @if ($this->canView('orders') || $this->canView('bookings'))
                <div class="lg:col-span-2 bg-white rounded-xl border border-gray-200 shadow-lg p-4">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-bold text-gray-900">Revenue Trend (6 Months)</h3>
                        <div class="w-8 h-8 bg-gradient-to-r from-green-500 to-emerald-500 rounded-lg flex items-center justify-center">
                            <x-fas-chart-line class="w-4 h-4 text-white" />
                        </div>
                    </div>
                    <div class="h-48">
                        <canvas id="revenueTrendChart"></canvas>
                    </div>
                </div>
            @endif

            <!-- Order Status Overview - Only show if user has orders permission -->
            @if ($this->canView('orders') || $this->canView('bookings'))
                <div class="bg-white rounded-xl border border-gray-200 shadow-lg p-4">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-bold text-gray-900">Order Status</h3>
                        <div class="w-8 h-8 bg-gradient-to-r from-green-500 to-emerald-500 rounded-lg flex items-center justify-center">
                            <x-fas-chart-pie class="w-4 h-4 text-white" />
                        </div>
                    </div>
                    <div class="space-y-3">
                        <div class="flex items-center justify-between p-3 bg-yellow-50 rounded-lg border border-yellow-200">
                            <div class="flex items-center space-x-3">
                                <div class="w-3 h-3 bg-yellow-500 rounded-full"></div>
                                <span class="text-gray-900 font-medium text-sm">Pending</span>
                            </div>
                            <span class="text-xl font-bold text-gray-900">{{ $this->orderStatusData['pending'] }}</span>
                        </div>
                        <div class="flex items-center justify-between p-3 bg-green-50 rounded-lg border border-green-200">
                            <div class="flex items-center space-x-3">
                                <div class="w-3 h-3 bg-green-500 rounded-full"></div>
                                <span class="text-gray-900 font-medium text-sm">Confirmed</span>
                            </div>
                            <span class="text-xl font-bold text-gray-900">{{ $this->orderStatusData['confirmed'] }}</span>
                        </div>
                        <div class="flex items-center justify-between p-3 bg-red-50 rounded-lg border border-red-200">
                            <div class="flex items-center space-x-3">
                                <div class="w-3 h-3 bg-red-500 rounded-full"></div>
                                <span class="text-gray-900 font-medium text-sm">Cancelled</span>
                            </div>
                            <span class="text-xl font-bold text-gray-900">{{ $this->orderStatusData['cancelled'] }}</span>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        <!-- Recent Activity Section - Permission-based -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
            <!-- Recent Orders - Only show if user has orders permission -->
            @if ($this->canView('orders') || $this->canView('bookings'))
                <div class="bg-white rounded-xl border border-gray-200 shadow-lg p-4">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-bold text-gray-900">Recent Orders</h3>
                        <a href="/admin/order/list" class="text-green-600 hover:text-green-700 text-sm font-medium flex items-center space-x-1">
                            <span>View All</span>
                            <x-fas-arrow-right class="w-3 h-3" />
                        </a>
                    </div>
                    <div class="space-y-2">
                        @forelse($this->recentOrders as $order)
                            <div
                                class="flex items-center justify-between p-3 bg-gray-50 rounded-lg border border-gray-200 hover:bg-gray-100 transition-all duration-300">
                                <div class="flex items-center space-x-3">
                                    <div class="w-8 h-8 bg-gradient-to-r from-green-500 to-emerald-500 rounded-lg flex items-center justify-center">
                                        <x-fas-shopping-cart class="w-3 h-3 text-white" />
                                    </div>
                                    <div>
                                        <p class="font-bold text-gray-900 text-sm">#{{ $order->id }}</p>
                                        <p class="text-xs text-gray-600">{{ $order->user->name ?? 'Guest' }}</p>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <p class="font-bold text-gray-900 text-sm">৳{{ number_format($order->total_amount) }}</p>
                                    <span
                                        class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium
                                    @if ($order->status->value === 'confirmed') bg-green-100 text-green-800 border border-green-200
                                    @elseif($order->status->value === 'pending') bg-yellow-100 text-yellow-800 border border-yellow-200
                                    @else bg-gray-100 text-gray-800 border border-gray-200 @endif">
                                        {{ ucfirst($order->status->value) }}
                                    </span>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-8 text-gray-500">
                                <x-fas-shopping-cart class="w-12 h-12 mx-auto mb-3 text-gray-300" />
                                <p class="text-sm">No recent orders</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            @endif

            <!-- Top Customers - Only show if user has customer permission -->
            @if ($this->canView('customer'))
                <div class="bg-white rounded-xl border border-gray-200 shadow-lg p-4">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-bold text-gray-900">Top Customers</h3>
                        <a href="/admin/customers" class="text-green-600 hover:text-green-700 text-sm font-medium flex items-center space-x-1">
                            <span>View All</span>
                            <x-fas-arrow-right class="w-3 h-3" />
                        </a>
                    </div>
                    <div class="space-y-2">
                        @forelse($this->topCustomers as $customer)
                            <div
                                class="flex items-center justify-between p-3 bg-gray-50 rounded-lg border border-gray-200 hover:bg-gray-100 transition-all duration-300">
                                <div class="flex items-center space-x-3">
                                    <div class="w-8 h-8 bg-gradient-to-r from-green-500 to-emerald-500 rounded-lg flex items-center justify-center">
                                        <x-fas-user class="w-3 h-3 text-white" />
                                    </div>
                                    <div>
                                        <p class="font-bold text-gray-900 text-sm">{{ $customer->user->name ?? 'N/A' }}</p>
                                        <p class="text-xs text-gray-600">{{ $customer->user->email ?? 'N/A' }}</p>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <p class="font-bold text-gray-900 text-sm">{{ $customer->orders_count }}</p>
                                    <p class="text-xs text-gray-600">orders</p>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-8 text-gray-500">
                                <x-fas-users class="w-12 h-12 mx-auto mb-3 text-gray-300" />
                                <p class="text-sm">No customers yet</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            @endif
        </div>

        <!-- Quick Actions - Permission-based -->
        <div class="bg-white rounded-lg border border-gray-200 shadow-sm">
            <div class="px-3 py-2 border-b border-gray-100">
                <h3 class="text-sm font-semibold text-gray-900 flex items-center">
                    <x-fas-bolt class="w-4 h-4 text-yellow-600 mr-2" />
                    Quick Actions
                </h3>
            </div>
            <div class="p-3">
                <div class="grid grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-2">
                    @if ($this->canView('orders') || $this->canView('bookings'))
                        <a href="/admin/order/list"
                            class="flex flex-col items-center p-2 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors">
                            <x-fas-list class="w-5 h-5 text-blue-600 mb-1" />
                            <span class="text-xs text-gray-700">Orders</span>
                        </a>
                    @endif

                    @if ($this->canView('hotel'))
                        <a href="/admin/hotel/create"
                            class="flex flex-col items-center p-2 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors">
                            <x-fas-plus class="w-5 h-5 text-green-600 mb-1" />
                            <span class="text-xs text-gray-700">Add Hotel</span>
                        </a>
                    @endif

                    @if ($this->canView('tour-package'))
                        <a href="/admin/tour/create"
                            class="flex flex-col items-center p-2 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors">
                            <x-fas-plus class="w-5 h-5 text-green-600 mb-1" />
                            <span class="text-xs text-gray-700">Add Tour</span>
                        </a>
                    @endif

                    @if ($this->canView('income-expense'))
                        <a href="/admin/income/list"
                            class="flex flex-col items-center p-2 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors">
                            <x-fas-arrow-up class="w-5 h-5 text-green-600 mb-1" />
                            <span class="text-xs text-gray-700">Income</span>
                        </a>
                    @endif

                    @if ($this->canView('income-expense'))
                        <a href="/admin/expense/list"
                            class="flex flex-col items-center p-2 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors">
                            <x-fas-arrow-down class="w-5 h-5 text-red-600 mb-1" />
                            <span class="text-xs text-gray-700">Expense</span>
                        </a>
                    @endif

                    @if ($this->canView('chart-of-account'))
                        <a href="/admin/accounts/chart-of-account"
                            class="flex flex-col items-center p-2 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors">
                            <x-fas-chart-bar class="w-5 h-5 text-indigo-600 mb-1" />
                            <span class="text-xs text-gray-700">Accounts</span>
                        </a>
                    @endif

                    @if ($this->canView('customer'))
                        <a href="/admin/customers" class="flex flex-col items-center p-2 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors">
                            <x-fas-users class="w-5 h-5 text-purple-600 mb-1" />
                            <span class="text-xs text-gray-700">Customers</span>
                        </a>
                    @endif

                    @if ($this->canView('agent'))
                        <a href="/admin/agent/list"
                            class="flex flex-col items-center p-2 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors">
                            <x-fas-user-tie class="w-5 h-5 text-orange-600 mb-1" />
                            <span class="text-xs text-gray-700">Agents</span>
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
