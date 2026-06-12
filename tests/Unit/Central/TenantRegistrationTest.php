<?php

namespace Tests\Unit\Central;

use Tests\TestCase;
use App\Services\Central\TenantService;
use App\Repositories\Central\TenantRepository;
use App\Models\Tenant;
use Illuminate\Support\Facades\DB;
use Exception;

class TenantRegistrationTest extends TestCase
{
    protected function tearDown(): void
    {
        if (function_exists('tenancy') && tenancy()->initialized) {
            tenancy()->end();
        }
        DB::purge('tenant');
        DB::disconnect('tenant');

        $tenant = Tenant::find('new-brand');
        if ($tenant) {
            try {
                $tenant->delete();
            } catch (\Exception $e) {
                $dbName = $tenant->database()->getName();
                DB::connection('pgsql')->statement("
                    SELECT pg_terminate_backend(pg_stat_activity.pid)
                    FROM pg_stat_activity
                    WHERE pg_stat_activity.datname = ?
                      AND pid <> pg_backend_pid();
                ", [$dbName]);

                $tenant->delete();
            }
        }
        
        parent::tearDown();
    }

    /** @test */
    public function test_it_can_successfully_register_a_tenant_and_bind_its_localhost_domain()
    {
        $repo = new TenantRepository();
        $service = new TenantService($repo);

        $tenantData = ['id' => 'new-brand'];
        $tenant = $service->registerTenant($tenantData);

        $this->assertInstanceOf(Tenant::class, $tenant);
        $this->assertEquals('new-brand', $tenant->id);
        
        // 验证域名是否正确绑定
        $this->assertDatabaseHas('domains', [
            'tenant_id' => 'new-brand',
            'domain'    => 'new-brand.localhost'
        ], 'pgsql');
    }

    /** @test */
    public function test_it_purges_and_deletes_tenant_if_domain_binding_fails()
    {
        // 故意模拟一个会抛出异常的 Repository 来触发 catch 块
        $mockRepo = $this->createMock(TenantRepository::class);
        $mockRepo->method('create')->willReturn(Tenant::create(['id' => 'new-brand']));
        $mockRepo->method('createDomain')->willThrowException(new Exception("Postgres Connection Deadlock"));

        $service = new TenantService($mockRepo);

        $this->expectException(Exception::class);

        try {
            $service->registerTenant(['id' => 'new-brand']);
        } finally {
            // 验证异常发生后，tenant是否被干净地剔除
            $this->assertDatabaseMissing('tenants', ['id' => 'new-brand'], 'pgsql');
        }
    }
}