<?php $activeMenu = $activeMenu ?? 'dashboard'; ?>
<header x-data="{ mobileMenuOpen: false, dropdownOpen: false }" class="border-b border-zinc-200 dark:border-zinc-800 bg-white/80 dark:bg-zinc-950/80 backdrop-blur-md sticky top-0 z-50 transition-colors">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
            <!-- Left side: Brand Logo & Navigation -->
            <div class="flex items-center gap-8">
                <!-- Logo -->
                <a href="/" class="flex items-center gap-3 hover:opacity-95 transition-opacity group">
                    <div class="relative flex items-center justify-center transition-all duration-300">
                        <img src="{{ asset('logo.png') }}" alt="Logo" class="dark:hidden relative w-14 h-14 md:w-16 md:h-16 object-contain z-10 transition-transform duration-300 group-hover:scale-105">
                        <img src="{{ asset('logob.png') }}" alt="Logo" class="hidden dark:block relative w-14 h-14 md:w-16 md:h-16 object-contain z-10 transition-transform duration-300 group-hover:scale-105">
                    </div>
                    <span class="font-display font-semibold text-lg tracking-tight text-zinc-900 dark:text-zinc-100 group-hover:text-red-600 dark:group-hover:text-red-300 transition-colors hidden sm:inline">Technical Ticket Network</span>
                </a>
                
                <!-- Desktop Navigation Links (hidden on mobile) -->
                <nav class="hidden md:flex items-center gap-1.5 border-l border-zinc-200 dark:border-zinc-800 pl-6 h-8">
                    <a href="/" class="text-sm font-semibold px-3 py-1.5 rounded-lg transition-colors {{ $activeMenu == 'dashboard' ? 'text-red-600 dark:text-red-400 bg-red-50 dark:bg-red-500/10 shadow-sm' : 'text-zinc-600 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-zinc-200' }}">
                        Dashboard
                    </a>
                    <a href="/tickets" class="text-sm font-semibold px-3 py-1.5 rounded-lg transition-colors {{ $activeMenu == 'tickets' ? 'text-red-600 dark:text-red-400 bg-red-50 dark:bg-red-500/10 shadow-sm' : 'text-zinc-600 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-zinc-200' }}">
                        Tiket
                    </a>
                    @if (auth()->check() && (auth()->user()->hasRole('admin') || auth()->user()->hasRole('dest_manager')))
                        <a href="/users" class="text-sm font-semibold px-3 py-1.5 rounded-lg transition-colors {{ $activeMenu == 'users' ? 'text-red-600 dark:text-red-400 bg-red-50 dark:bg-red-500/10 shadow-sm' : 'text-zinc-600 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-zinc-200' }}">
                            Pengguna
                        </a>

                    @endif
                </nav>
            </div>
            
            <!-- Right side: Theme & Profile / Desktop Actions (hidden on mobile) -->
            <div class="hidden md:flex items-center gap-4">
                <!-- Theme Switcher -->
                <button @click="theme = (theme === 'dark' ? 'light' : 'dark'); localStorage.setItem('theme', theme); document.documentElement.className = theme;" 
                        class="w-9 h-9 rounded-xl border border-zinc-200 dark:border-zinc-800 bg-white/80 dark:bg-zinc-900/50 hover:bg-zinc-100 dark:hover:bg-zinc-800 text-zinc-600 dark:text-zinc-400 flex items-center justify-center transition-all cursor-pointer shadow-sm">
                    <svg x-show="theme === 'dark'" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4.5 h-4.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v2.25m0 13.5V21M4.93 4.93l1.59 1.59m10.96 10.96l1.59 1.59M3 12h2.25m13.5 0H21m-16.07 7.07l1.59-1.59M16.95 6.05l1.59-1.59M12 7.5a4.5 4.5 0 1 0 0 9 4.5 4.5 0 0 0 0-9Z" />
                    </svg>
                    <svg x-show="theme === 'light'" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4.5 h-4.5" style="display: none;">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21.752 15.002A9.72 9.72 0 0 1 18 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 0 0 3 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 0 0 9.002-5.998Z" />
                    </svg>
                </button>

                @auth
                <!-- User Profile Dropdown -->
                <div class="relative">
                    <button @click="dropdownOpen = !dropdownOpen" class="w-9 h-9 rounded-full bg-gradient-to-tr from-red-600 to-rose-500 hover:from-red-500 hover:to-rose-400 text-white flex items-center justify-center font-bold text-sm tracking-wider transition-all duration-300 focus:outline-none focus:ring-2 focus:ring-red-500/50 dark:focus:ring-offset-zinc-950 cursor-pointer shadow-md active:scale-95">
                        {{ collect(explode(' ', auth()->user()->name))->map(fn($w) => mb_substr($w, 0, 1))->take(2)->join('') }}
                    </button>
                    
                    <!-- Dropdown Menu -->
                    <div x-show="dropdownOpen" 
                         @click.away="dropdownOpen = false"
                         x-transition:enter="transition ease-out duration-150"
                         x-transition:enter-start="opacity-0 scale-95"
                         x-transition:enter-end="opacity-100 scale-100"
                         x-transition:leave="transition ease-in duration-100"
                         x-transition:leave-start="opacity-100 scale-100"
                         x-transition:leave-end="opacity-0 scale-95"
                         class="absolute right-0 mt-2.5 w-64 rounded-xl border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 shadow-xl z-50 overflow-hidden divide-y divide-zinc-100 dark:divide-zinc-800/50"
                         style="display: none;">
                        
                        <!-- User Info Header -->
                        <div class="px-4 py-3.5 bg-zinc-50/50 dark:bg-zinc-900/50 text-left">
                            <p class="text-xs font-semibold text-zinc-400 dark:text-zinc-500 uppercase tracking-wider">Masuk sebagai</p>
                            <p class="text-sm font-bold text-zinc-800 dark:text-zinc-100 truncate mt-0.5">{{ auth()->user()->name }}</p>
                            <p class="text-xs text-zinc-500 dark:text-zinc-400 truncate mt-0.5">{{ auth()->user()->email }}</p>
                            <div class="mt-2.5">
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-bold tracking-wider uppercase bg-red-100 dark:bg-red-500/10 text-red-700 dark:text-red-400 border border-red-200/50 dark:border-red-500/20">
                                    <span class="w-1.5 h-1.5 rounded-full bg-red-500 animate-pulse"></span>
                                    {{ str_replace('_', ' ', auth()->user()->role) }}
                                </span>
                            </div>
                        </div>
                        
                        <!-- Dropdown Options / Actions -->
                        <div class="p-1.5 border-b border-zinc-100 dark:border-zinc-800/50">
                            <form action="{{ route('logout') }}" method="POST" class="w-full">
                                @csrf
                                <button type="submit" class="w-full text-left flex items-center gap-2 px-3 py-2 text-xs font-semibold text-zinc-700 dark:text-zinc-300 hover:text-red-600 dark:hover:text-red-400 hover:bg-red-50 dark:hover:bg-red-500/10 rounded-lg transition-colors cursor-pointer">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4 text-zinc-400 dark:text-zinc-500 group-hover:text-current">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15M12 9l-3 3m0 0 3 3m-3-3h12.75" />
                                    </svg>
                                    Keluar
                                </button>
                            </form>
                        </div>
                        <button @click="$dispatch('open-changelog')" class="w-full px-3 py-2 text-[10px] text-zinc-400 dark:text-zinc-500 text-center font-medium hover:text-zinc-600 dark:hover:text-zinc-300 hover:bg-zinc-50 dark:hover:bg-zinc-800/50 transition-colors cursor-pointer rounded-b-lg">
                            Version 1.1.4
                        </button>
                    </div>
                </div>
                @endauth
            </div>

            <!-- Mobile Controls: Theme + Burger Toggle (hidden on desktop) -->
            <div class="flex md:hidden items-center gap-2">
                <!-- Theme Switcher (Mobile) -->
                <button @click="theme = (theme === 'dark' ? 'light' : 'dark'); localStorage.setItem('theme', theme); document.documentElement.className = theme;" 
                        class="w-9 h-9 rounded-xl border border-zinc-200 dark:border-zinc-800 bg-white/80 dark:bg-zinc-900/50 text-zinc-600 dark:text-zinc-400 flex items-center justify-center transition-all cursor-pointer shadow-sm">
                    <svg x-show="theme === 'dark'" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4.5 h-4.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v2.25m0 13.5V21M4.93 4.93l1.59 1.59m10.96 10.96l1.59 1.59M3 12h2.25m13.5 0H21m-16.07 7.07l1.59-1.59M16.95 6.05l1.59-1.59M12 7.5a4.5 4.5 0 1 0 0 9 4.5 4.5 0 0 0 0-9Z" />
                    </svg>
                    <svg x-show="theme === 'light'" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4.5 h-4.5" style="display: none;">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21.752 15.002A9.72 9.72 0 0 1 18 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 0 0 3 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 0 0 9.002-5.998Z" />
                    </svg>
                </button>

                <!-- Burger Menu Toggle Button -->
                <button @click="mobileMenuOpen = !mobileMenuOpen" 
                        class="w-9 h-9 rounded-xl border border-zinc-200 dark:border-zinc-800 bg-white/80 dark:bg-zinc-900/50 text-zinc-600 dark:text-zinc-400 flex items-center justify-center transition-all cursor-pointer shadow-sm">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
                        <path x-show="!mobileMenuOpen" stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                        <path x-show="mobileMenuOpen" stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" style="display: none;" />
                    </svg>
                </button>
            </div>
        </div>

        <!-- Mobile Drawer/Menu (hidden on desktop) -->
        <div x-show="mobileMenuOpen" 
             x-transition:enter="transition ease-out duration-150"
             x-transition:enter-start="opacity-0 -translate-y-2"
             x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-100"
             x-transition:leave-start="opacity-100 translate-y-0"
             x-transition:leave-end="opacity-0 -translate-y-2"
             class="md:hidden border-t border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-950 px-4 py-4 space-y-4 shadow-lg"
             style="display: none;">
            <!-- Navigation Tabs Stacked -->
            <nav class="flex flex-col gap-1">
                <a href="/" class="text-sm font-semibold px-4 py-2.5 rounded-xl transition-colors {{ $activeMenu == 'dashboard' ? 'text-red-600 dark:text-red-400 bg-red-50 dark:bg-red-500/10' : 'text-zinc-600 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-zinc-200' }}">
                    Dashboard
                </a>
                <a href="/tickets" class="text-sm font-semibold px-4 py-2.5 rounded-xl transition-colors {{ $activeMenu == 'tickets' ? 'text-red-600 dark:text-red-400 bg-red-50 dark:bg-red-500/10' : 'text-zinc-600 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-zinc-200' }}">
                    Tiket
                </a>
                @if (auth()->check() && (auth()->user()->hasRole('admin') || auth()->user()->hasRole('dest_manager')))
                    <a href="/users" class="text-sm font-semibold px-4 py-2.5 rounded-xl transition-colors {{ $activeMenu == 'users' ? 'text-red-600 dark:text-red-400 bg-red-50 dark:bg-red-500/10' : 'text-zinc-600 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-zinc-200' }}">
                        Pengguna
                    </a>

                @endif
            </nav>

            @auth
            <!-- Profile Info & Logout -->
            <div class="border-t border-zinc-200 dark:border-zinc-800 pt-4 flex items-center justify-between">
                <div class="flex flex-col">
                    <span class="text-sm font-bold text-zinc-900 dark:text-zinc-100">{{ auth()->user()->name }}</span>
                    <span class="text-xs text-zinc-500 dark:text-zinc-400 font-semibold capitalize">{{ str_replace('_', ' ', auth()->user()->role) }}</span>
                </div>
                <form action="{{ route('logout') }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" class="bg-white hover:bg-zinc-100 dark:bg-zinc-900 dark:hover:bg-zinc-800 text-xs font-semibold text-zinc-700 dark:text-zinc-300 px-3.5 py-2 rounded-lg border border-zinc-200 dark:border-zinc-800 shadow-sm">
                        Keluar
                    </button>
                </form>
            </div>
            @endauth
            <button @click="$dispatch('open-changelog')" class="mt-4 w-full text-center text-[10px] text-zinc-400 dark:text-zinc-500 font-medium hover:text-zinc-600 dark:hover:text-zinc-300 transition-colors py-1">
                Version 1.1.4
            </button>
        </div>
    </header>