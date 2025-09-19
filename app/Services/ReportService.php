<?php

namespace App\Services;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class ReportService
{
    /** Sum per month (YYYY-MM) within [start, end] for a user (optionally a single category). */
    public function totalsByMonth(int $userId, Carbon $start, Carbon $end, ?int $categoryId = null)
    {
        [$expr, $group] = $this->monthExpr('spent_at');

        return DB::table('expenses')
            ->selectRaw("$expr AS month, SUM(amount) AS total")
            ->where('user_id', $userId)
            ->when($categoryId, fn($q, $cid) => $q->where('category_id', $cid))
            ->whereBetween('spent_at', [$start->toDateString(), $end->toDateString()])
            ->groupBy(DB::raw($group))
            ->orderBy('month')
            ->get()
            ->map(fn($r) => ['month' => $r->month, 'total' => (float)$r->total]);
    }

    /** Sum per category within [start, end]; returns [{category, total}] sorted desc. */
    public function totalsByCategory(int $userId, Carbon $start, Carbon $end, ?int $categoryId = null)
    {
        return DB::table('expenses as e')
            ->join('categories as c', 'c.id', '=', 'e.category_id')
            ->where('e.user_id', $userId)
            ->where('c.user_id', $userId)
            ->when($categoryId, fn($q, $cid) => $q->where('e.category_id', $cid))
            ->whereBetween('e.spent_at', [$start->toDateString(), $end->toDateString()])
            ->groupBy('e.category_id', 'c.name')
            ->selectRaw('c.name as category, SUM(e.amount) as total')
            ->orderByDesc('total')
            ->get()
            ->map(fn($r) => ['category' => $r->category, 'total' => (float)$r->total]);
    }

    /** Budget vs actual for the month that contains $monthDate (budgets keyed by that month’s first day). */
    public function budgetVsActual(int $userId, Carbon $monthDate)
    {
        $start = $monthDate->copy()->startOfMonth();
        $end   = $monthDate->copy()->endOfMonth();

        // Budgets for this month
        $budgets = DB::table('budgets as b')
            ->join('categories as c', 'c.id', '=', 'b.category_id')
            ->where('b.user_id', $userId)
            ->where('c.user_id', $userId)
            ->whereDate('b.period', $start->toDateString())
            ->select('b.category_id', 'c.name as category', 'b.amount')
            ->get();

        // Spent per category this month
        $spent = DB::table('expenses')
            ->where('user_id', $userId)
            ->whereBetween('spent_at', [$start->toDateString(), $end->toDateString()])
            ->groupBy('category_id')
            ->selectRaw('category_id, SUM(amount) as total')
            ->pluck('total', 'category_id'); // [category_id => total]

        // Combine
        return $budgets->map(function ($b) use ($spent) {
            $s = (float)($spent[$b->category_id] ?? 0);
            return [
                'category' => $b->category,
                'budget'   => (float)$b->amount,
                'spent'    => $s,
                'variance' => (float)$b->amount - $s,
            ];
        })->values();
    }

    /** DB-agnostic month expression (returns [selectExpr, groupExpr]). */
    private function monthExpr(string $column): array
    {
        $driver = DB::getDriverName();
        return match ($driver) {
            'mysql' => ["DATE_FORMAT($column, '%Y-%m')", "DATE_FORMAT($column, '%Y-%m')"],
            'pgsql' => ["TO_CHAR($column, 'YYYY-MM')", "TO_CHAR($column, 'YYYY-MM')"],
            default => ["STRFTIME('%Y-%m', $column)", "STRFTIME('%Y-%m', $column)"], // sqlite
        };
    }
}
