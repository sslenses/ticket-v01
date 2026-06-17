<!DOCTYPE html>
<html lang="en" class="h-full bg-zinc-950 text-zinc-50 antialiased">
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
    
    <style>
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
<body class="min-h-screen flex flex-col bg-radial from-zinc-900 to-zinc-950">

    <!-- Top Navigation -->
    <header class="border-b border-zinc-800 bg-zinc-950/50 backdrop-blur-md sticky top-0 z-50">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
            <a href="/" class="flex items-center gap-3 hover:opacity-95 transition-opacity group">
                <div class="relative w-9 h-9 rounded-xl bg-zinc-950 border border-zinc-800 flex items-center justify-center shadow-lg group-hover:border-violet-500/50 transition-all duration-300">
                    <!-- Glow background -->
                    <div class="absolute inset-0 bg-gradient-to-tr from-violet-600/20 to-indigo-600/20 rounded-xl blur-sm opacity-50 group-hover:opacity-100 group-hover:blur-md transition-all duration-300"></div>
                    
                    <!-- Logo Graphic -->
                    <svg class="relative w-6 h-6 text-violet-400 group-hover:text-indigo-300 transition-colors duration-300" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
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
                <span class="font-display font-semibold text-lg tracking-tight text-zinc-100 group-hover:text-violet-300 transition-colors duration-350">Ticketing System</span>
            </a>
            
            <!-- User Profile & Logout -->
            <div class="flex items-center gap-4">
                <div class="flex flex-col items-end text-right">
                    <span class="text-sm font-semibold text-zinc-100">{{ auth()->user()->name }}</span>
                    <span class="text-xs text-zinc-400 font-medium capitalize">{{ str_replace('_', ' ', auth()->user()->role) }}</span>
                </div>
                <form action="{{ route('logout') }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" class="bg-zinc-800 hover:bg-zinc-700 active:scale-95 text-xs font-semibold text-zinc-200 hover:text-white px-3.5 py-2 rounded-xl transition-all border border-zinc-700/50 cursor-pointer">
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
            <a href="/" class="inline-flex items-center gap-1.5 text-xs font-semibold text-zinc-400 hover:text-zinc-200 transition-colors bg-zinc-900/40 border border-zinc-800/80 px-3 py-1.5 rounded-lg">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-3.5 h-3.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5" />
                </svg>
                Back to Dashboard
            </a>
        </div>

        <!-- Header Title Section -->
        <div>
            <h1 class="text-3xl font-extrabold text-white tracking-tight">User Management</h1>
            <p class="text-zinc-400 text-sm mt-1">Manage and audit all registered accounts and system roles.</p>
        </div>

        <!-- User List Table -->
        <section class="rounded-2xl border border-zinc-800/80 bg-zinc-900/40 backdrop-blur-xl p-6 md:p-8 shadow-xl">
            <h2 class="text-xl font-bold mb-6 flex items-center gap-2">
                <span class="w-2 h-5 rounded bg-violet-600 inline-block"></span>
                Registered Users & System Roles
            </h2>

            <div class="overflow-x-auto">
                <table class="w-full border-collapse text-left">
                    <thead>
                        <tr class="border-b border-zinc-800 text-xs font-bold text-zinc-400 uppercase tracking-wider">
                            <th class="py-3 px-4">Name</th>
                            <th class="py-3 px-4">Email</th>
                            <th class="py-3 px-4">Testing Role</th>
                            <th class="py-3 px-4 text-right">Registered At</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-800/60 text-sm text-zinc-300">
                        @foreach($users as $user)
                        <tr class="hover:bg-zinc-800/25 transition-colors duration-200">
                            <td class="py-4 px-4 font-bold text-white select-all">
                                {{ $user->name }}
                            </td>
                            <td class="py-4 px-4 text-zinc-400">
                                {{ $user->email }}
                            </td>
                            <td class="py-4 px-4">
                                <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold border capitalize"
                                      :class="{
                                          'bg-violet-500/10 text-violet-400 border-violet-500/20': '{{ $user->role }}' === 'admin',
                                          'bg-cyan-500/10 text-cyan-400 border-cyan-500/20': '{{ $user->role }}' === 'dest_manager',
                                          'bg-emerald-500/10 text-emerald-400 border-emerald-500/20': '{{ $user->role }}' === 'staff',
                                          'bg-zinc-500/10 text-zinc-450 border-zinc-500/20': '{{ $user->role }}' === 'user'
                                      }">
                                    {{ str_replace('_', ' ', $user->role) }}
                                </span>
                            </td>
                            <td class="py-4 px-4 text-right text-zinc-500">
                                {{ $user->created_at->format('d M Y - H:i') }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>
        
    </main>

    <!-- Footer -->
    <footer class="border-t border-zinc-800 bg-zinc-950 py-6 text-center text-xs text-zinc-600 mt-auto">
        &copy; 2026 General Ticketing System. Built with Premium Dark UI.
    </footer>
</body>
</html>
