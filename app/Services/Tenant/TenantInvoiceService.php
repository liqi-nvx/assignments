<?php

namespace App\Services\Tenant;

use App\Repositories\Tenant\InvoiceRepository;
use App\Repositories\Tenant\PaymentRepository;
use App\Models\Tenant\Invoice;
use App\Models\Tenant\Payment;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Collection;
use Exception;

class TenantInvoiceService
{
    protected InvoiceRepository $invoiceRepo;
    protected PaymentRepository $paymentRepo;

    public function __construct(InvoiceRepository $invoiceRepo, PaymentRepository $paymentRepo)
    {
        $this->invoiceRepo = $invoiceRepo;
        $this->paymentRepo = $paymentRepo;
    }

    public function getPaginatedInvoices(array $filters): LengthAwarePaginator
    {
        return $this->invoiceRepo->getPaginated($filters);
    }

    public function getInvoiceDetails(Invoice $invoice): Invoice
    {
        return $invoice->load(['customer', 'goods']);
    }

    public function getPaymentsForInvoice(int $invoiceId): Collection
    {
        return $this->paymentRepo->getPaymentsByInvoiceId($invoiceId);
    }

    public function payInvoice(Invoice $invoice, array $data): Payment
    {
        return DB::transaction(function () use ($invoice, $data) {
            $invoice = Invoice::where('id', $invoice->id)->lockForUpdate()->firstOrFail();
            
            if ($invoice->status === 'paid') {
                throw new Exception("This invoice has already been fully paid; no duplicate payment is required.");
            }

            $amountToPay = (float)$data['paid_amount'];
            $remaining = round((float)$invoice->total_price - (float)$invoice->paid_amount, 2);

            if ($amountToPay > $remaining) {
                throw new Exception("The input amount [${amountToPay}] exceeds the maximum remaining balance of [${remaining}] for this invoice.");
            }

            $newPaidAmount = round((float)$invoice->paid_amount + $amountToPay, 2);
            $newStatus = ($newPaidAmount >= (float)$invoice->total_price) ? 'paid' : 'partial';

            $invoice->update([
                'paid_amount' => $newPaidAmount,
                'status'      => $newStatus
            ]);

            return $this->paymentRepo->create([
                'invoice_id'   => $invoice->id,
                'payment_date' => now()->toDateTimeString(),
                'paid_amount'  => $amountToPay,
                'trans_no'     => Payment::generateTransNo(),
                'status'       => 1
            ]);
        });
    }
}