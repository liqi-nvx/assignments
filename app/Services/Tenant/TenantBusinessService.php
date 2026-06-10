<?php
namespace App\Services\Tenant;

use App\Repositories\Tenant\CustomerRepository;
use App\Repositories\Tenant\ProductRepository;
use App\Models\Tenant\Goods;
use App\Models\Tenant\Customer;
use App\Models\Tenant\Invoice;
use App\Models\Tenant\Payment;
use Illuminate\Support\Facades\DB;
use Exception;

class TenantBusinessService
{
    protected CustomerRepository $customerRepo;
    protected ProductRepository $productRepo;

    public function __construct(CustomerRepository $customerRepo, ProductRepository $productRepo)
    {
        $this->customerRepo = $customerRepo;
        $this->productRepo = $productRepo;
    }

    // 顾客安全删除断言
    public function deleteCustomer(Customer $customer): void
    {
        if ($this->customerRepo->hasInvoices($customer)) {
            throw new Exception("Cannot delete customer. Invoices exist for this customer.");
        }
        $this->customerRepo->delete($customer);
    }

    // 商品安全删除断言
    public function deleteProduct(Goods $goods): void
    {
        if ($this->productRepo->hasInvoices($goods)) {
            throw new Exception("Cannot delete product. This item has already been purchased.");
        }
        $this->productRepo->delete($goods);
    }

    // 购买产生单据核心事务逻辑
    public function buyProduct(array $data): Invoice
    {
        return DB::transaction(function () use ($data) {
            // 采用 Pessimistic Locking (悲观锁) 杜绝高并发超卖漏洞
            $goods = Goods::where('id', $data['goods_id'])->lockForUpdate()->firstOrFail();

            if ($data['quantity'] > $goods->stock) {
                throw new Exception("Insufficient stock available in database.");
            }

            // 扣除库存
            $goods->decrement('stock', $data['quantity']);

            // 自动开立发票
            $totalPrice = $goods->price * $data['quantity'];
            
            return Invoice::create([
                'customer_id' => $data['customer_id'],
                'goods_id'    => $goods->id,
                'invoice_no'  => Invoice::generateInvNo(),
                'quantity'    => $data['quantity'],
                'unit_price'  => $goods->price,
                'total_price' => $totalPrice,
                'issue_date'  => now()->toDateString(),
                'due_date'    => now()->addDays(30)->toDateString(),
                'paid_amount' => 0.00,
                'status'      => 'unpaid',
            ]);
        });
    }

    // 发票核销支付核心事务逻辑
    public function payInvoice(Invoice $invoice, array $data): Payment
    {
        return DB::transaction(function () use ($invoice, $data) {
            // 重新锁住该发票防双重核销
            $invoice = Invoice::where('id', $invoice->id)->lockForUpdate()->firstOrFail();
            
            if ($invoice->status === 'paid') {
                throw new Exception("Invoice has already been fully paid.");
            }

            $amountToPay = (float)$data['paid_amount'];
            $remaining = $invoice->total_price - $invoice->paid_amount;

            if ($amountToPay <= 0 || $amountToPay > $remaining) {
                throw new Exception("Invalid payment amount entered.");
            }

            // 更新 Invoice 表累计已付与状态机制
            $newPaidAmount = $invoice->paid_amount + $amountToPay;
            $newStatus = ($newPaidAmount >= $invoice->total_price) ? 'paid' : 'partial';

            $invoice->update([
                'paid_amount' => $newPaidAmount,
                'status'      => $newStatus
            ]);

            // 写入流水日志表
            return Payment::create([
                'invoices_id'  => $invoice->id,
                'payment_date' => now()->toDateString(),
                'paid_amount'  => $amountToPay,
                'trans_no'     => Payment::generateTransNo(),
                'status'       => 1
            ]);
        });
    }
}