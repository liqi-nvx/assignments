<?php

namespace App\Models\Tenant;
use Illuminate\Database\Eloquent\Model;

class Goods extends Model
{
    protected $table = 'goods';
    
    protected $fillable = ['name', 'stock', 'price'];

    public function invoices() {
        return $this->hasMany(Invoice::class, 'goods_id');
    }
}