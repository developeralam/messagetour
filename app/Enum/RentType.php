<?php

namespace App\Enum;

enum RentType: int
{
    case InsideDhaka = 1;
    case OutsideDhaka = 2;

    public function label(): string
    {
        return match ($this) {
            self::InsideDhaka => 'Inside Dhaka',
            self::OutsideDhaka => 'Outside Dhaka',
        };
    }
    public static function getTypes(): array
    {
        return array_map(function ($case) {
            return ['id' => $case->value, 'name' => $case->label()];
        }, self::cases());
    }
}
