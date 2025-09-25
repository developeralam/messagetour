<?php

use Livewire\Volt\Volt;
use App\Http\Middleware\Admin;
use App\Http\Middleware\Partner;
use App\Http\Middleware\Customer;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PDFController;
use App\Http\Controllers\SocialLoginController;
use App\Http\Controllers\SslCommerzPaymentController;
use App\Http\Controllers\BkashTokenizePaymentController;

Volt::route('/', 'frontend.home');
Volt::route('/customer/dashboard', 'frontend.customer-dashboard');
Volt::route('/privacy/policy', 'frontend.privacypolicy');
Volt::route('/about-us', 'frontend.aboutus');
Volt::route('/contact-us', 'frontend.contactus');
Volt::route('/forgot-password', 'forgot-password')->name('forgetpassword');
Volt::route('/reset-password', 'reset-password')->name('resetpassword');
Volt::route('/blogs', 'frontend.allblog')->name('allblog');
Volt::route('/{slug}/blog-details', 'frontend.blogdetails')->name('blogdetails');
Route::get('/order/{order}/invoice', [PDFController::class, 'order_invoice'])->name('order.invoice');
Route::get('/order/{order}/markup-invoice', [PDFController::class, 'partner_markup_invoice'])
    ->name('order.markup-invoice');

Route::get('/logout', function () {
    auth()->logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();

    return redirect('/');
});

// Tour Routes Start
Volt::route('/tour-search', 'frontend.tour.search')->name('frontend.tour.search');
Volt::route('/tour/{slug}', 'frontend.tour.details');
Volt::route('/tour/booking/{slug}', 'frontend.tour.booking')->name('frontend.tour.booking');
// Tour Routes End

// Hotel Routes
Volt::route('/hotel-search', 'frontend.hotel.search')->name('frontend.hotel.search');
Volt::route('/hotel/{slug}', 'frontend.hotel.details');
Volt::route('/hotel/booking/{slug}', 'frontend.hotel.booking')->name('frontend.hotel.booking');
// Hotel Routes End

// Visa Routes
Volt::route('/visa/search', 'frontend.visa.search')->name('frontend.visa.search');
Volt::route('/visa/{slug}', 'frontend.visa.details')->name('frontend.visa.details');
Volt::route('/visa/booking/{slug}', 'frontend.visa.booking')->name('frontend.visa.booking');
// Visa Routes End

// Gear Routes
Volt::route('/gear-search', 'frontend.gear.search')->name('frontend.gear.search');
Volt::route('/gear/{slug}', 'frontend.gear.details');
Volt::route('/gear/booking/{slug}', 'frontend.gear.booking')->name('frontend.gear.booking');
// Gear Routes End

// Car Rental Routes
Volt::route('/car-rental-search', 'frontend.car.searchlist')->name('frontend.car.search');
Volt::route('/car-rental/booking/{slug}', 'frontend.car.booking')->name('frontend.car.booking');
// Car Rental Routes End

Volt::route('/group-ticket', 'frontend.group-ticket');
Volt::route('/promotion/{slug}', 'frontend.promotion.details');

// Login Routes
Volt::route('/admin/login', 'admin.login')->name('admin.login');
Volt::route('/partner/login', 'partner.login')->name('partner.login');
Volt::route('/customer/login', 'frontend.login')->name('frontend.customerlogin');
Volt::route('/partner/register', 'frontend.partnerregister')->name('frontend.partner.register');
Route::get('/login/{provider}', [SocialLoginController::class, 'redirectToProvider'])->name('social.redirect');
Route::get('/login/{provider}/callback', [SocialLoginController::class, 'handleProviderCallback']);

// Offer Details Routes
Volt::route('/offer/{slug}', 'frontend.offer-details')->name('frontend.offer.details');

// SSLCOMMERZ Start
Route::post('/success', [SslCommerzPaymentController::class, 'success']);
Route::post('/fail', [SslCommerzPaymentController::class, 'fail']);
Route::post('/cancel', [SslCommerzPaymentController::class, 'cancel']);

