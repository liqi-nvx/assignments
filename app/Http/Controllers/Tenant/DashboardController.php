<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Services\Tenant\TenantDashboardService;
use Inertia\Inertia;

class DashboardController extends Controller
{
    protected TenantDashboardService $dashboardService;

    public function __construct(TenantDashboardService $dashboardService)
    {
        $this->dashboardService = $dashboardService;
    }

    public function index()
    {
        $stats = $this->dashboardService->getDashboardStats(tenant('id'));

        return Inertia::render('Tenant/Dashboard', [
            'stats' => $stats
        ]);
    }
}