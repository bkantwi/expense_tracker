<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800">Accounts</h2>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            {{-- Create form --}}
            <form action="{{ route('accounts.store') }}" method="POST" class="mb-6 bg-white p-4 rounded-lg border">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-5 gap-3">
                    <input name="name" placeholder="Name (e.g. Cash)" class="rounded-lg border-gray-300" value="{{ old('name') }}">
                    <select name="type" class="rounded-lg border-gray-300">
                        <option value="cash">Cash</option>
                        <option value="momo">MoMo</option>
                        <option value="bank">Bank</option>
                        <option value="card">Card</option>
                    </select>
                    <input name="currency" value="GHS" class="rounded-lg border-gray-300">
                    <input name="starting_balance" placeholder="Starting balance" type="number" step="0.01" class="rounded-lg border-gray-300">
                    <button class="px-4 py-2 bg-indigo-600 text-white rounded-lg">Add Account</button>
                </div>
                <x-input-error :messages="$errors->all()" class="mt-2" />
            </form>

            {{-- Cards --}}
            {{-- Cards --}}
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($accounts as $a)
                    @php
                        $typeIcon = [
                            'cash' => '<svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor"><path d="M3 7a2 2 0 012-2h14a2 2 0 012 2v7a2 2 0 01-2 2H5a2 2 0 01-2-2V7zm3 0h10a3 3 0 003 3v1a3 3 0 00-3 3H6a3 3 0 00-3-3v-1a3 3 0 003-3z"/></svg>',
                            'momo'=> '<svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor"><path d="M7 3a2 2 0 00-2 2v14a2 2 0 002 2h8a2 2 0 002-2V8l-5-5H7zM9 13h6v2H9v-2zm0-4h4v2H9V9z"/></svg>',
                            'bank'=> '<svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor"><path d="M12 3l9 5v2H3V8l9-5zm-9 8h18v8H3v-8zm2 2v4h2v-4H5zm4 0v4h2v-4H9zm4 0v4h2v-4h-2zm4 0v4h2v-4h-2z"/></svg>',
                            'card'=> '<svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor"><path d="M3 6a2 2 0 012-2h14a2 2 0 012 2v2H3V6zm0 4h18v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6zm3 3h4v2H6v-2z"/></svg>',
                        ][$a->type] ?? '';
                    @endphp

                    <div class="group bg-white p-5 rounded-2xl border border-gray-200 shadow-sm hover:shadow-md hover:border-indigo-200 transition-all">
                        {{-- Header --}}
                        <div class="flex items-start justify-between">
                            <div class="flex items-center gap-2">
                    <span class="inline-flex items-center justify-center h-9 w-9 rounded-xl bg-gray-100 text-gray-700">
                        {!! $typeIcon !!}
                    </span>
                                <div>
                                    <div class="font-semibold text-gray-900 leading-tight">{{ $a->name }}</div>
                                    <div class="text-xs text-gray-500 tracking-wide">{{ strtoupper($a->type) }}</div>
                                </div>
                            </div>

                            <div class="flex items-center gap-2">
                                <span class="text-xs px-2 py-1 rounded-full bg-gray-100 text-gray-700">{{ $a->currency }}</span>
                                @if($a->archived)
                                    <span class="text-xs px-2 py-1 rounded-full bg-yellow-100 text-yellow-800">Archived</span>
                                @endif
                            </div>
                        </div>

                        {{-- Balance --}}
                        <div class="mt-4">
                            <div class="text-3xl font-extrabold text-gray-900">
                                ₵{{ number_format($a->balance(), 2) }}
                            </div>
                            <div class="text-xs text-gray-500">Current balance</div>
                        </div>

                        {{-- Inline edit --}}
                        <form action="{{ route('accounts.update', $a) }}" method="POST" class="mt-5 grid grid-cols-1 sm:grid-cols-3 gap-3">
                            @csrf @method('PATCH')
                            <input
                                name="name"
                                value="{{ $a->name }}"
                                class="sm:col-span-2 rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
                                placeholder="Account name"
                            />
                            <label class="inline-flex items-center justify-start text-sm text-gray-700">
                                <input type="checkbox" name="archived" value="1" @checked($a->archived)
                                class="mr-2 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                Archived
                            </label>
                            <div class="sm:col-span-3 flex gap-2">
                                <button class="inline-flex items-center px-3 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50">
                                    Save
                                </button>
                                <form action="{{ route('accounts.destroy', $a) }}" method="POST" onsubmit="return confirm('Delete this account? Only if it has no activity.')">
                                    @csrf @method('DELETE')
                                    <button class="inline-flex items-center px-3 py-2 rounded-lg bg-red-600 text-white hover:bg-red-700">
                                        Delete
                                    </button>
                                </form>
                            </div>
                        </form>
                    </div>
                @endforeach
            </div>

            {{-- Quick transfer --}}
            <div class="mt-8 bg-white p-5 rounded-lg border">
                <h3 class="font-semibold mb-3">Quick Transfer</h3>
                <form action="{{ route('transfers.store') }}" method="POST" class="grid grid-cols-1 md:grid-cols-5 gap-3">
                    @csrf
                    <select name="from_account_id" class="rounded-lg border-gray-300">
                        <option value="">From</option>
                        @foreach($accounts as $a)
                            <option value="{{ $a->id }}">{{ $a->name }} ({{ $a->currency }})</option>
                        @endforeach
                    </select>
                    <select name="to_account_id" class="rounded-lg border-gray-300">
                        <option value="">To</option>
                        @foreach($accounts as $a)
                            <option value="{{ $a->id }}">{{ $a->name }} ({{ $a->currency }})</option>
                        @endforeach
                    </select>
                    <input type="date" name="transferred_at" value="{{ now()->toDateString() }}" class="rounded-lg border-gray-300">
                    <input type="number" step="0.01" name="amount" placeholder="Amount" class="rounded-lg border-gray-300">
                    <input type="text" name="memo" placeholder="Memo (optional)" class="rounded-lg border-gray-300 md:col-span-2">
                    <button class="px-4 py-2 bg-indigo-600 text-white rounded-lg md:col-span-3">Record Transfer</button>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
