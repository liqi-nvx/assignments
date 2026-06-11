<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Invoice extends Model
{
    protected $table = 'invoices';
    
    protected $fillable = ['customer_id', 'invoice_no', 'total_price', 'issue_date', 'due_date', 'paid_amount', 'status'];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class, 'invoice_id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class, 'invoice_id');
    }

    public static function generateInvNo(): string
    {
        $prefix = 'INV' . now()->format('ym');
        $latestInvoice = self::where('invoice_no', 'like', $prefix . '%')->orderBy('id', 'desc')->first();
        if ($latestInvoice) {
            $lastFiveDigits = (int)substr($latestInvoice->invoice_no, -5) + 1;
            return $prefix . str_pad((string)$lastFiveDigits, 5, '0', STR_PAD_LEFT);
        }

        return $prefix . '00001';
    }
}