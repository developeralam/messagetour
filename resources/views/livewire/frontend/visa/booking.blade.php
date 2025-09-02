<?php

use Carbon\Carbon;
use App\Models\User;
use App\Models\Visa;
use App\Models\Order;
use App\Enum\UserType;
use App\Enum\VisaType;
use App\Models\Coupon;
use Mary\Traits\Toast;
use App\Models\Country;
use App\Enum\AmountType;
use App\Models\District;
use App\Models\Division;
use App\Enum\AgentStatus;
use App\Enum\OrderStatus;
use App\Enum\ProductType;
use App\Models\Commission;
use App\Enum\PaymentStatus;
use App\Models\VisaBooking;
use Livewire\Volt\Component;
use App\Jobs\OrderInvoiceJob;
use App\Enum\CommissionStatus;
use App\Models\PaymentGateway;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\DB;
use App\Models\EvisaBookingDetails;
use Illuminate\Support\Facades\Session;
use App\Notifications\OrderNotification;
use App\Traits\InteractsWithImageUploads;
use App\Services\SSLCOMMERZPaymentService;
use App\Services\BkashPaymentGatewayService;
use Illuminate\Support\Facades\Notification;

new #[Layout('components.layouts.app')] #[Title('Visa Booking')] class extends Component {
    use Toast;
    public $visa;
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
    public $total_travellers;
    public $selected_fee;
    public $convenient_fee;
    public $document_collection_date;
    public $total_convenient_fee = 0;

    public function mount($slug)
    {
        // Fetch visa details
        $this->visa = Visa::where('slug', $slug)->firstOrFail();

        // Booking data from session
        $bookingData = Session::get('booking_data', []);
        $evisaBookingData = Session::get('evisa_booking_data', []);

        // Determine if it's an eVisa booking
        $isEvisa = $this->visa->type == VisaType::Evisa; // Adjust field name if different

        // Common
        $this->total_travellers = $bookingData['total_travellers'] ?? ($evisaBookingData['total_travellers'] ?? 1);
        $this->selected_fee = $bookingData['selected_fee'] ?? ($evisaBookingData['selected_fee'] ?? 0);

        // Authenticated user
        $user = auth()->user();
        $this->name = $user->name ?? '';
        $this->email = $user->email ?? '';
        $this->phone = $user->phone ?? '';

        // Assign address fields based on user type
        if ($user->type == UserType::Customer && $user->customer) {
            $this->address = $user->customer->address ?? '';
            $this->country_id = $user->customer->country_id ?? '';
            $this->division_id = $user->customer->division_id ?? '';
            $this->district_id = $user->customer->district_id ?? '';
        } elseif ($user->type == UserType::Agent && $user->agent) {
            $this->address = $user->agent->primary_contact_address ?? '';
            $this->country_id = $user->agent->country_id ?? '';
            $this->division_id = $user->agent->division_id ?? '';
            $this->district_id = $user->agent->district_id ?? '';
        } else {
            $this->address = '';
            $this->country_id = '';
            $this->division_id = '';
            $this->district_id = '';
        }

        // Set base fee values
        $this->convenient_fee = $bookingData['convenient_fee'] ?? 0;
        $this->document_collection_date = $bookingData['document_collection_date'] ?? null;
        $this->total_fee = $bookingData['total_fee'] ?? 0;

        // ✅ Subtotal Calculation Based on Visa Type
        if ($isEvisa) {
            // eVisa: only selected_fee
            $subTotal = $this->selected_fee;
        } else {
            // Regular visa: selected_fee + convenient_fee
            $this->total_convenient_fee = $this->selected_fee + $this->convenient_fee;
            $subTotal = $this->total_convenient_fee;
        }

        // ✅ Agent Discount Logic (applies for both eVisa and regular visa)
        if ($user->type == UserType::Agent && $user->agent && $user->agent->status == AgentStatus::Approve && $user->agent->validity && Carbon::parse($user->agent->validity)->isFuture()) {
            $agent = $user->agent;

            $commission = Commission::where('commission_role', $agent->agent_type)
                ->whereIn('product_type', [ProductType::Visa, ProductType::All])
                ->where('status', CommissionStatus::Active)
                ->first();

            if ($commission) {
                if ($commission->amount_type == AmountType::Fixed) {
                    $subTotal -= $commission->amount;
                } elseif ($commission->amount_type == AmountType::Percent) {
                    $subTotal -= round(($subTotal * $commission->amount) / 100);
                }

                $subTotal = max(0, $subTotal); // prevent negative
            }
        }

        // Final assignment
        $this->subtotal = $subTotal;
        $this->total_amount = $this->subtotal;

        // Load countries and payment gateways
        $this->countries = Country::orderBy('name')->get();

        $query = PaymentGateway::where('is_active', 1);
        if ($user->type !== UserType::Agent) {
            $query->where('name', '!=', 'Wallet');
        }
        $this->gateways = $query->get();
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

    public function storeVisaBooking()
    {
        $coupon = $this->coupon_code ? Coupon::where('code', $this->coupon_code)->first() : null;

        DB::beginTransaction();
        try {
            // Create Order
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

            if ($coupon && $coupon->max_uses !== null && $coupon->used_count < $coupon->max_uses) {
                $coupon->increment('used_count');
            }

            // Create Visa Booking
            $visaBooking = VisaBooking::create([
                'order_id' => $order->id,
                'visa_id' => $this->visa->id,
                'total_traveller' => $this->total_travellers,
                'docuemnts_collection_date' => $this->document_collection_date,
            ]);

            // Associate visaBooking with order
            $order->sourceable()->associate($visaBooking);
            $order->save();

            // ✅ If Evisa, store additional evisa details
            if ($this->visa->type == VisaType::Evisa) {
                // Get non-file data from session
                $evisaData = Session::get('evisa_booking_data', []);
                unset($evisaData['total_travellers'], $evisaData['selected_fee']);

                // Get previously uploaded file paths from session (saved in details page)
                $uploadedPaths = Session::get('evisa_booking_files', []);

                // Merge and ensure visa_booking_id is first
                $data = ['visa_booking_id' => $visaBooking->id] + $evisaData + $uploadedPaths;

                EvisaBookingDetails::create($data);

                Session::forget('evisa_booking_data');
                Session::forget('evisa_booking_files');
            } else {
                Session::forget('booking_data');
            }

            // Dispatch invoice job
            OrderInvoiceJob::dispatch(auth()->user()->email, $order);

            // Notifications
            $admins = User::where('type', UserType::Admin)->get();
            $tourCreator = User::find($this->visa->created_by);
            $message = $this->visa->title . ' booked by ' . auth()->user()->name;

            Notification::send($admins, new OrderNotification($order, $message));
            if ($tourCreator && $tourCreator->type !== UserType::Admin) {
                $tourCreator->notify(new OrderNotification($order, $message));
            }

            DB::commit();

            // Redirect based on payment gateway
            if ($order->payment_gateway_id == 3) {
                $sslService = new SSLCOMMERZPaymentService();
                $response = $sslService->makePayment($order);
                $url = json_decode($response)->data;
                return redirect()->away($url);
            }

            if ($order->payment_gateway_id == 1) {
                return app(BkashPaymentGatewayService::class)->makePayment($order);
            }

            if ($order->payment_gateway_id == 4) {
                return redirect()->route('order.invoice', ['order' => $order->id]);
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

                $this->redirectRoute('order.invoice', ['order' => $order->id]);
            }

            $this->success('Visa Booking Successfully');
            $this->resetForm();
            return redirect()->back();
        } catch (\Throwable $th) {
            DB::rollBack();
            dd($th->getMessage());
            $this->error(env('APP_DEBUG', false) ? $th->getMessage() : 'Something went wrong');
        }
    }

    /**
     * Reset form fields after successful booking.
     */
    private function resetForm()
    {
        $this->reset(['phone', 'zipcode', 'payment_gateway_id', 'delivery_charge', 'coupon_code', 'coupon_amount', 'visa']);
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
    <section class="max-w-6xl mx-auto pt-10">
        <div class="bg-white p-6 sm:p-8 rounded-sm shadow-md">
            @if ($visa->type !== \App\Enum\VisaType::Evisa)
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <!-- Selected Date of Document Collection -->
                <div class="bg-gray-100 p-4 rounded border text-gray-700">
                    <p class="text-sm font-medium">Selected Date of Document Collection</p>
                    <p class="mt-1 text-base">
                        {{ \Carbon\Carbon::parse($document_collection_date)->format('d M, Y') }}
                    </p>
                </div>

                <!-- Visa Processing Fee -->
                <div class="bg-gray-100 p-4 rounded border text-gray-700">
                    <p class="text-sm font-medium">Visa Processing Fee</p>
                    <p class="mt-1 text-base">{{ $selected_fee }} BDT / <small>Per person</small></p>
                </div>

                <!-- Number of Persons -->
                <div class="bg-gray-100 p-4 rounded border text-gray-700">
                    <p class="text-sm font-medium">Number of Persons</p>
                    <p class="mt-1 text-base">{{ $total_travellers }}</p>
                </div>

                <!-- Convenient Fee -->
                <div class="bg-gray-100 p-4 rounded border text-gray-700">
                    <p class="text-sm font-medium">Convenient Fee (For Per Visit)</p>
                    <p class="mt-1 text-base">{{ $convenient_fee }} BDT</p>
                </div>
            </div>
            @else
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div
                    class="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-4 md:mb-6 text-sm rounded-md">
                    <p class="font-bold mb-1">Please read before proceeding:</p>
                    <ul class="list-disc pl-5 space-y-1">
                        <li>Please check documents checklist and upload according, less docs may
                            not
                            entertained.</li>
                        <li>Select number of Passenger and pay accordingly.</li>
                        <li>Orginal Copy with duly Signed.</li>
                        <li>Only in English and Notary (PDF/JPG).</li>
                        <li>Please Compressed to reduced Size.</li>
                        <li><span class="text-red-600 font-semibold">If any Documents are not
                                clear
                                you may ask for further.</span></li>
                    </ul>
                </div>
                <div>
                    <!-- Visa Processing Fee -->
                    <div class="bg-gray-100 p-4 mt-1 rounded border text-gray-700">
                        <p class="text-sm font-medium">Visa Processing Fee</p>
                        <p class="mt-1 text-base">{{ $selected_fee }} BDT / <small>Per person</small></p>
                    </div>

                    <!-- Number of Persons -->
                    <div class="bg-gray-100 p-4 rounded border text-gray-700 md:mt-12 mt-4">
                        <p class="text-sm font-medium">Number of Persons</p>
                        <p class="mt-1 text-base">{{ $total_travellers }}</p>
                    </div>
                </div>
            </div>
            @endif

        </div>
    </section>


    <section class="mt-6 max-w-6xl mx-auto bg-gray-100 py-5">
        <x-form wire:submit="storeVisaBooking">
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