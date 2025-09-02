<?php

namespace App\Services;

use App\Models\Agent;
use App\Models\Order;
use App\Enum\UserType;
use App\Models\TourBooking;
use App\Models\HotelRoomBooking;
use App\Models\TravelProductBooking;

class AgentWalletServices
{
    public static function handle(Order $order)
    {
        if (!$order->sourceable_type || !$order->sourceable) {
            return;
        }

        $booking = $order->sourceable;
        $createdBy = null;

        // Find the created_by ID depending on booking type
        if ($booking instanceof HotelRoomBooking) {
            $createdBy = $booking->hotelbookingitems?->room?->hotel?->created_by;
        } elseif ($booking instanceof TourBooking) {
            $createdBy = $booking->tour?->created_by;
        } elseif ($booking instanceof TravelProductBooking) {
            $createdBy = $booking->travelproduct?->created_by;
        }

        if (!$createdBy) {
            return;
        }

        // Get the agent and their associated user
        $agent = Agent::with('user')->where('user_id', $createdBy)->first();
        if ($agent && $agent->user && $agent->user->type == UserType::Agent) {
            $agent->user->wallet = ($agent->user->wallet ?? 0) + $order->total_amount;
            $agent->user->save();
        }
    }
}
