<?php

namespace App\Services\Tenant;

use App\Repositories\Tenant\ProductRepository;
use App\Repositories\Tenant\InvoiceRepository;
use App\Repositories\Tenant\CustomerRepository;
use App\Models\Tenant\Goods;
use App\Models\Tenant\Invoice;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Exception;

class TenantProductService
{
    protected ProductRepository $productRepo;
    protected InvoiceRepository $invoiceRepo;
    protected CustomerRepository $customerRepo;

    public function __construct(
        ProductRepository $productRepo,
        InvoiceRepository $invoiceRepo,
        CustomerRepository $customerRepo
    ) {
        $this->productRepo  = $productRepo;
        $this->invoiceRepo  = $invoiceRepo;
        $this->customerRepo = $customerRepo;
    }

    public function getPaginatedProducts(array $filters): LengthAwarePaginator
    {
        return $this->productRepo->getPaginated($filters);
    }

    public function getPaginatedInvoicesForProduct(Goods $product, array $filters): LengthAwarePaginator
    {
        return $this->productRepo->getInvoicesPaginated($product, $filters);
    }

    public function getCustomersForSelection(): Collection
    {
        return $this->customerRepo->getAllForSelection();
    }

    public function createProduct(array $data): Goods
    {
        return $this->productRepo->create($data);
    }

    public function restockProduct(Goods $goods, int $additionalStock): bool
    {
        return $this->productRepo->update($goods, [
            'stock' => $goods->stock + $additionalStock
        ]);
    }

    public function deleteProduct(Goods $goods): void
    {
        if ($this->productRepo->hasInvoices($goods)) {
            throw new Exception("Cannot delete product. This product is already linked to historical invoice records.");
        }
        $this->productRepo->delete($goods);
    }

    public function buyProduct(array $data): Invoice
    {
        return DB::transaction(function () use ($data) {
            $goods = Goods::where('id', $data['goods_id'])->lockForUpdate()->firstOrFail();

            if ($data['quantity'] > $goods->stock) {
                throw new Exception("Insufficient warehouse inventory; the purchase transaction cannot be completed.");
            }

            $goods->decrement('stock', $data['quantity']);

            $totalPrice = $goods->price * $data['quantity'];
            
            return $this->invoiceRepo->create([
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
}