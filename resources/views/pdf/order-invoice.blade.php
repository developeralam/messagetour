<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Invoice</title>
    <style>
        * {
            margin: 0;
            padding: 0;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            position: relative;
            /* Makes the body container relative */
            min-height: 100%;
            /* Ensures that body takes the entire height of the page */
        }

        .header,
        .footer {
            background: #22c55e;
            color: #fff;
            padding: 20px 30px;
        }

        .footer {
            position: absolute;
            /* Positions the footer at the bottom */
            bottom: 0;
            /* Positions it at the very bottom of the page */
            width: 100%;
            /* Ensures it spans the full width */
            text-align: center;
        }

        .section {
            padding: 5px 30px;
        }

        .table,
        .table th,
        .table td {
            border: 1px solid #000;
            border-collapse: collapse;
            padding: 8px;
        }

        .table {
            width: 100%;
            margin-top: 10px;
        }

        .text-center {
            text-align: center;
        }

        .bold {
            font-weight: bold;
        }

        .w-100 {
            width: 100%;
        }

        .text-uppercase {
            text-transform: uppercase;
        }

        .border {
            border: 1px solid #000;
        }

        .p-5 {
            padding: 5px;
        }

        .float-right {
            float: right;
        }

        .float-left {
            float: left;
        }

        .overflow-hidden {
            overflow: hidden;
        }

        .border-t {
            border-top: 1px solid #000;
        }
    </style>
</head>

