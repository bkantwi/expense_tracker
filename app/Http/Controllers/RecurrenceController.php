<?php

namespace App\Http\Controllers;

use App\Http\Requests\RecurrenceRequest;
use App\Models\Category;
use App\Models\Recurrence;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RecurrenceController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Recurrence::class, 'recurrence');
    }

    public function index(Request $request)
    {
        $recs = Recurrence::with('category')
            ->where('user_id', Auth::id())
            ->orderByDesc('active')
            ->orderBy('next_run_on')
            ->paginate(15);

        return view('recurrences.index', compact('recs'));
    }

    public function create()
    {
        $categories = Category::where('user_id', Auth::id())
            ->orderBy('name')->get(['id','name']);
        return view('recurrences.create', compact('categories'));
    }

    public function store(RecurrenceRequest $request)
    {
        Recurrence::create([
            'user_id'     => Auth::id(),
            ...$request->validated(),
        ]);

        return redirect()->route('recurrences.index')->with('success', 'Recurring expense created.');
    }

    public function edit(Recurrence $recurrence)
    {
        $categories = Category::where('user_id', Auth::id())
            ->orderBy('name')->get(['id','name']);
        return view('recurrences.edit', compact('recurrence','categories'));
    }

    public function update(RecurrenceRequest $request, Recurrence $recurrence)
    {
        $recurrence->update($request->validated());
        return redirect()->route('recurrences.index')->with('success', 'Recurring expense updated.');
    }

    public function destroy(Recurrence $recurrence)
    {
        $recurrence->delete();
        return redirect()->route('recurrences.index')->with('success', 'Recurring expense deleted.');
    }
}