Route::post('/ipn', [SslCommerzPaymentController::class, 'ipn']);
//SSLCOMMERZ END

Route::get('/bkash/payment', [BkashTokenizePaymentController::class, 'index']);
Route::get('/bkash/create-payment', [BkashTokenizePaymentController::class, 'createPayment'])->name('bkash-create-payment');
Route::get('/bkash/callback', [BkashTokenizePaymentController::class, 'callBack'])->name('bkash-callBack');

// Admin Routes
Route::middleware(Admin::class)->prefix('admin')->name('.admin')->group(function () {
    Route::get('/logout', function () {
        auth()->logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();

        return redirect('/admin/login');
    });

    // Other Transcation Routes
    Route::prefix('others-transcaiton')->group(function () {
        Volt::route('/create', 'admin.otherstranscation.create');
        Volt::route('/list', 'admin.otherstranscation.list');
    });

    Route::prefix('accounts')->group(function () {
        Volt::route('/chart-of-account-category', 'admin.accounts.chartofaccountcategory');
        Volt::route('/chart-of-account', 'admin.accounts.chartofaccount');

        // Report Route
        Route::prefix('reports')->group(function () {
            Volt::route('/trail-balance', 'admin.accounts.report.trailbalance');
            Volt::route('/balance-sheet', 'admin.accounts.report.balancesheet');
            Route::get('ledger-print-report/{category_id}/{from_date}/{to_date}', [PdfController::class, 'chart_of_account'])->name('ledger-print-report');
            Route::get('ledger-category-print-report/{category_id}/{from_date}/{to_date}', [PdfController::class, 'chart_of_account_category'])->name('ledger-category-print-report');
        });
    });

    // Income Expense Routes
    Volt::route('/income/list', 'admin.incomeexpense.income');
    Volt::route('/expense/list', 'admin.incomeexpense.expense');

    // Invoice Routes
    Route::get('/corporate-query/{query}', [PDFController::class, 'customer_corporate_query']);
    Route::get('/order/{order}/invoice', [PDFController::class, 'order_invoice']);

    Volt::route('/dashboard', 'admin.dashboard')->name('dashboard');
    Volt::route('/customers', 'admin.customer')->name('customer');
    Volt::route('/subscribers', 'admin.subscriber')->name('subscriber');
    Volt::route('contact-us', 'admin.contactus');
    Volt::route('reviews', 'admin.review');
    Volt::route('/payment-gateways', 'admin.payment')->name('paymentgateways');
    Volt::route('/commission/manage', 'admin.commissions')->name('commission');
    Volt::route('/coupon-codes', 'admin.coupon')->name('couponcode');
    Volt::route('/order/list', 'admin.orderlist')->name('ordelist');
    Volt::route('/booking/list', 'admin.bookinglist')->name('bookinglist');
    Volt::route('/group-flight/booking/list', 'admin.group-flight-booking')->name('groupflightbookinglist');
    Volt::route('/e-visa/booking/list', 'admin.evisa.bookinglist')->name('evisa.bookinglist');
    Volt::route('/{order}/e-visa/documents', 'admin.evisa.documents')->name('evisa.documents');
    Volt::route('/global-settings', 'admin.globalsetting.global_settings')->name('globalsettings');
    Volt::route('/global-settings/email', 'admin.globalsetting.email')->name('globalsettings.email');
    Volt::route('/payments', 'admin.bankpayment.payment')->name('payment');
    Volt::route('/banks', 'admin.bankpayment.bank')->name('bank');
    Volt::route('/corporate-queries', 'admin.corporatequery')->name('corporatequery');
    Volt::route('/notifications', 'admin.notifications')->name('notifications');
    Volt::route('/aminities', 'admin.aminities')->name('aminities');
    Volt::route('/agent/tour/list', 'admin.tour.agentlist');
    Volt::route('/agent/vehicle/list', 'admin.car.agentlist');
    Volt::route('/agent/hotel/list', 'admin.hotel.agentlist');
    Volt::route('/agent/travel-product/list', 'admin.travelproduct.agentlist');
    Volt::route('/transaction/history', 'admin.transaction-history');
    Volt::route('/deposit-request', 'admin.depositrequest')->name('deposit.request');
    Volt::route('/agent-payment-requests', 'admin.agent-payment-request')->name('agent.payment.requests');

    // Hotel
    Route::prefix('hotel')->group(function () {
        Volt::route('/list', 'admin.hotel.list');
        Volt::route('/create', 'admin.hotel.create');
        Volt::route('/{hotel}/edit', 'admin.hotel.edit');
        Route::prefix('{hotel}/room')->group(function () {
            Volt::route('/list', 'admin.hotel.room.list');
            Volt::route('/create', 'admin.hotel.room.create');
            Volt::route('/{room}/edit', 'admin.hotel.room.edit');
        });
    });

    // Car
    Route::prefix('vehicle')->group(function () {
        Volt::route('/list', 'admin.car.list');
        Volt::route('/create', 'admin.car.create');
        Volt::route('/{car}/edit', 'admin.car.edit');
    });

    // Tour
    Route::prefix('tour')->group(function () {
        Volt::route('/list', 'admin.tour.list');
        Volt::route('/create', 'admin.tour.create');
        Volt::route('/{tour}/edit', 'admin.tour.edit');
    });

    // Visa
    Route::prefix('visa')->group(function () {
        Volt::route('/list', 'admin.visa.list');
        Volt::route('/create', 'admin.visa.create');
        Volt::route('/{visa}/edit', 'admin.visa.edit');
    });

    // Travel Product
    Route::prefix('travel-product')->group(function () {
        Volt::route('/list', 'admin.travelproduct.list');
        Volt::route('/create', 'admin.travelproduct.create');
        Volt::route('/{product}/edit', 'admin.travelproduct.edit');
    });

    // Offer
    Route::prefix('offer')->group(function () {
        Volt::route('/list', 'admin.offer.list');
        Volt::route('/create', 'admin.offer.create');
        Volt::route('/{offer}/edit', 'admin.offer.edit');
    });

    // Offer
    Route::prefix('group-flight')->group(function () {
        Volt::route('/list', 'admin.groupflight.list');
        Volt::route('/create', 'admin.groupflight.create');
        Volt::route('/{groupflight}/edit', 'admin.groupflight.edit');
    });

    // FAQ
    Route::prefix('faq')->group(function () {
        Volt::route('/list', 'admin.faq.list');
        Volt::route('/create', 'admin.faq.create');
        Volt::route('/{faq}/edit', 'admin.faq.edit');
    });

    // Agent
    Route::prefix('agent')->group(function () {
        Volt::route('/list', 'admin.agent.list');
        Volt::route('/sale/report', 'admin.agent.sale-report');
        Volt::route('/create', 'admin.agent.create');
        Volt::route('/{agent}/edit', 'admin.agent.edit');
    });

    // Withdraw
    Route::prefix('withdraw')->group(function () {
        Volt::route('/method', 'admin.withdraw.method');
        Volt::route('/list', 'admin.withdraw.list');
    });

    // General Settings
    Route::prefix('settings')->group(function () {
        Route::prefix('location')->group(function () {
            Volt::route('/country', 'admin.settings.location.country');
            Volt::route('/division', 'admin.settings.location.division');
            Volt::route('/district', 'admin.settings.location.district');
        });
    });

    // Roles
    Route::prefix('role')->group(function () {
        Volt::route('/list', 'admin.role.list');
        Volt::route('/create', 'admin.role.create');
        Volt::route('/{role}/edit', 'admin.role.edit');
    });

    // System Users
    Volt::route('/system-user/list', 'admin.systemuser.list');

    // Blogs
    Route::prefix('blog')->group(function () {
        Volt::route('/list', 'admin.blog.list');
        Volt::route('/create', 'admin.blog.create');
        Volt::route('/{blog}/edit', 'admin.blog.edit');
    });

    Volt::route('/about-us', 'admin.aboutus');
});

