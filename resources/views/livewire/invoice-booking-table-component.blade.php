<div>
    @php
        $booking = $order->sourceable;
    @endphp

    @if ($booking instanceof \App\Models\TourBooking)
        {{-- Tour Booking Table --}}
        <table class="w-full border border-gray-300 mt-6">
            <thead class="bg-gray-200 text-gray-700">
                <tr>
                    <th class="p-2 border">Tour Name</th>
                    <th class="p-2 border">Location</th>
                    <th class="p-2 border">Date</th>
                    <th class="p-2 border">Price</th>
                    <th class="p-2 border">Total</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td class="p-2 border">{{ $booking->tour->title ?? '' }}</td>
                    <td class="p-2 border">{{ $booking->tour->location ?? '' }}</td>
                    <td class="p-2 border">{{ $booking->tour->start_date->format('d M,Y') ?? '' }}</td>
                    <td class="p-2 border">{{ $booking->tour->end_date->format('d M,Y') ?? '' }}</td>
                </tr>
            </tbody>
        </table>
    @elseif ($booking instanceof \App\Models\HotelRoomBooking)
        {{-- Hotel Booking Table Example --}}
        <table class="w-full border border-gray-300 mt-6">
            <thead class="bg-gray-200 text-gray-700">
                <tr>
                    <th class="p-2 border">Room</th>
                    <th class="p-2 border">Hotel</th>
                    <th class="p-2 border">Total Room</th>
                    <th class="p-2 border">Amount</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td class="p-2 border">
                        @foreach ($booking->hotelbookingitems as $item)
                            {{ $item->room->room_no ?? 'N/A' }}@if (!$loop->last)
                                ,
                            @endif
                        @endforeach
                    </td>
                    <td class="p-2 border">{{ $booking->hotelbookingitems->room->hotel->name ?? '' }}</td>
                    <td class="p-2 border">{{ $booking->hotelbookingitems->count() }}</td>
                    <td class="p-2 border">{{ $booking->order->total_amount ?? '' }}</td>
                </tr>
            </tbody>
        </table>
    @endif
</div>
