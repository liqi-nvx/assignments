<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Services\Tenant\TenantProductService;
use App\Http\Requests\Tenant\StoreProductRequest;
use App\Http\Requests\Tenant\UpdateProductStockRequest;
use App\Http\Requests\Tenant\BuyProductRequest;
use App\Models\Tenant\Goods;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Exception;

class ProductController extends Controller
{
    protected TenantProductService $productService;

    public function __construct(TenantProductService $productService)
    {
        $this->productService = $productService;
    }

    public function index(Request $request)
    {
        $filters = $request->only(['search', 'stock_status']);

        $products  = $this->productService->getPaginatedProducts($filters);
        $customers = $this->productService->getCustomersForSelection();

        return Inertia::render('Tenant/Products/Index', [
            'products'  => $products,
            'filters'   => $filters,
            'customers' => $customers
        ]);
    }

    public function show(Goods $product, Request $request)
    {
        $filters = $request->only(['invoice_no', 'customer_name', 'status', 'start_date', 'end_date']);
        
        $invoices = $this->productService->getPaginatedInvoicesForProduct($product, $filters);

        return Inertia::render('Tenant/Products/Show', [
            'product'  => $product,
            'invoices' => $invoices,
            'filters'  => $filters
        ]);
    }

    public function store(StoreProductRequest $request)
    {
        $this->productService->createProduct($request->validated());

        return back()->with('success', 'Product created successfully.');
    }

    public function update(UpdateProductStockRequest $request, Goods $product)
    {
        $this->productService->restockProduct($product, $request->validated()['stock']);

        return back()->with('success', 'Stock updated successfully.');
    }

    public function destroy(Goods $product)
    {
        try {
            $this->productService->deleteProduct($product);

            return back();
        } catch (Exception $e) {

            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function buy(BuyProductRequest $request)
    {
        try {
            $this->productService->buyProduct($request->validated());

            return back()->with('success', 'Purchase completed and Invoice generated.');
        } catch (Exception $e) {
            
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }
}