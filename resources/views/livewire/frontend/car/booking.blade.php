<?php

use App\Models\Car;
use App\Models\User;
use App\Enum\CarType;
use App\Models\Order;
use App\Enum\UserType;
use App\Models\Coupon;
use Mary\Traits\Toast;
use App\Models\Country;
use App\Enum\AmountType;
use App\Models\District;
use App\Models\Division;
use App\Enum\AgentStatus;
use App\Enum\OrderStatus;
use App\Enum\ProductType;
use App\Models\CarBooking;
use App\Models\Commission;
use App\Enum\PaymentStatus;
use Livewire\Volt\Component;
use App\Jobs\OrderInvoiceJob;
use App\Enum\CommissionStatus;
use App\Models\PaymentGateway;
use Illuminate\Support\Carbon;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\DB;
use App\Notifications\OrderNotification;
use App\Services\SSLCOMMERZPaymentService;
use App\Services\BkashPaymentGatewayService;
use Illuminate\Support\Facades\Notification;

new #[Layout('components.layouts.app')] #[Title('Car Booking')] class extends Component {
    use Toast;

    public $car;
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
    public $pickup_country_id;
    public $pickup_division_id;
    public $pickup_district_id;
    public $dropoff_district_id;
    public $pickup_datetime;
    public $return_datetime;
    public $rental_type;
    public $vehicle_type;

    public function mount($slug)
    {
        // Fetch the car details using the provided slug
        $this->car = Car::where('slug', $slug)->firstOrFail();

        // Get the authenticated user and their customer details
        $user = auth()->user();
        $customer = $user->customer ?? null;

        // Assign user details
        $this->name = $user->name ?? '';
        $this->email = $user->email ?? '';
        $this->phone = $user->phone ?? '';
        $this->address = $customer->address ?? '';
        $this->country_id = $customer->country_id ?? '';
        $this->division_id = $customer->division_id ?? '';
        $this->district_id = $customer->district_id ?? '';

        // Get pickup and dropoff location and date/time from query parameters
        $this->pickup_country_id = request()->query('pickup_country_id');
        $this->pickup_division_id = request()->query('pickup_division_id');
        $this->pickup_district_id = request()->query('pickup_district_id');
        $this->dropoff_district_id = request()->query('dropoff_district_id');
        $this->pickup_datetime = request()->query('pickup_datetime');
        $this->return_datetime = request()->query('return_datetime');
        $this->rental_type = request()->query('rental_type');
        $this->vehicle_type = request()->query('vehicle_type');
        // Parse pickup and dropoff datetime
        $pickupDateTime = Carbon::parse($this->pickup_datetime);
        $dropoffDateTime = Carbon::parse($this->return_datetime);

        // Calculate the duration in hours
        $durationInHours = $pickupDateTime->diffInHours($dropoffDateTime);

        // Calculate the duration in days if the duration is greater than 24 hours
        $durationInDays = $pickupDateTime->diffInDays($dropoffDateTime);

        // Apply pricing logic based on duration
        if ($durationInHours <= 2) {
            // If the duration is 2 hours or less, apply the 2-hour price
            $this->subtotal = $this->car->price_2_hours;
        } elseif ($durationInHours <= 4) {
            // If the duration is 4 hours or less, apply the 4-hour price
            $this->subtotal = $this->car->price_4_hours;
        } elseif ($durationInHours <= 8) {
            // If the duration is 5 to 8 hours, apply the half-day price
            $this->subtotal = $this->car->price_half_day;
        } elseif ($durationInHours <= 10) {
            // If the duration is 9 to 10 hours, apply the full-day price
            $this->subtotal = $this->car->price_day;
        } else {
            // If the duration is more than 10 hours, apply per-day price
            // Multiply per-day price by the number of days
            $this->subtotal = $this->car->price_per_day * $durationInDays;
        }

        // Initialize total_amount with calculated price
        $this->total_amount = $this->subtotal;

        // Additional code for loading countries (if needed)
        $this->countries = Country::orderBy('name')->get();

        // Get active payment gateways, excluding 'Wallet' for non-agents
        $query = PaymentGateway::where('is_active', 1);
        if ($user->type !== UserType::Agent) {
            $query->where('name', '!=', 'Wallet');
        }
        $this->gateways = $query->get();

        // Apply agent discount if the user is an approved agent with valid commission settings
        if ($user->type == UserType::Agent && $user->agent && $user->agent->status == AgentStatus::Approve && $user->agent->validity && Carbon::parse($user->agent->validity)->isFuture()) {
            $agent = $user->agent;

            // Get the applicable commission for the agent's product type
            $commission = Commission::where('commission_role', $agent->agent_type)
                ->whereIn('product_type', [ProductType::Car, ProductType::All])
                ->where('status', CommissionStatus::Active)
                ->first();

            // If a commission is found, apply the discount
            if ($commission) {
                if ($commission->amount_type == AmountType::Fixed) {
                    $this->subtotal -= $commission->amount;
                } elseif ($commission->amount_type == AmountType::Percent) {
                    $this->subtotal -= round(($this->subtotal * $commission->amount) / 100);
                }

                // Ensure subtotal does not go below 0
                $this->subtotal = max(0, $this->subtotal);
            }
        }

        // Set total_amount with the discounted subtotal
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
        match ($property) {
            'payment_gateway_id' => $this->updatePaymentGateway(),
            default => null,
        };
    }

    private function updatePaymentGateway()
    {
        $this->delivery_charge = PaymentGateway::find($this->payment_gateway_id)?->charge ?? 0;
        $this->updateTotalAmount();
    }

    private function updateTotalAmount()
    {
        $this->total_amount = max(0, $this->subtotal - $this->coupon_amount + $this->delivery_charge);
    }

    /**
     * Store a new car booking, process payment, and send notifications.
     *
     * This method handles the entire booking process: from storing the order and car booking,
     * calculating the pricing based on the selected duration, applying coupon discounts,
     * and managing payment gateway logic (SSLCOMMERZ, Bkash, Wallet).
     * It also sends notifications to the car creator and admins and commits the transaction.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function storeCarBooking()
    {
        // Retrieve the coupon if applicable
        // If a coupon code is provided, attempt to find the coupon in the database
        $coupon = $this->coupon_code ? Coupon::where('code', $this->coupon_code)->first() : null;

        // Start a database transaction to ensure atomicity
        DB::beginTransaction();
        try {
            // Create a new order record
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
                'coupon_id' => $coupon?->id, // Link the coupon if exists
                'coupon_amount' => $this->coupon_amount, // Apply coupon discount
                'subtotal' => $this->subtotal,
                'delivery_charge' => $this->delivery_charge,
                'tran_id' => uniqid(), // Generate a unique transaction ID
                'total_amount' => $this->total_amount, // Total amount to be paid
                'status' => OrderStatus::Pending, // Initial order status
                'payment_gateway_id' => $this->payment_gateway_id, // Payment gateway used
                'payment_status' => PaymentStatus::Unpaid, // Initially mark as unpaid
            ]);

            // Increment coupon's used_count if coupon exists and has not reached max uses
            if ($coupon && $coupon->max_uses !== null && $coupon->used_count < $coupon->max_uses) {
                $coupon->increment('used_count');
            }

            // Calculate the duration between pickup and dropoff times
            $pickupDateTime = Carbon::parse($this->pickup_datetime);
            $dropoffDateTime = Carbon::parse($this->return_datetime);
            $durationInHours = $pickupDateTime->diffInHours($dropoffDateTime); // Duration in hours
            $durationInDays = $pickupDateTime->diffInDays($dropoffDateTime); // Duration in days

            // Determine the car pricing based on the selected duration
            if ($durationInHours <= 4) {
                $calculated_price = $this->car->price_4_hours;
            } elseif ($durationInHours <= 8) {
                $calculated_price = $this->car->price_half_day;
            } elseif ($durationInHours <= 10) {
                $calculated_price = $this->car->price_day;
            } else {
                $calculated_price = $this->car->price_per_day * $durationInDays; // Calculate price for more than 10 hours
            }

            // Create the car booking associated with the order
            $carBooking = CarBooking::create([
                'order_id' => $order->id,
                'car_id' => $this->car->id,
                'pickup_country_id' => $this->pickup_country_id,
                'pickup_division_id' => $this->pickup_division_id,
                'pickup_district_id' => $this->pickup_district_id,
                'dropoff_district_id' => $this->dropoff_district_id,
                'pickup_datetime' => $this->pickup_datetime,
                'return_datetime' => $this->return_datetime,
            ]);

            // Associate the car booking with the order using Eloquent relationship
            $order->sourceable()->associate($carBooking);
            $order->save(); // Save the updated order

            // Dispatch a job to send the invoice to the user
            OrderInvoiceJob::dispatch(auth()->user()->email, $order);

            // Send notifications to admins and the car creator
            $admins = User::where('type', UserType::Admin)->get();
            $carCreator = User::find($this->car->created_by); // Get the creator of the car
            $message = $this->car->title . ' booked by ' . auth()->user()->name;

            // Notify all admins about the new booking
            Notification::send($admins, new OrderNotification($order, $message));

            // Notify the car creator if they are not an admin
            if ($carCreator && $carCreator->type !== UserType::Admin) {
                $carCreator->notify(new OrderNotification($order, $message));
            }

            // Commit the transaction after successful operations
            DB::commit();

            // Handle payment gateways
            if ($order->payment_gateway_id == 3) {
                $sslService = new SSLCOMMERZPaymentService();
                $response = $sslService->makePayment($order);

                // Redirect to the payment gateway URL
                $url = json_decode($response)->data;
                return redirect()->away($url);
            }

            if ($order->payment_gateway_id == 1) {
                // Redirect to Bkash payment gateway
                return app(BkashPaymentGatewayService::class)->makePayment($order);
            }

            if ($order->payment_gateway_id == 4) {
                // Redirect to the order invoice page
                return redirect()->route('order.invoice', ['order' => $order->id]);
            }

            if ($order->payment_gateway_id == 5) {
                // Handle wallet payment logic
                $bookingAgent = auth()->user()->agent;
                if (!$bookingAgent || $bookingAgent->wallet < $order->total_amount) {
                    DB::rollBack();
                    return $this->error('Insufficient Wallet Balance');
                }

                // Deduct the amount from the agent's wallet
                $bookingAgent->wallet -= $order->total_amount;
                $bookingAgent->save();

                // Mark the order as paid and confirmed
                $order->payment_status = PaymentStatus::Paid;
                $order->status = OrderStatus::Confirmed;
                $order->save();

                // Handle wallet update for car creator (if applicable)
                $carCreator = User::find($this->car->created_by);
                if ($carCreator && $carCreator->type == UserType::Agent && $carCreator->id !== auth()->id()) {
                    $creatorAgent = $carCreator->agent;

                    // Add the calculated price to the creator's wallet
                    $creatorAgent->wallet += $calculated_price;
                    $creatorAgent->save();
                }

                // Redirect to the order invoice page after payment
                $this->redirectRoute('order.invoice', ['order' => $order->id]);
            }

            // Success message
            $this->success('Car Booking Successfully');
            $this->resetForm(); // Reset the form after booking
            return redirect()->back();
        } catch (\Throwable $th) {
            // Rollback the transaction if something goes wrong
            DB::rollBack();
            $this->error(env('APP_DEBUG', false) ? $th->getMessage() : 'Something went wrong');
        }
    }

    /**
     * Reset form fields after successful booking.
     */
    private function resetForm()
    {
        $this->reset(['phone', 'zipcode', 'payment_gateway_id', 'delivery_charge', 'coupon_code', 'coupon_amount', 'car']);
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

<div class="bg-gray-100 px-5 xl:px-0">
    @section('booking')
    <div class="flex items-center space-x-1 justify-center text-white mt-10 font-extrabold text-xl">
        <a href="/" class="hover:text-green-700 hover:underline">Home</a>
        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-green-800" fill="none" stroke="currentColor"
            stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" aria-hidden="true">
            <path d="M9 18l6-6-6-6"></path>
        </svg>
        <span>Car Booking</span>
    </div>
    @endsection

    <section class="pt-6">
        <div class="max-w-6xl mx-auto bg-white shadow-md rounded-md">
            <div class="flex flex-col md:flex-row gap-4 border mb-2 p-4 md:p-6 items-start md:items-stretch">

                <!-- Car Image -->
                <div class="w-full md:w-1/2">
                    <div class="h-48 md:h-full rounded-md overflow-hidden shadow-sm">
                        <img class="lg:object-cover md:object-center w-full h-full" src="{{ $car->image_link }}"
                            alt="{{ $car->title }}" />
                    </div>
                </div>

                <!-- Car Details -->
                <div class="w-full md:w-2/3 rounded-md border bg-gray-100 p-4 shadow-sm">

                    <!-- Car Title and Year -->
                    <div class="mb-3">
                        <p class="text-lg font-semibold text-gray-800">
                            {{ $car->title }} ({{ $car->model_year }})
                        </p>
                    </div>

                    <!-- Basic Info -->
                    <div
                        class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-2 lg:grid-cols-3 gap-y-2 gap-x-4 text-sm text-gray-700 mb-4">
                        <p><span class="font-medium">Seating Capacity:</span> {{ $car->seating_capacity }} persons</p>
                        <p><span class="font-medium">Car CC:</span> {{ $car->car_cc }}</p>
                        <p><span class="font-medium">Type:</span> {{ $car->car_type->label() }}</p>
                        <p><span class="font-medium">Color:</span> {{ $car->color }}</p>
                        <p><span class="font-medium">AC Facility:</span> {{ $car->ac_facility ? 'Yes' : 'No' }}</p>
                        <p><span class="font-medium">Only Body:</span> {{ $car->only_body ? 'Yes' : 'No' }}</p>
                    </div>

                    <!-- Rent Info -->
                    <div class="mb-3 text-sm md:text-base text-gray-800">
                        @if ($car->only_body == 1)
                        <p class="font-medium">
                            <span class="text-[#f73] font-bold">Hour Rent:</span>
                            BDT {{ $car->hour_rent }}/ <small>Per Hour</small>
                        </p>
                        @elseif ($car->only_body == 0)
                        <p class="font-medium">
                            <span class="text-[#f73] font-bold">Rent:</span>
                            BDT {{ $car->rent }}/ <small>Per Day</small>
                        </p>
                        @endif

                        @if ($car->extra_time_cost_by_hour && $car->only_body == 1)
                        <p class="text-xs text-gray-600 mt-1">
                            Extra Time Cost Hour: {{ $car->extra_time_cost_by_hour }}
                        </p>
                        @endif

                        @if ($car->extra_time_cost && $car->only_body == 0)
                        <p class="text-xs text-gray-600">
                            Extra Time Cost: BDT {{ $car->extra_time_cost }}
                        </p>
                        @endif
                    </div>

                    <!-- VAT Notice -->
                    <div class="text-xs md:text-sm font-semibold">
                        <p class="text-red-500">
                            <span class="text-red-500">*</span>
                            Price includes VAT & Tax
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>


    <section class="mt-6 max-w-6xl mx-auto bg-gray-100 py-5">
        <x-form wire:submit="storeCarBooking">
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
                    <div class="card bg-white rounded-sm overflow-hidden shadow-md h-full flex flex-col">
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
                                    <span>BDT {{ number_format($subtotal) ?? '0' }}</span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="font-semibold">Discount :</span>
                                    <span>{{ $coupon_amount > 0 ? 'BDT ' . number_format($coupon_amount) : '0' }}</span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="font-semibold">Gateway Charge :</span>
                                    <span>{{ $delivery_charge > 0 ? 'BDT ' . number_format($delivery_charge) : '0' }}</span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="font-semibold">Total Amount :</span>
                                    <span>BDT {{ number_format($total_amount) ?? '0' }}</span>
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