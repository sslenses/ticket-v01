<!DOCTYPE html>
<html lang="en" x-data="{ theme: localStorage.getItem('theme') || 'dark' }" :class="theme" class="h-full antialiased">
<head>
    <script>
        if (localStorage.getItem('theme') === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        } else if (localStorage.getItem('theme') === 'light') {
            document.documentElement.classList.remove('dark');
        } else {
            document.documentElement.classList.add('dark'); // default
        }
    </script>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Tenant - Technical Ticket Network</title>
    <link rel="icon" type="image/x-icon" href="https://github.githubassets.com/favicon.ico">
    
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
    <script>
        window.__tenants = @json($tenants);
    </script>
    @include('layouts.spa-script')
</head>
<body class="min-h-screen flex flex-col bg-zinc-50 dark:bg-zinc-950 text-zinc-900 dark:text-zinc-50 transition-colors duration-300">
    <div id="app-root" class="min-h-screen flex flex-col" x-data="{
          showEditModal: false,
          isCreateMode: false,
          editTenant: { id: null, name: '', code: '' },
          tenants: window.__tenants || [],
          errorMessage: '',

          nameExists() {
              if (!this.editTenant.name.trim()) return false;
              return this.tenants.some(t => 
                  t.id !== this.editTenant.id && 
                  t.name.toLowerCase() === this.editTenant.name.trim().toLowerCase()
              );
          },

          codeExists() {
              if (!this.editTenant.code.trim()) return false;
              return this.tenants.some(t => 
                  t.id !== this.editTenant.id && 
                  t.code.toLowerCase() === this.editTenant.code.trim().toLowerCase()
              );
          },

          openCreateModal() {
              this.isCreateMode = true;
              this.editTenant = { id: null, name: '', code: '' };
              this.errorMessage = '';
              this.showEditModal = true;
          },

          openEditModal(tenant) {
              this.isCreateMode = false;
              this.editTenant = { id: tenant.id, name: tenant.name, code: tenant.code };
              this.errorMessage = '';
              this.showEditModal = true;
          },

          async submitTenantForm() {
              this.errorMessage = '';
              if (this.nameExists() || this.codeExists()) {
                  this.errorMessage = 'Nama atau kode Tenant sudah terdaftar.';
                  return;
              }
              if (this.isCreateMode) {
                  await this.createTenant();
              } else {
                  await this.updateTenant();
              }
          },

          async createTenant() {
              try {
                  const response = await fetch('/api/tenants', {
                      method: 'POST',
                      headers: {
                          'Content-Type': 'application/json',
                          'Accept': 'application/json',
                          'X-CSRF-TOKEN': '{{ csrf_token() }}'
                      },
                      body: JSON.stringify({
                          name: this.editTenant.name,
                          code: this.editTenant.code
                      })
                  });

                  if (!response.ok) {
                      const err = await response.json();
                      this.errorMessage = 'Gagal membuat tenant: ' + (err.message || 'Error validasi');
                      return;
                  }

                  window.reloadPage();
              } catch (e) {
                  this.errorMessage = 'Gagal menghubungi server.';
              }
          },

          async updateTenant() {
              try {
                  const response = await fetch(`/api/tenants/${this.editTenant.id}`, {
                      method: 'PATCH',
                      headers: {
                          'Content-Type': 'application/json',
                          'Accept': 'application/json',
                          'X-CSRF-TOKEN': '{{ csrf_token() }}'
                      },
                      body: JSON.stringify(this.editTenant)
                  });

                  if (!response.ok) {
                      const err = await response.json();
                      this.errorMessage = 'Gagal memperbarui tenant: ' + (err.message || 'Error validasi');
                      return;
                  }

                  window.reloadPage();
              } catch (e) {
                  this.errorMessage = 'Gagal menghubungi server.';
              }
          },

          async deleteTenant(tenantId) {
              this.errorMessage = '';
              if (!confirm('Apakah Anda yakin ingin menghapus tenant ini? Tindakan ini permanen.')) {
                  return;
              }

              try {
                  const response = await fetch(`/api/tenants/${tenantId}`, {
                      method: 'DELETE',
                      headers: {
                          'Content-Type': 'application/json',
                          'Accept': 'application/json',
                          'X-CSRF-TOKEN': '{{ csrf_token() }}'
                      }
                  });

                  if (!response.ok) {
                      const err = await response.json();
                      this.errorMessage = 'Gagal menghapus tenant: ' + (err.message || 'Error');
                      return;
                  }

                  window.reloadPage();
              } catch (e) {
                  this.errorMessage = 'Gagal menghubungi server.';
              }
          }
      }">

    <!-- Top Navigation -->
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
                    <a href="/" class="text-sm font-semibold px-3 py-1.5 rounded-lg transition-colors text-zinc-600 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-zinc-200">
                        Dashboard
                    </a>
                    @if (auth()->user()->hasRole('admin') || auth()->user()->hasRole('dest_manager'))
                        <a href="/users" class="text-sm font-semibold px-3 py-1.5 rounded-lg transition-colors text-zinc-600 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-zinc-200">
                            Pengguna
                        </a>
                        <a href="/tenants" class="text-sm font-semibold px-3 py-1.5 rounded-lg transition-colors text-red-600 dark:text-red-400 bg-red-50 dark:bg-red-500/10 shadow-sm">
                            Manajemen Tenant
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
                        
                        <div class="p-1.5">
                            <form action="{{ route('logout') }}" method="POST" class="w-full">
                                @csrf
                                <button type="submit" class="w-full text-left flex items-center gap-2 px-3 py-2 text-xs font-semibold text-zinc-700 dark:text-zinc-300 hover:text-red-650 hover:bg-red-50 dark:hover:bg-red-500/10 rounded-lg transition-colors cursor-pointer">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4 text-zinc-400 dark:text-zinc-500 group-hover:text-current">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15M12 9l-3 3m0 0 3 3m-3-3h12.75" />
                                    </svg>
                                    Keluar
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Mobile Controls -->
            <div class="flex md:hidden items-center gap-2">
                <button @click="theme = (theme === 'dark' ? 'light' : 'dark'); localStorage.setItem('theme', theme); document.documentElement.className = theme;" 
                        class="w-9 h-9 rounded-xl border border-zinc-200 dark:border-zinc-800 bg-white/80 dark:bg-zinc-900/50 text-zinc-600 dark:text-zinc-400 flex items-center justify-center transition-all cursor-pointer shadow-sm">
                    <svg x-show="theme === 'dark'" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4.5 h-4.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v2.25m0 13.5V21M4.93 4.93l1.59 1.59m10.96 10.96l1.59 1.59M3 12h2.25m13.5 0H21m-16.07 7.07l1.59-1.59M16.95 6.05l1.59-1.59M12 7.5a4.5 4.5 0 1 0 0 9 4.5 4.5 0 0 0 0-9Z" />
                    </svg>
                    <svg x-show="theme === 'light'" xmlns="http://www.w3.org/2500/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4.5 h-4.5" style="display: none;">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21.752 15.002A9.72 9.72 0 0 1 18 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 0 0 3 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 0 0 9.002-5.998Z" />
                    </svg>
                </button>

                <button @click="mobileMenuOpen = !mobileMenuOpen" 
                        class="w-9 h-9 rounded-xl border border-zinc-200 dark:border-zinc-800 bg-white/80 dark:bg-zinc-900/50 text-zinc-600 dark:text-zinc-400 flex items-center justify-center transition-all cursor-pointer shadow-sm">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
                        <path x-show="!mobileMenuOpen" stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                        <path x-show="mobileMenuOpen" stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" style="display: none;" />
                    </svg>
                </button>
            </div>
        </div>

        <!-- Mobile Drawer/Menu -->
        <div x-show="mobileMenuOpen" 
             x-transition:enter="transition ease-out duration-150"
             x-transition:enter-start="opacity-0 -translate-y-2"
             x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-100"
             x-transition:leave-start="opacity-100 translate-y-0"
             x-transition:leave-end="opacity-0 -translate-y-2"
             class="md:hidden border-t border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-950 px-4 py-4 space-y-4 shadow-lg"
             style="display: none;">
            <nav class="flex flex-col gap-1">
                <a href="/" class="text-sm font-semibold px-4 py-2.5 rounded-xl transition-colors text-zinc-600 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-zinc-200">
                    Dashboard
                </a>
                @if (auth()->user()->hasRole('admin') || auth()->user()->hasRole('dest_manager'))
                    <a href="/users" class="text-sm font-semibold px-4 py-2.5 rounded-xl transition-colors text-zinc-600 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-zinc-200">
                        Pengguna
                    </a>
                    <a href="/tenants" class="text-sm font-semibold px-4 py-2.5 rounded-xl transition-colors text-red-600 dark:text-red-400 bg-red-50 dark:bg-red-500/10">
                        Manajemen Tenant
                    </a>
                @endif
            </nav>

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
                Kembali ke Dashboard
            </a>
        </div>

        <!-- Header Title Section -->
        <div>
            <h1 class="text-3xl font-extrabold text-zinc-900 dark:text-white tracking-tight">Manajemen Tenant</h1>
            <p class="text-zinc-500 dark:text-zinc-500 text-sm mt-1">Tambah, perbarui, dan kelola detail serta ID tenant jaringan.</p>
        </div>

        <!-- Global Error Banner (Only visible when modal is closed) -->
        <div x-show="errorMessage && !showEditModal" 
             class="p-4 bg-red-50 dark:bg-red-500/10 border border-red-200 dark:border-red-500/20 rounded-xl flex items-start gap-2.5"
             x-transition
             style="display: none;">
            <svg class="w-5 h-5 text-red-650 dark:text-red-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
            <div class="flex-1">
                <span class="text-xs text-red-650 dark:text-red-400 font-semibold" x-text="errorMessage"></span>
            </div>
            <button @click="errorMessage = ''" class="text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300 font-bold text-sm cursor-pointer px-1">&times;</button>
        </div>

        <!-- Tenant List Table Section -->
        <section class="rounded-2xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900/60 backdrop-blur-xl p-5 md:p-8 shadow-sm dark:shadow-2xl transition-colors">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
                <h2 class="text-xl font-bold flex items-center gap-2 text-zinc-900 dark:text-white">
                    <span class="w-2 h-5 rounded bg-red-600 inline-block"></span>
                    Tenant Terdaftar
                </h2>
                <button @click="openCreateModal()"
                        class="bg-red-600 hover:bg-red-500 text-xs font-semibold px-4 py-2.5 rounded-lg text-white transition-all cursor-pointer flex items-center gap-1.5 shadow-md active:scale-95">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                    Tambah Tenant Baru
                </button>
            </div>

            <!-- Desktop View: Table Layout -->
            <div class="hidden md:block overflow-x-auto">
                <table class="w-full border-collapse text-left">
                    <thead>
                        <tr class="border-b border-zinc-200 dark:border-zinc-700 text-xs font-bold text-zinc-600 dark:text-zinc-400 uppercase tracking-wider">
                            <th class="py-3 px-4">Nama Tenant</th>
                            <th class="py-3 px-4">Kode Tenant</th>
                            <th class="py-3 px-4">ID UUID</th>
                            <th class="py-3 px-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-200 dark:divide-zinc-800 text-sm text-zinc-800 dark:text-zinc-200">
                        @forelse($tenants as $tenant)
                        <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/50 transition-colors duration-200">
                            <td class="py-4 px-4 font-bold text-zinc-900 dark:text-white select-all">
                                {{ $tenant->name }}
                            </td>
                            <td class="py-4 px-4 font-mono font-bold text-red-600 dark:text-red-400">
                                {{ $tenant->code }}
                            </td>
                            <td class="py-4 px-4 text-zinc-500 dark:text-zinc-400 font-mono text-xs select-all">
                                {{ $tenant->id }}
                            </td>
                            <td class="py-4 px-4 text-right flex justify-end gap-2 items-center">
                                <button @click="openEditModal({ id: '{{ $tenant->id }}', name: '{{ addslashes($tenant->name) }}', code: '{{ addslashes($tenant->code) }}' })"
                                        class="text-red-600 hover:text-red-950 dark:text-red-400 dark:hover:text-red-200 font-semibold text-xs px-2.5 py-1.5 rounded-lg border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900/50 hover:bg-zinc-50 dark:hover:bg-zinc-800 transition-all cursor-pointer inline-flex items-center gap-1 shadow-sm">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-3.5 h-3.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                                    </svg>
                                    Ubah
                                </button>
                                <button @click="deleteTenant('{{ $tenant->id }}')"
                                        class="text-red-600 hover:text-red-950 dark:text-red-400 dark:hover:text-red-200 font-semibold text-xs px-2.5 py-1.5 rounded-lg border border-red-200 dark:border-red-500/20 bg-red-500/5 hover:bg-red-50 dark:hover:bg-red-950/30 transition-all cursor-pointer inline-flex items-center gap-1 shadow-sm">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-3.5 h-3.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                    </svg>
                                    Hapus
                                </button>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="py-8 text-center text-zinc-500">
                                Tenant tidak ditemukan. Klik "Tambah Tenant Baru" untuk mendaftar.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Mobile View: Card List Layout -->
            <div class="block md:hidden space-y-4">
                @forelse($tenants as $tenant)
                    <div class="rounded-xl border border-zinc-200 dark:border-zinc-800 bg-zinc-50/50 dark:bg-zinc-900/50 p-4 shadow-sm flex flex-col gap-3">
                        <div class="flex items-center justify-between">
                            <span class="font-bold text-sm text-zinc-900 dark:text-white">{{ $tenant->name }}</span>
                            <span class="px-2 py-0.5 rounded-full text-xs font-mono font-bold border bg-red-50 dark:bg-red-500/10 text-red-650 dark:text-red-400 border-red-200 dark:border-red-500/20 uppercase">
                                {{ $tenant->code }}
                            </span>
                        </div>
                        <div class="text-xs text-zinc-500 dark:text-zinc-400 flex flex-col gap-1.5 pt-2 border-t border-zinc-200 dark:border-zinc-800">
                            <div>
                                <span class="text-zinc-400 dark:text-zinc-500 uppercase font-semibold text-[9px] block">ID UUID Tenant</span>
                                <span class="font-mono text-zinc-800 dark:text-zinc-300 text-[11px] select-all block overflow-ellipsis truncate" title="{{ $tenant->id }}">{{ $tenant->id }}</span>
                            </div>
                        </div>
                        <div class="border-t border-zinc-200 dark:border-zinc-800 pt-3 flex justify-end gap-2">
                            <button @click="openEditModal({ id: '{{ $tenant->id }}', name: '{{ addslashes($tenant->name) }}', code: '{{ addslashes($tenant->code) }}' })"
                                    class="text-red-600 hover:text-red-950 dark:text-red-400 dark:hover:text-red-200 font-semibold text-xs px-2.5 py-1.5 rounded-lg border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900/50 hover:bg-zinc-50 dark:hover:bg-zinc-800 transition-all cursor-pointer inline-flex items-center gap-1 shadow-sm">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-3.5 h-3.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                                </svg>
                                Ubah
                            </button>
                            <button @click="deleteTenant('{{ $tenant->id }}')"
                                    class="text-red-600 hover:text-red-950 dark:text-red-400 dark:hover:text-red-200 font-semibold text-xs px-2.5 py-1.5 rounded-lg border border-red-200 dark:border-red-500/20 bg-red-500/5 hover:bg-red-50 dark:hover:bg-red-950/30 transition-all cursor-pointer inline-flex items-center gap-1 shadow-sm">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-3.5 h-3.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                </svg>
                                Hapus
                            </button>
                        </div>
                    </div>
                @empty
                    <div class="py-8 text-center text-zinc-500">
                        Tenant tidak ditemukan. Klik "Tambah Tenant Baru" untuk mendaftar.
                    </div>
                @endforelse
            </div>
        </section>
        
    </main>

    <!-- Edit/Create Tenant Modal -->
    <div x-show="showEditModal" 
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-zinc-950/80 backdrop-blur-sm"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95"
         style="display: none;">
        
        <div class="relative w-full max-w-lg bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-2xl p-6 shadow-2xl flex flex-col gap-6"
             @click.away="showEditModal = false">
            
            <div class="flex items-center justify-between pb-3 border-b border-zinc-200 dark:border-zinc-800">
                <h3 class="text-lg font-bold text-zinc-900 dark:text-white" x-text="isCreateMode ? 'Daftarkan Tenant Baru' : 'Ubah Detail Tenant'"></h3>
                <button @click="showEditModal = false" class="text-zinc-400 hover:text-zinc-700 dark:text-zinc-500 dark:hover:text-zinc-300 cursor-pointer">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

             <form @submit.prevent="submitTenantForm" class="space-y-4">
                <!-- Error Banner -->
                <div x-show="errorMessage" 
                     class="mb-4 p-3 bg-red-50 dark:bg-red-500/10 border border-red-200 dark:border-red-500/20 rounded-xl flex items-start gap-2.5"
                     x-transition
                     style="display: none;">
                    <svg class="w-5 h-5 text-red-650 dark:text-red-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                    <span class="text-xs text-red-650 dark:text-red-400 font-semibold" x-text="errorMessage"></span>
                </div>
                <div>
                    <label class="text-xs text-zinc-600 dark:text-zinc-400 font-semibold block mb-1">Nama Tenant</label>
                    <input type="text" x-model="editTenant.name" required placeholder="misal Tenant Alpha"
                           class="w-full bg-zinc-50 dark:bg-zinc-950 border border-zinc-200 dark:border-zinc-800 rounded-lg px-3 py-2 text-sm text-zinc-900 dark:text-white focus:outline-none focus:border-red-600 transition-colors">
                    <span x-show="nameExists()" class="text-xs text-red-500 font-semibold mt-1 block">
                        ⚠️ Nama Tenant sudah terdaftar.
                    </span>
                </div>

                <div>
                    <label class="text-xs text-zinc-600 dark:text-zinc-400 font-semibold block mb-1">Kode Tenant (Unik)</label>
                    <input type="text" x-model="editTenant.code" required placeholder="misal T-ALPHA"
                           class="w-full bg-zinc-50 dark:bg-zinc-950 border border-zinc-200 dark:border-zinc-800 rounded-lg px-3 py-2 text-sm text-zinc-900 dark:text-white focus:outline-none focus:border-red-600 transition-colors uppercase">
                    <span x-show="codeExists()" class="text-xs text-red-500 font-semibold mt-1 block">
                        ⚠️ Kode Tenant sudah terdaftar.
                    </span>
                </div>

                <div class="flex justify-end gap-3 pt-4 border-t border-zinc-200 dark:border-zinc-800">
                    <button type="button" @click="showEditModal = false"
                            class="bg-zinc-100 hover:bg-zinc-200 dark:bg-zinc-800 dark:hover:bg-zinc-700 text-xs font-semibold px-4 py-2.5 rounded-lg text-zinc-700 dark:text-zinc-300 transition-colors cursor-pointer">
                        Batal
                    </button>
                    <button type="submit"
                            :disabled="nameExists() || codeExists()"
                            class="bg-red-600 hover:bg-red-500 disabled:opacity-50 disabled:cursor-not-allowed text-xs font-semibold px-4 py-2.5 rounded-lg text-white transition-colors cursor-pointer"
                            x-text="isCreateMode ? 'Buat Tenant' : 'Simpan Perubahan'">
                    </button>
                </div>
            </form>
        </div>
    </div>

        <!-- Footer -->
        <footer class="border-t border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-950 py-6 text-center text-xs text-zinc-500 dark:text-zinc-600 mt-auto transition-colors">
            &copy; 2026 Technical Ticket Network by Sidiq Setyadji.
        </footer>
    </div>
</body>
</html>
