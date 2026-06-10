<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model {

    protected $table = 'customers';

    protected $fillable = ['name', 'email', 'phone', 'address'];

    public function invoices() {
        return $this->hasMany(Invoice::class);
    }
}