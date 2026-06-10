<?php
namespace App\Repositories\Tenant;

use App\Models\Tenant\User;
use Illuminate\Support\Facades\Hash;

class TenantUserRepository
{
    public function create(array $data): User
    {
        return User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);
    }
    
    public function update(User $user, array $data): bool
    {
        if (!empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }
        return $user->update($data);
    }
}