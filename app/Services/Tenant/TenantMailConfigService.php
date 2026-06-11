<?php

namespace App\Services\Tenant;

use App\Models\Tenant\TenantMailSetting;
use Illuminate\Support\Facades\Mail;

class TenantMailConfigService
{
    public static function setTenantMailConfig(): bool
    {
        if (config('app.env') === 'local' && env('MAIL_PORT') == 1025) {
            config([
                'mail.mailers.smtp.host'       => '127.0.0.1',
                'mail.mailers.smtp.port'       => 1025,
                'mail.mailers.smtp.username'   => null,
                'mail.mailers.smtp.password'   => null,
                'mail.mailers.smtp.encryption' => null,
            ]);
            Mail::forgetMailers();
            return true;
        }

        $settings = TenantMailSetting::first();

        if (!$settings || empty($settings->mail_username) || empty($settings->mail_password)) {
            return false;
        }

        config([
            'mail.mailers.smtp.host'       => 'smtp.gmail.com',
            'mail.mailers.smtp.port'       => 587,
            'mail.mailers.smtp.encryption' => 'tls',
            'mail.mailers.smtp.scheme'     => 'tls',

            'mail.mailers.smtp.username'   => $settings->mail_username,
            'mail.mailers.smtp.password'   => $settings->mail_password,
            'mail.from.address'            => $settings->mail_username,
            'mail.from.name'               => $settings->sender_name ?? config('app.name'),
        ]);

        Mail::forgetMailers();

        return true;
    }
}