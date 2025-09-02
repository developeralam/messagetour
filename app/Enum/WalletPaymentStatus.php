<?php

namespace App\Enum;

enum WalletPaymentStatus: int
{
    case Declined = 0;
    case Approved = 1;
    case Pending = 2;

    public function label(): string
    {
        return match ($this) {
            self::Declined => 'Declined',
            self::Approved => 'Approved',
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
