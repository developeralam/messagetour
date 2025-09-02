<?php

use Carbon\Carbon;
use App\Models\User;
use App\Models\Order;
use App\Enum\UserType;
use App\Models\Coupon;
use Mary\Traits\Toast;
use App\Models\Country;
use App\Enum\AmountType;
use App\Models\District;
use App\Models\Division;
use App\Enum\OrderStatus;
use App\Enum\ProductType;
use App\Models\HotelRoom;
use App\Models\Commission;
use App\Enum\BookingStatus;
use App\Enum\PaymentStatus;
use Livewire\Volt\Component;
use App\Jobs\OrderInvoiceJob;
use App\Enum\CommissionStatus;
use App\Models\PaymentGateway;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;
use App\Models\HotelRoomBooking;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\HotelRoomBookingItems;
use Illuminate\Support\Facades\Cache;
use App\Notifications\OrderNotification;
use App\Services\SSLCOMMERZPaymentService;
use App\Services\BkashPaymentGatewayService;
use Illuminate\Support\Facades\Notification;

new #[Layout('components.layouts.app')] #[Title('Hotel Room Booking')] class extends Component {
    use Toast;
    public $room = [];
    public $hotelRoom;
    public $countries = [];
    public $gateways = [];
    public $name;
    public $email;
    public $phone;
    public $address;
    public $country_id;
    public $division_id;
    public $district_id;
    public $payment_gateway_id;
    public $delivery_charge;
    public $coupon_code;
    public $coupon_amount;
    public $zipcode;
    public $subtotal;
    public $total_amount;
    public $hotel_checkin;
    public $hotel_checkout;
    public $agentDiscount = 0;
    public $discountPercentage = 0;

    public function mount($slug)
    {
        $room = HotelRoom::with('hotel')->where('slug', $slug)->first();
        $this->hotelRoom = $room;
        $this->room = [
            [
                'room_id' => $room->id,
                'hotel_adult' => 1,
                'hotel_child' => 0,
                'hotel_infant' => 0,
            ],
        ];

        $this->name = auth()->user()->name ?? '';
        $this->email = auth()->user()->email ?? '';
        $this->phone = auth()->user()->phone ?? '';
        $this->address = auth()->user()->customer->address ?? '';
        $this->country_id = auth()->user()->customer->country_id ?? '';
        $this->division_id = auth()->user()->customer->division_id ?? '';
        $this->district_id = auth()->user()->customer->district_id ?? '';
        $this->countries = Country::orderBy('name', 'asc')->get();
        $this->hotel_checkin = request()->query('check_in');
        $this->hotel_checkout = request()->query('check_out');

        $query = PaymentGateway::where('is_active', 1);
        if (auth()->user()?->type !== UserType::Agent) {
            $query->where('name', '!=', 'Wallet');
        }
        $this->gateways = $query->get();

        // Default price
        $this->subtotal = $this->hotelRoom->offer_price && $this->hotelRoom->offer_price < $this->hotelRoom->regular_price ? $this->hotelRoom->offer_price : $this->hotelRoom->regular_price;

        // Authenticated agent logic
        if (auth()->check() && auth()->user()->type == UserType::Agent && auth()->user()->agent && auth()->user()->agent->validity && auth()->user()->agent->validity->isFuture()) {
            $agent = auth()->user()->agent;

            // Find commission by matching agent type and product type
            $commission = Commission::where('commission_role', $agent->agent_type)
                ->whereIn('product_type', [ProductType::Hotel, ProductType::All])
                ->where('status', CommissionStatus::Active)
                ->first();
            if ($commission) {
                // Calculate commission based discount
                if ($commission->amount_type == AmountType::Fixed) {
                    $this->agentDiscount = $commission->amount;
                } elseif ($commission->amount_type == AmountType::Percent) {
                    $this->agentDiscount = round(($this->subtotal * $commission->amount) / 100);
                }

                $this->discountPercentage = $commission->amount_type == AmountType::Percent ? $commission->amount : null;

                // Apply discount
                $this->subtotal -= $this->agentDiscount;
            }
        }
        $this->total_amount = $this->subtotal;
    }

    /**
     * Apply the coupon when the button is clicked.
     */
    public function applyCoupon()
    {
        // Check if a coupon code is entered
        if (empty($this->coupon_code)) {
            return $this->resetCoupon('Please enter a coupon code.');
        }

        // Retrieve coupon from cache or database
        $coupon = Coupon::where('code', $this->coupon_code)->first();

        // Use match function to handle different cases
        $message = match (true) {
            !$coupon => 'Invalid Coupon Code',
            Carbon::now()->greaterThan($coupon->expiry_date) => 'This coupon has expired',
            $coupon->max_uses !== null && $coupon->used_count >= $coupon->max_uses => 'This coupon has reached its maximum usage limit',
            default => null,
        };

        // If there's an error, reset coupon and return
        if ($message) {
            return $this->resetCoupon($message);
        }

        // Apply coupon amount if valid
        $this->coupon_amount = $coupon->amount;
        $this->success('Coupon Applied Successfully');
        $this->updateTotalAmount();
    }

    /**
     * Reset coupon amount and display an error message.
     *
     * @param string $message Error message to display
     */
    private function resetCoupon(string $message)
    {
        $this->coupon_amount = 0;
        $this->updateTotalAmount();
        $this->error($message);
    }

    public function updated($property)
    {
        if ($property == 'payment_gateway_id') {
            $gateway = PaymentGateway::find($this->payment_gateway_id);
            $this->delivery_charge = $gateway ? $gateway->charge : 0;
            $this->updateTotalAmount();
        }
    }

    public function updateTotalAmount()
    {
        $this->total_amount = max(0, $this->subtotal - $this->coupon_amount + $this->delivery_charge);
    }

    public function storeHotelBooking()
    {
        // Retrieve the coupon if applicable
        $coupon = $this->coupon_code ? Coupon::where('code', $this->coupon_code)->first() : null;

        DB::beginTransaction();
        try {
            $order = Order::create([
                'user_id' => auth()->id(),
                'name' => $this->name,
                'email' => $this->email,
                'phone' => $this->phone,
                'address' => $this->address,
                'country_id' => $this->country_id,
                'division_id' => $this->division_id,
                'district_id' => $this->district_id,
                'zipcode' => $this->zipcode,
                'coupon_id' => $coupon?->id,
                'coupon_amount' => $this->coupon_amount,
                'subtotal' => $this->subtotal,
                'delivery_charge' => $this->delivery_charge,
                'tran_id' => uniqid('flyvaly_'),
                'total_amount' => $this->total_amount,
                'status' => OrderStatus::Pending,
                'payment_gateway_id' => $this->payment_gateway_id,
                'payment_status' => PaymentStatus::Unpaid,
            ]);

            // Increment coupon's used_count if coupon exists
            if ($coupon && $coupon->max_uses !== null && $coupon->used_count < $coupon->max_uses) {
                $coupon->increment('used_count');
            }

            // Create the hotel room booking associated with the order
            $hotelBooking = HotelRoomBooking::create([
                'order_id' => $order->id,
                'check_in' => $this->hotel_checkin,
                'check_out' => $this->hotel_checkout,
                'status' => BookingStatus::Requested,
            ]);

            // Store hotel room booking items
            foreach ($this->room as $room) {
                HotelRoomBookingItems::create([
                    'hotel_room_booking_id' => $hotelBooking->id,
                    'room_id' => $room['room_id'],
                    'hotel_adult' => $room['hotel_adult'] ?? 0,
                    'hotel_child' => $room['hotel_child'] ?? 0,
                    'hotel_infant' => $room['hotel_infant'] ?? 0,
                ]);
            }

            OrderInvoiceJob::dispatch(auth()->user()->email, $order);
            $message = auth()->user()->name . ' has booked ' . $this->hotelRoom->room_no . ' in ' . $this->hotelRoom->hotel->name;
            $roomcreator = User::find($this->hotelRoom->created_by); // Find the hotel room creator
            $admins = User::where('type', UserType::Admin)->get();
            Notification::send($admins, new OrderNotification($order, $message));

            // Notify the hotel room creator only if they exist and are NOT an admin
            if ($roomcreator && $roomcreator->type !== UserType::Admin) {
                $roomcreator->notify(new OrderNotification($order, $message));
            }

            // **Associate the hotel booking with the order using Eloquent relationship**
            $order->sourceable()->associate($hotelBooking);
            $order->save();
            DB::commit();
            if ($order->payment_gateway_id == 3) {
                $sslService = new SSLCOMMERZPaymentService();
                $response = $sslService->makePayment($order);

                // Redirect to gateway
                $url = json_decode($response)->data;
                return redirect()->away($url);
            }

            if ($order->payment_gateway_id == 1) {
                return app(BkashPaymentGatewayService::class)->makePayment($order);
            }

            if ($order->payment_gateway_id == 4) {
                $this->redirectRoute('order.invoice', ['order' => $order->id]);
            }

            if ($order->payment_gateway_id == 5) {
                $bookingAgent = auth()->user()->agent;

                if (!$bookingAgent || $bookingAgent->wallet < $order->total_amount) {
                    DB::rollBack();
                    return $this->error('Insufficient Wallet Balance');
                }

                // Deduct total_amount from booking agent
                $bookingAgent->wallet -= $order->total_amount;
                $bookingAgent->save();

                // Mark order as paid
                $order->payment_status = PaymentStatus::Paid;
                $order->status = OrderStatus::Confirmed;
                $order->save();

                // Check if hotelRoom creator is a different, valid agent
                $tourCreator = User::find($this->hotelRoom->created_by);
                if ($tourCreator && $tourCreator->type == UserType::Agent && $tourCreator->id !== auth()->id() && $tourCreator->agent && $tourCreator->agent->validity && $tourCreator->agent->validity->isFuture()) {
                    $creatorAgent = $tourCreator->agent;

                    // Original total: regular price + coupon + delivery
                    $basePrice = $this->hotelRoom->offer_price && $this->hotelRoom->offer_price < $this->hotelRoom->regular_price ? $this->hotelRoom->offer_price : $this->hotelRoom->regular_price;

                    $originalTotal = $basePrice - ($order->coupon_amount ?? 0);

                    $creatorAgent->wallet += $originalTotal;
                    $creatorAgent->save();

                    // (Optional) log the transaction to a wallet_commissions table
                }

                $this->redirectRoute('order.invoice', ['order' => $order->id]);
            }

            $this->success('Hotel Booking Successfully Placed!');
        } catch (\Throwable $th) {
            DB::rollBack();
            $this->error(env('APP_DEBUG', false) ? $th->getMessage() : 'Something went wrong');
        }
    }

    public function with(): array
    {
        return [
            'divisions' => Division::when($this->country_id, function ($q) {
                $q->where('country_id', intval($this->country_id));
            })->get(),
            'districts' => District::when($this->division_id, function ($q) {
                $q->where('division_id', intval($this->division_id));
            })->get(),
        ];
    }
}; ?>

