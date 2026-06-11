<?php
namespace App\Services\Tenant;

use App\Repositories\Tenant\ReportRepository;

class TenantReportService
{
    protected ReportRepository $reportRepo;

    public function __construct(ReportRepository $reportRepo)
    {
        $this->reportRepo = $reportRepo;
    }

    public function generateSalesReport(array $filters): array
    {
        return $this->reportRepo->getSalesReportData($filters);
    }
}