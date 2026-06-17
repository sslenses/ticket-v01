<!DOCTYPE html>
<html lang="en" class="h-full bg-zinc-950 text-zinc-50 antialiased">
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
<body class="min-h-screen flex flex-col bg-radial from-zinc-900 to-zinc-950" 
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
              { key: 'done', label: 'Completed', color: 'violet' }
          ],
          
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
          }
      }">

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
            
            <!-- User Information & Logout / Login Link -->
            @if ($isPublic)
                <a href="{{ route('login') }}" class="bg-violet-600 hover:bg-violet-500 active:scale-95 text-xs font-semibold text-white px-4 py-2 rounded-xl transition-all shadow-lg flex items-center gap-2 cursor-pointer border border-violet-500/25">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15m3 0 3-3m0 0-3-3m3 3H9" />
                    </svg>
                    Sign In
                </a>
            @else
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
            @endif
        </div>
    </header>

    <!-- Main Container -->
    <main class="flex-1 mx-auto max-w-7xl w-full px-4 sm:px-6 lg:px-8 py-8 flex flex-col gap-6">
        
        <!-- Back Navigation / Breadcrumb -->
        @if (!$isPublic)
            <div class="flex items-center">
                <a href="/" class="inline-flex items-center gap-1.5 text-xs font-semibold text-zinc-400 hover:text-zinc-200 transition-colors bg-zinc-900/40 border border-zinc-800/80 px-3 py-1.5 rounded-lg">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-3.5 h-3.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5" />
                    </svg>
                    Back to Dashboard
                </a>
            </div>
        @endif
        
        <!-- Ticket Header Card -->
        <section class="relative overflow-hidden rounded-2xl border border-zinc-800/80 bg-zinc-900/40 backdrop-blur-xl p-6 md:p-8 flex flex-col md:flex-row md:items-center justify-between gap-6 shadow-2xl">
            <div class="absolute inset-0 bg-gradient-to-tr from-violet-500/5 to-transparent pointer-events-none"></div>
            <div>
                <div class="flex items-center gap-3 mb-2">
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold tracking-wide bg-violet-500/10 text-violet-400 border border-violet-500/20 uppercase">
                        Ticket Detail
                    </span>
                    <span class="text-zinc-500">•</span>
                    <span class="text-zinc-400 text-sm">Updated {{ $ticket->updated_at->diffForHumans() }}</span>
                </div>
                <h1 class="text-3xl md:text-4xl font-bold tracking-tight text-white mb-2">
                    {{ $ticket->label }}
                </h1>
                <p class="text-zinc-400 text-sm md:text-base max-w-2xl">
                    @if ($isPublic)
                        Technical ticket for optical connection. <span class="text-zinc-500 font-medium">Log in to view devices.</span>
                    @else
                        Technical ticket for optical connection from <span class="text-zinc-100 font-semibold">{{ $ticket->source_device }}</span> to <span class="text-zinc-100 font-semibold">{{ $ticket->destination_device }}</span>.
                    @endif
                </p>
            </div>
            
            <!-- Quick Status & Actions -->
            <div class="flex flex-col sm:items-end gap-3 shrink-0">
                <div class="text-right">
                    <span class="text-xs text-zinc-500 uppercase tracking-widest font-semibold block mb-1">Current State</span>
                    <span :class="{
                        'bg-indigo-500/10 text-indigo-400 border-indigo-500/20': currentStatus === 'waiting_destination',
                        'bg-cyan-500/10 text-cyan-400 border-cyan-500/20': currentStatus === 'approved_destination',
                        'bg-emerald-500/10 text-emerald-400 border-emerald-500/20': currentStatus === 'approved_admin',
                        'bg-amber-500/10 text-amber-400 border-amber-500/20': currentStatus === 'sended_cable',
                        'bg-orange-500/10 text-orange-400 border-orange-500/20': currentStatus === 'received_cable',
                        'bg-violet-500/10 text-violet-400 border-violet-500/20': currentStatus === 'done'
                    }" class="inline-flex px-4 py-1.5 rounded-full text-sm font-semibold border capitalize tracking-wide shadow-inner" x-text="currentStatus.replace('_', ' ')"></span>
                </div>
                
                @if (!$isPublic)
                    <!-- Interactive Action Button (for testing transitions easily) -->
                    <div class="flex gap-2">
                        <!-- If Waiting Dest -> Approve Dest -->
                        <button x-show="currentStatus === 'waiting_destination' && (currentRole === 'dest_manager' || currentRole === 'admin')"
                                @click="transitionStatus('approved_destination')"
                                class="bg-cyan-600 hover:bg-cyan-500 active:scale-95 transition-all text-xs font-semibold px-4 py-2 rounded-lg text-white shadow-lg cursor-pointer">
                            Approve Destination
                        </button>
                        <!-- If Approved Dest -> Approve Admin -->
                        <button x-show="currentStatus === 'approved_destination' && currentRole === 'admin'"
                                @click="transitionStatus('approved_admin')"
                                class="bg-emerald-600 hover:bg-emerald-500 active:scale-95 transition-all text-xs font-semibold px-4 py-2 rounded-lg text-white shadow-lg cursor-pointer">
                            Approve Admin
                        </button>
                        <!-- If Approved Admin -> Send Cable -->
                        <button x-show="currentStatus === 'approved_admin' && currentRole === 'admin'"
                                @click="transitionStatus('sended_cable')"
                                class="bg-amber-600 hover:bg-amber-500 active:scale-95 transition-all text-xs font-semibold px-4 py-2 rounded-lg text-white shadow-lg cursor-pointer">
                            Send Cable
                        </button>
                        <!-- If Sended Cable -> Receive Cable -->
                        <button x-show="currentStatus === 'sended_cable' && currentRole === 'admin'"
                                @click="transitionStatus('received_cable')"
                                class="bg-orange-600 hover:bg-orange-500 active:scale-95 transition-all text-xs font-semibold px-4 py-2 rounded-lg text-white shadow-lg cursor-pointer">
                            Receive Cable
                        </button>
                        <!-- If Received Cable -> Mark Done -->
                        <button x-show="currentStatus === 'received_cable' && currentRole === 'admin'"
                                @click="transitionStatus('done')"
                                class="bg-violet-600 hover:bg-violet-500 active:scale-95 transition-all text-xs font-semibold px-4 py-2 rounded-lg text-white shadow-lg cursor-pointer">
                            Mark Complete (Done)
                        </button>
                    </div>
                @endif
            </div>
        </section>

        <!-- Horizontal Timeline Progress Bar -->
        <section class="rounded-2xl border border-zinc-800/80 bg-zinc-900/40 backdrop-blur-xl p-6 md:p-8 shadow-xl overflow-hidden">
            <h2 class="text-xl font-bold mb-8 flex items-center gap-2">
                <span class="w-2 h-5 rounded bg-violet-600 inline-block"></span>
                Deployment Lifecycle Progression
            </h2>
            
            <div class="relative flex flex-col md:flex-row md:items-start md:justify-between gap-y-12 md:gap-y-0 px-4 md:px-0">
                <!-- Running Line Background (Desktop) -->
                <div class="hidden md:block absolute top-[18px] left-[20px] right-[20px] h-1 bg-zinc-800 rounded-full z-0">
                    <!-- Progress Fill Line -->
                    <div class="h-full bg-gradient-to-r from-violet-600 via-indigo-600 to-emerald-500 rounded-full transition-all duration-700 ease-out"
                         :style="{ width: (getStageIndex(currentStatus) / (stages.length - 1)) * 100 + '%' }"></div>
                </div>

                <template x-for="(stage, index) in stages" :key="stage.key">
                    <div class="relative z-10 flex flex-row md:flex-col items-center md:items-center gap-4 md:gap-0 md:w-1/6 group">
                        
                        <!-- Timeline Dot -->
                        <div class="relative flex items-center justify-center shrink-0">
                            <!-- Outer Ring Pulse (Only active stage) -->
                            <div x-show="isActive(stage.key)" 
                                 class="absolute w-12 h-12 rounded-full bg-zinc-800 animate-ping opacity-35"></div>
                            
                            <!-- Circle Status Dot -->
                            <div :class="{
                                     'border-zinc-800 bg-zinc-900 text-zinc-600': !isCompleted(stage.key),
                                     'border-violet-500 bg-zinc-950 text-violet-400 shadow-[0_0_15px_rgba(139,92,246,0.3)]': isActive(stage.key),
                                     'border-emerald-500 bg-emerald-500 text-zinc-950': isCompleted(stage.key) && !isActive(stage.key)
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
                            <h3 :class="isCompleted(stage.key) ? 'text-zinc-100 font-semibold' : 'text-zinc-500 font-medium'"
                                class="text-sm md:text-base tracking-tight transition-colors duration-300"
                                x-text="stage.label"></h3>
                            
                            <!-- Execution Metadata (Triggered on status transition) -->
                            <div class="mt-1 flex flex-col md:items-center text-xs text-zinc-400">
                                <template x-if="getExecutor(stage.key)">
                                    <div class="space-y-0.5">
                                        <span class="text-zinc-200 block font-medium" x-text="getExecutor(stage.key).name"></span>
                                        <span class="text-zinc-500 text-[10px]" x-text="getExecutor(stage.key).date + ' @ ' + getExecutor(stage.key).time"></span>
                                    </div>
                                </template>
                                <template x-if="!getExecutor(stage.key)">
                                    <span class="text-zinc-600">Pending...</span>
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
            <div class="rounded-2xl border border-zinc-800/80 bg-zinc-900/30 backdrop-blur-xl p-6 shadow-lg flex flex-col gap-4">
                <div class="flex items-center gap-3 pb-3 border-b border-zinc-800">
                    <div class="w-8 h-8 rounded-lg bg-indigo-500/10 text-indigo-400 flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5.25 14.25h13.5m-13.5 0a3 3 0 0 1-3-3V7.5a3 3 0 0 1 3-3h13.5a3 3 0 0 1 3 3v3.75a3 3 0 0 1-3 3M5.25 14.25a3 3 0 0 0-3 3v2.25a3 3 0 0 0 3 3h13.5a3 3 0 0 0 3-3V17.25a3 3 0 0 0-3-3M6.75 7.75h.008v.008H6.75V7.75Zm0 3.5h.008v.008H6.75v-.008Zm0 3.5h.008v.008H6.75v-.008Z" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-zinc-100">Source Device</h3>
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
                        <div class="absolute inset-0 flex flex-col items-center justify-center p-4">
                            <div class="w-8 h-8 rounded-full bg-zinc-850 border border-zinc-805 flex items-center justify-center text-zinc-400 mb-1.5 shadow-inner">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-3.5 h-3.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" />
                                </svg>
                            </div>
                            <span class="text-xs font-semibold text-zinc-300">Technical Data Locked</span>
                            <p class="text-[10px] text-zinc-500 mt-0.5 max-w-[200px]">Sign in to view equipment name and Tenant ID.</p>
                        </div>
                    </div>
                @else
                    <div class="space-y-4">
                        <div>
                            <label class="text-xs text-zinc-500 font-semibold block uppercase">Device Name</label>
                            <span class="text-sm text-zinc-200 font-medium">{{ $ticket->source_device }}</span>
                        </div>
                        <div>
                            <label class="text-xs text-zinc-500 font-semibold block uppercase">Tenant ID</label>
                            <span class="text-xs font-mono bg-zinc-950/70 border border-zinc-800/80 px-2 py-1 rounded select-all text-zinc-300 block overflow-ellipsis truncate" title="{{ $ticket->source_tenant_id }}">
                                {{ $ticket->source_tenant_id }}
                            </span>
                        </div>
                    </div>
                @endif
            </div>

            <!-- Destination Device Card -->
            <div class="rounded-2xl border border-zinc-800/80 bg-zinc-900/30 backdrop-blur-xl p-6 shadow-lg flex flex-col gap-4">
                <div class="flex items-center gap-3 pb-3 border-b border-zinc-800">
                    <div class="w-8 h-8 rounded-lg bg-cyan-500/10 text-cyan-400 flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5.25 14.25h13.5m-13.5 0a3 3 0 0 1-3-3V7.5a3 3 0 0 1 3-3h13.5a3 3 0 0 1 3 3v3.75a3 3 0 0 1-3 3M5.25 14.25a3 3 0 0 0-3 3v2.25a3 3 0 0 0 3 3h13.5a3 3 0 0 0 3-3V17.25a3 3 0 0 0-3-3M6.75 7.75h.008v.008H6.75V7.75Zm0 3.5h.008v.008H6.75v-.008Zm0 3.5h.008v.008H6.75v-.008Z" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-zinc-100">Destination Device</h3>
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
                        <div class="absolute inset-0 flex flex-col items-center justify-center p-4">
                            <div class="w-8 h-8 rounded-full bg-zinc-850 border border-zinc-805 flex items-center justify-center text-zinc-400 mb-1.5 shadow-inner">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-3.5 h-3.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" />
                                </svg>
                            </div>
                            <span class="text-xs font-semibold text-zinc-300">Technical Data Locked</span>
                            <p class="text-[10px] text-zinc-500 mt-0.5 max-w-[200px]">Sign in to view equipment name and Tenant ID.</p>
                        </div>
                    </div>
                @else
                    <div class="space-y-4">
                        <div>
                            <label class="text-xs text-zinc-500 font-semibold block uppercase">Device Name</label>
                            <span class="text-sm text-zinc-200 font-medium">{{ $ticket->destination_device }}</span>
                        </div>
                        <div>
                            <label class="text-xs text-zinc-500 font-semibold block uppercase">Tenant ID</label>
                            <span class="text-xs font-mono bg-zinc-950/70 border border-zinc-800/80 px-2 py-1 rounded select-all text-zinc-300 block overflow-ellipsis truncate" title="{{ $ticket->destination_tenant_id }}">
                                {{ $ticket->destination_tenant_id }}
                            </span>
                        </div>
                    </div>
                @endif
            </div>

            <!-- Cable details Card -->
            <div class="rounded-2xl border border-zinc-800/80 bg-zinc-900/30 backdrop-blur-xl p-6 shadow-lg flex flex-col gap-4">
                <div class="flex items-center gap-3 pb-3 border-b border-zinc-800">
                    <div class="w-8 h-8 rounded-lg bg-violet-500/10 text-violet-400 flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.181 8.68a4.503 4.503 0 0 1 1.903 6.405m-9.768-2.282a4.503 4.503 0 0 1 6.405-1.903m-2.983 2.983c-.094.094-.094.248 0 .342l3.62 3.62m-1.373-8.59 1.373-1.373a2.5 2.5 0 0 1 3.536 0l1.373 1.373a2.5 2.5 0 0 1 0 3.536l-1.373 1.373m-12.022 4.67 1.373-1.373a2.5 2.5 0 0 1 3.536 0l1.373 1.373a2.5 2.5 0 0 1 0 3.536l-1.373 1.373" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-zinc-100">Cable Specs</h3>
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
                        <div class="absolute inset-0 flex flex-col items-center justify-center p-4">
                            <div class="w-8 h-8 rounded-full bg-zinc-850 border border-zinc-805 flex items-center justify-center text-zinc-400 mb-1.5 shadow-inner">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-3.5 h-3.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" />
                                </svg>
                            </div>
                            <span class="text-xs font-semibold text-zinc-300">Technical Data Locked</span>
                            <p class="text-[10px] text-zinc-500 mt-0.5 max-w-[200px]">Sign in to view connector types and cable specs.</p>
                        </div>
                    </div>
                @else
                    <div class="space-y-4 flex-1">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="text-xs text-zinc-500 font-semibold block uppercase">Connector Type</label>
                                <span class="text-sm font-semibold text-zinc-100">{{ $ticket->connector_type }}</span>
                            </div>
                            <div>
                                <label class="text-xs text-zinc-500 font-semibold block uppercase">Deployment Mode</label>
                                <span class="text-sm text-zinc-300">Fiber Optic</span>
                            </div>
                        </div>
                        
                        <div class="bg-zinc-950/50 rounded-xl p-3 border border-zinc-800">
                            <label class="text-xs text-zinc-500 font-semibold block uppercase mb-1">JSON Metadata Details</label>
                            <pre class="text-xs font-mono text-emerald-400 overflow-x-auto select-all p-1 whitespace-pre-wrap">{{ json_encode($ticket->cable_details ?? [], JSON_PRETTY_PRINT) }}</pre>
                        </div>
                    </div>
                @endif
            </div>
        </section>

        <!-- Audit log History Table -->
        <section class="rounded-2xl border border-zinc-800/80 bg-zinc-900/40 backdrop-blur-xl p-6 md:p-8 shadow-xl min-h-[220px] flex flex-col">
            <h2 class="text-xl font-bold mb-6 flex items-center gap-2">
                <span class="w-2 h-5 rounded bg-violet-600 inline-block"></span>
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
                    <div class="absolute inset-0 flex flex-col items-center justify-center p-4">
                        <div class="w-10 h-10 rounded-full bg-zinc-850 border border-zinc-805 flex items-center justify-center text-zinc-400 mb-2 shadow-inner">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" />
                            </svg>
                        </div>
                        <span class="text-sm font-semibold text-zinc-300">Audit logs Locked</span>
                        <p class="text-xs text-zinc-500 mt-1 max-w-xs">Sign in to view the complete history of state transitions and operators.</p>
                    </div>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full border-collapse text-left">
                        <thead>
                            <tr class="border-b border-zinc-800 text-xs font-bold text-zinc-400 uppercase tracking-wider">
                                <th class="py-3 px-4">From State</th>
                                <th class="py-3 px-4">To State</th>
                                <th class="py-3 px-4">Executor</th>
                                <th class="py-3 px-4">Role</th>
                                <th class="py-3 px-4 text-right">Timestamp</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-800/60 text-sm text-zinc-300">
                            @forelse($ticket->logs as $log)
                            <tr class="hover:bg-zinc-800/25 transition-colors duration-200">
                                <td class="py-3 px-4">
                                    <span class="text-zinc-400 font-medium capitalize" x-text="'{{ $log->from_state }}'.replace('_', ' ')"></span>
                                </td>
                                <td class="py-3 px-4">
                                    <span class="text-zinc-100 font-semibold capitalize" x-text="'{{ $log->to_state }}'.replace('_', ' ')"></span>
                                </td>
                                <td class="py-3 px-4 font-medium text-zinc-200">
                                    {{ $log->user->name ?? 'System' }}
                                </td>
                                <td class="py-3 px-4">
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold border capitalize" 
                                          :class="{
                                              'bg-violet-500/10 text-violet-400 border-violet-500/20': '{{ $log->user->role ?? '' }}' === 'admin',
                                              'bg-cyan-500/10 text-cyan-400 border-cyan-500/20': '{{ $log->user->role ?? '' }}' === 'dest_manager',
                                              'bg-zinc-500/10 text-zinc-400 border-zinc-500/20': '{{ $log->user->role ?? '' }}' === 'staff'
                                          }">
                                        {{ str_replace('_', ' ', $log->user->role ?? 'system') }}
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-right text-zinc-500">
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
            @endif
        </section>
        
    </main>

    <!-- Footer -->
    <footer class="border-t border-zinc-800 bg-zinc-950 py-6 text-center text-xs text-zinc-600">
        &copy; 2026 General Ticketing System. Built with Premium Dark UI.
    </footer>
</body>
</html>
