<?php

namespace App\Repositories\Tenant;

use App\Models\Tenant\TenantMailSetting;

class MailSettingRepository
{
    public function getFirst(): TenantMailSetting
    {
        return TenantMailSetting::first() ?? new TenantMailSetting();
    }

    public function updateOrCreate(array $data): TenantMailSetting
    {
        $settings = TenantMailSetting::firstOrNew();

        $settings->sender_name   = $data['sender_name'];
        $settings->mail_username = $data['mail_username'];

        if (!empty($data['mail_password'])) {
            $settings->mail_password = $data['mail_password'];
        }

        $settings->save();

        return $settings;
    }
}