<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Tenant;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed. the application's database.
     */
    public function run(): void
    {
        // 1. 注册 3 个演示租户
        $tenants = ['alpha', 'beta', 'gamma'];

        foreach ($tenants as $id) {
            // 避免重复创建
            if (!Tenant::where('id', $id)->exists()) {
                $tenant = Tenant::create(['id' => $id]);
                $tenant->domains()->create(['domain' => $id . '.localhost']);
            }
        }

        // 2. 遍历租户，穿梭进入租户独立的 DB 跑数据
        Tenant::all()->each(function ($tenant) {
            $tenant->run(function () {
                $this->call(TenantDataSeeder::class);
            });
        });
    }
}