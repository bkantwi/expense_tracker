<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Import Expenses (CSV)') }}</h2>
            <a href="{{ route('expenses.index') }}" class="text-indigo-600 hover:text-indigo-800">Back to expenses</a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            <x-flash />

            <form method="POST" action="{{ route('expenses.import.preview') }}" enctype="multipart/form-data"
                  class="bg-white rounded-lg shadow-sm p-6 space-y-4">
                @csrf

                <div>
                    <label class="block text-sm font-medium text-gray-700">CSV file</label>
                    <input type="file" name="file" accept=".csv,text/csv"
                           class="mt-1 w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500" required>
                    @error('file') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Delimiter</label>
                        <select name="delimiter" class="mt-1 w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                            <option value=",">Comma</option>
                            <option value=";">Semicolon</option>
                            <option value="tab">Tab</option>
                        </select>
                    </div>
                    <div class="md:col-span-2 flex items-center mt-6">
                        <label class="inline-flex items-center">
                            <input type="checkbox" name="has_header" value="1" checked
                                   class="rounded border-gray-300 text-indigo-600">
                            <span class="ml-2 text-gray-700">First row contains headers</span>
                        </label>
                    </div>
                </div>

                <div class="pt-2">
                    <button class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">
                        Preview
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
