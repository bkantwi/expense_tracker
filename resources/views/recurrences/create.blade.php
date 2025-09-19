<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">New Recurring Expense</h2>
            <a href="{{ route('recurrences.index') }}" class="text-indigo-600 hover:text-indigo-800">Back</a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            <x-flash />

            <form method="POST" action="{{ route('recurrences.store') }}" class="bg-white rounded-lg shadow-sm p-6 space-y-4">
                @csrf

                <div>
                    <label class="block text-sm font-medium text-gray-700">Title</label>
                    <input type="text" name="title" value="{{ old('title') }}" required class="mt-1 w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500" />
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Category</label>
                        <select name="category_id" required class="mt-1 w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">Select</option>
                            @foreach($categories as $c)
                                <option value="{{ $c->id }}" @selected(old('category_id')==$c->id)>{{ $c->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Amount (GHS)</label>
                        <input type="number" step="0.01" min="0.01" name="amount" value="{{ old('amount') }}" required class="mt-1 w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500" />
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Cadence</label>
                        <select name="cadence" class="mt-1 w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                            @foreach(['daily','weekly','monthly','quarterly','yearly'] as $opt)
                                <option value="{{ $opt }}" @selected(old('cadence')==$opt)>{{ ucfirst($opt) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Every</label>
                        <input type="number" name="interval" min="1" value="{{ old('interval',1) }}" class="mt-1 w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Next run on</label>
                        <input type="date" name="next_run_on" value="{{ old('next_run_on', now()->toDateString()) }}" required class="mt-1 w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500" />
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Notes</label>
                    <textarea name="notes" rows="3" class="mt-1 w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">{{ old('notes') }}</textarea>
                </div>

                <label class="inline-flex items-center">
                    <input type="checkbox" name="active" value="1" class="rounded border-gray-300 text-indigo-600" {{ old('active', true) ? 'checked' : '' }}>
                    <span class="ml-2 text-gray-700">Active</span>
                </label>

                <div class="flex items-center gap-3 pt-2">
                    <button class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">Save</button>
                    <a href="{{ route('recurrences.index') }}" class="px-4 py-2 rounded-lg border hover:bg-gray-50">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
