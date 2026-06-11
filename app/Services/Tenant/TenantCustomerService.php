<?php

namespace App\Services\Tenant;

use App\Repositories\Tenant\CustomerRepository;
use App\Models\Tenant\Customer;
use Illuminate\Pagination\LengthAwarePaginator;
use Exception;

class TenantCustomerService
{
    protected CustomerRepository $customerRepo;

    public function __construct(CustomerRepository $customerRepo)
    {
        $this->customerRepo = $customerRepo;
    }

    public function getCustomersPaginated(array $filters): LengthAwarePaginator
    {
        return $this->customerRepo->getPaginated($filters);
    }

    public function createCustomer(array $data): Customer
    {
        return $this->customerRepo->create($data);
    }

    public function updateCustomer(Customer $customer, array $data): bool
    {
        return $this->customerRepo->update($customer, $data);
    }

    public function deleteCustomer(Customer $customer): void
    {
        if ($this->customerRepo->hasInvoices($customer)) {
            throw new Exception("Cannot delete customer. Invoices exist for this customer.");
        }
        
        $this->customerRepo->delete($customer);
    }
}