<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Recurring Expenses</h2>
            <a href="{{ route('recurrences.create') }}" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">+ New</a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <x-flash />

            <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-50 text-gray-600">
                    <tr>
                        <th class="px-4 py-2 text-left">Title</th>
                        <th class="px-4 py-2 text-left">Category</th>
                        <th class="px-4 py-2 text-right">Amount</th>
                        <th class="px-4 py-2 text-left">Cadence</th>
                        <th class="px-4 py-2 text-left">Next run</th>
                        <th class="px-4 py-2 text-left">Active</th>
                        <th class="px-4 py-2"></th>
                    </tr>
                    </thead>
                    <tbody class="divide-y">
                    @forelse($recs as $r)
                        <tr>
                            <td class="px-4 py-2">{{ $r->title }}</td>
                            <td class="px-4 py-2">{{ $r->category->name ?? '—' }}</td>
                            <td class="px-4 py-2 text-right">₵{{ number_format($r->amount,2) }}</td>
                            <td class="px-4 py-2 capitalize">{{ $r->cadence }}{{ $r->interval > 1 ? ' x'.$r->interval : '' }}</td>
                            <td class="px-4 py-2">{{ optional($r->next_run_on)->toDateString() }}</td>
                            <td class="px-4 py-2">{{ $r->active ? 'Yes' : 'No' }}</td>
                            <td class="px-4 py-2 text-right">
                                <a href="{{ route('recurrences.edit', $r) }}" class="px-3 py-1.5 rounded border hover:bg-gray-50">Edit</a>
                                <form action="{{ route('recurrences.destroy', $r) }}" method="POST" class="inline" onsubmit="return confirm('Delete this recurring expense?')">
                                    @csrf @method('DELETE')
                                    <button class="px-3 py-1.5 rounded bg-red-600 text-white hover:bg-red-700">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td class="px-4 py-8 text-center text-gray-600" colspan="7">No recurring expenses yet.</td></tr>
                    @endforelse
                    </tbody>
                </table>
                <div class="p-4">{{ $recs->links() }}</div>
            </div>
        </div>
    </div>
</x-app-layout>
