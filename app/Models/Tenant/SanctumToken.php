<?php

namespace App\Models\Tenant;

use Laravel\Sanctum\PersonalAccessToken as SanctumPersonalAccessToken;

class SanctumToken extends SanctumPersonalAccessToken
{
    protected $connection = 'tenant';
    
    protected $table = 'personal_access_tokens';
}