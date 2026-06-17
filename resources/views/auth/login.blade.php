<!DOCTYPE html>
<html lang="en" class="h-full bg-zinc-950 text-zinc-50 antialiased">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Ticketing System</title>
    
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
    </style>
</head>
<body class="min-h-screen flex items-center justify-center bg-radial from-zinc-900 to-zinc-950 p-4 md:p-6"
      x-data="{
          email: '',
          password: '',
          autofill(testEmail) {
              this.email = testEmail;
              this.password = 'password';
          }
      }">

    <div class="w-full max-w-md flex flex-col gap-6">
        
        <!-- Logo Header -->
        <div class="flex flex-col items-center gap-2 text-center">
            <div class="w-12 h-12 rounded-xl bg-gradient-to-tr from-violet-600 to-indigo-600 flex items-center justify-center font-display font-bold text-2xl text-white shadow-lg">
                T
            </div>
            <h1 class="text-2xl font-bold tracking-tight text-white mt-2">Welcome Back</h1>
            <p class="text-zinc-500 text-sm">Log in to manage your connection and deployment tickets</p>
        </div>

        <!-- Login Card -->
        <div class="rounded-2xl border border-zinc-800 bg-zinc-900/40 backdrop-blur-xl p-6 md:p-8 shadow-2xl relative overflow-hidden">
            <div class="absolute inset-0 bg-gradient-to-tr from-violet-500/5 to-transparent pointer-events-none"></div>
            
            <!-- Session Status / Validation Errors -->
            @if ($errors->any())
                <div class="mb-5 bg-red-500/10 border border-red-500/20 text-red-400 text-xs rounded-xl p-3.5 flex flex-col gap-1">
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
                    <label for="email" class="text-xs text-zinc-400 font-semibold block mb-1">Email Address</label>
                    <input type="email" id="email" name="email" x-model="email" required autofocus placeholder="name@example.com"
                           class="w-full bg-zinc-950 border border-zinc-800 rounded-xl px-4 py-2.5 text-sm text-white focus:outline-none focus:border-violet-600 transition-colors">
                </div>

                <div>
                    <div class="flex items-center justify-between mb-1">
                        <label for="password" class="text-xs text-zinc-400 font-semibold block">Password</label>
                    </div>
                    <input type="password" id="password" name="password" x-model="password" required placeholder="••••••••"
                           class="w-full bg-zinc-950 border border-zinc-800 rounded-xl px-4 py-2.5 text-sm text-white focus:outline-none focus:border-violet-600 transition-colors">
                </div>

                <div class="flex items-center justify-between pt-1">
                    <label class="flex items-center text-xs text-zinc-400 cursor-pointer select-none">
                        <input type="checkbox" name="remember" class="mr-2 rounded border-zinc-800 bg-zinc-950 text-violet-600 focus:ring-violet-600">
                        Remember me
                    </label>
                </div>

                <button type="submit"
                        class="w-full bg-violet-600 hover:bg-violet-500 active:scale-[0.98] text-sm font-semibold text-white py-3 rounded-xl transition-all shadow-lg shadow-violet-600/10 cursor-pointer mt-2">
                    Sign In
                </button>
            </form>
        </div>

        <!-- Dev Sandbox Accounts Helper -->
        <div class="rounded-2xl border border-zinc-800 bg-zinc-900/10 backdrop-blur-sm p-5 flex flex-col gap-3">
            <div>
                <span class="text-xs font-bold text-violet-400 uppercase tracking-widest">Test Accounts (RBAC Roles)</span>
                <p class="text-[11px] text-zinc-500 mt-0.5">Click any account below to autofill, then click Sign In.</p>
            </div>
            
            <div class="grid grid-cols-2 gap-2 text-xs">
                <button @click="autofill('admin@example.com')" 
                        class="bg-zinc-900/60 hover:bg-zinc-800/80 border border-zinc-800 text-left p-2.5 rounded-xl transition-colors cursor-pointer group flex flex-col">
                    <span class="font-bold text-zinc-200 group-hover:text-violet-400 transition-colors">Admin Account</span>
                    <span class="text-[10px] text-zinc-500 font-mono mt-0.5">admin@example.com</span>
                </button>
                <button @click="autofill('manager@example.com')" 
                        class="bg-zinc-900/60 hover:bg-zinc-800/80 border border-zinc-800 text-left p-2.5 rounded-xl transition-colors cursor-pointer group flex flex-col">
                    <span class="font-bold text-zinc-200 group-hover:text-cyan-400 transition-colors">Dest Manager</span>
                    <span class="text-[10px] text-zinc-500 font-mono mt-0.5">manager@example.com</span>
                </button>
                <button @click="autofill('staff@example.com')" 
                        class="bg-zinc-900/60 hover:bg-zinc-800/80 border border-zinc-800 text-left p-2.5 rounded-xl transition-colors cursor-pointer group flex flex-col">
                    <span class="font-bold text-zinc-200 group-hover:text-emerald-400 transition-colors">Staff Account</span>
                    <span class="text-[10px] text-zinc-500 font-mono mt-0.5">staff@example.com</span>
                </button>
                <button @click="autofill('user@example.com')" 
                        class="bg-zinc-900/60 hover:bg-zinc-800/80 border border-zinc-800 text-left p-2.5 rounded-xl transition-colors cursor-pointer group flex flex-col">
                    <span class="font-bold text-zinc-200 group-hover:text-zinc-400 transition-colors">User Account</span>
                    <span class="text-[10px] text-zinc-500 font-mono mt-0.5">user@example.com</span>
                </button>
            </div>
            
            <div class="border-t border-zinc-800/50 pt-2 text-[10px] text-zinc-500 flex justify-between">
                <span>Default Password:</span>
                <span class="font-mono text-zinc-400">password</span>
            </div>
        </div>
        
    </div>

</body>
</html>
