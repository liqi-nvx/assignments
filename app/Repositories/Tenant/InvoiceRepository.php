<?php
namespace App\Repositories\Tenant;

use App\Models\Tenant\Invoice;
use Illuminate\Pagination\LengthAwarePaginator;

class InvoiceRepository
{
    public function getPaginated(array $filters): LengthAwarePaginator
    {
        // 核心加速：完美提前加载 customer 和 goods 模型关系，杜绝在 DataTable 循环渲染时的额外 SQL 损耗
        $query = Invoice::with(['customer', 'goods']);

        if (!empty($filters['invoice_no'])) {
            $query->where('invoice_no', 'ILIKE', "%{$filters['invoice_no']}%");
        }
        if (!empty($filters['customer_name'])) {
            $query->whereHas('customer', function($q) use ($filters) {
                $q->where('name', 'ILIKE', "%{$filters['customer_name']}%");
            });
        }
        if (!empty($filters['goods_name'])) {
            $query->whereHas('goods', function($q) use ($filters) {
                $q->where('name', 'ILIKE', "%{$filters['goods_name']}%");
            });
        }
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        if (!empty($filters['start_date']) && !empty($filters['end_date'])) {
            $query->whereBetween('issue_date', [$filters['start_date'], $filters['end_date']]);
        }

        return $query->orderBy('id', 'desc')->paginate(10)->withQueryString();
    }
}