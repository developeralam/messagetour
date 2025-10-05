<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Ledger Report</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Open+Sans:ital,wght@0,300..800;1,300..800&display=swap');

        body {
            font-family: 'Open Sans', sans-serif;
        }

        * {
            margin: 0;
            padding: 0;
            color: #6a6a6a;
        }

        section {
            padding: 20px 50px 20px 50px;
            font-size: 14px;
        }

        p {
            margin-bottom: 5px;
        }

        .text-right {
            text-align: right;
        }

        .t-left {
            text-align: left;
        }

        .f-right {
            float: right;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
            border: 1px solid #898888;
        }

        .table td,
        th {
            border: 1px solid #898888;
            padding: 5px;
        }

        .border-top-3 {
            border-top: 3px solid #898888;
        }

        .pricing {
            overflow: hidden;
            border: 1px solid #000;
        }

        .border-left-1 {
            border-left: 1px solid #000;
        }

        .striped-table {
            width: 70%;
            border-collapse: collapse;
        }

        .striped-table td {
            padding: 10px;
        }

        .striped-table tr:nth-child(odd) {
            background-color: #f5f5f5;
        }

        .price-section {
            padding: 15px;
        }

        .border-top-bottom {
            border-top: 3px solid #000;
            border-bottom: 3px solid #000;
        }

        .text-underline {
            text-decoration: underline;
        }

        .price-summery {
            padding: 10px;
        }

        .signature_section {
            padding-top: 50px;
        }

        .signature_section span {
            padding-top: 2px;
            border-top: 1px solid #000;
        }

        .float-right {
            float: right;
        }

        .mt-10 {
            margin-top: 15px;
        }

        .mt-50 {
            margin-top: 50px;
        }
    </style>
</head>

<body>
    <section>
        <div style="height: 80px">
            <table style="width: 100%">
                <tr>
                    <td style="width: 120px">
                        @php
                            $business = auth()->user()->agent;
                            if ($business && $business->business_logo) {
                                $imagePath = $business->business_logo_link;
                                if (file_exists(public_path('storage/' . $business->business_logo))) {
                                    $imageData = base64_encode(file_get_contents($imagePath));
                                    $src = 'data:image/webp;base64,' . $imageData;
                                } else {
                                    $src = asset('logo.png');
                                }
                            } else {
                                $src = asset('logo.png');
                            }
                        @endphp

                        <img src="{{ $src }}" width="80" height="60" style="margin-right: 15px" />
                    </td>
                    <td>
                        <p>{{ $business->business_name ?? 'Business Name' }}</p>
                        <p>{{ $business->country->name ?? 'Country' }}, {{ $business->district->name ?? 'City' }}</p>
                        <p>Contact: {{ $business->business_phone ?? 'Phone' }}, {{ $business->business_email ?? 'Email' }}</p>
                        <p>Web: {{ $business->business_email ?? 'Website' }}</p>
                    </td>
                    <td style="text-align: right">
                        <span style="background: #000; color: #fff; padding: 3px 5px">Account Report</span>
                    </td>
                </tr>
            </table>
        </div>

        <div style="margin: 10px 0px">
            <h4>Account Name: {{ $account->name ?? '' }}</h4>
            <h4>Date Range: {{ $from_date ?? '' }} - {{ $to_date }}</h4>
        </div>
        @if (in_array($account_type, ['revenue', 'expense']))
            <div>
                <table class="table">
                    <thead>
                        <tr>
                            <th>SL</th>
                            <th>Date</th>
                            <th>Account</th>
                            <th>Remarks</th>
                            <th>Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($transactions as $receive_transcation)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $receive_transcation->date }}</td>
                                <td>{{ $receive_transcation->description }}</td>
                                <td>{{ $receive_transcation->related_payment->note ?? '' }}</td>

                                @if ($account_type == 'revenue')
                                    <td>{{ $receive_transcation->credit }}</td>
                                @else
                                    <td>{{ $receive_transcation->debit }}</td>
                                @endif
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="4" class="text-right"><strong>Total</strong></td>

                            @if ($account_type == 'revenue')
                                <td>{{ $transactions->sum('credit') }}</td>
                            @else
                                <td>{{ $transactions->sum('debit') }}</td>
                            @endif
                        </tr>
                    </tfoot>
                </table>
            </div>
        @else
            <div style="width: 50%; float: left">
                <table class="table">
                    <thead>
                        <tr>
                            <th>SL</th>
                            <th>Date</th>
                            <th>Account</th>
                            <th>Remarks</th>
                            <th>Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        @isset($opening_balance)
                            <tr>
                                <td colspan="4" style="text-align: center">Opening Balance</td>
                                <td>{{ $opening_balance ?? 0 }}</td>
                            </tr>
                        @endisset

                        @foreach ($receive_transcations as $receive_transcation)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $receive_transcation->date }}</td>
                                <td>{{ $receive_transcation->description }}</td>
                                <td>{{ $receive_transcation->related_payment->note ?? '' }}</td>
                                <td>{{ $receive_transcation->debit }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        @if (!empty($opening_balance))
                            <tr>
                                <td colspan="4" class="text-right"><strong>Total Received</strong></td>
                                <td>{{ $receive_transcations->sum('debit') + $opening_balance ?? 0 }}</td>
                            </tr>
                        @else
                            <tr>
                                <td colspan="4" class="text-right"><strong>Total Received</strong></td>
                                <td>{{ $receive_transcations->sum('debit') }}</td>
                            </tr>
                        @endif
                    </tfoot>
                </table>
            </div>
            <div style="width: 50%; float: left">
                <table class="table">
                    <thead>
                        <tr>
                            <th>SL</th>
                            <th>Date</th>
                            <th>Account</th>
                            <th>Remarks</th>
                            <th>Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($payment_transcations as $payment_transcation)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $payment_transcation->date }}</td>
                                <td>{{ $payment_transcation->account->name }}</td>
                                <td>{{ $payment_transcation->related_payment->note ?? '' }}</td>
                                <td>{{ $payment_transcation->credit }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        @if (!empty($opening_balance))
                            <tr>
                                <td colspan="4" class="text-right"><strong>Total Repayment</strong></td>
                                <td>{{ $payment_transcations->sum('credit') }}</td>
                            </tr>
                            <tr>
                                <td colspan="4" class="text-right"><strong>Balance</strong></td>
                                <td>{{ $receive_transcations->sum('debit') + $opening_balance - $payment_transcations->sum('credit') }}
                                </td>
                            </tr>
                        @else
                            <tr>
                                <td colspan="4" class="text-right"><strong>Total Repayment</strong></td>
                                <td>{{ $payment_transcations->sum('credit') }}</td>
                            </tr>
                            <tr>
                                <td colspan="4" class="text-right"><strong>Balance</strong></td>
                                <td>{{ $receive_transcations->sum('debit') - $payment_transcations->sum('credit') }}
                                </td>
                            </tr>
                        @endif
                    </tfoot>
                </table>
            </div>
        @endif
    </section>
</body>

</html>
