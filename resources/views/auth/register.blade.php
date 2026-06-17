<!DOCTYPE html>
<html lang="en" x-data="{ role: 'user', theme: localStorage.getItem('theme') || 'dark' }" :class="theme" class="h-full antialiased">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Ticketing System</title>
    
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
    </style>
</head>
<body class="min-h-screen flex items-center justify-center bg-zinc-50 dark:bg-zinc-950 text-zinc-900 dark:text-zinc-50 p-4 md:p-6 transition-colors duration-300 relative">

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
            <div class="w-12 h-12 rounded-xl bg-gradient-to-tr from-violet-600 to-indigo-600 flex items-center justify-center font-display font-bold text-2xl text-white shadow-lg shadow-violet-500/10 dark:shadow-none">
                T
            </div>
            <h1 class="text-2xl font-bold tracking-tight text-zinc-900 dark:text-white mt-2">Create Account</h1>
            <p class="text-zinc-550 dark:text-zinc-500 text-sm">Register a new account with a specific RBAC testing role</p>
        </div>

        <!-- Registration Card -->
        <div class="rounded-2xl border border-zinc-200 dark:border-zinc-800 bg-white/80 dark:bg-zinc-900/40 backdrop-blur-xl p-6 md:p-8 shadow-xl dark:shadow-2xl relative overflow-hidden">
            <div class="absolute inset-0 bg-gradient-to-tr from-violet-500/5 to-transparent pointer-events-none"></div>
            
            <!-- Validation Errors -->
            @if ($errors->any())
                <div class="mb-5 bg-red-500/10 border border-red-500/20 text-red-655 dark:text-red-400 text-xs rounded-xl p-3.5 flex flex-col gap-1">
                    <span class="font-bold">Registration Failed:</span>
                    <ul class="list-disc pl-4 space-y-0.5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="/register" class="space-y-4">
                @csrf
                
                <div>
                    <label for="name" class="text-xs text-zinc-550 dark:text-zinc-400 font-semibold block mb-1">Full Name</label>
                    <input type="text" id="name" name="name" value="{{ old('name') }}" required autofocus placeholder="John Doe"
                           class="w-full bg-white dark:bg-zinc-950 border border-zinc-200 dark:border-zinc-800 rounded-xl px-4 py-2.5 text-sm text-zinc-900 dark:text-white focus:outline-none focus:border-violet-600 dark:focus:border-violet-600 focus:ring-1 focus:ring-violet-600 transition-colors">
                </div>

                <div>
                    <label for="email" class="text-xs text-zinc-550 dark:text-zinc-400 font-semibold block mb-1">Email Address</label>
                    <input type="email" id="email" name="email" value="{{ old('email') }}" required placeholder="name@example.com"
                           class="w-full bg-white dark:bg-zinc-950 border border-zinc-200 dark:border-zinc-800 rounded-xl px-4 py-2.5 text-sm text-zinc-900 dark:text-white focus:outline-none focus:border-violet-600 dark:focus:border-violet-600 focus:ring-1 focus:ring-violet-600 transition-colors">
                </div>

                <div>
                    <label for="role" class="text-xs text-zinc-550 dark:text-zinc-400 font-semibold block mb-1">Testing Role</label>
                    <select id="role" name="role" x-model="role" required
                            class="w-full bg-white dark:bg-zinc-950 border border-zinc-200 dark:border-zinc-800 rounded-xl px-4 py-2.5 text-sm text-zinc-900 dark:text-white focus:outline-none focus:border-violet-600 dark:focus:border-violet-600 focus:ring-1 focus:ring-violet-600 transition-colors">
                        <option value="user">User (Biasa)</option>
                        <option value="staff">Staff Account</option>
                        <option value="dest_manager">Destination Manager</option>
                        <option value="admin">Admin Account</option>
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="password" class="text-xs text-zinc-550 dark:text-zinc-400 font-semibold block mb-1">Password</label>
                        <input type="password" id="password" name="password" required placeholder="••••••••"
                               class="w-full bg-white dark:bg-zinc-950 border border-zinc-200 dark:border-zinc-800 rounded-xl px-4 py-2.5 text-sm text-zinc-900 dark:text-white focus:outline-none focus:border-violet-600 dark:focus:border-violet-600 focus:ring-1 focus:ring-violet-600 transition-colors">
                    </div>

                    <div>
                        <label for="password_confirmation" class="text-xs text-zinc-550 dark:text-zinc-400 font-semibold block mb-1">Confirm Password</label>
                        <input type="password" id="password_confirmation" name="password_confirmation" required placeholder="••••••••"
                               class="w-full bg-white dark:bg-zinc-950 border border-zinc-200 dark:border-zinc-800 rounded-xl px-4 py-2.5 text-sm text-zinc-900 dark:text-white focus:outline-none focus:border-violet-600 dark:focus:border-violet-600 focus:ring-1 focus:ring-violet-600 transition-colors">
                    </div>
                </div>

                <button type="submit"
                        class="w-full bg-violet-600 hover:bg-violet-750 dark:hover:bg-violet-500 active:scale-[0.98] text-sm font-semibold text-white py-3 rounded-xl transition-all shadow-lg shadow-violet-600/10 cursor-pointer mt-4">
                    Sign Up
                </button>
            </form>
        </div>

        <div class="text-center text-xs text-zinc-500">
            Already have an account? 
            <a href="/login" class="text-violet-600 dark:text-violet-400 hover:text-violet-500 dark:hover:text-violet-300 font-semibold transition-colors">Sign In</a>
        </div>
        
    </div>

</body>
</html>
