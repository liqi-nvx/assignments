<?php
namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\BuyProductRequest;
use App\Repositories\Tenant\ProductRepository;
use App\Repositories\Tenant\CustomerRepository;
use App\Services\Tenant\TenantBusinessService;
use App\Models\Tenant\Goods;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Exception;

class ProductController extends Controller
{
    protected ProductRepository $productRepo;
    protected TenantBusinessService $businessService;

    public function __construct(ProductRepository $productRepo, TenantBusinessService $businessService)
    {
        $this->productRepo = $productRepo;
        $this->businessService = $businessService;
    }

    public function index(Request $request)
    {
        $filters = $request->only(['search', 'stock_status']);
        $products = $this->productRepo->getPaginated($filters);
        // 获取全部顾客供购买弹窗下拉选择
        $customers = \App\Models\Tenant\Customer::all(['id', 'name']);

        return Inertia::render('Tenant/Products/Index', [
            'products' => $products,
            'filters' => $filters,
            'customers' => $customers
        ]);
    }

    public function show(Goods $product, Request $request)
    {
        $filters = $request->only(['search']);
        // 核心快速查询：不产生N+1的分页单品发票历史
        $invoices = $this->productRepo->getInvoicesPaginated($product, $filters);

        return Inertia::render('Tenant/Products/Show', [
            'product' => $product,
            'invoices' => $invoices,
            'filters' => $filters
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate(['name' => 'required', 'stock' => 'required|integer', 'price' => 'required|numeric']);
        $this->productRepo->create($data);
        return back();
    }

    public function update(Request $request, Goods $product)
    {
        // 仅能追补和追加库存数 (加 Stock Only)
        $data = $request->validate(['stock' => 'required|integer|min:0']);
        $this->productRepo->update($product, ['stock' => $product->stock + $data['stock']]);
        return back();
    }

    public function destroy(Goods $product)
    {
        try {
            $this->businessService->deleteProduct($product);
            return back();
        } catch (Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function buy(BuyProductRequest $request)
    {
        try {
            $this->businessService->buyProduct($request->validated());
            return back();
        } catch (Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }
}