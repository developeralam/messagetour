<?php

namespace App\Enum;

enum AccountPaymentType: int
{
    case Paid = 1;
    case Unpaid = 2;

    public function label(): string
    {
        return match ($this) {
            self::Paid => 'Paid',
            self::Unpaid => 'Unpaid',
        };
    }
    public static function getPaymentTypes(): array
    {
        return array_map(function ($case) {
            return ['id' => $case->value, 'name' => $case->label()];
        }, self::cases());
    }
}
