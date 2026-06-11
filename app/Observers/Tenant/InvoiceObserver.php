<?php

namespace App\Observers\Tenant;

use App\Models\Tenant\Invoice;
use Illuminate\Support\Facades\Cache;

class InvoiceObserver
{
    private function clearCache()
    {
        if (tenant('id')) {
            Cache::forget("tenant:" . tenant('id') . ":dashboard:stats");
        }
    }

    public function created(Invoice $invoice)
    { 
        $this->clearCache();
    }

    public function updated(Invoice $invoice)
    { 
        $this->clearCache();
    }

    public function deleted(Invoice $invoice)
    { 
        $this->clearCache();
    }
}