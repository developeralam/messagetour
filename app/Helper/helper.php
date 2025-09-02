<?php

use App\Models\GlobalSettings;
use Illuminate\Support\Facades\Config;

if (!function_exists('sms_send')) {

    function sms_send($number, $message)
    {
        $settings = GlobalSettings::first();

        $url = "http://bulksmsbd.net/api/smsapi";
        $api_key = $settings->sms_api_key;
        $senderid = $settings->sms_sender_id;

        $data = [
            "api_key" => $api_key,
            "senderid" => $senderid,
            "number" => $number,
            "message" => $message
        ];
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $response = curl_exec($ch);
        curl_close($ch);
        return $response;
    }
}
if (!function_exists('setMailConfig')) {

    function setMailConfig()
    {
        $mailSetting = GlobalSettings::first();

        if ($mailSetting) {
            $data = [
                'driver'     => $mailSetting->mail_mailer,
                'host'       => $mailSetting->mail_host,
                'port'       => $mailSetting->mail_port,
                'username'   => $mailSetting->mail_username,
                'encryption' => $mailSetting->mail_encryption,
                'password'   => $mailSetting->mail_password,
                'from'       => [
                    'address' => $mailSetting->mail_from_address,
                    'name'    => $mailSetting->mail_from_name,
                ],
            ];
            $appName = [
                'name' => $mailSetting->mail_from_name
            ];
            Config::set('app.name', $appName['name']);
            Config::set('mail', $data);
        }
    }
}
