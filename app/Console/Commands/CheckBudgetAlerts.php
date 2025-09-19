<?php

namespace App\Console\Commands;

use App\Services\BudgetAlertService;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class CheckBudgetAlerts extends Command
{
    protected $signature = 'budgets:check-alerts {--date=} {--user=}';
    protected $description = 'Compute spend vs budget for the current month and send alerts at thresholds';

    public function handle(BudgetAlertService $svc): int
    {
        $date   = $this->option('date') ? Carbon::parse($this->option('date')) : now();
        $userId = $this->option('user') ? (int) $this->option('user') : null;

        $out = $svc->check($date, $userId);
        $this->info("Checked: {$out['checked']}  Notified: {$out['notified']}");
        return self::SUCCESS;
    }
}
