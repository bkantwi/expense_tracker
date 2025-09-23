<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Preview Import') }}</h2>
            <a href="{{ route('expenses.import.index') }}" class="text-indigo-600 hover:text-indigo-800">Choose another file</a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <form method="POST" action="{{ route('expenses.import.commit') }}" class="bg-white rounded-lg shadow-sm p-6">
                @csrf

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Date format</label>
                        <select name="date_format" class="mt-1 w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                            @foreach($dateFormats as $fmt)
                                <option value="{{ $fmt }}">{{ $fmt }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex items-end">
                        <label class="inline-flex items-center">
                            <input type="checkbox" name="create_categories" value="1" checked
                                   class="rounded border-gray-300 text-indigo-600">
                            <span class="ml-2 text-gray-700">Create missing categories</span>
                        </label>
                    </div>
                    <div class="flex items-end">
                        <label class="inline-flex items-center">
                            <input type="checkbox" name="invert_sign" value="1"
                                   class="rounded border-gray-300 text-indigo-600">
                            <span class="ml-2 text-gray-700">Amounts are negative for expenses</span>
                        </label>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead>
                        <tr class="bg-gray-50">
                            @foreach($header as $i => $label)
                                <th class="px-3 py-2 text-left font-semibold text-gray-700">
                                    <div class="mb-1">{{ $label }}</div>
                                    <select name="map[{{ $thisIndex = $i }}]" class="w-40 rounded border-gray-300">
                                        <option value="">— ignore —</option>
                                    </select>
                                </th>
                            @endforeach
                        </tr>
                        <tr>
                            @foreach($header as $i => $label)
                                <th class="px-3 pb-2 text-left">
                                    <select name="map_fixed[{{ $i }}]" class="hidden"></select>
                                </th>
                            @endforeach
                        </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                        <tr class="bg-white">
                            @foreach($header as $i => $label)
                                <td class="px-3 py-2">
                                    {{-- mapping selects (real ones, with defaults) --}}
                                    @php
                                        $role = null;
                                        if(isset($guess['date']) && $guess['date'] === $i)   $role = 'date';
                                        if(isset($guess['title']) && $guess['title'] === $i) $role = 'title';
                                        if(isset($guess['amount']) && $guess['amount'] === $i)$role = 'amount';
                                        if(isset($guess['category']) && $guess['category'] === $i)$role = 'category';
                                        if(isset($guess['notes']) && $guess['notes'] === $i) $role = 'notes';
                                    @endphp
                                    <select name="map_role[{{ $i }}]" class="w-40 rounded border-gray-300">
                                        <option value="">— map as —</option>
                                        <option value="date"     @selected($role==='date')>Date</option>
                                        <option value="title"    @selected($role==='title')>Title</option>
                                        <option value="amount"   @selected($role==='amount')>Amount</option>
                                        <option value="category" @selected($role==='category')>Category</option>
                                        <option value="notes"    @selected($role==='notes')>Notes</option>
                                    </select>
                                </td>
                            @endforeach
                        </tr>

                        @foreach($rows as $r)
                            <tr class="bg-white">
                                @foreach($header as $i => $label)
                                    <td class="px-3 py-2 whitespace-nowrap text-gray-700">
                                        {{ $r[$i] ?? '' }}
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Hidden canonical map inputs (filled by JS from map_role[]) --}}
                <input type="hidden" name="map[date]"     id="map-date">
                <input type="hidden" name="map[title]"    id="map-title">
                <input type="hidden" name="map[amount]"   id="map-amount">
                <input type="hidden" name="map[category]" id="map-category">
                <input type="hidden" name="map[notes]"    id="map-notes">

                <div class="mt-6 flex items-center gap-3">
                    <button class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700"
                            onclick="return buildMapBeforeSubmit();">
                        Import
                    </button>
                    <a href="{{ route('expenses.import.index') }}" class="px-4 py-2 rounded-lg border hover:bg-gray-50">Cancel</a>
                </div>
            </form>
        </div>
    </div>

    <script>
        function buildMapBeforeSubmit() {
            // Find selected role per column and fill hidden canonical inputs with column indices
            const selects = document.querySelectorAll('select[name^="map_role["]');
            const roleToIndex = {};
            selects.forEach((sel) => {
                const m = sel.name.match(/map_role\[(\d+)\]/);
                if (!m) return;
                const idx = parseInt(m[1], 10);
                const role = sel.value;
                if (role) roleToIndex[role] = idx;
            });

            document.getElementById('map-date').value     = roleToIndex['date'] ?? '';
            document.getElementById('map-title').value    = roleToIndex['title'] ?? '';
            document.getElementById('map-amount').value   = roleToIndex['amount'] ?? '';
            document.getElementById('map-category').value = roleToIndex['category'] ?? '';
            document.getElementById('map-notes').value    = roleToIndex['notes'] ?? '';

            if (!document.getElementById('map-date').value ||
                !document.getElementById('map-title').value ||
                !document.getElementById('map-amount').value) {
                alert('Please map Date, Title and Amount.');
                return false;
            }
            return true;
        }
    </script>
</x-app-layout>
