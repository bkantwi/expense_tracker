<?php

namespace App\Services;

use App\Models\Budget;
use App\Models\User;
use App\Notifications\BudgetThresholdCrossed;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;

class BudgetAlertService
{
    /** Check all (or a single user's) budgets for the month containing $date. Returns counters. */
    public function check(Carbon $date, ?int $userId = null): array
    {
        $start = $date->copy()->startOfMonth();
        $end   = $date->copy()->endOfMonth();

        $budgets = Budget::query()
            ->when($userId, fn($q, $uid) => $q->where('user_id', $uid))
            ->whereDate('period', $start->toDateString()) // this month's budget row
            ->where('alerts_enabled', true)
            ->get();

        $notified = 0; $checked = 0;

        foreach ($budgets as $b) {
            $checked++;
            if ($b->amount <= 0) {
                // Skip invalid zero-budget rows
                continue;
            }

            $spent = DB::table('expenses')
                ->where('user_id', $b->user_id)
                ->where('category_id', $b->category_id)
                ->whereBetween('spent_at', [$start->toDateString(), $end->toDateString()])
                ->sum('amount');

            $pct = $spent > 0 ? ($spent / (float)$b->amount) * 100.0 : 0.0;

            // Send the highest crossed threshold first, only once per threshold.
            $level = null;
            if ($pct >= $b->over_threshold && is_null($b->over_sent_at)) {
                $b->over_sent_at = now();
                $level = 'over';
            } elseif ($pct >= $b->at_threshold && is_null($b->at_sent_at)) {
                $b->at_sent_at = now();
                $level = 'at';
            } elseif ($pct >= $b->warn_threshold && is_null($b->warn_sent_at)) {
                $b->warn_sent_at = now();
                $level = 'warn';
            }

            if ($level) {
                $user = User::find($b->user_id);
                if ($user) {
                    Notification::send($user, new BudgetThresholdCrossed($b, $spent, round($pct, 1), $level));
                    $notified++;
                }
                $b->save();
            }
        }

        return compact('checked', 'notified');
    }
}
