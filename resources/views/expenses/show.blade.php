<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ $expense->title }}</h2>
            <a href="{{ route('expenses.index') }}" class="text-indigo-600 hover:text-indigo-800">Back to list</a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white rounded-lg shadow-sm p-6 space-y-2">
                <div class="text-gray-600">Category: <span class="font-medium">{{ $expense->category->name ?? '—' }}</span></div>
                <div class="text-gray-600">Amount: <span class="font-semibold">₵{{ number_format($expense->amount,2) }}</span></div>
                <div class="text-gray-600">Date: <span class="font-medium">{{ $expense->spent_at->format('M d, Y') }}</span></div>
                <div class="text-gray-600">Notes:</div>
                <div class="text-gray-900 whitespace-pre-line">{{ $expense->notes ?: '—' }}</div>
            </div>
        </div>
    </div>
</x-app-layout>
