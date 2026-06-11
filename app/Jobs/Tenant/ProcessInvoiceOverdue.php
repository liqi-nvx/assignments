<?php

namespace App\Jobs\Tenant;

use App\Models\Tenant\Invoice;
use App\Models\Tenant\InvoiceOverdueTask;
use App\Services\Tenant\TenantInvoiceService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Exception;

class ProcessInvoiceOverdue implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected int $taskId;
    protected string $tenantId;

    public function __construct(int $taskId, string $tenantId = '')
    {
        $this->taskId = $taskId;
        $this->tenantId = $tenantId;
    }

    public function handle(TenantInvoiceService $invoiceService): void
    {
        tenancy()->initialize($this->tenantId); 

        try {
            $invoiceService->processOverdue($this->taskId);
        } catch (Exception $e) {
            InvoiceOverdueTask::where('id', $this->taskId)->update([
                'status' => 'failed',
                'response' => substr($e->getMessage(), 0, 1000)
            ]);

            throw $e; 
        }
    }
}