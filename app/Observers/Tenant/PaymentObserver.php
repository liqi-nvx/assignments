<?php

namespace App\Observers\Tenant;

use App\Models\Tenant\Payment;
use Illuminate\Support\Facades\Cache;

class PaymentObserver
{
    private function clearCache()
    {
        if (tenant('id')) {
            Cache::forget("tenant:" . tenant('id') . ":dashboard:stats");
        }
    }

    public function created(Payment $payment)
    { 
        $this->clearCache();
    }

    public function updated(Payment $payment) 
    { 
        $this->clearCache();
    }

    public function deleted(Payment $payment) 
    { 
        $this->clearCache();
    }
}