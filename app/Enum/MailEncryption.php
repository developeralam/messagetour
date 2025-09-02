<?php

namespace App\Enum;

enum MailEncryption:string
{
    case Tls = 'tls';
    case Ssl = 'ssl';

    public function label(): string
    {
        return match($this) {
            self::Tls => 'Tls',
            self::Ssl => 'Ssl',
        };
    }
    public static function getEncryption(): array
    {
        return array_map(function ($case) {
            return ['id' => $case->value, 'name' => $case->label()];
        }, self::cases());
    }
}
