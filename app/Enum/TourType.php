<?php

namespace App\Enum;

enum TourType: int
{
    case Tour = 10;
    case EventsFixedDate = 11;
    case VisitBD = 12;
    case Resort = 13;
    case Daylong = 14;
    public function label(): string
    {
        return match ($this) {
            self::Tour => 'Tour',
            self::EventsFixedDate => 'Events Fixed Date',
            self::VisitBD => 'Visit BD',
            self::Resort => 'Resort',
            self::Daylong => 'Daylong',
        };
    }
    public static function getTourTypes(): array
    {
        return array_map(function ($case) {
            return ['id' => $case->value, 'name' => $case->label()];
        }, self::cases());
    }
}
