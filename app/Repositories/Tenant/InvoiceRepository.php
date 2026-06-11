<?php
namespace App\Repositories\Tenant;

use App\Models\Tenant\Invoice;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;

class InvoiceRepository
{
    public function getPaginated(array $filters): LengthAwarePaginator
    {
        $query = Invoice::select('invoices.*')->with(['customer:id,name', 'goods:id,name']);

        if (!empty($filters['customer_name'])) {
            $query->join('customers', 'invoices.customer_id', '=', 'customers.id')
                ->where('customers.name', 'ILIKE', "%{$filters['customer_name']}%");
        }

        if (!empty($filters['goods_name'])) {
            $query->join('goods', 'invoices.goods_id', '=', 'goods.id')
                ->where('goods.name', 'ILIKE', "%{$filters['goods_name']}%");
        }

        if (!empty($filters['invoice_no'])) {
            $query->where('invoices.invoice_no', 'ILIKE', "%{$filters['invoice_no']}%");
        }
        
        if (!empty($filters['status'])) {
            $query->where('invoices.status', $filters['status']);
        }

        if (!empty($filters['start_date'])) {
            $startDate = Carbon::parse($filters['start_date'])->startOfDay()->toDateTimeString();
            $query->where('invoices.issue_date', '>=', $startDate);
        }

        if (!empty($filters['end_date'])) {
            $endDate = Carbon::parse($filters['end_date'])->endOfDay()->toDateTimeString();
            $query->where('invoices.issue_date', '<=', $endDate);
        }

        return $query->orderBy('invoices.id', 'desc')->paginate(10)->withQueryString();
    }

    public function create(array $data): Invoice
    {
        return Invoice::create($data);
    }
}