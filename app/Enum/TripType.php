<?php

namespace App\Enum;

enum TripType: int
{
    case HalfDay = 1;
    case FullDay = 0;

    public function label(): string
    {
        return match ($this) {
            self::HalfDay => 'Half Day',
            self::FullDay => 'Full Day',
        };
    }
    public static function getTypes(): array
    {
        return array_map(function ($case) {
            return ['id' => $case->value, 'name' => $case->label()];
        }, self::cases());
    }
}