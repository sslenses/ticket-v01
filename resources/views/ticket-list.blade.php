<!DOCTYPE html>
<html lang="id" class="dark">
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
    <title>Daftar Tiket - Technical Ticket Network</title>
    <script src="https://unpkg.com/@tailwindcss/browser@4"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <!-- Google Fonts: Inter & Outfit -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Outfit:wght@500;600;700&display=swap" rel="stylesheet">
    <style type="text/tailwindcss">
        @custom-variant dark (&:where(.dark, .dark *));
        body { font-family: 'Inter', sans-serif; }
        h1, h2, h3, .font-display { font-family: 'Outfit', sans-serif; }
        [x-cloak] { display: none !important; }
        
        /* Custom Scrollbar */
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { 
            background: #d4d4d8; 
            border-radius: 10px; 
        }
        .dark ::-webkit-scrollbar-thumb { background: #3f3f46; }
        ::-webkit-scrollbar-thumb:hover { background: #a1a1aa; }
    </style>
    @include('layouts.spa-script')
</head>
<body class="bg-zinc-50 dark:bg-zinc-950 text-zinc-900 dark:text-zinc-100 min-h-screen antialiased selection:bg-red-500/30 selection:text-red-900 dark:selection:text-red-100 transition-colors duration-300" x-data="{ theme: localStorage.getItem('theme') || 'dark', mobileMenuOpen: false }" x-init="document.documentElement.className = theme">
    <div id="app-root" class="min-h-screen flex flex-col" x-data="ticketListApp()">

    <!-- Top Navigation Bar -->
        @include('layouts.header', ['activeMenu' => 'tickets'])

    <!-- Main Content -->
    <main id="ticket-main-container" class="w-full mx-auto py-8 bg-zinc-50 dark:bg-zinc-950 overflow-y-auto" x-data="ticketListApp()">
        <!-- Header -->
        <div class="mb-8 max-w-7xl mx-auto w-full px-4 sm:px-6 lg:px-8 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div x-show="!isFullscreen" x-transition>
                <h1 class="text-3xl font-extrabold text-zinc-900 dark:text-white tracking-tight">Daftar Tiket</h1>
                <p class="text-zinc-500 dark:text-zinc-400 text-sm mt-1">Pantau progress seluruh tiket yang sedang berjalan.</p>
            </div>
            
            <!-- Search & Filter Controls -->
            <div x-show="!isFullscreen" x-transition class="flex items-center gap-2 w-full md:w-auto">
                <div class="relative w-full md:w-64">
                    <svg xmlns="http://www.w3.org/2000/svg" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-zinc-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                    </svg>
                    <input type="text" x-model="searchQuery" placeholder="Cari ID tiket, layanan..." 
                           class="w-full bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl pl-9 pr-4 py-2 text-sm text-zinc-900 dark:text-white focus:outline-none focus:border-red-600 focus:ring-1 focus:ring-red-600 transition-colors shadow-sm">
                </div>
                <!-- Fullscreen Toggle -->
                <button @click="toggleFullscreen()" class="p-2 rounded-xl bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 text-zinc-500 hover:text-zinc-900 dark:hover:text-white hover:bg-zinc-50 dark:hover:bg-zinc-800 transition-colors shadow-sm focus:outline-none focus:ring-1 focus:ring-red-600" title="Toggle Fullscreen">
                    <!-- Maximize Icon -->
                    <svg x-show="!isFullscreen" xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 3.75v4.5m0-4.5h4.5m-4.5 0L9 9M3.75 20.25v-4.5m0 4.5h4.5m-4.5 0L9 15M20.25 3.75h-4.5m4.5 0v4.5m0-4.5L15 9m5.25 11.25h-4.5m4.5 0v-4.5m0 4.5L15 15" />
                    </svg>
                    <!-- Minimize Icon -->
                    <svg x-show="isFullscreen" style="display: none;" xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 9V4.5M9 9H4.5M9 9L3.75 3.75M9 15v4.5M9 15H4.5M9 15l-5.25 5.25M15 9h4.5M15 9V4.5M15 9l5.25-5.25M15 15h4.5M15 15v4.5M15 15l5.25 5.25" />
                    </svg>
                </button>
            </div>
        </div>

        <!-- Tickets List -->
        <div class="w-full px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            <template x-for="ticket in filteredTickets" :key="ticket.id">
                <a :href="'/tickets/' + ticket.uuid" class="block cursor-pointer rounded-2xl border overflow-hidden border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900/60 backdrop-blur-xl p-5 shadow-sm dark:shadow-md hover:shadow-lg dark:hover:shadow-xl transition-all duration-300">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6 pb-4 border-b border-zinc-100 dark:border-zinc-800">
                        <div>
                            <div class="flex flex-wrap items-center gap-2 mb-1">
                                <span class="text-xs font-bold px-2 py-0.5 rounded-full bg-red-50 dark:bg-red-500/10 text-red-600 dark:text-red-400 border border-red-100 dark:border-red-500/20" x-text="ticket.label"></span>
                                <template x-if="ticket.cable_details && ticket.cable_details.user_name">
                                    <span class="text-xs font-medium text-zinc-500 dark:text-zinc-400 flex items-center gap-1 ml-1 border-l border-zinc-200 dark:border-zinc-700 pl-3">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                          <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                                        </svg>
                                        <span x-text="ticket.cable_details.user_name"></span>
                                    </span>
                                </template>
                            </div>
                        </div>
                        <div class="flex items-center self-start sm:self-auto">
                            <span class="text-[11px] font-bold px-2.5 py-1 rounded-md border capitalize"
                                  :class="{
                                      'bg-indigo-50 dark:bg-indigo-500/10 text-indigo-700 dark:text-indigo-400 border-indigo-200 dark:border-indigo-500/20': ticket.status === 'waiting_destination',
                                      'bg-cyan-50 dark:bg-cyan-500/10 text-cyan-700 dark:text-cyan-400 border-cyan-200 dark:border-cyan-500/20': ticket.status === 'approved_destination',
                                      'bg-emerald-50 dark:bg-emerald-500/10 text-emerald-700 dark:text-emerald-400 border-emerald-200 dark:border-emerald-500/20': ticket.status === 'approved_admin',
                                      'bg-amber-50 dark:bg-amber-500/10 text-amber-700 dark:text-amber-400 border-amber-200 dark:border-amber-500/20': ticket.status === 'sended_cable',
                                      'bg-orange-50 dark:bg-orange-500/10 text-orange-700 dark:text-orange-400 border-orange-200 dark:border-orange-500/20': ticket.status === 'received_cable',
                                      'bg-emerald-100 dark:bg-emerald-950/30 text-emerald-750 dark:text-emerald-400 border-emerald-300 dark:border-emerald-500/30': ticket.status === 'done',
                                      'bg-zinc-50 dark:bg-zinc-500/10 text-zinc-700 dark:text-zinc-400 border-zinc-200 dark:border-zinc-500/20': ticket.status === 'cancelled'
                                  }"  x-text="getStageLabel(ticket.label, {key: ticket.status})"></span>
                        </div>
                    </div>

                    <!-- Progress Bar Component for this Ticket -->
                    <div class="w-full relative py-2" x-data="{
                            stages: getStages(ticket.label),
                            currentStatus: ticket.status,
                            progress: getProgress(ticket.label, ticket.status)
                        }">
                        <!-- Running Line Background -->
                        <div class="absolute top-[18px] left-0 right-0 h-1.5 bg-zinc-200 dark:bg-zinc-800 rounded-full z-0 overflow-hidden">
                            <div class="h-full bg-gradient-to-r from-red-600 via-rose-500 to-emerald-500 rounded-full transition-all duration-700 shadow-[0_0_8px_#ef4444]"
                                 :style="{ width: progress + '%' }"></div>
                        </div>

                        <!-- Dots Container -->
                        <div class="relative z-10 flex justify-between items-center w-full">
                            <template x-for="(stage, index) in stages" :key="stage.key">
                                <div class="flex flex-col items-center group relative cursor-help">
                                    <!-- Dot -->
                                    <div :class="{
                                             'border-zinc-300 dark:border-zinc-700 bg-zinc-100 dark:bg-zinc-900 text-transparent': !isCompleted(stages, currentStatus, stage.key),
                                             'border-red-500 bg-white dark:bg-zinc-950 shadow-[0_0_12px_rgba(239,68,68,0.3)]': isActive(currentStatus, stage.key),
                                             'border-emerald-500 bg-emerald-500 text-white': isCompleted(stages, currentStatus, stage.key) && !isActive(currentStatus, stage.key)
                                         }"
                                         class="w-7 h-7 sm:w-9 sm:h-9 rounded-full border-2 flex items-center justify-center transition-all duration-500 z-10">
                                        
                                        <!-- Checkmark for completed steps -->
                                        <template x-if="isCompleted(stages, currentStatus, stage.key) && !isActive(currentStatus, stage.key)">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor" class="w-4 h-4 sm:w-5 sm:h-5">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                                            </svg>
                                        </template>

                                        <!-- Outer Pulse for active step -->
                                        <div x-show="isActive(currentStatus, stage.key)" 
                                             class="absolute w-10 h-10 sm:w-12 sm:h-12 rounded-full bg-red-500/20 animate-ping pointer-events-none"></div>
                                    </div>
                                    
                                    <!-- Label -->
                                    <span :class="isCompleted(stages, currentStatus, stage.key) ? 'text-zinc-800 dark:text-zinc-200 font-bold' : 'text-zinc-400 dark:text-zinc-500 font-medium'"
                                          class="text-[9px] sm:text-xs text-center mt-2 w-14 sm:w-16 leading-tight truncate px-1 transition-colors duration-300"
                                          x-text="getStageLabel(ticket.label, stage, index)"
                                          :title="getStageLabel(ticket.label, stage, index)">
                                    </span>
                                </div>
                            </template>
                        </div>
                    </div>
                </a>
            </template>
            
            <div x-show="filteredTickets.length === 0" class="text-center py-12" x-cloak>
                <div class="w-16 h-16 bg-zinc-100 dark:bg-zinc-800 rounded-full flex items-center justify-center mx-auto mb-4 text-zinc-400">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                </div>
                <h3 class="text-lg font-bold text-zinc-900 dark:text-white">Tidak Ada Tiket Ditemukan</h3>
                <p class="text-sm text-zinc-500 dark:text-zinc-400 mt-1">Coba gunakan kata kunci pencarian yang berbeda.</p>
            </div>
            </div>
        </div>

        <!-- Floating Exit Fullscreen Button -->
        <div x-show="isFullscreen" x-transition.opacity class="fixed bottom-8 left-1/2 -translate-x-1/2 z-[100]" x-cloak>
            <button @click="toggleFullscreen()" class="px-5 py-2.5 rounded-full bg-zinc-900/90 dark:bg-white/90 backdrop-blur-md text-white dark:text-zinc-900 font-semibold shadow-2xl shadow-black/20 hover:scale-105 active:scale-95 transition-all flex items-center gap-2 border border-white/10 dark:border-zinc-900/10">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 9V4.5M9 9H4.5M9 9L3.75 3.75M9 15v4.5M9 15H4.5M9 15l-5.25 5.25M15 9h4.5M15 9V4.5M15 9l5.25-5.25M15 15h4.5M15 15v4.5M15 15l5.25 5.25" />
                </svg>
                Tutup Layar Penuh
            </button>
        </div>
    </main>

    <!-- Footer -->
    <footer class="border-t border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-950 py-6 text-center text-xs text-zinc-500 dark:text-zinc-600 transition-colors mt-auto">
        &copy; 2026 Technical Ticket Network by Sidiq Setyadji.
    </footer>

    <!-- AlpineJS App Script -->
    <script>
        window.ticketListApp = function() {
            return {
                tickets: @json($tickets),

                isFullscreen: false,
                toggleFullscreen() {
                    const elem = document.getElementById('ticket-main-container');
                    if (!document.fullscreenElement) {
                        if (elem.requestFullscreen) {
                            elem.requestFullscreen();
                        } else if (elem.webkitRequestFullscreen) { /* Safari */
                            elem.webkitRequestFullscreen();
                        } else if (elem.msRequestFullscreen) { /* IE11 */
                            elem.msRequestFullscreen();
                        }
                    } else {
                        if (document.exitFullscreen) {
                            document.exitFullscreen();
                        } else if (document.webkitExitFullscreen) { /* Safari */
                            document.webkitExitFullscreen();
                        } else if (document.msExitFullscreen) { /* IE11 */
                            document.msExitFullscreen();
                        }
                    }
                },
                init() {
                    document.addEventListener('fullscreenchange', () => {
                        this.isFullscreen = !!document.fullscreenElement;
                    });
                    document.addEventListener('webkitfullscreenchange', () => {
                        this.isFullscreen = !!document.fullscreenElement;
                    });
                },
                searchQuery: '',
                
                get filteredTickets() {
                    const query = this.searchQuery.toLowerCase();
                    return this.tickets.filter(t => 
                        (t.label && t.label.toLowerCase().includes(query)) ||
                        (t.source_device && t.source_device.toLowerCase().includes(query)) ||
                        (t.destination_device && t.destination_device.toLowerCase().includes(query))
                    );
                },
                
                getStages(label) {
                    const labelPrefix = (label || '').toUpperCase();
                    const isPO = labelPrefix.startsWith('PO-') || labelPrefix.startsWith('UP-');
                    const isSRV = labelPrefix.startsWith('SRV-');
                    const allStages = [
                        { key: 'waiting_destination', label: 'Menunggu Destinasi', color: 'indigo' },
                        { key: 'approved_destination', label: 'Disetujui Destinasi', color: 'cyan' },
                        { key: 'approved_admin', label: 'Disetujui Admin', color: 'emerald' },
                        { key: 'sended_cable', label: 'Kabel Dikirim', color: 'amber' },
                        { key: 'received_cable', label: 'Kabel Diterima', color: 'orange' },
                        { key: 'done', label: 'Selesai', color: 'emerald' }
                    ];
                    return (isPO || isSRV) ? allStages.filter(s => s.key !== 'approved_admin') : allStages;
                },

                getStageIndex(stages, status) {
                    return stages.findIndex(s => s.key === status);
                },

                getProgress(label, currentStatus) {
                    if (currentStatus === 'cancelled') return 0;
                    const stages = this.getStages(label);
                    const idx = this.getStageIndex(stages, currentStatus);
                    if (idx === -1) return 0;
                    return Math.round((idx / (stages.length - 1)) * 100);
                },

                getStageLabel(label, stage, index) {
                    const labelPrefix = (label || '').toUpperCase();
                    if (labelPrefix.startsWith('PO-') || labelPrefix.startsWith('UP-')) {
                        if (stage.key === 'waiting_destination') return 'Pengajuan Baru';
                        if (stage.key === 'approved_destination') return 'Validasi & Kelayakan';
                        if (stage.key === 'sended_cable') return 'Provisioning / Eksekusi';
                        if (stage.key === 'received_cable') return 'Uji Terima Klien';
                        if (stage.key === 'done') return 'Selesai';
                    }
                    if (labelPrefix.startsWith('ERR-')) {
                        if (stage.key === 'waiting_destination') return 'Pengajuan Baru';
                        if (stage.key === 'approved_destination') return 'Identifikasi Masalah';
                        if (stage.key === 'approved_admin') return 'Persetujuan & Eskalasi';
                        if (stage.key === 'sended_cable') return 'Provisioning / Eksekusi';
                        if (stage.key === 'received_cable') return 'Uji Terima Klien';
                        if (stage.key === 'done') return 'Selesai';
                    }
                    if (labelPrefix.startsWith('SRV-')) {
                        if (stage.key === 'waiting_destination') return 'Pengajuan Baru';
                        if (stage.key === 'approved_destination') return 'Identifikasi Lapangan';
                        if (stage.key === 'approved_admin') return 'Persetujuan Desain & Biaya';
                        if (stage.key === 'sended_cable') return 'Provisioning / Eksekusi';
                        if (stage.key === 'received_cable') return 'Uji Terima & Validasi';
                        if (stage.key === 'done') return 'Selesai';
                    }
                    return stage.label;
                },

                isCompleted(stages, currentStatus, stageKey) {
                    if (currentStatus === 'cancelled') return false;
                    return this.getStageIndex(stages, currentStatus) >= this.getStageIndex(stages, stageKey);
                },

                isActive(currentStatus, stageKey) {
                    if (currentStatus === 'cancelled') return false;
                    return currentStatus === stageKey && stageKey !== 'done';
                }
            }
        }
    </script>
    <!-- Changelog Modal -->
        </div>
    <x-changelog />

</body>
</html>
