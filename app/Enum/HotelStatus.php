<?php

namespace App\Enum;

enum HotelStatus: int
{
    case Pending = 0;
    case Inactive = 1;
    case Active = 2;

    public function label(): string
    {
        return match ($this) {
            self::Active => 'Active',
            self::Inactive => 'Inactive',
            self::Pending => 'Pending',
        };
    }
    public static function getHotelStatuses(): array
    {
        return array_map(function ($case) {
            return ['id' => $case->value, 'name' => $case->label()];
        }, self::cases());
    }
    public static function getLabelByValue(int $value): ?string
    {
        foreach (self::cases() as $case) {
            if ($case->value === $value) {
                return $case->label();
            }
        }

        return null;
    }
}
