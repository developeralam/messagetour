<?php

namespace App\Enum;

enum TravelProductStatus: int
{
    case Pending = 0;
    case Inactive = 1;
    case Active = 2;

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pending',
            self::Inactive => 'Inactive',
            self::Active => 'Active',
        };
    }
    public static function getStatus(): array
    {
        return array_map(function ($case) {
            return ['id' => $case->value, 'name' => $case->label()];
        }, self::cases());
    }
}
