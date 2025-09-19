<?php

namespace App\Http\Controllers;

use App\Http\Requests\BudgetRequest;
use App\Models\Budget;
use App\Models\Category;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BudgetController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Budget::class, 'budget');
    }

    public function index(Request $request)
    {
        // Optional filters: month (YYYY-MM) and category
        $period = $request->get('period'); // e.g., "2025-09"
        $categoryId = $request->get('category_id');

        $budgets = Budget::with('category')
            ->where('user_id', Auth::id())
            ->when($period && preg_match('/^\d{4}-\d{2}$/', $period), function($q) use ($period) {
                $q->where('period', \Illuminate\Support\Carbon::createFromFormat('Y-m', $period)->startOfMonth()->toDateString());
            })
            ->when($categoryId, fn($q, $cid) => $q->where('category_id', $cid))
            ->orderByDesc('period')
            ->orderBy('category_id')
            ->paginate(12)
            ->withQueryString();

        $categories = Category::where('user_id', Auth::id())->orderBy('name')->get(['id','name']);

        return view('budgets.index', compact('budgets','categories','period','categoryId'));
    }

    public function create()
    {
        $categories = Category::where('user_id', Auth::id())->orderBy('name')->get(['id','name']);
        return view('budgets.create', compact('categories'));
    }

    public function store(BudgetRequest $request)
    {
        Budget::create([
            'user_id' => Auth::id(),
            'category_id' => $request->category_id,
            'period' => $request->period,
            'amount' => $request->amount,
        ]);

        return redirect()->route('budgets.index')->with('success', 'Budget created.');
    }

    public function show(Budget $budget)
    {
        return view('budgets.show', compact('budget'));
    }

    public function edit(Budget $budget)
    {
        $categories = Category::where('user_id', Auth::id())->orderBy('name')->get(['id','name']);
        return view('budgets.edit', compact('budget','categories'));
    }

    /**
     * @throws AuthorizationException
     */
// app/Http/Controllers/BudgetController.php

    public function update(BudgetRequest $request, \App\Models\Budget $budget)
    {
        $this->authorize('update', $budget);

        $data = $request->validated();
        $dirtyKeys = ['amount','period','alerts_enabled','warn_threshold','at_threshold','over_threshold'];

        $changed = collect($dirtyKeys)->some(fn($k) => array_key_exists($k,$data) && $budget->{$k} != $data[$k]);

        $budget->update($data);

        if ($changed) {
            $budget->update([
                'warn_sent_at' => null,
                'at_sent_at'   => null,
                'over_sent_at' => null,
            ]);
        }

        return redirect()->route('budgets.index')->with('success', 'Budget updated.');
    }

    public function destroy(Budget $budget)
    {
        $budget->delete();
        return redirect()->route('budgets.index')->with('success', 'Budget deleted.');
    }
}
