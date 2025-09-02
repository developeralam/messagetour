<?php

namespace App\Enum;

enum HotelRoomStatus: int
{
    case Available = 1;
    case Booked = 2;
    public function label(): string
    {
        return match ($this) {
            self::Available => 'Available',
            self::Booked => 'Booked',
        };
    }
    public static function getHotelRoomStatus(): array
    {
        return array_map(function ($case) {
            return ['id' => $case->value, 'name' => $case->label()];
        }, self::cases());
    }
}
