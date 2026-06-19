<!DOCTYPE html>
<html lang="en" x-data="{ theme: localStorage.getItem('theme') || 'dark' }" :class="theme" class="h-full antialiased">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Technical Ticket Network</title>
    
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
<body class="min-h-screen flex items-center justify-center bg-zinc-50 dark:bg-zinc-950 text-zinc-900 dark:text-zinc-50 p-4 md:p-6 transition-colors duration-300 relative"
      x-data="{
          email: '',
          password: ''
      }">

    <!-- Top Right Theme Switcher -->
    <div class="absolute top-4 right-4 z-50">
        <button @click="theme = (theme === 'dark' ? 'light' : 'dark'); localStorage.setItem('theme', theme); document.documentElement.className = theme;" 
                class="w-9 h-9 rounded-xl border border-zinc-200 dark:border-zinc-800 bg-white/80 dark:bg-zinc-900/50 hover:bg-zinc-100 dark:hover:bg-zinc-800 text-zinc-600 dark:text-zinc-400 flex items-center justify-center transition-all cursor-pointer shadow-sm">
            <!-- Sun Icon (shows when theme is dark) -->
            <svg x-show="theme === 'dark'" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4.5 h-4.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v2.25m0 13.5V21M4.93 4.93l1.59 1.59m10.96 10.96l1.59 1.59M3 12h2.25m13.5 0H21m-16.07 7.07l1.59-1.59M16.95 6.05l1.59-1.59M12 7.5a4.5 4.5 0 1 0 0 9 4.5 4.5 0 0 0 0-9Z" />
            </svg>
            <!-- Moon Icon (shows when theme is light) -->
            <svg x-show="theme === 'light'" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4.5 h-4.5" style="display: none;">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21.752 15.002A9.72 9.72 0 0 1 18 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 0 0 3 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 0 0 9.002-5.998Z" />
            </svg>
        </button>
    </div>

    <div class="w-full max-w-md flex flex-col gap-6 my-auto">
        
        <!-- Logo Header -->
        <div class="flex flex-col items-center gap-2 text-center">
            <div class="relative w-12 h-12 rounded-xl bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 flex items-center justify-center shadow-md hover:border-red-500/50 transition-all duration-300 group">
                <!-- Glow background -->
                <div class="absolute inset-0 bg-gradient-to-tr from-red-600/10 to-rose-600/10 dark:from-red-600/20 dark:to-rose-600/20 rounded-xl blur-sm opacity-50 group-hover:opacity-100 group-hover:blur-md transition-all duration-300"></div>
                
                <!-- Logo Graphic -->
                <svg class="relative w-8 h-8 text-red-600 dark:text-red-400 group-hover:text-rose-500 dark:group-hover:text-rose-300 transition-colors duration-300" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <!-- Connecting Fiber Lines -->
                    <path d="M4 6h16" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" class="opacity-30" />
                    <path d="M12 6v12" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" class="opacity-30" />
                    
                    <!-- Animated Signal dashes -->
                    <path d="M4 6h16" stroke="#f87171" stroke-width="1.5" stroke-linecap="round" class="signal-line" style="stroke-dasharray: 4, 12; animation: signal-flow 2s linear infinite;" />
                    <path d="M12 6v12" stroke="#f87171" stroke-width="1.5" stroke-linecap="round" class="signal-line" style="stroke-dasharray: 4, 12; animation: signal-flow 2s linear infinite;" />

                    <!-- Network Node Circles -->
                    <circle cx="4" cy="6" r="2" fill="currentColor" />
                    <circle cx="20" cy="6" r="2" fill="currentColor" />
                    <circle cx="12" cy="18" r="2" fill="currentColor" />
                    
                    <!-- Glowing Pulsing Core -->
                    <circle cx="12" cy="6" r="3.5" fill="#f87171" class="animate-ping opacity-75" />
                    <circle cx="12" cy="6" r="3.5" fill="#ef4444" />
                    <circle cx="12" cy="6" r="1.5" fill="#ffffff" />
                </svg>
            </div>
            <h1 class="text-2xl font-bold tracking-tight text-zinc-900 dark:text-white mt-2">Welcome Back</h1>
            <p class="text-zinc-550 dark:text-zinc-500 text-sm">Log in to manage your connection and deployment tickets</p>
        </div>

        <!-- Login Card -->
        <div class="rounded-2xl border border-zinc-200 dark:border-zinc-800 bg-white/80 dark:bg-zinc-900/40 backdrop-blur-xl p-6 md:p-8 shadow-xl dark:shadow-2xl relative overflow-hidden">
            <div class="absolute inset-0 bg-gradient-to-tr from-red-500/5 to-transparent pointer-events-none"></div>
            
            <!-- Session Status / Validation Errors / Success Status -->
            @if (session('status'))
                <div class="mb-5 bg-emerald-500/10 border border-emerald-500/20 text-emerald-600 dark:text-emerald-400 text-xs rounded-xl p-3.5 flex flex-col gap-1">
                    <span class="font-bold">Success:</span>
                    <p>{{ session('status') }}</p>
                </div>
            @endif

            @if ($errors->any())
                <div class="mb-5 bg-red-500/10 border border-red-500/20 text-red-600 dark:text-red-400 text-xs rounded-xl p-3.5 flex flex-col gap-1">
                    <span class="font-bold">Login Failed:</span>
                    <ul class="list-disc pl-4 space-y-0.5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="/login" class="space-y-4">
                @csrf
                <div>
                    <label for="email" class="text-xs text-zinc-500 dark:text-zinc-400 font-semibold block mb-1">Email Address</label>
                    <input type="email" id="email" name="email" x-model="email" required autofocus placeholder="name@example.com"
                           class="w-full bg-white dark:bg-zinc-950 border border-zinc-200 dark:border-zinc-800 rounded-xl px-4 py-2.5 text-sm text-zinc-900 dark:text-white focus:outline-none focus:border-red-600 dark:focus:border-red-600 focus:ring-1 focus:ring-red-600 transition-colors">
                </div>

                <div x-data="{ showPassword: false }">
                    <div class="flex items-center justify-between mb-1">
                        <label for="password" class="text-xs text-zinc-500 dark:text-zinc-400 font-semibold block">Password</label>
                    </div>
                    <div class="relative">
                        <input :type="showPassword ? 'text' : 'password'" id="password" name="password" x-model="password" required placeholder="••••••••"
                               class="w-full bg-white dark:bg-zinc-950 border border-zinc-200 dark:border-zinc-800 rounded-xl pl-4 pr-11 py-2.5 text-sm text-zinc-900 dark:text-white focus:outline-none focus:border-red-600 dark:focus:border-red-600 focus:ring-1 focus:ring-red-600 transition-colors">
                        <button type="button" @click="showPassword = !showPassword" class="absolute inset-y-0 right-0 flex items-center pr-4 text-zinc-400 dark:text-zinc-500 hover:text-zinc-600 dark:hover:text-zinc-400 focus:outline-none cursor-pointer">
                            <!-- Eye icon (shown when password is hidden) -->
                            <svg x-show="!showPassword" class="h-4.5 w-4.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                            </svg>
                            <!-- Eye slash icon (shown when password is visible) -->
                            <svg x-show="showPassword" class="h-4.5 w-4.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="display: none;">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 0 0 1.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.451 10.451 0 0 1 12 4.5c4.756 0 8.773 3.162 10.065 7.498a10.522 10.522 0 0 1-4.293 5.774M6.228 6.228 3 3m3.228 3.228 3.65 3.65m7.894 7.894L21 21m-3.228-3.228-3.65-3.65m0 0a3 3 0 1 0-4.243-4.243m4.242 4.242L9.88 9.88" />
                            </svg>
                        </button>
                    </div>
                </div>

                <div class="flex items-center justify-between pt-1">
                    <label class="flex items-center text-xs text-zinc-650 dark:text-zinc-400 cursor-pointer select-none">
                        <input type="checkbox" name="remember" class="mr-2 rounded border-zinc-300 dark:border-zinc-800 bg-white dark:bg-zinc-950 text-red-600 focus:ring-red-600">
                        Remember me
                    </label>
                </div>

                <button type="submit"
                        class="w-full bg-red-600 hover:bg-red-750 dark:hover:bg-red-500 active:scale-[0.98] text-sm font-semibold text-white py-3 rounded-xl transition-all shadow-lg shadow-red-600/10 cursor-pointer mt-2">
                    Sign In
                </button>
            </form>
        </div>

    </div>

</body>
</html>
