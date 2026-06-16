<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Redis;

class Invoice extends Model
{
    protected $table = 'invoices';
    
    protected $fillable = ['customer_id', 'invoice_no', 'total_price', 'issue_date', 'due_date', 'paid_amount', 'status'];

    protected $appends = ['computed_status'];

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
        // 从配置或系统中读取位数，默认 5 位
        $padding = config('invoice.number_padding', 5);
        $prefix = 'INV' . now()->format('ym');
        
        // 使用 Redis 的 INCR 保证并发安全
        // 键名示例: invoice_sequence:INV2606
        $key = "invoice_sequence:{$prefix}";
        
        // Redis INCR 会自动从 1 开始递增
        $nextId = Redis::incr($key);
        
        // 如果是第一天生成的，设置过期时间（防止 Redis 内存堆积）
        if ($nextId === 1) {
            Redis::expire($key, 86400 * 60); // 保留 60 天
        }

        return $prefix . str_pad((string)$nextId, $padding, '0', STR_PAD_LEFT);
    }

    public function getComputedStatusAttribute()
    {
        if ($this->status === 'overdue') {
            return 'overdue';
        }

        if ($this->due_date < now() && $this->paid_amount < $this->total_price) {
            return 'overdue';
        }

        return $this->status;
    }
}