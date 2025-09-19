<?php

namespace App\Http\Controllers;

use App\Http\Requests\CategoryRequest;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CategoryController extends Controller
{
    public function __construct()
    {
        // This automatically applies policy methods per action (view, update, etc.)
        $this->authorizeResource(Category::class, 'category');
    }

    public function index(Request $request)
    {
        // Only fetch the current user's categories, newest first.
        $categories = Category::where('user_id', Auth::id())
            ->when($request->get('q'), function ($query, $q) {
                $query->where('name', 'like', "%{$q}%");
            })
            ->orderByDesc('id')
            ->paginate(10)
            ->withQueryString();

        return view('categories.index', compact('categories'));
    }

    public function create()
    {
        return view('categories.create');
    }

    public function store(CategoryRequest $request)
    {
        Category::create([
            'user_id' => Auth::id(),
            'name' => $request->name,
            'description' => $request->description,
        ]);

        return redirect()
            ->route('categories.index')
            ->with('success', 'Category created.');
    }

    public function show(Category $category)
    {
        // Not strictly required in an expense app; included for completeness.
        return view('categories.show', compact('category'));
    }

    public function edit(Category $category)
    {
        return view('categories.edit', compact('category'));
    }

    public function update(CategoryRequest $request, Category $category)
    {
        $category->update($request->validated());

        return redirect()
            ->route('categories.index')
            ->with('success', 'Category updated.');
    }

    public function destroy(Category $category)
    {
        $category->delete();

        return redirect()
            ->route('categories.index')
            ->with('success', 'Category deleted.');
    }
}
