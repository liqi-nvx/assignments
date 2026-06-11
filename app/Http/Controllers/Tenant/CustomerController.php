<?php
namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\CustomerRequest;
use App\Services\Tenant\TenantCustomerService;
use App\Models\Tenant\Customer;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Exception;

class CustomerController extends Controller
{
    protected TenantCustomerService $customerService;

    public function __construct(TenantCustomerService $customerService)
    {
        $this->customerService = $customerService;
    }

    public function index(Request $request)
    {
        $filters = $request->only(['name', 'email', 'phone']);
        $customers = $this->customerService->getCustomersPaginated($filters);

        return Inertia::render('Tenant/Customers/Index', ['customers' => $customers, 'filters' => $filters]);
    }

    public function store(CustomerRequest $request)
    {
        $this->customerService->createCustomer($request->validated());

        return back();
    }

    public function update(CustomerRequest $request, Customer $customer)
    {
        $this->customerService->updateCustomer($customer, $request->validated());

        return back();
    }

    public function destroy(Customer $customer)
    {
        try {
            $this->customerService->deleteCustomer($customer);

            return back();
        } catch (Exception $e) {
            
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }
}