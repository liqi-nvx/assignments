<?php

namespace Tests\Unit\Tenant;

use Tests\TenantTestCase;
use App\Services\Tenant\TenantCustomerService;
use App\Repositories\Tenant\CustomerRepository;
use App\Services\Tenant\TenantProductService;
use App\Repositories\Tenant\ProductRepository;
use App\Models\Tenant\Customer;
use App\Models\Tenant\Goods;
use App\Models\Tenant\Invoice;
use Exception;

class CustomerAndProductBusinessTest extends TenantTestCase
{
    protected TenantCustomerService $customerService;
    protected TenantProductService $productService;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->customerService = new TenantCustomerService(new CustomerRepository());
        $this->productService = new TenantProductService(new ProductRepository());
    }

    /** @test */
    public function test_it_can_create_and_update_a_customer_with_insensitive_searching()
    {
        // 1. 测试创建
        $customer = $this->customerService->createCustomer([
            'name'  => 'John Doe',
            'email' => 'john@example.com',
            'phone' => '123456789'
        ]);

        $this->assertDatabaseHas('customers', ['email' => 'john@example.com']);

        // 2. 测试不区分大小写模糊搜索 (ILIKE)
        $filters = ['name' => 'jOhN'];
        $paginated = $this->customerService->getCustomersPaginated($filters);
        $this->assertEquals(1, $paginated->total());

        // 3. 测试更新
        $this->customerService->updateCustomer($customer, ['name' => 'John Pro']);
        $this->assertEquals('John Pro', $customer->fresh()->name);
    }

    /** @test */
    public function test_it_blocks_deletion_of_customer_if_historical_invoices_exist()
    {
        $customer = Customer::create(['name' => 'Hazard Customer', 'email' => 'h@test.com', 'phone' => '000']);
        
        // 为该客户伪造一张发票
        Invoice::create([
            'customer_id' => $customer->id,
            'invoice_no'  => 'INV-BLOCK-DEL',
            'total_price' => 100.00,
            'paid_amount' => 0.00,
            'status'      => 'unpaid',
            'issue_date'  => now(),
            'due_date'    => now()->addDays(30)
        ]);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage("Cannot delete customer. Invoices exist for this customer.");

        // 触发级联阻断校验
        $this->customerService->deleteCustomer($customer);
    }

    /** @test */
    public function test_it_can_restock_and_handle_product_lifecycle()
    {
        $product = $this->productService->createProduct([
            'name'  => 'Platinum Engine Oil',
            'stock' => 10,
            'price' => 180.00
        ]);

        // 测试补货逻辑
        $this->productService->restockProduct($product, 5);
        $this->assertEquals(15, $product->fresh()->stock);

        // 测试删除阻断
        $this->productService->deleteProduct($product);
        $this->assertDatabaseMissing('goods', ['id' => $product->id]);
    }
}