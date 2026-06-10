<?php
namespace App\Repositories\Tenant;

use App\Models\Tenant\User;

class TenantUserRepository
{
    public function create(array $data): User
    {
        return User::create($data);
    }
    
    public function update(User $user, array $data): bool
    {
        return $user->update($data);
    }
}