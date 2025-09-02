<?php

namespace App\Enum;

enum MailPort:int
{
    case Default = 25;
    case Tls = 587;
    case Ssl = 465;

    public function label(): string
    {
        return match($this) {
            self::Default => '25',
            self::Tls => '587',
            self::Ssl => '465',
        };
    }
    public static function getPorts(): array
    {
        return array_map(function ($case) {
            return ['id' => $case->value, 'name' => $case->label()];
        }, self::cases());
    }
}
