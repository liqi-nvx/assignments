<?php

namespace App\Jobs\Tenant;

use App\Models\Tenant\InvoiceEmailTask;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Exception;

class SendInvoiceEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected int $taskId;
    protected string $tenantId;

    public function __construct(int $taskId, string $tenantId)
    {
        $this->taskId = $taskId;
        $this->tenantId = $tenantId;
    }

    public function handle(): void
    {
        tenancy()->initialize($this->tenantId);

        $task = InvoiceEmailTask::with('invoice.customer')->find($this->taskId);
        if (!$task || $task->status === 'success') {
            return;
        }

        $task->update(['status' => 'processing']);

        try {
            $invoice = $task->invoice;
            if (!$invoice) {
                throw new Exception("Linked Invoice not found for Task ID [{$this->taskId}].");
            }

            Mail::send([], [], function ($message) use ($task, $invoice) {
                $message->to($task->customer_email)
                        ->subject("Invoice Created: " . $invoice->invoice_no)
                        ->html("<h3>Dear Customer,</h3>
                                <p>Your purchase is completed successfully.</p>
                                <p><strong>Invoice No:</strong> {$invoice->invoice_no}</p>
                                <p><strong>Total Amount:</strong> \${$invoice->total_price}</p>
                                <p>Thank you for your business!</p>");
            });

            $task->update([
                'status' => 'success',
                'response' => 'Email dispatched successfully to ' . $task->customer_email
            ]);

        } catch (Exception $e) {
            $task->update([
                'status' => 'failed',
                'response' => substr($e->getMessage(), 0, 1000)
            ]);

            throw $e;
        }
    }
}