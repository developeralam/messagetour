<?php

namespace App\Enum;

enum CarStatus: int
{
    case Available = 1;
    case Unavailable = 2;
    case Pending = 3;

    public function label(): string
    {
        return match ($this) {
            self::Available => 'Available',
            self::Unavailable => 'Unavailable',
            self::Pending => 'Pending',
        };
    }
    public static function getStatuses(): array
    {
        return array_map(function ($case) {
            return ['id' => $case->value, 'name' => $case->label()];
        }, self::cases());
    }
}
