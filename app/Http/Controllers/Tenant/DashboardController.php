<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\Invoice;
use App\Models\Tenant\Payment;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $tenantId = tenant('id');
        $cacheKey = "tenant:{$tenantId}:dashboard:stats";

        // 尝试从 Redis 缓存中获取，如果没有则执行闭包函数查询并缓存 24 小时
        $stats = Cache::remember($cacheKey, now()->addDay(), function () {
            $now = Carbon::now();

            // 1. Current month revenue (本月已付账款总额，即实际收入)
            // 假设：本月内收到的所有 Payment 总和
            $currentMonthRevenue = Payment::whereMonth('created_at', $now->month)
                ->whereYear('created_at', $now->year)
                ->sum('paid_amount');

            // 2. Outstanding invoices total (未付/欠款账单总额)
            // 假设：状态为 'sent' 或 'partial' 的账单中，未付部分的金额（或直接算这些账单的 total）
            // 这里以常见逻辑为例：状态不是 paid 且不是 cancelled 的账单总额
            $outstandingInvoices = Invoice::whereIn('status', ['overdue', 'partial', 'unpaid'])->sum(DB::raw('total_price - paid_amount'));

            // 3. Paid invoices total (历史已付账单总额)
            $paidInvoices = Invoice::where('status', 'paid')
                ->sum('total_price');

            // 4. Overdue invoices total (已逾期账单总额)
            // 假设：截止日期早于今天，且状态未支付的账单
            $overdueInvoices = Invoice::where('due_date', '<', $now->toDateString())
                ->whereIn('status', ['overdue', 'partial', 'unpaid'])
                ->sum(DB::raw('total_price - paid_amount'));

            return [
                'current_month_revenue' => (float)$currentMonthRevenue,
                'outstanding_invoices_total' => (float)$outstandingInvoices,
                'paid_invoices_total' => (float)$paidInvoices,
                'overdue_invoices_total' => (float)$overdueInvoices,
            ];
        });

        return Inertia::render('Tenant/Dashboard', [
            'stats' => $stats
        ]);
    }
}