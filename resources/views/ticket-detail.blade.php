<!DOCTYPE html>
<html lang="en" x-data="{ theme: localStorage.getItem('theme') || 'dark' }" :class="theme" class="h-full antialiased">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ticket #{{ $ticket->id }} - {{ $ticket->label }}</title>
    
    <!-- Google Fonts: Inter & Outfit -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Outfit:wght@500;600;700&display=swap" rel="stylesheet">
    
    <!-- Alpine.js CDN for Interactivity -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <!-- Tailwind CSS v4 Browser CDN for instant load without compilation -->
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
<body class="min-h-screen flex flex-col bg-zinc-50 dark:bg-zinc-950 text-zinc-900 dark:text-zinc-50 transition-colors duration-300" 
      x-data="{
          currentRole: '{{ auth()->user() ? auth()->user()->role : 'admin' }}',
          currentStatus: '{{ $ticket->status }}',
          ticketLogs: [
              @foreach($ticket->logs as $log)
              {
                  id: {{ $log->id }},
                  from: '{{ $log->from_state }}',
                  to: '{{ $log->to_state }}',
                  user: '{{ $log->user->name ?? 'System' }}',
                  role: '{{ $log->user->role ?? 'user' }}',
                  time: '{{ $log->created_at->format('H:i') }}',
                  date: '{{ $log->created_at->format('d M Y') }}'
              },
              @endforeach
          ],
          stages: [
              { key: 'waiting_destination', label: 'Waiting Dest', color: 'indigo' },
              { key: 'approved_destination', label: 'Approved Dest', color: 'cyan' },
              { key: 'approved_admin', label: 'Approved Admin', color: 'emerald' },
              { key: 'sended_cable', label: 'Sended Cable', color: 'amber' },
              { key: 'received_cable', label: 'Received Cable', color: 'orange' },
              { key: 'done', label: 'Completed', color: 'red' }
          ],
          
          showEditModal: false,
          @if (!$isPublic)
          editTicket: {
              label: '{{ $ticket->label }}',
              source_device: '{{ $ticket->source_device }}',
              destination_device: '{{ $ticket->destination_device }}',
              source_tenant_id: '{{ $ticket->source_tenant_id }}',
              destination_tenant_id: '{{ $ticket->destination_tenant_id }}',
              connector_type: '{{ $ticket->connector_type }}',
              length: {{ $ticket->cable_details['length'] ?? 0 }},
              color: '{{ $ticket->cable_details['color'] ?? '' }}'
          },
          @endif
          
          getStageIndex(status) {
              return this.stages.findIndex(s => s.key === status);
          },
          
          isCompleted(status) {
              return this.getStageIndex(this.currentStatus) >= this.getStageIndex(status);
          },

          isActive(status) {
              return this.currentStatus === status && status !== 'done';
          },

          getExecutor(status) {
              // Find the log that transitioned to this status
              const log = this.ticketLogs.find(l => l.to === status);
              if (log) {
                  return { name: log.user, time: log.time, date: log.date };
              }
              // Default/Initial stage executor is ticket creator
              if (status === 'waiting_destination') {
                  return { name: 'Staff Creator', time: '08:00', date: '17 Jun 2026' };
              }
              return null;
          },

          async transitionStatus(nextStatus) {
              try {
                  const response = await fetch(`/api/tickets/{{ $ticket->id }}/status`, {
                      method: 'PATCH',
                      headers: {
                          'Content-Type': 'application/json',
                          'Accept': 'application/json',
                          'X-CSRF-TOKEN': '{{ csrf_token() }}'
                      },
                      body: JSON.stringify({ status: nextStatus })
                  });
                  
                  if (!response.ok) {
                      const data = await response.json();
                      alert('Transition failed: ' + (data.message || 'Unauthorized'));
                      return;
                  }
                  
                  const data = await response.json();
                  this.currentStatus = data.status;
                  
                  // Reload page to refresh logs or append log dynamically
                  window.location.reload();
              } catch (e) {
                  alert('Error communicating with server.');
              }
          },

          @if (!$isPublic)
          async updateTicket() {
              try {
                  const response = await fetch(`/api/tickets/{{ $ticket->id }}`, {
                      method: 'PATCH',
                      headers: {
                          'Content-Type': 'application/json',
                          'Accept': 'application/json',
                          'X-CSRF-TOKEN': '{{ csrf_token() }}'
                      },
                      body: JSON.stringify({
                          label: this.editTicket.label,
                          source_device: this.editTicket.source_device,
                          destination_device: this.editTicket.destination_device,
                          source_tenant_id: this.editTicket.source_tenant_id,
                          destination_tenant_id: this.editTicket.destination_tenant_id,
                          connector_type: this.editTicket.connector_type,
                          cable_details: {
                              length: parseInt(this.editTicket.length),
                              color: this.editTicket.color
                          }
                      })
                  });

                  if (!response.ok) {
                      const err = await response.json();
                      alert('Failed to update ticket: ' + (err.message || 'Validation error'));
                      return;
                  }

                  window.location.reload();
              } catch (e) {
                  alert('Error communicating with server.');
              }
          }
          @endif
      }">

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
                @if (!($isPublic ?? false))
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
                @endif
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

                @if ($isPublic)
                    <a href="{{ route('login') }}" class="bg-red-600 hover:bg-red-500 active:scale-95 text-xs font-semibold text-white px-4 py-2 rounded-xl transition-all shadow-lg flex items-center gap-2 cursor-pointer border border-red-500/25">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15m3 0 3-3m0 0-3-3m3 3H9" />
                        </svg>
                        Sign In
                    </a>
                @else
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
                @endif
            </div>

            <!-- Mobile Controls: Theme + Burger Toggle / Sign In (hidden on desktop) -->
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

                @if ($isPublic)
                    <a href="{{ route('login') }}" class="bg-red-600 hover:bg-red-500 active:scale-95 text-xs font-semibold text-white px-3.5 py-1.5 rounded-xl transition-all shadow-md flex items-center gap-1.5 cursor-pointer border border-red-500/25">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-3.5 h-3.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15m3 0 3-3m0 0-3-3m3 3H9" />
                        </svg>
                        Sign In
                    </a>
                @else
                    <!-- Burger Menu Toggle Button -->
                    <button @click="mobileMenuOpen = !mobileMenuOpen" 
                            class="w-9 h-9 rounded-xl border border-zinc-200 dark:border-zinc-800 bg-white/80 dark:bg-zinc-900/50 text-zinc-600 dark:text-zinc-400 flex items-center justify-center transition-all cursor-pointer shadow-sm">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
                            <path x-show="!mobileMenuOpen" stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                            <path x-show="mobileMenuOpen" stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" style="display: none;" />
                        </svg>
                    </button>
                @endif
            </div>
        </div>

        @if (!($isPublic ?? false))
            <!-- Mobile Drawer/Menu (hidden on desktop) -->
            <div x-show="mobileMenuOpen" 
                 x-transition:enter="transition ease-out duration-150"
                 x-transition:enter-start="opacity-0 -translate-y-2"
                 x-transition:enter-end="opacity-100 translate-y-0"
                 x-transition:leave="transition ease-in duration-100"
                 x-transition:leave-start="opacity-100 translate-y-0"
                 x-transition:leave-end="opacity-0 -translate-y-2"
                 class="md:hidden border-t border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-800 px-4 py-4 space-y-4 shadow-lg"
                 style="display: none;">
                <!-- Navigation Tabs Stacked -->
                <nav class="flex flex-col gap-1">
                    <a href="/" class="text-sm font-semibold px-4 py-2.5 rounded-xl transition-colors text-red-600 dark:text-red-400 bg-red-50 dark:bg-red-500/10 shadow-sm">
                        Dashboard
                    </a>
                    @if (auth()->user()->hasRole('admin') || auth()->user()->hasRole('dest_manager'))
                        <a href="/users" class="text-sm font-semibold px-4 py-2.5 rounded-xl transition-colors text-zinc-600 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-zinc-200">
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
        @endif
    </header>

    <!-- Main Container -->
    <main class="flex-1 mx-auto max-w-7xl w-full px-4 sm:px-6 lg:px-8 py-8 flex flex-col gap-6">
        
        <!-- Back Navigation / Breadcrumb -->
        @if (!$isPublic)
            <div class="flex items-center">
                <a href="/" class="inline-flex items-center gap-1.5 text-xs font-semibold text-zinc-600 hover:text-zinc-950 dark:text-zinc-400 dark:hover:text-zinc-200 transition-colors bg-white dark:bg-zinc-900/40 border border-zinc-200 dark:border-zinc-800/80 px-3 py-1.5 rounded-lg shadow-sm">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-3.5 h-3.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5" />
                    </svg>
                    Back to Dashboard
                </a>
            </div>
        @endif

        <!-- Canceled Alert Banner -->
        <template x-if="currentStatus === 'cancelled'">
            <div class="rounded-2xl border border-red-200 dark:border-red-500/20 bg-red-50 dark:bg-red-500/5 backdrop-blur-xl p-5 flex items-start gap-4 shadow-lg select-none animate-pulse">
                <div class="w-8 h-8 rounded-lg bg-red-100 dark:bg-red-500/10 text-red-600 dark:text-red-400 flex items-center justify-center shrink-0">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m9.75 9.75 4.5 4.5m0-4.5-4.5 4.5M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                    </svg>
                </div>
                <div>
                    <h3 class="text-sm font-bold text-red-700 dark:text-red-400">This ticket has been cancelled</h3>
                    <p class="text-xs text-zinc-600 dark:text-zinc-400 mt-1">This ticket is inactive. Further status transitions and details editing are permanently locked.</p>
                </div>
            </div>
        </template>
        
        <!-- Ticket Header Card -->
        <section class="relative overflow-hidden rounded-2xl border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900/60 backdrop-blur-xl p-6 md:p-8 flex flex-col md:flex-row md:items-center justify-between gap-6 shadow-md dark:shadow-2xl transition-colors">
            <div class="absolute inset-0 bg-gradient-to-tr from-red-500/5 to-transparent pointer-events-none"></div>
            <div>
                <div class="flex items-center gap-3 mb-2">
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold tracking-wide bg-red-100 dark:bg-red-500/10 text-red-700 dark:text-red-400 border border-red-200 dark:border-red-500/20 uppercase">
                        Ticket Detail
                    </span>
                    <span class="text-zinc-400 dark:text-zinc-500">•</span>
                    <span class="text-zinc-500 dark:text-zinc-400 text-sm">Updated {{ $ticket->updated_at->diffForHumans() }}</span>
                </div>
                <h1 class="text-3xl md:text-4xl font-bold tracking-tight text-zinc-900 dark:text-white mb-2">
                    {{ $ticket->label }}
                </h1>
                <p class="text-zinc-600 dark:text-zinc-400 text-sm md:text-base max-w-2xl">
                    @if ($isPublic)
                        Technical ticket for optical connection. <span class="text-zinc-500 dark:text-zinc-400 font-medium">Log in to view devices.</span>
                    @else
                        Technical ticket for optical connection from <span class="text-zinc-900 dark:text-zinc-100 font-semibold">{{ $ticket->source_device }}</span> to <span class="text-zinc-900 dark:text-zinc-100 font-semibold">{{ $ticket->destination_device }}</span>.
                    @endif
                </p>
            </div>
            
            <!-- Quick Status & Actions -->
            <div class="flex flex-col sm:items-end gap-3 shrink-0">
                <div class="text-right">
                    <span class="text-xs text-zinc-500 dark:text-zinc-500 uppercase tracking-widest font-semibold block mb-1">Current State</span>
                    <span :class="{
                        'bg-indigo-50 dark:bg-indigo-500/10 text-indigo-700 dark:text-indigo-400 border-indigo-200 dark:border-indigo-500/20': currentStatus === 'waiting_destination',
                        'bg-cyan-50 dark:bg-cyan-500/10 text-cyan-700 dark:text-cyan-400 border-cyan-200 dark:border-cyan-500/20': currentStatus === 'approved_destination',
                        'bg-emerald-50 dark:bg-emerald-500/10 text-emerald-700 dark:text-emerald-400 border-emerald-200 dark:border-emerald-500/20': currentStatus === 'approved_admin',
                        'bg-amber-50 dark:bg-amber-500/10 text-amber-700 dark:text-amber-400 border-amber-200 dark:border-amber-500/20': currentStatus === 'sended_cable',
                        'bg-orange-50 dark:bg-orange-500/10 text-orange-700 dark:text-orange-400 border-orange-200 dark:border-orange-500/20': currentStatus === 'received_cable',
                        'bg-red-50 dark:bg-red-500/10 text-red-700 dark:text-red-400 border-red-200 dark:border-red-500/20': currentStatus === 'done',
                        'bg-zinc-50 dark:bg-zinc-500/10 text-zinc-700 dark:text-zinc-400 border-zinc-200 dark:border-zinc-500/20': currentStatus === 'cancelled'
                    }" class="inline-flex px-4 py-1.5 rounded-full text-sm font-semibold border capitalize tracking-wide shadow-sm" x-text="currentStatus.replace('_', ' ')"></span>
                </div>
                
                @if (!$isPublic)
                    <!-- Interactive Action Button (for testing transitions easily) -->
                    <div class="flex flex-wrap gap-2 justify-end">
                        @can('update', $ticket)
                            <button x-show="currentStatus !== 'done' && currentStatus !== 'cancelled'"
                                    @click="showEditModal = true"
                                    class="bg-white hover:bg-zinc-100 dark:bg-zinc-800 dark:hover:bg-zinc-700 border border-zinc-200 dark:border-zinc-700 active:scale-95 transition-all text-xs font-semibold px-4 py-2 rounded-lg text-zinc-700 dark:text-white shadow-sm cursor-pointer">
                                Edit Details
                            </button>
                        @endcan
                        @can('cancel', $ticket)
                            <button x-show="currentStatus !== 'done' && currentStatus !== 'cancelled'"
                                    @click="if(confirm('Are you sure you want to cancel this ticket? It cannot be used or modified after cancellation.')) transitionStatus('cancelled')"
                                    class="bg-red-50 dark:bg-red-950/80 hover:bg-red-100 dark:hover:bg-red-900 border border-red-200 dark:border-red-800 active:scale-95 transition-all text-xs font-semibold px-4 py-2 rounded-lg text-red-700 dark:text-red-200 shadow-sm cursor-pointer">
                                Cancel Ticket
                            </button>
                        @endcan

                        <!-- If Waiting Dest -> Approve Dest -->
                        <button x-show="currentStatus === 'waiting_destination' && (currentRole === 'dest_manager' || currentRole === 'admin')"
                                @click="transitionStatus('approved_destination')"
                                class="bg-cyan-600 hover:bg-cyan-500 active:scale-95 transition-all text-xs font-semibold px-4 py-2 rounded-lg text-white shadow-md cursor-pointer">
                            Approve Destination
                        </button>
                        <!-- If Approved Dest -> Approve Admin -->
                        <button x-show="currentStatus === 'approved_destination' && currentRole === 'admin'"
                                @click="transitionStatus('approved_admin')"
                                class="bg-emerald-600 hover:bg-emerald-500 active:scale-95 transition-all text-xs font-semibold px-4 py-2 rounded-lg text-white shadow-md cursor-pointer">
                            Approve Admin
                        </button>
                        <!-- If Approved Admin -> Send Cable -->
                        <button x-show="currentStatus === 'approved_admin' && currentRole === 'admin'"
                                @click="transitionStatus('sended_cable')"
                                class="bg-amber-600 hover:bg-amber-500 active:scale-95 transition-all text-xs font-semibold px-4 py-2 rounded-lg text-white shadow-md cursor-pointer">
                            Send Cable
                        </button>
                        <!-- If Sended Cable -> Receive Cable -->
                        <button x-show="currentStatus === 'sended_cable' && currentRole === 'admin'"
                                @click="transitionStatus('received_cable')"
                                class="bg-orange-600 hover:bg-orange-500 active:scale-95 transition-all text-xs font-semibold px-4 py-2 rounded-lg text-white shadow-md cursor-pointer">
                            Receive Cable
                        </button>
                        <!-- If Received Cable -> Mark Done -->
                        <button x-show="currentStatus === 'received_cable' && currentRole === 'admin'"
                                @click="transitionStatus('done')"
                                class="bg-red-600 hover:bg-red-500 active:scale-95 transition-all text-xs font-semibold px-4 py-2 rounded-lg text-white shadow-md cursor-pointer">
                            Mark Complete (Done)
                        </button>
                    </div>
                @endif
            </div>
        </section>

        <!-- Horizontal/Vertical Timeline Progress Bar -->
        <section class="rounded-2xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900/60 backdrop-blur-xl p-6 md:p-8 shadow-sm dark:shadow-xl overflow-hidden transition-colors">
            <!-- Header Section with Real-Time Progress Stat -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8">
                <h2 class="text-xl font-bold flex items-center gap-2 text-zinc-900 dark:text-white">
                    <span class="w-2 h-5 rounded bg-red-600 inline-block"></span>
                    Deployment Lifecycle Progression
                </h2>
                <!-- Dynamic Progress Stats Bar -->
                <div class="flex items-center gap-2 bg-zinc-50 dark:bg-zinc-950/50 border border-zinc-200 dark:border-zinc-800 px-3 py-1.5 rounded-xl self-start sm:self-auto shadow-inner">
                    <span class="text-xs font-semibold uppercase tracking-widest text-zinc-500 dark:text-zinc-400">Progress:</span>
                    <span class="px-2 py-0.5 rounded-full text-xs font-extrabold bg-red-100 dark:bg-red-500/10 text-red-600 dark:text-red-400 border border-red-200 dark:border-red-500/20 shadow-sm"
                          x-text="currentStatus === 'cancelled' ? 'Cancelled' : Math.round((getStageIndex(currentStatus) === -1 ? 0 : getStageIndex(currentStatus)) / (stages.length - 1) * 100) + '%'"></span>
                    <span class="text-xs text-zinc-400 dark:text-zinc-600">•</span>
                    <span class="text-xs text-zinc-700 dark:text-zinc-300 font-semibold capitalize" x-text="currentStatus.replace('_', ' ')"></span>
                </div>
            </div>
            
            <!-- Timeline Track Wrapper -->
            <div class="relative flex flex-col md:flex-row md:items-start md:justify-between gap-y-12 md:gap-y-0 px-4 md:px-0">
                <!-- Running Line Background (Desktop) -->
                <div class="hidden md:block absolute top-[18px] left-[20px] right-[20px] h-1 bg-zinc-200 dark:bg-zinc-800 rounded-full z-0">
                    <!-- Progress Fill Line -->
                    <div class="h-full bg-gradient-to-r from-red-600 via-rose-600 to-emerald-500 rounded-full transition-all duration-700 ease-out shadow-[0_0_8px_#ef4444]"
                         :style="{ width: (currentStatus === 'cancelled' ? 0 : (getStageIndex(currentStatus) === -1 ? 0 : getStageIndex(currentStatus)) / (stages.length - 1) * 100) + '%' }"></div>
                </div>

                <!-- Running Line Background (Mobile) -->
                <div class="block md:hidden absolute top-[20px] bottom-[20px] left-[36px] w-1 bg-zinc-200 dark:bg-zinc-800 rounded-full z-0">
                    <!-- Progress Fill Line -->
                    <div class="w-full bg-gradient-to-b from-red-600 via-rose-600 to-emerald-500 rounded-full transition-all duration-700 ease-out shadow-[0_0_8px_#ef4444]"
                         :style="{ height: (currentStatus === 'cancelled' ? 0 : (getStageIndex(currentStatus) === -1 ? 0 : getStageIndex(currentStatus)) / (stages.length - 1) * 100) + '%' }"></div>
                </div>

                <template x-for="(stage, index) in stages" :key="stage.key">
                    <div class="relative z-10 flex flex-row md:flex-col items-center md:items-center gap-4 md:gap-0 md:w-1/6 group">
                        
                        <!-- Timeline Dot -->
                        <div class="relative flex items-center justify-center shrink-0">
                            <!-- Outer Ring Pulse (Only active stage) -->
                            <div x-show="isActive(stage.key)" 
                                 class="absolute w-12 h-12 rounded-full bg-red-100 dark:bg-red-955/40 animate-ping opacity-35"></div>
                            
                            <!-- Circle Status Dot -->
                            <div :class="{
                                     'border-zinc-300 dark:border-zinc-700 bg-zinc-100 dark:bg-zinc-900 text-zinc-500 dark:text-zinc-400': !isCompleted(stage.key),
                                     'border-red-500 bg-white dark:bg-zinc-950 text-red-600 dark:text-red-400 shadow-[0_0_15px_rgba(239,68,68,0.15)] dark:shadow-[0_0_15px_rgba(239,68,68,0.3)]': isActive(stage.key),
                                     'border-emerald-500 bg-emerald-500 text-white dark:text-zinc-950': isCompleted(stage.key) && !isActive(stage.key)
                                 }"
                                 class="w-10 h-10 rounded-full border-2 flex items-center justify-center font-bold text-sm transition-all duration-500">
                                
                                <!-- Checkmark for completed steps -->
                                <template x-if="isCompleted(stage.key) && !isActive(stage.key)">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor" class="w-5 h-5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                                    </svg>
                                </template>
                                
                                <!-- Number/Index for current or future steps -->
                                <template x-if="isActive(stage.key) || !isCompleted(stage.key)">
                                    <span x-text="index + 1"></span>
                                </template>
                            </div>
                        </div>

                        <!-- Step Labels & Details -->
                        <div class="flex-1 md:text-center mt-0 md:mt-4">
                            <h3 :class="isCompleted(stage.key) ? 'text-zinc-800 dark:text-zinc-100 font-semibold' : 'text-zinc-500 dark:text-zinc-500 font-medium'"
                                class="text-sm md:text-base tracking-tight transition-colors duration-300"
                                x-text="stage.label"></h3>
                            
                            <!-- Execution Metadata (Triggered on status transition) -->
                            <div class="mt-1 flex flex-col md:items-center text-xs">
                                <template x-if="getExecutor(stage.key)">
                                    <div class="space-y-0.5">
                                        <span class="text-zinc-700 dark:text-zinc-200 block font-medium" x-text="getExecutor(stage.key).name"></span>
                                        <span class="text-zinc-500 dark:text-zinc-500 text-[10px]" x-text="getExecutor(stage.key).date + ' @ ' + getExecutor(stage.key).time"></span>
                                    </div>
                                </template>
                                <template x-if="!getExecutor(stage.key)">
                                    <span class="text-zinc-400 dark:text-zinc-650">Pending...</span>
                                </template>
                            </div>
                        </div>
                    </div>
                </template>
            </div>
        </section>

        <!-- Information details Grid -->
        <section class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            
            <!-- Source Device Card -->
            <div class="rounded-2xl border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900/60 backdrop-blur-xl p-6 shadow-md dark:shadow-lg flex flex-col gap-4 transition-colors">
                <div class="flex items-center gap-3 pb-3 border-b border-zinc-150 dark:border-zinc-800">
                    <div class="w-8 h-8 rounded-lg bg-indigo-500/10 text-indigo-500 dark:text-indigo-400 flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5.25 14.25h13.5m-13.5 0a3 3 0 0 1-3-3V7.5a3 3 0 0 1 3-3h13.5a3 3 0 0 1 3 3v3.75a3 3 0 0 1-3 3M5.25 14.25a3 3 0 0 0-3 3v2.25a3 3 0 0 0 3 3h13.5a3 3 0 0 0 3-3V17.25a3 3 0 0 0-3-3M6.75 7.75h.008v.008H6.75V7.75Zm0 3.5h.008v.008H6.75v-.008Zm0 3.5h.008v.008H6.75v-.008Z" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-zinc-900 dark:text-zinc-100">Source Device</h3>
                        <p class="text-xs text-zinc-500">Originating Equipment</p>
                    </div>
                </div>
                
                @if ($isPublic)
                    <div class="flex-1 flex flex-col items-center justify-center py-6 text-center relative overflow-hidden">
                        <!-- Blurred Mock Data Background -->
                        <div class="filter blur-md select-none opacity-20 pointer-events-none w-full space-y-2">
                            <div class="h-4 bg-zinc-700 rounded w-3/4 mx-auto"></div>
                            <div class="h-3 bg-zinc-700 rounded w-5/6 mx-auto"></div>
                        </div>
                        
                        <!-- Lock Centered Overlay -->
                        <div class="absolute inset-0 flex flex-col items-center justify-center p-4 bg-zinc-50/40 dark:bg-zinc-950/20 backdrop-blur-[2px] rounded-xl">
                            <div class="w-8 h-8 rounded-full bg-zinc-100 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-800 flex items-center justify-center text-zinc-500 dark:text-zinc-400 mb-1.5 shadow-inner">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-3.5 h-3.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" />
                                </svg>
                            </div>
                            <span class="text-xs font-semibold text-zinc-800 dark:text-zinc-300">Technical Data Locked</span>
                            <p class="text-[10px] text-zinc-500 mt-0.5 max-w-[200px]">Sign in to view equipment name and Tenant ID.</p>
                        </div>
                    </div>
                @else
                    <div class="space-y-4">
                        <div>
                            <label class="text-xs text-zinc-500 dark:text-zinc-500 font-semibold block uppercase">Device Name</label>
                            <span class="text-sm text-zinc-800 dark:text-zinc-200 font-medium">{{ $ticket->source_device }}</span>
                        </div>
                        <div>
                            <label class="text-xs text-zinc-500 dark:text-zinc-500 font-semibold block uppercase mb-1">Tenant ID</label>
                            <span class="text-xs font-mono bg-zinc-50 dark:bg-zinc-950/70 border border-zinc-200 dark:border-zinc-800/80 px-2 py-1.5 rounded select-all text-zinc-700 dark:text-zinc-300 block overflow-ellipsis truncate" title="{{ $ticket->source_tenant_id }}">
                                {{ $ticket->source_tenant_id }}
                            </span>
                        </div>
                    </div>
                @endif
            </div>

            <!-- Destination Device Card -->
            <div class="rounded-2xl border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900/60 backdrop-blur-xl p-6 shadow-md dark:shadow-lg flex flex-col gap-4 transition-colors">
                <div class="flex items-center gap-3 pb-3 border-b border-zinc-150 dark:border-zinc-800">
                    <div class="w-8 h-8 rounded-lg bg-cyan-500/10 text-cyan-500 dark:text-cyan-400 flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5.25 14.25h13.5m-13.5 0a3 3 0 0 1-3-3V7.5a3 3 0 0 1 3-3h13.5a3 3 0 0 1 3 3v3.75a3 3 0 0 1-3 3M5.25 14.25a3 3 0 0 0-3 3v2.25a3 3 0 0 0 3 3h13.5a3 3 0 0 0 3-3V17.25a3 3 0 0 0-3-3M6.75 7.75h.008v.008H6.75V7.75Zm0 3.5h.008v.008H6.75v-.008Zm0 3.5h.008v.008H6.75v-.008Z" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-zinc-900 dark:text-zinc-100">Destination Device</h3>
                        <p class="text-xs text-zinc-500">Target Equipment</p>
                    </div>
                </div>
                
                @if ($isPublic)
                    <div class="flex-1 flex flex-col items-center justify-center py-6 text-center relative overflow-hidden">
                        <!-- Blurred Mock Data Background -->
                        <div class="filter blur-md select-none opacity-20 pointer-events-none w-full space-y-2">
                            <div class="h-4 bg-zinc-700 rounded w-3/4 mx-auto"></div>
                            <div class="h-3 bg-zinc-700 rounded w-5/6 mx-auto"></div>
                        </div>
                        
                        <!-- Lock Centered Overlay -->
                        <div class="absolute inset-0 flex flex-col items-center justify-center p-4 bg-zinc-50/40 dark:bg-zinc-950/20 backdrop-blur-[2px] rounded-xl">
                            <div class="w-8 h-8 rounded-full bg-zinc-100 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-800 flex items-center justify-center text-zinc-500 dark:text-zinc-400 mb-1.5 shadow-inner">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-3.5 h-3.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" />
                                </svg>
                            </div>
                            <span class="text-xs font-semibold text-zinc-800 dark:text-zinc-300">Technical Data Locked</span>
                            <p class="text-[10px] text-zinc-500 mt-0.5 max-w-[200px]">Sign in to view equipment name and Tenant ID.</p>
                        </div>
                    </div>
                @else
                    <div class="space-y-4">
                        <div>
                            <label class="text-xs text-zinc-500 dark:text-zinc-500 font-semibold block uppercase">Device Name</label>
                            <span class="text-sm text-zinc-800 dark:text-zinc-200 font-medium">{{ $ticket->destination_device }}</span>
                        </div>
                        <div>
                            <label class="text-xs text-zinc-500 dark:text-zinc-500 font-semibold block uppercase mb-1">Tenant ID</label>
                            <span class="text-xs font-mono bg-zinc-50 dark:bg-zinc-950/70 border border-zinc-200 dark:border-zinc-800/80 px-2 py-1.5 rounded select-all text-zinc-700 dark:text-zinc-300 block overflow-ellipsis truncate" title="{{ $ticket->destination_tenant_id }}">
                                {{ $ticket->destination_tenant_id }}
                            </span>
                        </div>
                    </div>
                @endif
            </div>

            <!-- Cable details Card -->
            <div class="rounded-2xl border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900/60 backdrop-blur-xl p-6 shadow-md dark:shadow-lg flex flex-col gap-4 transition-colors">
                <div class="flex items-center gap-3 pb-3 border-b border-zinc-150 dark:border-zinc-800">
                    <div class="w-8 h-8 rounded-lg bg-red-500/10 text-red-500 dark:text-red-400 flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.181 8.68a4.503 4.503 0 0 1 1.903 6.405m-9.768-2.282a4.503 4.503 0 0 1 6.405-1.903m-2.983 2.983c-.094.094-.094.248 0 .342l3.62 3.62m-1.373-8.59 1.373-1.373a2.5 2.5 0 0 1 3.536 0l1.373 1.373a2.5 2.5 0 0 1 0 3.536l-1.373 1.373m-12.022 4.67 1.373-1.373a2.5 2.5 0 0 1 3.536 0l1.373 1.373a2.5 2.5 0 0 1 0 3.536l-1.373 1.373" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-zinc-900 dark:text-zinc-100">Cable Specs</h3>
                        <p class="text-xs text-zinc-500">Interface & Deployment Specs</p>
                    </div>
                </div>
                
                @if ($isPublic)
                    <div class="flex-1 flex flex-col items-center justify-center py-6 text-center relative overflow-hidden">
                        <!-- Blurred Mock Data Background -->
                        <div class="filter blur-md select-none opacity-20 pointer-events-none w-full space-y-2">
                            <div class="h-4 bg-zinc-700 rounded w-3/4 mx-auto"></div>
                            <div class="h-3 bg-zinc-700 rounded w-5/6 mx-auto"></div>
                        </div>
                        
                        <!-- Lock Centered Overlay -->
                        <div class="absolute inset-0 flex flex-col items-center justify-center p-4 bg-zinc-50/40 dark:bg-zinc-950/20 backdrop-blur-[2px] rounded-xl">
                            <div class="w-8 h-8 rounded-full bg-zinc-100 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-800 flex items-center justify-center text-zinc-500 dark:text-zinc-400 mb-1.5 shadow-inner">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-3.5 h-3.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" />
                                </svg>
                            </div>
                            <span class="text-xs font-semibold text-zinc-800 dark:text-zinc-300">Technical Data Locked</span>
                            <p class="text-[10px] text-zinc-500 mt-0.5 max-w-[200px]">Sign in to view connector types and cable specs.</p>
                        </div>
                    </div>
                @else
                    <div class="space-y-4 flex-1">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="text-xs text-zinc-500 dark:text-zinc-500 font-semibold block uppercase">Connector Type</label>
                                <span class="text-sm font-semibold text-zinc-800 dark:text-zinc-100">{{ $ticket->connector_type }}</span>
                            </div>
                            <div>
                                <label class="text-xs text-zinc-500 dark:text-zinc-500 font-semibold block uppercase">Deployment Mode</label>
                                <span class="text-sm text-zinc-700 dark:text-zinc-300 font-medium">Fiber Optic</span>
                            </div>
                        </div>
                        
                        <div class="bg-zinc-50 dark:bg-zinc-950/50 rounded-xl p-3 border border-zinc-200 dark:border-zinc-800 transition-colors">
                            <label class="text-xs text-zinc-500 dark:text-zinc-500 font-semibold block uppercase mb-1">JSON Metadata Details</label>
                            <pre class="text-xs font-mono text-emerald-700 dark:text-emerald-400 overflow-x-auto select-all p-1 whitespace-pre-wrap">{{ json_encode($ticket->cable_details ?? [], JSON_PRETTY_PRINT) }}</pre>
                        </div>
                    </div>
                @endif
            </div>
        </section>

        <!-- Audit log History Section -->
        <section class="rounded-2xl border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900/60 backdrop-blur-xl p-6 md:p-8 shadow-sm dark:shadow-xl min-h-[220px] flex flex-col transition-colors">
            <h2 class="text-xl font-bold mb-6 flex items-center gap-2 text-zinc-900 dark:text-white">
                <span class="w-2 h-5 rounded bg-red-600 inline-block"></span>
                Audit logs & Transition History
            </h2>
            
            @if ($isPublic)
                <div class="flex-1 flex flex-col items-center justify-center py-8 text-center relative overflow-hidden">
                    <!-- Blurred Mock Table Background -->
                    <div class="filter blur-md select-none opacity-20 pointer-events-none w-full max-w-md space-y-3">
                        <div class="h-4 bg-zinc-700 rounded"></div>
                        <div class="h-4 bg-zinc-700 rounded w-11/12 mx-auto"></div>
                        <div class="h-4 bg-zinc-700 rounded w-10/12 mx-auto"></div>
                    </div>
                    
                    <!-- Lock Centered Overlay -->
                    <div class="absolute inset-0 flex flex-col items-center justify-center p-4 bg-zinc-50/40 dark:bg-zinc-950/20 backdrop-blur-[2px] rounded-xl">
                        <div class="w-10 h-10 rounded-full bg-zinc-100 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-800 flex items-center justify-center text-zinc-500 dark:text-zinc-400 mb-2 shadow-inner">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" />
                            </svg>
                        </div>
                        <span class="text-sm font-semibold text-zinc-800 dark:text-zinc-300">Audit logs Locked</span>
                        <p class="text-xs text-zinc-500 mt-1 max-w-xs">Sign in to view the complete history of state transitions and operators.</p>
                    </div>
                </div>
            @else
                <!-- Desktop Table View -->
                <div class="overflow-x-auto hidden md:block">
                    <table class="w-full border-collapse text-left">
                        <thead>
                            <tr class="border-b border-zinc-200 dark:border-zinc-700 text-xs font-bold text-zinc-600 dark:text-zinc-300 uppercase tracking-wider">
                                <th class="py-3 px-4">From State</th>
                                <th class="py-3 px-4">To State</th>
                                <th class="py-3 px-4">Executor</th>
                                <th class="py-3 px-4">Role</th>
                                <th class="py-3 px-4 text-right">Timestamp</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-200 dark:divide-zinc-800 text-sm text-zinc-800 dark:text-zinc-200">
                            @forelse($ticket->logs as $log)
                            <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/50 transition-colors duration-200">
                                <td class="py-3 px-4">
                                    <span class="text-zinc-500 dark:text-zinc-400 font-medium capitalize" x-text="'{{ $log->from_state }}'.replace('_', ' ')"></span>
                                </td>
                                <td class="py-3 px-4">
                                    <span class="text-zinc-900 dark:text-zinc-100 font-semibold capitalize" x-text="'{{ $log->to_state }}'.replace('_', ' ')"></span>
                                </td>
                                <td class="py-3 px-4 font-medium text-zinc-800 dark:text-zinc-200">
                                    {{ $log->user->name ?? 'System' }}
                                </td>
                                <td class="py-3 px-4">
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold border capitalize" 
                                          :class="{
                                              'bg-red-50 dark:bg-red-500/10 text-red-700 dark:text-red-400 border-red-200 dark:border-red-500/20': '{{ $log->user->role ?? '' }}' === 'admin',
                                              'bg-cyan-50 dark:bg-cyan-500/10 text-cyan-700 dark:text-cyan-400 border-cyan-200 dark:border-cyan-500/20': '{{ $log->user->role ?? '' }}' === 'dest_manager',
                                              'bg-zinc-50 dark:bg-zinc-500/10 text-zinc-700 dark:text-zinc-400 border-zinc-200 dark:border-zinc-500/20': '{{ $log->user->role ?? '' }}' === 'staff'
                                          }">
                                        {{ str_replace('_', ' ', $log->user->role ?? 'system') }}
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-right text-zinc-500 dark:text-zinc-500">
                                    {{ $log->created_at->format('d M Y - H:i:s') }}
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="py-8 text-center text-zinc-500">
                                    No logs recorded yet for this ticket.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Mobile Stacked Card View -->
                <div class="block md:hidden space-y-4">
                    @forelse($ticket->logs as $log)
                        <div class="p-4 rounded-xl border border-zinc-200 dark:border-zinc-800 bg-zinc-50/50 dark:bg-zinc-900/50 flex flex-col gap-2.5 transition-colors">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-1.5">
                                    <span class="text-xs text-zinc-500 dark:text-zinc-400 capitalize" x-text="'{{ $log->from_state }}'.replace('_', ' ')"></span>
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-3 h-3 text-zinc-400">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                                    </svg>
                                    <span class="text-xs font-bold text-zinc-900 dark:text-zinc-100 capitalize" x-text="'{{ $log->to_state }}'.replace('_', ' ')"></span>
                                </div>
                                <span class="text-[11px] text-zinc-500 dark:text-zinc-500 font-medium">
                                    {{ $log->created_at->format('H:i') }}
                                </span>
                            </div>
                                                     <div class="flex items-center justify-between gap-2 border-t border-zinc-200/60 dark:border-zinc-800/60 pt-2">
                                <div class="flex flex-col">
                                    <span class="text-[10px] uppercase font-bold tracking-wider text-zinc-400 dark:text-zinc-500 mb-0.5">Executor</span>
                                    <span class="text-sm font-semibold text-zinc-800 dark:text-zinc-200">{{ $log->user->name ?? 'System' }}</span>
                                </div>
                                <div class="flex flex-col items-end">
                                    <span class="text-[10px] uppercase font-bold tracking-wider text-zinc-400 dark:text-zinc-500 mb-0.5">Role</span>
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold border capitalize" 
                                          :class="{
                                              'bg-red-100 dark:bg-red-500/10 text-red-700 dark:text-red-400 border-red-200 dark:border-red-500/20': '{{ $log->user->role ?? '' }}' === 'admin',
                                              'bg-cyan-100 dark:bg-cyan-500/10 text-cyan-700 dark:text-cyan-400 border-cyan-200 dark:border-cyan-500/20': '{{ $log->user->role ?? '' }}' === 'dest_manager',
                                              'bg-zinc-100 dark:bg-zinc-500/10 text-zinc-700 dark:text-zinc-400 border-zinc-200 dark:border-zinc-500/20': '{{ $log->user->role ?? '' }}' === 'staff'
                                          }">
                                        {{ str_replace('_', ' ', $log->user->role ?? 'system') }}
                                    </span>
                                </div>
                            </div>
                            <div class="text-[10px] text-zinc-500 dark:text-zinc-500 text-right">ht">
                                {{ $log->created_at->format('d M Y') }}
                            </div>
                        </div>
                    @empty
                        <div class="py-8 text-center text-zinc-500">
                            No logs recorded yet for this ticket.
                        </div>
                    @endforelse
                </div>
            @endif
        </section>
        
    </main>

    @if (!$isPublic)
    <!-- Edit Ticket Modal -->
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
                <h3 class="text-lg font-bold text-zinc-900 dark:text-white">Edit Ticket Details</h3>
                <button @click="showEditModal = false" class="text-zinc-400 hover:text-zinc-600 dark:text-zinc-500 dark:hover:text-zinc-300 cursor-pointer">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <form @submit.prevent="updateTicket" class="space-y-4">
                <div>
                    <label class="text-xs text-zinc-600 dark:text-zinc-400 font-semibold block mb-1">Ticket Label (Unique)</label>
                    <input type="text" x-model="editTicket.label" required placeholder="e.g. TICKET-101"
                           class="w-full bg-zinc-50 dark:bg-zinc-950 border border-zinc-200 dark:border-zinc-800 rounded-lg px-3 py-2 text-sm text-zinc-900 dark:text-white focus:outline-none focus:border-red-600 dark:focus:border-red-600 transition-colors">
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="text-xs text-zinc-600 dark:text-zinc-400 font-semibold block mb-1">Source Device</label>
                        <input type="text" x-model="editTicket.source_device" required placeholder="e.g. JKT-SW-01"
                               class="w-full bg-zinc-50 dark:bg-zinc-950 border border-zinc-200 dark:border-zinc-800 rounded-lg px-3 py-2 text-sm text-zinc-900 dark:text-white focus:outline-none focus:border-red-600 dark:focus:border-red-600 transition-colors">
                    </div>
                    <div>
                        <label class="text-xs text-zinc-600 dark:text-zinc-400 font-semibold block mb-1">Destination Device</label>
                        <input type="text" x-model="editTicket.destination_device" required placeholder="e.g. SG-SW-02"
                               class="w-full bg-zinc-50 dark:bg-zinc-950 border border-zinc-200 dark:border-zinc-800 rounded-lg px-3 py-2 text-sm text-zinc-900 dark:text-white focus:outline-none focus:border-red-600 dark:focus:border-red-600 transition-colors">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="text-xs text-zinc-600 dark:text-zinc-400 font-semibold block mb-1">Connector Type</label>
                        <select x-model="editTicket.connector_type"
                                class="w-full bg-zinc-50 dark:bg-zinc-950 border border-zinc-200 dark:border-zinc-800 rounded-lg px-3 py-2 text-sm text-zinc-900 dark:text-white focus:outline-none focus:border-red-600 dark:focus:border-red-600 transition-colors">
                            <option class="text-zinc-900 dark:text-white bg-white dark:bg-zinc-950" value="LC-LC">LC-LC</option>
                            <option class="text-zinc-900 dark:text-white bg-white dark:bg-zinc-950" value="SC-SC">SC-SC</option>
                            <option class="text-zinc-900 dark:text-white bg-white dark:bg-zinc-950" value="FC-FC">FC-FC</option>
                            <option class="text-zinc-900 dark:text-white bg-white dark:bg-zinc-950" value="RJ45">RJ45</option>
                        </select>
                    </div>
                    <div>
                        <label class="text-xs text-zinc-600 dark:text-zinc-400 font-semibold block mb-1">Cable Length (m)</label>
                        <input type="number" x-model="editTicket.length" required
                               class="w-full bg-zinc-50 dark:bg-zinc-950 border border-zinc-200 dark:border-zinc-800 rounded-lg px-3 py-2 text-sm text-zinc-900 dark:text-white focus:outline-none focus:border-red-600 dark:focus:border-red-600 transition-colors">
                    </div>
                </div>

                <div>
                    <label class="text-xs text-zinc-600 dark:text-zinc-400 font-semibold block mb-1">Cable Color</label>
                    <input type="text" x-model="editTicket.color" required placeholder="e.g. Yellow, Aqua"
                           class="w-full bg-zinc-50 dark:bg-zinc-950 border border-zinc-200 dark:border-zinc-800 rounded-lg px-3 py-2 text-sm text-zinc-900 dark:text-white focus:outline-none focus:border-red-600 dark:focus:border-red-600 transition-colors">
                </div>

                <div class="flex justify-end gap-3 pt-4 border-t border-zinc-200 dark:border-zinc-800">
                    <button type="button" @click="showEditModal = false"
                            class="bg-zinc-100 hover:bg-zinc-200 dark:bg-zinc-800 dark:hover:bg-zinc-700 text-xs font-semibold px-4 py-2.5 rounded-lg text-zinc-700 dark:text-zinc-300 transition-colors cursor-pointer">
                        Cancel
                    </button>
                    <button type="submit"
                            class="bg-red-600 hover:bg-red-500 text-xs font-semibold px-4 py-2.5 rounded-lg text-white transition-colors cursor-pointer">
                        Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif

    <!-- Footer -->
    <footer class="border-t border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-950 py-6 text-center text-xs text-zinc-500 dark:text-zinc-600 transition-colors">
        &copy; 2026 Technical Ticket Network by Sidiq Setyadji.
    </footer>
</body>
</html>
