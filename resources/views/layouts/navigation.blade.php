<nav x-data="{ open: false }" class="bg-white border-b border-gray-100">
    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('dashboard') }}">
                        <x-application-logo class="block h-9 w-auto fill-current text-gray-800" />
                    </a>
                </div>

                <!-- Navigation Links -->
                <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                    <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                        {{ __('Dashboard') }}
                    </x-nav-link>
                </div>
                <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                    <x-nav-link :href="route('reports.index')" :active="request()->routeIs('reports.index')">
                        {{ __('Reports') }}
                    </x-nav-link>
                </div>
            </div>

            @auth
                @php
                    $unreadCount = auth()->user()->unreadNotifications()->count();
                    $recent = auth()->user()->notifications()->limit(10)->get();
                @endphp
                <div class="relative">
                    <button type="button" class="relative rounded-full p-2 hover:bg-gray-100" onclick="document.getElementById('notif-dd').classList.toggle('hidden')">
                        <!-- bell icon -->
                        <svg class="w-6 h-6 text-gray-600" viewBox="0 0 20 20" fill="currentColor"><path d="M10 2a6 6 0 00-6 6v3H3a1 1 0 000 2h14a1 1 0 000-2h-1V8a6 6 0 00-6-6zM7 16a3 3 0 006 0H7z"/></svg>
                        @if($unreadCount>0)
                            <span class="absolute -top-1 -right-1 bg-red-600 text-white text-xs rounded-full px-1.5 py-0.5">{{ $unreadCount }}</span>
                        @endif
                    </button>
                    <div id="notif-dd" class="hidden absolute right-0 mt-2 w-80 bg-white shadow-lg rounded-lg border">
                        <div class="px-4 py-2 font-semibold border-b">Notifications</div>
                        <div class="max-h-80 overflow-auto">
                            @forelse($recent as $n)
                                <div class="px-4 py-3 border-b text-sm {{ $n->read() ? 'text-gray-600' : 'text-gray-900' }}">
                                    @php $d = $n->data; @endphp
                                    @if(($d['level'] ?? '') === 'over')
                                        <span class="text-red-600 font-medium">Over budget</span>
                                    @elseif(($d['level'] ?? '') === 'at')
                                        <span class="text-orange-600 font-medium">At budget</span>
                                    @else
                                        <span class="text-yellow-600 font-medium">Warning</span>
                                    @endif
                                    — {{ $d['category'] ?? 'Budget' }} ({{ \Illuminate\Support\Str::of($d['period'] ?? '')->substr(0,7) }})
                                    <div class="text-xs text-gray-600">
                                        Spent ₵{{ number_format($d['spent'] ?? 0,2) }} of ₵{{ number_format($d['amount'] ?? 0,2) }} ({{ $d['percent'] ?? 0 }}%)
                                    </div>
                                </div>
                            @empty
                                <div class="px-4 py-6 text-sm text-gray-600">No notifications yet.</div>
                            @endforelse
                        </div>
                    </div>
                </div>
            @endauth

            <!-- Settings Dropdown -->
            <div class="hidden sm:flex sm:items-center sm:ms-6">
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none transition ease-in-out duration-150">
                            <div>{{ Auth::user()->name }}</div>

                            <div class="ms-1">
                                <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <x-dropdown-link :href="route('profile.edit')">
                            {{ __('Profile') }}
                        </x-dropdown-link>

                        <!-- Authentication -->
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf

                            <x-dropdown-link :href="route('logout')"
                                    onclick="event.preventDefault();
                                                this.closest('form').submit();">
                                {{ __('Log Out') }}
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

            <!-- Hamburger -->
            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-500 transition duration-150 ease-in-out">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden">
        <div class="pt-2 pb-3 space-y-1">
            <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                {{ __('Dashboard') }}
            </x-responsive-nav-link>
        </div>
        <div class="pt-2 pb-3 space-y-1">
            <x-responsive-nav-link :href="route('reports.index')" :active="request()->routeIs('reports.index')">
                {{ __('Reports') }}
            </x-responsive-nav-link>
        </div>

        <!-- Responsive Settings Options -->
        <div class="pt-4 pb-1 border-t border-gray-200">
            <div class="px-4">
                <div class="font-medium text-base text-gray-800">{{ Auth::user()->name }}</div>
                <div class="font-medium text-sm text-gray-500">{{ Auth::user()->email }}</div>
            </div>

            <div class="mt-3 space-y-1">
                <x-responsive-nav-link :href="route('profile.edit')">
                    {{ __('Profile') }}
                </x-responsive-nav-link>

                <!-- Authentication -->
                <form method="POST" action="{{ route('logout') }}">
                    @csrf

                    <x-responsive-nav-link :href="route('logout')"
                            onclick="event.preventDefault();
                                        this.closest('form').submit();">
                        {{ __('Log Out') }}
                    </x-responsive-nav-link>
                </form>
            </div>
        </div>
    </div>
</nav>
