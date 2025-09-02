<?php

namespace App\Enum;

enum VisaType: int
{
    case Evisa = 1;
    case Tourist = 2;
    case Business = 3;
    case Medical = 4;
    case Student = 5;
    case Worker = 6;
    public function label(): string
    {
        return match ($this) {
            self::Evisa => 'E-Visa',
            self::Tourist => 'Tourist',
            self::Business => 'Business',
            self::Medical => 'Medical',
            self::Student => 'Student',
            self::Worker => 'Worker',
        };
    }
    public static function getVisaTypes(): array
    {
        return array_map(function ($case) {
            return ['id' => $case->value, 'name' => $case->label()];
        }, self::cases());
    }
}
