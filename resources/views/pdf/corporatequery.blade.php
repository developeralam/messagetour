<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <title>Invoice</title>
    <style>
        * {
            margin: 0;
            padding: 0;
        }

        body {
            font-family:
                DejaVu Sans,
                sans-serif;
            font-size: 12px;
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
    <div class="header">
        <table class="w-100">
            <tr>
                <td style="text-align: left">
                    <h3 class="text-uppercase" style="font-size: 30px">Corporate Query</h3>
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
                    <h2>{{ $query->name ?? '' }}</h2>
                    <p>
                        <strong>{{ $query->email ?? '' }}</strong>
                    </p>
                    <p>
                        <strong>{{ $query->phone ?? '' }}</strong>
                    </p>
                </td>
                <td width="10%"></td>
                <td width="50%">
                    <p>
                        <strong>Date:</strong>
                        {{ $query->created_at->format('d M ,Y') }}
                    </p>
                    <p>
                        <strong>Invoice: #</strong>
                        {{ $query->id }}
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
                <strong>FLYVALY.</strong>
            </span>
        </div>
        <table class="w-100">
            <tr>
                <td width="40%" class="border p-5">
                    <p>{{ optional($query->user->customer)->address ?: optional($query->user->agent)->primary_contact_address ?? '' }}
                    </p>
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

    <div class="section">
        <table class="table">
            <thead style="background: #d9f3d2">
                <tr>
                    <th>DESTINATION</th>
                    <th>GROUP SIZE</th>
                    <th>PROGRAM</th>
                    <th>HOTEL TYPE</th>
                    <th>HOTEL ROOM TYPE</th>
                    <th>TRAVEL START DATE</th>
                    <th>TRAVEL END DATE</th>
                    <th>MEALS</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>{{ $query->destination->name ?? '' }}</td>
                    <td>{{ $query->group_size ?? '' }}</td>
                    <td>{{ $query->program ?? '' }}</td>
                    <td>{{ $query->hotel_type->label() ?? '' }}</td>
                    <td>{{ $query->hotel_room_type->label() ?? '' }}</td>
                    <td>{{ $query->travel_start_date ?? '' }}</td>
                    <td>{{ $query->travel_end_date ?? '' }}</td>
                    <td>{{ $query->meals ?? '' }}</td>
                </tr>
            </tbody>
        </table>
        <table class="table">
            <thead style="background: #d9f3d2">
                <tr>
                    <th>VISA SERVICE</th>
                    <th>AIR TICKET</th>
                    <th>TOUR GUIDE</th>
                    <th>MEALS CHOICES</th>
                    <th>RECOMMEND PLACES</th>
                    <th>ACTIVITIES</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>{{ $query->visa_service == 1 ? 'YES' : 'NO' }}</td>
                    <td>{{ $query->air_ticket == 1 ? 'YES' : 'NO' }}</td>
                    <td>{{ $query->tour_guide == 1 ? 'YES' : 'NO' }}</td>
                    <td>{{ $query->meals_choices ?? '' }}</td>
                    <td>{{ $query->recommend_places ?? '' }}</td>
                    <td>{{ $query->activities ?? '' }}</td>
                </tr>
            </tbody>
        </table>
    </div>
    <footer class="footer text-center">
        <h1>Thank you for being with us!</h2>
            <p>ANY QUERY:{{ $globalSettings->phone ?? '' }}</p>
    </footer>
</body>

</html>
