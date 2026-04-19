<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Add Expense') }}</h2>
            <a href="{{ route('expenses.index') }}" class="text-indigo-600 hover:text-indigo-800">Back to list</a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            <x-flash />

            @if($categories->isEmpty())
                <div class="mb-4 rounded-lg bg-yellow-50 text-yellow-800 px-4 py-3">
                    You have no categories yet. <a class="underline" href="{{ route('categories.create') }}">Create a category</a> first.
                </div>
            @endif

            <form method="POST" action="{{ route('expenses.store') }}" enctype="multipart/form-data"
                  class="bg-white rounded-lg shadow-sm p-6 space-y-4">
                @csrf

                <div>
                    <label class="block text-sm font-medium text-gray-700">Title</label>
                    <input name="title" value="{{ old('title') }}" required
                           class="mt-1 w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500" />
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Amount (GHS)</label>
                        <input type="number" step="0.01" min="0.01" name="amount" value="{{ old('amount') }}" required
                               class="mt-1 w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Date</label>
                        <input type="date" name="spent_at" value="{{ old('spent_at', now()->toDateString()) }}" required
                               class="mt-1 w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500" />
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Category</label>
                    <select name="category_id" required
                            class="mt-1 w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">Select category</option>
                        @foreach($categories as $c)
                            <option value="{{ $c->id }}" @selected(old('category_id') == $c->id)>{{ $c->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Notes (optional)</label>
                    <textarea name="notes" rows="4"
                              class="mt-1 w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">{{ old('notes') }}</textarea>
                </div>

                <hr class="my-6">

                <h4 class="font-semibold text-gray-800 mb-2">Make this expense recurring</h4>
                <label class="inline-flex items-center mb-3">
                    <input type="checkbox" name="make_recurring" value="1" class="rounded border-gray-300 text-indigo-600">
                    <span class="ml-2 text-gray-700">Repeat this expense</span>
                </label>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Cadence</label>
                        <select name="recurrence_cadence" class="mt-1 w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                            @foreach(['daily','weekly','monthly','quarterly','yearly'] as $opt)
                                <option value="{{ $opt }}">{{ ucfirst($opt) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Every</label>
                        <input type="number" name="recurrence_interval" min="1" value="1" class="mt-1 w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Next run on</label>
                        <input type="date" name="recurrence_next_run_on" value="{{ now()->toDateString() }}" class="mt-1 w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500" />
                    </div>
                </div>

                <hr class="my-6">
                <h4 class="font-semibold text-gray-800 mb-2">Receipts</h4>
                <input type="file" name="files[]" multiple
                       class="mt-1 w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500" />
                <p class="text-xs text-gray-500 mt-1">Accepted: JPG, PNG, WEBP, PDF. Max 5MB each.</p>

                <div class="flex items-center gap-3">
                    <button class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">Save</button>
                    <a href="{{ route('expenses.index') }}" class="px-4 py-2 rounded-lg border hover:bg-gray-50">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
