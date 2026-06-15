<?php
namespace App\Repositories\Tenant;

use App\Models\Tenant\Payment;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;

class PaymentRepository
{
    public function getPaginated(array $filters): LengthAwarePaginator
    {
        $query = Payment::select('payments.*', 'invoices.invoice_no as invoice_no')
            ->leftJoin('invoices', 'payments.invoice_id', '=', 'invoices.id');

        if (!empty($filters['invoice_no'])) {
            $query->where('invoices.invoice_no', 'ILIKE', "%{$filters['invoice_no']}%");
        }

        if (!empty($filters['trans_no'])) {
            $query->where('payments.trans_no', 'ILIKE', "%{$filters['trans_no']}%");
        }

        if (!empty($filters['start_date'])) {
            $startDate = Carbon::parse($filters['start_date'])->startOfDay()->toDateTimeString();
            $query->where('payments.payment_date', '>=', $startDate);
        }

        if (!empty($filters['end_date'])) {
            $endDate = Carbon::parse($filters['end_date'])->endOfDay()->toDateTimeString();
            $query->where('payments.payment_date', '<=', $endDate);
        }

        return $query->orderBy('payments.id', 'desc')->paginate(10)->withQueryString();
    }

    public function getPaymentsByInvoiceId(int $invoiceId): Collection
    {
        return Payment::where('invoice_id', $invoiceId)->orderBy('id', 'desc')->get();
    }

    public function create(array $data): Payment
    {
        return Payment::create($data);
    }
}