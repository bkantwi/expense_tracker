{{-- resources/views/expenses/edit.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Edit Expense') }}</h2>
            <a href="{{ route('expenses.index') }}" class="text-indigo-600 hover:text-indigo-800">Back to list</a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <x-flash />

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
                {{-- Edit form --}}
                <div class="lg:col-span-7">
                    <div class="bg-white rounded-lg shadow-sm p-6">
                        <form method="POST" action="{{ route('expenses.update', $expense) }}" class="space-y-4">
                            @csrf
                            @method('PUT')

                            <div>
                                <label class="block text-sm font-medium text-gray-700">Title</label>
                                <input name="title"
                                       value="{{ old('title', $expense->title) }}"
                                       required
                                       class="mt-1 w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500" />
                                @error('title')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Amount (GHS)</label>
                                    <input type="number" step="0.01" min="0.01" name="amount"
                                           value="{{ old('amount', $expense->amount) }}" required
                                           class="mt-1 w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500" />
                                    @error('amount')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Date</label>
                                    <input type="date" name="spent_at"
                                           value="{{ old('spent_at', $expense->spent_at->toDateString()) }}" required
                                           class="mt-1 w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500" />
                                    @error('spent_at')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Category</label>
                                    <select name="category_id" required
                                            class="mt-1 w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                                        @foreach($categories as $c)
                                            <option value="{{ $c->id }}" @selected(old('category_id', $expense->category_id) == $c->id)>
                                                {{ $c->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('category_id')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Account</label>
                                    <select name="account_id" required
                                            class="mt-1 w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                                        @foreach($accounts as $a)
                                            <option value="{{ $a->id }}" @selected(old('account_id', $expense->account_id) == $a->id)>
                                                {{ $a->name }} ({{ $a->currency }})
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('account_id')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">Notes (optional)</label>
                                <textarea name="notes" rows="4"
                                          class="mt-1 w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">{{ old('notes', $expense->notes) }}</textarea>
                                @error('notes')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="flex items-center gap-3 pt-2">
                                <button class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">Update</button>
                                <a href="{{ route('expenses.index') }}" class="px-4 py-2 rounded-lg border hover:bg-gray-50">Cancel</a>
                            </div>
                        </form>
                    </div>
                </div>

                {{-- Receipts upload + list --}}
                <div class="lg:col-span-5">
                    <div class="bg-white rounded-lg shadow-sm p-6 space-y-4">
                        <h3 class="text-lg font-semibold text-gray-800">Receipts</h3>

                        {{-- Upload form (separate; no nesting) --}}
                        <form method="POST" action="{{ route('expenses.attachments.store', $expense) }}"
                              enctype="multipart/form-data" class="space-y-2">
                            @csrf
                            <input type="file" name="files[]" multiple
                                   class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500" />
                            <p class="text-xs text-gray-500">Accepted: JPG, PNG, WEBP, PDF. Max 5MB each.</p>
                            @error('files.*')
                            <p class="text-sm text-red-600">{{ $message }}</p>
                            @enderror

                            <button class="px-3 py-1.5 bg-indigo-600 text-white rounded hover:bg-indigo-700">
                                Upload
                            </button>
                        </form>

                        {{-- Existing attachments --}}
                        <div class="grid grid-cols-1 gap-3">
                            @forelse($expense->attachments as $att)
                                <div class="border rounded-lg p-3 flex items-start gap-3">
                                    @if(\Illuminate\Support\Str::startsWith($att->mime_type, 'image/'))
                                        <img src="{{ asset('storage/'.$att->path) }}" alt=""
                                             class="w-16 h-16 object-cover rounded">
                                    @else
                                        <div class="w-16 h-16 flex items-center justify-center bg-gray-100 rounded text-sm text-gray-700">
                                            PDF
                                        </div>
                                    @endif

                                    <div class="flex-1 min-w-0">
                                        <div class="font-medium truncate">{{ $att->original_name }}</div>
                                        <div class="text-xs text-gray-500">
                                            {{ number_format($att->size / 1024, 1) }} KB
                                        </div>

                                        <div class="mt-2 flex items-center gap-3">
                                            <a class="text-indigo-600 hover:text-indigo-800 text-sm"
                                               href="{{ route('attachments.download', $att) }}">Download</a>

                                            <form action="{{ route('attachments.destroy', $att) }}" method="POST"
                                                  onsubmit="return confirm('Delete this attachment?')">
                                                @csrf
                                                @method('DELETE')
                                                <button class="text-red-600 hover:text-red-800 text-sm">Delete</button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="text-sm text-gray-600">No receipts yet.</div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div> {{-- /grid --}}
        </div>
    </div>
</x-app-layout>
