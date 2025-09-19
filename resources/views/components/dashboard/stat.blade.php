{{--<div>--}}
    <!-- Simplicity is an acquired taste. - Katharine Gerould -->
{{--</div>--}}

@props(['title','value','prefix'=>null,'icon'=>null])
<div class="bg-white rounded-lg shadow-sm border border-gray-100 p-5">
    <div class="flex items-center justify-between">
        <div>
            <p class="text-sm text-gray-500">{{ $title }}</p>
            <p class="mt-1 text-2xl font-semibold text-gray-900">
                {{ $prefix ? $prefix : '' }}{{ $value }}
            </p>
        </div>
        @if($icon)
            <div class="w-10 h-10 rounded-full bg-indigo-50 flex items-center justify-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-indigo-600" viewBox="0 0 24 24" fill="currentColor">
                    <path d="{{ $icon }}"/>
                </svg>
            </div>
        @endif
    </div>
</div>

