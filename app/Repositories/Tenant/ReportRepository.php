<?php
namespace App\Repositories\Tenant;

use App\Models\Tenant\Invoice;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class ReportRepository
{
    public function getSalesReportData(array $filters): array
    {
        $query = Invoice::select('invoices.*')->with(['customer:id,name']);

        if (!empty($filters['invoice_no'])) {
            $query->where('invoices.invoice_no', 'ILIKE', "%{$filters['invoice_no']}%");
        }
        
        if (!empty($filters['customer_name'])) {
            $query->join('customers', 'invoices.customer_id', '=', 'customers.id')
                ->where('customers.name', 'ILIKE', "%{$filters['customer_name']}%");
        }

        if (!empty($filters['start_date'])) {
            $startDate = Carbon::parse($filters['start_date'])->startOfDay()->toDateTimeString();
            $query->where('invoices.issue_date', '>=', $startDate);
        }

        if (!empty($filters['end_date'])) {
            $endDate = Carbon::parse($filters['end_date'])->endOfDay()->toDateTimeString();
            $query->where('invoices.issue_date', '<=', $endDate);
        }

        $summaryQuery = clone $query;
        $aggregates = $summaryQuery->select(
            DB::raw('COALESCE(SUM(invoices.total_price), 0) as total_price_sum'),
            DB::raw('COALESCE(SUM(invoices.paid_amount), 0) as paid_amount_sum')
        )->first();

        $totalPriceSum = (float) $aggregates->total_price_sum;
        $paidAmountSum = (float) $aggregates->paid_amount_sum;

        $paginatedItems = $query->orderBy('invoices.issue_date', 'desc')
                                ->paginate(10)
                                ->withQueryString();

        return [
            'paginated_items' => $paginatedItems,
            'summary' => [
                'total_price_sum'        => $totalPriceSum,
                'paid_amount_sum'        => $paidAmountSum,
                'outstanding_amount_sum' => round($totalPriceSum - $paidAmountSum, 2),
            ]
        ];
    }
}