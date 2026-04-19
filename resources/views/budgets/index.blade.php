@php use Illuminate\Support\Carbon; @endphp
<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Budgets') }}</h2>
            <a href="{{ route('budgets.create') }}"
               class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">
                + {{ __('Create Budget') }}
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <x-flash />

            @if($monthlyGroups && $monthlyGroups->isNotEmpty())
                {{-- Grouped by month view --}}
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach($monthlyGroups as $group)
                        <a href="{{ route('budgets.index', ['period' => $group->period->format('Y-m')]) }}"
                           class="bg-white rounded-lg shadow-sm p-6 border border-gray-100 hover:border-indigo-300 transition-colors block">
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="text-lg font-semibold text-gray-900">{{ $group->period->format('F Y') }}</h3>
                                <div class="text-indigo-600 font-medium">View →</div>
                            </div>
                            <div class="text-sm text-gray-500">
                                Total Budget: <span class="text-gray-900 font-semibold">₵{{ number_format($group->total_budget, 2) }}</span>
                            </div>
                        </a>
                    @endforeach
                </div>
            @else
                {{-- Detail view (list for a specific month) OR empty state if no groups --}}
                @if(!request('period') && !request('category_id') && !request('account_id'))
                    {{-- Truly empty state --}}
                    <div class="bg-white rounded-lg shadow-sm p-8 text-center text-gray-600">
                        No budgets yet. <a href="{{ route('budgets.create') }}" class="text-indigo-600 hover:underline">Create your first one!</a>
                    </div>
                @else
                    <form method="GET" class="mb-4">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-2">
                            <input type="month" name="period" value="{{ request('period') }}"
                                   class="rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500" />
                            <select name="category_id" class="rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">All categories</option>
                                @foreach($categories as $c)
                                    <option value="{{ $c->id }}" @selected(request('category_id')==$c->id)>{{ $c->name }}</option>
                                @endforeach
                            </select>
                            <div class="flex gap-2">
                                <button class="px-4 py-2 rounded-lg border bg-white hover:bg-gray-50 flex-1">Filter</button>
                                <a href="{{ route('budgets.index') }}" class="px-4 py-2 rounded-lg border bg-gray-50 text-gray-600 flex items-center justify-center">Reset</a>
                            </div>
                        </div>
                    </form>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    @forelse($budgets as $b)
                        @php
                            $spent = $b->spent();
                            $remaining = max(0, $b->amount - $spent);
                            $pct = $b->amount > 0 ? min(100, round(($spent / $b->amount) * 100)) : 0;
                            $over = $spent > $b->amount;
                        @endphp
                        <div class="bg-white rounded-lg shadow-sm p-5 border border-gray-100">
                            <div class="flex items-center justify-between mb-1">
                                <div class="font-semibold text-gray-900">
                                    {{ $b->category->name ?? '—' }}
                                    <span class="text-xs ml-2 px-2 py-0.5 rounded bg-gray-100 text-gray-700">{{ $b->account->name ?? '—' }}</span>
                                </div>
                                <div class="text-sm text-gray-500">{{ $b->period->format('M Y') }}</div>
                            </div>

                            <div class="text-sm text-gray-600 mb-2">
                                Budget: <span class="font-medium">₵{{ number_format($b->amount,2) }}</span>
                            </div>

                            {{-- Progress bar --}}
                            <div class="w-full h-2 rounded bg-gray-100 overflow-hidden mb-2">
                                <div class="h-full {{ $over ? 'bg-red-500' : 'bg-indigo-600' }}" style="width: {{ $pct }}%"></div>
                            </div>

                            <div class="flex items-center justify-between text-sm">
                                <div>Spent: <span class="font-medium">₵{{ number_format($spent,2) }}</span></div>
                                <div class="{{ $over ? 'text-red-600' : 'text-gray-700' }}">
                                    {{ $over ? 'Over by' : 'Left' }}:
                                    <span class="font-medium">₵{{ number_format(abs($b->amount - $spent),2) }}</span>
                                </div>
                            </div>

                            <div class="mt-4 flex gap-2">
                                <a href="{{ route('budgets.edit', $b) }}"
                                   class="inline-flex items-center px-3 py-1.5 rounded-md border hover:bg-gray-50 text-sm">Edit</a>
                                <form action="{{ route('budgets.destroy', $b) }}" method="POST" onsubmit="return confirm('Delete this budget?')">
                                    @csrf @method('DELETE')
                                    <button class="inline-flex items-center px-3 py-1.5 rounded-md bg-red-600 text-white hover:bg-red-700 text-sm">Delete</button>
                                </form>
                            </div>
                        </div>
                    @empty
                        <div class="col-span-full bg-white rounded-lg shadow-sm p-8 text-center text-gray-600">
                            No budgets found for this month/category.
                        </div>
                    @endforelse
                </div>

                <div class="mt-4">{{ $budgets->links() }}</div>
                @endif
            @endif
        </div>
    </div>
</x-app-layout>
