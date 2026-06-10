<?php
namespace App\Repositories\Tenant;

use App\Models\Tenant\Payment;
use Illuminate\Pagination\LengthAwarePaginator;

class PaymentRepository
{
    public function getPaginated(array $filters): LengthAwarePaginator
    {
        $query = Payment::query();

        if (!empty($filters['trans_no'])) {
            $query->where('trans_no', 'ILIKE', "%{$filters['trans_no']}%");
        }
        if (!empty($filters['paid_amount'])) {
            $query->where('paid_amount', $filters['paid_amount']);
        }
        if (!empty($filters['start_date']) && !empty($filters['end_date'])) {
            $query->whereBetween('payment_date', [$filters['start_date'], $filters['end_date']]);
        }

        return $query->orderBy('id', 'desc')->paginate(10)->withQueryString();
    }
}