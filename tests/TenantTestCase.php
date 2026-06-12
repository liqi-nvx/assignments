<?php

namespace Tests;

use App\Models\Tenant;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\DB;

abstract class TenantTestCase extends BaseTestCase
{
    protected Tenant $tenant;
    protected string $tenantId = 'test';
    protected string $domain = 'test.localhost';

    protected function setUp(): void
    {
        parent::setUp();

        $this->cleanupTenantSystem();

        $this->tenant = Tenant::create(['id' => $this->tenantId]);
        $this->tenant->domains()->create(['domain' => $this->domain]);

        tenancy()->initialize($this->tenant);
    }

    protected function tearDown(): void
    {
        $this->cleanupTenantSystem();

        parent::tearDown();
    }

    protected function cleanupTenantSystem(): void
    {
        if (function_exists('tenancy') && tenancy()->initialized) {
            tenancy()->end();
        }

        DB::purge('tenant');
        DB::disconnect('tenant');

        $tenant = Tenant::find($this->tenantId);
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
    }
}