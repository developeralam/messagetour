<?php

namespace App\Enum;

enum OrderStatus: int
{
    case Pending = 1;
    case OnHold = 2;
    case Cancelled = 3;
    case Returned = 4;
    case Confirmed = 5;
    case Shipping = 6;
    case Delivered = 7;
    case Failed = 8;

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pending',
            self::OnHold => 'On Hold',
            self::Delivered => 'Delivered',
            self::Cancelled => 'Cancelled',
            self::Returned => 'Returned',
            self::Confirmed => 'Confirmed',
            self::Shipping => 'Shipping',
            self::Failed => 'Failed',
        };
    }

    public static function getStatuses(): array
    {
        return array_map(function ($case) {
            return ['id' => $case->value, 'name' => $case->label()];
        }, self::cases());
    }
}
