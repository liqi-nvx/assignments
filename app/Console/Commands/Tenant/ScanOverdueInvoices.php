<?php

namespace App\Console\Commands\Tenant;

use App\Models\Tenant\Invoice;
use App\Models\Tenant\InvoiceOverdueTask;
use App\Jobs\Tenant\ProcessInvoiceOverdue;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\Tenant;

class ScanOverdueInvoices extends Command
{
    protected $signature = 'tenant:scan-overdue';
    protected $description = 'Scan all tenants for overdue invoices, record them in tasks table, and dispatch to Redis queue';

    public function handle()
    {
        $this->info('Starting scan for overdue invoices...');

        $tenants = Tenant::all();

        foreach ($tenants as $tenant) {
            $this->info("Scanning database for Tenant: {$tenant->id}");
            
            tenancy()->initialize($tenant); 

            $this->scanCurrentTenantDatabase($tenant->id);
            
            tenancy()->end();
        }

        $this->info('All overdue scans and dispatching completed.');
    }

    private function scanCurrentTenantDatabase($tenantId)
    {
        $today = now()->toDateTimeString();

        Invoice::where('due_date', '<', $today)
            ->where('paid_amount', '<', DB::raw('total_price'))
            ->where('status', '!=', 'overdue')
            ->chunkById(100, function ($invoices) use ($tenantId) {
                foreach ($invoices as $invoice) {
                    
                    $task = InvoiceOverdueTask::create([
                        'invoice_id' => $invoice->id,
                        'status'     => 'pending',
                        'response'   => null
                    ]);

                    ProcessInvoiceOverdue::dispatch($task->id, $tenantId)->onQueue('default');
                }
            });
    }
}