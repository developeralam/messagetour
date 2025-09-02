<?php

namespace App\Enum;

enum PaymentStatus: int
{
    case Paid = 1;
    case Unpaid = 2;
    case Failed = 3;
    case Cancelled = 4;

    public function label(): string
    {
        return match ($this) {
            self::Paid => 'Paid',
            self::Unpaid => 'Unpaid',
            self::Failed => 'Failed',
            self::Cancelled => 'Cancelled',
        };
    }
    public static function getStatuses(): array
    {
        return array_map(function ($case) {
            return ['id' => $case->value, 'name' => $case->label()];
        }, self::cases());
    }
}
