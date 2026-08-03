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
    <title>Technical Ticket Network - Dashboard</title>
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

    <script>
        window.__tenants = <?php echo json_encode($tenants, 15, 512) ?>;
        
        window.dashboardApp = function() {
            return {
                showCreateModal: false,
                activeCreateTab: 'network',
                errorMessage: '',
                searchQuery: '',
                activeStatusFilter: 'all',
                currentUserRole: '<?php echo e(auth()->user()->role); ?>',
                tickets: <?php echo json_encode($ticketsJson, 15, 512) ?>,
                async deleteTicket(ticket) {
                    if (!confirm('Apakah Anda yakin ingin menghapus tiket "' + ticket.label + '"? Tindakan ini tidak dapat dibatalkan.')) {
                        return;
                    }
                    try {
                        const response = await fetch('/api/tickets/' + ticket.uuid, {
                            method: 'DELETE',
                            headers: {
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>'
                            }
                        });
                        if (!response.ok) {
                            const err = await response.json();
                            alert('Gagal menghapus tiket: ' + (err.message || 'Error'));
                            return;
                        }
                        window.reloadPage();
                    } catch (e) {
                        alert('Gagal menghubungi server.');
                    }
                },
                getStatusLabel(t) {
                    const labelPrefix = (t.label || '').toUpperCase();
                    const status = t.status;
                    if (labelPrefix.startsWith('PO-')) {
                        if (status === 'waiting_destination') return 'Pengajuan Baru';
                        if (status === 'approved_destination') return 'Validasi & Kelayakan';
                        if (status === 'approved_admin') return 'Persetujuan Kontrak/Biaya';
                        if (status === 'sended_cable') return 'Provisioning / Eksekusi';
                        if (status === 'received_cable') return 'Uji Terima Klien';
                        if (status === 'done') return 'Selesai';
                    }
                    if (labelPrefix.startsWith('ERR-')) {
                        if (status === 'waiting_destination') return 'Pengajuan Baru';
                        if (status === 'approved_destination') return 'Identifikasi & Melokalisir Masalah';
                        if (status === 'approved_admin') return 'Persetujuan & Eskalasi';
                        if (status === 'sended_cable') return 'Provisioning / Eksekusi';
                        if (status === 'received_cable') return 'Uji Terima Klien';
                        if (status === 'done') return 'Selesai';
                    }
                    if (labelPrefix.startsWith('SRV-')) {
                        if (status === 'waiting_destination') return 'Pengajuan Baru';
                        if (status === 'approved_destination') return 'Identifikasi & Verifikasi Lapangan';
                        if (status === 'approved_admin') return 'Persetujuan Desain & Biaya';
                        if (status === 'sended_cable') return 'Provisioning / Eksekusi';
                        if (status === 'received_cable') return 'Uji Terima & Validasi';
                        if (status === 'done') return 'Selesai';
                    }
                    return t.statusLabel || status.replace('_', ' ');
                },

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
                        (t.label || '').toLowerCase().includes(q) ||
                        (t.user_name || '').toLowerCase().includes(q) ||
                        (t.user_contact || '').toLowerCase().includes(q) ||
                                                (this.getStatusLabel(t) || '').toLowerCase().includes(q)
                    );
                },
                generateNextLabel(prefix) {
                    const matchingTickets = this.tickets.filter(t => t.label && t.label.startsWith(prefix));
                    let nextNum = 1;
                    if (matchingTickets.length > 0) {
                        const nums = matchingTickets.map(t => {
                            const part = t.label.substring(prefix.length);
                            const num = parseInt(part, 10);
                            return isNaN(num) ? 0 : num;
                        });
                        const maxNum = Math.max(...nums);
                        nextNum = maxNum + 1;
                    }
                    const paddedNum = String(nextNum).padStart(5, '0');
                    return `${prefix}${paddedNum}`;
                },
                tenants: window.__tenants || [],
                sourceTenantSearch: '<?php echo e($tenants->first()->name ?? ''); ?>',
                destTenantSearch: '<?php echo e($tenants->skip(1)->first()->name ?? ($tenants->first()->name ?? '')); ?>',
                sourceTenantDropdownOpen: false,
                destTenantDropdownOpen: false,
                filteredSourceTenants() {
                    return this.tenants.filter(t => t.name.toLowerCase().includes(this.sourceTenantSearch.toLowerCase()));
                },
                filteredDestTenants() {
                    return this.tenants.filter(t => t.name.toLowerCase().includes(this.destTenantSearch.toLowerCase()));
                },
                selectSourceTenant(t) {
                    this.newTicket.source_tenant_id = t.id;
                    this.newTicket.new_source_tenant_name = '';
                    this.sourceTenantSearch = t.name;
                    this.sourceTenantDropdownOpen = false;
                },
                selectDestTenant(t) {
                    this.newTicket.destination_tenant_id = t.id;
                    this.newTicket.new_destination_tenant_name = '';
                    this.destTenantSearch = t.name;
                    this.destTenantDropdownOpen = false;
                },
                markSourceTenantAsNew() {
                    if (!this.sourceTenantSearch.trim()) return;
                    if (this.tenants.some(t => t.name.toLowerCase() === this.sourceTenantSearch.trim().toLowerCase())) return;
                    this.newTicket.source_tenant_id = 'NEW_TENANT';
                    this.newTicket.new_source_tenant_name = this.sourceTenantSearch.trim();
                    this.sourceTenantDropdownOpen = false;
                },
                markDestTenantAsNew() {
                    if (!this.destTenantSearch.trim()) return;
                    if (this.tenants.some(t => t.name.toLowerCase() === this.destTenantSearch.trim().toLowerCase())) return;
                    this.newTicket.destination_tenant_id = 'NEW_TENANT';
                    this.newTicket.new_destination_tenant_name = this.destTenantSearch.trim();
                    this.destTenantDropdownOpen = false;
                },
                onSourceSearchInput() {
                    this.newTicket.new_source_tenant_name = '';
                    const match = this.tenants.find(t => t.name.toLowerCase() === this.sourceTenantSearch.trim().toLowerCase());
                    if (match) {
                        this.newTicket.source_tenant_id = match.id;
                    } else {
                        this.newTicket.source_tenant_id = '';
                    }
                },
                onDestSearchInput() {
                    this.newTicket.new_destination_tenant_name = '';
                    const match = this.tenants.find(t => t.name.toLowerCase() === this.destTenantSearch.trim().toLowerCase());
                    if (match) {
                        this.newTicket.destination_tenant_id = match.id;
                    } else {
                        this.newTicket.destination_tenant_id = '';
                    }
                },
                newTicket: {
                    label: '',
                    source_device: '',
                    destination_device: '',
                    source_tenant_id: '<?php echo e($tenants->first()->id ?? ''); ?>',
                    destination_tenant_id: '<?php echo e($tenants->skip(1)->first()->id ?? ($tenants->first()->id ?? '')); ?>',
                    new_source_tenant_name: '',
                    new_destination_tenant_name: '',
                    connector_type: 'LC-LC',
                    length: 10,
                    color: 'Yellow',
                    type: 'Single-Mode OS2',
                    notes: '',
                    keterangan: '',
                    user_name: '',
                    user_contact: '',
                    backhaul: '',
                    metro: '',
                    destination_site: '',
                    capacity: '',
                    alamat: '',
                    titik_koordinat: '',
                    link_maps: ''
                },
                validateCreateForm() {
                    this.errorMessage = '';
                    
                    if (!this.newTicket.label || !this.newTicket.label.trim()) {
                        this.activeCreateTab = 'network';
                        this.errorMessage = 'Label Tiket wajib diisi.';
                        return false;
                    }
                    if (!this.newTicket.user_name || !this.newTicket.user_name.trim()) {
                        this.activeCreateTab = 'network';
                        this.errorMessage = 'Nama Pengguna wajib diisi.';
                        return false;
                    }


                    return true;
                },
                async createTicket() {
                    if (!this.validateCreateForm()) return;
                    try {
                        const response = await fetch('/api/tickets', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>'
                            },
                            body: JSON.stringify({
                                label: this.newTicket.label,
                                source_device: this.newTicket.source_device,
                                destination_device: this.newTicket.destination_device,
                                source_tenant_id: this.newTicket.source_tenant_id,
                                destination_tenant_id: this.newTicket.destination_tenant_id,
                                new_source_tenant_name: this.newTicket.new_source_tenant_name,
                                new_destination_tenant_name: this.newTicket.new_destination_tenant_name,
                                connector_type: this.newTicket.connector_type,
                                cable_details: {
                                    length: parseInt(this.newTicket.length),
                                    color: this.newTicket.color,
                                    type: this.newTicket.type,
                                    notes: this.newTicket.keterangan || this.newTicket.notes,
                                    keterangan: this.newTicket.keterangan,
                                    user_name: this.newTicket.user_name,
                                    user_contact: this.newTicket.user_contact,
                                    backhaul: this.newTicket.backhaul,
                                    metro: this.newTicket.metro,
                                    destination_site: this.newTicket.destination_site,
                                    capacity: this.newTicket.capacity,
                                    alamat: this.newTicket.alamat,
                                    titik_koordinat: this.newTicket.titik_koordinat,
                                    link_maps: this.newTicket.link_maps
                                }
                            })
                        });

                        if (!response.ok) {
                            const err = await response.json();
                            this.errorMessage = 'Gagal membuat tiket: ' + (err.message || 'Error validasi');
                            return;
                        }

                        window.reloadPage();
                    } catch (e) {
                        this.errorMessage = 'Gagal menghubungi server.';
                    }
                }
            };
        }
    </script>
    <?php echo $__env->make('layouts.spa-script', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
</head>
<body class="min-h-screen flex flex-col bg-zinc-50 dark:bg-zinc-950 text-zinc-900 dark:text-zinc-50 transition-colors duration-300">
    <div id="app-root" class="min-h-screen flex flex-col" x-data="dashboardApp()">

    <!-- Top Navigation -->
        <?php echo $__env->make('layouts.header', ['activeMenu' => 'dashboard'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <!-- Main Container -->
    <main class="flex-1 mx-auto max-w-7xl w-full px-4 sm:px-6 lg:px-8 py-8 flex flex-col gap-8">
        
        <!-- Header Section -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-3xl font-extrabold text-zinc-900 dark:text-white tracking-tight">Dashboard Utama</h1>
                <p class="text-zinc-500 dark:text-zinc-400 text-sm mt-1">Kelola, pantau, dan pasang peralatan serta kabel koneksi.</p>
            </div>
            <button @click="showCreateModal = true; activeCreateTab = 'network'; errorMessage = '';" 
                    class="hidden sm:inline-flex items-center bg-red-600 hover:bg-red-700 dark:hover:bg-red-500 active:scale-95 text-sm font-semibold text-white px-5 py-2.5 rounded-xl transition-all shadow-lg shadow-red-600/10 cursor-pointer sm:self-auto">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4 inline-block mr-1">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                Buat Tiket Baru
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
                          :class="activeStatusFilter === 'all' ? 'text-red-600 dark:text-red-400 font-bold' : 'text-zinc-500'">Total Tiket</span>
                    <span class="w-1.5 h-1.5 rounded-full bg-red-500 animate-pulse" x-show="activeStatusFilter === 'all'"></span>
                </div>
                <span class="text-3xl font-extrabold text-zinc-900 dark:text-white block mt-1 font-display"><?php echo e($tickets->count()); ?></span>
            </div>
            
            <!-- Waiting Dest Card -->
            <div @click="activeStatusFilter = 'waiting_destination'" 
                 class="rounded-2xl border p-5 shadow-sm transition-all duration-300 cursor-pointer select-none hover:-translate-y-1 hover:bg-zinc-100/50 dark:hover:bg-zinc-800/40"
                 :class="activeStatusFilter === 'waiting_destination' ? 'border-indigo-500 bg-indigo-50/50 dark:bg-indigo-950/20 shadow-md shadow-indigo-500/10' : 'border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900/60'">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold uppercase tracking-widest transition-colors"
                          :class="activeStatusFilter === 'waiting_destination' ? 'text-indigo-600 dark:text-indigo-400 font-bold' : 'text-zinc-500'">Menunggu Destinasi</span>
                    <span class="w-1.5 h-1.5 rounded-full bg-indigo-500 animate-pulse" x-show="activeStatusFilter === 'waiting_destination'"></span>
                </div>
                <span class="text-3xl font-extrabold text-indigo-600 dark:text-indigo-400 block mt-1 font-display">
                    <?php echo e($tickets->where('status', 'waiting_destination')->count()); ?>

                </span>
            </div>

            <!-- In Progress Card -->
            <div @click="activeStatusFilter = 'in_progress'" 
                 class="rounded-2xl border p-5 shadow-sm transition-all duration-300 cursor-pointer select-none hover:-translate-y-1 hover:bg-zinc-100/50 dark:hover:bg-zinc-800/40"
                 :class="activeStatusFilter === 'in_progress' ? 'border-amber-500 bg-amber-50/50 dark:bg-amber-950/20 shadow-md shadow-amber-500/10' : 'border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900/60'">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold uppercase tracking-widest transition-colors"
                          :class="activeStatusFilter === 'in_progress' ? 'text-amber-600 dark:text-amber-400 font-bold' : 'text-zinc-500'">Sedang Diproses</span>
                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse" x-show="activeStatusFilter === 'in_progress'"></span>
                </div>
                <span class="text-3xl font-extrabold text-amber-600 dark:text-amber-500 block mt-1 font-display">
                    <?php echo e($tickets->whereNotIn('status', ['waiting_destination', 'done'])->count()); ?>

                </span>
            </div>

            <!-- Completed Card -->
            <div @click="activeStatusFilter = 'completed'" 
                 class="rounded-2xl border p-5 shadow-sm transition-all duration-300 cursor-pointer select-none hover:-translate-y-1 hover:bg-zinc-100/50 dark:hover:bg-zinc-800/40"
                 :class="activeStatusFilter === 'completed' ? 'border-emerald-500 bg-emerald-50/50 dark:bg-emerald-950/20 shadow-md shadow-emerald-500/10' : 'border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900/60'">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold uppercase tracking-widest transition-colors"
                          :class="activeStatusFilter === 'completed' ? 'text-emerald-600 dark:text-emerald-400 font-bold' : 'text-zinc-500'">Selesai</span>
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse" x-show="activeStatusFilter === 'completed'"></span>
                </div>
                <span class="text-3xl font-extrabold text-emerald-600 dark:text-emerald-400 block mt-1 font-display">
                    <?php echo e($tickets->where('status', 'done')->count()); ?>

                </span>
            </div>
        </section>

        <!-- Mobile Create Ticket Button -->
        <button @click="showCreateModal = true; activeCreateTab = 'network'; errorMessage = '';" 
                class="flex sm:hidden items-center justify-center w-full bg-red-600 hover:bg-red-700 dark:hover:bg-red-500 active:scale-95 text-sm font-semibold text-white py-3 rounded-xl transition-all shadow-lg shadow-red-600/10 cursor-pointer text-center">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4 inline-block mr-1">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
            </svg>
            Buat Tiket Baru
        </button>

        <!-- Ticket List Panel -->
        <section class="rounded-2xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900/60 backdrop-blur-xl p-5 md:p-8 shadow-sm dark:shadow-2xl transition-colors">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
                <h2 class="text-xl font-bold flex items-center gap-2 text-zinc-900 dark:text-white">
                    <span class="w-2 h-5 rounded bg-red-600 inline-block"></span>
                    Antrean & Status Tiket
                </h2>
                <div class="relative w-full sm:w-72">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-zinc-400 dark:text-zinc-500">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.637 10.637Z" />
                        </svg>
                    </div>
                    <input type="text" x-model="searchQuery" placeholder="Cari berdasarkan label, perangkat, atau status..."
                           class="w-full bg-zinc-100 dark:bg-zinc-900/70 border border-zinc-200 dark:border-zinc-800 rounded-xl pl-10 pr-4 py-2.5 text-xs text-zinc-900 dark:text-white focus:outline-none focus:border-red-600 dark:focus:border-red-600 transition-colors placeholder-zinc-500">
                </div>
            </div>

            <!-- Desktop Table View (hidden on mobile screens) -->
            <div class="hidden md:block overflow-x-auto">
                <table class="w-full border-collapse text-left">
                    <thead>
                        <tr class="border-b border-zinc-200 dark:border-zinc-700 text-xs font-bold text-zinc-600 dark:text-zinc-400 uppercase tracking-wider">
                            <th class="py-3 px-4">Label</th>
                            <th class="py-3 px-4">Nama Pengguna</th>
                            <th class="py-3 px-4">Kontak Pengguna</th>
                                                        <th class="py-3 px-4">Status Saat Ini</th>
                            <th class="py-3 px-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-200 dark:divide-zinc-800 text-sm text-zinc-800 dark:text-zinc-200">
                        <template x-for="t in filteredTickets()" :key="t.id">
                            <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/50 transition-colors duration-200">
                                <td class="py-4 px-4 font-bold text-zinc-900 dark:text-white select-all" x-text="t.label"></td>
                                <td class="py-4 px-4 text-zinc-700 dark:text-zinc-300" x-text="t.user_name || '-'"></td>
                                <td class="py-4 px-4 text-zinc-700 dark:text-zinc-300 select-all" x-text="t.user_contact || '-'"></td>
                                <td class="py-4 px-4">
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold border capitalize transition-all duration-300 hover:scale-105"
                                          :class="{
                                              'bg-indigo-50 dark:bg-indigo-500/10 text-indigo-700 dark:text-indigo-400 border-indigo-200 dark:border-indigo-500/20': t.status === 'waiting_destination',
                                              'bg-cyan-50 dark:bg-cyan-500/10 text-cyan-700 dark:text-cyan-400 border-cyan-200 dark:border-cyan-500/20': t.status === 'approved_destination',
                                              'bg-emerald-50 dark:bg-emerald-500/10 text-emerald-700 dark:text-emerald-400 border-emerald-200 dark:border-emerald-500/20': t.status === 'approved_admin',
                                              'bg-amber-50 dark:bg-amber-500/10 text-amber-700 dark:text-amber-400 border-amber-200 dark:border-amber-500/20': t.status === 'sended_cable',
                                              'bg-orange-50 dark:bg-orange-500/10 text-orange-700 dark:text-orange-400 border-orange-200 dark:border-orange-500/20': t.status === 'received_cable',
                                              'bg-emerald-100 dark:bg-emerald-950/30 text-emerald-750 dark:text-emerald-400 border-emerald-300 dark:border-emerald-500/30 font-bold': t.status === 'done',
                                              'bg-zinc-100 dark:bg-zinc-800/30 text-zinc-500 dark:text-zinc-500 border-zinc-200 dark:border-zinc-800/50': t.status === 'cancelled'
                                          }">
                                        <!-- Pulsing live dot for active/in-progress statuses -->
                                        <span class="relative flex h-2 w-2" x-show="t.status !== 'done' && t.status !== 'cancelled'">
                                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full opacity-75"
                                                  :class="{
                                                      'bg-indigo-400': t.status === 'waiting_destination',
                                                      'bg-cyan-400': t.status === 'approved_destination',
                                                      'bg-emerald-400': t.status === 'approved_admin',
                                                      'bg-amber-400': t.status === 'sended_cable',
                                                      'bg-orange-400': t.status === 'received_cable'
                                                  }"></span>
                                            <span class="relative inline-flex rounded-full h-2 w-2"
                                                  :class="{
                                                      'bg-indigo-500': t.status === 'waiting_destination',
                                                      'bg-cyan-500': t.status === 'approved_destination',
                                                      'bg-emerald-500': t.status === 'approved_admin',
                                                      'bg-amber-500': t.status === 'sended_cable',
                                                      'bg-orange-500': t.status === 'received_cable'
                                                  }"></span>
                                        </span>
                                        <!-- Done state green dot -->
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 shrink-0" x-show="t.status === 'done'"></span>
                                        <!-- Cancelled state gray dot -->
                                        <span class="w-1.5 h-1.5 rounded-full bg-zinc-400 dark:bg-zinc-500 shrink-0" x-show="t.status === 'cancelled'"></span>
                                        <span class="truncate max-w-[130px] whitespace-nowrap block" :title="getStatusLabel(t)" x-text="getStatusLabel(t)"></span>
                                    </span>
                                </td>
                                <td class="py-4 px-4 text-right font-display">
                                    <div class="flex items-center justify-end gap-2">
                                        <button x-show="currentUserRole === 'admin'" @click="deleteTicket(t)"
                                                class="inline-flex items-center gap-1 text-xs font-semibold text-red-600 hover:text-red-950 dark:text-red-400 dark:hover:text-red-200 border border-red-200 dark:border-red-500/20 bg-red-500/5 hover:bg-red-50 dark:hover:bg-red-950/30 px-2.5 py-1.5 rounded-lg transition-all cursor-pointer">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-3.5 h-3.5">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                            </svg>
                                            Hapus
                                        </button>
                                        <a :href="'/tickets/' + t.uuid" 
                                           class="inline-flex items-center gap-1.5 text-xs font-semibold text-emerald-600 hover:text-emerald-750 dark:text-emerald-400 dark:hover:text-emerald-300 border border-emerald-200 dark:border-emerald-500/20 hover:border-emerald-500/50 bg-emerald-500/5 px-3 py-1.5 rounded-lg transition-all">
                                            Detail
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-3.5 h-3.5">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                                            </svg>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        </template>
                        <!-- Search/Filter empty state desktop -->
                        <tr x-show="filteredTickets().length === 0 && searchQuery !== ''">
                            <td colspan="6" class="py-12 text-center text-zinc-500 dark:text-zinc-500">
                                <svg xmlns="http://www.w3.org/2054/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-12 h-12 mx-auto text-zinc-400 dark:text-zinc-700 mb-3">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.637 10.637Z" />
                                </svg>
                                Tidak ada tiket yang cocok untuk "<span class="text-zinc-800 dark:text-zinc-300 font-medium" x-text="searchQuery"></span>".
                            </td>
                        </tr>
                        <!-- DB empty state desktop -->
                        <tr x-show="filteredTickets().length === 0 && searchQuery === ''">
                            <td colspan="6" class="py-12 text-center text-zinc-500 dark:text-zinc-500">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-12 h-12 mx-auto text-zinc-400 dark:text-zinc-700 mb-3">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 6v.75m0 3v.75m0 3v.75m0 3V18m-9-5.25h5.25M7.5 15h3M3.375 5.25c-.621 0-1.125.504-1.125 1.125v3.026a2.999 2.999 0 0 1 0 5.198v3.026c0 .621.504 1.125 1.125 1.125h17.25c.621 0 1.125-.504 1.125-1.125v-3.026a2.999 2.999 0 0 1 0-5.198V6.375c0-.621-.504-1.125-1.125-1.125H3.375Z" />
                                </svg>
                                Tidak ada tiket ditemukan. Silakan buat satu untuk memulai.
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
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-semibold border capitalize transition-all duration-300 hover:scale-105"
                                  :class="{
                                      'bg-indigo-50 dark:bg-indigo-500/10 text-indigo-700 dark:text-indigo-400 border-indigo-200 dark:border-indigo-500/20': t.status === 'waiting_destination',
                                      'bg-cyan-50 dark:bg-cyan-500/10 text-cyan-700 dark:text-cyan-400 border-cyan-200 dark:border-cyan-500/20': t.status === 'approved_destination',
                                      'bg-emerald-50 dark:bg-emerald-500/10 text-emerald-700 dark:text-emerald-400 border-emerald-200 dark:border-emerald-500/20': t.status === 'approved_admin',
                                      'bg-amber-50 dark:bg-amber-500/10 text-amber-700 dark:text-amber-400 border-amber-200 dark:border-amber-500/20': t.status === 'sended_cable',
                                      'bg-orange-50 dark:bg-orange-500/10 text-orange-700 dark:text-orange-400 border-orange-200 dark:border-orange-500/20': t.status === 'received_cable',
                                      'bg-emerald-100 dark:bg-emerald-950/30 text-emerald-750 dark:text-emerald-400 border-emerald-300 dark:border-emerald-500/30 font-bold': t.status === 'done',
                                      'bg-zinc-100 dark:bg-zinc-800/30 text-zinc-500 dark:text-zinc-500 border-zinc-200 dark:border-zinc-800/50': t.status === 'cancelled'
                                  }">
                                <!-- Pulsing live dot for active/in-progress statuses -->
                                <span class="relative flex h-1.5 w-1.5" x-show="t.status !== 'done' && t.status !== 'cancelled'">
                                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full opacity-75"
                                          :class="{
                                              'bg-indigo-400': t.status === 'waiting_destination',
                                              'bg-cyan-400': t.status === 'approved_destination',
                                              'bg-emerald-400': t.status === 'approved_admin',
                                              'bg-amber-400': t.status === 'sended_cable',
                                              'bg-orange-400': t.status === 'received_cable'
                                          }"></span>
                                    <span class="relative inline-flex rounded-full h-1.5 w-1.5"
                                          :class="{
                                              'bg-indigo-500': t.status === 'waiting_destination',
                                              'bg-cyan-500': t.status === 'approved_destination',
                                              'bg-emerald-500': t.status === 'approved_admin',
                                              'bg-amber-500': t.status === 'sended_cable',
                                              'bg-orange-500': t.status === 'received_cable'
                                          }"></span>
                                </span>
                                <!-- Done state green dot -->
                                <span class="w-1 h-1 rounded-full bg-emerald-500 shrink-0" x-show="t.status === 'done'"></span>
                                <!-- Cancelled state gray dot -->
                                <span class="w-1 h-1 rounded-full bg-zinc-400 dark:bg-zinc-500 shrink-0" x-show="t.status === 'cancelled'"></span>
                                <span class="truncate max-w-[120px] whitespace-nowrap block" :title="getStatusLabel(t)" x-text="getStatusLabel(t)"></span>
                            </span>
                        </div>
                        
                        <div class="grid grid-cols-2 gap-2 text-xs text-zinc-500 dark:text-zinc-400 pt-2 border-t border-zinc-200 dark:border-zinc-800">
                            <div>
                                <span class="block text-[9px] text-zinc-400 dark:text-zinc-500 uppercase font-semibold">Nama Pengguna</span>
                                <span class="font-medium text-zinc-700 dark:text-zinc-200" x-text="t.user_name || '-'"></span>
                            </div>
                            <div>
                                <span class="block text-[9px] text-zinc-400 dark:text-zinc-500 uppercase font-semibold">Kontak Pengguna</span>
                                <span class="font-medium text-zinc-700 dark:text-zinc-200" x-text="t.user_contact || '-'"></span>
                            </div>
                            <div class="col-span-2">
                                <span class="block text-[9px] text-zinc-400 dark:text-zinc-500 uppercase font-semibold">Konektor</span>
                                <span class="font-medium text-zinc-700 dark:text-zinc-200 block truncate" x-text="t.notes || '-'" :title="t.notes"></span>
                            </div>
                        </div>
                        
                        <div class="border-t border-zinc-200 dark:border-zinc-800 pt-3 flex justify-end gap-3 font-display">
                            <button x-show="currentUserRole === 'admin'" @click="deleteTicket(t)"
                                    class="inline-flex items-center gap-1 text-xs font-semibold text-red-600 dark:text-red-400 hover:text-red-950 dark:hover:text-red-200 cursor-pointer">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-3.5 h-3.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                </svg>
                                Hapus
                            </button>
                            <a :href="'/tickets/' + t.uuid" 
                               class="inline-flex items-center gap-1.5 text-xs font-semibold text-emerald-600 hover:text-emerald-750 dark:text-emerald-400 dark:hover:text-emerald-300 border border-emerald-200 dark:border-emerald-500/20 bg-emerald-500/5 hover:bg-emerald-50 dark:hover:bg-emerald-950/30 px-3 py-1.5 rounded-lg transition-all">
                                Detail
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-3.5 h-3.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                                </svg>
                            </a>
                        </div>
                    </div>
                </template>
                <!-- Mobile search fallback -->
                <div x-show="filteredTickets().length === 0" class="py-8 text-center text-zinc-500">
                    Tidak ada tiket yang cocok dengan filter saat ini.
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
                <h3 class="text-lg font-bold text-zinc-900 dark:text-white">Buat Tiket Pemasangan Baru</h3>
                <button @click="showCreateModal = false" class="text-zinc-500 hover:text-zinc-700 dark:hover:text-zinc-300 cursor-pointer">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <form @submit.prevent="createTicket" class="space-y-4">
                <!-- Error Banner -->
                <div x-show="errorMessage" 
                     class="bg-red-50 dark:bg-red-950/30 border border-red-200 dark:border-red-905 text-red-600 dark:text-red-400 p-3 rounded-lg text-xs font-semibold" 
                     style="display: none;" 
                     x-text="errorMessage"></div>



                <div class="max-h-[calc(100vh-20rem)] overflow-y-auto pr-1 space-y-4">
                    <!-- Tab 1: User & Network Details -->
                    <div x-show="activeCreateTab === 'network'" class="space-y-4">
                        <div>
                            <label class="text-xs text-zinc-500 dark:text-zinc-400 font-semibold block mb-1">Label Tiket</label>
                            <input type="text" x-model="newTicket.label" placeholder="PO-00001"
                                   class="w-full bg-zinc-50 dark:bg-zinc-950 border border-zinc-200 dark:border-zinc-800 rounded-lg px-3 py-2 text-sm text-zinc-900 dark:text-white focus:outline-none focus:border-red-600 transition-colors">
                            <div class="mt-2 flex flex-wrap gap-1.5 items-center">
                                <span class="text-[10px] text-zinc-500 dark:text-zinc-400 font-medium">Templat Cepat:</span>
                                <button type="button" @click="newTicket.label = generateNextLabel('PO-')"
                                        class="px-2.5 py-1 rounded border border-zinc-200 dark:border-zinc-800 bg-zinc-100/50 hover:bg-zinc-200/50 dark:bg-zinc-900/50 dark:hover:bg-zinc-800/50 active:scale-95 transition-all text-[10px] font-bold text-zinc-700 dark:text-zinc-300 cursor-pointer">
                                    PO (Pre Order)
                                </button>
                                <button type="button" @click="newTicket.label = generateNextLabel('UP-')"
                                        class="px-2.5 py-1 rounded border border-zinc-200 dark:border-zinc-800 bg-zinc-100/50 hover:bg-zinc-200/50 dark:bg-zinc-900/50 dark:hover:bg-zinc-800/50 active:scale-95 transition-all text-[10px] font-bold text-zinc-700 dark:text-zinc-300 cursor-pointer">
                                    PO Uplink
                                </button>
                                <button type="button" @click="newTicket.label = generateNextLabel('DIS-')"
                                        class="px-2.5 py-1 rounded border border-zinc-200 dark:border-zinc-800 bg-zinc-100/50 hover:bg-zinc-200/50 dark:bg-zinc-900/50 dark:hover:bg-zinc-800/50 active:scale-95 transition-all text-[10px] font-bold text-zinc-700 dark:text-zinc-300 cursor-pointer">
                                    DIS (PO Dismantle)
                                </button>
                                <button type="button" @click="newTicket.label = generateNextLabel('SRV-')"
                                        class="px-2.5 py-1 rounded border border-zinc-200 dark:border-zinc-800 bg-zinc-100/50 hover:bg-zinc-200/50 dark:bg-zinc-900/50 dark:hover:bg-zinc-800/50 active:scale-95 transition-all text-[10px] font-bold text-zinc-700 dark:text-zinc-300 cursor-pointer">
                                    SRV (Survey)
                                </button>
                                <button type="button" @click="newTicket.label = generateNextLabel('ERR-')"
                                        class="px-2.5 py-1 rounded border border-zinc-200 dark:border-zinc-800 bg-zinc-100/50 hover:bg-zinc-200/50 dark:bg-zinc-900/50 dark:hover:bg-zinc-800/50 active:scale-95 transition-all text-[10px] font-bold text-zinc-700 dark:text-zinc-300 cursor-pointer">
                                    ERR (Error)
                                </button>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="text-xs text-zinc-500 dark:text-zinc-400 font-semibold block mb-1">Nama Pengguna</label>
                                <input type="text" x-model="newTicket.user_name" placeholder="PT Maju Jaya"
                                       class="w-full bg-zinc-50 dark:bg-zinc-950 border border-zinc-200 dark:border-zinc-800 rounded-lg px-3 py-2 text-sm text-zinc-900 dark:text-white focus:outline-none focus:border-red-600 transition-colors">
                            </div>
                            <div>
                                <label class="text-xs text-zinc-500 dark:text-zinc-400 font-semibold block mb-1">Kontak Pengguna</label>
                                <input type="text" x-model="newTicket.user_contact" placeholder="08123456789"
                                       class="w-full bg-zinc-50 dark:bg-zinc-950 border border-zinc-200 dark:border-zinc-800 rounded-lg px-3 py-2 text-sm text-zinc-900 dark:text-white focus:outline-none focus:border-red-600 transition-colors">
                            </div>
                        </div>


                        <div>
                            <label class="text-xs text-zinc-500 dark:text-zinc-400 font-semibold block mb-1">Alamat</label>
                            <textarea x-model="newTicket.alamat" placeholder="Jalan Kaliurang KM 5, Sleman, Yogyakarta" rows="2"
                                      class="w-full bg-zinc-50 dark:bg-zinc-950 border border-zinc-200 dark:border-zinc-800 rounded-lg px-3 py-2 text-sm text-zinc-900 dark:text-white focus:outline-none focus:border-red-600 transition-colors resize-none"></textarea>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="text-xs text-zinc-500 dark:text-zinc-400 font-semibold block mb-1">Titik Koordinat</label>
                                <input type="text" x-model="newTicket.titik_koordinat" placeholder="-7.756123, 110.378901"
                                       class="w-full bg-zinc-50 dark:bg-zinc-950 border border-zinc-200 dark:border-zinc-800 rounded-lg px-3 py-2 text-sm text-zinc-900 dark:text-white focus:outline-none focus:border-red-600 transition-colors">
                            </div>
                            <div>
                                <label class="text-xs text-zinc-500 dark:text-zinc-400 font-semibold block mb-1">Link Maps</label>
                                <input type="text" x-model="newTicket.link_maps" placeholder="https://maps.google.com/..."
                                       class="w-full bg-zinc-50 dark:bg-zinc-950 border border-zinc-200 dark:border-zinc-800 rounded-lg px-3 py-2 text-sm text-zinc-900 dark:text-white focus:outline-none focus:border-red-600 transition-colors">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="text-xs text-zinc-500 dark:text-zinc-400 font-semibold block mb-1">Backhaul</label>
                                <input type="text" x-model="newTicket.backhaul" placeholder="BH-EAST-01"
                                       class="w-full bg-zinc-50 dark:bg-zinc-950 border border-zinc-200 dark:border-zinc-800 rounded-lg px-3 py-2 text-sm text-zinc-900 dark:text-white focus:outline-none focus:border-red-600 transition-colors">
                            </div>
                            <div>
                                <label class="text-xs text-zinc-500 dark:text-zinc-400 font-semibold block mb-1">Layanan</label>
                                <input type="text" x-model="newTicket.metro" placeholder="Metro / IPT / T2IX"
                                       class="w-full bg-zinc-50 dark:bg-zinc-950 border border-zinc-200 dark:border-zinc-800 rounded-lg px-3 py-2 text-sm text-zinc-900 dark:text-white focus:outline-none focus:border-red-600 transition-colors">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="text-xs text-zinc-500 dark:text-zinc-400 font-semibold block mb-1">Lokasi Destinasi</label>
                                <input type="text" x-model="newTicket.destination_site" placeholder="Gedung Cyber 1"
                                       class="w-full bg-zinc-50 dark:bg-zinc-950 border border-zinc-200 dark:border-zinc-800 rounded-lg px-3 py-2 text-sm text-zinc-900 dark:text-white focus:outline-none focus:border-red-600 transition-colors">
                            </div>
                            <div>
                                <label class="text-xs text-zinc-500 dark:text-zinc-400 font-semibold block mb-1">Kapasitas</label>
                                <input type="text" x-model="newTicket.capacity" placeholder="10 Gbps"
                                       class="w-full bg-zinc-50 dark:bg-zinc-950 border border-zinc-200 dark:border-zinc-800 rounded-lg px-3 py-2 text-sm text-zinc-900 dark:text-white focus:outline-none focus:border-red-600 transition-colors">
                            </div>
                        </div>

                        <div>
                            <label class="text-xs text-zinc-500 dark:text-zinc-400 font-semibold block mb-1">Keterangan</label>
                            <textarea x-model="newTicket.keterangan" placeholder="Masukkan keterangan tambahan tiket..." rows="2"
                                      class="w-full bg-zinc-50 dark:bg-zinc-950 border border-zinc-200 dark:border-zinc-800 rounded-lg px-3 py-2 text-sm text-zinc-900 dark:text-white focus:outline-none focus:border-red-600 transition-colors resize-none"></textarea>
                        </div>
                    </div>




                </div>

                <div class="flex justify-end gap-3 pt-4 border-t border-zinc-200 dark:border-zinc-800">
                    <button type="button" @click="showCreateModal = false"
                            class="bg-zinc-100 hover:bg-zinc-200 dark:bg-zinc-800 dark:hover:bg-zinc-700 text-xs font-semibold px-4 py-2.5 rounded-lg text-zinc-700 dark:text-zinc-350 transition-colors cursor-pointer">
                        Batal
                    </button>
                    <button type="submit"
                            class="bg-red-600 hover:bg-red-750 dark:hover:bg-red-500 text-xs font-semibold px-4 py-2.5 rounded-lg text-white transition-colors cursor-pointer">
                        Kirim Tiket
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
    <?php if (isset($component)) { $__componentOriginal1c457ba5b5f542f2dd7db6cfc7787fdc = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal1c457ba5b5f542f2dd7db6cfc7787fdc = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.changelog','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('changelog'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal1c457ba5b5f542f2dd7db6cfc7787fdc)): ?>
<?php $attributes = $__attributesOriginal1c457ba5b5f542f2dd7db6cfc7787fdc; ?>
<?php unset($__attributesOriginal1c457ba5b5f542f2dd7db6cfc7787fdc); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal1c457ba5b5f542f2dd7db6cfc7787fdc)): ?>
<?php $component = $__componentOriginal1c457ba5b5f542f2dd7db6cfc7787fdc; ?>
<?php unset($__componentOriginal1c457ba5b5f542f2dd7db6cfc7787fdc); ?>
<?php endif; ?>
</body>
</html><?php /**PATH /app/resources/views/dashboard.blade.php ENDPATH**/ ?>