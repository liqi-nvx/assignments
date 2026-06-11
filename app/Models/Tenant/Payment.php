<?php

namespace App\Models\Tenant;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $table = 'payments';
    
    protected $fillable = ['invoice_id', 'payment_date', 'paid_amount', 'trans_no', 'status'];

    public function invoice()
    {
        return $this->belongsTo(Invoice::class, 'invoice_id');
    }

    public static function generateTransNo(): string
    {
        $prefix = 'TRS' . now()->format('ym');
        $latest = self::where('trans_no', 'like', $prefix . '%')->orderBy('id', 'desc')->first();
        if ($latest) {
            $lastFiveDigits = (int)substr($latest->trans_no, -5) + 1;
            return $prefix . str_pad((string)$lastFiveDigits, 5, '0', STR_PAD_LEFT);
        }
        return $prefix . '00001';
    }
}