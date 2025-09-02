<?php

namespace App\Enum;

enum CorporateQueryStatus: int
{
    case Pending = 0;
    case Inreview = 1;
    case Answered = 2;

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pending',
            self::Inreview => 'In-review',
            self::Answered => 'Answered',
        };
    }
    public static function getOfferStatuses(): array
    {
        return array_map(function ($case) {
            return ['id' => $case->value, 'name' => $case->label()];
        }, self::cases());
    }
}
