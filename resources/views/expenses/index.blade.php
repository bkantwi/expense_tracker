@php use Illuminate\Support\Str; @endphp

<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Expenses') }}</h2>

            <div class="flex items-center gap-2">
                <a href="{{ route('expenses.import.index') }}"
                   class="inline-flex items-center px-4 py-2 border rounded-lg bg-white text-gray-700 hover:bg-gray-50">
                    Import CSV
                </a>

                <a href="{{ route('expenses.create') }}"
                   class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">
                    + {{ __('Add Expense') }}
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <x-flash />

            <form method="GET" class="mb-4">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-2">
                    <input name="q" value="{{ request('q') }}" placeholder="Search by title…"
                           class="rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500" />
                    <select name="category_id"
                            class="rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">All categories</option>
                        @foreach($categories as $c)
                            <option value="{{ $c->id }}" @selected(request('category_id') == $c->id)>{{ $c->name }}</option>
                        @endforeach
                    </select>
                    <button class="px-4 py-2 rounded-lg border bg-white hover:bg-gray-50">Filter</button>
                </div>
            </form>

            <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Title</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Category</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Amount</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                        <th class="px-6 py-3"></th>
                    </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                    @forelse($expenses as $e)
                        <tr>
                            <td class="px-6 py-4 font-medium text-gray-900">
                                <a href="{{ route('expenses.show', $e) }}" class="hover:underline">
                                    {{ Str::limit($e->title, 80) }}
                                </a>
                            </td>
                            <td class="px-6 py-4 text-gray-600">{{ $e->category->name ?? '—' }}</td>
                            <td class="px-6 py-4 text-right text-gray-900 font-semibold">₵{{ number_format($e->amount, 2) }}</td>
                            <td class="px-6 py-4 text-gray-600">{{ $e->spent_at->format('M d, Y') }}</td>
                            <td class="px-6 py-4 text-right">
                                <a href="{{ route('expenses.edit', $e) }}"
                                   class="inline-flex items-center px-3 py-1.5 rounded-md border hover:bg-gray-50 mr-2">Edit</a>
                                <form action="{{ route('expenses.destroy', $e) }}" method="POST" class="inline"
                                      onsubmit="return confirm('Delete this expense?');">
                                    @csrf @method('DELETE')
                                    <button class="inline-flex items-center px-3 py-1.5 rounded-md bg-red-600 text-white hover:bg-red-700">
                                        Delete
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-16 text-center text-gray-600">
                                No expenses yet. Click “Add Expense” to create one.
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $expenses->links() }}
            </div>
        </div>
    </div>
</x-app-layout>
