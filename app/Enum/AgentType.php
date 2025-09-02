<?php

namespace App\Enum;

enum AgentType: int
{
    case General = 1;
    case Service = 2;

    public function label(): string
    {
        return match ($this) {
            self::General => 'General',
            self::Service => 'Service',
        };
    }
    public static function getTypes(): array
    {
        return array_map(function ($case) {
            return ['id' => $case->value, 'name' => $case->label()];
        }, self::cases());
    }
}
