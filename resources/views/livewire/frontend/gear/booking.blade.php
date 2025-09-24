<?php

use Carbon\Carbon;
use App\Models\Bank;
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
use App\Models\Commission;
use App\Enum\CountryStatus;
use App\Enum\PaymentStatus;
use App\Enum\ShippingMethod;
use Livewire\Volt\Component;
use App\Jobs\OrderInvoiceJob;
use App\Jobs\SendCustomerMailJob;
use App\Jobs\SendAgentMailJob;
use App\Models\TravelProduct;
use App\Enum\CommissionStatus;
use App\Models\PaymentGateway;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\DB;
use App\Models\TravelProductBooking;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use App\Notifications\OrderNotification;
use App\Traits\InteractsWithImageUploads;
use App\Services\SSLCOMMERZPaymentService;
use App\Services\BkashPaymentGatewayService;
use Illuminate\Support\Facades\Notification;

new #[Layout('components.layouts.app')] #[Title('Gear Booking')] class extends Component {
    use Toast, InteractsWithImageUploads;
    public $gear;
    public $countries = [];
    public $gateways = [];
    public $shippingMethods = [];
    public $name;
    public $email;
    public $phone;
    public $address;
    public $country_id;
    public $division_id;
    public $district_id;
    public $payment_gateway_id;
    public $delivery_charge = 0;
    public $shipping_charge = 100;
    public $coupon_code;
    public $coupon_amount;
    public $zipcode;
    public $subtotal;
    public $total_amount;
    public $shipping_method = 1;
    public $qty = 1;
    public $banks = [];
    public $selected_bank_id;
    public $payment_receipt;
    public $divisions = [];
    public $districts = [];

    public function mount($slug)
    {
        $gearSlug = TravelProduct::where('slug', $slug)->first();
        $this->gear = $gearSlug;
        $this->name = auth()->user()->name;
        $this->email = auth()->user()->email;
        $this->phone = auth()->user()->phone;
        $this->address = auth()->user()->customer->address ?? '';
        $this->country_id = auth()->user()->customer->country_id ?? '';
        $this->division_id = auth()->user()->customer->division_id ?? '';
        $this->district_id = auth()->user()->customer->district_id ?? '';
        // Fetch available shipping methods from Enum
        $this->shippingMethods = ShippingMethod::getMethods();

        // Calculate initial subtotal and total amount
        $this->countries = Country::orderBy('name', 'asc')->get();
        $this->banks = Bank::with('country')->orderBy('name', 'asc')->get();
        $query = PaymentGateway::where('is_active', 1);
        if (auth()->user()?->type !== UserType::Agent) {
            $query->where('name', '!=', 'Wallet');
            $query->where('name', '!=', 'Manual');
        }
        $this->gateways = $query->get();

        // Determine unit price (for 1 qty first)
        $unitPrice = $this->gear->offer_price && $this->gear->offer_price < $this->gear->regular_price ? $this->gear->offer_price : $this->gear->regular_price;

        // Default subtotal
        $this->subtotal = $unitPrice;

        // Agent-specific commission discount
        if (auth()->check() && auth()->user()->type == UserType::Agent && auth()->user()->agent && auth()->user()->agent->validity && auth()->user()->agent->validity->isFuture()) {
            $agent = auth()->user()->agent;

            $commission = Commission::where('commission_role', $agent->agent_type)
                ->whereIn('product_type', [ProductType::Gear, ProductType::All])
                ->where('status', CommissionStatus::Active)
                ->first();

            if ($commission) {
                if ($commission->amount_type == AmountType::Fixed) {
                    $this->subtotal -= $commission->amount;
                } elseif ($commission->amount_type == AmountType::Percent) {
                    $this->subtotal -= round(($unitPrice * $commission->amount) / 100);
                }

                // Prevent negative subtotal
                $this->subtotal = max(0, $this->subtotal);
            }
        }

        $this->shippingMethods = ShippingMethod::getMethods();
        $this->countries = Country::orderBy('name', 'asc')->get();
        $this->divisions = Division::when($this->country_id, function ($q) {
            $q->where('country_id', intval($this->country_id));
        })->get();
        $this->districts = District::when($this->division_id, function ($q) {
            $q->where('division_id', intval($this->division_id));
        })->get();

        // Payment gateways (hide wallet for non-agents)
        $query = PaymentGateway::where('is_active', 1);
        if (auth()->user()?->type !== UserType::Agent) {
            $query->where('name', '!=', 'Wallet');
            $query->where('name', '!=', 'Manual');
        }
        $this->gateways = $query->get();
        $this->updateTotalAmount();
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

    public function countrySearch(string $search = '')
    {
        $searchTerm = '%' . $search . '%';

        $citizen = Country::where('status', CountryStatus::Active)->where('name', 'like', $searchTerm)->limit(5)->get();

        $this->countries = $citizen;
    }
    public function divisionSearch(string $search = '')
    {
        $searchTerm = '%' . $search . '%';
        $divisions = Division::where('country_id', $this->country_id)->where('name', 'like', $searchTerm)->limit(5)->get();

        $this->divisions = $divisions;
    }

    public function districtSearch(string $search = '')
    {
        $searchTerm = '%' . $search . '%';
        $districts = District::where('division_id', $this->division_id)->where('name', 'like', $searchTerm)->limit(5)->get();

        $this->districts = $districts;
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

    /**
     * Handles property updates dynamically using match().
     * - Updates payment gateway details when 'payment_gateway_id' changes.
     * - Updates subtotal and shipping charges when 'qty' or 'shipping_method' changes.
     * - Updates divisions and districts when country_id or division_id changes.
     */
    public function updated($property)
    {
        match ($property) {
            'payment_gateway_id' => $this->updatePaymentGateway(),
            'qty', 'shipping_method' => $this->updateSubtotal(),
            'country_id' => $this->updateDivisions(),
            'division_id' => $this->updateDistricts(),
            default => null,
        };
    }

    /**
     * - Calls updateTotalAmount() to refresh the total price.
     */
    private function updatePaymentGateway()
    {
        $gateway = PaymentGateway::find($this->payment_gateway_id);

        if ($gateway) {
            $this->delivery_charge = ($this->subtotal * $gateway->charge) / 100;
        } else {
            $this->delivery_charge = 0;
        }

        $this->updateTotalAmount();
    }

    /**
     * Update divisions when country_id changes
     */
    private function updateDivisions()
    {
        $this->divisions = Division::when($this->country_id, function ($q) {
            $q->where('country_id', intval($this->country_id));
        })->get();

        // Reset division_id and districts when country changes
        $this->division_id = null;
        $this->districts = collect();
    }

    /**
     * Update districts when division_id changes
     */
    private function updateDistricts()
    {
        $this->districts = District::when($this->division_id, function ($q) {
            $q->where('division_id', intval($this->division_id));
        })->get();
    }

    /**
     * - Calls updateTotalAmount() to recalculate the final total.
     */

    public function updateSubtotal()
    {
        // Ensure quantity is at least 1
        $this->qty = max(1, $this->qty);

        // Determine base unit price (offer or regular)
        $unitPrice = $this->gear->offer_price && $this->gear->offer_price < $this->gear->regular_price ? $this->gear->offer_price : $this->gear->regular_price;

        // Apply agent-specific commission discount
        if (auth()->check() && auth()->user()->type == UserType::Agent && auth()->user()->agent && auth()->user()->agent->validity && auth()->user()->agent->validity->isFuture()) {
            $agent = auth()->user()->agent;

            $commission = Commission::where('commission_role', $agent->agent_type)
                ->whereIn('product_type', [ProductType::Gear, ProductType::All])
                ->where('status', CommissionStatus::Active)
                ->first();

            if ($commission) {
                if ($commission->amount_type === AmountType::Fixed) {
                    $unitPrice -= $commission->amount;
                } elseif ($commission->amount_type === AmountType::Percent) {
                    $unitPrice -= round(($unitPrice * $commission->amount) / 100);
                }

                $unitPrice = max(0, $unitPrice); // prevent negative unit price
            }
        }

        // Final subtotal based on quantity
        $this->subtotal = $unitPrice * $this->qty;

        // Shipping charge
        $this->shipping_charge = ShippingMethod::getChargeById($this->shipping_method);

        // Recalculate total
        $this->updateTotalAmount();
    }

    /**
     * Recalculates the total amount based on:
     * - Subtotal price
     * - Applied coupon discounts
     * - Delivery charges from the selected payment gateway
     * - Shipping charges based on selected shipping method
     * Ensures the total is never negative.
     */
    public function updateTotalAmount()
    {
        $this->total_amount = max(0, $this->subtotal - $this->coupon_amount + $this->delivery_charge + $this->shipping_charge);
    }

    /**
     * Increment Qty and update subtotal
     */
    public function incrementQty()
    {
        $this->qty++;
        $this->updateSubtotal();
    }

    /**
     * Decrement Qty and update subtotal
     */
    public function decrementQty()
    {
        if ($this->qty > 1) {
            $this->qty--;
            $this->updateSubtotal();
        }
    }

    /**
     *Store Order and TravelProductBooking
     */
    public function storeGearBooking()
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
                'shipping_method' => $this->shipping_method,
                'shipping_charge' => $this->shipping_charge,
                'total_amount' => $this->total_amount,
                'tran_id' => uniqid('flyvaly_'),
                'status' => OrderStatus::Pending,
                'payment_gateway_id' => $this->payment_gateway_id,
                'payment_status' => PaymentStatus::Unpaid,
            ]);

            // Increment coupon's used_count if coupon exists
            if ($coupon && $coupon->max_uses !== null && $coupon->used_count < $coupon->max_uses) {
                $coupon->increment('used_count');
            }

            // Create the gear booking associated with the order
            $gearBooking = TravelProductBooking::create([
                'order_id' => $order->id,
                'travel_product_id' => $this->gear->id,
                'qty' => $this->qty,
            ]);

            // **Update the stock of the travel product**
            $travelProduct = TravelProduct::find($this->gear->id);
            if ($travelProduct) {
                $travelProduct->decrement('stock', $this->qty); // Decrease the stock by the booked quantity
            }

            // **Associate the gear booking with the order using Eloquent relationship**
            $order->sourceable()->associate($gearBooking);
            $order->save();

            // Send invoice email to customer
            OrderInvoiceJob::dispatch(auth()->user()->email, $order);

            // Send booking confirmation email to customer
            $customerSubject = 'Gear Booking Confirmation - ' . $this->gear->title;
            $customerEmailBody = 'Dear ' . auth()->user()->name . ',<br><br>' . 'Thank you for order the gear: <strong>' . $this->gear->title . '</strong><br><br>' . 'Your order ID: <strong>' . $order->tran_id . '</strong><br>' . 'Quantity: <strong>' . $this->qty . '</strong><br>' . 'Total Amount: <strong>৳' . number_format($order->total_amount, 2) . '</strong><br><br>' . 'We will contact you soon with further details.<br><br>' . 'Best regards,<br>FlyValy Team';

            SendCustomerMailJob::dispatch(
                auth()->user()->email,
                [], // No BCC for customer email
                $customerSubject,
                $customerEmailBody,
            );

            // Send notifications and emails
            $admins = User::where('type', UserType::Admin)->get();
            $gearCreator = User::find($this->gear->created_by); // Find the gear creator
            $message = $this->gear->title . ' booked by ' . auth()->user()->name;

            // Notify all admins
            Notification::send($admins, new OrderNotification($order, $message));

            // Send email to gear creator (partner) if they exist and are NOT an admin
            if ($gearCreator && $gearCreator->type !== UserType::Admin) {
                $gearCreator->notify(new OrderNotification($order, $message));

                // Send email to gear creator
                $partnerSubject = 'New Gear Booking - ' . $this->gear->title;
                $partnerEmailBody = 'Dear ' . $gearCreator->name . ',<br><br>' . 'Great news! Your travel product <strong>' . $this->gear->title . '</strong> has been booked by <strong>' . auth()->user()->name . '</strong>.<br><br>' . 'Booking Details:<br>' . 'Order ID: <strong>' . $order->tran_id . '</strong><br>' . 'Customer: <strong>' . auth()->user()->name . '</strong><br>' . 'Email: <strong>' . auth()->user()->email . '</strong><br>' . 'Phone: <strong>' . $order->phone . '</strong><br>' . 'Quantity: <strong>' . $this->qty . '</strong><br>' . 'Total Amount: <strong>৳' . number_format($order->total_amount, 2) . '</strong><br><br>' . 'Please contact the customer for further arrangements.<br><br>' . 'Best regards,<br>FlyValy Team';

                SendAgentMailJob::dispatch(
                    $gearCreator->email,
                    [], // No BCC for partner email
                    $partnerSubject,
                    $partnerEmailBody,
                );
            }
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
                    return $this->error('Insufficient wallet balance');
                }

                // Deduct total from booking agent
                $bookingAgent->wallet -= $order->total_amount;
                $bookingAgent->save();

                // Mark order as paid
                $order->payment_status = PaymentStatus::Paid;
                $order->status = OrderStatus::Confirmed;
                $order->save();

                // Credit to gear creator agent (if different and valid)
                $gearCreator = User::find($this->gear->created_by);
                if ($gearCreator && $gearCreator->type == UserType::Agent && $gearCreator->id !== auth()->id() && $gearCreator->agent && $gearCreator->agent->validity && $gearCreator->agent->validity->isFuture()) {
                    $creatorAgent = $gearCreator->agent;

                    // Determine base unit price
                    $unitPrice = $this->gear->offer_price && $this->gear->offer_price < $this->gear->regular_price ? $this->gear->offer_price : $this->gear->regular_price;

                    $basePrice = $unitPrice * $this->qty;

                    // Apply coupon discount
                    $adjustedPrice = max(0, $basePrice - ($order->coupon_amount ?? 0));

                    // Add delivery and shipping charges
                    $originalTotal = $adjustedPrice + ($order->delivery_charge ?? 0) + ($order->shipping_charge ?? 0);

                    // Add commission (if exists)
                    $commissionAmount = 0;
                    $commission = Commission::where('commission_role', $creatorAgent->agent_type)
                        ->whereIn('product_type', [ProductType::Gear, ProductType::All])
                        ->where('status', CommissionStatus::Active)
                        ->first();

                    if ($commission) {
                        if ($commission->amount_type == AmountType::Fixed) {
                            $commissionAmount = $commission->amount;
                        } elseif ($commission->amount_type == AmountType::Percent) {
                            $commissionAmount = round(($originalTotal * $commission->amount) / 100);
                        }
                    }

                    // Final credit = original total + commission
                    $creatorAgent->wallet += $originalTotal + $commissionAmount;
                    $creatorAgent->save();
                }

                // Redirect to invoice
                return $this->redirectRoute('order.invoice', ['order' => $order->id]);
            }

            if ($order->payment_gateway_id == 6) {
                // Handle manual payment
                // Store payment receipt and bank information

                $storedThumbnailPath = null;
                if ($this->payment_receipt) {
                    $apiKey = env('IMGBB_API_KEY') ?? '30624d49f53abfcec50351257ad0ce43';
                    $uploadUrl = env('IMGBB_UPLOAD_URL') ?? 'https://api.imgbb.com/1/upload';

                    // Convert Livewire file to base64
                    $imageData = base64_encode(file_get_contents($this->payment_receipt->path()));

                    $response = Http::asForm()->post($uploadUrl, [
                        'key' => $apiKey,
                        'image' => $imageData,
                    ]);

                    $result = $response->json();

                    if (isset($result['data']['url'])) {
                        // ✅ Replace file path with actual ImgBB URL
                        $storedThumbnailPath = $result['data']['url'];
                    } else {
                        throw new \Exception('ImgBB upload failed for payment receipt: ' . json_encode($result));
                    }
                }
                $order->payment_receipt = $storedThumbnailPath;

                $order->selected_bank_id = $this->selected_bank_id;
                $order->save();

                $this->success('Gear booking submitted successfully! Your payment will be verified manually.');
                $this->redirectRoute('order.invoice', ['order' => $order->id]);
            }

            $this->success('Gear Booking Successfully');
        } catch (\Throwable $th) {
            DB::rollBack();
            $this->error(env('APP_DEBUG', false) ? $th->getMessage() : 'Something went wrong');
        }
    }

    public function with(): array
    {
        return [
            'divisions' => $this->divisions,
            'districts' => $this->districts,
        ];
    }
}; ?>

