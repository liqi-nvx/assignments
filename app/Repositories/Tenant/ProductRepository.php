<?php

namespace App\Repositories\Tenant;

use App\Models\Tenant\Goods;
use Illuminate\Support\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;

class ProductRepository
{
    public function getPaginated(array $filters): LengthAwarePaginator
    {
        $query = Goods::query();

        if (!empty($filters['search'])) {
            $query->where('name', 'ILIKE', "%{$filters['search']}%");
        }

        if (isset($filters['stock_status']) && $filters['stock_status'] !== '') {
            if ($filters['stock_status'] === 'zero') {
                $query->where('stock', 0);
            } elseif ($filters['stock_status'] === 'available') {
                $query->where('stock', '>=', 1);
            }
        }

        return $query->orderBy('id', 'desc')->paginate(10)->withQueryString();
    }

    public function getInvoicesPaginated(Goods $product, array $filters): LengthAwarePaginator
    {
        $query = $product->invoices()->select('invoices.*')->with(['customer:id,name']);

        if (!empty($filters['customer_name'])) {
            $query->join('customers', 'invoices.customer_id', '=', 'customers.id')
                ->where('customers.name', 'ILIKE', "%{$filters['customer_name']}%");
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

    public function create(array $data): Goods
    {
        return Goods::create($data);
    }
    
    public function update(Goods $goods, array $data): bool
    {
        return $goods->update($data);
    }

    public function hasInvoices(Goods $goods): bool
    { 
        return $goods->invoices()->exists();
    }

    public function delete(Goods $goods): bool
    { 
        return $goods->delete(); 
    }
}