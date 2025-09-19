<?php

namespace App\Services;

use App\Models\Expense;
use App\Models\Recurrence;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class RecurrenceRunner
{
    /**
     * Materialize all due recurrences (next_run_on <= $date), optionally for a single user.
     * Returns ['created' => n, 'advanced' => n, 'skipped' => n].
     */
    public function runDue(Carbon $date, ?int $userId = null): array
    {
        $created = $advanced = $skipped = 0;

        $recurrences = Recurrence::query()
            ->where('active', true)
            ->when($userId, fn($q, $uid) => $q->where('user_id', $uid))
            ->whereDate('next_run_on', '<=', $date->toDateString())
            ->where(function ($q) use ($date) {
                $q->whereNull('ends_on')->orWhereDate('ends_on', '>=', $date->toDateString());
            })
            ->orderBy('next_run_on')
            ->lockForUpdate() // avoid races if multiple workers
            ->get();

        DB::transaction(function () use ($recurrences, $date, &$created, &$advanced, &$skipped) {
            foreach ($recurrences as $r) {
                $runFor = $r->next_run_on->copy(); // the scheduled day to post

                // Idempotency: skip if an identical auto expense already exists for that day.
                $exists = Expense::query()
                    ->where('user_id', $r->user_id)
                    ->where('category_id', $r->category_id)
                    ->whereDate('spent_at', $runFor->toDateString())
                    ->where('title', $r->title)
                    ->where('amount', $r->amount)
                    ->exists();

                if ($exists) {
                    $skipped++;
                } else {
                    Expense::create([
                        'user_id'     => $r->user_id,
                        'category_id' => $r->category_id,
                        'title'       => $r->title,
                        'amount'      => $r->amount,
                        'spent_at'    => $runFor->toDateString(),
                        'notes'       => $r->notes,
                    ]);
                    $created++;
                }

                $r->last_run_on = $runFor;
                $r->next_run_on = $this->nextDate($r);
                $r->save();
                $advanced++;
            }
        });

        return compact('created','advanced','skipped');
    }

    /** Compute the next run date based on cadence & interval (no overflow for months). */
    public function nextDate(Recurrence $r): Carbon
    {
        $cur = $r->next_run_on->copy();
        $n   = max(1, (int) $r->interval);

        return match ($r->cadence) {
            'daily'     => $cur->addDays($n),
            'weekly'    => $cur->addWeeks($n),
            'monthly'   => $cur->addMonthsNoOverflow($n),
            'quarterly' => $cur->addMonthsNoOverflow(3 * $n),
            'yearly'    => $cur->addYears($n),
            default     => $cur->addMonthsNoOverflow($n),
        };
    }
}
