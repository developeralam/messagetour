<?php

namespace App\Enum;

enum BankStatus: int
{
    case Active = 1;
    case Inactive = 2;

    public function label(): string
    {
        return match ($this) {
            self::Active => 'Active',
            self::Inactive => 'Inactive',
        };
    }
    public static function getStatuses(): array
    {
        return array_map(function ($case) {
            return ['id' => $case->value, 'name' => $case->label()];
        }, self::cases());
    }
}
