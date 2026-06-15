<?php

namespace App\Console\Commands\Tenant;

use App\Jobs\Tenant\ProcessInvoiceOverdue;
use App\Models\Tenant;
use App\Models\Tenant\InvoiceOverdueTask;
use Carbon\Carbon;
use Illuminate\Console\Command;

class DispatchOverdueTasks extends Command
{
    protected $signature = 'tenant:dispatch-overdue-tasks {--date= : date to send for [format: "Y-m-d"]}';

    public function handle()
    {
        $date = Carbon::createFromFormat('Y-m-d', $this->option('date'))->startOfDay();
        $startDate = $date->format("Y-m-d"). " 00:00:00";
        $endDate = $date->format("Y-m-d"). " 23:59:59";

        $tenants = Tenant::all();
        
        foreach ($tenants as $tenant) {
            tenancy()->initialize($tenant);

            $tasks = InvoiceOverdueTask::where('status', 'pending')
                ->whereBetween('date_at', [$startDate, $endDate])
                ->get();

            foreach ($tasks as $task) {
                $task->update(['status' => 'processing']);
                ProcessInvoiceOverdue::dispatch($task->id, $tenant->id);
            }
            
            tenancy()->end();
        }
        
        $this->info('All tenant overdue tasks dispatched.');
    }
}