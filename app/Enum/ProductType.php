<?php

namespace App\Enum;

enum ProductType: int
{
    case Tour = 1;
    case Gear = 2;
    case Hotel = 3;
    case Visa = 4;
    case Car = 5;
    case All = 6;

    public function label(): string
    {
        return match ($this) {
            self::Hotel => 'Hotel',
            self::Tour => 'Tour',
            self::Gear => 'Gear',
            self::Visa => 'Visa',
            self::Car => 'Car',
            self::All => 'All',
        };
    }
    public static function getTypes(): array
    {
        return array_map(function ($case) {
            return ['id' => $case->value, 'name' => $case->label()];
        }, self::cases());
    }
}
