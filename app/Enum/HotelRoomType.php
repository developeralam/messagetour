<?php

namespace App\Enum;

enum HotelRoomType: int
{
    case Economy = 1;
    case Standard = 2;
    case Deluxe = 3;
    case Premium = 4;
    public function label(): string
    {
        return match ($this) {
            self::Economy => 'Economy',
            self::Standard => 'Standard',
            self::Deluxe => 'Deluxe',
            self::Premium => 'Premium',
        };
    }
    public static function getHotelRoomTypes(): array
    {
        return array_map(function ($case) {
            return ['id' => $case->value, 'name' => $case->label()];
        }, self::cases());
    }
}
