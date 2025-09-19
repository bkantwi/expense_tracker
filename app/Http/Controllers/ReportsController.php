<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Services\ReportService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\DB;

class ReportsController extends Controller
{
    public function __construct(private ReportService $reports) {}

    public function index(Request $request)
    {
        $user   = $request->user();
        $start  = $this->parseDate($request->input('start')) ?? now()->startOfMonth();
        $end    = $this->parseDate($request->input('end'))   ?? now()->endOfMonth();
        if ($end->lt($start)) [$start, $end] = [$end, $start];

        $categoryId = null;
        if ($request->filled('category_id')) {
            $cid = (int) $request->input('category_id');
            $exists = Category::where('user_id', $user->id)->where('id', $cid)->exists();
            if ($exists) $categoryId = $cid;
        }

        $categories  = Category::where('user_id', $user->id)->orderBy('name')->get(['id','name']);
        $monthly     = $this->reports->totalsByMonth($user->id, $start, $end, $categoryId);
        $byCategory  = $this->reports->totalsByCategory($user->id, $start, $end, $categoryId);
        $isSingleMon = $start->format('Y-m') === $end->format('Y-m')
            && $start->isSameDay($start->copy()->startOfMonth())
            && $end->isSameDay($end->copy()->endOfMonth());
        $budgetTable = $isSingleMon ? $this->reports->budgetVsActual($user->id, $start) : collect();

        return view('reports.index', compact(
            'start','end','categories','categoryId','monthly','byCategory','budgetTable','isSingleMon'
        ));
    }

    public function export(Request $request)
    {
        $user  = $request->user();
        $start = $this->parseDate($request->input('start')) ?? now()->startOfMonth();
        $end   = $this->parseDate($request->input('end'))   ?? now()->endOfMonth();
        if ($end->lt($start)) [$start, $end] = [$end, $start];

        $categoryId = $request->input('category_id');

        $query = DB::table('expenses as e')
            ->leftJoin('categories as c', 'c.id', '=', 'e.category_id')
            ->where('e.user_id', $user->id)
            ->whereBetween('e.spent_at', [$start->toDateString(), $end->toDateString()])
            ->when($categoryId, fn($q, $cid) => $q->where('e.category_id', $cid))
            ->orderBy('e.spent_at')
            ->select('e.spent_at', 'e.title', 'c.name as category', 'e.amount', 'e.notes');

        $filename = 'expenses_'.$start->format('Ymd').'_'.$end->format('Ymd').'.csv';

        return Response::streamDownload(function () use ($query) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Date','Title','Category','Amount','Notes']);
            $query->chunk(500, function ($rows) use ($out) {
                foreach ($rows as $r) {
                    fputcsv($out, [
                        \Illuminate\Support\Carbon::parse($r->spent_at)->toDateString(),
                        $r->title,
                        $r->category ?? '',
                        number_format((float)$r->amount, 2, '.', ''),
                        $r->notes ?? '',
                    ]);
                }
            });
            fclose($out);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    private function parseDate(?string $v): ?Carbon
    {
        if (!$v) return null;
        try { return Carbon::parse($v); } catch (\Throwable $e) { return null; }
    }
}
