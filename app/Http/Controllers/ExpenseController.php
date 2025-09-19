<?php

namespace App\Http\Controllers;

use App\Http\Requests\ExpenseRequest;
use App\Models\Category;
use App\Models\Expense;
use App\Models\Recurrence;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ExpenseController extends Controller
{
    public function __construct()
    {
        // Requires base Controller to use AuthorizesRequests
        $this->authorizeResource(Expense::class, 'expense');
    }

    public function index(Request $request)
    {
        // List my expenses, filterable by q (title) and category
        $expenses = Expense::with('category')
            ->where('user_id', Auth::id())
            ->when($request->get('q'), fn($q, $term) => $q->where('title','like',"%{$term}%"))
            ->when($request->get('category_id'), fn($q, $cid) => $q->where('category_id',$cid))
            ->orderByDesc('spent_at')
            ->orderByDesc('id')
            ->paginate(10)
            ->withQueryString();

        // For filter dropdown
        $categories = Category::where('user_id', Auth::id())->orderBy('name')->get(['id','name']);

        return view('expenses.index', compact('expenses','categories'));
    }

    public function create()
    {
        // If user has no categories, encourage creating one first
        $categories = Category::where('user_id', Auth::id())->orderBy('name')->get(['id','name']);
        return view('expenses.create', compact('categories'));
    }

    public function store(ExpenseRequest $request)
    {
        DB::transaction(function () use ($request) {
            $expense = Expense::create([
                'user_id'     => Auth::id(),
                'category_id' => $request->category_id,
                'title'       => $request->title,
                'amount'      => $request->amount,
                'spent_at'    => $request->spent_at,
                'notes'       => $request->notes,
            ]);

            if ($request->boolean('make_recurring')) {
                Recurrence::create([
                    'user_id'     => Auth::id(),
                    'category_id' => $expense->category_id,
                    'title'       => $expense->title,
                    'amount'      => $expense->amount,
                    'cadence'     => $request->input('recurrence_cadence', 'monthly'),
                    'interval'    => (int) $request->input('recurrence_interval', 1),
                    'next_run_on' => $request->input('recurrence_next_run_on', now()->toDateString()),
                    'notes'       => $expense->notes,
                    'active'      => true,
                ]);
            }
        });

        return redirect()->route('expenses.index')->with('success', 'Expense added.');
    }

    public function show(Expense $expense)
    {
        // Optional detailed view
        return view('expenses.show', compact('expense'));
    }

    public function edit(Expense $expense)
    {
        $categories = Category::where('user_id', Auth::id())->orderBy('name')->get(['id','name']);
        return view('expenses.edit', compact('expense','categories'));
    }

    public function update(ExpenseRequest $request, Expense $expense)
    {
        $expense->update($request->validated());
        return redirect()->route('expenses.index')->with('success','Expense updated.');
    }

    public function destroy(Expense $expense)
    {
        $expense->delete();
        return redirect()->route('expenses.index')->with('success','Expense deleted.');
    }
}
