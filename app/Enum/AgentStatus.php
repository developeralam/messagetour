<?php

namespace App\Enum;

enum AgentStatus: int
{
    case Approve = 1;
    case Pending = 0;

    public function label(): string
    {
        return match ($this) {
            self::Approve => 'Approve',
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
