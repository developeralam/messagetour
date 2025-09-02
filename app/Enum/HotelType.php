<?php

namespace App\Enum;

enum HotelType: int
{
    case Hotel = 1;
    case RoomShare = 2;
    case Appertment = 3;

    public function label(): string
    {
        return match ($this) {
            self::Hotel => 'Hotel',
            self::RoomShare => 'Room Share',
            self::Appertment => 'Appertment',
        };
    }
    public static function getHotelTypes(): array
    {
        return array_map(function ($case) {
            return ['id' => $case->value, 'name' => $case->label()];
        }, self::cases());
    }
}
