<?php

namespace App\Enum;

enum DepositStatus: int
{
    case Pending = 0;
    case Approved = 1;
    case Declined = 2;

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pending',
            self::Approved => 'Approved',
            self::Declined => 'Declined',
        };
    }
    public static function getDepositStatus(): array
    {
        return array_map(function ($case) {
            return ['id' => $case->value, 'name' => $case->label()];
        }, self::cases());
    }
}
