<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use App\Models\HotelRoom;
use App\Enum\HotelRoomStatus;
use Illuminate\Console\Command;

class UpdateHotelRoomAvailability extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'hotel:update-room-status';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Auto-update hotel room status to Available if booking is completed';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $today = Carbon::today();

        $updatedCount = 0;

        HotelRoom::with(['hotelRoomBookingItems.hotelroombooking'])->get()->each(function ($room) use ($today, &$updatedCount) {
            // Get all bookings for this room
            $latestBooking = $room->hotelRoomBookingItems
                ->sortByDesc(fn ($item) => $item->hotelroombooking->check_out ?? null)
                ->first();

            // If no bookings, skip
            if (!$latestBooking || !$latestBooking->hotelroombooking) {
                return;
            }

            $checkOut = $latestBooking->hotelroombooking->check_out;

            // If booking ended before today, mark as available
            if ($checkOut && $checkOut < $today && $room->status === HotelRoomStatus::Booked) {
                $room->update(['status' => HotelRoomStatus::Available]);
                $updatedCount++;
            }
        });

        $this->info("Updated $updatedCount room(s) to Available.");
    }
}