<body>
    @php
        $booking = $order->sourceable;

        $bookingTypes = [
            \App\Models\TourBooking::class => 'Tour Booking',

            \App\Models\VisaBooking::class => function ($booking) {
                return $booking->visa?->type == \App\Enum\VisaType::Evisa ? 'E-visa Booking' : 'Visa Booking';
            },

            \App\Models\TravelProductBooking::class => 'Product Booking',

            \App\Models\CarBooking::class => 'Car Booking',

            \App\Models\HotelRoomBooking::class => 'Hotel Room Booking',
        ];

        $resolver = $bookingTypes[get_class($booking)] ?? 'Unknown';
        $typeLabel = is_callable($resolver) ? $resolver($booking) : $resolver;
    @endphp
    <div class="header">
        <table class="w-100">
            <tr>
                <td style="text-align: left">
                    <h3 class="text-uppercase" style="font-size: 30px">INVOICE</h3>
                    <p class="text-uppercase bold">{{ $typeLabel }}</p>
                </td>
                <td style="width: 120px; background-color: #fff">
                    <img width="300px"
                        src="data:image/png;base64,{{ base64_encode(file_get_contents(public_path('logo.png'))) }}"
                        alt="Logo" />
                </td>
            </tr>
        </table>
    </div>


    <div class="section">
        <table class="w-100">
            <tr>
                <td width="40%">
                    <h2>{{ $order->user->name ?? '' }}</h2>
                    <p>
                        <strong>{{ $order->user->email ?? '' }}</strong>
                    </p>
                    <p>
                        <strong>{{ $order->phone ?? '' }}</strong>
                    </p>
                </td>
                <td width="10%"></td>
                <td width="50%" style="text-align:right">
                    <p>
                        <strong>Date:</strong>
                        {{ $order->created_at->format('d M ,Y') }}
                    </p>
                    <p>
                        <strong>Invoice: #</strong>
                        {{ $order->id }}
                    </p>
                </td>
            </tr>
        </table>
        <hr style="margin-top: 15px" />
    </div>

    <div class="section">
        <div>
            <span><strong>Address</strong></span>
            <span class="float-right">
                Make all cheques payable to
                <strong>{{ $globalSettings->application_name ?? 'FLYVALY' }}.</strong>
            </span>
        </div>
        <table class="w-100">
            <tr>
                <td width="40%" class="border p-5">
                    <p>{{ $order->address ?? '' }}</p>
                </td>
                <td width="10%"></td>
                <td width="50%" class="border p-5">
                    <p>
                        <strong>Company Address:</strong>
                        Uttara, Dhaka, Bangladesh
                        <br />
                        DHAKA-1230
                    </p>
                    <p>
                        <strong>HOTLINE:</strong>
                        {{ $globalSettings->phone ?? '' }}
                    </p>
                    <p>
                        <strong>Email:</strong>
                        {{ $globalSettings->contact_email ?? '' }}
                    </p>
                </td>
            </tr>
        </table>
    </div>

    {{-- Booking Information --}}
    @if ($booking instanceof \App\Models\TourBooking)
        <div class="section">
            <h3 style="text-align: center; margin:40px 0 20px 0; font-size:20px;" class="text-uppercase bold">Tour
                Booking
                Information
            </h3>
            <table class="table">
                <thead style="background: #d9f3d2">
                    <tr>
                        <th>TOUR</th>
                        <th>LOCATION</th>
                        <th>START DATE</th>
                        <th>END DATE</th>
                        <th>TOUR TYPE</th>
                    </tr>
                </thead>
                <tbody>
                    <tr style="text-align: center">
                        <td style="width: 38%">
                            {{ $booking->tour->title ?? '' }}
                        </td>
                        <td style="width: 20%">
                            {{ $booking->tour->location ?? '' }}
                        </td>
                        <td style="width: 16%">
                            {{ $booking->tour->start_date->format('d M, Y') ?? '' }}
                        </td>
                        <td style="width: 13%">
                            {{ $booking->tour->end_date->format('d M, Y') ?? '' }}
                        </td>
                        <td style="width: 13%">
                            {{ $booking->tour->type->label() ?? '' }}
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div class="section overflow-hidden" style="margin-top: 20px">
            <table class="w-100">
                <tr>
                    <td width="60%" style="text-align: left">
                        <table class="table">
                            <tr>
                                <td class="bold">Order Transaction</td>
                                <td>{{ $booking->order->tran_id }}</td>
                            </tr>
                            <tr>
                                <td class="bold">Payment Method</td>
                                <td>{{ $booking->order->paymentgateway->name }}</td>
                            </tr>
                            <tr>
                                <td class="bold">Payment Status</td>
                                <td>{{ $booking->order->payment_status->label() }}</td>
                            </tr>
                        </table>
                    </td>
                    <td width="40%">
                        <table class="table">
                            <tr>
                                <td class="bold">Sub Total</td>
                                <td>
                                    @if ($booking->order->subtotal)
                                        BDT {{ number_format($booking->order->subtotal) }}
                                    @else
                                        0
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td class="bold">Discount</td>
                                <td>
                                    @if ($booking->order->coupon_amount)
                                        BDT {{ number_format($booking->order->coupon_amount) }}
                                    @else
                                        0
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td class="bold">Total Amount</td>
                                <td>
                                    @if ($booking->order->total_amount)
                                        BDT {{ number_format($booking->order->total_amount) }}
                                    @else
                                        0
                                    @endif
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
        </div>
    @elseif ($booking instanceof \App\Models\HotelRoomBooking)
        <div class="section">
            <h3 style="text-align: center; margin:40px 0 20px 0; font-size:20px;" class="text-uppercase bold">Hotel Room
                Booking Information</h3>
            <table class="table">
                <thead style="background: #d9f3d2">
                    <tr>
                        <th>HOTEL</th>
                        <th>HOTEL ROOM NO</th>
                        <th>HOTEL CHECKIN DATE</th>
                        <th>HOTEL CHECKOUT DATE</th>
                    </tr>
                </thead>
                <tbody>
                    <tr style="text-align: center">
                        <td style="width: 30%">
                            {{ $booking->hotelbookingitems->first()->room->hotel->name ?? '' }}
                        </td>
                        <td style="width: 20%">
                            {{-- Assuming the first item is the relevant one --}}
                            {{ $booking->hotelbookingitems->first()->room->room_no ?? '' }}
                        </td>
                        <td style="width: 25%">
                            {{-- Assuming the first item is the relevant one --}}
                            {{ $booking->hotelbookingitems->first()->hotelroombooking->check_in->format('d M, Y ') ?? '' }}
                        </td>
                        <td style="width: 25%">
                            {{-- Assuming the first item is the relevant one --}}
                            {{ $booking->hotelbookingitems->first()->hotelroombooking->check_out->format('d M, Y ') ?? '' }}
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div class="section overflow-hidden">
            <table class="w-100">
                <tr>
                    <td width="60%" style="text-align: left">
                        <table class="table">
                            <tr>
                                <td class="bold">Order Transaction</td>
                                <td>{{ $booking->order->tran_id }}</td>
                            </tr>
                            <tr>
                                <td class="bold">Payment Method</td>
                                <td>{{ $booking->order->paymentgateway->name }}</td>
                            </tr>
                            <tr>
                                <td class="bold">Payment Status</td>
                                <td>{{ $booking->order->payment_status->label() }}</td>
                            </tr>
                        </table>
                    </td>
                    <td width="40%">
                        <table class="table">
                            <tr>
                                <td class="bold">Sub Total</td>
                                <td>
                                    @if ($booking->order->subtotal)
                                        BDT {{ number_format($booking->order->subtotal) }}
                                    @else
                                        0
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td class="bold">Discount</td>
                                <td>
                                    @if ($booking->order->coupon_amount)
                                        BDT {{ number_format($booking->order->coupon_amount) }}
                                    @else
                                        0
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td class="bold">Total Amount</td>
                                <td>
                                    @if ($booking->order->total_amount)
                                        BDT {{ number_format($booking->order->total_amount) }}
                                    @else
                                        0
                                    @endif
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
        </div>
    @elseif ($booking instanceof \App\Models\CarBooking)
        <div class="section">
            <h3 style="text-align: center; margin:40px 0 20px 0; font-size:20px;" class="text-uppercase bold">Car
                Booking Information</h3>
            <table class="table">
                <thead style="background: #d9f3d2">
                    <tr>
                        <th style="width: 15%;">CAR</th>
                        <th style="width: 10%;">CAR TYPE</th>
                        <th style="width: 20%;">PICKUP LOCATION</th>
                        <th style="width: 17%;">PICKUP DATE TIME</th>
                        <th style="width: 20%;">DROPOUT LOCATION</th>
                        <th style="width: 18%;">DROPOUT DATE TIME</th>
                    </tr>
                </thead>
                <tbody>
                    <tr style="text-align: center">
                        <td style="width: 15%;">
                            {{ $booking->car->title . ' (' . ($booking->car->model_year ?? '') . ')' }}</td>
                        <td style="width: 15%;">{{ $booking->car->car_type->label() ?? '' }}</td>
                        <td style="width: 20%;">
                            @php
                                // Split pickup_location into id and type
                                $location = explode('-', $booking->pickup_location);
                                $locationId = $location[0]; // The actual ID
                                $locationType = $location[1]; // Either 'district' or 'division'
                            @endphp

                            @if ($locationType == 'district')
                                @php
                                    // Find the District and related Division and Country
                                    $district = \App\Models\District::find($locationId);
                                    $division = $district ? $district->division : null;
                                    $country = $division ? $division->country : null;
                                @endphp
                                {{ optional($country)->name ?? '' }},
                                {{ optional($division)->name ?? '' }},
                                {{ optional($district)->name ?? '' }}
                            @elseif ($locationType == 'division')
                                @php
                                    // Find the Division and related Country
                                    $division = \App\Models\Division::find($locationId);
                                    $country = $division ? $division->country : null;
                                @endphp
                                {{ optional($country)->name ?? '' }},
                                {{ optional($division)->name ?? '' }}
                            @endif
                        </td>

                        <td style="width: 20%;">
                            <p>
                                {{ \Carbon\Carbon::parse($booking->pickup_date)->format('d M, Y') }},
                                {{ \Carbon\Carbon::parse($booking->pickup_time)->format('h:i A') }}
                            </p>
                        </td>
                        <td style="width: 15%;">
                            @php
                                // Split dropout_location into id and type
                                $dropout_location = explode('-', $booking->dropout_location);
                                $dropout_locationId = $dropout_location[0] ?? ''; // The actual ID
                                $dropout_locationType = $dropout_location[1] ?? ''; // Either 'district' or 'division'
                            @endphp

                            @if ($locationType == 'district')
                                @php
                                    // Find the District and related Division and Country
                                    $dropout_district = \App\Models\District::find($dropout_locationId);
                                    $dropout_division = $dropout_district ? $dropout_district->division : null;
                                    $dropout_country = $dropout_division ? $dropout_division->country : null;
                                @endphp
                                {{ optional($dropout_country)->name ?? '' }},
                                {{ optional($dropout_division)->name ?? '' }},
                                {{ optional($dropout_district)->name ?? '' }}
                            @elseif ($dropout_locationType == 'division')
                                @php
                                    // Find the Division and related Country
                                    $dropout_division = \App\Models\Division::find($dropout_locationId);
                                    $dropout_country = $dropout_division ? $dropout_division->country : null;
                                @endphp
                                {{ optional($dropout_country)->name ?? '' }},
                                {{ optional($dropout_division)->name ?? '' }}
                            @else
                                <span>The drop-off location is the same as the pickup location.</span>
                            @endif
                        </td>
                        <td style="width: 15%;">
                            <p>
                                {{ \Carbon\Carbon::parse($booking->dropout_date)->format('d M, Y') }},
                                {{ \Carbon\Carbon::parse($booking->dropout_time)->format('h:i A') }}
                            </p>
                        </td>
                    </tr>
                </tbody>
            </table>

        </div>
        <div class="section overflow-hidden">
            <table class="w-100">
                <tr>
                    <td width="60%" style="text-align: left">
                        <table class="table">
                            <tr>
                                <td class="bold">Order Transaction</td>
                                <td>{{ $booking->order->tran_id }}</td>
                            </tr>
                            <tr>
                                <td class="bold">Payment Method</td>
                                <td>{{ $booking->order->paymentgateway->name }}</td>
                            </tr>
                            <tr>
                                <td class="bold">Payment Status</td>
                                <td>{{ $booking->order->payment_status->label() }}</td>
                            </tr>
                        </table>
                    </td>
                    <td width="40%">
                        <table class="table">
                            <tr>
                                <td class="bold">Sub Total</td>
                                <td>
                                    @if ($booking->order->subtotal)
                                        BDT {{ number_format($booking->order->subtotal) }}
                                    @else
                                        0
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td class="bold">Discount</td>
                                <td>
                                    @if ($booking->order->coupon_amount)
                                        BDT {{ number_format($booking->order->coupon_amount) }}
                                    @else
                                        0
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td class="bold">Total Amount</td>
                                <td>
                                    @if ($booking->order->total_amount)
                                        BDT {{ number_format($booking->order->total_amount) }}
                                    @else
                                        0
                                    @endif
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
        </div>
    @elseif ($booking instanceof \App\Models\TravelProductBooking)
        <div class="section">
            <h3 style="text-align: center; margin:40px 0 20px 0; font-size:20px;" class="text-uppercase bold">Gear
                Booking Information</h3>
            <table class="table">
                <thead style="background: #d9f3d2">
                    <tr>
                        <th>PRODUCT</th>
                        <th>BRAND</th>
                        <th>QUANTITY</th>
                    </tr>
                </thead>
                <tbody>
                    <tr style="text-align: center">
                        <td>
                            {{ $booking->travelproduct->title ?? '' }}
                        </td>
                        <td>
                            {{ $booking->travelproduct->brand ?? '' }}
                        </td>
                        <td>
                            {{ $booking->qty ?? '' }}
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div class="section overflow-hidden">
            <table class="w-100">
                <tr>
                    <td width="60%" style="text-align: left">
                        <table class="table">
                            <tr>
                                <td class="bold">Order Transaction</td>
                                <td>{{ $booking->order->tran_id }}</td>
                            </tr>
                            <tr>
                                <td class="bold">Shipping</td>
                                <td>{{ $booking->order->shipping_method->label() }}</td>
                            </tr>
                            <tr>
                                <td class="bold">Payment Method</td>
                                <td>{{ $booking->order->paymentgateway->name }}</td>
                            </tr>
                            <tr>
                                <td class="bold">Payment Status</td>
                                <td>{{ $booking->order->payment_status->label() }}</td>
                            </tr>
                        </table>
                    </td>
                    <td width="40%">
                        <table class="table">
                            <tr>
                                <td class="bold">Sub Total</td>
                                <td>
                                    @if ($booking->order->subtotal)
                                        BDT {{ number_format($booking->order->subtotal) }}
                                    @else
                                        0
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td class="bold">Delivery Charge</td>
                                <td>
                                    @if ($booking->order->shipping_charge)
                                        BDT {{ number_format($booking->order->shipping_charge) }}
                                    @else
                                        0
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td class="bold">Discount</td>
                                <td>
                                    @if ($booking->order->coupon_amount)
                                        BDT {{ number_format($booking->order->coupon_amount) }}
                                    @else
                                        0
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td class="bold">Total Amount</td>
                                <td>
                                    @if ($booking->order->total_amount)
                                        BDT {{ number_format($booking->order->total_amount) }}
                                    @else
                                        0
                                    @endif
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
        </div>
    @elseif ($booking instanceof \App\Models\VisaBooking)
        @if ($booking->visa->type !== \App\Enum\VisaType::Evisa)
            <div class="section">
                <h3 style="text-align: center; margin:40px 0 20px 0; font-size:20px;" class="text-uppercase bold">Visa
                    Booking Information</h3>
                <table class="table">
                    <thead style="background: #d9f3d2; text-align: center">
                        <tr>
                            <th>VISA</th>
                            <th>ORIGIN COUNTRY</th>
                            <th>DESTINATION COUNTRY</th>
                            <th>TOTAL TRAVELLER</th>
                            <th>DOCUMENT COLLECTION DATE</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td style="text-align: center">
                                {{ $booking->visa->title ?? '' }}
                            </td>
                            <td style="text-align: center">
                                {{ $booking->visa->origin->name ?? '' }}
                            </td>
                            <td style="text-align: center">
                                {{ $booking->visa->destination->name ?? '' }}
                            </td>
                            <td style="text-align: center">
                                {{ $booking->total_traveller ?? '' }}
                            </td>
                            <td style="text-align: center">
                                {{ optional($booking->docuemnts_collection_date)->format('d M, Y') }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        @else
            <div class="section">
                <h3 style="text-align: center; margin:10px 0 5px 0; font-size:20px;" class="text-uppercase bold">
                    E-visa
                    Booking Information</h3>
                <p style="text-align: center; font-size:13px; font-weight:600" class="text-uppercase">
                    Visa Information</p>
                <table class="table">
                    <thead style="background: #d9f3d2; text-align: center">
                        <tr>
                            <th>VISA</th>
                            <th>ORIGIN COUNTRY</th>
                            <th>DESTINATION COUNTRY</th>..
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td style="text-align: center">
                                {{ $booking->visa->title ?? '' }}
                            </td>
                            <td style="text-align: center">
                                {{ $booking->visa->origin->name ?? '' }}
                            </td>
                            <td style="text-align: center">
                                {{ $booking->visa->destination->name ?? '' }}
                            </td>
                        </tr>
                    </tbody>
                </table>
                <p style="text-align: center; font-size:13px; font-weight:600; margin-top:5px;"
                    class="text-uppercase">
                    Personal Information</p>
                <table class="table">
                    <thead style="background: #d9f3d2; text-align: center">
                        <tr>
                            <th>NAME</th>
                            <th>EMAIL</th>
                            <th>PHONE</th>
                            <th>DATE OF BIRTH</th>
                            <th>GENDER</th>
                            <th>NATIONALITY</th>
                            <th>NID</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr style="text-align: center;">
                            <td style="width: 10%">
                                {{ $booking->evisa_booking_detail->first_name . ' ' . $booking->evisa_booking_detail->last_name }}
                            </td>
                            <td style="width: 10%">
                                {{ $booking->evisa_booking_detail->email ?? '' }}
                            </td>
                            <td style="width: 10%">
                                {{ $booking->evisa_booking_detail->phone ?? '' }}
                            </td>
                            <td style="width: 20%">
                                {{ $booking->evisa_booking_detail->dob->format('d M, Y') ?? '' }}
                            </td>
                            <td style="width: 10%">
                                {{ $booking->evisa_booking_detail->gender->label() ?? '' }}
                            </td>
                            <td style="width: 15%">
                                {{ $booking->evisa_booking_detail->nationality ?? '' }}
                            </td>
                            <td style="width: 15%">
                                {{ $booking->evisa_booking_detail->nid ?? '' }}
                            </td>
                        </tr>
                    </tbody>
                </table>
                <p style="text-align: center; font-size:13px; font-weight:600; margin-top:5px;"
                    class="text-uppercase">
                    Passport Information</p>
                <table class="table">
                    <thead style="background: #d9f3d2; text-align: center">
                        <tr>
                            <th>PASSPORT NUMBER</th>
                            <th>ISSUE DATE</th>
                            <th>EXPIRY DATE</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr style="text-align: center;">
                            <td>
                                {{ $booking->evisa_booking_detail->passport_number ?? '' }}
                            </td>
                            <td>
                                {{ $booking->evisa_booking_detail->passport_issue_date->format('d M, Y') ?? '' }}
                            </td>
                            <td>
                                {{ $booking->evisa_booking_detail->passport_exp_date->format('d M, Y') ?? '' }}
                            </td>
                        </tr>
                    </tbody>
                </table>
                <p style="text-align: center; font-size:13px; font-weight:600; margin-top:5px;"
                    class="text-uppercase">
                    Present Information</p>
                <table class="table">
                    <thead style="background: #d9f3d2; text-align: center">
                        <tr>
                            <th>HOUSE/STREET NO</th>
                            <th>VILLAGE/TOWN/CITY</th>
                            <th>COUNTRY</th>
                            <th>DIVISION/STATE</th>
                            <th>DISTRICT/STATE</th>
                            <th>ZIP CODE</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr style="text-align: center;">
                            <td>
                                {{ $booking->evisa_booking_detail->pa_house_no ?? '' }}
                            </td>
                            <td>
                                {{ $booking->evisa_booking_detail->pa_address ?? '' }}
                            </td>
                            <td>
                                {{ $booking->evisa_booking_detail->presentCountry->name ?? '' }}
                            </td>
                            <td>
                                {{ $booking->evisa_booking_detail->presentDivision->name ?? '' }}
                            </td>
                            <td>
                                {{ $booking->evisa_booking_detail->presentDistrict->name ?? '' }}
                            </td>
                            <td>
                                {{ $booking->evisa_booking_detail->pa_zip_code ?? '' }}
                            </td>
                        </tr>
                    </tbody>
                </table>
                <p style="text-align: center; font-size:13px; font-weight:600; margin-top:5px;"
                    class="text-uppercase">
                    Travel Information</p>
                <table class="table">
                    <thead style="background: #d9f3d2; text-align: center">
                        <tr>
                            <th>ADDRESS IN DESTINATION</th>
                            <th>DESTINATION CONTACT NUMBER</th>
                            <th>POST CODE</th>
                            <th>ENTRY PORT</th>
                            <th>ARRIVAL DATE</th>
                            <th>PURPOSE</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr style="text-align: center;">
                            <td>
                                {{ $booking->evisa_booking_detail->des_address ?? '' }}
                            </td>
                            <td>
                                {{ $booking->evisa_booking_detail->des_phone ?? '' }}
                            </td>
                            <td>
                                {{ $booking->evisa_booking_detail->des_post_code ?? '' }}
                            </td>
                            <td>
                                {{ $booking->evisa_booking_detail->arr_date->format('d M, Y') ?? '' }}
                            </td>
                            <td>
                                {{ $booking->evisa_booking_detail->entry_port ?? '' }}
                            </td>
                            <td>
                                {{ $booking->evisa_booking_detail->purpose->label() ?? '' }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        @endif
        <div class="section overflow-hidden">
            <table class="w-100">
                <tr>
                    <td width="60%" style="text-align: left">
                        <table class="table">
                            <tr>
                                <td class="bold">Order Transaction</td>
                                <td>{{ $booking->order->tran_id }}</td>
                            </tr>
                            <tr>
                                <td class="bold">Payment Method</td>
                                <td>{{ $booking->order->paymentgateway->name }}</td>
                            </tr>
                            <tr>
                                <td class="bold">Payment Status</td>
                                <td>{{ $booking->order->payment_status->label() }}</td>
                            </tr>
                        </table>
                    </td>
                    <td width="40%">
                        <table class="table">
                            <tr>
                                <td class="bold">Sub Total</td>
                                <td>
                                    @if ($booking->order->subtotal)
                                        BDT {{ number_format($booking->order->subtotal) }}
                                    @else
                                        0
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td class="bold">Discount</td>
                                <td>
                                    @if ($booking->order->coupon_amount)
                                        BDT {{ number_format($booking->order->coupon_amount) }}
                                    @else
                                        0
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td class="bold">Total Amount</td>
                                <td>
                                    @if ($booking->order->total_amount)
                                        BDT {{ number_format($booking->order->total_amount) }}
                                    @else
                                        0
                                    @endif
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
        </div>
    @endif

    <footer class="footer text-center">
        <h1>Thank you for being with us!</h2>
            <p>ANY QUERY:{{ $globalSettings->phone ?? '' }}</p>
    </footer>
</body>

</html>
