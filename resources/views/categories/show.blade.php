<x-show />

<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ $category->name }}
            </h2>
            <a href="{{ route('categories.index') }}" class="text-indigo-600 hover:text-indigo-800">Back to list</a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white rounded-lg shadow-sm p-6">
                <p class="text-gray-600">{{ $category->description ?: 'No description.' }}</p>
            </div>
        </div>
    </div>
</x-app-layout>
