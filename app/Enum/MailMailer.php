<?php

namespace App\Enum;

enum MailMailer:string
{
    case Smtp = 'smtp';
    case Sendmail = 'sendmail';
    case Mailgun = 'mailgun';
    case Log = 'log';
    case Postmark = 'postmark';

    public function label(): string
    {
        return match ($this) {
            self::Smtp => 'Smtp',
            self::Sendmail => 'Sendmail',
            self::Mailgun => 'Mailgun',
            self::Log => 'Log',
            self::Postmark => 'Postmark'
        };
    }
    
    public static function getMailers(): array
    {
        return array_map(function ($case) {
            return ['id' => $case->value, 'name' => $case->label()];
        }, self::cases());
    }
}