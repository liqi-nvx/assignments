<?php
namespace App\Repositories\Tenant;

use App\Models\Tenant\Invoice;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class ReportRepository
{
    public function getSalesReportData(array $filters): array
    {
        $query = Invoice::query()
            ->select('invoices.*', 'customers.name as customer_name')
            ->leftJoin('customers', 'invoices.customer_id', '=', 'customers.id');

        if (!empty($filters['invoice_no'])) {
            $query->where('invoices.invoice_no', 'ILIKE', "%{$filters['invoice_no']}%");
        }
        
        if (!empty($filters['customer_name'])) {
            $query->where('customers.name', 'ILIKE', "%{$filters['customer_name']}%");
        }

        if (!empty($filters['status'])) {
            if ($filters['status'] === 'overdue') {
                $query->where(function ($q) {
                    $q->where('invoices.status', 'overdue')
                    ->orWhere(function ($sub) {
                        $sub->where('due_date', '<', now())
                            ->whereColumn('paid_amount', '<', 'total_price');
                    });
                });
            } else {
                $query->where('invoices.status', $filters['status'])
                    ->whereNot(function ($q) {
                        $q->where('invoices.due_date', '<', now())
                            ->whereColumn('invoices.paid_amount', '<', 'invoices.total_price');
                    });
            }
        }

        if (!empty($filters['start_date'])) {
            $startDate = Carbon::parse($filters['start_date'])->startOfDay()->toDateTimeString();
            $query->where('invoices.issue_date', '>=', $startDate);
        }

        if (!empty($filters['end_date'])) {
            $endDate = Carbon::parse($filters['end_date'])->endOfDay()->toDateTimeString();
            $query->where('invoices.issue_date', '<=', $endDate);
        }

        $aggregates = clone $query;
        $aggregates->getQuery()->columns = null; 

        $aggregates = $aggregates->selectRaw('COALESCE(SUM(invoices.total_price), 0) as total_price_sum, COALESCE(SUM(invoices.paid_amount), 0) as paid_amount_sum')
            ->toBase()
            ->first();

        $paginatedItems = $query->orderBy('invoices.issue_date', 'desc')
                                ->paginate(10)
                                ->through(function ($invoice) {
                                    $isOverdue = ($invoice->due_date < now() && $invoice->invoice_paid_amount < $invoice->total_price);
                                    $invoice->status = $isOverdue ? 'overdue' : $invoice->status;
                                    return $invoice;
                                })
                                ->withQueryString();

        return [
            'paginated_items' => $paginatedItems,
            'summary' => [
                'total_price_sum'        => (float) $aggregates->total_price_sum,
                'paid_amount_sum'        => (float) $aggregates->paid_amount_sum,
                'outstanding_amount_sum' => round($aggregates->total_price_sum - $aggregates->paid_amount_sum, 2),
            ]
        ];
    }
}