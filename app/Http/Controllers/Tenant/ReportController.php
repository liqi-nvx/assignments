<?php
namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Repositories\Tenant\ReportRepository;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ReportController extends Controller
{
    public function salesReport(Request $request, ReportRepository $repo)
    {
        $filters = $request->only(['start_date', 'end_date']);
        $reportData = $repo->getSalesReportData($filters);
        return Inertia::render('Tenant/Reports/Sales', ['report' => $reportData, 'filters' => $filters]);
    }
}