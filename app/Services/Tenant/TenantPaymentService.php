<?php

namespace App\Services\Tenant;

use App\Repositories\Tenant\PaymentRepository;
use Illuminate\Pagination\LengthAwarePaginator;

class TenantPaymentService
{
    protected PaymentRepository $paymentRepo;

    public function __construct(PaymentRepository $paymentRepo)
    {
        $this->paymentRepo = $paymentRepo;
    }

    public function getPaginatedPayments(array $filters): LengthAwarePaginator
    {
        return $this->paymentRepo->getPaginated($filters);
    }
}