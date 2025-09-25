<?php

use Carbon\Carbon;
use App\Models\Bank;
use App\Models\Tour;
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
use App\Models\TourBooking;
use Livewire\Volt\Component;
use App\Jobs\OrderInvoiceJob;
use App\Jobs\SendCustomerMailJob;
use App\Jobs\SendAgentMailJob;
use App\Enum\CommissionStatus;
use App\Mail\OrderInvoiceMail;
use App\Models\PaymentGateway;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Cache;
use App\Notifications\OrderNotification;
use App\Traits\InteractsWithImageUploads;
use App\Services\SSLCOMMERZPaymentService;
use App\Services\BkashPaymentGatewayService;
use Illuminate\Support\Facades\Notification;

new #[Layout('components.layouts.app')] #[Title('Tour Booking')] class extends Component {
    use Toast, InteractsWithImageUploads;
    public $tour;
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
    public $agentDiscount = 0;
    public $discountPercentage = 0;
    public $banks = [];
    public $selected_bank_id;
    public $payment_receipt;
    public $divisions = [];
    public $districts = [];

    public function mount($slug)
    {
        $tourSlug = Tour::where('slug', $slug)->first();
        $this->tour = $tourSlug;
        $this->name = auth()->user()->name ?? '';
        $this->email = auth()->user()->email ?? '';
        $this->phone = auth()->user()->phone ?? '';
        $this->address = auth()->user()->customer->address ?? '';
        $this->country_id = auth()->user()->customer->country_id ?? '';
        $this->division_id = auth()->user()->customer->division_id ?? '';
        $this->district_id = auth()->user()->customer->district_id ?? '';
        $this->countries = Country::orderBy('name', 'asc')->get();
        $this->banks = Bank::with('country')->orderBy('name', 'asc')->get();
        $this->divisions = Division::when($this->country_id, function ($q) {
            $q->where('country_id', intval($this->country_id));
        })->get();
        $this->districts = District::when($this->division_id, function ($q) {
            $q->where('division_id', intval($this->division_id));
        })->get();
        $query = PaymentGateway::where('is_active', 1);

        if (auth()->user()?->type !== UserType::Agent) {
            $query->where('name', '!=', 'Wallet');
            $query->where('name', '!=', 'Manual');
        }

        $this->gateways = $query->where('id', '!=', 4)->get();

        // Default price
        $this->subtotal = $this->tour->offer_price && $this->tour->offer_price < $this->tour->regular_price ? $this->tour->offer_price : $this->tour->regular_price;

        // Authenticated agent logic
        if (auth()->check() && auth()->user()->type == UserType::Agent && auth()->user()->agent && auth()->user()->agent->validity && auth()->user()->agent->validity->isFuture()) {
            $agent = auth()->user()->agent;

            // Find commission by matching agent type and product type
            $commission = Commission::where('commission_role', $agent->agent_type)
                ->whereIn('product_type', [ProductType::Tour, ProductType::All])
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

    public function updated($property)
    {
        match ($property) {
            'payment_gateway_id' => $this->updatePaymentGateway(),
            'country_id' => $this->updateDivisions(),
            'division_id' => $this->updateDistricts(),
            default => null,
        };
    }

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

    private function updateDivisions()
    {
        $this->divisions = Division::when($this->country_id, function ($q) {
            $q->where('country_id', intval($this->country_id));
        })->get();

        $this->division_id = null;
        $this->districts = collect();
    }

    private function updateDistricts()
    {
        $this->districts = District::when($this->division_id, function ($q) {
            $q->where('division_id', intval($this->division_id));
        })->get();
    }

    public function updateTotalAmount()
    {
        $this->total_amount = max(0, $this->subtotal - $this->coupon_amount + $this->delivery_charge);
    }

    public function storeTourBooking()
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

            // Create the tour booking associated with the order
            $tourBooking = TourBooking::create([
                'order_id' => $order->id,
                'tour_id' => $this->tour->id,
            ]);

            // **Associate the tour booking with the order using Eloquent relationship**
            $order->sourceable()->associate($tourBooking);
            $order->save();
            DB::commit();

            // Send invoice email to customer
            OrderInvoiceJob::dispatch(auth()->user()->email, $order);

            // Send booking confirmation email to customer
            $customerSubject = 'Tour Booking Confirmation - ' . $this->tour->title;
            $customerEmailBody = 'Dear ' . auth()->user()->name . ',<br><br>' . 'Thank you for booking the tour: <strong>' . $this->tour->title . '</strong><br><br>' . 'Your booking ID: <strong>' . $order->tran_id . '</strong><br>' . 'Total Amount: <strong>৳' . number_format($order->total_amount, 2) . '</strong><br><br>' . 'We will contact you soon with further details.<br><br>' . 'Best regards,<br>FlyValy Team';

            SendCustomerMailJob::dispatch(
                auth()->user()->email,
                [], // No BCC for customer email
                $customerSubject,
                $customerEmailBody,
            );

            // Send notifications and emails
            $admins = User::where('type', UserType::Admin)->get();
            $tourCreator = User::find($this->tour->created_by); // Find the tour creator
            $message = $this->tour->title . ' booked by ' . auth()->user()->name;

            // Notify the user who placed the order ONLY if they are NOT Admin and NOT the tour creator
            if (auth()->user()->type !== UserType::Admin && auth()->id() !== $this->tour->created_by) {
                auth()->user()->notify(new OrderNotification($order, $message));
            }

            // Notify all admins
            Notification::send($admins, new OrderNotification($order, $message));

            // Send email to tour creator (partner) if they exist and are NOT an admin
            if ($tourCreator && $tourCreator->type !== UserType::Admin) {
                $tourCreator->notify(new OrderNotification($order, $message));

                // Send email to tour creator
                $partnerSubject = 'New Tour Booking - ' . $this->tour->title;
                $partnerEmailBody = 'Dear ' . $tourCreator->name . ',<br><br>' . 'Great news! Your tour <strong>' . $this->tour->title . '</strong> has been booked by <strong>' . auth()->user()->name . '</strong>.<br><br>' . 'Booking Details:<br>' . 'Order ID: <strong>' . $order->tran_id . '</strong><br>' . 'Customer: <strong>' . auth()->user()->name . '</strong><br>' . 'Email: <strong>' . auth()->user()->email . '</strong><br>' . 'Phone: <strong>' . $order->phone . '</strong><br>' . 'Total Amount: <strong>৳' . number_format($order->total_amount, 2) . '</strong><br><br>' . 'Please contact the customer for further arrangements.<br><br>' . 'Best regards,<br>FlyValy Team';

                SendAgentMailJob::dispatch(
                    $tourCreator->email,
                    [], // No BCC for partner email
                    $partnerSubject,
                    $partnerEmailBody,
                );
            }

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
                // Create payment request for Cash on Delivery
                $bookingAgent = auth()->user()->agent;
                if ($bookingAgent) {
                    \App\Models\AgentPaymentRequest::create([
                        'agent_id' => $bookingAgent->id,
                        'order_id' => $order->id,
                        'amount' => $order->total_amount,
                        'status' => 'pending',
                        'notes' => 'Cash on Delivery payment request',
                    ]);
                }

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

                // Check if tour creator is a different, valid agent
                $tourCreator = User::find($this->tour->created_by);
                if ($tourCreator && $tourCreator->type == UserType::Agent && $tourCreator->id !== auth()->id() && $tourCreator->agent && $tourCreator->agent->validity && $tourCreator->agent->validity->isFuture()) {
                    $creatorAgent = $tourCreator->agent;

                    // Original total: regular price + coupon + delivery
                    $basePrice = $this->tour->offer_price && $this->tour->offer_price < $this->tour->regular_price ? $this->tour->offer_price : $this->tour->regular_price;

                    $originalTotal = $basePrice - ($order->coupon_amount ?? 0);

                    $creatorAgent->wallet += $originalTotal;
                    $creatorAgent->save();

                    // (Optional) log the transaction to a wallet_commissions table
                }

                $this->redirectRoute('order.invoice', ['order' => $order->id]);
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

                $this->success('Tour booking submitted successfully! Your payment will be verified manually.');
                $this->redirectRoute('order.invoice', ['order' => $order->id]);
            }

            $this->success('Tour Booking Successfully');
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
        <div class="flex items-center space-x-1 justify-center text-white mt-10 font-extrabold text-xl">
            <a href="/" class="hover:text-green-700 hover:underline">Home</a>
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-green-800" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                stroke-linejoin="round" viewBox="0 0 24 24" aria-hidden="true">
                <path d="M9 18l6-6-6-6"></path>
            </svg>
            <span>Tour Booking</span>
        </div>
    @endsection

    <section class="pt-6">
        <div class="max-w-6xl mx-auto bg-white shadow-md">
            <div class="grid grid-cols-12 items-start gap-x-4 gap-y-3 border mb-2 p-3">
                <div class="col-span-12 md:col-span-3 h-52 rounded-md overflow-hidden shadow-md">
                    <img class="object-cover h-full w-full" src="{{ $tour->thumbnail_link ?? '' }}" alt="">
                </div>
                <div class="col-span-12 lg:col-span-9 rounded-lg shadow-lg border p-4 bg-gray-100">
                    <!-- Hotel Name and Room No Section -->
                    <div class="mb-2">
                        <p class="text-sm md:text-lg font-semibold">
                            {{ $tour->title }}
                        </p>
                    </div>

                    <!-- Hotel Location Section -->
                    <div class="mb-2">
                        <p class="text-sm md:text-sm font-medium flex items-center gap-x-1">
                            <x-icon name="fas.location-dot" class="text-[#f73]" />
                            {{ $tour->location }}
                        </p>
                    </div>

                    <!-- Price and Discount Section -->
                    <div class="mb-2">
                        <!-- Original Price with Discount -->
                        @if (auth()->check() && auth()->user()->type == \App\Enum\UserType::Agent && isset($agentDiscount) && $agentDiscount > 0)
                            <p class="text-sm md:text-base flex items-center gap-x-2">
                                <del class="text-sm text-red-500">BDT {{ number_format($tour->regular_price) }}</del>
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
                        @elseif ($tour->offer_price && $tour->offer_price < $tour->regular_price)
                            <p class="text-sm md:text-base flex items-center gap-x-2">
                                @php
                                    $discountPercentage = round((($tour->regular_price - $tour->offer_price) / $tour->regular_price) * 100);
                                @endphp
                                <del class="text-sm text-red-500">BDT {{ number_format($tour->regular_price) }}</del>
                            </p>
                            <div class="flex items-center">
                                <img class="w-6 h-6" src="{{ asset('images/discount-mono.svg') }}" alt="Discount Icon">
                                <p class="text-sm md:text-base flex items-center">
                                    <span class="text-[#f73] text-xs font-bold">{{ $discountPercentage }}% OFF</span>
                                    <span class="font-bold ml-1">BDT {{ number_format($tour->offer_price) }}</span>
                                    <span class="text-xs ml-1">/ Per Person</span>
                                </p>
                            </div>
                        @else
                            <p class="text-sm md:text-base flex items-center">
                                <span class="font-bold ml-1">BDT {{ number_format($tour->regular_price) }}</span>
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
        <x-form wire:submit="storeTourBooking">
            <div class="flex flex-col lg:flex-row gap-4 mb-6">
                <!-- Billing Details -->
                <div class="w-full lg:w-1/2">
                    <x-card x-cloak class="py-6 h-full overflow-hidden shadow-md">
                        <div class="card-header">
                            <h3 class="text-lg md:text-xl text-center font-bold mb-4">Billing Details</h3>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4 px-2 md:px-4">
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
                        <div class="px-2 md:px-4">
                            <x-textarea class="custome-input-field" wire:model="additional_information" label="Additional Information"
                                placeholder="Additional Information" />
                        </div>
                    </x-card>
                </div>
                <!-- Payment Method & Summary -->
                <div class="w-full lg:w-1/2 mt-4 lg:mt-0">
                    <div class="card bg-white rounded-sm overflow-hidden shadow-md h-full flex flex-col">
                        <div class="card-header">
                            <h3 class="md:text-xl text-lg text-center font-bold mt-4">Payment Method</h3>
                        </div>
                        <div class="px-2 md:px-6 py-3 flex flex-col gap-y-3 flex-1">
                            <div class="payment_method">
                                <h5 class="p-2 bg-light font-bold">Select a payment method</h5>
                                @foreach ($gateways as $gateway)
                                    <div class="form-check mb-2">
                                        <input class="form-check-input custom-radio-dot" type="radio" id="gateway-{{ $gateway->id }}"
                                            value="{{ $gateway->id }}" wire:model.live="payment_gateway_id">
                                        <label class="form-check-label text-sm" for="gateway-{{ $gateway->id }}">{{ $gateway->name }}
                                        </label>
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
                                    class="bg-green-500 text-white px-6 py-2 rounded-md shadow-md hover:bg-green-600 focus:outline-none sm:mt-6 mt-2 w-full sm:w-auto">
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
                                    <span> {{ $coupon_amount > 0 ? 'BDT ' . number_format($coupon_amount) : '0' }}
                                    </span>
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
