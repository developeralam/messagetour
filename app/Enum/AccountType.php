<?php

namespace App\Enum;

enum AccountType: string
{
    case Asset = 'asset';
    case Liability = 'liability';
    case Expense = 'expense';
    case Revenue = 'revenue';

    public function label(): string
    {
        return match ($this) {
            self::Asset => 'Asset',
            self::Liability => 'Liability',
            self::Expense => 'Expense',
            self::Revenue => 'Revenue',
        };
    }

    public static function getTypes(): array
    {
        return array_map(function ($case) {
            return ['id' => $case->value, 'name' => $case->label()];
        }, self::cases());
    }
}
