<?php

namespace App\Actions\Tenant;

use App\Models\Tenant\Invoice;
use App\Models\Tenant\Payment;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class GetDashboardStatsAction
{
    public function execute(string $tenantId): array
    {
        $cacheKey = "tenant:{$tenantId}:dashboard:stats";

        return Cache::remember($cacheKey, now()->addDay(), function () {
            $now = Carbon::now();
            $startOfMonth = $now->copy()->startOfMonth()->toDateTimeString();
            $endOfMonth = $now->copy()->endOfMonth()->toDateTimeString();

            $currentMonthRevenue = Payment::whereBetween('payment_date', [$startOfMonth, $endOfMonth])->sum('paid_amount');

            $invoiceStats = Invoice::query()
                ->selectRaw("
                    SUM(CASE WHEN status IN ('overdue', 'partial', 'unpaid') THEN (total_price - paid_amount) ELSE 0 END) as outstanding,
                    SUM(CASE WHEN status = 'paid' THEN total_price ELSE 0 END) as paid,
                    SUM(CASE WHEN status IN ('overdue', 'partial', 'unpaid') AND due_date < ? THEN (total_price - paid_amount) ELSE 0 END) as overdue
                ", [$now])
                ->first();

            return [
                'current_month_revenue' => (float)$currentMonthRevenue,
                'outstanding_invoices_total' => (float)($invoiceStats->outstanding ?? 0),
                'paid_invoices_total' => (float)($invoiceStats->paid ?? 0),
                'overdue_invoices_total' => (float)($invoiceStats->overdue ?? 0),
            ];
        });
    }
}