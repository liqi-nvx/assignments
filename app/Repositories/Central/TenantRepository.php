<?php
namespace App\Repositories\Central;

use App\Models\Tenant;

class TenantRepository
{
    public function create(array $data): Tenant
    {
        return Tenant::create(['id' => $data['id']]);
    }

    public function createDomain(Tenant $tenant, string $domain)
    {
        return $tenant->domains()->create(['domain' => $domain]);
    }
}