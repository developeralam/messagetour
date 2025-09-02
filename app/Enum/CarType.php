<?php

namespace App\Enum;

enum CarType: int
{
    case Sedan = 1;
    case Microbus = 2;
    case Minibus = 3;
    case Bus = 4;

    public function label(): string
    {
        return match ($this) {
            self::Sedan => 'Sedan',
            self::Microbus => 'Microbus',
            self::Minibus => 'Minibus',
            self::Bus => 'Bus',
        };
    }
    public static function getTypes(): array
    {
        return array_map(function ($case) {
            return ['id' => $case->value, 'name' => $case->label()];
        }, self::cases());
    }
}
