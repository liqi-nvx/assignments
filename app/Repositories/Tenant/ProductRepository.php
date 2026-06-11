<?php

namespace App\Repositories\Tenant;

use App\Models\Tenant\Goods;
use App\Models\Tenant\InvoiceItem;
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
        $query = InvoiceItem::query()
            ->where('goods_id', $product->id)
            ->join('invoices', 'invoice_items.invoice_id', '=', 'invoices.id')
            ->join('customers', 'invoices.customer_id', '=', 'customers.id')
            ->select(
                'invoices.id as id', 
                'invoices.invoice_no as invoice_no',
                'invoices.issue_date as issue_date',
                'invoices.due_date as due_date',
                'invoices.status as status',
                'invoices.paid_amount as invoice_paid_amount',
                'customers.name as customer_name',
                'invoice_items.quantity as quantity',
                'invoice_items.unit_price as unit_price',
                'invoice_items.total_price as total_price'
            );

        if (!empty($filters['customer_name'])) {
            $query->where('customers.name', 'ILIKE', "%{$filters['customer_name']}%");
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
        return $goods->invoiceItems()->exists();
    }

    public function delete(Goods $goods): bool
    { 
        return $goods->delete(); 
    }
}