<?php
namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Repositories\Tenant\PaymentRepository;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PaymentController extends Controller
{
    public function index(Request $request, PaymentRepository $repo)
    {
        $filters = $request->only(['trans_no', 'paid_amount', 'start_date', 'end_date']);
        return Inertia::render('Tenant/Payments/Index', ['payments' => $repo->getPaginated($filters), 'filters' => $filters]);
    }
}