<?php

namespace App\Models;

use App\Enum\MailPort;
use App\Enum\MailMailer;
use App\Enum\MailEncryption;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class GlobalSettings extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'favicon',
        'logo',
        'contact_email',
        'support_email',
        'phone',
        'address',
        'facebook_url',
        'youtube_url',
        'linkedin_url',
        'instagram_url',
        'twitter_url',
        'reservation',
        'reservation_email',
        'account',
        'account_email',
        'mail_mailer',
        'mail_host',
        'mail_port',
        'mail_username',
        'mail_password',
        'mail_encryption',
        'mail_from_address',
        'mail_from_name',
        'sms_api_key',
        'sms_sender_id',
        'application_name',
    ];

    public function getLogoLinkAttribute()
    {
        if ($this->logo) {
            return Storage::disk('public')->url('/' . $this->logo);
        }
        return '/logo.png';
    }

    public function getFaviconLinkAttribute()
    {
        if ($this->favicon) {
            return Storage::disk('public')->url('/' . $this->favicon);
        }
    }
}
