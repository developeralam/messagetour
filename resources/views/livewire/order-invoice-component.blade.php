<div>
    <div class="bg-gray-100 p-10">
        <div class="max-w-3xl mx-auto bg-white shadow-md p-6 rounded-lg">
            <div class="bg-black text-white text-center py-2 rounded-t-lg font-bold text-lg">INVOICE</div>

            <div class="flex justify-between items-center mt-4">
                <div>
                    <h1 class="text-red-600 text-xl font-bold">My<span class="text-black">flight</span></h1>
                    <p class="text-gray-600 text-sm">admin@flivaly.com</p>
                    <p class="text-gray-600 text-sm">Uttara, Dhaka, Bangladesh</p>
                    <p class="text-gray-600 text-sm">+8801700668403</p>
                </div>
                <div class="text-right">
                    <p class="text-gray-600 text-sm font-semibold">{{ $order->user->name ?? '' }}</p>
                    <p class="text-gray-600 text-sm">{{ $order->phone ?? '' }}</p>
                    <p class="text-gray-600 text-sm">{{ $order->user->email ?? '' }}</p>
                    <p class="text-gray-600 text-sm">{{ $order->user->customer->address ?? '' }}</p>
                </div>
            </div>

            <div class="border border-gray-300 rounded-lg mt-6 p-4">
                <div class="grid grid-cols-3 gap-4">
                    <div class="text-gray-600 text-sm">Invoice #</div>
                    <div class="col-span-2 text-gray-800">{{ $order->id }}</div>
                    <div class="text-gray-600 text-sm">Date</div>
                    <div class="col-span-2 text-gray-800">{{ $order->created_at->format('d M,Y') ?? '' }}</div>
                    <div class="text-gray-600 text-sm">Total Amount</div>
                    <div class="col-span-2 text-gray-800 font-semibold">৳ {{ $order->total_amount ?? '' }}</div>
                </div>
            </div>

            <livewire:invoice-booking-table-component :order="$order" />

            <div class="flex justify-end mt-4">
                <div class="w-1/3">
                    <div class="flex justify-between border-b p-2">
                        <span class="text-gray-600 text-sm">Sub Total</span>
                        <span class="text-gray-800">৳ {{ $order->total_amount ?? '' }}</span>
                    </div>
                    <div class="flex justify-between p-2 font-semibold">
                        <span class="text-gray-600 text-sm">Grand Total</span>
                        <span class="text-gray-800">৳ {{ $order->total_amount ?? '' }}</span>
                    </div>
                </div>
            </div>

            <p class="mt-4 font-semibold">Payment Method : <span class="text-gray-700">{{ $order->paymentgateway->name ?? '' }}</span></p>

            <div class="border-t border-gray-300 mt-6 pt-4 text-gray-600 text-sm text-center">
                <p>Thank you for your business</p>
            </div>
        </div>
    </div>
</div>
