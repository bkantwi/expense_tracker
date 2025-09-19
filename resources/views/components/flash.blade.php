@if(session('success'))
    <div class="mb-4 rounded-lg bg-green-50 text-green-800 px-4 py-3">
        {{ session('success') }}
    </div>
@endif

@if ($errors->any())
    <div class="mb-4 rounded-lg bg-red-50 text-red-800 px-4 py-3">
        <ul class="list-disc ml-5 text-sm">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
