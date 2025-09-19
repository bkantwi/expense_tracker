<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ $budget->category->name ?? 'Budget' }} — {{ $budget->period->format('M Y') }}</h2>
            <a href="{{ route('budgets.index') }}" class="text-indigo-600 hover:text-indigo-800">Back to list</a>
        </div>
    </x-slot>

    @php
        $spent = $budget->spent();
        $pct = $budget->amount > 0 ? min(100, round(($spent / $budget->amount) * 100)) : 0;
        $over = $spent > $budget->amount;
    @endphp

    <div class="py-8">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white rounded-lg shadow-sm p-6 space-y-4">
                <div>Budget: <span class="font-semibold">₵{{ number_format($budget->amount,2) }}</span></div>
                <div>Spent: <span class="font-semibold">₵{{ number_format($spent,2) }}</span></div>

                <div class="w-full h-2 rounded bg-gray-100 overflow-hidden">
                    <div class="h-full {{ $over ? 'bg-red-500' : 'bg-indigo-600' }}" style="width: {{ $pct }}%"></div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
