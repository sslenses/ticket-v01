<!DOCTYPE html>
<html lang="en" class="h-full bg-zinc-950 text-zinc-50 antialiased">
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
          role: 'user'
      }">

    <div class="w-full max-w-md flex flex-col gap-6">
        
        <!-- Logo Header -->
        <div class="flex flex-col items-center gap-2 text-center">
            <div class="w-12 h-12 rounded-xl bg-gradient-to-tr from-violet-600 to-indigo-600 flex items-center justify-center font-display font-bold text-2xl text-white shadow-lg">
                T
            </div>
            <h1 class="text-2xl font-bold tracking-tight text-white mt-2">Create Account</h1>
            <p class="text-zinc-500 text-sm">Register a new account with a specific RBAC testing role</p>
        </div>

        <!-- Registration Card -->
        <div class="rounded-2xl border border-zinc-800 bg-zinc-900/40 backdrop-blur-xl p-6 md:p-8 shadow-2xl relative overflow-hidden">
            <div class="absolute inset-0 bg-gradient-to-tr from-violet-500/5 to-transparent pointer-events-none"></div>
            
            <!-- Session Status / Validation Errors -->
            @if ($errors->any())
                <div class="mb-5 bg-red-500/10 border border-red-500/20 text-red-400 text-xs rounded-xl p-3.5 flex flex-col gap-1">
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
                    <label for="name" class="text-xs text-zinc-400 font-semibold block mb-1">Full Name</label>
                    <input type="text" id="name" name="name" value="{{ old('name') }}" required autofocus placeholder="John Doe"
                           class="w-full bg-zinc-950 border border-zinc-800 rounded-xl px-4 py-2.5 text-sm text-white focus:outline-none focus:border-violet-600 transition-colors">
                </div>

                <div>
                    <label for="email" class="text-xs text-zinc-400 font-semibold block mb-1">Email Address</label>
                    <input type="email" id="email" name="email" value="{{ old('email') }}" required placeholder="name@example.com"
                           class="w-full bg-zinc-950 border border-zinc-800 rounded-xl px-4 py-2.5 text-sm text-white focus:outline-none focus:border-violet-600 transition-colors">
                </div>

                <div>
                    <label for="role" class="text-xs text-zinc-400 font-semibold block mb-1">Testing Role</label>
                    <select id="role" name="role" x-model="role" required
                            class="w-full bg-zinc-950 border border-zinc-800 rounded-xl px-4 py-2.5 text-sm text-white focus:outline-none focus:border-violet-600 transition-colors">
                        <option value="user">User (Biasa)</option>
                        <option value="staff">Staff Account</option>
                        <option value="dest_manager">Destination Manager</option>
                        <option value="admin">Admin Account</option>
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="password" class="text-xs text-zinc-400 font-semibold block mb-1">Password</label>
                        <input type="password" id="password" name="password" required placeholder="••••••••"
                               class="w-full bg-zinc-950 border border-zinc-800 rounded-xl px-4 py-2.5 text-sm text-white focus:outline-none focus:border-violet-600 transition-colors">
                    </div>

                    <div>
                        <label for="password_confirmation" class="text-xs text-zinc-400 font-semibold block mb-1">Confirm Password</label>
                        <input type="password" id="password_confirmation" name="password_confirmation" required placeholder="••••••••"
                               class="w-full bg-zinc-950 border border-zinc-800 rounded-xl px-4 py-2.5 text-sm text-white focus:outline-none focus:border-violet-600 transition-colors">
                    </div>
                </div>

                <button type="submit"
                        class="w-full bg-violet-600 hover:bg-violet-500 active:scale-[0.98] text-sm font-semibold text-white py-3 rounded-xl transition-all shadow-lg shadow-violet-600/10 cursor-pointer mt-4">
                    Sign Up
                </button>
            </form>
        </div>

        <div class="text-center text-xs text-zinc-500">
            Already have an account? 
            <a href="/login" class="text-violet-400 hover:text-violet-300 font-semibold transition-colors">Sign In</a>
        </div>
        
    </div>

</body>
</html>
