<!DOCTYPE html>
<html lang="en" class="h-full bg-zinc-950 text-zinc-50 antialiased">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>General Ticketing System - Dashboard</title>
    
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
<body class="min-h-screen flex flex-col bg-radial from-zinc-900 to-zinc-950"
      x-data="{ 
          showCreateModal: false,
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
            
            <!-- User Information & Logout -->
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
    <main class="flex-1 mx-auto max-w-7xl w-full px-4 sm:px-6 lg:px-8 py-8 flex flex-col gap-8">
        
        <!-- Header Section -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-3xl font-extrabold text-white tracking-tight">Main Dashboard</h1>
                <p class="text-zinc-400 text-sm mt-1">Manage, monitor, and deploy equipment and connection cables.</p>
            </div>
            <button @click="showCreateModal = true" 
                    class="bg-violet-600 hover:bg-violet-500 active:scale-95 text-sm font-semibold text-white px-5 py-2.5 rounded-xl transition-all shadow-lg flex items-center gap-2 cursor-pointer self-start sm:self-auto">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                Create New Ticket
            </button>
        </div>

        <!-- Statistics Panel -->
        <section class="grid grid-cols-2 lg:grid-cols-4 gap-6">
            <div class="rounded-2xl border border-zinc-800 bg-zinc-900/30 backdrop-blur-xl p-5 shadow-md">
                <span class="text-xs font-semibold text-zinc-500 uppercase tracking-widest">Total Tickets</span>
                <span class="text-3xl font-extrabold text-white block mt-1 font-display">{{ $tickets->count() }}</span>
            </div>
            
            <div class="rounded-2xl border border-zinc-800 bg-zinc-900/30 backdrop-blur-xl p-5 shadow-md">
                <span class="text-xs font-semibold text-zinc-500 uppercase tracking-widest">Waiting Dest</span>
                <span class="text-3xl font-extrabold text-indigo-400 block mt-1 font-display">
                    {{ $tickets->where('status', 'waiting_destination')->count() }}
                </span>
            </div>

            <div class="rounded-2xl border border-zinc-800 bg-zinc-900/30 backdrop-blur-xl p-5 shadow-md">
                <span class="text-xs font-semibold text-zinc-500 uppercase tracking-widest">In Progress</span>
                <span class="text-3xl font-extrabold text-amber-400 block mt-1 font-display">
                    {{ $tickets->whereNotIn('status', ['waiting_destination', 'done'])->count() }}
                </span>
            </div>

            <div class="rounded-2xl border border-zinc-800 bg-zinc-900/30 backdrop-blur-xl p-5 shadow-md">
                <span class="text-xs font-semibold text-zinc-500 uppercase tracking-widest">Completed</span>
                <span class="text-3xl font-extrabold text-emerald-400 block mt-1 font-display">
                    {{ $tickets->where('status', 'done')->count() }}
                </span>
            </div>
        </section>

        <!-- Ticket List Panel -->
        <section class="rounded-2xl border border-zinc-800/80 bg-zinc-900/40 backdrop-blur-xl p-6 md:p-8 shadow-xl">
            <h2 class="text-xl font-bold mb-6 flex items-center gap-2">
                <span class="w-2 h-5 rounded bg-violet-600 inline-block"></span>
                Ticket Queue & Statuses
            </h2>

            <div class="overflow-x-auto">
                <table class="w-full border-collapse text-left">
                    <thead>
                        <tr class="border-b border-zinc-800 text-xs font-bold text-zinc-400 uppercase tracking-wider">
                            <th class="py-3 px-4">Label</th>
                            <th class="py-3 px-4">Source Device</th>
                            <th class="py-3 px-4">Destination Device</th>
                            <th class="py-3 px-4">Connector</th>
                            <th class="py-3 px-4">Current Status</th>
                            <th class="py-3 px-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-800/60 text-sm text-zinc-300">
                        @forelse($tickets as $ticket)
                        <tr class="hover:bg-zinc-800/25 transition-colors duration-200">
                            <td class="py-4 px-4 font-bold text-white select-all">
                                {{ $ticket->label }}
                            </td>
                            <td class="py-4 px-4">
                                {{ $ticket->source_device }}
                            </td>
                            <td class="py-4 px-4">
                                {{ $ticket->destination_device }}
                            </td>
                            <td class="py-4 px-4 font-mono text-xs text-zinc-400">
                                {{ $ticket->connector_type }}
                            </td>
                            <td class="py-4 px-4">
                                <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold border capitalize"
                                      :class="{
                                          'bg-indigo-500/10 text-indigo-400 border-indigo-500/20': '{{ $ticket->status }}' === 'waiting_destination',
                                          'bg-cyan-500/10 text-cyan-400 border-cyan-500/20': '{{ $ticket->status }}' === 'approved_destination',
                                          'bg-emerald-500/10 text-emerald-400 border-emerald-500/20': '{{ $ticket->status }}' === 'approved_admin',
                                          'bg-amber-500/10 text-amber-400 border-amber-500/20': '{{ $ticket->status }}' === 'sended_cable',
                                          'bg-orange-500/10 text-orange-400 border-orange-500/20': '{{ $ticket->status }}' === 'received_cable',
                                          'bg-violet-500/10 text-violet-400 border-violet-500/20': '{{ $ticket->status }}' === 'done'
                                      }">
                                    {{ str_replace('_', ' ', $ticket->status) }}
                                </span>
                            </td>
                            <td class="py-4 px-4 text-right">
                                <a href="/tickets/{{ $ticket->id }}" 
                                   class="inline-flex items-center gap-1.5 text-xs font-semibold text-violet-400 hover:text-violet-300 border border-violet-500/20 hover:border-violet-500/50 bg-violet-500/5 px-3 py-1.5 rounded-lg transition-all">
                                    Manage Details
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-3.5 h-3.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                                    </svg>
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="py-12 text-center text-zinc-500">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-12 h-12 mx-auto text-zinc-700 mb-3">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 6v.75m0 3v.75m0 3v.75m0 3V18m-9-5.25h5.25M7.5 15h3M3.375 5.25c-.621 0-1.125.504-1.125 1.125v3.026a2.999 2.999 0 0 1 0 5.198v3.026c0 .621.504 1.125 1.125 1.125h17.25c.621 0 1.125-.504 1.125-1.125v-3.026a2.999 2.999 0 0 1 0-5.198V6.375c0-.621-.504-1.125-1.125-1.125H3.375Z" />
                                </svg>
                                No tickets found. Please create one to start.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
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
        
        <div class="relative w-full max-w-lg bg-zinc-900 border border-zinc-800 rounded-2xl p-6 shadow-2xl flex flex-col gap-6"
             @click.away="showCreateModal = false">
            
            <div class="flex items-center justify-between pb-3 border-b border-zinc-800">
                <h3 class="text-lg font-bold text-white">Create New Deployment Ticket</h3>
                <button @click="showCreateModal = false" class="text-zinc-500 hover:text-zinc-300">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <form @submit.prevent="createTicket" class="space-y-4">
                <div>
                    <label class="text-xs text-zinc-400 font-semibold block mb-1">Ticket Label (Unique)</label>
                    <input type="text" x-model="newTicket.label" required placeholder="e.g. TICKET-101"
                           class="w-full bg-zinc-950 border border-zinc-800 rounded-lg px-3 py-2 text-sm text-white focus:outline-none focus:border-violet-600 transition-colors">
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="text-xs text-zinc-400 font-semibold block mb-1">Source Device</label>
                        <input type="text" x-model="newTicket.source_device" required placeholder="e.g. JKT-SW-01"
                               class="w-full bg-zinc-950 border border-zinc-800 rounded-lg px-3 py-2 text-sm text-white focus:outline-none focus:border-violet-600 transition-colors">
                    </div>
                    <div>
                        <label class="text-xs text-zinc-400 font-semibold block mb-1">Destination Device</label>
                        <input type="text" x-model="newTicket.destination_device" required placeholder="e.g. SG-SW-02"
                               class="w-full bg-zinc-950 border border-zinc-800 rounded-lg px-3 py-2 text-sm text-white focus:outline-none focus:border-violet-600 transition-colors">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="text-xs text-zinc-400 font-semibold block mb-1">Connector Type</label>
                        <select x-model="newTicket.connector_type"
                                class="w-full bg-zinc-950 border border-zinc-800 rounded-lg px-3 py-2 text-sm text-white focus:outline-none focus:border-violet-600 transition-colors">
                            <option value="LC-LC">LC-LC</option>
                            <option value="SC-SC">SC-SC</option>
                            <option value="FC-FC">FC-FC</option>
                            <option value="RJ45">RJ45</option>
                        </select>
                    </div>
                    <div>
                        <label class="text-xs text-zinc-400 font-semibold block mb-1">Cable Length (m)</label>
                        <input type="number" x-model="newTicket.length" required
                               class="w-full bg-zinc-950 border border-zinc-800 rounded-lg px-3 py-2 text-sm text-white focus:outline-none focus:border-violet-600 transition-colors">
                    </div>
                </div>

                <div>
                    <label class="text-xs text-zinc-400 font-semibold block mb-1">Cable Color</label>
                    <input type="text" x-model="newTicket.color" required placeholder="e.g. Yellow, Aqua"
                           class="w-full bg-zinc-950 border border-zinc-800 rounded-lg px-3 py-2 text-sm text-white focus:outline-none focus:border-violet-600 transition-colors">
                </div>

                <div class="flex justify-end gap-3 pt-4 border-t border-zinc-800">
                    <button type="button" @click="showCreateModal = false"
                            class="bg-zinc-800 hover:bg-zinc-700 text-xs font-semibold px-4 py-2.5 rounded-lg text-zinc-300 transition-colors">
                        Cancel
                    </button>
                    <button type="submit"
                            class="bg-violet-600 hover:bg-violet-500 text-xs font-semibold px-4 py-2.5 rounded-lg text-white transition-colors">
                        Submit Ticket
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Footer -->
    <footer class="border-t border-zinc-800 bg-zinc-950 py-6 text-center text-xs text-zinc-600">
        &copy; 2026 General Ticketing System. Built with Premium Dark UI.
    </footer>
</body>
</html>
