<?php
namespace App\Repositories\Tenant;

use App\Models\Tenant\Invoice;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;

class InvoiceRepository
{
    public function getPaginated(array $filters): LengthAwarePaginator
    {
        $query = Invoice::query()
            ->select('invoices.*', 'customers.name as customer_name')
            ->leftJoin('customers', 'invoices.customer_id', '=', 'customers.id')
            ->with(['items.goods:id,name']);

        if (!empty($filters['customer_name'])) {
            $query->where('customers.name', 'ILIKE', "%{$filters['customer_name']}%");
        }

        if (!empty($filters['goods_name'])) {
            $query->whereIn('invoices.id', function ($subQuery) use ($filters) {
                $subQuery->select('invoice_items.invoice_id')
                        ->from('invoice_items')
                        ->join('goods', 'invoice_items.goods_id', '=', 'goods.id')
                        ->where('goods.name', 'ILIKE', "%{$filters['goods_name']}%");
            });
        }

        if (!empty($filters['invoice_no'])) {
            $query->where('invoices.invoice_no', 'ILIKE', "%{$filters['invoice_no']}%");
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

        return $query->orderBy('invoices.id', 'desc')
            ->paginate(10)
            ->through(function ($invoice) {
                $isOverdue = ($invoice->due_date < now() && $invoice->invoice_paid_amount < $invoice->total_price);
                $invoice->status = $isOverdue ? 'overdue' : $invoice->status;
                return $invoice;
            })
            ->withQueryString();
    }

    public function create(array $data): Invoice
    {
        return Invoice::create($data);
    }
}