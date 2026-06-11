<?php

namespace App\Jobs\Tenant;

use App\Models\Tenant\Invoice;
use App\Models\Tenant\InvoiceOverdueTask;
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

    public function handle(): void
    {
        tenancy()->initialize($this->tenantId); 

        $task = InvoiceOverdueTask::find($this->taskId);
        if (!$task || $task->status === 'success') {
            return;
        }

        $task->update(['status' => 'processing']);

        DB::beginTransaction();
        try {
            $invoice = Invoice::where('id', $task->invoice_id)
                ->lockForUpdate()
                ->first();

            if (!$invoice) {
                throw new Exception("Invoice ID [{$task->invoice_id}] not found.");
            }

            if ($invoice->due_date < now()->toDateTimeString() && $invoice->paid_amount < $invoice->total_price) {
                
                $invoice->update([
                    'status' => 'overdue'
                ]);

                $task->update([
                    'status' => 'success',
                    'response' => 'Invoice status successfully updated to overdue.'
                ]);
            } else {
                $task->update([
                    'status' => 'success',
                    'response' => 'Skipped: Invoice is either paid or not yet due.'
                ]);
            }

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();

            $task->update([
                'status' => 'failed',
                'response' => $e->getMessage()
            ]);

            throw $e; 
        }
    }
}