<div class="bg-gray-100 px-5 xl:px-0">
    @section('booking')
        <div class="flex items-center space-x-2 justify-center text-white mt-10 font-extrabold text-xl">
            <a href="/" class="hover:text-green-600 hover:underline">Home</a>
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-green-700" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                stroke-linejoin="round" viewBox="0 0 24 24" aria-hidden="true">
                <path d="M9 18l6-6-6-6"></path>
            </svg>
            <span>Product Purchase</span>
        </div>
    @endsection

    <section class="pt-6">
        <div class="max-w-6xl md:mx-auto mx-3 bg-white">
            <div class="grid grid-cols-12 items-start gap-x-4 gap-y-3 border rounded-md shadow-lg mb-2 p-3">
                <!-- Gear Image -->
                <div class="col-span-12 md:col-span-3 h-full rounded-md overflow-hidden shadow-md">
                    <img class="object-cover h-full w-full" src="{{ $gear->thumbnail ?? '' }}" alt="">
                </div>

                <!-- Gear Details -->
                <div class="col-span-12 md:col-span-9 px-4 py-3 rounded-md shadow-md bg-gray-100 border p-3">
                    <div class="flex flex-col md:flex-row justify-between items-start gap-6">
                        <!-- Left: Title, Brand, SKU -->
                        <div class="flex-1 space-y-1">
                            <h2 class="text-lg md:text-xl font-semibold text-gray-800">{{ $gear->title ?? '' }}</h2>
                            <p class="text-sm text-gray-600">Brand: {{ $gear->brand ?? 'N/A' }}</p>
                            <p class="text-sm text-gray-600">SKU: {{ $gear->sku ?? 'N/A' }}</p>
                        </div>

                        <!-- Right: Quantity & Price -->
                        <div class="flex flex-col items-center md:items-end justify-center gap-3 w-full md:w-auto">
                            <!-- Quantity Selector -->
                            <div class="flex flex-col items-center justify-center gap-2">
                                <label class="text-sm font-semibold text-gray-600">Quantity</label>
                                <div class="flex items-center border border-green-500 rounded-md overflow-hidden">
                                    <button wire:click="decrementQty" class="bg-green-500 hover:bg-green-600 text-white px-2 py-1">
                                        <x-icon name="o-minus" class="w-4 h-4" />
                                    </button>
                                    <input type="number" wire:model.live="qty"
                                        class="w-12 text-center md:pl-3 py-1 text-sm border-l border-r border-green-500 focus:outline-none focus:ring-0"
                                        min="1" />
                                    <button wire:click="incrementQty" class="bg-green-500 hover:bg-green-600 text-white px-2 py-1">
                                        <x-icon name="o-plus" class="w-4 h-4" />
                                    </button>
                                </div>
                            </div>

                            <!-- Price Details -->
                            <div class="text-center md:text-right">
                                @php
                                    $hasOffer = $gear->offer_price && $gear->offer_price < $gear->regular_price;
                                    $regularPrice = $gear->regular_price;
                                    $offerPrice = $gear->offer_price;
                                    $baseUnitPrice = $hasOffer ? $offerPrice : $regularPrice;

                                    $originalSubtotal = $regularPrice * $qty;
                                    $baseSubtotal = $baseUnitPrice * $qty;

                                    $agentDiscount = $baseSubtotal - $subtotal;

                                    $isAgent = auth()->check() && auth()->user()->type == \App\Enum\UserType::Agent;
                                    $hasAgentDiscount = $isAgent && $agentDiscount > 0;

                                    $finalDiscount = $originalSubtotal - $subtotal;
                                    $finalDiscountPercentage = $finalDiscount > 0 ? round(($finalDiscount / $originalSubtotal) * 100) : 0;
                                @endphp

                                <!-- Show regular price if discount available -->
                                @if ($finalDiscount > 0)
                                    <p class="text-sm md:text-base">
                                        <del class="text-sm text-red-500">BDT
                                            {{ number_format($originalSubtotal) }}</del>
                                    </p>
                                @endif

                                <!-- Final price section -->
                                <div class="flex items-center justify-end gap-1">
                                    @if ($finalDiscount > 0)
                                        <img loading="lazy" class="w-5 h-5" src="{{ asset('images/discount-mono.svg') }}" alt="Discount Icon">
                                    @endif

                                    <p class="text-base flex items-center gap-1">
                                        @if ($finalDiscount > 0)
                                            <span class="text-[#f73] text-xs font-semibold">
                                                {{ $finalDiscountPercentage }}% OFF
                                            </span>
                                        @endif

                                        <span class="font-bold text-lg">
                                            BDT {{ number_format($subtotal) }}
                                        </span>
                                    </p>
                                </div>

                                <!-- Price Includes Section -->
                                <div class="text-xs md:text-sm font-semibold">
                                    <p class="whitespace-nowrap text-red-500">
                                        <span class="text-red-500">*</span> Price includes VAT & Tax
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div> <!-- flex -->
                </div>
            </div>
        </div>
    </section>

    <section class="mt-6 max-w-6xl mx-auto bg-gray-100 py-5">
        <x-form wire:submit="storeGearBooking">
            <div class="flex flex-col md:flex-row gap-4 mb-6">
                <div class="flex-1">
                    <x-card x-cloak class="py-6 h-full overflow-hidden shadow-md">
                        <div class="card-header">
                            <h3 class="text-lg md:text-xl text-center font-bold mb-4">Billing Details</h3>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4 px-4">
                            <!-- Name -->
                            <x-input class="custome-input-field" wire:model="name" label="Name" placeholder="Name" required readonly />

                            <!-- Email -->
                            <x-input class="custome-input-field" type="email" wire:model="email" label="Email" placeholder="Email" required
                                readonly />

                            <!-- Phone -->
                            <x-input class="custome-input-field" wire:model="phone" label="Phone" placeholder="Phone" required />

                            <!-- Address -->
                            <x-input class="custome-input-field" wire:model="address" label="Address" placeholder="Address" required />

                            <!-- Country -->
                            <x-choices class="custom-select-field" wire:model.live="country_id" :options="$countries" label="Country"
                                placeholder="Select Country" single required search-function="countrySearch" searchable />

                            <!-- Division -->
                            <x-choices class="custom-select-field" wire:model.live="division_id" :options="$divisions" label="Division"
                                placeholder="Select Division" single required search-function="divisionSearch" searchable />

                            <!-- District -->
                            <x-choices class="custom-select-field" wire:model.live="district_id" :options="$districts" label="District"
                                placeholder="Select District" search-function="districtSearch" searchable single required />

                            <!-- Zipcode -->
                            <x-input class="custome-input-field" wire:model="zipcode" label="Zipcode" placeholder="Zipcode" />
                        </div>

                        <!-- Additional Information -->
                        <div class="px-4">
                            <x-textarea class="custome-input-field" wire:model="additional_information" label="Additional Information"
                                placeholder="Additional Information" rows="5" />
                        </div>
                    </x-card>
                </div>
                <div class="flex-1 bg-white overflow-hidden shadow-md h-full" x-cloak>
                    <div class="card">
                        <!-- Shipping Method -->
                        <div class="card-header">
                            <h3 class="md:text-xl text-lg text-center font-bold mt-4">Shipping Method</h3>
                        </div>
                        <div class="px-6 py-3">
                            <x-choices class="custome-input-field" wire:model.live="shipping_method" :options="$shippingMethods"
                                label="Select Shipping
                                    Method" single required />
                        </div>

                        <!-- Payment Method -->
                        <div class="card-header">
                            <h3 class="md:text-xl text-lg text-center font-bold">Payment Method</h3>
                        </div>
                        <div class="px-6 py-3 flex flex-col">
                            <div class="payment_method">
                                <h5 class="py-2 bg-light font-bold">Select a payment method</h5>
                                @foreach ($gateways as $gateway)
                                    <div class="form-check mb-2">
                                        <input class="form-check-input custom-radio-dot" type="radio" id="gateway-{{ $gateway->id }}"
                                            value="{{ $gateway->id }}" wire:model.live="payment_gateway_id">
                                        <label class="form-check-label text-sm" for="gateway-{{ $gateway->id }}">{{ $gateway->name }}</label>
                                    </div>
                                @endforeach
                            </div>

                            <!-- Manual Payment Section -->
                            @if ($payment_gateway_id == 6)
                                <div class="mt-4 p-4 bg-gray-50 rounded-lg border">
                                    <h6 class="font-semibold mb-3 text-gray-700">Manual Payment Details</h6>

                                    <!-- Bank Selection -->
                                    <div class="mb-4">
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Select Bank</label>
                                        <select wire:model="selected_bank_id"
                                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500">
                                            <option value="">Choose a bank</option>
                                            @foreach ($banks as $bank)
                                                <option value="{{ $bank->id }}">
                                                    {{ $bank->name }} - {{ $bank->ac_no }} ({{ $bank->country->name }})
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('selected_bank_id')
                                            <span class="text-red-500 text-xs">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <!-- Payment Receipt Upload -->
                                    <div class="mb-4">
                                        <x-file label="Payment Receipt" wire:model="payment_receipt" required />
                                        @error('payment_receipt')
                                            <span class="text-red-500 text-xs">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <!-- Bank Details Display -->
                                    @if ($selected_bank_id)
                                        @php
                                            $selectedBank = $banks->firstWhere('id', $selected_bank_id);
                                        @endphp
                                        @if ($selectedBank)
                                            <div class="mt-3 p-3 bg-white rounded border">
                                                <h6 class="font-semibold text-sm mb-2">Bank Details:</h6>
                                                <div class="text-xs space-y-1">
                                                    <p><strong>Bank:</strong> {{ $selectedBank->name }}</p>
                                                    <p><strong>Account No:</strong> {{ $selectedBank->ac_no }}</p>
                                                    <p><strong>Branch:</strong> {{ $selectedBank->branch ?? 'N/A' }}</p>
                                                    <p><strong>Swift Code:</strong> {{ $selectedBank->swift_code }}</p>
                                                    <p><strong>Routing No:</strong> {{ $selectedBank->routing_no }}</p>
                                                    <p><strong>Address:</strong> {{ $selectedBank->address }}</p>
                                                </div>
                                            </div>
                                        @endif
                                    @endif
                                </div>
                            @endif
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
                                    class="bg-green-500 text-white px-6 py-2 rounded-md shadow-md hover:bg-green-600 focus:outline-none sm:mt-6 w-full sm:w-auto">
                                    Apply
                                </button>
                            </div>
                            <div class="summery mt-3 text-sm flex flex-col gap-y-1">
                                <div class="flex items-center justify-between">
                                    <span class="font-semibold">Sub Total :</span>
                                    <span>BDT {{ number_format($subtotal) ?? '' }}</span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="font-semibold">Discount :</span>
                                    <span>{{ $coupon_amount > 0 ? 'BDT ' . number_format($coupon_amount) : '0' }}</span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="font-semibold">Delivery Charge :</span>
                                    <span>BDT {{ number_format($shipping_charge) ?? '' }} </span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="font-semibold">Gateway Charge :</span>
                                    <span>{{ $delivery_charge > 0 ? 'BDT ' . number_format($delivery_charge) : '0' }}</span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="font-semibold">Total Amount :</span>
                                    <span>BDT {{ number_format($total_amount) ?? '' }}</span>
                                </div>
                            </div>

                            <div x-data="{ accepted: false }">
                                <label class="flex items-center gap-x-1 text-sm whitespace-nowrap mt-2 cursor-pointer">
                                    <input type="checkbox" name="termsCondition" id="flexCheckDefaultf1" x-model="accepted"
                                        class="custom-checkbox">
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
