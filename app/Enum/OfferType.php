<?php

namespace App\Enum;

enum OfferType: int
{
    case Coupon = 1;
    case Card = 2;
    case MFS = 3;

    public function label(): string
    {
        return match ($this) {
            self::Coupon => 'Coupon',
            self::Card => 'Card',
            self::MFS => 'MFS',
        };
    }
    public static function getOfferTypes(): array
    {
        return array_map(function ($case) {
            return ['id' => $case->value, 'name' => $case->label()];
        }, self::cases());
    }
}
