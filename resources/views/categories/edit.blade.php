<x-flash />

<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Edit Category') }}
            </h2>
            <a href="{{ route('categories.index') }}" class="text-indigo-600 hover:text-indigo-800">Back to list</a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            <x-flash />

            <form method="POST" action="{{ route('categories.update', $category) }}" class="bg-white rounded-lg shadow-sm p-6 space-y-4">
                @csrf @method('PUT')

                <div>
                    <label class="block text-sm font-medium text-gray-700">Name</label>
                    <input name="name" value="{{ old('name', $category->name) }}" required
                           class="mt-1 w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500" />
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Description (optional)</label>
                    <textarea name="description" rows="4"
                              class="mt-1 w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">{{ old('description', $category->description) }}</textarea>
                </div>

                <div class="flex items-center gap-3">
                    <button class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">Update</button>
                    <a href="{{ route('categories.index') }}" class="px-4 py-2 rounded-lg border hover:bg-gray-50">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
