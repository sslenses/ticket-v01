<!DOCTYPE html>
<html lang="en" x-data="{ theme: localStorage.getItem('theme') || 'dark' }" :class="theme" class="h-full antialiased">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management - Ticketing System</title>
    
    <!-- Google Fonts: Inter & Outfit -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Outfit:wght@500;600;700&display=swap" rel="stylesheet">
    
    <!-- Alpine.js CDN -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <!-- Tailwind CSS Browser CDN -->
    <script src="https://unpkg.com/@tailwindcss/browser@4"></script>
    
    <script>
        if (localStorage.getItem('theme') === 'light') {
            document.documentElement.classList.add('light');
            document.documentElement.classList.remove('dark');
        } else {
            document.documentElement.classList.add('dark');
            document.documentElement.classList.remove('light');
        }
    </script>
    
    <style type="text/tailwindcss">
        @custom-variant dark (&:where(.dark, .dark *));
        body {
            font-family: 'Inter', sans-serif;
        }
        h1, h2, h3, .font-display {
            font-family: 'Outfit', sans-serif;
        }
        @keyframes signal-flow {
            to {
                stroke-dashoffset: -20;
            }
        }
    </style>
</head>
<body class="min-h-screen flex flex-col bg-zinc-50 dark:bg-zinc-950 text-zinc-900 dark:text-zinc-50 transition-colors duration-300">

    <!-- Top Navigation -->
    <header class="border-b border-zinc-200 dark:border-zinc-800 bg-white/50 dark:bg-zinc-950/50 backdrop-blur-md sticky top-0 z-50 transition-colors">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
            <a href="/" class="flex items-center gap-3 hover:opacity-95 transition-opacity group">
                <div class="relative w-9 h-9 rounded-xl bg-white dark:bg-zinc-950 border border-zinc-200 dark:border-zinc-800 flex items-center justify-center shadow-md group-hover:border-violet-500/50 transition-all duration-300">
                    <!-- Glow background -->
                    <div class="absolute inset-0 bg-gradient-to-tr from-violet-600/10 to-indigo-600/10 dark:from-violet-600/20 dark:to-indigo-600/20 rounded-xl blur-sm opacity-50 group-hover:opacity-100 group-hover:blur-md transition-all duration-300"></div>
                    
                    <!-- Logo Graphic -->
                    <svg class="relative w-6 h-6 text-violet-600 dark:text-violet-400 group-hover:text-indigo-500 dark:group-hover:text-indigo-300 transition-colors duration-300" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <!-- Connecting Fiber Lines -->
                        <path d="M4 6h16" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" class="opacity-30" />
                        <path d="M12 6v12" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" class="opacity-30" />
                        
                        <!-- Animated Signal dashes -->
                        <path d="M4 6h16" stroke="#c084fc" stroke-width="1.5" stroke-linecap="round" class="signal-line" style="stroke-dasharray: 4, 12; animation: signal-flow 2s linear infinite;" />
                        <path d="M12 6v12" stroke="#c084fc" stroke-width="1.5" stroke-linecap="round" class="signal-line" style="stroke-dasharray: 4, 12; animation: signal-flow 2s linear infinite;" />

                        <!-- Network Node Circles -->
                        <circle cx="4" cy="6" r="2" fill="currentColor" />
                        <circle cx="20" cy="6" r="2" fill="currentColor" />
                        <circle cx="12" cy="18" r="2" fill="currentColor" />
                        
                        <!-- Glowing Pulsing Core -->
                        <circle cx="12" cy="6" r="3.5" fill="#818cf8" class="animate-ping opacity-75" />
                        <circle cx="12" cy="6" r="3.5" fill="#6366f1" />
                        <circle cx="12" cy="6" r="1.5" fill="#ffffff" />
                    </svg>
                </div>
                <span class="font-display font-semibold text-lg tracking-tight text-zinc-900 dark:text-zinc-100 group-hover:text-violet-600 dark:group-hover:text-violet-300 transition-colors">Ticketing System</span>
            </a>
            
            <!-- Header Actions & Logout -->
            <div class="flex items-center gap-4">
                <!-- Theme Switcher -->
                <button @click="theme = (theme === 'dark' ? 'light' : 'dark'); localStorage.setItem('theme', theme); document.documentElement.className = theme;" 
                        class="w-9 h-9 rounded-xl border border-zinc-200 dark:border-zinc-800 bg-white/80 dark:bg-zinc-900/50 hover:bg-zinc-105 dark:hover:bg-zinc-800 text-zinc-600 dark:text-zinc-400 flex items-center justify-center transition-all cursor-pointer shadow-sm">
                    <svg x-show="theme === 'dark'" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4.5 h-4.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v2.25m0 13.5V21M4.93 4.93l1.59 1.59m10.96 10.96l1.59 1.59M3 12h2.25m13.5 0H21m-16.07 7.07l1.59-1.59M16.95 6.05l1.59-1.59M12 7.5a4.5 4.5 0 1 0 0 9 4.5 4.5 0 0 0 0-9Z" />
                    </svg>
                    <svg x-show="theme === 'light'" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4.5 h-4.5" style="display: none;">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21.752 15.002A9.72 9.72 0 0 1 18 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 0 0 3 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 0 0 9.002-5.998Z" />
                    </svg>
                </button>

                <div class="hidden sm:flex flex-col items-end text-right">
                    <span class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">{{ auth()->user()->name }}</span>
                    <span class="text-xs text-zinc-550 dark:text-zinc-400 font-medium capitalize">{{ str_replace('_', ' ', auth()->user()->role) }}</span>
                </div>
                <form action="{{ route('logout') }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" class="bg-white hover:bg-zinc-50 dark:bg-zinc-800 dark:hover:bg-zinc-700 active:scale-95 text-xs font-semibold text-zinc-700 dark:text-zinc-200 px-3.5 py-2 rounded-xl transition-all border border-zinc-200 dark:border-zinc-700/50 cursor-pointer shadow-sm">
                        Logout
                    </button>
                </form>
            </div>
        </div>
    </header>

    <!-- Main Container -->
    <main class="flex-1 mx-auto max-w-7xl w-full px-4 sm:px-6 lg:px-8 py-8 flex flex-col gap-6">
        
        <!-- Back Navigation -->
        <div class="flex items-center">
            <a href="/" class="inline-flex items-center gap-1.5 text-xs font-semibold text-zinc-600 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-zinc-200 transition-colors bg-white dark:bg-zinc-900/40 border border-zinc-200 dark:border-zinc-800 px-3 py-1.5 rounded-lg shadow-sm">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-3.5 h-3.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5" />
                </svg>
                Back to Dashboard
            </a>
        </div>

        <!-- Header Title Section -->
        <div>
            <h1 class="text-3xl font-extrabold text-zinc-900 dark:text-white tracking-tight">User Management</h1>
            <p class="text-zinc-550 dark:text-zinc-500 text-sm mt-1">Manage and audit all registered accounts and system roles.</p>
        </div>

        <!-- User List Table Section -->
        <section class="rounded-2xl border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900/40 backdrop-blur-xl p-5 md:p-8 shadow-xl">
            <h2 class="text-xl font-bold mb-6 flex items-center gap-2">
                <span class="w-2 h-5 rounded bg-violet-600 inline-block"></span>
                Registered Users & System Roles
            </h2>

            <!-- Desktop View: Table Layout -->
            <div class="hidden md:block overflow-x-auto">
                <table class="w-full border-collapse text-left">
                    <thead>
                        <tr class="border-b border-zinc-200 dark:border-zinc-800 text-xs font-bold text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                            <th class="py-3 px-4">Name</th>
                            <th class="py-3 px-4">Email</th>
                            <th class="py-3 px-4">Testing Role</th>
                            <th class="py-3 px-4 text-right">Registered At</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-200 dark:divide-zinc-800/60 text-sm text-zinc-700 dark:text-zinc-300">
                        @foreach($users as $user)
                        <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/25 transition-colors duration-200">
                            <td class="py-4 px-4 font-bold text-zinc-900 dark:text-white select-all">
                                {{ $user->name }}
                            </td>
                            <td class="py-4 px-4 text-zinc-600 dark:text-zinc-400 font-mono">
                                {{ $user->email }}
                            </td>
                            <td class="py-4 px-4">
                                <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold border capitalize"
                                      :class="{
                                          'bg-violet-550/10 text-violet-600 dark:text-violet-400 border-violet-550/20': '{{ $user->role }}' === 'admin',
                                          'bg-cyan-550/10 text-cyan-600 dark:text-cyan-400 border-cyan-550/20': '{{ $user->role }}' === 'dest_manager',
                                          'bg-emerald-550/10 text-emerald-600 dark:text-emerald-400 border-emerald-550/20': '{{ $user->role }}' === 'staff',
                                          'bg-zinc-500/10 text-zinc-600 dark:text-zinc-400 border-zinc-500/20': '{{ $user->role }}' === 'user'
                                      }">
                                    {{ str_replace('_', ' ', $user->role) }}
                                </span>
                            </td>
                            <td class="py-4 px-4 text-right text-zinc-500 dark:text-zinc-550">
                                {{ $user->created_at->format('d M Y - H:i') }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Mobile View: Card List Layout -->
            <div class="block md:hidden space-y-4">
                @foreach($users as $user)
                    <div class="rounded-xl border border-zinc-200 dark:border-zinc-800 bg-zinc-50/50 dark:bg-zinc-900/30 p-4 shadow-sm flex flex-col gap-3">
                        <div class="flex items-center justify-between">
                            <span class="font-bold text-sm text-zinc-900 dark:text-white">{{ $user->name }}</span>
                            <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold border capitalize"
                                  :class="{
                                      'bg-violet-550/10 text-violet-600 dark:text-violet-400 border-violet-550/20': '{{ $user->role }}' === 'admin',
                                      'bg-cyan-550/10 text-cyan-600 dark:text-cyan-400 border-cyan-550/20': '{{ $user->role }}' === 'dest_manager',
                                      'bg-emerald-550/10 text-emerald-600 dark:text-emerald-400 border-emerald-550/20': '{{ $user->role }}' === 'staff',
                                      'bg-zinc-500/10 text-zinc-600 dark:text-zinc-400 border-zinc-500/20': '{{ $user->role }}' === 'user'
                                  }">
                                {{ str_replace('_', ' ', $user->role) }}
                            </span>
                        </div>
                        <div class="text-xs text-zinc-550 dark:text-zinc-400 flex flex-col gap-1.5 pt-2 border-t border-zinc-200/50 dark:border-zinc-800/50">
                            <div>
                                <span class="text-zinc-400 dark:text-zinc-500 uppercase font-semibold text-[9px] block">Email Address</span>
                                <span class="font-mono text-zinc-800 dark:text-zinc-300 text-xs">{{ $user->email }}</span>
                            </div>
                            <div>
                                <span class="text-zinc-400 dark:text-zinc-500 uppercase font-semibold text-[9px] block">Registered On</span>
                                <span class="text-zinc-700 dark:text-zinc-355">{{ $user->created_at->format('d M Y - H:i') }}</span>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </section>
        
    </main>

    <!-- Footer -->
    <footer class="border-t border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-950 py-6 text-center text-xs text-zinc-500 dark:text-zinc-600 mt-auto transition-colors">
        &copy; 2026 General Ticketing System. Built with Premium Dark & Light UI.
    </footer>
</body>
</html>
