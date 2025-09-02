<?php

namespace App\Enum;

enum AmountType: int
{
    case Fixed = 1;
    case Percent = 2;

    public function label(): string
    {
        return match ($this) {
            self::Fixed => 'Fixed',
            self::Percent => 'Percent',
        };
    }
    public static function getTypes(): array
    {
        return array_map(function ($case) {
            return ['id' => $case->value, 'name' => $case->label()];
        }, self::cases());
    }
}
