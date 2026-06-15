<?php

namespace Tests\Unit\Tenant;

use Tests\TenantTestCase;
use App\Services\Tenant\TenantInvoiceService;
use App\Repositories\Tenant\InvoiceRepository;
use App\Repositories\Tenant\PaymentRepository;
use App\Repositories\Tenant\CustomerRepository;
use App\Repositories\Tenant\InvoiceEmailTaskRepository;
use App\Services\Tenant\TenantReportService;
use App\Repositories\Tenant\ReportRepository;
use App\Models\Tenant\Customer;
use App\Models\Tenant\Goods;
use App\Models\Tenant\Invoice;
use App\Models\Tenant\InvoiceOverdueTask;
use Illuminate\Support\Facades\Queue;
use App\Jobs\Tenant\SendInvoiceEmailJob;
use Exception;

class FinancialInvoiceFlowTest extends TenantTestCase
{
    protected TenantInvoiceService $invoiceService;
    protected TenantReportService $reportService;
    protected Customer $customer;
    protected Goods $itemA;

    protected function setUp(): void
    {
        parent::setUp();

        $this->invoiceService = new TenantInvoiceService(
            new InvoiceRepository(),
            new PaymentRepository(),
            new CustomerRepository(),
            new InvoiceEmailTaskRepository()
        );

        $this->reportService = new TenantReportService(new ReportRepository());

        // 预备测试基础数据
        $this->customer = Customer::create(['name' => 'Dealer A', 'email' => 'dealer@auto.com', 'phone' => '112233']);
        $this->itemA = Goods::create(['name' => 'Gearbox Component', 'stock' => 5, 'price' => 1200.00]);
    }

    /** @test */
    public function test_it_creates_invoice_with_pessimistic_lock_decrements_stock_and_dispatches_job()
    {
        Queue::fake();

        $invoiceData = [
            'customer_id' => $this->customer->id,
            'items' => [
                ['goods_id' => $this->itemA->id, 'quantity' => 2]
            ]
        ];

        $invoice = $this->invoiceService->createInvoice($invoiceData);

        // 1. 验证发票基本信息与自动生成的发票号
        $this->assertNotNull($invoice->invoice_no);
        $this->assertEquals(2400.00, $invoice->total_price);
        $this->assertEquals('unpaid', $invoice->status);

        // 2. 验证悲观锁扣减库存
        $this->assertEquals(3, $this->itemA->fresh()->stock);

        // 3. 验证异步发信任务已正常压入默认队列（使用代码里真实的属性名：taskId）
        Queue::assertPushed(SendInvoiceEmailJob::class, function ($job) {
            $reflection = new \ReflectionClass($job);
            
            // 读取 protected string $tenantId
            $tenantIdProp = $reflection->getProperty('tenantId');
            $tenantIdProp->setAccessible(true);
            $actualTenantId = $tenantIdProp->getValue($job);

            // 修正：读取代码里真实的 protected int $taskId
            $taskIdProp = $reflection->getProperty('taskId');
            $taskIdProp->setAccessible(true);
            $actualTaskId = $taskIdProp->getValue($job);

            return $actualTenantId === $this->tenantId && !empty($actualTaskId);
        });
    }

    /** @test */
    public function test_it_throws_exception_if_invoice_quantity_exceeds_warehouse_stock()
    {
        $invoiceData = [
            'customer_id' => $this->customer->id,
            'items' => [
                ['goods_id' => $this->itemA->id, 'quantity' => 10] // 现有库存仅5件
            ]
        ];

        $this->expectException(Exception::class);
        $this->expectExceptionMessage("insufficient warehouse inventory");

        $this->invoiceService->createInvoice($invoiceData);
    }

    /** @test */
    public function test_it_handles_partial_and_full_invoice_payments()
    {
        // 预设一张 1000 元的发票
        $invoice = Invoice::create([
            'customer_id' => $this->customer->id,
            'invoice_no'  => 'INV-PAY-TEST',
            'total_price' => 1000.00,
            'paid_amount' => 0.00,
            'status'      => 'unpaid',
            'issue_date'  => now(),
            'due_date'    => now()->addDays(30)
        ]);

        // 1. 支付 400 (预期变为 partial 状态)
        $payment1 = $this->invoiceService->payInvoice($invoice, ['paid_amount' => 400.00]);
        $this->assertEquals(400.00, $invoice->fresh()->paid_amount);
        $this->assertEquals('partial', $invoice->fresh()->status);
        $this->assertNotNull($payment1->trans_no);

        // 2. 企图超额支付 (预期拦截)
        $this->expectException(Exception::class);
        try {
            $this->invoiceService->payInvoice($invoice, ['paid_amount' => 700.00]); // 余额仅剩 600
        } catch (Exception $e) {
            $this->assertStringContainsString('exceeds remaining balance', $e->getMessage());
            
            // 3. 补满余款支付 600 (预期变为 paid 状态)
            $this->invoiceService->payInvoice($invoice, ['paid_amount' => 600.00]);
            $this->assertEquals('paid', $invoice->fresh()->status);
            throw $e;
        }
    }

    /** @test */
    public function test_it_calculates_accurate_sales_reports_and_aggregates_via_cloned_query()
    {
        // 创建两张不同状态的发票用于财务对账
        Invoice::create([
            'customer_id' => $this->customer->id, 'invoice_no' => 'R-1',
            'total_price' => 500.00, 'paid_amount' => 500.00, 'status' => 'paid',
            'issue_date' => now(), 'due_date' => now()->addDays(1)
        ]);
        Invoice::create([
            'customer_id' => $this->customer->id, 'invoice_no' => 'R-2',
            'total_price' => 300.00, 'paid_amount' => 100.00, 'status' => 'partial',
            'issue_date' => now(), 'due_date' => now()->addDays(1)
        ]);

        $reportData = $this->reportService->generateSalesReport([]);

        // 验证单次查询克隆器的聚合数值
        $this->assertEquals(800.00, $reportData['summary']['total_price_sum']);
        $this->assertEquals(600.00, $reportData['summary']['paid_amount_sum']);
        $this->assertEquals(200.00, $reportData['summary']['outstanding_amount_sum']);
        $this->assertEquals(2, $reportData['paginated_items']->total());
    }

    /** @test */
    public function test_it_processes_and_updates_overdue_invoice_via_task()
    {
        // 建立一张过期的未付账单
        $expiredInvoice = Invoice::create([
            'customer_id' => $this->customer->id,
            'invoice_no'  => 'INV-OLD',
            'total_price' => 150.00,
            'paid_amount' => 0.00,
            'status'      => 'unpaid',
            'issue_date'  => now()->subDays(40),
            'due_date'    => now()->subDays(10) // 已经过期 10 天
        ]);

        $task = InvoiceOverdueTask::create([
            'invoice_id' => $expiredInvoice->id,
            'date_at'    => now()->toDateTimeString(),
            'status'     => 'pending'
        ]);

        $this->invoiceService->processOverdue($task->id);

        $this->assertEquals('overdue', $expiredInvoice->fresh()->status);
        $this->assertEquals('success', $task->fresh()->status);
    }
}