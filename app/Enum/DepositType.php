<?php

namespace App\Enum;

enum DepositType: int
{
    case Bank = 1;
    case Online = 2;
    case Cash = 3;
    case Bkash = 4;
    case Rocket = 5;

    public function label(): string
    {
        return match ($this) {
            self::Bank => 'Bank',
            self::Online => 'Online',
            self::Cash => 'Cash',
            self::Bkash => 'Bkash',
            self::Rocket => 'Rocket',
        };
    }

    public static function getDepositTypes(): array
    {
        return array_map(function ($case) {
            return ['id' => $case->value, 'name' => $case->label()];
        }, self::cases());
    }
}
