@php
    $monthlyLabels = collect($monthly)->pluck('month');
    $monthlyValues = collect($monthly)->pluck('total')->map(fn($v)=>round($v,2));
    $catLabels     = collect($byCategory)->pluck('category');
    $catValues     = collect($byCategory)->pluck('total')->map(fn($v)=>round($v,2));
@endphp

<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Reports') }}</h2>
            <a href="{{ route('reports.export', request()->only('start','end','category_id')) }}"
               class="inline-flex items-center px-4 py-2 bg-gray-800 text-white rounded-lg hover:bg-gray-900">
                {{ __('Download CSV') }}
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">

            <!-- Filters -->
            <form method="GET" class="bg-white rounded-lg shadow-sm p-4">
                <div class="grid grid-cols-1 md:grid-cols-5 gap-3">
                    <div>
                        <label class="block text-sm text-gray-600 mb-1">Start</label>
                        <input type="date" name="start" value="{{ $start->toDateString() }}"
                               class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500" />
                    </div>
                    <div>
                        <label class="block text-sm text-gray-600 mb-1">End</label>
                        <input type="date" name="end" value="{{ $end->toDateString() }}"
                               class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500" />
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm text-gray-600 mb-1">Category</label>
                        <select name="category_id"
                                class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">All</option>
                            @foreach($categories as $c)
                                <option value="{{ $c->id }}" @selected($categoryId == $c->id)>{{ $c->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex items-end">
                        <button class="px-4 py-2 w-full md:w-auto bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">
                            Apply
                        </button>
                    </div>
                </div>

                <!-- Quick ranges -->
                <div class="mt-3 flex flex-wrap gap-2 text-sm">
                    @php
                        $now = now();
                        $lmStart = $now->copy()->subMonth()->startOfMonth()->toDateString();
                        $lmEnd   = $now->copy()->subMonth()->endOfMonth()->toDateString();
                        $tmStart = $now->copy()->startOfMonth()->toDateString();
                        $tmEnd   = $now->copy()->endOfMonth()->toDateString();
                        $ytdStart= $now->copy()->startOfYear()->toDateString();
                    @endphp
                    <a class="px-3 py-1.5 border rounded hover:bg-gray-50"
                       href="{{ route('reports.index', ['start'=>$tmStart,'end'=>$tmEnd,'category_id'=>$categoryId]) }}">This month</a>
                    <a class="px-3 py-1.5 border rounded hover:bg-gray-50"
                       href="{{ route('reports.index', ['start'=>$lmStart,'end'=>$lmEnd,'category_id'=>$categoryId]) }}">Last month</a>
                    <a class="px-3 py-1.5 border rounded hover:bg-gray-50"
                       href="{{ route('reports.index', ['start'=>$ytdStart,'end'=>$tmEnd,'category_id'=>$categoryId]) }}">YTD</a>
                </div>
            </form>

            <!-- Monthly trend -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-3">Monthly Trend</h3>
                <div>
                    <canvas id="monthlyChart" height="120"></canvas>
                </div>
                <!-- Table fallback -->
                <div class="mt-4 overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-2 text-left text-gray-600">Month</th>
                            <th class="px-4 py-2 text-right text-gray-600">Total</th>
                        </tr>
                        </thead>
                        <tbody class="divide-y">
                        @forelse($monthly as $row)
                            <tr>
                                <td class="px-4 py-2">{{ $row['month'] }}</td>
                                <td class="px-4 py-2 text-right">₵{{ number_format($row['total'],2) }}</td>
                            </tr>
                        @empty
                            <tr><td class="px-4 py-4 text-gray-600" colspan="2">No data in this range.</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Category breakdown -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-3">Category Breakdown</h3>
                <div>
                    <canvas id="categoryChart" height="120"></canvas>
                </div>
                <!-- Table fallback -->
                <div class="mt-4 overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-2 text-left text-gray-600">Category</th>
                            <th class="px-4 py-2 text-right text-gray-600">Total</th>
                        </tr>
                        </thead>
                        <tbody class="divide-y">
                        @forelse($byCategory as $row)
                            <tr>
                                <td class="px-4 py-2">{{ $row['category'] }}</td>
                                <td class="px-4 py-2 text-right">₵{{ number_format($row['total'],2) }}</td>
                            </tr>
                        @empty
                            <tr><td class="px-4 py-4 text-gray-600" colspan="2">No data in this range.</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Budget vs Actual (only when a full single month is selected) -->
            @if($isSingleMon)
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <div class="flex items-center justify-between mb-3">
                        <h3 class="text-lg font-semibold text-gray-800">Budget vs Actual — {{ $start->format('M Y') }}</h3>
                        <a href="{{ route('budgets.index', ['period'=>$start->format('Y-m')]) }}" class="text-sm text-indigo-600 hover:text-indigo-800">Manage budgets →</a>
                    </div>

                    @if(($budgetTable ?? collect())->count() === 0)
                        <div class="text-gray-600">No budgets set for this month.</div>
                    @else
                        <div class="space-y-3">
                            @foreach($budgetTable as $row)
                                @php
                                    $spent = $row['spent']; $budget = max(0.01, $row['budget']);
                                    $pct = min(100, round(($spent/$budget)*100));
                                    $over = $spent > $budget;
                                @endphp
                                <div>
                                    <div class="flex items-center justify-between">
                                        <div class="font-medium text-gray-900">{{ $row['category'] }}</div>
                                        <div class="{{ $over ? 'text-red-600' : 'text-gray-700' }} text-sm">
                                            ₵{{ number_format($spent,2) }} / ₵{{ number_format($budget,2) }}
                                        </div>
                                    </div>
                                    <div class="w-full h-2 rounded bg-gray-100 overflow-hidden">
                                        <div class="h-full {{ $over ? 'bg-red-500' : 'bg-indigo-600' }}" style="width: {{ $pct }}%"></div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            @endif

        </div>
    </div>

    {{-- Chart.js --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Monthly trend (line)
        (function(){
            const ctx = document.getElementById('monthlyChart');
            if(!ctx) return;
            const labels = @json($monthlyLabels);
            const data = @json($monthlyValues);
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels,
                    datasets: [{
                        label: 'Total',
                        data,
                        fill: false,
                        tension: 0.25
                    }]
                },
                options: {
                    responsive: true,
                    plugins: { legend: { display: false } },
                    scales: {
                        y: { beginAtZero: true }
                    }
                }
            });
        })();

        // Category breakdown (bar)
        (function(){
            const ctx = document.getElementById('categoryChart');
            if(!ctx) return;
            const labels = @json($catLabels);
            const data = @json($catValues);
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels,
                    datasets: [{ label: 'Total', data }]
                },
                options: {
                    responsive: true,
                    plugins: { legend: { display: false } },
                    scales: { y: { beginAtZero: true } }
                }
            });
        })();
    </script>
</x-app-layout>
