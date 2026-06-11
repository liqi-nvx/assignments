<?php

namespace App\Services\Tenant;

use App\Repositories\Tenant\MailSettingRepository;
use App\Models\Tenant\TenantMailSetting;

class MailSettingService
{
    protected MailSettingRepository $mailSettingRepo;

    public function __construct(MailSettingRepository $mailSettingRepo)
    {
        $this->mailSettingRepo = $mailSettingRepo;
    }

    public function getSettingsForEdit(): array
    {
        $settings = $this->mailSettingRepo->getFirst();

        return [
            'sender_name'   => $settings->sender_name ?? '',
            'mail_username' => $settings->mail_username ?? '',
            'has_password'  => !empty($settings->mail_password),
        ];
    }

    public function updateSettings(array $data): TenantMailSetting
    {
        return $this->mailSettingRepo->updateOrCreate($data);
    }
}