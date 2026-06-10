<?php
namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\CustomerRequest;
use App\Repositories\Tenant\CustomerRepository;
use App\Services\Tenant\TenantBusinessService;
use App\Models\Tenant\Customer;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Exception;

class CustomerController extends Controller
{
    protected CustomerRepository $customerRepo;
    protected TenantBusinessService $businessService;

    public function __construct(CustomerRepository $customerRepo, TenantBusinessService $businessService)
    {
        $this->customerRepo = $customerRepo;
        $this->businessService = $businessService;
    }

    public function index(Request $request)
    {
        $filters = $request->only(['search']);
        $customers = $this->customerRepo->getPaginated($filters);
        return Inertia::render('Tenant/Customers/Index', ['customers' => $customers, 'filters' => $filters]);
    }

    public function store(CustomerRequest $request)
    {
        $this->customerRepo->create($request->validated());
        return back();
    }

    public function update(CustomerRequest $request, Customer $customer)
    {
        $data = $request->validated();
        $this->customerRepo->update($customer, $data);
        return back();
    }

    public function destroy(Customer $customer)
    {
        try {
            $this->businessService->deleteCustomer($customer);
            return back();
        } catch (Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }
}