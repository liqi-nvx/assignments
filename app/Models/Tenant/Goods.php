<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Goods extends Model
{
    protected $table = 'goods';
    
    protected $fillable = ['name', 'stock', 'price'];

    public function invoiceItems(): HasMany
    {
        return $this->hasMany(InvoiceItem::class, 'goods_id');
    }

    public function invoices(): BelongsToMany
    {
        return $this->belongsToMany(Invoice::class, 'invoice_items', 'goods_id', 'invoice_id')->withPivot('quantity', 'unit_price', 'total_price');
    }
}