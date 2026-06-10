<?php
namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\PayInvoiceRequest;
use App\Repositories\Tenant\InvoiceRepository;
use App\Services\Tenant\TenantBusinessService;
use App\Models\Tenant\Invoice;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Exception;

class InvoiceController extends Controller
{
    protected InvoiceRepository $invoiceRepo;
    protected TenantBusinessService $businessService;

    public function __construct(InvoiceRepository $invoiceRepo, TenantBusinessService $businessService)
    {
        $this->invoiceRepo = $invoiceRepo;
        $this->businessService = $businessService;
    }

    public function index(Request $request)
    {
        $filters = $request->only(['invoice_no', 'customer_name', 'goods_name', 'status', 'start_date', 'end_date']);
        $invoices = $this->invoiceRepo->getPaginated($filters);

        return Inertia::render('Tenant/Invoices/Index', [
            'invoices' => $invoices,
            'filters'  => $filters
        ]);
    }

    public function pay(PayInvoiceRequest $request, Invoice $invoice)
    {
        try {
            $this->businessService->payInvoice($invoice, $request->validated());
            return back();
        } catch (Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }
}