<?php

namespace App\Services\Tenant;

use App\Jobs\Tenant\SendInvoiceEmailJob;
use App\Models\Tenant\Customer;
use App\Repositories\Tenant\ProductRepository;
use App\Models\Tenant\Goods;
use App\Models\Tenant\Invoice;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Exception;

class TenantProductService
{
    protected ProductRepository $productRepo;

    public function __construct(ProductRepository $productRepo)
    {
        $this->productRepo = $productRepo;
    }

    public function getPaginatedProducts(array $filters): LengthAwarePaginator
    {
        return $this->productRepo->getPaginated($filters);
    }

    public function getPaginatedInvoicesForProduct(Goods $product, array $filters): LengthAwarePaginator
    {
        return $this->productRepo->getInvoicesPaginated($product, $filters);
    }

    public function getGoodsForSelection(): Collection
    {
        return Goods::orderBy('name', 'asc')->get();
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
}