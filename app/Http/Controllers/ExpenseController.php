<?php

namespace App\Http\Controllers;

use App\Http\Requests\ExpenseRequest;
use App\Models\Category;
use App\Models\Expense;
use App\Models\ExpenseAttachment;
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
        $accounts = \App\Models\Account::where('user_id', Auth::id())
            ->where('archived', false)
            ->orderBy('name')
            ->get(['id','name','currency']);

        return view('expenses.create', compact('categories','accounts'));
    }

    public function store(ExpenseRequest $request)
    {
        $expense = DB::transaction(function () use ($request) {
            $expense = \App\Models\Expense::create([
                'user_id'     => Auth::id(),
                'category_id' => $request->category_id,
                'account_id'  => $request->account_id,
                'title'       => $request->title,
                'amount'      => $request->amount,
                'spent_at'    => $request->spent_at,
                'notes'       => $request->notes,
            ]);

            // optional recurrence creation...
            if ($request->boolean('make_recurring')) {
                \App\Models\Recurrence::create([
                    'user_id'     => Auth::id(),
                    'category_id' => $expense->category_id,
                    'account_id'  => $expense->account_id,
                    'title'       => $expense->title,
                    'amount'      => $expense->amount,
                    'cadence'     => $request->input('recurrence_cadence', 'monthly'),
                    'interval'    => (int) $request->input('recurrence_interval', 1),
                    'next_run_on' => $request->input('recurrence_next_run_on', now()->toDateString()),
                    'notes'       => $expense->notes,
                    'active'      => true,
                ]);
            }

            // handle attachments (if any were uploaded on create)
            if ($request->hasFile('files')) {
                foreach ((array) $request->file('files') as $file) {
                    $path = $file->store('receipts', 'public');
                    ExpenseAttachment::create([
                        'user_id'       => Auth::id(),
                        'expense_id'    => $expense->id,
                        'path'          => $path,
                        'original_name' => $file->getClientOriginalName(),
                        'mime_type'     => $file->getClientMimeType(),
                        'size'          => $file->getSize(),
                    ]);
                }
            }

            return $expense;
        });

        return redirect()->route('expenses.index')->with('success','Expense added.');
    }

    public function show(Expense $expense)
    {
        // Optional detailed view
        return view('expenses.show', compact('expense'));
    }

    public function edit(Expense $expense)
    {
        $categories = Category::where('user_id', Auth::id())->orderBy('name')->get(['id','name']);
        $accounts = \App\Models\Account::where('user_id', Auth::id())
            ->where('archived', false)
            ->orderBy('name')
            ->get(['id','name','currency']);

        return view('expenses.edit', compact('expense','categories','accounts'));
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
