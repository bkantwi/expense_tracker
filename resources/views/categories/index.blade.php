<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Categories') }}
            </h2>
            <a href="{{ route('categories.create') }}"
               class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">
                + {{ __('Create Category') }}
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <x-flash />

            <form method="GET" class="mb-4">
                <div class="flex gap-2">
                    <input name="q" value="{{ request('q') }}" placeholder="Search by name..."
                           class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500" />
                    <button class="px-4 py-2 rounded-lg border bg-white hover:bg-gray-50">Search</button>
                </div>
            </form>

            <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Description</th>
                        <th class="px-6 py-3"></th>
                    </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                    @forelse($categories as $category)
                        <tr>
                            <td class="px-6 py-4 font-medium text-gray-900">
                                <a href="{{ route('categories.show', $category) }}" class="hover:underline">
                                    {{ $category->name }}
                                </a>
                            </td>
                            <td class="px-6 py-4 text-gray-600">{{ Str::limit($category->description, 120) }}</td>
                            <td class="px-6 py-4 text-right">
                                <a href="{{ route('categories.edit', $category) }}"
                                   class="inline-flex items-center px-3 py-1.5 rounded-md border hover:bg-gray-50 mr-2">Edit</a>
                                <form action="{{ route('categories.destroy', $category) }}" method="POST" class="inline"
                                      onsubmit="return confirm('Delete this category?');">
                                    @csrf @method('DELETE')
                                    <button class="inline-flex items-center px-3 py-1.5 rounded-md bg-red-600 text-white hover:bg-red-700">
                                        Delete
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="px-6 py-16 text-center text-gray-600">
                                No categories yet. Create your first one!
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $categories->links() }}
            </div>
        </div>
    </div>
</x-app-layout>
