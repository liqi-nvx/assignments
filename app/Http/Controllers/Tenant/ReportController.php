<?php
namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Services\Tenant\TenantReportService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ReportController extends Controller
{
    protected TenantReportService $reportService;

    public function __construct(TenantReportService $reportService)
    {
        $this->reportService = $reportService;
    }

    public function salesReport(Request $request)
    {
        $filters = $request->only(['invoice_no', 'customer_name', 'start_date', 'end_date']);
        
        $reportData = $this->reportService->generateSalesReport($filters);

        return Inertia::render('Tenant/Reports/Sales', [
            'report'  => $reportData,
            'filters' => $filters
        ]);
    }
}