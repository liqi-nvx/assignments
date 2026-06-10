<?php
namespace App\Services\Central;

use App\Repositories\Central\TenantRepository;
use App\Models\Tenant;
use Illuminate\Support\Facades\DB;
use Exception;

class TenantService
{
    protected TenantRepository $tenantRepo;

    public function __construct(TenantRepository $tenantRepo)
    {
        $this->tenantRepo = $tenantRepo;
    }

    public function registerTenant(array $data): Tenant
    {
        // 关键点：stancl/tenancy 在创建 Tenant 模型时会在底层同步触发 DB::statement 自动建库，
        // 故绝对不能将其包裹在 BEGIN...COMMIT 的内部显式事务块中，否则 PostgreSQL 报错。
        try {
            $tenant = $this->tenantRepo->create($data);

            // 域名绑定可以通过显式事务进行保障
            DB::transaction(function () use ($tenant, $data) {
                $this->tenantRepo->createDomain($tenant, $data['id'] . '.localhost');
            });

            return $tenant;
        } catch (Exception $e) {
            if (isset($tenant) && $tenant) {
                DB::purge('tenant'); 
                DB::disconnect('tenant'); 
                $tenant->delete();
            }
            throw $e;
        }
    }
}