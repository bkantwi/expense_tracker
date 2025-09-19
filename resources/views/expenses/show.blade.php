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

    <!-- Upload more -->
    <form method="POST" action="{{ route('expenses.attachments.store', $expense) }}" enctype="multipart/form-data" class="space-y-2">
        @csrf
        <input type="file" name="files[]" multiple class="...">
        <button class="px-3 py-1.5 bg-indigo-600 text-white rounded hover:bg-indigo-700">Upload</button>
    </form>

    <!-- Existing attachments -->
    <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-3">
        @forelse($expense->attachments as $att)
            <div class="border rounded-lg p-3 flex items-start gap-3">
                @if(Str::startsWith($att->mime_type, 'image/'))
                    <img src="{{ asset('storage/'.$att->path) }}" alt="" class="w-16 h-16 object-cover rounded">
                @else
                    <div class="w-16 h-16 flex items-center justify-center bg-gray-100 rounded">PDF</div>
                @endif

                <div class="flex-1 min-w-0">
                    <div class="font-medium truncate">{{ $att->original_name }}</div>
                    <div class="text-xs text-gray-500">{{ number_format($att->size/1024,1) }} KB</div>
                    <div class="mt-2 flex items-center gap-2">
                        <a class="text-indigo-600 hover:text-indigo-800 text-sm"
                           href="{{ route('attachments.download', $att) }}">Download</a>

                        <form action="{{ route('attachments.destroy', $att) }}" method="POST" onsubmit="return confirm('Delete this attachment?')">
                            @csrf @method('DELETE')
                            <button class="text-red-600 hover:text-red-800 text-sm">Delete</button>
                        </form>
                    </div>
                </div>
            </div>
        @empty
            <div class="text-sm text-gray-600">No receipts yet.</div>
        @endforelse
    </div>
</x-app-layout>
