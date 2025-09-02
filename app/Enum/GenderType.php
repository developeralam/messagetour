<?php

namespace App\Enum;

enum GenderType: int
{
    case Male = 1;
    case Female = 2;
    case Others = 3;

    public function label(): string
    {
        return match ($this) {
            self::Male => 'Male',
            self::Female => 'Female',
            self::Others => 'Others',
        };
    }
    public static function getGenderTypes(): array
    {
        return array_map(function ($case) {
            return ['id' => $case->value, 'name' => $case->label()];
        }, self::cases());
    }
}
