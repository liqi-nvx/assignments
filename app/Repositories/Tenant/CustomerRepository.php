<?php
namespace App\Repositories\Tenant;

use App\Models\Tenant\Customer;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class CustomerRepository
{
    public function getPaginated(array $filters): LengthAwarePaginator
    {
        $query = Customer::query();

        if (!empty($filters['name'])) {
            $query->where('name', 'ILIKE', "%{$filters['name']}%");
        }

        if (!empty($filters['email'])) {
            $query->where('email', 'ILIKE', "%{$filters['email']}%");
        }

        if (!empty($filters['phone'])) {
            $query->where('phone', 'ILIKE', "%{$filters['phone']}%");
        }

        return $query->orderBy('id', 'desc')->paginate(10)->withQueryString();
    }

    public function getAllForSelection(): Collection
    {
        return Customer::all(['id', 'name']);
    }

    public function create(array $data): Customer
    {
        return Customer::create($data);
    }

    public function update(Customer $customer, array $data): bool
    {
        return $customer->update($data);
    }
    
    public function hasInvoices(Customer $customer): bool
    {
        return $customer->invoices()->exists();
    }

    public function delete(Customer $customer): bool
    {
        return $customer->delete();
    }
}