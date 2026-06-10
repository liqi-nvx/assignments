<?php
namespace App\Repositories\Tenant;

use App\Models\Tenant\Customer;
use Illuminate\Pagination\LengthAwarePaginator;

class CustomerRepository
{
    // 利用 Eloquent Eager Loading 和高效模糊查询保障无 N+1 且极端响应
    public function getPaginated(array $filters): LengthAwarePaginator
    {
        $query = Customer::query();

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function($q) use ($search) {
                $q->where('name', 'ILIKE', "%{$search}%") // 使用 PostgreSQL 不区分大小写的 ILIKE 算子
                  ->orWhere('email', 'ILIKE', "%{$search}%")
                  ->orWhere('phone', 'ILIKE', "%{$search}%");
            });
        }

        return $query->orderBy('id', 'desc')->paginate(10)->withQueryString();
    }

    public function create(array $data): Customer { return Customer::create($data); }
    public function update(Customer $customer, array $data): bool { return $customer->update($data); }
    
    public function hasInvoices(Customer $customer): bool
    {
        return $customer->invoices()->exists();
    }

    public function delete(Customer $customer): bool { return $customer->delete(); }
}