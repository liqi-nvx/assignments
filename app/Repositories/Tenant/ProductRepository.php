<?php
namespace App\Repositories\Tenant;

use App\Models\Tenant\Goods;
use Illuminate\Pagination\LengthAwarePaginator;

class ProductRepository
{
    public function getPaginated(array $filters): LengthAwarePaginator
    {
        $query = Goods::query();

        if (!empty($filters['search'])) {
            $query->where('name', 'ILIKE', "%{$filters['search']}%");
        }

        if (isset($filters['stock_status'])) {
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
        // 关键点：提前利用 with 关联 customer 深度预载，杜绝 DataTable N+1 崩溃
        return $product->invoices()
            ->with(['customer'])
            ->where(function($q) use ($filters) {
                if (!empty($filters['search'])) {
                    $search = $filters['search'];
                    $q->where('invoice_no', 'ILIKE', "%{$search}%")
                      ->orWhereHas('customer', function($subQ) use ($search) {
                          $subQ->where('name', 'ILIKE', "%{$search}%");
                      });
                }
            })
            ->orderBy('id', 'desc')
            ->paginate(10);
    }

    public function create(array $data): Goods { return Goods::create($data); }
    public function update(Goods $goods, array $data): bool { return $goods->update($data); }
    public function hasInvoices(Goods $goods): bool { return $goods->invoices()->exists(); }
    public function delete(Goods $goods): bool { return $goods->delete(); }
}