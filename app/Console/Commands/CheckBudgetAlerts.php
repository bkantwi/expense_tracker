<?php

namespace App\Console\Commands;

use App\Services\BudgetAlertService;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class CheckBudgetAlerts extends Command
{
    protected $signature = 'budgets:check-alerts {--date=} {--user=}';
    protected $description = 'Compute spend vs budget for the current month and send alerts at thresholds';

    public function handle(\App\Services\BudgetAlertService $svc): int
    {
        $date   = $this->option('date') ? \Illuminate\Support\Carbon::parse($this->option('date')) : now();
        $userId = $this->option('user') ? (int) $this->option('user') : null;

        $out = $svc->check($date, $userId);

        // Print on separate lines so tests can match each token reliably
        $this->line('Checked: ' . $out['checked']);
        $this->line('Notified: ' . $out['notified']);

        return self::SUCCESS;
    }
}
