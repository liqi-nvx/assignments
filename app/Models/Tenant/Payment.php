<?php

namespace App\Models\Tenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Redis;

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
        // 从配置或系统中读取位数，默认 5 位
        $padding = config('payment.number_padding', 5);
        $prefix = 'TRS' . now()->format('ym');
        
        // 使用 Redis 的 INCR 保证并发安全
        // 键名示例: payment_sequence:TRS2606
        $key = "payment_sequence:{$prefix}";
        
        // Redis INCR 会自动从 1 开始递增
        $nextId = Redis::incr($key);
        
        // 如果是第一天生成的，设置过期时间（防止 Redis 内存堆积）
        if ($nextId === 1) {
            Redis::expire($key, 86400 * 60); // 保留 60 天
        }

        return $prefix . str_pad((string)$nextId, $padding, '0', STR_PAD_LEFT);
    }
}