<?php

namespace App\Enum;

enum ShippingMethod: int
{
    case InsideDhaka = 1;
    case OutsideDhaka = 2;
    public function label(): string
    {
        return match ($this) {
            self::InsideDhaka => 'Inside Dhaka',
            self::OutsideDhaka => 'Outside Dhaka',
        };
    }
    public function charge(): int
    {
        return match ($this) {
            self::InsideDhaka => 100,
            self::OutsideDhaka => 150,
        };
    }
    public static function getMethods(): array
    {
        return array_map(function ($case) {
            return ['id' => $case->value, 'name' => $case->label()];
        }, self::cases());
    }
    public static function getChargeById(int $id): int
    {
        return self::tryFrom($id)?->charge() ?? 0;
    }
}