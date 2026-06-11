<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Services\Tenant\TenantPaymentService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PaymentController extends Controller
{
    protected TenantPaymentService $paymentService;

    public function __construct(TenantPaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    public function index(Request $request)
    {
        $filters = $request->only(['trans_no', 'invoice_no', 'start_date', 'end_date']);
        
        $payments = $this->paymentService->getPaginatedPayments($filters);

        return Inertia::render('Tenant/Payments/Index', [
            'payments' => $payments,
            'filters'  => $filters
        ]);
    }
}