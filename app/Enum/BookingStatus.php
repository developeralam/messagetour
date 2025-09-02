<?php

namespace App\Enum;

enum BookingStatus: int
{
    case Requested = 1;
    case Processing = 2;
    case Success = 3;
    case Canceled = 4;

    public function label(): string
    {
        return match ($this) {
            self::Requested => 'Requested',
            self::Processing => 'Processing',
            self::Success => 'Success',
            self::Canceled => 'Canceled',
        };
    }
    public static function getStatuses(): array
    {
        return array_map(function ($case) {
            return ['id' => $case->value, 'name' => $case->label()];
        }, self::cases());
    }
}
