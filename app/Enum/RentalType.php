<?php

namespace App\Enum;

enum RentalType: int
{
    case WithDriver = 1;
    case WithOutDriver = 0;

    public function label(): string
    {
        return match ($this) {
            self::WithDriver => 'With Driver',
            self::WithOutDriver => 'Without Driver',
        };
    }
    public static function getRentalTypes(): array
    {
        return array_map(function ($case) {
            return ['id' => $case->value, 'name' => $case->label()];
        }, self::cases());
    }
}
