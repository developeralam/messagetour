<?php

namespace App\Enum;

enum GroupFlightType: int
{
    case Regular = 0;
    case Umrah = 1;

    public function label(): string
    {
        return match ($this) {
            self::Regular => 'Regular',
            self::Umrah => 'Umrah',
        };
    }
    public static function getGroupFlightTypes(): array
    {
        return array_map(function ($case) {
            return ['id' => $case->value, 'name' => $case->label()];
        }, self::cases());
    }
}
