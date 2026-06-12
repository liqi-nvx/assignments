<?php

namespace Tests\Unit\Tenant;

use Tests\TenantTestCase;
use App\Services\Tenant\MailSettingService;
use App\Repositories\Tenant\MailSettingRepository;
use App\Services\Tenant\TenantMailConfigService;
use App\Models\Tenant\TenantMailSetting;
use Illuminate\Support\Facades\Config;

class MailContainerIsolationTest extends TenantTestCase
{
    protected MailSettingService $mailService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->mailService = new MailSettingService(new MailSettingRepository());
    }

    /** @test */
    public function test_it_can_save_and_retrieve_tenant_smtp_settings()
    {
        $this->mailService->updateSettings([
            'sender_name'   => 'Platinum Premium Workshop',
            'mail_username' => 'recond-dealer@gmail.com',
            'mail_password' => 'secret-smtp-token'
        ]);

        $editViewData = $this->mailService->getSettingsForEdit();

        $this->assertEquals('Platinum Premium Workshop', $editViewData['sender_name']);
        $this->assertEquals('recond-dealer@gmail.com', $editViewData['mail_username']);
        $this->assertTrue($editViewData['has_password']);
    }

    /** @test */
    public function test_it_dynamically_reconfigures_global_mail_transport_and_destroys_singleton_container()
    {
        // 模拟切换生产/独立公有云环境
        Config::set('app.env', 'production');

        TenantMailSetting::create([
            'sender_name'   => 'Dynamic Tenant Brand',
            'mail_username' => 'allocated-mailbox@gmail.com',
            'mail_password' => 'securepass123'
        ]);

        // 执行动态热切换
        $isConfigured = TenantMailConfigService::setTenantMailConfig();

        $this->assertTrue($isConfigured);
        // 校验全局 config 容器是否已被注入并覆盖
        $this->assertEquals('allocated-mailbox@gmail.com', config('mail.mailers.smtp.username'));
        $this->assertEquals('Dynamic Tenant Brand', config('mail.from.name'));
    }
}