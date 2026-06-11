<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;

class TenantMailSetting extends Model
{
    protected $table = 'tenant_mail_settings';

    protected $fillable = ['sender_name', 'mail_username', 'mail_password'];
}