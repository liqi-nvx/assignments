<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Actions\Tenant\GetDashboardStatsAction;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index(GetDashboardStatsAction $getStatsAction)
    {
        $stats = $getStatsAction->execute(tenant('id'));

        return Inertia::render('Tenant/Dashboard', [
            'stats' => $stats
        ]);
    }
}