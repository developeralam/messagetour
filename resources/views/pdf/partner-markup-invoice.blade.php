<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
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

        .w-50 {
            width: 50%;
        }

        .w-33 {
            width: 33%;
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

            \App\Models\VisaBooking::class => 'Visa Booking',

            \App\Models\TravelProductBooking::class => 'Product Booking',

            \App\Models\CarBooking::class => 'Car Booking',

            \App\Models\HotelRoomBooking::class => 'Hotel Room Booking',
        ];
        $typeLabel = $bookingTypes[get_class($booking)] ?? 'Unknown';

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
                <td width="50%">
                    <p>
                        <strong>Date:</strong>
                        {{ $order->created_at->format('d M, Y') }}
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
                                <td>{{ $custom_amount }}</td>
                            </tr>
                            <tr>
                                <td class="bold">Total Amount</td>
                                <td>{{ $custom_amount }}</td>
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
                                <td>{{ $custom_amount }}</td>
                            </tr>
                            <tr>
                                <td class="bold">Total Amount</td>
                                <td>{{ $custom_amount }}</td>
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
                        <th>CAR</th>
                        <th>CAR RENT TYPE</th>
                        <th>PICKUP</th>
                        <th>PICKUP DATE TIME</th>
                    </tr>
                </thead>
                <tbody>
                    <tr style="text-align: center">
                        <td>
                            {{ $booking->car->title ?? '' }}
                        </td>
                        <td>
                            {{ $booking->rent_type->label() ?? '' }}
                        </td>
                        <td>
                            {{ $booking->pickup ?? '' }}
                        </td>
                        <td>
                            {{ $booking->pickup_datetime->format('d M, Y, H:i') ?? '' }}
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
                                <td>{{ $custom_amount }}</td>
                            </tr>
                            <tr>
                                <td class="bold">Total Amount</td>
                                <td>{{ $custom_amount }}</td>
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
                                <td>{{ $custom_amount }}</td>
                            </tr>
                            <tr>
                                <td class="bold">Total Amount</td>
                                <td>{{ $custom_amount }}</td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
        </div>
    @elseif ($booking instanceof \App\Models\VisaBooking)
        <div class="section">
            <h3 style="text-align: center; margin:40px 0 20px 0; font-size:20px;" class="text-uppercase bold">Visa
                Booking Information</h3>
            <table class="table">
                <thead style="background: #d9f3d2">
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
                        <td>
                            {{ $booking->visa->title ?? '' }}
                        </td>
                        <td>
                            {{ $booking->visa->origin->name ?? '' }}
                        </td>
                        <td>
                            {{ $booking->visa->destination->name ?? '' }}
                        </td>
                        <td>
                            {{ $booking->total_traveller ?? '' }}
                        </td>
                        <td>
                            {{ $booking->docuemnts_collection_date->format('d M, Y') ?? '' }}
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
                                <td>{{ $custom_amount }}</td>
                            </tr>
                            <tr>
                                <td class="bold">Total Amount</td>
                                <td>{{ $custom_amount }}</td>
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
