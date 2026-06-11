<?php

namespace App\Services\Tenant;

use App\Jobs\Tenant\SendInvoiceEmailJob;
use App\Repositories\Tenant\InvoiceRepository;
use App\Repositories\Tenant\PaymentRepository;
use App\Repositories\Tenant\CustomerRepository;
use App\Repositories\Tenant\InvoiceEmailTaskRepository;
use App\Models\Tenant\Invoice;
use App\Models\Tenant\Goods;
use App\Models\Tenant\Customer;
use App\Models\Tenant\Payment;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Collection;
use Exception;

class TenantInvoiceService
{
    protected InvoiceRepository $invoiceRepo;
    protected PaymentRepository $paymentRepo;
    protected CustomerRepository $customerRepo;
    protected InvoiceEmailTaskRepository $emailTaskRepo;

    public function __construct(
        InvoiceRepository $invoiceRepo, 
        PaymentRepository $paymentRepo,
        CustomerRepository $customerRepo,
        InvoiceEmailTaskRepository $emailTaskRepo
    ) {
        $this->invoiceRepo = $invoiceRepo;
        $this->paymentRepo = $paymentRepo;
        $this->customerRepo = $customerRepo;
        $this->emailTaskRepo = $emailTaskRepo;
    }

    public function getPaginatedInvoices(array $filters): LengthAwarePaginator
    {
        return $this->invoiceRepo->getPaginated($filters);
    }

    public function getCustomersForSelection(): Collection
    {
        return $this->customerRepo->getAllForSelection();
    }

    public function getGoodsForSelection(): Collection
    {
        return Goods::orderBy('name', 'asc')->get();
    }

    public function getInvoiceDetails(Invoice $invoice): Invoice
    {
        return $invoice->load(['customer', 'items.goods']);
    }

    public function getPaymentsForInvoice(int $invoiceId): Collection
    {
        return $this->paymentRepo->getPaymentsByInvoiceId($invoiceId);
    }

    public function createInvoice(array $data): Invoice
    {
        return DB::transaction(function () use ($data) {
            $totalInvoicePrice = 0.00;
            $itemsToCreate = [];

            foreach ($data['items'] as $item) {
                $goods = Goods::where('id', $item['goods_id'])->lockForUpdate()->firstOrFail();

                if ($item['quantity'] > $goods->stock) {
                    throw new Exception("Product [{$goods->name}] has insufficient warehouse inventory (Available: {$goods->stock}).");
                }

                $goods->decrement('stock', $item['quantity']);

                $itemTotal = round($goods->price * $item['quantity'], 2);
                $totalInvoicePrice += $itemTotal;

                $itemsToCreate[] = [
                    'goods_id' => $goods->id,
                    'quantity' => $item['quantity'],
                    'unit_price' => $goods->price,
                    'total_price' => $itemTotal
                ];
            }

            $invoice = $this->invoiceRepo->create([
                'customer_id' => $data['customer_id'],
                'invoice_no'  => Invoice::generateInvNo(),
                'total_price' => $totalInvoicePrice,
                'issue_date'  => now()->toDateTimeString(),
                'due_date'    => now()->addDays(30)->endOfDay()->toDateTimeString(),
                'paid_amount' => 0.00,
                'status'      => 'unpaid',
            ]);

            $invoice->items()->createMany($itemsToCreate);

            $customer = Customer::findOrFail($data['customer_id']);
            $emailTask = $this->emailTaskRepo->create([
                'invoice_id'     => $invoice->id,
                'customer_email' => $customer->email,
                'status'         => 'pending'
            ]);

            $currentTenantId = tenant('id'); 
            SendInvoiceEmailJob::dispatch($emailTask->id, $currentTenantId)->onQueue('default');

            return $invoice;
        });
    }

    public function payInvoice(Invoice $invoice, array $data): Payment
    {
        return DB::transaction(function () use ($invoice, $data) {
            $invoice = Invoice::where('id', $invoice->id)->lockForUpdate()->firstOrFail();
            
            if ($invoice->status === 'paid') {
                throw new Exception("This invoice has already been fully paid.");
            }

            $amountToPay = (float)$data['paid_amount'];
            $remaining = round((float)$invoice->total_price - (float)$invoice->paid_amount, 2);

            if ($amountToPay > $remaining) {
                throw new Exception("The input amount [${amountToPay}] exceeds remaining balance [${remaining}].");
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