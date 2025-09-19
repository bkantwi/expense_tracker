<x-flash />
<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Dashboard') }}
            </h2>
            <div class="flex gap-2">
                <a href="{{ route('expenses.create', [], false) }}"
                   class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition">
                    <!-- plus icon -->
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" viewBox="0 0 20 20" fill="currentColor"><path d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z"/></svg>
                    {{ __('Add Expense') }}
                </a>
                <a href="{{ route('categories.create', [], false) }}"
                   class="inline-flex items-center px-4 py-2 bg-gray-800 text-white rounded-lg hover:bg-gray-900 transition">
                    <!-- folder icon -->
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" viewBox="0 0 24 24" fill="currentColor"><path d="M9.707 5.293A1 1 0 009 5H4a2 2 0 00-2 2v1h20V8a2 2 0 00-2-2h-7a1 1 0 01-.707-.293L10.586 4H10a1 1 0 00-.707.293l-.586.586z"/><path d="M22 10H2v6a2 2 0 002 2h16a2 2 0 002-2v-6z"/></svg>
                    {{ __('Add Category') }}
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            <!-- Stats -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <x-dashboard.stat
                    title="This Month"
                    :value="number_format($stats['month_total'], 2)"
                    prefix="₵"
                    icon="M3 10a7 7 0 1114 0A7 7 0 013 10zm7-4a1 1 0 011 1v2h2a1 1 0 110 2h-2v2a1 1 0 11-2 0v-2H7a1 1 0 110-2h2V7a1 1 0 011-1z" />

                <x-dashboard.stat
                    title="Today"
                    :value="number_format($stats['today_total'], 2)"
                    prefix="₵"
                    icon="M10 2a8 8 0 100 16A8 8 0 0010 2zM9 5a1 1 0 112 0v5h3a1 1 0 110 2H9V5z" />

                <x-dashboard.stat
                    title="Total Expenses"
                    :value="$stats['total_count']"
                    icon="M17 12h-2v2h2V12zm-4 0H9v2h4V12zm-4 0H5v2h4V12zM19 8h-2V6h2v2zm-4 0H9V6h6v2zm-6 0H5V6h4v2z" />

                <x-dashboard.stat
                    title="Categories"
                    :value="$stats['category_count']"
                    icon="M4 4h6v6H4V4zm0 8h6v6H4v-6zm8-8h6v6h-6V4zm0 8h6v6h-6v-6z" />
            </div>

            <!-- Recent -->
            <div class="mt-8 bg-white shadow-sm rounded-lg overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-gray-800">{{ __('Recent Expenses') }}</h3>
                    <a href="{{ route('expenses.index', [], false) }}" class="text-sm text-indigo-600 hover:text-indigo-800">
                        {{ __('View all') }} →
                    </a>
                </div>

                @if($recentExpenses->count() > 0)
                    <div class="divide-y divide-gray-100">
                        @foreach($recentExpenses as $exp)
                            <div class="px-6 py-4 flex items-center justify-between">
                                <div>
                                    <div class="font-medium text-gray-900">{{ $exp->title }}</div>
                                    <div class="text-xs text-gray-500">
                                        {{ \Illuminate\Support\Carbon::parse($exp->spent_at)->format('M d, Y') }}
                                    </div>
                                </div>
                                <div class="text-right">
                                    <div class="text-gray-900 font-semibold">₵{{ number_format($exp->amount, 2) }}</div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="px-6 py-10 text-center">
                        <div class="mx-auto w-12 h-12 rounded-full bg-gray-100 flex items-center justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-gray-400" viewBox="0 0 20 20" fill="currentColor"><path d="M3 4a1 1 0 011-1h3l1 1h6a1 1 0 011 1v9a2 2 0 01-2 2H5a2 2 0 01-2-2V4z"/></svg>
                        </div>
                        <p class="mt-4 text-gray-700 font-medium">No expenses yet</p>
                        <p class="text-gray-500 text-sm">Start by creating a category, then add your first expense.</p>
                        <div class="mt-6 flex justify-center gap-3">
                            <a href="{{ route('categories.create', [], false) }}" class="px-4 py-2 border rounded-lg text-gray-700 hover:bg-gray-50">Create Category</a>
                            <a href="{{ route('expenses.create', [], false) }}" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">Add Expense</a>
                        </div>
                    </div>
                @endif
            </div>

        </div>
    </div>
</x-app-layout>
