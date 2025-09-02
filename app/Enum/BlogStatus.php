<?php

namespace App\Enum;

enum BlogStatus: int
{   
    case Active = 0;
    case Inactive = 1;
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
            return ['id' => $case->value, 'name' => $case->name];
        }, self::cases());
    }
}