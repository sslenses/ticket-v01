<!DOCTYPE html>
<html lang="en" x-data="{ 
          showCreateModal: false,
          searchQuery: '',
          activeStatusFilter: 'all',
          theme: localStorage.getItem('theme') || 'dark',
          tickets: [
              @foreach($tickets as $ticket)
              {
                  id: {{ $ticket->id }},
                  label: '{{ $ticket->label }}',
                  source: '{{ $ticket->source_device }}',
                  destination: '{{ $ticket->destination_device }}',
                  connector: '{{ $ticket->connector_type }}',
                  status: '{{ $ticket->status }}',
                  statusLabel: '{{ str_replace('_', ' ', $ticket->status) }}'
              },
              @endforeach
          ],
          filteredTickets() {
              let filtered = this.tickets;
              if (this.activeStatusFilter === 'waiting_destination') {
                  filtered = filtered.filter(t => t.status === 'waiting_destination');
              } else if (this.activeStatusFilter === 'in_progress') {
                  filtered = filtered.filter(t => t.status !== 'waiting_destination' && t.status !== 'done');
              } else if (this.activeStatusFilter === 'completed') {
                  filtered = filtered.filter(t => t.status === 'done');
              }

              if (!this.searchQuery) return filtered;
              const q = this.searchQuery.toLowerCase();
              return filtered.filter(t => 
                  t.label.toLowerCase().includes(q) ||
                  t.source.toLowerCase().includes(q) ||
                  t.destination.toLowerCase().includes(q) ||
                  t.statusLabel.toLowerCase().includes(q)
              );
          },
          newTicket: {
              label: '',
              source_device: '',
              destination_device: '',
              source_tenant_id: '9b1deb4d-3b7d-4bad-9bdd-2b0d7b3dcb6d',
              destination_tenant_id: 'a1b2c3d4-e5f6-7a8b-9c0d-1e2f3a4b5c6d',
              connector_type: 'LC-LC',
              length: 10,
              color: 'Yellow'
          },
          async createTicket() {
              try {
                  const response = await fetch('/api/tickets', {
                      method: 'POST',
                      headers: {
                          'Content-Type': 'application/json',
                          'Accept': 'application/json',
                          'X-CSRF-TOKEN': '{{ csrf_token() }}'
                      },
                      body: JSON.stringify({
                          label: this.newTicket.label,
                          source_device: this.newTicket.source_device,
                          destination_device: this.newTicket.destination_device,
                          source_tenant_id: this.newTicket.source_tenant_id,
                          destination_tenant_id: this.newTicket.destination_tenant_id,
                          connector_type: this.newTicket.connector_type,
                          cable_details: {
                              length: parseInt(this.newTicket.length),
                              color: this.newTicket.color
                          }
                      })
                  });

                  if (!response.ok) {
                      const err = await response.json();
                      alert('Failed to create ticket: ' + (err.message || 'Validation error'));
                      return;
                  }

                  window.location.reload();
              } catch (e) {
                  alert('Error communicating with server.');
              }
          }
      }"
      :class="theme" 
      class="h-full antialiased">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Technical Ticket Network - Dashboard</title>
    
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
    <header x-data="{ mobileMenuOpen: false, dropdownOpen: false }" class="border-b border-zinc-200 dark:border-zinc-800 bg-white/80 dark:bg-zinc-950/80 backdrop-blur-md sticky top-0 z-50 transition-colors">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
            <!-- Left side: Brand Logo & Navigation -->
            <div class="flex items-center gap-8">
                <!-- Logo -->
                <a href="/" class="flex items-center gap-3 hover:opacity-95 transition-opacity group">
                    <div class="relative w-9 h-9 rounded-xl bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 flex items-center justify-center shadow-md group-hover:border-red-500/50 transition-all duration-300">
                        <!-- Glow background -->
                        <div class="absolute inset-0 bg-gradient-to-tr from-red-600/10 to-rose-600/10 dark:from-red-600/20 dark:to-rose-600/20 rounded-xl blur-sm opacity-50 group-hover:opacity-100 group-hover:blur-md transition-all duration-300"></div>
                        
                        <!-- Logo Graphic -->
                        <svg class="relative w-6 h-6 text-red-600 dark:text-red-400 group-hover:text-rose-500 dark:group-hover:text-rose-300 transition-colors duration-300" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
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
                    <span class="font-display font-semibold text-lg tracking-tight text-zinc-900 dark:text-zinc-100 group-hover:text-red-600 dark:group-hover:text-red-300 transition-colors hidden sm:inline">Technical Ticket Network</span>
                </a>
                
                <!-- Desktop Navigation Links (hidden on mobile) -->
                <nav class="hidden md:flex items-center gap-1.5 border-l border-zinc-200 dark:border-zinc-800 pl-6 h-8">
                    <a href="/" class="text-sm font-semibold px-3 py-1.5 rounded-lg transition-colors text-red-600 dark:text-red-400 bg-red-50 dark:bg-red-500/10 shadow-sm">
                        Dashboard
                    </a>
                    @if (auth()->user()->hasRole('admin') || auth()->user()->hasRole('dest_manager'))
                        <a href="/users" class="text-sm font-semibold px-3 py-1.5 rounded-lg transition-colors text-zinc-600 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-zinc-200">
                            Users
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
                        
                        <!-- User Info Header -->
                        <div class="px-4 py-3.5 bg-zinc-50/50 dark:bg-zinc-900/50 text-left">
                            <p class="text-xs font-semibold text-zinc-400 dark:text-zinc-500 uppercase tracking-wider">Signed in as</p>
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
                        <div class="p-1.5">
                            <form action="{{ route('logout') }}" method="POST" class="w-full">
                                @csrf
                                <button type="submit" class="w-full text-left flex items-center gap-2 px-3 py-2 text-xs font-semibold text-zinc-700 dark:text-zinc-300 hover:text-red-600 dark:hover:text-red-400 hover:bg-red-50 dark:hover:bg-red-500/10 rounded-lg transition-colors cursor-pointer">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4 text-zinc-400 dark:text-zinc-500 group-hover:text-current">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15M12 9l-3 3m0 0 3 3m-3-3h12.75" />
                                    </svg>
                                    Logout
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
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
                <a href="/" class="text-sm font-semibold px-4 py-2.5 rounded-xl transition-colors text-red-600 dark:text-red-400 bg-red-50 dark:bg-red-500/10">
                    Dashboard
                </a>
                @if (auth()->user()->hasRole('admin') || auth()->user()->hasRole('dest_manager'))
                    <a href="/users" class="text-sm font-semibold px-4 py-2.5 rounded-xl transition-colors text-zinc-655 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-zinc-200">
                        Users
                    </a>
                @endif
            </nav>

            <!-- Profile Info & Logout -->
            <div class="border-t border-zinc-200 dark:border-zinc-800 pt-4 flex items-center justify-between">
                <div class="flex flex-col">
                    <span class="text-sm font-bold text-zinc-900 dark:text-zinc-100">{{ auth()->user()->name }}</span>
                    <span class="text-xs text-zinc-500 dark:text-zinc-400 font-semibold capitalize">{{ str_replace('_', ' ', auth()->user()->role) }}</span>
                </div>
                <form action="{{ route('logout') }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" class="bg-white hover:bg-zinc-100 dark:bg-zinc-900 dark:hover:bg-zinc-800 text-xs font-semibold text-zinc-700 dark:text-zinc-300 px-3.5 py-2 rounded-lg border border-zinc-200 dark:border-zinc-800 shadow-sm">
                        Logout
                    </button>
                </form>
            </div>
        </div>
    </header>

    <!-- Main Container -->
    <main class="flex-1 mx-auto max-w-7xl w-full px-4 sm:px-6 lg:px-8 py-8 flex flex-col gap-8">
        
        <!-- Header Section -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-3xl font-extrabold text-zinc-900 dark:text-white tracking-tight">Main Dashboard</h1>
                <p class="text-zinc-500 dark:text-zinc-400 text-sm mt-1">Manage, monitor, and deploy equipment and connection cables.</p>
            </div>
            <button @click="showCreateModal = true" 
                    class="bg-red-600 hover:bg-red-700 dark:hover:bg-red-500 active:scale-95 text-sm font-semibold text-white px-5 py-2.5 rounded-xl transition-all shadow-lg shadow-red-600/10 cursor-pointer self-start sm:self-auto">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4 inline-block mr-1">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                Create New Ticket
            </button>
        </div>

        <!-- Statistics Panel -->
        <section class="grid grid-cols-2 lg:grid-cols-4 gap-4 md:gap-6">
            <!-- Total Tickets Card -->
            <div @click="activeStatusFilter = 'all'" 
                 class="rounded-2xl border p-5 shadow-sm transition-all duration-300 cursor-pointer select-none hover:-translate-y-1 hover:bg-zinc-100/50 dark:hover:bg-zinc-800/40"
                 :class="activeStatusFilter === 'all' ? 'border-red-500 bg-red-50/50 dark:bg-red-950/20 shadow-md shadow-red-500/10' : 'border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900/60'">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold uppercase tracking-widest transition-colors"
                          :class="activeStatusFilter === 'all' ? 'text-red-600 dark:text-red-400 font-bold' : 'text-zinc-500'">Total Tickets</span>
                    <span class="w-1.5 h-1.5 rounded-full bg-red-500 animate-pulse" x-show="activeStatusFilter === 'all'"></span>
                </div>
                <span class="text-3xl font-extrabold text-zinc-900 dark:text-white block mt-1 font-display">{{ $tickets->count() }}</span>
            </div>
            
            <!-- Waiting Dest Card -->
            <div @click="activeStatusFilter = 'waiting_destination'" 
                 class="rounded-2xl border p-5 shadow-sm transition-all duration-300 cursor-pointer select-none hover:-translate-y-1 hover:bg-zinc-100/50 dark:hover:bg-zinc-800/40"
                 :class="activeStatusFilter === 'waiting_destination' ? 'border-indigo-500 bg-indigo-50/50 dark:bg-indigo-950/20 shadow-md shadow-indigo-500/10' : 'border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900/60'">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold uppercase tracking-widest transition-colors"
                          :class="activeStatusFilter === 'waiting_destination' ? 'text-indigo-600 dark:text-indigo-400 font-bold' : 'text-zinc-500'">Waiting Dest</span>
                    <span class="w-1.5 h-1.5 rounded-full bg-indigo-500 animate-pulse" x-show="activeStatusFilter === 'waiting_destination'"></span>
                </div>
                <span class="text-3xl font-extrabold text-indigo-600 dark:text-indigo-400 block mt-1 font-display">
                    {{ $tickets->where('status', 'waiting_destination')->count() }}
                </span>
            </div>

            <!-- In Progress Card -->
            <div @click="activeStatusFilter = 'in_progress'" 
                 class="rounded-2xl border p-5 shadow-sm transition-all duration-300 cursor-pointer select-none hover:-translate-y-1 hover:bg-zinc-100/50 dark:hover:bg-zinc-800/40"
                 :class="activeStatusFilter === 'in_progress' ? 'border-amber-500 bg-amber-50/50 dark:bg-amber-950/20 shadow-md shadow-amber-500/10' : 'border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900/60'">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold uppercase tracking-widest transition-colors"
                          :class="activeStatusFilter === 'in_progress' ? 'text-amber-600 dark:text-amber-400 font-bold' : 'text-zinc-500'">In Progress</span>
                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse" x-show="activeStatusFilter === 'in_progress'"></span>
                </div>
                <span class="text-3xl font-extrabold text-amber-600 dark:text-amber-500 block mt-1 font-display">
                    {{ $tickets->whereNotIn('status', ['waiting_destination', 'done'])->count() }}
                </span>
            </div>

            <!-- Completed Card -->
            <div @click="activeStatusFilter = 'completed'" 
                 class="rounded-2xl border p-5 shadow-sm transition-all duration-300 cursor-pointer select-none hover:-translate-y-1 hover:bg-zinc-100/50 dark:hover:bg-zinc-800/40"
                 :class="activeStatusFilter === 'completed' ? 'border-emerald-500 bg-emerald-50/50 dark:bg-emerald-950/20 shadow-md shadow-emerald-500/10' : 'border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900/60'">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold uppercase tracking-widest transition-colors"
                          :class="activeStatusFilter === 'completed' ? 'text-emerald-600 dark:text-emerald-400 font-bold' : 'text-zinc-500'">Completed</span>
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse" x-show="activeStatusFilter === 'completed'"></span>
                </div>
                <span class="text-3xl font-extrabold text-emerald-600 dark:text-emerald-400 block mt-1 font-display">
                    {{ $tickets->where('status', 'done')->count() }}
                </span>
            </div>
        </section>

        <!-- Ticket List Panel -->
        <section class="rounded-2xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900/60 backdrop-blur-xl p-5 md:p-8 shadow-sm dark:shadow-2xl transition-colors">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
                <h2 class="text-xl font-bold flex items-center gap-2 text-zinc-900 dark:text-white">
                    <span class="w-2 h-5 rounded bg-red-600 inline-block"></span>
                    Ticket Queue & Statuses
                </h2>
                <div class="relative w-full sm:w-72">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-zinc-400 dark:text-zinc-500">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.637 10.637Z" />
                        </svg>
                    </div>
                    <input type="text" x-model="searchQuery" placeholder="Search by label, device, or status..."
                           class="w-full bg-zinc-100 dark:bg-zinc-900/70 border border-zinc-200 dark:border-zinc-800 rounded-xl pl-10 pr-4 py-2.5 text-xs text-zinc-900 dark:text-white focus:outline-none focus:border-red-600 dark:focus:border-red-600 transition-colors placeholder-zinc-500">
                </div>
            </div>

            <!-- Desktop Table View (hidden on mobile screens) -->
            <div class="hidden md:block overflow-x-auto">
                <table class="w-full border-collapse text-left">
                    <thead>
                        <tr class="border-b border-zinc-200 dark:border-zinc-700 text-xs font-bold text-zinc-600 dark:text-zinc-400 uppercase tracking-wider">
                            <th class="py-3 px-4">Label</th>
                            <th class="py-3 px-4">Source Device</th>
                            <th class="py-3 px-4">Destination Device</th>
                            <th class="py-3 px-4">Connector</th>
                            <th class="py-3 px-4">Current Status</th>
                            <th class="py-3 px-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-200 dark:divide-zinc-800 text-sm text-zinc-800 dark:text-zinc-200">
                        <template x-for="t in filteredTickets()" :key="t.id">
                            <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/50 transition-colors duration-200">
                                <td class="py-4 px-4 font-bold text-zinc-900 dark:text-white select-all" x-text="t.label"></td>
                                <td class="py-4 px-4 text-zinc-700 dark:text-zinc-300" x-text="t.source"></td>
                                <td class="py-4 px-4 text-zinc-700 dark:text-zinc-300" x-text="t.destination"></td>
                                <td class="py-4 px-4 font-mono text-xs text-zinc-500 dark:text-zinc-400" x-text="t.connector"></td>
                                <td class="py-4 px-4">
                                    <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold border capitalize"
                                          :class="{
                                              'bg-indigo-50 dark:bg-indigo-500/10 text-indigo-700 dark:text-indigo-400 border-indigo-200 dark:border-indigo-500/20': t.status === 'waiting_destination',
                                              'bg-cyan-50 dark:bg-cyan-500/10 text-cyan-700 dark:text-cyan-400 border-cyan-200 dark:border-cyan-500/20': t.status === 'approved_destination',
                                              'bg-emerald-50 dark:bg-emerald-500/10 text-emerald-700 dark:text-emerald-400 border-emerald-200 dark:border-emerald-500/20': t.status === 'approved_admin',
                                              'bg-amber-50 dark:bg-amber-500/10 text-amber-700 dark:text-amber-400 border-amber-200 dark:border-amber-500/20': t.status === 'sended_cable',
                                              'bg-orange-50 dark:bg-orange-500/10 text-orange-700 dark:text-orange-400 border-orange-200 dark:border-orange-500/20': t.status === 'received_cable',
                                              'bg-red-50 dark:bg-red-500/10 text-red-700 dark:text-red-400 border-red-200 dark:border-red-500/20': t.status === 'done',
                                              'bg-zinc-50 dark:bg-zinc-500/10 text-zinc-700 dark:text-zinc-400 border-zinc-200 dark:border-zinc-500/20': t.status === 'cancelled'
                                          }"
                                          x-text="t.statusLabel">
                                    </span>
                                </td>
                                <td class="py-4 px-4 text-right font-display">
                                    <a :href="'/tickets/' + t.id" 
                                       class="inline-flex items-center gap-1.5 text-xs font-semibold text-red-600 hover:text-red-750 dark:text-red-400 dark:hover:text-red-300 border border-red-200 dark:border-red-500/20 hover:border-red-500/50 bg-red-500/5 px-3 py-1.5 rounded-lg transition-all">
                                        Manage Details
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-3.5 h-3.5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                                        </svg>
                                    </a>
                                </td>
                            </tr>
                        </template>
                        <!-- Search/Filter empty state desktop -->
                        <tr x-show="filteredTickets().length === 0 && searchQuery !== ''">
                            <td colspan="6" class="py-12 text-center text-zinc-500 dark:text-zinc-500">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-12 h-12 mx-auto text-zinc-400 dark:text-zinc-700 mb-3">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.637 10.637Z" />
                                </svg>
                                No matching tickets found for "<span class="text-zinc-800 dark:text-zinc-300 font-medium" x-text="searchQuery"></span>".
                            </td>
                        </tr>
                        <!-- DB empty state desktop -->
                        <tr x-show="filteredTickets().length === 0 && searchQuery === ''">
                            <td colspan="6" class="py-12 text-center text-zinc-500 dark:text-zinc-500">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-12 h-12 mx-auto text-zinc-400 dark:text-zinc-700 mb-3">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 6v.75m0 3v.75m0 3v.75m0 3V18m-9-5.25h5.25M7.5 15h3M3.375 5.25c-.621 0-1.125.504-1.125 1.125v3.026a2.999 2.999 0 0 1 0 5.198v3.026c0 .621.504 1.125 1.125 1.125h17.25c.621 0 1.125-.504 1.125-1.125v-3.026a2.999 2.999 0 0 1 0-5.198V6.375c0-.621-.504-1.125-1.125-1.125H3.375Z" />
                                </svg>
                                No tickets found. Please create one to start.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Mobile Stacked Card View (only visible on mobile screens) -->
            <div class="block md:hidden space-y-4">
                <template x-for="t in filteredTickets()" :key="t.id">
                    <div class="rounded-xl border border-zinc-200 dark:border-zinc-800 bg-zinc-50/50 dark:bg-zinc-900/50 p-4 shadow-sm flex flex-col gap-3">
                        <div class="flex items-center justify-between">
                            <span class="font-bold text-sm text-zinc-900 dark:text-white" x-text="t.label"></span>
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold border capitalize"
                                  :class="{
                                      'bg-indigo-50 dark:bg-indigo-500/10 text-indigo-700 dark:text-indigo-400 border-indigo-200 dark:border-indigo-500/20': t.status === 'waiting_destination',
                                      'bg-cyan-50 dark:bg-cyan-500/10 text-cyan-700 dark:text-cyan-400 border-cyan-200 dark:border-cyan-500/20': t.status === 'approved_destination',
                                      'bg-emerald-50 dark:bg-emerald-500/10 text-emerald-700 dark:text-emerald-400 border-emerald-200 dark:border-emerald-500/20': t.status === 'approved_admin',
                                      'bg-amber-50 dark:bg-amber-500/10 text-amber-700 dark:text-amber-400 border-amber-200 dark:border-amber-500/20': t.status === 'sended_cable',
                                      'bg-orange-50 dark:bg-orange-500/10 text-orange-700 dark:text-orange-400 border-orange-200 dark:border-orange-500/20': t.status === 'received_cable',
                                      'bg-red-50 dark:bg-red-500/10 text-red-700 dark:text-red-400 border-red-200 dark:border-red-500/20': t.status === 'done',
                                      'bg-zinc-50 dark:bg-zinc-500/10 text-zinc-700 dark:text-zinc-400 border-zinc-200 dark:border-zinc-500/20': t.status === 'cancelled'
                                  }"
                                  x-text="t.statusLabel">
                            </span>
                        </div>
                        
                        <div class="grid grid-cols-2 gap-2 text-xs text-zinc-500 dark:text-zinc-400 pt-2 border-t border-zinc-200 dark:border-zinc-800">
                            <div>
                                <span class="block text-[9px] text-zinc-400 dark:text-zinc-500 uppercase font-semibold">Source Device</span>
                                <span class="font-medium text-zinc-700 dark:text-zinc-200" x-text="t.source"></span>
                            </div>
                            <div>
                                <span class="block text-[9px] text-zinc-400 dark:text-zinc-500 uppercase font-semibold">Dest Device</span>
                                <span class="font-medium text-zinc-700 dark:text-zinc-200" x-text="t.destination"></span>
                            </div>
                            <div>
                                <span class="block text-[9px] text-zinc-400 dark:text-zinc-500 uppercase font-semibold">Connector Type</span>
                                <span class="font-mono text-zinc-700 dark:text-zinc-300" x-text="t.connector"></span>
                            </div>
                        </div>
                        
                        <div class="border-t border-zinc-200 dark:border-zinc-800 pt-3 flex justify-end font-display">
                            <a :href="'/tickets/' + t.id" 
                               class="inline-flex items-center gap-1 text-xs font-semibold text-red-600 dark:text-red-400 hover:text-red-750 dark:hover:text-red-300">
                                Manage Details
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-3.5 h-3.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                                </svg>
                            </a>
                        </div>
                    </div>
                </template>
                <!-- Mobile search fallback -->
                <div x-show="filteredTickets().length === 0" class="py-8 text-center text-zinc-500">
                    No tickets found matching current filters.
                </div>
            </div>
        </section>
        
    </main>

    <!-- Create Ticket Modal -->
    <div x-show="showCreateModal" 
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-zinc-950/80 backdrop-blur-sm"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95"
         style="display: none;">
        
        <div class="relative w-full max-w-lg bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-2xl p-6 shadow-2xl flex flex-col gap-6"
             @click.away="showCreateModal = false">
            
            <div class="flex items-center justify-between pb-3 border-b border-zinc-200 dark:border-zinc-800">
                <h3 class="text-lg font-bold text-zinc-900 dark:text-white">Create New Deployment Ticket</h3>
                <button @click="showCreateModal = false" class="text-zinc-500 hover:text-zinc-700 dark:hover:text-zinc-300 cursor-pointer">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <form @submit.prevent="createTicket" class="space-y-4">
                <div>
                    <label class="text-xs text-zinc-500 dark:text-zinc-400 font-semibold block mb-1">Ticket Label (Unique)</label>
                    <input type="text" x-model="newTicket.label" required placeholder="e.g. TICKET-101"
                           class="w-full bg-zinc-50 dark:bg-zinc-950 border border-zinc-200 dark:border-zinc-800 rounded-lg px-3 py-2 text-sm text-zinc-900 dark:text-white focus:outline-none focus:border-red-600 transition-colors">
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="text-xs text-zinc-500 dark:text-zinc-400 font-semibold block mb-1">Source Device</label>
                        <input type="text" x-model="newTicket.source_device" required placeholder="e.g. JKT-SW-01"
                               class="w-full bg-zinc-50 dark:bg-zinc-950 border border-zinc-200 dark:border-zinc-800 rounded-lg px-3 py-2 text-sm text-zinc-900 dark:text-white focus:outline-none focus:border-red-600 transition-colors">
                    </div>
                    <div>
                        <label class="text-xs text-zinc-500 dark:text-zinc-400 font-semibold block mb-1">Destination Device</label>
                        <input type="text" x-model="newTicket.destination_device" required placeholder="e.g. SG-SW-02"
                               class="w-full bg-zinc-50 dark:bg-zinc-950 border border-zinc-200 dark:border-zinc-800 rounded-lg px-3 py-2 text-sm text-zinc-900 dark:text-white focus:outline-none focus:border-red-600 transition-colors">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="text-xs text-zinc-500 dark:text-zinc-400 font-semibold block mb-1">Connector Type</label>
                        <select x-model="newTicket.connector_type"
                                class="w-full bg-zinc-50 dark:bg-zinc-950 border border-zinc-200 dark:border-zinc-800 rounded-lg px-3 py-2 text-sm text-zinc-900 dark:text-white focus:outline-none focus:border-red-600 transition-colors">
                            <option value="LC-LC">LC-LC</option>
                            <option value="SC-SC">SC-SC</option>
                            <option value="FC-FC">FC-FC</option>
                            <option value="RJ45">RJ45</option>
                        </select>
                    </div>
                    <div>
                        <label class="text-xs text-zinc-500 dark:text-zinc-400 font-semibold block mb-1">Cable Length (m)</label>
                        <input type="number" x-model="newTicket.length" required
                               class="w-full bg-zinc-50 dark:bg-zinc-950 border border-zinc-200 dark:border-zinc-800 rounded-lg px-3 py-2 text-sm text-zinc-900 dark:text-white focus:outline-none focus:border-red-600 transition-colors">
                    </div>
                </div>

                <div>
                    <label class="text-xs text-zinc-500 dark:text-zinc-400 font-semibold block mb-1">Cable Color</label>
                    <input type="text" x-model="newTicket.color" required placeholder="e.g. Yellow, Aqua"
                           class="w-full bg-zinc-50 dark:bg-zinc-950 border border-zinc-200 dark:border-zinc-800 rounded-lg px-3 py-2 text-sm text-zinc-900 dark:text-white focus:outline-none focus:border-red-600 transition-colors">
                </div>

                <div class="flex justify-end gap-3 pt-4 border-t border-zinc-200 dark:border-zinc-800">
                    <button type="button" @click="showCreateModal = false"
                            class="bg-zinc-100 hover:bg-zinc-200 dark:bg-zinc-800 dark:hover:bg-zinc-700 text-xs font-semibold px-4 py-2.5 rounded-lg text-zinc-700 dark:text-zinc-350 transition-colors cursor-pointer">
                        Cancel
                    </button>
                    <button type="submit"
                            class="bg-red-600 hover:bg-red-750 dark:hover:bg-red-500 text-xs font-semibold px-4 py-2.5 rounded-lg text-white transition-colors cursor-pointer">
                        Submit Ticket
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Footer -->
    <footer class="border-t border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-950 py-6 text-center text-xs text-zinc-500 dark:text-zinc-600 mt-auto transition-colors">
        &copy; 2026 Technical Ticket Network by Sidiq Setyadji.
    </footer>
</body>
</html>
