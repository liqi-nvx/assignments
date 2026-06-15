<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;

class InvoiceOverdueTask extends Model
{
    protected $table = 'invoice_overdue_tasks';

    protected $fillable = ['invoice_id', 'status', 'date_at','response'];

    public function invoice()
    {
        return $this->belongsTo(Invoice::class, 'invoice_id');
    }
}