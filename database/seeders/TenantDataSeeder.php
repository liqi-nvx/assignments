<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Carbon\Carbon;

class TenantDataSeeder extends Seeder
{
    public function run(): void
    {
        // 规范：海量原生插入时，临时关闭外键约束与事件能极大提升速度
        DB::connection('tenant')->unsetEventDispatcher();

        $this->command->info("====== 开始初始化租户 [" . tenant('id') . "] 的海量测试数据 ======");

        // 1. 生成账户 (密码: 12345678)
        $this->seedUsers();

        // 2. 生成商品数据 (Goods)
        $goodsIds = $this->seedGoods();

        // 3. 生成客户数据 (Customers: 300个)
        $customerIds = $this->seedCustomers();

        // 4. 混合级联生成发票、发票明细及支付流水
        $this->seedInvoicesAndPayments($customerIds, $goodsIds);

        $this->command->info("====== 租户 [" . tenant('id') . "] 数据全部注入成功！ ======");

        $this->resetSequence('invoices');
        $this->resetSequence('payments');
        $this->resetSequence('customers');
        $this->resetSequence('goods');
        $this->resetSequence('users');
        $this->resetSequence('invoice_items');

        $this->command->info("====== 所有序列已重置，数据库已就绪 ======");
    }

    private function seedUsers(): void
    {
        $password = Hash::make('12345678');
        DB::table('users')->insert([
            [
                'name'       => 'Master Admin',
                'email'      => 'admin@' . tenant('id') . '.com',
                'password'   => $password,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name'       => 'Manager Staff',
                'email'      => 'staff@' . tenant('id') . '.com',
                'password'   => $password,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ]);
        $this->command->comment('-> 基础账户生成完毕.');
    }

    private function seedGoods(): array
    {
        $goods = [];
        for ($i = 1; $i <= 100; $i++) {
            $goods[] = [
                'name'       => 'Premium Spare Part #' . str_pad((string)$i, 3, '0', STR_PAD_LEFT),
                'stock'      => rand(500, 2000),
                'price'      => rand(50, 1500) + (rand(0, 99) / 100),
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        DB::table('goods')->insert($goods);
        return DB::table('goods')->pluck('id')->toArray();
    }

    private function seedCustomers(): array
    {
        $chunks = [];
        for ($i = 1; $i <= 500; $i++) {
            $chunks[] = [
                'name'       => 'Corporate Client ' . Str::random(5),
                'email'      => 'client' . $i . '@example.com',
                'phone'      => '+601' . rand(1, 9) . '-' . rand(1000000, 9999999),
                'address'    => 'No. ' . rand(1, 100) . ', Automobile Business Park, Johor Bahru',
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        DB::table('customers')->insert($chunks);
        return DB::table('customers')->pluck('id')->toArray();
    }

    private function seedInvoicesAndPayments(array $customerIds, array $goodsIds): void
    {
        $totalInvoices = 5000; // 设在区间 2000-5000 的中间偏高位置
        $invoiceBatch = [];
        $itemBatch    = [];
        $paymentBatch = [];

        $invoiceCounter = 1;
        $paymentCounter = 1;
        $counters = [];

        $startDate = Carbon::now()->subMonths(2);

        $this->command->getOutput()->progressStart($totalInvoices);

        for ($i = 1; $i <= $totalInvoices; $i++) {
            // 模拟连续一年的业务时间轴递增
            $issueDate = $startDate->copy()->addMinutes($i * 17);
            $dueDate   = $issueDate->copy()->addDays(30)->endOfDay();

            // 获取年月 key (例如 '2603', '2604')
            $ymKey = $issueDate->format('ym');
            // 如果该年月计数器不存在，则初始化为 1
            if (!isset($counters['invoice'][$ymKey])) {
                $counters['invoice'][$ymKey] = 1;
                $counters['payment'][$ymKey] = 1;
            }
            // 获取当前流水号并自增
            $invoiceSeq = $counters['invoice'][$ymKey]++;

            // 提前预扣一个固定的自增 ID（PostgreSQL/MySQL 原生自增序列可预测）
            $currentInvoiceId = $invoiceCounter++;
            
            // 1. 计算这一张发票包含几个商品条目 (平均 3 到 4 个条目，总数约 15,000 条)
            $itemCount = rand(2, 5);
            $totalPrice = 0.00;

            for ($j = 0; $j < $itemCount; $j++) {
                $gId = $goodsIds[array_rand($goodsIds)];
                $qty = rand(1, 4);
                // 模拟价格快照，直接产生单价
                $unitPrice = rand(100, 800) + 0.5; 
                $itemTotal = round($unitPrice * $qty, 2);
                $totalPrice += $itemTotal;

                $itemBatch[] = [
                    'invoice_id'  => $currentInvoiceId,
                    'goods_id'    => $gId,
                    'quantity'    => $qty,
                    'unit_price'  => $unitPrice,
                    'total_price' => $itemTotal,
                    'created_at'  => $issueDate,
                    'updated_at'  => $issueDate,
                ];
            }

            // 2. 决定发票支付状态
            $statusSeed = rand(1, 10);
            if ($statusSeed <= 7) {
                // 70% 几率完全支付
                $status = 'paid';
                $paidAmount = $totalPrice;
            } elseif ($statusSeed <= 9) {
                // 20% 几率部分支付
                $status = 'partial';
                $paidAmount = round($totalPrice * 0.5, 2);
            } else {
                // 10% 未支付
                $status = 'unpaid';
                $paidAmount = 0.00;
            }

            $invoiceBatch[] = [
                'id'          => $currentInvoiceId,
                'customer_id' => $customerIds[array_rand($customerIds)],
                'invoice_no'  => 'INV' . $ymKey . str_pad((string)$invoiceSeq, 5, '0', STR_PAD_LEFT),
                'total_price' => $totalPrice,
                'paid_amount' => $paidAmount,
                'status'      => $status,
                'issue_date'  => $issueDate,
                'due_date'    => $dueDate,
                'created_at'  => $issueDate,
                'updated_at'  => $issueDate,
            ];

            // 3. 关联生成支付流水 (Payments)
            if ($paidAmount > 0) {
                $paymentSeq = $counters['payment'][$ymKey]++;
                
                $paymentBatch[] = [
                    'invoice_id'   => $currentInvoiceId,
                    'payment_date' => $issueDate,
                    'paid_amount'  => $paidAmount,
                    'trans_no'     => 'TRX' . $ymKey . str_pad((string)$paymentSeq, 5, '0', STR_PAD_LEFT),
                    'status'       => 1,
                    'created_at'   => $issueDate,
                    'updated_at'   => $issueDate,
                ];
            }

            // 4. 每积攒 500 条记录执行一次物理 Bulk Insert，防止内存暴涨
            if (count($invoiceBatch) >= 500) {
                DB::table('invoices')->insert($invoiceBatch);
                DB::table('invoice_items')->insert($itemBatch);
                if (!empty($paymentBatch)) {
                    DB::table('payments')->insert($paymentBatch);
                }

                // 清空内存缓冲区
                $invoiceBatch = [];
                $itemBatch    = [];
                $paymentBatch = [];
            }

            $this->command->getOutput()->progressAdvance();
        }

        // 5. 插入最后残余的尾部数据
        if (count($invoiceBatch) > 0) {
            DB::table('invoices')->insert($invoiceBatch);
            DB::table('invoice_items')->insert($itemBatch);
            if (!empty($paymentBatch)) {
                DB::table('payments')->insert($paymentBatch);
            }
        }

        $this->command->getOutput()->progressFinish();
        $this->command->comment("-> 级联关系发票/明细/流水注入完毕.");
    }

    private function resetSequence(string $table): void
    {
        // PostgreSQL 获取当前最大 ID 并重置序列
        $maxId = DB::table($table)->max('id');
        if ($maxId) {
            $sequenceName = "{$table}_id_seq";
            DB::statement("SELECT setval(pg_get_serial_sequence('$table', 'id'), $maxId + 1);");
        }
    }
}