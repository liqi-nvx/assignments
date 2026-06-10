<?php
namespace App\Repositories\Tenant;

use App\Models\Tenant\Invoice;
use Illuminate\Support\Facades\DB;

class ReportRepository
{
    public function getSalesReportData(array $filters): array
    {
        $query = Invoice::with(['customer']);

        if (!empty($filters['start_date']) && !empty($filters['end_date'])) {
            $query->whereBetween('issue_date', [$filters['start_date'], $filters['end_date']]);
        }

        $items = $query->orderBy('issue_date', 'desc')->get();

        // 动态汇总计算逻辑
        $totalPriceSum = $items->sum('total_price');
        $paidAmountSum = $items->sum('paid_amount');
        $outstandingAmountSum = $totalPriceSum - $paidAmountSum;

        return [
            'items' => $items,
            'summary' => [
                'total_price_sum' => $totalPriceSum,
                'paid_amount_sum' => $paidAmountSum,
                'outstanding_amount_sum' => $outstandingAmountSum,
            ]
        ];
    }
}