<?php

namespace App\Repositories\Tenant;

use App\Models\Tenant\InvoiceEmailTask;

class InvoiceEmailTaskRepository
{
    public function create(array $data): InvoiceEmailTask
    {
        return InvoiceEmailTask::create([
            'invoice_id'     => $data['invoice_id'],
            'customer_email' => $data['customer_email'],
            'status'         => $data['status'] ?? 'pending',
            'date_at'        => $data['date_at'] ?? now()->toDateTimeString(),
            'response'       => $data['response'] ?? null,
        ]);
    }
}