<div class="bg-gray-100 px-5 mx-auto md:px-8 xl:px-0">

    @section('booking')
        <div class="flex items-center space-x-2 justify-center text-white mt-10 font-extrabold text-xl">
            <a href="/" class="hover:text-green-700 hover:underline">Home</a>
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-green-800" fill="none" stroke="currentColor"
                stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" aria-hidden="true">
                <path d="M9 18l6-6-6-6"></path>
            </svg>
            <span>Hotel Booking</span>
        </div>
    @endsection

    <section class="pt-8 md:pt-12 lg:pt-16">
        <div class="max-w-6xl mx-auto bg-white">
            <div class="grid grid-cols-1 sm:grid-cols-12 items-start gap-4 border rounded-md shadow-lg mb-2 p-2 sm:p-3">
                <!-- Image Section -->
                <div
                    class="col-span-1 sm:col-span-4 md:col-span-3 h-48 sm:h-full rounded-md overflow-hidden shadow-md flex items-center justify-center">
                    <img class="object-cover w-full h-full max-h-48 sm:max-h-full"
                        src="{{ $hotelRoom->thumbnail_link ?? '' }}" alt="">
                </div>
                <!-- Details Section -->
                <div class="col-span-1 sm:col-span-8 md:col-span-9 rounded-lg shadow-lg border p-3 sm:p-4 bg-gray-100">
                    <!-- Hotel Name and Room No Section -->
                    <div class="mb-2">
                        <p class="text-xs sm:text-sm md:text-lg font-semibold">
                            Hotel:
                            <span>{{ $hotelRoom->hotel->name }}</span>
                        </p>
                        <p class="text-xs sm:text-sm md:text-md mt-1">
                            <span class="font-semibold">Room No:</span>
                            <span>{{ $hotelRoom->room_no }}</span>
                        </p>
                    </div>

                    <!-- Hotel Location Section -->
                    <div class="mb-2">
                        <p class="text-xs sm:text-sm font-medium flex items-center gap-x-2">
                            <x-icon name="fas.location-dot" class="text-[#f73]" />
                            {{ $hotelRoom->hotel->address }}
                        </p>
                    </div>

                    <!-- Price and Discount Section -->
                    <div class="mb-2">
                        <!-- Original Price with Discount -->
                        @if (auth()->check() && auth()->user()->type == \App\Enum\UserType::Agent && isset($agentDiscount) && $agentDiscount > 0)
                            <p class="text-sm md:text-base flex items-center gap-x-2">
                                <del class="text-sm text-red-500">BDT
                                    {{ number_format($hotelRoom->regular_price) }}</del>
                            </p>
                            <div class="flex items-center">
                                <img class="w-6 h-6" src="{{ asset('images/discount-mono.svg') }}" alt="Discount Icon">
                                <p class="text-sm md:text-base flex items-center">
                                    <span class="text-[#f73] text-xs font-bold">
                                        @if ($discountPercentage)
                                            {{ $discountPercentage }}% OFF
                                        @else
                                            BDT {{ number_format($agentDiscount) }} OFF
                                        @endif
                                    </span>
                                    <span class="font-bold ml-1">BDT {{ number_format($subtotal) }}</span>
                                    <span class="text-xs ml-1">/ Per Person</span>
                                </p>
                            </div>
                        @elseif ($hotelRoom->offer_price && $hotelRoom->offer_price < $hotelRoom->regular_price)
                            <p class="text-sm md:text-base flex items-center gap-x-2">
                                @php
                                    $discountPercentage = round(
                                        (($hotelRoom->regular_price - $hotelRoom->offer_price) /
                                            $hotelRoom->regular_price) *
                                            100,
                                    );
                                @endphp
                                <del class="text-sm text-red-500">BDT
                                    {{ number_format($hotelRoom->regular_price) }}</del>
                            </p>
                            <div class="flex items-center">
                                <img class="w-6 h-6" src="{{ asset('images/discount-mono.svg') }}" alt="Discount Icon">
                                <p class="text-sm md:text-base flex items-center">
                                    <span class="text-[#f73] text-xs font-bold">{{ $discountPercentage }}% OFF</span>
                                    <span class="font-bold ml-1">BDT
                                        {{ number_format($hotelRoom->offer_price) }}</span>
                                    <span class="text-xs ml-1">/ Per Person</span>
                                </p>
                            </div>
                        @else
                            <p class="text-sm md:text-base flex items-center">
                                <span class="font-bold ml-1">BDT {{ number_format($hotelRoom->regular_price) }}</span>
                                <span class="text-xs ml-1">/ Per Person</span>
                            </p>
                        @endif
                    </div>

                    <!-- Price Includes Section -->
                    <div class="text-xs md:text-sm font-semibold">
                        <p class="whitespace-nowrap text-red-500">
                            <span class="text-red-500">*</span> Price includes VAT & Tax
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="mt-6 max-w-6xl mx-auto bg-gray-100 py-5">
        <x-form wire:submit="storeHotelBooking">
            <div class="flex flex-col lg:flex-row gap-4 mb-6">
                <!-- Billing Details -->
                <div class="w-full lg:w-1/2">
                    <x-card x-cloak class="py-6 h-full overflow-hidden shadow-md">
                        <div class="card-header">
                            <h3 class="text-lg md:text-xl text-center font-bold mb-4">Billing Details</h3>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4 px-2 md:px-4">
                            <!-- Name -->
                            <x-input class="custome-input-field" wire:model="name" label="Name" placeholder="Name"
                                required readonly />

                            <!-- Email -->
                            <x-input class="custome-input-field" type="email" wire:model="email" label="Email"
                                placeholder="Email" required readonly />

                            <!-- Phone -->
                            <x-input class="custome-input-field" wire:model="phone" label="Phone" placeholder="Phone"
                                required />

                            <!-- Address -->
                            <x-input class="custome-input-field" wire:model="address" label="Address"
                                placeholder="Address" required />

                            <!-- Country -->
                            <x-choices class="custom-select-field" wire:model.live="country_id" :options="$countries"
                                label="Country" placeholder="Select Country" single required />

                            <!-- Division -->
                            <x-choices class="custom-select-field" wire:model.live="division_id" :options="$divisions"
                                label="Division" placeholder="Select Division" single required />

                            <!-- District -->
                            <x-choices class="custom-select-field" wire:model.live="district_id" :options="$districts"
                                label="District" placeholder="Select District" single required />

                            <!-- Zipcode -->
                            <x-input class="custome-input-field" wire:model="zipcode" label="Zipcode"
                                placeholder="Zipcode" />
                        </div>

                        <!-- Additional Information -->
                        <div class="px-2 md:px-4">
                            <x-textarea class="custome-input-field" wire:model="additional_information"
                                label="Additional Information" placeholder="Additional Information" />
                        </div>
                    </x-card>
                </div>
                <!-- Payment Method & Summary -->
                <div class="w-full lg:w-1/2 mt-4 lg:mt-0">
                    <div class="card bg-white rounded-md overflow-hidden shadow-md h-full flex flex-col">
                        <div class="card-header">
                            <h3 class="md:text-xl text-lg text-center font-bold mt-4">Payment Method</h3>
                        </div>
                        <div class="px-2 md:px-6 py-3 flex flex-col gap-y-3 flex-1">
                            <div class="payment_method">
                                <h5 class="p-2 bg-light font-bold">Select a payment method</h5>
                                @foreach ($gateways as $gateway)
                                    <div class="form-check mb-2">
                                        <input class="form-check-input custom-radio-dot" type="radio"
                                            id="gateway-{{ $gateway->id }}" value="{{ $gateway->id }}"
                                            wire:model.live="payment_gateway_id">
                                        <label class="form-check-label text-sm"
                                            for="gateway-{{ $gateway->id }}">{{ $gateway->name }}
                                        </label>
                                    </div>
                                @endforeach
                            </div>
                            <div class="flex flex-col sm:flex-row gap-x-2 gap-y-2 items-stretch">
                                <!-- Input Field -->
                                <div class="flex-1 w-full">
                                    <label for="coupon_code" class="block text-sm font-medium text-gray-700">Coupon
                                        Code</label>
                                    <input type="text" id="coupon_code" wire:model="coupon_code"
                                        class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none"
                                        placeholder="Apply Coupon Code Here" />
                                </div>

                                <!-- Apply Button -->
                                <button wire:click="applyCoupon" type="button"
                                    class="bg-green-500 text-white px-6 py-2 rounded-md shadow-md hover:bg-green-600 focus:outline-none sm:mt-6 mt-2 w-full sm:w-auto">
                                    Apply
                                </button>
                            </div>

                            <div class="summery mt-3 text-sm flex flex-col gap-y-1">
                                <div class="flex items-center justify-between">
                                    <span class="font-semibold">Sub Total :</span>
                                    <span>{{ $subtotal ?? '' }}</span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="font-semibold">Discount :</span>
                                    <span>{{ $coupon_amount ?? '' }}</span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="font-semibold">Gateway Charge :</span>
                                    <span>{{ $delivery_charge ?? '' }}</span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="font-semibold">Total Amount :</span>
                                    <span>{{ $total_amount ?? '' }}</span>
                                </div>
                            </div>
                            <div x-data="{ accepted: false }">
                                <label class="flex items-center gap-x-1 text-sm whitespace-nowrap mt-2 cursor-pointer">
                                    <input type="checkbox" name="termsCondition" id="flexCheckDefaultf1"
                                        x-model="accepted" class="custom-checkbox">
                                    <span>I read and accept all</span>
                                    <a href="#" class="text-green-500 hover:underline" @click.prevent>Terms and
                                        conditions</a>
                                </label>

                                <button type="submit" :disabled="!accepted"
                                    class="bg-green-500 text-white px-6 py-2 rounded-md shadow-md mt-3 w-full"
                                    :class="accepted ? 'hover:bg-green-600 focus:ring-2 focus:ring-green-500' :
                                        'opacity-50 cursor-not-allowed'">
                                    Place Order
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </x-form>
    </section>
</div>
