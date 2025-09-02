<?php

namespace App\Enum;

enum VisaPurpose: int
{
    case ToursimBusinessConference = 1;
    case Student = 2;
    case FamilyVisit = 3;
    case Medical = 4;
    case UmrahWorker = 5;
    case Embassy = 6;

    public function label(): string
    {
        return match ($this) {
            self::ToursimBusinessConference => 'For Tourism, Business/Conference',
            self::Student => 'For Student',
            self::FamilyVisit => 'For Family Visit',
            self::Medical => 'For Medical',
            self::UmrahWorker => 'For Umrah Worker',
            self::Embassy => 'For Embassy Appointment',
        };
    }
    public static function getVisaPurposes(): array
    {
        return array_map(function ($case) {
            return ['id' => $case->value, 'name' => $case->label()];
        }, self::cases());
    }
}
