@php $month = $budget->period->format('Y-m'); @endphp

<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Edit Budget') }}</h2>
            <a href="{{ route('budgets.index') }}" class="text-indigo-600 hover:text-indigo-800">Back to list</a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            <x-flash />

            <form method="POST" action="{{ route('budgets.update', $budget) }}" class="bg-white rounded-lg shadow-sm p-6 space-y-4">
                @csrf @method('PUT')

                <select name="account_id" class="mt-1 block w-full rounded-md border-gray-300 focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="">— Select account —</option>
                    @foreach($accounts as $acc)
                        <option value="{{ $acc->id }}" @selected(old('account_id', $expense->account_id ?? null) == $acc->id)>
                            {{ $acc->name }} ({{ $acc->currency }})
                        </option>
                    @endforeach
                </select>
                @error('account_id') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror

                <div>
                    <label class="block text-sm font-medium text-gray-700">Category</label>
                    <select name="category_id" required
                            class="mt-1 w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                        @foreach($categories as $c)
                        <option value="{{ $c->id }}" @selected(old('category_id', $budget->category_id) == $c->id)>{{ $c->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Month</label>
                        <input type="month" name="period" value="{{ old('period', $month) }}" required
                               class="mt-1 w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Amount (GHS)</label>
                        <input type="number" step="0.01" min="0.01" name="amount" value="{{ old('amount', $budget->amount) }}" required
                               class="mt-1 w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500" />
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    <button class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">Update</button>
                    <a href="{{ route('budgets.index') }}" class="px-4 py-2 rounded-lg border hover:bg-gray-50">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
