<?php

namespace App\Enum;

enum TransactionStatus: int
{
    case PENDING = 0;
    case APPROVED = 1;
    case REJECTED = 2;

    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'Pending',
            self::APPROVED => 'Approved',
            self::REJECTED => 'Rejected',
        };
    }
    public static function getTransactionStatuses(): array
    {
        return array_map(function ($case) {
            return ['id' => $case->value, 'name' => $case->label()];
        }, self::cases());
    }
}
