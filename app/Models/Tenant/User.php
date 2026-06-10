<?php

namespace App\Models\Tenant;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    protected $table = 'users';
    
    use HasApiTokens;
    protected $fillable = ['name', 'email', 'password'];
    protected $hidden = ['password'];
}