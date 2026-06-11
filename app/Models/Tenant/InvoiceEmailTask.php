<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;

class InvoiceEmailTask extends Model
{
    protected $table = 'invoice_email_tasks';

    protected $fillable = ['invoice_id', 'customer_email', 'status', 'response'];

    public function invoice()
    {
        return $this->belongsTo(Invoice::class, 'invoice_id');
    }
}