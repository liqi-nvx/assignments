<?php
namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use App\Http\Requests\Central\StoreTenantRequest;
use App\Services\Central\TenantService;
use Illuminate\Http\JsonResponse;
use Inertia\Inertia;

class TenantRegisterController extends Controller
{
    protected TenantService $tenantService;

    public function __construct(TenantService $tenantService)
    {
        $this->tenantService = $tenantService;
    }

    public function showRegister()
    {
        return Inertia::render('Central/Register');
    }

    public function store(StoreTenantRequest $request): JsonResponse
    {
        $tenant = $this->tenantService->registerTenant($request->validated());
        
        return response()->json([
            'message' => 'Tenant created successfully',
            'tenant' => $tenant,
            'redirect_url' => 'http://' . $tenant->id . '.localhost:8000/register'
        ], 201);
    }
}