<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

class DashboardController extends Controller
{
    public function __invoke(Request $request): \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
    {
        $user = Auth::user();

        // Defaults for when we haven't created tables yet
        $stats = [
            'month_total' => 0,
            'today_total' => 0,
            'total_count' => 0,
            'category_count' => 0,
        ];
        $recentExpenses = collect();

        // Only query if tables exist (so the page works before migrations)
        if (Schema::hasTable('expenses')) {
            $Expense = app('App\\Models\\Expense');
            $startOfMonth = Carbon::now()->startOfMonth()->toDateString();
            $today = Carbon::today()->toDateString();

            $stats['month_total'] = (float) $Expense::where('user_id', $user->id)
                ->whereDate('spent_at', '>=', $startOfMonth)
                ->sum('amount');

            $stats['today_total'] = (float) $Expense::where('user_id', $user->id)
                ->whereDate('spent_at', $today)
                ->sum('amount');

            $stats['total_count'] = (int) $Expense::where('user_id', $user->id)->count();

            $recentExpenses = $Expense::where('user_id', $user->id)
                ->orderByDesc('spent_at')
                ->orderByDesc('id')
                ->take(5)
                ->get(['id','title','amount','spent_at']);
        }

        if (Schema::hasTable('categories')) {
            $Category = app('App\\Models\\Category');
            $stats['category_count'] = (int) $Category::where('user_id', $user->id)->count();
        }

        return view('dashboard', [
            'stats' => $stats,
            'recentExpenses' => $recentExpenses,
        ]);
    }
}