// Partner Routes
Route::middleware(Partner::class)->prefix('partner')->name('.partner')->group(function () {
    Route::get('/logout', function () {
        auth()->logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();

        return redirect('/partner/login');
    });

    Volt::route('/dashboard', 'partner.dashboard')->name('dashboard');
    Volt::route('/markup', 'partner.markup')->name('markup');
    Volt::route('/wallet', 'partner.wallet')->name('wallet');
    Volt::route('/deposit-request', 'partner.depositrequest')->name('deposit.request');
    Volt::route('/payment-requests', 'partner.agent-payment-request')->name('payment.requests');
    Volt::route('/profile', 'partner.profile')->name('profile');
    Volt::route('/order/list', 'partner.orderlist')->name('orderlist');
    Volt::route('/booking/list', 'partner.bookinglist')->name('bookinglist');
    Volt::route('/corporate-query/list', 'partner.query.list');
    Route::get('/booking/{order}/invoice', [PDFController::class, 'partner_booking_invoice']);
    Route::get('/order/{order}/invoice', [PDFController::class, 'partner_order_invoice']);
    Route::get('/corporate-query/{query}', [PDFController::class, 'partner_corporate_query']);

    Volt::route('/aminities', 'partner.aminities')->name('aminities');


    // Hotel
    Route::prefix('hotel')->group(function () {
        Volt::route('/list', 'partner.hotel.list');
        Volt::route('/create', 'partner.hotel.create');
        Volt::route('/{hotel}/edit', 'partner.hotel.edit');
        Route::prefix('{hotel}/room')->group(function () {
            Volt::route('/list', 'partner.hotel.room.list');
            Volt::route('/create', 'partner.hotel.room.create');
            Volt::route('/{room}/edit', 'partner.hotel.room.edit');
        });
    });

    // Tour
    Route::prefix('tour')->group(function () {
        Volt::route('/list', 'partner.tour.list');
        Volt::route('/create', 'partner.tour.create');
        Volt::route('/{tour}/edit', 'partner.tour.edit');
    });
    // Car
    Route::prefix('vehicle')->group(function () {
        Volt::route('/list', 'partner.car.list');
        Volt::route('/create', 'partner.car.create');
        Volt::route('/{car}/edit', 'partner.car.edit');
    });
    // Corporate Query
    Route::prefix('corporate-query')->group(function () {
        Volt::route('/{query}/edit', 'partner.query.edit');
    });

    // Travel Product
    Route::prefix('travel-product')->group(function () {
        Volt::route('/list', 'partner.travelproduct.list');
        Volt::route('/create', 'partner.travelproduct.create');
        Volt::route('/{product}/edit', 'partner.travelproduct.edit');
    });
});
Route::middleware(Customer::class)->name('.customer')->group(function () {
    Route::get('/logout', function () {
        auth()->logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();

        return redirect('/customer/login');
    });

    Volt::route('/dashboard', 'customer.dashboard')->name('dashboard');
    Volt::route('/bookings', 'frontend.customer-bookings')->name('bookings');
    Volt::route('/profile', 'customer.profile')->name('profile');
    Volt::route('/change-password', 'customer.change-password')->name('change-password');

    // Legacy routes for backward compatibility
    Volt::route('/my-orders', 'customer.orders')->name('order');
    Volt::route('/my-group-flight-booking', 'customer.booking.group-flight')->name('group.flight');
    Volt::route('/my-hotel-booking', 'customer.booking.hotel')->name('hotel.booking');
    Volt::route('/my-car-booking', 'customer.booking.car')->name('car.booking');
    Volt::route('/my-corporate-query', 'customer.corporate-query.list')->name('corporate.query.list');
    Volt::route('/my-corporate-query/{query}/edit', 'customer.corporate-query.edit')->name('corporate.query.edit');
    Volt::route('/my-visa-booking', 'customer.booking.visa')->name('visa.booking');
    Volt::route('/my-tour-booking', 'customer.booking.tour')->name('tour.booking');
    Route::get('/my-corporate-query/{query}', [PDFController::class, 'customer_corporate_query']);
});
