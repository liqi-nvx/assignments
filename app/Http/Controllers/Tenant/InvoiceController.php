<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\PayInvoiceRequest;
use App\Http\Requests\Tenant\StoreInvoiceRequest;
use App\Services\Tenant\TenantInvoiceService;
use App\Models\Tenant\Invoice;
use App\Services\Tenant\TenantProductService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Exception;

class InvoiceController extends Controller
{
    protected TenantInvoiceService $invoiceService;
    protected TenantProductService $productService;

    public function __construct(
        TenantInvoiceService $invoiceService,
        TenantProductService $productService
    ) {
        $this->invoiceService = $invoiceService;
        $this->productService = $productService;
    }

    public function index(Request $request)
    {
        $filters = $request->only(['invoice_no', 'customer_name', 'goods_name', 'status', 'start_date', 'end_date']);
        
        $invoices = $this->invoiceService->getPaginatedInvoices($filters);
        $customers = $this->invoiceService->getCustomersForSelection();
        $goods = $this->productService->getGoodsForSelection();

        return Inertia::render('Tenant/Invoices/Index', [
            'invoices'  => $invoices,
            'filters'   => $filters,
            'customers' => $customers,
            'goods'     => $goods
        ]);
    }

    public function store(StoreInvoiceRequest $request)
    {
        try {
            $this->invoiceService->createInvoice($request->validated());

            return back()->with('success', 'Invoice created successfully.');
        } catch (Exception $e) {
            
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function show(Invoice $invoice)
    {
        $invoiceDetails = $this->invoiceService->getInvoiceDetails($invoice);
        $payments       = $this->invoiceService->getPaymentsForInvoice($invoice->id);

        return Inertia::render('Tenant/Invoices/Show', [
            'invoice'  => $invoiceDetails,
            'payments' => $payments
        ]);
    }

    public function pay(PayInvoiceRequest $request, Invoice $invoice)
    {
        try {
            $this->invoiceService->payInvoice($invoice, $request->validated());

            return back()->with('success', 'Payment applied successfully.');
        } catch (Exception $e) {

            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }
}