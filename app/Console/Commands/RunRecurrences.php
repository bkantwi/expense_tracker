<?php

namespace App\Console\Commands;

use App\Services\RecurrenceRunner;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class RunRecurrences extends Command
{
    protected $signature = 'expenses:run-recurrences {--date=} {--user=}';
    protected $description = 'Materialize due recurring expenses into expenses table';

    public function handle(RecurrenceRunner $runner): int
    {
        $date   = $this->option('date') ? Carbon::parse($this->option('date')) : now();
        $userId = $this->option('user') ? (int) $this->option('user') : null;

        $out = $runner->runDue($date, $userId);

        $this->info("Created: {$out['created']}  Advanced: {$out['advanced']}  Skipped: {$out['skipped']}");
        return self::SUCCESS;
    }
}
