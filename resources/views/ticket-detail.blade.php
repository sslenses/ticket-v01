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
    <title>Detail Tiket - {{ $ticket->label }}</title>
    <link rel="icon" type="image/x-icon" href="https://github.githubassets.com/favicon.ico">
    
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
    <script>
        window.ticketDetailApp = function() {
            return {
                currentRole: '{{ auth()->user() ? auth()->user()->role : 'admin' }}',
                currentStatus: '{{ $ticket->status }}',
                ticketLogs: {!! json_encode($logsData) !!},
                stages: (() => {
                    const labelPrefix = '{{ $ticket->label }}'.toUpperCase();
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
                })(),
                isPO: '{{ $ticket->label }}'.toUpperCase().startsWith('PO-') || '{{ $ticket->label }}'.toUpperCase().startsWith('UP-'),
                isSRV: '{{ $ticket->label }}'.toUpperCase().startsWith('SRV-'),
                showEditModal: false,
                showNoteModal: false,
                noteText: '',
                showDoneModal: false,
                showDoneSurveyModal: false,
                showProvisioningModal: false,
                doneSurveyKeterangan: '',
                provErrorMessage: '',
                provSourceDevice: '',
                provDestinationDevice: '',
                provIpPtp: '',
                provIpPublic: '',
                provVlan: '',
                provDeviceName: '',
                provDevicePort: '',
                btestInputCount: 1,
                doneKeterangan: '',
                doneErrorMessage: '',
                isDoneSubmitting: false,
                activeEditTab: 'network',
                errorMessage: '',
                @if(!$isPublic)
                tenants: @json($tenants),
                sourceTenantSearch: '{{ $ticket->sourceTenant->name ?? '' }}',
                destTenantSearch: '{{ $ticket->destinationTenant->name ?? '' }}',
                sourceTenantDropdownOpen: false,
                destTenantDropdownOpen: false,
                filteredSourceTenants() {
                    return this.tenants.filter(t => t.name.toLowerCase().includes(this.sourceTenantSearch.toLowerCase()));
                },
                filteredDestTenants() {
                    return this.tenants.filter(t => t.name.toLowerCase().includes(this.destTenantSearch.toLowerCase()));
                },
                selectSourceTenant(t) {
                    this.editTicket.source_tenant_id = t.id;
                    this.editTicket.new_source_tenant_name = '';
                    this.sourceTenantSearch = t.name;
                    this.sourceTenantDropdownOpen = false;
                },
                selectDestTenant(t) {
                    this.editTicket.destination_tenant_id = t.id;
                    this.editTicket.new_destination_tenant_name = '';
                    this.destTenantSearch = t.name;
                    this.destTenantDropdownOpen = false;
                },
                markSourceTenantAsNew() {
                    if (!this.sourceTenantSearch.trim()) return;
                    if (this.tenants.some(t => t.name.toLowerCase() === this.sourceTenantSearch.trim().toLowerCase())) return;
                    this.editTicket.source_tenant_id = 'NEW_TENANT';
                    this.editTicket.new_source_tenant_name = this.sourceTenantSearch.trim();
                    this.sourceTenantDropdownOpen = false;
                },
                markDestTenantAsNew() {
                    if (!this.destTenantSearch.trim()) return;
                    if (this.tenants.some(t => t.name.toLowerCase() === this.destTenantSearch.trim().toLowerCase())) return;
                    this.editTicket.destination_tenant_id = 'NEW_TENANT';
                    this.editTicket.new_destination_tenant_name = this.destTenantSearch.trim();
                    this.destTenantDropdownOpen = false;
                },
                onSourceSearchInput() {
                    this.editTicket.new_source_tenant_name = '';
                    const match = this.tenants.find(t => t.name.toLowerCase() === this.sourceTenantSearch.trim().toLowerCase());
                    if (match) {
                        this.editTicket.source_tenant_id = match.id;
                    } else {
                        this.editTicket.source_tenant_id = '';
                    }
                },
                onDestSearchInput() {
                    this.editTicket.new_destination_tenant_name = '';
                    const match = this.tenants.find(t => t.name.toLowerCase() === this.destTenantSearch.trim().toLowerCase());
                    if (match) {
                        this.editTicket.destination_tenant_id = match.id;
                    } else {
                        this.editTicket.destination_tenant_id = '';
                    }
                },
                editBtestInputCount: 1,
                existingBtests: {!! json_encode(is_array($ticket->getCableDetail('btest_proof')) ? $ticket->getCableDetail('btest_proof') : ($ticket->getCableDetail('btest_proof') ? [$ticket->getCableDetail('btest_proof')] : [])) !!},
                existingQos: {!! json_encode($ticket->getCableDetail('qos_proof') ?: null) !!},
                editTicket: {
                    label: {!! json_encode($ticket->label) !!},
                    source_device: {!! json_encode($ticket->source_device) !!},
                    destination_device: {!! json_encode($ticket->destination_device) !!},
                    source_tenant_id: {!! json_encode($ticket->source_tenant_id) !!},
                    destination_tenant_id: {!! json_encode($ticket->destination_tenant_id) !!},
                    new_source_tenant_name: '',
                    new_destination_tenant_name: '',
                    connector_type: {!! json_encode($ticket->connector_type) !!},
                    length: '{{ $ticket->cable_details['length'] ?? '' }}',
                    color: {!! json_encode($ticket->cable_details['color'] ?? '') !!},
                    type: {!! json_encode($ticket->cable_details['type'] ?? 'Single-Mode OS2') !!},
                    user_name: {!! json_encode($ticket->getCableDetail('user_name')) !!},
                    user_contact: {!! json_encode($ticket->getCableDetail('user_contact')) !!},
                    backhaul: {!! json_encode($ticket->getCableDetail('backhaul')) !!},
                    metro: {!! json_encode($ticket->getCableDetail('metro')) !!},
                    destination_site: {!! json_encode($ticket->getCableDetail('destination_site')) !!},
                    capacity: {!! json_encode($ticket->getCableDetail('capacity')) !!},
                    notes: {!! json_encode($ticket->getCableDetail('notes')) !!},
                    alamat: {!! json_encode($ticket->getCableDetail('alamat')) !!},
                    titik_koordinat: {!! json_encode($ticket->getCableDetail('titik_koordinat')) !!},
                    link_maps: {!! json_encode($ticket->getCableDetail('link_maps')) !!},
                    ip_ptp: {!! json_encode($ticket->getCableDetail('ip_ptp')) !!},
                    ip_public: {!! json_encode($ticket->getCableDetail('ip_public')) !!},
                    vlan: {!! json_encode($ticket->getCableDetail('vlan')) !!},
                    device_name: {!! json_encode($ticket->getCableDetail('device_name')) !!},
                    device_port: {!! json_encode($ticket->getCableDetail('device_port')) !!}

                },
                @endif

                getStageIndex(status) {
                    return this.stages.findIndex(s => s.key === status);
                },

                getStageLabel(stage, index) {
                    const labelPrefix = '{{ $ticket->label }}'.toUpperCase();
                    if (labelPrefix.startsWith('PO-') || labelPrefix.startsWith('UP-')) {
                        if (stage.key === 'waiting_destination') return 'Pengajuan Baru';
                        if (stage.key === 'approved_destination') return 'Validasi & Kelayakan';
                        if (stage.key === 'sended_cable') return 'Provisioning / Eksekusi';
                        if (stage.key === 'received_cable') return 'Uji Terima Klien';
                        if (stage.key === 'done') return 'Selesai';
                    }
                    if (labelPrefix.startsWith('ERR-')) {
                        if (stage.key === 'waiting_destination') return 'Pengajuan Baru';
                        if (stage.key === 'approved_destination') return 'Identifikasi & Melokalisir Masalah';
                        if (stage.key === 'approved_admin') return 'Persetujuan & Eskalasi';
                        if (stage.key === 'sended_cable') return 'Provisioning / Eksekusi';
                        if (stage.key === 'received_cable') return 'Uji Terima Klien';
                        if (stage.key === 'done') return 'Selesai';
                    }
                    if (labelPrefix.startsWith('SRV-')) {
                        if (stage.key === 'waiting_destination') return 'Pengajuan Baru';
                        if (stage.key === 'approved_destination') return 'Identifikasi & Verifikasi Lapangan';
                        if (stage.key === 'approved_admin') return 'Persetujuan Desain & Biaya';
                        if (stage.key === 'sended_cable') return 'Provisioning / Eksekusi';
                        if (stage.key === 'received_cable') return 'Uji Terima & Validasi';
                        if (stage.key === 'done') return 'Selesai';
                    }
                    return stage.label;
                },

                getStageLabelByKey(key) {
                    const stage = this.stages.find(s => s.key === key);
                    if (!stage) return key.replace('_', ' ');
                    const index = this.stages.indexOf(stage);
                    return this.getStageLabel(stage, index);
                },

                getTransitionButtonLabel(status) {
                    const labelPrefix = '{{ $ticket->label }}'.toUpperCase();
                    if (status === 'approved_destination') {
                        if (labelPrefix.startsWith('PO-') || labelPrefix.startsWith('UP-')) return 'Validasi & Layak';
                        if (labelPrefix.startsWith('ERR-')) return 'Mulai Identifikasi';
                        if (labelPrefix.startsWith('SRV-')) return 'Identifikasi & Verifikasi';
                        return 'Setujui Destinasi';
                    }
                    if (status === 'approved_admin') {
                        if (labelPrefix.startsWith('PO-') || labelPrefix.startsWith('UP-')) return 'Setujui Kontrak/Biaya';
                        if (labelPrefix.startsWith('ERR-')) return 'Setujui & Eskalasi';
                        if (labelPrefix.startsWith('SRV-')) return 'Setujui Desain & Biaya';
                        return 'Setujui Admin';
                    }
                    if (status === 'sended_cable') {
                        if (labelPrefix.startsWith('PO-') || labelPrefix.startsWith('UP-')) return 'Mulai Provisioning';
                        if (labelPrefix.startsWith('ERR-')) return 'Mulai Eksekusi';
                        if (labelPrefix.startsWith('SRV-')) return 'Mulai Eksekusi';
                        return 'Kirim Kabel';
                    }
                    if (status === 'received_cable') {
                        if (labelPrefix.startsWith('PO-') || labelPrefix.startsWith('UP-')) return 'Kirim Uji Terima';
                        if (labelPrefix.startsWith('ERR-')) return 'Kirim Uji Terima';
                        if (labelPrefix.startsWith('SRV-')) return 'Kirim Hasil Uji Terima';
                        return 'Terima Kabel';
                    }
                    if (status === 'done') {
                        if (labelPrefix.startsWith('PO-') || labelPrefix.startsWith('UP-')) return 'Tanda Uji Terima OK';
                        if (labelPrefix.startsWith('ERR-')) return 'Tanda Uji Terima OK';
                        if (labelPrefix.startsWith('SRV-')) return 'Validasi OK & Selesai';
                        return 'Tandai Selesai';
                    }
                    return 'Lanjutkan';
                },

                isCompleted(status) {
                    return this.getStageIndex(this.currentStatus) >= this.getStageIndex(status);
                },

                isActive(status) {
                    return this.currentStatus === status && status !== 'done';
                },

                ticketCreatedAtDate: '{{ $ticket->created_at ? $ticket->created_at->format("d M Y") : "" }}',
                ticketCreatedAtTime: '{{ $ticket->created_at ? $ticket->created_at->format("H:i") : "" }}',
                ticketCreatorName: '{{ $ticket->logs->first()?->user?->name ?? "Staf Pembuat" }}',

                getExecutor(status) {
                    const log = this.ticketLogs.find(l => l.to === status);
                    if (log) {
                        return { name: log.user, time: log.time, date: log.date };
                    }
                    if (status === 'waiting_destination') {
                        return { 
                            name: this.ticketCreatorName || 'Staf Pembuat', 
                            time: this.ticketCreatedAtTime, 
                            date: this.ticketCreatedAtDate 
                        };
                    }
                    return null;
                },

                async transitionStatus(nextStatus) {
                    const labelPrefix = '{{ $ticket->label }}'.toUpperCase();
                    if (nextStatus === 'done' && (labelPrefix.startsWith('PO-') || labelPrefix.startsWith('UP-'))) {
                        this.showDoneModal = true;
                        this.doneKeterangan = '';
                        this.doneErrorMessage = '';
                        const fabInput = document.getElementById('done_fab_file');
                        const baInput = document.getElementById('done_ba_file');
                        if (fabInput) fabInput.value = '';
                        if (baInput) baInput.value = '';
                        return;
                    }
                    if (nextStatus === 'done' && labelPrefix.startsWith('SRV-')) {
                        this.showDoneSurveyModal = true;
                        this.doneSurveyKeterangan = '';
                        this.doneErrorMessage = '';
                        return;
                    }
                    if (nextStatus === 'received_cable' && (labelPrefix.startsWith('PO-') || labelPrefix.startsWith('UP-'))) {
                        this.showProvisioningModal = true;
                        this.provErrorMessage = '';
                        this.provSourceDevice = '';
                        this.provDestinationDevice = '';
                        this.provIpPtp = '';
                        this.provIpPublic = '';
                        this.provVlan = '';
                        this.provDeviceName = '';
                        this.provDevicePort = '';
                        for (let i = 1; i <= this.btestInputCount; i++) {
                            const bInput = document.getElementById('prov_btest_proof_' + i);
                            if (bInput) bInput.value = '';
                        }
                        this.btestInputCount = 1;
                        const qosInput = document.getElementById('prov_qos_proof');
                        if (qosInput) qosInput.value = '';
                        return;
                    }
                    await this.executeTransition(nextStatus);
                },

                async submitProvisioningTransition() {
                    this.provErrorMessage = '';
                    const qosInput = document.getElementById('prov_qos_proof');
                    let hasBtest = false;
                    for (let i = 1; i <= this.btestInputCount; i++) {
                        const bInput = document.getElementById('prov_btest_proof_' + i);
                        if (bInput && bInput.files.length > 0) hasBtest = true;
                    }
                    if (!hasBtest) {
                        this.provErrorMessage = 'File Screenshot BTest wajib diunggah.';
                        return;
                    }
                    if (!qosInput || !qosInput.files.length) {
                        this.provErrorMessage = 'File Screenshot QoS/Limiter wajib diunggah.';
                        return;
                    }

                    const formData = new FormData();
                    formData.append('source_device', this.provSourceDevice);
                    formData.append('destination_device', this.provDestinationDevice);
                    for (let i = 1; i <= this.btestInputCount; i++) {
                        const bInput = document.getElementById('prov_btest_proof_' + i);
                        if (bInput && bInput.files.length > 0) {
                            formData.append('btest_proof[]', bInput.files[0]);
                        }
                    }
                    formData.append('qos_proof', qosInput.files[0]);
                    formData.append('ip_ptp', this.provIpPtp);
                    formData.append('ip_public', this.provIpPublic);
                    formData.append('vlan', this.provVlan);
                    formData.append('device_name', this.provDeviceName);
                    formData.append('device_port', this.provDevicePort);

                    this.isDoneSubmitting = true; // reusing done submission indicator spinner
                    const success = await this.executeTransition('received_cable', formData);
                    this.isDoneSubmitting = false;

                    if (success !== false) {
                        this.showProvisioningModal = false;
                        if (typeof window.reloadPage === "function") { window.reloadPage(); } else { window.location.reload(); }
                    }
                },

                async executeTransition(nextStatus, formData = null) {
                    try {
                        let response;
                        if (formData) {
                            formData.append('status', nextStatus);
                            response = await fetch(`/api/tickets/{{ $ticket->uuid }}/status`, {
                                method: 'POST',
                                headers: {
                                    'Accept': 'application/json',
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                },
                                body: formData
                            });
                        } else {
                            response = await fetch(`/api/tickets/{{ $ticket->uuid }}/status`, {
                                method: 'PATCH',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'Accept': 'application/json',
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                },
                                body: JSON.stringify({ status: nextStatus })
                            });
                        }
                        if (!response.ok) {
                            let msg = 'Tidak diizinkan';
                            try {
                                const text = await response.text();
                                try {
                                    const data = JSON.parse(text);
                                    msg = data.message || msg;
                                    if (data.errors) {
                                        msg = Object.values(data.errors).flat().join('\n');
                                    }
                                } catch (e) {
                                    const doc = new DOMParser().parseFromString(text, 'text/html');
                                    const title = doc.querySelector('title')?.innerText || '';
                                    const h1 = doc.querySelector('h1')?.innerText || '';
                                    msg = `Server error (${response.status}): ` + (title || h1 || text.substring(0, 300));
                                }
                            } catch (parseError) {
                                msg = 'Server error (' + response.status + ')';
                            }
                            alert('Transisi gagal:\n' + msg);
                            return false;
                        }
                        const data = await response.json();
                        this.currentStatus = data.status;
                        if (typeof window.reloadPage === "function") { window.reloadPage(); } else { window.location.reload(); }
                        return true;
                    } catch (e) {
                        console.error(e);
                        alert('Gagal menghubungi server: ' + e.message);
                        return false;
                    }
                },

                async submitDoneSurveyTransition() {
                    this.doneErrorMessage = '';
                    
                    if (!this.doneSurveyKeterangan || !this.doneSurveyKeterangan.trim()) {
                        this.doneErrorMessage = 'Catatan wajib diisi.';
                        return;
                    }

                    const formData = new FormData();
                    formData.append('_token', '{{ csrf_token() }}');
                    formData.append('keterangan', this.doneSurveyKeterangan.trim());

                    this.isDoneSubmitting = true;
                    try {
                        const success = await this.executeTransition('done', formData);
                        if (success) {
                            this.showDoneSurveyModal = false;
                        }
                    } finally {
                        this.isDoneSubmitting = false;
                    }
                },

                async submitDoneTransition() {
                    this.doneErrorMessage = '';
                    const fabInput = document.getElementById('done_fab_file');
                    const baInput = document.getElementById('done_ba_file');
                    
                    if (!fabInput || !fabInput.files || fabInput.files.length === 0) {
                        this.doneErrorMessage = 'File FAB wajib diunggah.';
                        return;
                    }
                    if (!baInput || !baInput.files || baInput.files.length === 0) {
                        this.doneErrorMessage = 'File BA wajib diunggah.';
                        return;
                    }
                    if (!this.doneKeterangan || !this.doneKeterangan.trim()) {
                        this.doneErrorMessage = 'Keterangan wajib diisi.';
                        return;
                    }

                    const fabFile = fabInput.files[0];
                    const baFile = baInput.files[0];

                    if (fabFile.type !== 'application/pdf' && !fabFile.name.toLowerCase().endsWith('.pdf')) {
                        this.doneErrorMessage = 'File FAB harus berformat PDF.';
                        return;
                    }
                    if (baFile.type !== 'application/pdf' && !baFile.name.toLowerCase().endsWith('.pdf')) {
                        this.doneErrorMessage = 'File BA harus berformat PDF.';
                        return;
                    }

                    const formData = new FormData();
                    formData.append('_token', '{{ csrf_token() }}');
                    formData.append('fab_file', fabFile);
                    formData.append('ba_file', baFile);
                    formData.append('keterangan', this.doneKeterangan.trim());

                    this.isDoneSubmitting = true;
                    try {
                        const success = await this.executeTransition('done', formData);
                        if (success) {
                            this.showDoneModal = false;
                        }
                    } finally {
                        this.isDoneSubmitting = false;
                    }
                },

                validateEditForm() {
                    this.errorMessage = '';
                    if (!this.editTicket.label || !this.editTicket.label.trim()) {
                        this.activeEditTab = 'network';
                        this.errorMessage = 'Label Tiket wajib diisi.';
                        return false;
                    }
                    if (!this.editTicket.user_name || !this.editTicket.user_name.trim()) {
                        this.activeEditTab = 'network';
                        this.errorMessage = 'Nama Pengguna wajib diisi.';
                        return false;
                    }


                    return true;
                },
                async submitNote() {
                    if (!this.noteText.trim()) {
                        alert('Catatan tidak boleh kosong.');
                        return;
                    }
                    try {
                        let resp = await fetch(`/api/tickets/{{ $ticket->uuid }}/note`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'X-Requested-With': 'XMLHttpRequest'
                            },
                            body: JSON.stringify({
                                keterangan: this.noteText
                            })
                        });
                        
                        let data = await resp.json();
                        if (resp.ok) {
                            this.showNoteModal = false;
                            this.noteText = '';
                            if (typeof window.reloadPage === "function") { window.reloadPage(); } else { window.location.reload(); }
                        } else {
                            alert(data.message || 'Gagal menambahkan catatan.');
                        }
                    } catch (e) {
                        alert('Terjadi kesalahan jaringan.');
                    }
                },

                openEditModal() {
                    this.existingBtests = {!! json_encode(is_array($ticket->getCableDetail('btest_proof')) ? $ticket->getCableDetail('btest_proof') : ($ticket->getCableDetail('btest_proof') ? [$ticket->getCableDetail('btest_proof')] : [])) !!};
                    this.existingQos = {!! json_encode($ticket->getCableDetail('qos_proof') ?: null) !!};
                    this.editBtestInputCount = 1;
                    this.showEditModal = true;
                },

                async rollbackTicket() {
                    if (!confirm('Apakah Anda yakin ingin mengembalikan tiket ini ke step sebelumnya?')) return;
                    try {
                        const response = await fetch(`/api/tickets/{{ $ticket->uuid }}/rollback`, {
                            method: 'POST',
                            headers: {
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            }
                        });
                        
                        if (!response.ok) {
                            const err = await response.json();
                            alert(err.message || 'Gagal mengembalikan tiket.');
                            return;
                        }
                        
                        if (typeof window.reloadPage === "function") { window.reloadPage(); } else { window.location.reload(); }
                    } catch (e) {
                        alert('Gagal menghubungi server.');
                    }
                },

                async updateTicket() {
                    if (!this.validateEditForm()) return;
                    try {
                        const formData = new FormData();
                        formData.append('_method', 'PATCH');
                        if(this.editTicket.label) formData.append('label', this.editTicket.label);
                        if(this.editTicket.source_device) formData.append('source_device', this.editTicket.source_device);
                        if(this.editTicket.destination_device) formData.append('destination_device', this.editTicket.destination_device);
                        if(this.editTicket.source_tenant_id) formData.append('source_tenant_id', this.editTicket.source_tenant_id);
                        if(this.editTicket.destination_tenant_id) formData.append('destination_tenant_id', this.editTicket.destination_tenant_id);
                        if(this.editTicket.new_source_tenant_name) formData.append('new_source_tenant_name', this.editTicket.new_source_tenant_name);
                        if(this.editTicket.new_destination_tenant_name) formData.append('new_destination_tenant_name', this.editTicket.new_destination_tenant_name);
                        if(this.editTicket.connector_type) formData.append('connector_type', this.editTicket.connector_type);
                        
                        const cd = this.editTicket;
                        if(cd.length) formData.append('cable_details[length]', parseInt(cd.length));
                        if(cd.color) formData.append('cable_details[color]', cd.color);
                        if(cd.type) formData.append('cable_details[type]', cd.type);
                        if(cd.user_name) formData.append('cable_details[user_name]', cd.user_name);
                        if(cd.user_contact) formData.append('cable_details[user_contact]', cd.user_contact);
                        if(cd.backhaul) formData.append('cable_details[backhaul]', cd.backhaul);
                        if(cd.metro) formData.append('cable_details[metro]', cd.metro);
                        if(cd.destination_site) formData.append('cable_details[destination_site]', cd.destination_site);
                        if(cd.capacity) formData.append('cable_details[capacity]', cd.capacity);
                        if(cd.notes) formData.append('cable_details[notes]', cd.notes);
                        if(cd.alamat) formData.append('cable_details[alamat]', cd.alamat);
                        if(cd.titik_koordinat) formData.append('cable_details[titik_koordinat]', cd.titik_koordinat);
                        if(cd.link_maps) formData.append('cable_details[link_maps]', cd.link_maps);
                        if(cd.ip_ptp) formData.append('cable_details[ip_ptp]', cd.ip_ptp);
                        if(cd.ip_public) formData.append('cable_details[ip_public]', cd.ip_public);
                        if(cd.vlan) formData.append('cable_details[vlan]', cd.vlan);
                        if(cd.device_name) formData.append('cable_details[device_name]', cd.device_name);
                        if(cd.device_port) formData.append('cable_details[device_port]', cd.device_port);
                        
                        this.existingBtests.forEach((proof) => {
                            formData.append('existing_btests[]', proof);
                        });
                        if (this.existingQos) {
                            formData.append('existing_qos', this.existingQos);
                        }

                        for (let i = 1; i <= this.editBtestInputCount; i++) {
                            const bInput = document.getElementById('edit_btest_proof_' + i);
                            if (bInput && bInput.files.length > 0) {
                                formData.append('btest_proof[]', bInput.files[0]);
                            }
                        }
                        const qosInput = document.getElementById('edit_qos_proof');
                        if (qosInput && qosInput.files.length > 0) {
                            formData.append('qos_proof', qosInput.files[0]);
                        }

                        const response = await fetch(`/api/tickets/{{ $ticket->uuid }}`, {
                            method: 'POST',
                            headers: {
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: formData
                        });
                        if (!response.ok) {
                            const err = await response.json();
                            this.errorMessage = err.message || 'Gagal memperbarui tiket.';
                            return;
                        }
                        if (typeof window.reloadPage === "function") { window.reloadPage(); } else { window.location.reload(); }
                    } catch (e) {
                        this.errorMessage = 'Gagal menghubungi server.';
                    }
                }
            };
        }
    </script>
</head>
<body class="min-h-screen flex flex-col bg-zinc-50 dark:bg-zinc-950 text-zinc-900 dark:text-zinc-50 transition-colors duration-300">
    <div id="app-root" class="min-h-screen flex flex-col" x-data="ticketDetailApp()">

    <!-- Top Navigation -->
        @include('layouts.header', ['activeMenu' => 'tickets'])

    <!-- Main Container -->
    <main class="flex-1 mx-auto max-w-7xl w-full px-4 sm:px-6 lg:px-8 py-8 flex flex-col gap-6">
        
        <!-- Back Navigation / Breadcrumb -->
        @if(!$isPublic)
            <div class="flex items-center">
                <a href="/" class="inline-flex items-center gap-1.5 text-xs font-semibold text-zinc-600 hover:text-zinc-950 dark:text-zinc-400 dark:hover:text-zinc-200 transition-colors bg-white dark:bg-zinc-900/40 border border-zinc-200 dark:border-zinc-800/80 px-3 py-1.5 rounded-lg shadow-sm">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-3.5 h-3.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5" />
                    </svg>
                    Kembali ke Dashboard
                </a>
            </div>
        @endif

        <!-- Canceled Alert Banner -->
        <template x-if="currentStatus === 'cancelled'">
            <div class="rounded-2xl border border-red-200 dark:border-red-500/20 bg-red-50 dark:bg-red-500/5 backdrop-blur-xl p-5 flex items-start gap-4 shadow-lg select-none animate-pulse">
                <div class="w-8 h-8 rounded-lg bg-red-100 dark:bg-red-500/10 text-red-655 dark:text-red-400 flex items-center justify-center shrink-0">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m9.75 9.75 4.5 4.5m0-4.5-4.5 4.5M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                    </svg>
                </div>
                <div>
                    <h3 class="text-sm font-bold text-red-700 dark:text-red-400">Tiket ini telah dibatalkan</h3>
                    <p class="text-xs text-zinc-605 dark:text-zinc-400 mt-1">Tiket ini tidak aktif. Transisi status lebih lanjut dan pengubahan detail dikunci secara permanen.</p>
                </div>
            </div>
        </template>
        
        <!-- Ticket Header Card -->
        <section class="relative overflow-hidden rounded-2xl border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900/60 backdrop-blur-xl p-6 md:p-8 flex flex-col md:flex-row md:items-center justify-between gap-6 shadow-md dark:shadow-2xl transition-colors">
            <div class="absolute inset-0 bg-gradient-to-tr from-red-500/5 to-transparent pointer-events-none"></div>
            <div>
                <div class="flex items-center gap-3 mb-2">
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold tracking-wide bg-red-100 dark:bg-red-500/10 text-red-700 dark:text-red-400 border border-red-200 dark:border-red-500/20 uppercase">
                        Detail Tiket
                    </span>
                    <span class="text-zinc-400 dark:text-zinc-500">•</span>
                    <span class="text-zinc-500 dark:text-zinc-400 text-sm">Diperbarui {{ $ticket->updated_at->diffForHumans() }}</span>
                </div>
                <h1 class="text-3xl md:text-4xl font-bold tracking-tight text-zinc-900 dark:text-white mb-2">
                    {{ $ticket->label }}

                </h1>
                <p class="text-zinc-600 dark:text-zinc-400 text-sm md:text-base max-w-2xl">
                    Tiket dari <span class="text-zinc-900 dark:text-zinc-100 font-semibold">{{ $ticket->source_device }}</span> ke <span class="text-zinc-900 dark:text-zinc-100 font-semibold">{{ $ticket->destination_device }}</span>.
                </p>
            </div>
            
            <!-- Quick Status & Actions -->
            <div class="flex flex-col sm:items-end gap-3 shrink-0">
                <div class="text-right">
                    <span class="text-xs text-zinc-500 dark:text-zinc-500 uppercase tracking-widest font-semibold block mb-1">Status Saat Ini</span>
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
                
                @if(!$isPublic)
                    <!-- Interactive Action Button (for testing transitions easily) -->
                    <div class="flex flex-wrap gap-2 justify-end">
                        @can('update', $ticket)
                            <button @click="showNoteModal = true"
                                    class="bg-blue-50 dark:bg-blue-900/50 hover:bg-blue-100 dark:hover:bg-blue-800 border border-blue-200 dark:border-blue-700 active:scale-95 transition-all text-xs font-semibold px-4 py-2 rounded-lg text-blue-700 dark:text-blue-300 shadow-sm cursor-pointer">
                                Catatan
                            </button>
                            <button x-show="currentStatus !== 'done' && currentStatus !== 'cancelled'"
                                    @click="openEditModal()"
                                    class="bg-white hover:bg-zinc-100 dark:bg-zinc-800 dark:hover:bg-zinc-700 border border-zinc-200 dark:border-zinc-700 active:scale-95 transition-all text-xs font-semibold px-4 py-2 rounded-lg text-zinc-700 dark:text-white shadow-sm cursor-pointer">
                                Ubah Detail
                            </button>
                        @endcan
                        @if(auth()->check() && auth()->user()->role === 'admin')
                            <button x-show="currentStatus !== 'waiting_destination' && currentStatus !== 'draft' && currentStatus !== 'cancelled'"
                                    @click="rollbackTicket()"
                                    class="bg-orange-50 dark:bg-orange-900/40 hover:bg-orange-100 dark:hover:bg-orange-800 border border-orange-200 dark:border-orange-700 active:scale-95 transition-all text-xs font-semibold px-4 py-2 rounded-lg text-orange-700 dark:text-orange-300 shadow-sm cursor-pointer flex items-center gap-1.5">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-3.5 h-3.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 15 3 9m0 0 6-6M3 9h12a6 6 0 0 1 0 12h-3" /></svg>
                                Kembalikan Step
                            </button>
                        @endif

                        @can('cancel', $ticket)
                            <button x-show="currentStatus !== 'done' && currentStatus !== 'cancelled'"
                                    @click="if(confirm('Apakah Anda yakin ingin membatalkan tiket ini? Tiket yang dibatalkan tidak dapat digunakan atau diubah lagi.')) transitionStatus('cancelled')"
                                    class="bg-red-50 dark:bg-red-950/80 hover:bg-red-100 dark:hover:bg-red-900 border border-red-200 dark:border-red-800 active:scale-95 transition-all text-xs font-semibold px-4 py-2 rounded-lg text-red-700 dark:text-red-200 shadow-sm cursor-pointer">
                                Batalkan Tiket
                            </button>
                        @endcan

                        <!-- If Waiting Dest -> Approve Dest -->
                        <button x-show="currentStatus === 'waiting_destination' && (currentRole === 'dest_manager' || currentRole === 'admin')"
                                @click="transitionStatus('approved_destination')"
                                class="bg-cyan-600 hover:bg-cyan-500 active:scale-95 transition-all text-xs font-semibold px-4 py-2 rounded-lg text-white shadow-md cursor-pointer">
                            <span x-text="getTransitionButtonLabel('approved_destination')"></span>
                        </button>
                        <!-- If Approved Dest -> Approve Admin (Only for non-PO/non-SRV) -->
                        <button x-show="currentStatus === 'approved_destination' && (currentRole === 'admin' || currentRole === 'teknisi') && !isPO && !isSRV"
                                @click="transitionStatus('approved_admin')"
                                class="bg-emerald-600 hover:bg-emerald-500 active:scale-95 transition-all text-xs font-semibold px-4 py-2 rounded-lg text-white shadow-md cursor-pointer">
                            <span x-text="getTransitionButtonLabel('approved_admin')"></span>
                        </button>
                        <!-- If Approved Dest -> Send Cable (Only for PO or SRV) -->
                        <button x-show="currentStatus === 'approved_destination' && (currentRole === 'admin' || currentRole === 'teknisi') && (isPO || isSRV)"
                                @click="transitionStatus('sended_cable')"
                                class="bg-amber-600 hover:bg-amber-500 active:scale-95 transition-all text-xs font-semibold px-4 py-2 rounded-lg text-white shadow-md cursor-pointer">
                            <span x-text="getTransitionButtonLabel('sended_cable')"></span>
                        </button>
                        <!-- If Approved Admin -> Send Cable (Only for non-PO/non-SRV) -->
                        <button x-show="currentStatus === 'approved_admin' && (currentRole === 'admin' || currentRole === 'teknisi') && !isPO && !isSRV"
                                @click="transitionStatus('sended_cable')"
                                class="bg-amber-600 hover:bg-amber-500 active:scale-95 transition-all text-xs font-semibold px-4 py-2 rounded-lg text-white shadow-md cursor-pointer">
                            <span x-text="getTransitionButtonLabel('sended_cable')"></span>
                        </button>
                        <!-- If Sended Cable -> Receive Cable -->
                        <button x-show="currentStatus === 'sended_cable' && (currentRole === 'admin' || currentRole === 'teknisi')"
                                @click="transitionStatus('received_cable')"
                                class="bg-orange-600 hover:bg-orange-500 active:scale-95 transition-all text-xs font-semibold px-4 py-2 rounded-lg text-white shadow-md cursor-pointer">
                            <span x-text="getTransitionButtonLabel('received_cable')"></span>
                        </button>
                        <!-- If Received Cable -> Mark Done -->
                        <button x-show="currentStatus === 'received_cable' && currentRole === 'admin'"
                                @click="transitionStatus('done')"
                                class="bg-red-600 hover:bg-red-500 active:scale-95 transition-all text-xs font-semibold px-4 py-2 rounded-lg text-white shadow-md cursor-pointer">
                            <span x-text="getTransitionButtonLabel('done')"></span>
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
                    Progress Tiket
                </h2>
                <!-- Dynamic Progress Stats Bar -->
                <div class="flex items-center gap-2 bg-zinc-50 dark:bg-zinc-950/50 border border-zinc-200 dark:border-zinc-800 px-3 py-1.5 rounded-xl self-start sm:self-auto shadow-inner">
                    <span class="text-xs font-semibold uppercase tracking-widest text-zinc-500 dark:text-zinc-400">Kemajuan:</span>
                    <span class="px-2 py-0.5 rounded-full text-xs font-extrabold bg-red-100 dark:bg-red-500/10 text-red-600 dark:text-red-400 border border-red-200 dark:border-red-500/20 shadow-sm"
                          x-text="currentStatus === 'cancelled' ? 'Dibatalkan' : Math.round((getStageIndex(currentStatus) === -1 ? 0 : getStageIndex(currentStatus)) / (stages.length - 1) * 100) + '%'"></span>
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
                    <div class="relative z-10 flex flex-row md:flex-col items-center md:items-center gap-4 md:gap-0 group"
                         :class="stages.length === 5 ? 'md:w-1/5' : 'md:w-1/6'">
                        
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
                                x-text="getStageLabel(stage, index)"></h3>
                            
                            <!-- Execution Metadata (Triggered on status transition) -->
                            <div class="mt-1 flex flex-col md:items-center text-xs">
                                <template x-if="getExecutor(stage.key)">
                                    <div class="space-y-0.5">
                                        <span class="text-zinc-700 dark:text-zinc-200 block font-medium" x-text="getExecutor(stage.key).name"></span>
                                        <span class="text-zinc-500 dark:text-zinc-500 text-[10px]" x-text="getExecutor(stage.key).date + ' @ ' + getExecutor(stage.key).time"></span>
                                    </div>
                                </template>
                                <template x-if="!getExecutor(stage.key)">
                                    <span class="text-zinc-400 dark:text-zinc-650 font-medium"
                                          x-text="isCompleted(stage.key) ? 'Selesai' : 'Menunggu...'"></span>
                                </template>
                            </div>
                        </div>
                    </div>
                </template>
            </div>
        </section>

        <!-- Information details Grid -->
        <section class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            
            <!-- Profile Detail Card -->
            <div class="rounded-2xl border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900/60 backdrop-blur-xl p-6 shadow-md dark:shadow-lg flex flex-col gap-4 transition-colors lg:col-span-2">
                <div class="flex items-center gap-3 pb-3 border-b border-zinc-150 dark:border-zinc-800">
                    <div class="w-8 h-8 rounded-lg bg-emerald-500/10 flex items-center justify-center shrink-0">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-user-check w-6 h-6 text-emerald-500 dark:text-emerald-400 shrink-0">
                            <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/>
                            <circle cx="9" cy="7" r="4"/>
                            <polyline points="16 11 18 13 22 9"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-zinc-900 dark:text-zinc-100">Detail Profil</h3>
                        <p class="text-xs text-zinc-500">Informasi Pengguna & Lokasi</p>
                    </div>
                </div>
                
                <div class="space-y-4 flex-1">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="text-xs text-zinc-500 dark:text-zinc-500 font-semibold block uppercase">Nama Pengguna</label>
                            <span class="text-sm font-semibold text-zinc-800 dark:text-zinc-100">{{ $ticket->getCableDetail('user_name') ?: '—' }}</span>
                        </div>
                        <div>
                            <label class="text-xs text-zinc-500 dark:text-zinc-500 font-semibold block uppercase">Kontak Pengguna</label>
                            @if($ticket->getCableDetail('user_contact'))
                                @php
                                    $rawContact = $ticket->getCableDetail('user_contact');
                                    $cleanContact = preg_replace('/[^0-9]/', '', $rawContact);
                                    if (strpos($cleanContact, '0') === 0) {
                                        $cleanContact = '62' . substr($cleanContact, 1);
                                    }
                                    // Format nomor agar mudah dibaca: 0812 3456 7890
                                    $digits = preg_replace('/[^0-9]/', '', $rawContact);
                                    if (strlen($digits) >= 10) {
                                        $displayContact = substr($digits, 0, 4) . ' ' . substr($digits, 4, 4) . ' ' . substr($digits, 8);
                                    } else {
                                        $displayContact = $rawContact;
                                    }
                                @endphp
                                <a href="https://wa.me/{{ $cleanContact }}" target="_blank" rel="noopener noreferrer" 
                                   class="text-sm text-emerald-600 dark:text-emerald-400 font-semibold hover:underline inline-flex items-center gap-2 mt-0.5">
                                    <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-emerald-500 shrink-0 shadow-sm">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="white" class="w-3.5 h-3.5">
                                            <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                                        </svg>
                                    </span>
                                    {{ $displayContact }}

                                </a>
                            @else
                                <span class="text-sm text-zinc-700 dark:text-zinc-300 font-medium block mt-0.5">—</span>
                            @endif
                        </div>
                    </div>

                    <div class="border-t border-zinc-150 dark:border-zinc-800 pt-3">
                        <label class="text-xs text-zinc-500 dark:text-zinc-500 font-semibold block uppercase">Alamat</label>
                        <span class="text-sm text-zinc-700 dark:text-zinc-300 font-medium">{{ $ticket->getCableDetail('alamat') ?: '—' }}</span>
                    </div>

                    <div class="border-t border-zinc-150 dark:border-zinc-800 pt-3 grid grid-cols-2 gap-4">
                        <div>
                            <label class="text-xs text-zinc-500 dark:text-zinc-500 font-semibold block uppercase">Titik Koordinat</label>
                            @if($ticket->getCableDetail('titik_koordinat'))
                            <a href="https://www.google.com/maps?q={{ urlencode($ticket->getCableDetail('titik_koordinat')) }}" target="_blank" rel="noopener noreferrer"
                               class="text-xs font-mono bg-zinc-50 hover:bg-zinc-100 dark:bg-zinc-950/70 dark:hover:bg-zinc-950 border border-zinc-200 dark:border-zinc-800/80 px-2.5 py-2 rounded-lg select-all text-zinc-700 dark:text-zinc-300 block mt-0.5 w-full truncate transition-colors hover:text-red-650 dark:hover:text-red-400" title="{{ $ticket->getCableDetail('titik_koordinat') }}">
                                {{ $ticket->getCableDetail('titik_koordinat') }}

                            </a>
                            @else
                            <span class="text-sm text-zinc-700 dark:text-zinc-300 font-medium block mt-0.5">—</span>
                            @endif
                        </div>
                        <div>
                            <label class="text-xs text-zinc-500 dark:text-zinc-500 font-semibold block uppercase">Google Maps</label>
                            @if($ticket->getCableDetail('link_maps'))
                            <a href="{{ $ticket->getCableDetail('link_maps') }}" target="_blank" rel="noopener noreferrer"
                               class="w-full flex items-center justify-center gap-1.5 text-xs font-semibold text-red-650 hover:text-white dark:text-red-400 dark:hover:text-white border border-red-200 dark:border-red-500/30 hover:border-red-500 bg-red-500/5 hover:bg-red-500 dark:hover:bg-red-500 px-2.5 py-2 rounded-lg transition-all mt-0.5">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-3.5 h-3.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z" />
                                </svg>
                                Buka Peta
                            </a>
                            @else
                            <span class="text-sm text-zinc-700 dark:text-zinc-300 font-medium block mt-0.5">—</span>
                            @endif
                        </div>
                    </div>

                    <div class="border-t border-zinc-150 dark:border-zinc-800 pt-3 grid grid-cols-3 gap-4">
                        <div>
                            <label class="text-xs text-zinc-500 dark:text-zinc-500 font-semibold block uppercase">Backhaul</label>
                            <span class="text-sm text-zinc-700 dark:text-zinc-300 font-medium truncate block" title="{{ $ticket->getCableDetail('backhaul') ?: '—' }}">{{ $ticket->getCableDetail('backhaul') ?: '—' }}</span>
                        </div>
                        <div>
                            <label class="text-xs text-zinc-500 dark:text-zinc-500 font-semibold block uppercase">Layanan</label>
                            <span class="text-sm text-zinc-700 dark:text-zinc-300 font-medium truncate block" title="{{ $ticket->getCableDetail('metro') ?: '—' }}">{{ $ticket->getCableDetail('metro') ?: '—' }}</span>
                        </div>
                        <div>
                            <label class="text-xs text-zinc-500 dark:text-zinc-500 font-semibold block uppercase">Kapasitas</label>
                            <span class="text-sm font-semibold text-zinc-800 dark:text-zinc-100 truncate block">
                                {{ $ticket->getCableDetail('capacity') ? $ticket->getCableDetail('capacity') . ' Mbps' : '—' }}

                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Cable details Card -->
            <div class="rounded-2xl border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900/60 backdrop-blur-xl p-6 shadow-md dark:shadow-lg flex flex-col gap-4 transition-colors lg:col-span-1 lg:row-span-2">
                <div class="flex items-center gap-3 pb-3 border-b border-zinc-150 dark:border-zinc-800">
                    <div class="w-8 h-8 rounded-lg bg-red-500/10 flex items-center justify-center shrink-0">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-route w-6 h-6 text-red-500 dark:text-red-400 shrink-0">
                            <circle cx="6" cy="19" r="3"/>
                            <path d="M9 19h8.5a3.5 3.5 0 0 0 0-7h-11a3.5 3.5 0 0 1 0-7H15"/>
                            <circle cx="18" cy="5" r="3"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-zinc-900 dark:text-zinc-100">Detail Teknis</h3>
                        <p class="text-xs text-zinc-500">Spesifikasi Teknis & Pemasangan</p>
                    </div>
                </div>
                
                    <div class="space-y-4 flex-1">
                        @if(!$ticket->getCableDetail('ip_ptp') && !$ticket->getCableDetail('ip_public') && !$ticket->getCableDetail('vlan') && !$ticket->getCableDetail('device_name') && !$ticket->getCableDetail('device_port') && !$ticket->getCableDetail('btest_proof') && !$ticket->getCableDetail('qos_proof') && !(auth()->user() && auth()->user()->role === 'admin'))
                        <div class="flex flex-col items-center justify-center py-6 text-zinc-400 dark:text-zinc-600 gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-8 h-8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9.879 7.519c1.171-1.025 3.071-1.025 4.242 0 1.172 1.025 1.172 2.687 0 3.712-.203.179-.43.326-.67.442-.745.361-1.45.999-1.45 1.827v.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 5.25h.008v.008H12v-.008Z" />
                            </svg>
                            <span class="text-xs font-medium">Belum ada detail teknis</span>
                        </div>
                        @endif

                        @if($ticket->getCableDetail('ip_ptp') || $ticket->getCableDetail('ip_public') || $ticket->getCableDetail('vlan') || $ticket->getCableDetail('device_name') || $ticket->getCableDetail('device_port'))
                        <div class="space-y-3">
                            <h4 class="text-xs font-bold text-zinc-400 dark:text-zinc-500 uppercase tracking-wider">Konfigurasi Network & Perangkat</h4>
                            <div class="grid grid-cols-2 gap-4">
                                @if($ticket->getCableDetail('ip_ptp'))
                                <div>
                                    <label class="text-[10px] text-zinc-500 dark:text-zinc-500 font-semibold block uppercase">IP PTP</label>
                                    <span class="text-sm font-semibold font-mono text-zinc-800 dark:text-zinc-100 select-all">{{ $ticket->getCableDetail('ip_ptp') }}</span>
                                </div>
                                @endif
                                @if($ticket->getCableDetail('ip_public'))
                                <div>
                                    <label class="text-[10px] text-zinc-500 dark:text-zinc-500 font-semibold block uppercase">IP Public</label>
                                    <span class="text-sm font-semibold font-mono text-zinc-800 dark:text-zinc-100 select-all">{{ $ticket->getCableDetail('ip_public') }}</span>
                                </div>
                                @endif
                            </div>
                            <div class="grid grid-cols-3 gap-4">
                                @if($ticket->getCableDetail('vlan'))
                                <div>
                                    <label class="text-[10px] text-zinc-500 dark:text-zinc-500 font-semibold block uppercase">VLAN</label>
                                    <span class="text-sm font-semibold text-zinc-800 dark:text-zinc-100 select-all">{{ $ticket->getCableDetail('vlan') }}</span>
                                </div>
                                @endif
                                @if($ticket->getCableDetail('device_name'))
                                <div>
                                    <label class="text-[10px] text-zinc-500 dark:text-zinc-500 font-semibold block uppercase">Perangkat</label>
                                    <span class="text-sm text-zinc-700 dark:text-zinc-300 font-medium select-all">{{ $ticket->getCableDetail('device_name') }}</span>
                                </div>
                                @endif
                                @if($ticket->getCableDetail('device_port'))
                                <div>
                                    <label class="text-[10px] text-zinc-500 dark:text-zinc-500 font-semibold block uppercase">Port</label>
                                    <span class="text-sm text-zinc-700 dark:text-zinc-300 font-medium select-all">{{ $ticket->getCableDetail('device_port') }}</span>
                                </div>
                                @endif
                            </div>
                        </div>
                        @endif

                        @if($ticket->getCableDetail('btest_proof') || $ticket->getCableDetail('qos_proof'))
                        <div class="@if($ticket->getCableDetail('ip_ptp') || $ticket->getCableDetail('ip_public') || $ticket->getCableDetail('vlan') || $ticket->getCableDetail('device_name') || $ticket->getCableDetail('device_port')) border-t border-zinc-150 dark:border-zinc-800 pt-3 @endif space-y-3">
                            <h4 class="text-xs font-bold text-zinc-400 dark:text-zinc-500 uppercase tracking-wider">Lampiran Bukti Uji Terima</h4>
                            <div class="grid grid-cols-2 gap-4">
                                @if($ticket->getCableDetail('btest_proof'))
                                @php
                                    $btestProofs = is_array($ticket->getCableDetail('btest_proof')) ? $ticket->getCableDetail('btest_proof') : [$ticket->getCableDetail('btest_proof')];
                                @endphp
                                @foreach($btestProofs as $proof)
                                <div>
                                    <label class="text-[10px] text-zinc-500 dark:text-zinc-500 font-semibold block uppercase mb-1">Bukti BTest {{ count($btestProofs) > 1 ? $loop->iteration : '' }}</label>
                                    <a href="{{ $proof }}" target="_blank" class="block group relative rounded-lg overflow-hidden border border-zinc-200 dark:border-zinc-800 bg-zinc-50 dark:bg-zinc-950 aspect-[4/3] transition-all hover:border-red-500">
                                        <img src="{{ $proof }}" class="w-full h-full object-cover transition-transform group-hover:scale-105" alt="BTest Proof">
                                        <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 flex items-center justify-center transition-opacity text-white text-xs font-bold gap-1">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.43 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                                                <circle cx="12" cy="12" r="3" />
                                            </svg>
                                            Lihat
                                        </div>
                                    </a>
                                </div>
                                @endforeach
                                @endif
                                @if($ticket->getCableDetail('qos_proof'))
                                <div>
                                    <label class="text-[10px] text-zinc-500 dark:text-zinc-500 font-semibold block uppercase mb-1">QoS / Limiter</label>
                                    <a href="{{ $ticket->getCableDetail('qos_proof') }}" target="_blank" class="block group relative rounded-lg overflow-hidden border border-zinc-200 dark:border-zinc-800 bg-zinc-50 dark:bg-zinc-950 aspect-[4/3] transition-all hover:border-red-500">
                                        <img src="{{ $ticket->getCableDetail('qos_proof') }}" class="w-full h-full object-cover transition-transform group-hover:scale-105" alt="QoS Proof">
                                        <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 flex items-center justify-center transition-opacity text-white text-xs font-bold gap-1">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.43 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                                                <circle cx="12" cy="12" r="3" />
                                            </svg>
                                            Lihat
                                        </div>
                                    </a>
                                </div>
                                @endif
                            </div>
                        </div>
                        @endif

                        
                        @if(auth()->user() && auth()->user()->role === 'admin')
                        <div class="bg-zinc-50 dark:bg-zinc-950/50 rounded-xl p-3 border border-zinc-200 dark:border-zinc-800 transition-colors mt-auto">
                            <label class="text-xs text-zinc-500 dark:text-zinc-500 font-semibold block uppercase mb-1">Detail Metadata JSON</label>
                            <pre class="text-xs font-mono text-emerald-700 dark:text-emerald-400 max-h-40 overflow-y-auto overflow-x-auto select-all p-1 whitespace-pre-wrap">{{ json_encode($ticket->cable_details ?? [], JSON_PRETTY_PRINT) }}</pre>
                        </div>
                        @endif
                    </div>
            </div>

            <!-- Source Device Card -->
            <div class="rounded-2xl border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900/60 backdrop-blur-xl p-6 shadow-md dark:shadow-lg flex flex-col gap-4 transition-colors lg:col-span-1">
                <div class="flex items-center gap-3 pb-3 border-b border-zinc-150 dark:border-zinc-800">
                    <div class="w-8 h-8 rounded-lg bg-indigo-500/10 flex items-center justify-center shrink-0">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-server w-6 h-6 text-indigo-500 dark:text-indigo-400 shrink-0">
                            <rect width="20" height="8" x="2" y="2" rx="2" ry="2"/>
                            <rect width="20" height="8" x="2" y="14" rx="2" ry="2"/>
                            <line x1="6" x2="6.01" y1="6" y2="6"/>
                            <line x1="6" x2="6.01" y1="18" y2="18"/>
                            <line x1="10" x2="10.01" y1="6" y2="6"/>
                            <line x1="10" x2="10.01" y1="18" y2="18"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-zinc-900 dark:text-zinc-100">Perangkat Asal</h3>
                        <p class="text-xs text-zinc-500">Peralatan Asal</p>
                    </div>
                </div>
                    <div class="space-y-4">
                        <div>
                            <label class="text-xs text-zinc-500 dark:text-zinc-500 font-semibold block uppercase">Nama Perangkat</label>
                            <span class="text-sm text-zinc-800 dark:text-zinc-200 font-medium">{{ $ticket->source_device }}</span>
                        </div>

                    </div>
            </div>

            <!-- Destination Device Card -->
            <div class="rounded-2xl border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900/60 backdrop-blur-xl p-6 shadow-md dark:shadow-lg flex flex-col gap-4 transition-colors lg:col-span-1">
                <div class="flex items-center gap-3 pb-3 border-b border-zinc-150 dark:border-zinc-800">
                    <div class="w-8 h-8 rounded-lg bg-cyan-500/10 flex items-center justify-center shrink-0">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-server w-6 h-6 text-cyan-500 dark:text-cyan-400 shrink-0">
                            <rect width="20" height="8" x="2" y="2" rx="2" ry="2"/>
                            <rect width="20" height="8" x="2" y="14" rx="2" ry="2"/>
                            <line x1="6" x2="6.01" y1="6" y2="6"/>
                            <line x1="6" x2="6.01" y1="18" y2="18"/>
                            <line x1="10" x2="10.01" y1="6" y2="6"/>
                            <line x1="10" x2="10.01" y1="18" y2="18"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-zinc-900 dark:text-zinc-100">Perangkat Tujuan</h3>
                        <p class="text-xs text-zinc-500">Peralatan Target</p>
                    </div>
                </div>
                
                    <div class="space-y-4">
                        <div>
                            <label class="text-xs text-zinc-500 dark:text-zinc-500 font-semibold block uppercase">Nama Perangkat</label>
                            <span class="text-sm text-zinc-800 dark:text-zinc-200 font-medium">{{ $ticket->destination_device }}</span>
                        </div>

                    </div>
            </div>
        </section>

        <!-- Audit log History Section -->
        <section class="rounded-2xl border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900/60 backdrop-blur-xl p-6 md:p-8 shadow-sm dark:shadow-xl min-h-[220px] flex flex-col transition-colors">
            <h2 class="text-xl font-bold mb-6 flex items-center gap-2 text-zinc-900 dark:text-white">
                <span class="w-2 h-5 rounded bg-red-600 inline-block"></span>
                Log Audit & Riwayat Transisi
            </h2>
            
            <!-- Desktop Table View -->
            <div class="overflow-x-auto hidden md:block">
                <table class="w-full border-collapse text-left">
                    <thead>
                        <tr class="border-b border-zinc-200 dark:border-zinc-700 text-xs font-bold text-zinc-600 dark:text-zinc-300 uppercase tracking-wider">
                            <th class="py-3 px-4">Status Awal</th>
                            <th class="py-3 px-4">Status Tujuan</th>
                            <th class="py-3 px-4">PIC</th>
                            <th class="py-3 px-4">Peran</th>
                            <th class="py-3 px-4 text-right">Waktu</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-200 dark:divide-zinc-800 text-sm text-zinc-800 dark:text-zinc-200">
                        @forelse($ticket->logs as $log)
                        <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/50 transition-colors duration-200">
                            <td class="py-3 px-4">
                                <span class="text-zinc-500 dark:text-zinc-400 font-medium" x-text="getStageLabelByKey('{{ $log->from_state }}')"></span>
                            </td>
                            <td class="py-3 px-4">
                                <span class="text-zinc-900 dark:text-zinc-100 font-semibold" x-text="getStageLabelByKey('{{ $log->to_state }}')"></span>
                            </td>
                            <td class="py-3 px-4 font-medium text-zinc-800 dark:text-zinc-200">
                                {{ $log->user->name ?? 'Sistem' }}

                            </td>
                            <td class="py-3 px-4">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold border capitalize" 
                                      :class="{
                                          'bg-red-50 dark:bg-red-500/10 text-red-700 dark:text-red-400 border-red-200 dark:border-red-500/20': '{{ $log->user->role ?? '' }}' === 'admin',
                                          'bg-cyan-50 dark:bg-cyan-500/10 text-cyan-700 dark:text-cyan-400 border-cyan-200 dark:border-cyan-500/20': '{{ $log->user->role ?? '' }}' === 'dest_manager',
                                          'bg-zinc-50 dark:bg-zinc-500/10 text-zinc-700 dark:text-zinc-400 border-zinc-200 dark:border-zinc-500/20': '{{ $log->user->role ?? '' }}' === 'teknisi'
                                      }">
                                    {{ str_replace('_', ' ', $log->user->role ?? 'sistem') }}

                                </span>
                            </td>
                            <td class="py-3 px-4 text-right text-zinc-500 dark:text-zinc-500">
                                {{ $log->created_at->format('d M Y - H:i:s') }}

                            </td>
                        </tr>
                        @if(($log->fab_file && auth()->user() && auth()->user()->role === 'admin') || $log->ba_file || $log->keterangan)
                        <tr class="bg-zinc-50/30 dark:bg-zinc-950/20">
                            <td colspan="5" class="py-2.5 px-4 text-xs">
                                <div class="flex flex-col gap-1.5 text-zinc-600 dark:text-zinc-400 pl-4 border-l-2 border-zinc-200 dark:border-zinc-800">
                                    @if($log->keterangan)
                                    <div class="leading-relaxed">
                                        <span class="font-bold text-zinc-500 dark:text-zinc-400">Keterangan:</span>
                                        <span class="text-zinc-800 dark:text-zinc-200">{{ $log->keterangan }}</span>
                                    </div>
                                    @endif
                                    @if(($log->fab_file && auth()->user() && auth()->user()->role === 'admin') || $log->ba_file)
                                    <div class="flex items-center gap-4 mt-0.5">
                                        @if($log->fab_file && auth()->user() && auth()->user()->role === 'admin')
                                        <a href="{{ $log->fab_file }}" target="_blank" class="inline-flex items-center gap-1.5 text-red-650 hover:text-red-500 dark:text-red-400 dark:hover:text-red-300 font-semibold transition-colors">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-3.5 h-3.5">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                                            </svg>
                                            File FAB (PDF)
                                        </a>
                                        @endif
                                        @if($log->ba_file)
                                        <a href="{{ $log->ba_file }}" target="_blank" class="inline-flex items-center gap-1.5 text-red-650 hover:text-red-500 dark:text-red-400 dark:hover:text-red-300 font-semibold transition-colors">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-3.5 h-3.5">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                                            </svg>
                                            File BA (PDF)
                                        </a>
                                        @endif
                                    </div>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @endif
                        @empty
                        <tr>
                            <td colspan="5" class="py-8 text-center text-zinc-500">
                                Belum ada log yang tercatat untuk tiket ini.
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
                                <span class="text-xs text-zinc-500 dark:text-zinc-400" x-text="getStageLabelByKey('{{ $log->from_state }}')"></span>
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-3 h-3 text-zinc-400">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                                </svg>
                                <span class="text-xs font-bold text-zinc-900 dark:text-zinc-100" x-text="getStageLabelByKey('{{ $log->to_state }}')"></span>
                            </div>
                            <span class="text-[11px] text-zinc-500 dark:text-zinc-500 font-medium">
                                {{ $log->created_at->format('H:i') }}

                            </span>
                        </div>
                        <div class="flex items-center justify-between gap-2 border-t border-zinc-200/60 dark:border-zinc-800/60 pt-2">
                            <div class="flex flex-col">
                                <span class="text-[10px] uppercase font-bold tracking-wider text-zinc-400 dark:text-zinc-500 mb-0.5">Pelaksana</span>
                                <span class="text-sm font-semibold text-zinc-800 dark:text-zinc-200">{{ $log->user->name ?? 'Sistem' }}</span>
                            </div>
                            <div class="flex flex-col items-end">
                                <span class="text-[10px] uppercase font-bold tracking-wider text-zinc-400 dark:text-zinc-500 mb-0.5">Peran</span>
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold border capitalize" 
                                      :class="{
                                          'bg-red-100 dark:bg-red-500/10 text-red-700 dark:text-red-400 border-red-200 dark:border-red-500/20': '{{ $log->user->role ?? '' }}' === 'admin',
                                          'bg-cyan-100 dark:bg-cyan-500/10 text-cyan-700 dark:text-cyan-400 border-cyan-200 dark:border-cyan-500/20': '{{ $log->user->role ?? '' }}' === 'dest_manager',
                                          'bg-zinc-100 dark:bg-zinc-500/10 text-zinc-700 dark:text-zinc-400 border-zinc-200 dark:border-zinc-500/20': '{{ $log->user->role ?? '' }}' === 'teknisi'
                                      }">
                                    {{ str_replace('_', ' ', $log->user->role ?? 'sistem') }}

                                </span>
                            </div>
                        </div>
                        @if(($log->fab_file && auth()->user() && auth()->user()->role === 'admin') || $log->ba_file || $log->keterangan)
                        <div class="mt-2 text-xs bg-zinc-100 dark:bg-zinc-800/60 p-3 rounded-lg flex flex-col gap-1.5 border border-zinc-200/50 dark:border-zinc-800/50 text-left">
                            @if($log->keterangan)
                            <div>
                                <span class="font-bold text-zinc-500 dark:text-zinc-400">Keterangan:</span>
                                <p class="text-zinc-800 dark:text-zinc-200 mt-0.5 leading-relaxed">{{ $log->keterangan }}</p>
                            </div>
                            @endif
                            @if(($log->fab_file && auth()->user() && auth()->user()->role === 'admin') || $log->ba_file)
                            <div class="flex flex-wrap gap-3 mt-1 pt-1.5 border-t border-zinc-250 dark:border-zinc-700/50">
                                @if($log->fab_file && auth()->user() && auth()->user()->role === 'admin')
                                <a href="{{ $log->fab_file }}" target="_blank" class="inline-flex items-center gap-1.5 text-red-650 hover:text-red-500 dark:text-red-400 dark:hover:text-red-300 font-semibold transition-colors">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-3.5 h-3.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                                    </svg>
                                    FAB (PDF)
                                </a>
                                @endif
                                @if($log->ba_file)
                                <a href="{{ $log->ba_file }}" target="_blank" class="inline-flex items-center gap-1.5 text-red-650 hover:text-red-500 dark:text-red-400 dark:hover:text-red-300 font-semibold transition-colors">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-3.5 h-3.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                                    </svg>
                                    BA (PDF)
                                </a>
                                @endif
                            </div>
                            @endif
                        </div>
                        @endif
                        <div class="text-[10px] text-zinc-500 dark:text-zinc-500 text-right">
                            {{ $log->created_at->format('d M Y') }}

                        </div>
                    </div>
                @empty
                    <div class="py-8 text-center text-zinc-500">
                        Belum ada log yang tercatat untuk tiket ini.
                    </div>
                @endforelse
            </div>
        </section>
        
    </main>

    @if(!$isPublic)
    <!-- Note Modal -->
    <div x-show="showNoteModal" style="display: none;"
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-zinc-950/80 backdrop-blur-sm"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95">

        <div class="relative w-full max-w-md bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-2xl p-6 shadow-2xl flex flex-col gap-6"
             @click.away="showNoteModal = false">

            <div class="flex items-center justify-between pb-3 border-b border-zinc-200 dark:border-zinc-800">
                <h3 class="text-lg font-bold text-zinc-900 dark:text-white">Tambah Catatan</h3>
                <button @click="showNoteModal = false" class="text-zinc-400 hover:text-zinc-600 dark:text-zinc-500 dark:hover:text-zinc-300 cursor-pointer">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <div>
                <label class="text-sm font-semibold text-zinc-700 dark:text-zinc-300 block mb-2">Catatan / Keterangan Tambahan</label>
                <textarea x-model="noteText" rows="4" placeholder="Tuliskan catatan untuk tiket ini..."
                          class="w-full bg-zinc-50 dark:bg-zinc-950 border border-zinc-200 dark:border-zinc-800 rounded-xl p-3 text-sm text-zinc-900 dark:text-white focus:outline-none focus:border-blue-500 transition-colors resize-none"></textarea>
            </div>

            <div class="flex justify-end gap-3 pt-4 border-t border-zinc-200 dark:border-zinc-800">
                <button type="button" @click="showNoteModal = false"
                        class="bg-zinc-100 hover:bg-zinc-200 dark:bg-zinc-800 dark:hover:bg-zinc-700 text-sm font-semibold px-4 py-2 rounded-lg text-zinc-700 dark:text-zinc-300 transition-colors cursor-pointer">
                    Batal
                </button>
                <button type="button" @click="submitNote()"
                        class="bg-blue-600 hover:bg-blue-700 text-sm font-semibold px-4 py-2 rounded-lg text-white shadow-md active:scale-95 transition-all cursor-pointer">
                    Simpan Catatan
                </button>
            </div>
        </div>
    </div>

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
                <h3 class="text-lg font-bold text-zinc-900 dark:text-white">Ubah Detail Tiket</h3>
                <button @click="showEditModal = false" class="text-zinc-400 hover:text-zinc-600 dark:text-zinc-500 dark:hover:text-zinc-300 cursor-pointer">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <!-- Error Banner -->
            <div x-show="errorMessage" 
                 class="bg-red-50 dark:bg-red-950/30 border border-red-200 dark:border-red-900 text-red-600 dark:text-red-400 p-3 rounded-lg text-xs font-semibold" 
                 style="display: none;" 
                 x-text="errorMessage"></div>



            
            <!-- Tab Navigation -->
            <div class="flex border-b border-zinc-200 dark:border-zinc-800 mb-4">
                <button type="button" @click="activeEditTab = 'network'"
                        :class="activeEditTab === 'network' ? 'border-red-600 text-red-600 dark:text-red-500' : 'border-transparent text-zinc-500 hover:text-zinc-700 dark:text-zinc-400 dark:hover:text-zinc-300'"
                        class="px-4 py-2 border-b-2 font-semibold text-sm transition-colors cursor-pointer">
                    Informasi Jaringan
                </button>
                <button type="button" @click="activeEditTab = 'technical'"
                        :class="activeEditTab === 'technical' ? 'border-red-600 text-red-600 dark:text-red-500' : 'border-transparent text-zinc-500 hover:text-zinc-700 dark:text-zinc-400 dark:hover:text-zinc-300'"
                        class="px-4 py-2 border-b-2 font-semibold text-sm transition-colors cursor-pointer">
                    Detail Teknis
                </button>
            </div>

            <form @submit.prevent="updateTicket" class="space-y-4" enctype="multipart/form-data">
                <div class="max-h-[calc(100vh-20rem)] overflow-y-auto pr-1 space-y-4">
                    <!-- Tab 1: User & Network Details -->
                    <div x-show="activeEditTab === 'network'" class="space-y-4">
                        <div>
                            <label class="text-xs text-zinc-500 dark:text-zinc-400 font-semibold block mb-1">Label Tiket</label>
                            <input type="text" x-model="editTicket.label" placeholder="PO-00001"
                                   class="w-full bg-zinc-50 dark:bg-zinc-950 border border-zinc-200 dark:border-zinc-800 rounded-lg px-3 py-2 text-sm text-zinc-900 dark:text-white focus:outline-none focus:border-red-600 transition-colors">
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="text-xs text-zinc-500 dark:text-zinc-400 font-semibold block mb-1">Nama Pengguna</label>
                                <input type="text" x-model="editTicket.user_name" placeholder="PT Tonggak Teknologi Netikom"
                                       class="w-full bg-zinc-50 dark:bg-zinc-950 border border-zinc-200 dark:border-zinc-800 rounded-lg px-3 py-2 text-sm text-zinc-900 dark:text-white focus:outline-none focus:border-red-600 transition-colors">
                            </div>
                            <div>
                                <label class="text-xs text-zinc-500 dark:text-zinc-400 font-semibold block mb-1">Kontak Pengguna</label>
                                <input type="text" x-model="editTicket.user_contact" placeholder="08123456789"
                                       class="w-full bg-zinc-50 dark:bg-zinc-950 border border-zinc-200 dark:border-zinc-800 rounded-lg px-3 py-2 text-sm text-zinc-900 dark:text-white focus:outline-none focus:border-red-600 transition-colors">
                            </div>
                        </div>


                        <div>
                            <label class="text-xs text-zinc-500 dark:text-zinc-400 font-semibold block mb-1">Alamat</label>
                            <textarea x-model="editTicket.alamat" placeholder="Jalan Kaliurang KM 5, Sleman, Yogyakarta" rows="2"
                                      class="w-full bg-zinc-50 dark:bg-zinc-950 border border-zinc-200 dark:border-zinc-800 rounded-lg px-3 py-2 text-sm text-zinc-900 dark:text-white focus:outline-none focus:border-red-600 transition-colors resize-none"></textarea>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="text-xs text-zinc-500 dark:text-zinc-400 font-semibold block mb-1">Titik Koordinat</label>
                                <input type="text" x-model="editTicket.titik_koordinat" placeholder="-7.756123, 110.378901"
                                       class="w-full bg-zinc-50 dark:bg-zinc-950 border border-zinc-200 dark:border-zinc-800 rounded-lg px-3 py-2 text-sm text-zinc-900 dark:text-white focus:outline-none focus:border-red-600 transition-colors">
                            </div>
                            <div>
                                <label class="text-xs text-zinc-500 dark:text-zinc-400 font-semibold block mb-1">Link Maps</label>
                                <input type="text" x-model="editTicket.link_maps" placeholder="https://maps.google.com/..."
                                       class="w-full bg-zinc-50 dark:bg-zinc-950 border border-zinc-200 dark:border-zinc-800 rounded-lg px-3 py-2 text-sm text-zinc-900 dark:text-white focus:outline-none focus:border-red-600 transition-colors">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="text-xs text-zinc-500 dark:text-zinc-400 font-semibold block mb-1">Backhaul</label>
                                <input type="text" x-model="editTicket.backhaul" placeholder="BH-EAST-01"
                                       class="w-full bg-zinc-50 dark:bg-zinc-950 border border-zinc-200 dark:border-zinc-800 rounded-lg px-3 py-2 text-sm text-zinc-900 dark:text-white focus:outline-none focus:border-red-600 transition-colors">
                            </div>
                            <div>
                                <label class="text-xs text-zinc-500 dark:text-zinc-400 font-semibold block mb-1">Layanan</label>
                                <input type="text" x-model="editTicket.metro" placeholder="Metro / IPT / T2IX"
                                       class="w-full bg-zinc-50 dark:bg-zinc-950 border border-zinc-200 dark:border-zinc-800 rounded-lg px-3 py-2 text-sm text-zinc-900 dark:text-white focus:outline-none focus:border-red-600 transition-colors">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="text-xs text-zinc-500 dark:text-zinc-400 font-semibold block mb-1">Lokasi Destinasi</label>
                                <input type="text" x-model="editTicket.destination_site" placeholder="Gedung Cyber 1"
                                       class="w-full bg-zinc-50 dark:bg-zinc-950 border border-zinc-200 dark:border-zinc-800 rounded-lg px-3 py-2 text-sm text-zinc-900 dark:text-white focus:outline-none focus:border-red-600 transition-colors">
                            </div>
                            <div>
                                <label class="text-xs text-zinc-500 dark:text-zinc-400 font-semibold block mb-1">Kapasitas</label>
                                <input type="text" x-model="editTicket.capacity" placeholder="10 Gbps"
                                       class="w-full bg-zinc-50 dark:bg-zinc-950 border border-zinc-200 dark:border-zinc-800 rounded-lg px-3 py-2 text-sm text-zinc-900 dark:text-white focus:outline-none focus:border-red-600 transition-colors">
                            </div>
                        </div>
                        
                    </div> <!-- End of network tab -->
                    
                    <!-- Tab 2: Technical Details -->
                    <div x-show="activeEditTab === 'technical'" class="space-y-4" style="display: none;">
                        <div>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="text-xs text-zinc-500 dark:text-zinc-400 font-semibold block mb-1">IP PTP</label>
                                    <input type="text" x-model="editTicket.ip_ptp" placeholder="10.20.30.2/30"
                                           class="w-full bg-zinc-50 dark:bg-zinc-950 border border-zinc-200 dark:border-zinc-800 rounded-lg px-3 py-2 text-sm text-zinc-900 dark:text-white focus:outline-none focus:border-red-600 transition-colors">
                                </div>
                                <div>
                                    <label class="text-xs text-zinc-500 dark:text-zinc-400 font-semibold block mb-1">IP Public</label>
                                    <input type="text" x-model="editTicket.ip_public" placeholder="103.20.30.2"
                                           class="w-full bg-zinc-50 dark:bg-zinc-950 border border-zinc-200 dark:border-zinc-800 rounded-lg px-3 py-2 text-sm text-zinc-900 dark:text-white focus:outline-none focus:border-red-600 transition-colors">
                                </div>
                                <div>
                                    <label class="text-xs text-zinc-500 dark:text-zinc-400 font-semibold block mb-1">VLAN</label>
                                    <input type="text" x-model="editTicket.vlan" placeholder="100"
                                           class="w-full bg-zinc-50 dark:bg-zinc-950 border border-zinc-200 dark:border-zinc-800 rounded-lg px-3 py-2 text-sm text-zinc-900 dark:text-white focus:outline-none focus:border-red-600 transition-colors">
                                </div>
                                <div>
                                    <label class="text-xs text-zinc-500 dark:text-zinc-400 font-semibold block mb-1">Device Name</label>
                                    <input type="text" x-model="editTicket.device_name" placeholder="Sw-Dist"
                                           class="w-full bg-zinc-50 dark:bg-zinc-950 border border-zinc-200 dark:border-zinc-800 rounded-lg px-3 py-2 text-sm text-zinc-900 dark:text-white focus:outline-none focus:border-red-600 transition-colors">
                                </div>
                                <div class="sm:col-span-2">
                                    <label class="text-xs text-zinc-500 dark:text-zinc-400 font-semibold block mb-1">Device Port</label>
                                    <input type="text" x-model="editTicket.device_port" placeholder="sfp-sfpplus1"
                                           class="w-full bg-zinc-50 dark:bg-zinc-950 border border-zinc-200 dark:border-zinc-800 rounded-lg px-3 py-2 text-sm text-zinc-900 dark:text-white focus:outline-none focus:border-red-600 transition-colors">
                                </div>
                            </div>
                        </div>
                        
                        <!-- Lampiran Bukti Uji Terima -->
                        <div class="mt-6 pt-4 border-t border-zinc-200 dark:border-zinc-800">
                            <h4 class="text-sm font-bold text-zinc-900 dark:text-white mb-3">Lampiran Bukti Uji Terima</h4>
                            
                            <div class="space-y-4">
                                <div class="space-y-3">
                                    <label class="text-xs text-zinc-500 dark:text-zinc-400 font-semibold block mb-1">Screenshot BTest (PNG/JPG/JPEG)</label>
                                    
                                    <template x-for="(proof, index) in existingBtests" :key="index">
                                        <div class="flex items-center justify-between w-full bg-zinc-50 dark:bg-zinc-950 border border-zinc-200 dark:border-zinc-800 rounded-lg px-3 py-1.5 text-sm text-zinc-900 dark:text-white transition-colors">
                                            <div class="flex items-center truncate">
                                                <div class="mr-4 py-1 px-2.5 rounded-md border-0 text-xs font-semibold bg-zinc-200 text-zinc-700 dark:bg-zinc-800 dark:text-zinc-300">Choose File</div>
                                                <span class="truncate text-sm" x-text="proof.split('/').pop()"></span>
                                            </div>
                                            <button type="button" @click="existingBtests.splice(index, 1)" class="text-zinc-400 hover:text-red-500 ml-2 flex-shrink-0" title="Hapus">
                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                                            </button>
                                        </div>
                                    </template>

                                    <template x-for="i in editBtestInputCount" :key="'new_' + i">
                                        <div>
                                            <input type="file" :id="'edit_btest_proof_' + i" accept="image/png, image/jpeg, image/jpg"
                                                   @change="if(i === editBtestInputCount && $event.target.files.length > 0) editBtestInputCount++"
                                                   class="w-full bg-zinc-50 dark:bg-zinc-950 border border-zinc-200 dark:border-zinc-800 rounded-lg px-3 py-2 text-sm text-zinc-900 dark:text-white focus:outline-none focus:border-red-650 transition-colors file:mr-4 file:py-1 file:px-2.5 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-zinc-200 file:text-zinc-700 dark:file:bg-zinc-800 dark:file:text-zinc-300 hover:file:bg-zinc-300 dark:hover:file:bg-zinc-700 cursor-pointer">
                                        </div>
                                    </template>
                                </div>

                                <div class="space-y-3">
                                    <label class="text-xs text-zinc-500 dark:text-zinc-400 font-semibold block mb-1">Screenshot QoS/Limiter (PNG/JPG/JPEG)</label>
                                    
                                    <template x-if="existingQos">
                                        <div class="flex items-center justify-between w-full bg-zinc-50 dark:bg-zinc-950 border border-zinc-200 dark:border-zinc-800 rounded-lg px-3 py-1.5 text-sm text-zinc-900 dark:text-white transition-colors">
                                            <div class="flex items-center truncate">
                                                <div class="mr-4 py-1 px-2.5 rounded-md border-0 text-xs font-semibold bg-zinc-200 text-zinc-700 dark:bg-zinc-800 dark:text-zinc-300">Choose File</div>
                                                <span class="truncate text-sm" x-text="existingQos.split('/').pop()"></span>
                                            </div>
                                            <button type="button" @click="existingQos = null" class="text-zinc-400 hover:text-red-500 ml-2 flex-shrink-0" title="Hapus">
                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                                            </button>
                                        </div>
                                    </template>

                                    <template x-if="!existingQos">
                                        <input type="file" id="edit_qos_proof" accept="image/png, image/jpeg, image/jpg"
                                               class="w-full bg-zinc-50 dark:bg-zinc-950 border border-zinc-200 dark:border-zinc-800 rounded-lg px-3 py-2 text-sm text-zinc-900 dark:text-white focus:outline-none focus:border-red-650 transition-colors file:mr-4 file:py-1 file:px-2.5 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-zinc-200 file:text-zinc-700 dark:file:bg-zinc-800 dark:file:text-zinc-300 hover:file:bg-zinc-300 dark:hover:file:bg-zinc-700 cursor-pointer">
                                    </template>
                                </div>
                            </div>
                        </div>
                    </div>


                </div>

                <div class="flex justify-end gap-3 pt-4 border-t border-zinc-200 dark:border-zinc-800">
                    <button type="button" @click="showEditModal = false"
                            class="bg-zinc-100 hover:bg-zinc-200 dark:bg-zinc-800 dark:hover:bg-zinc-700 text-xs font-semibold px-4 py-2.5 rounded-lg text-zinc-700 dark:text-zinc-300 transition-colors cursor-pointer">
                        Batal
                    </button>
                    <button type="submit"
                            class="bg-red-600 hover:bg-red-500 text-xs font-semibold px-4 py-2.5 rounded-lg text-white transition-colors cursor-pointer">
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Done Transition (Uji Terima OK) Modal -->
    <div x-show="showDoneModal" 
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-zinc-950/80 backdrop-blur-sm"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95"
         style="display: none;">
        
        <div class="relative w-full max-w-lg bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-2xl p-6 shadow-2xl flex flex-col gap-6"
             @click.away="showDoneModal = false">
            
            <div class="flex items-center justify-between pb-3 border-b border-zinc-200 dark:border-zinc-800">
                <h3 class="text-lg font-bold text-zinc-900 dark:text-white">Tanda Uji Terima OK</h3>
                <button @click="showDoneModal = false" class="text-zinc-400 hover:text-zinc-600 dark:text-zinc-500 dark:hover:text-zinc-300 cursor-pointer">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <!-- Error Banner -->
            <div x-show="doneErrorMessage" 
                 class="bg-red-50 dark:bg-red-950/30 border border-red-200 dark:border-red-900 text-red-600 dark:text-red-400 p-3 rounded-lg text-xs font-semibold" 
                 x-text="doneErrorMessage"
                 style="display: none;">
            </div>

            <form @submit.prevent="submitDoneTransition()" class="space-y-4" enctype="multipart/form-data">
                <div>
                    <label class="text-xs text-zinc-500 dark:text-zinc-400 font-semibold block mb-1">File FAB (PDF) <span class="text-red-500">*</span></label>
                    <input type="file" id="done_fab_file" accept=".pdf" required :disabled="isDoneSubmitting"
                           class="w-full bg-zinc-50 dark:bg-zinc-950 border border-zinc-200 dark:border-zinc-800 rounded-lg px-3 py-2 text-sm text-zinc-900 dark:text-white focus:outline-none focus:border-red-650 transition-colors file:mr-4 file:py-1 file:px-2.5 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-zinc-200 file:text-zinc-700 dark:file:bg-zinc-800 dark:file:text-zinc-300 hover:file:bg-zinc-300 dark:hover:file:bg-zinc-700"
                           :class="isDoneSubmitting ? 'opacity-50 cursor-not-allowed' : 'cursor-pointer'">
                </div>

                <div>
                    <label class="text-xs text-zinc-500 dark:text-zinc-400 font-semibold block mb-1">File BA (PDF) <span class="text-red-500">*</span></label>
                    <input type="file" id="done_ba_file" accept=".pdf" required :disabled="isDoneSubmitting"
                           class="w-full bg-zinc-50 dark:bg-zinc-950 border border-zinc-200 dark:border-zinc-800 rounded-lg px-3 py-2 text-sm text-zinc-900 dark:text-white focus:outline-none focus:border-red-650 transition-colors file:mr-4 file:py-1 file:px-2.5 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-zinc-200 file:text-zinc-700 dark:file:bg-zinc-800 dark:file:text-zinc-300 hover:file:bg-zinc-300 dark:hover:file:bg-zinc-700"
                           :class="isDoneSubmitting ? 'opacity-50 cursor-not-allowed' : 'cursor-pointer'">
                </div>

                <div>
                    <label class="text-xs text-zinc-500 dark:text-zinc-400 font-semibold block mb-1">Keterangan <span class="text-red-500">*</span></label>
                    <textarea x-model="doneKeterangan" placeholder="Masukkan keterangan penyelesaian uji terima di sini..." rows="3" required :disabled="isDoneSubmitting"
                              class="w-full bg-zinc-50 dark:bg-zinc-950 border border-zinc-200 dark:border-zinc-800 rounded-lg px-3 py-2 text-sm text-zinc-900 dark:text-white focus:outline-none focus:border-red-650 transition-colors resize-none"
                              :class="isDoneSubmitting ? 'opacity-50 cursor-not-allowed' : ''"></textarea>
                </div>

                <div class="flex justify-end gap-3 pt-4 border-t border-zinc-200 dark:border-zinc-800">
                    <button type="button" @click="showDoneModal = false" :disabled="isDoneSubmitting"
                            class="bg-zinc-100 hover:bg-zinc-200 dark:bg-zinc-800 dark:hover:bg-zinc-700 text-xs font-semibold px-4 py-2.5 rounded-lg text-zinc-700 dark:text-zinc-300 transition-colors"
                            :class="isDoneSubmitting ? 'opacity-50 cursor-not-allowed' : 'cursor-pointer'">
                        Batal
                    </button>
                    <button type="submit" :disabled="isDoneSubmitting"
                            class="bg-emerald-600 hover:bg-emerald-500 text-xs font-semibold px-4 py-2.5 rounded-lg text-white transition-colors flex items-center gap-1.5 shadow-md"
                            :class="isDoneSubmitting ? 'opacity-50 cursor-not-allowed bg-emerald-700 hover:bg-emerald-700' : 'cursor-pointer'">
                        <!-- Spinner Icon -->
                        <svg x-show="isDoneSubmitting" class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" style="display: none;">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <!-- Send Icon -->
                        <svg x-show="!isDoneSubmitting" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                        </svg>
                        <span x-text="isDoneSubmitting ? 'Mengunggah...' : 'Kirim Uji Terima OK'"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Done Survey Modal -->
    <div x-show="showDoneSurveyModal" 
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-zinc-950/80 backdrop-blur-sm"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95"
         style="display: none;">
        
        <div class="relative w-full max-w-lg bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-2xl p-6 shadow-2xl flex flex-col gap-6"
             @click.away="showDoneSurveyModal = false">
            
            <div class="flex items-center justify-between pb-3 border-b border-zinc-200 dark:border-zinc-800">
                <h3 class="text-lg font-bold text-zinc-900 dark:text-white">Tandai Selesai</h3>
                <button @click="showDoneSurveyModal = false" class="text-zinc-400 hover:text-zinc-600 dark:text-zinc-500 dark:hover:text-zinc-300 cursor-pointer">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <!-- Error Banner -->
            <div x-show="doneErrorMessage" 
                 class="bg-red-50 dark:bg-red-950/30 border border-red-200 dark:border-red-900 text-red-600 dark:text-red-400 p-3 rounded-lg text-xs font-semibold" 
                 x-text="doneErrorMessage"
                 style="display: none;">
            </div>

            <form @submit.prevent="submitDoneSurveyTransition()" class="space-y-4">
                <div>
                    <label class="text-xs text-zinc-500 dark:text-zinc-400 font-semibold block mb-1">Catatan / Keterangan Tambahan <span class="text-red-500">*</span></label>
                    <textarea x-model="doneSurveyKeterangan" placeholder="Masukkan catatan penyelesaian survey di sini..." rows="3" required :disabled="isDoneSubmitting"
                              class="w-full bg-zinc-50 dark:bg-zinc-950 border border-zinc-200 dark:border-zinc-800 rounded-lg px-3 py-2 text-sm text-zinc-900 dark:text-white focus:outline-none focus:border-red-650 transition-colors resize-none"
                              :class="isDoneSubmitting ? 'opacity-50 cursor-not-allowed' : ''"></textarea>
                </div>

                <div class="flex justify-end gap-3 pt-4 border-t border-zinc-200 dark:border-zinc-800">
                    <button type="button" @click="showDoneSurveyModal = false" :disabled="isDoneSubmitting"
                            class="bg-zinc-100 hover:bg-zinc-200 dark:bg-zinc-800 dark:hover:bg-zinc-700 text-xs font-semibold px-4 py-2.5 rounded-lg text-zinc-700 dark:text-zinc-300 transition-colors"
                            :class="isDoneSubmitting ? 'opacity-50 cursor-not-allowed' : 'cursor-pointer'">
                        Batal
                    </button>
                    <button type="submit" :disabled="isDoneSubmitting"
                            class="bg-emerald-600 hover:bg-emerald-500 text-xs font-semibold px-4 py-2.5 rounded-lg text-white transition-colors flex items-center gap-1.5 shadow-md"
                            :class="isDoneSubmitting ? 'opacity-50 cursor-not-allowed bg-emerald-700 hover:bg-emerald-700' : 'cursor-pointer'">
                        <!-- Spinner Icon -->
                        <svg x-show="isDoneSubmitting" class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" style="display: none;">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <!-- Send Icon -->
                        <svg x-show="!isDoneSubmitting" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                        </svg>
                        <span x-text="isDoneSubmitting ? 'Mengunggah...' : 'Tandai Selesai'"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Provisioning Modal -->
    <div x-show="showProvisioningModal" 
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-zinc-950/80 backdrop-blur-sm"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95"
         style="display: none;">
        
        <div class="relative w-full max-w-lg bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-2xl p-6 shadow-2xl flex flex-col gap-6"
             @click.away="showProvisioningModal = false">
            
            <div class="flex items-center justify-between pb-3 border-b border-zinc-200 dark:border-zinc-800">
                <h3 class="text-lg font-bold text-zinc-900 dark:text-white">Kirim Uji Terima PO</h3>
                <button @click="showProvisioningModal = false" class="text-zinc-400 hover:text-zinc-600 dark:text-zinc-500 dark:hover:text-zinc-300 cursor-pointer">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <!-- Error Banner -->
            <div x-show="provErrorMessage" 
                 class="bg-red-50 dark:bg-red-950/30 border border-red-200 dark:border-red-900 text-red-600 dark:text-red-400 p-3 rounded-lg text-xs font-semibold" 
                 x-text="provErrorMessage"
                 style="display: none;">
            </div>

            <form @submit.prevent="submitProvisioningTransition()" class="space-y-4" enctype="multipart/form-data">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="text-xs text-zinc-500 dark:text-zinc-400 font-semibold block mb-1">Perangkat Asal</label>
                        <input type="text" x-model="provSourceDevice" placeholder="JKT-SW-01" :disabled="isDoneSubmitting"
                               class="w-full bg-zinc-50 dark:bg-zinc-950 border border-zinc-200 dark:border-zinc-800 rounded-lg px-3 py-2 text-sm text-zinc-900 dark:text-white focus:outline-none focus:border-red-650 transition-colors">
                    </div>
                    <div>
                        <label class="text-xs text-zinc-500 dark:text-zinc-400 font-semibold block mb-1">Perangkat Tujuan</label>
                        <input type="text" x-model="provDestinationDevice" placeholder="SG-SW-02" :disabled="isDoneSubmitting"
                               class="w-full bg-zinc-50 dark:bg-zinc-950 border border-zinc-200 dark:border-zinc-800 rounded-lg px-3 py-2 text-sm text-zinc-900 dark:text-white focus:outline-none focus:border-red-650 transition-colors">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="text-xs text-zinc-500 dark:text-zinc-400 font-semibold block mb-1">IP PTP <span class="text-red-500">*</span></label>
                        <input type="text" x-model="provIpPtp" placeholder="10.20.30.2/30" required :disabled="isDoneSubmitting"
                               class="w-full bg-zinc-50 dark:bg-zinc-950 border border-zinc-200 dark:border-zinc-800 rounded-lg px-3 py-2 text-sm text-zinc-900 dark:text-white focus:outline-none focus:border-red-650 transition-colors">
                    </div>
                    <div>
                        <label class="text-xs text-zinc-500 dark:text-zinc-400 font-semibold block mb-1">IP Public <span class="text-red-500">*</span></label>
                        <input type="text" x-model="provIpPublic" placeholder="103.20.30.2" required :disabled="isDoneSubmitting"
                               class="w-full bg-zinc-50 dark:bg-zinc-950 border border-zinc-200 dark:border-zinc-800 rounded-lg px-3 py-2 text-sm text-zinc-900 dark:text-white focus:outline-none focus:border-red-650 transition-colors">
                    </div>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div class="sm:col-span-2">
                        <label class="text-xs text-zinc-500 dark:text-zinc-400 font-semibold block mb-1">Nama Perangkat</label>
                        <input type="text" x-model="provDeviceName" placeholder="Sw-Dist" :disabled="isDoneSubmitting"
                               class="w-full bg-zinc-50 dark:bg-zinc-950 border border-zinc-200 dark:border-zinc-800 rounded-lg px-3 py-2 text-sm text-zinc-900 dark:text-white focus:outline-none focus:border-red-650 transition-colors">
                    </div>
                    <div class="sm:col-span-1">
                        <label class="text-xs text-zinc-500 dark:text-zinc-400 font-semibold block mb-1">VLAN</label>
                        <input type="text" x-model="provVlan" placeholder="100" :disabled="isDoneSubmitting"
                               class="w-full bg-zinc-50 dark:bg-zinc-950 border border-zinc-200 dark:border-zinc-800 rounded-lg px-3 py-2 text-sm text-zinc-900 dark:text-white focus:outline-none focus:border-red-650 transition-colors">
                    </div>
                </div>
                <div>
                    <label class="text-xs text-zinc-500 dark:text-zinc-400 font-semibold block mb-1">Port Perangkat</label>
                    <input type="text" x-model="provDevicePort" placeholder="sfp-sfpplus1" :disabled="isDoneSubmitting"
                           class="w-full bg-zinc-50 dark:bg-zinc-950 border border-zinc-200 dark:border-zinc-800 rounded-lg px-3 py-2 text-sm text-zinc-900 dark:text-white focus:outline-none focus:border-red-650 transition-colors">
                </div>

                <div>
                    <label class="text-xs text-zinc-500 dark:text-zinc-400 font-semibold block mb-1">Screenshot BTest (PNG/JPG/JPEG) <span class="text-red-500">*</span></label>
                    <template x-for="i in btestInputCount" :key="i">
                        <div :class="i > 1 ? 'mt-3' : ''">
                            <input type="file" :id="'prov_btest_proof_' + i" accept="image/png, image/jpeg, image/jpg" :required="i === 1" :disabled="isDoneSubmitting"
                                   @change="if(i === btestInputCount && $event.target.files.length > 0) btestInputCount++"
                                   class="w-full bg-zinc-50 dark:bg-zinc-950 border border-zinc-200 dark:border-zinc-800 rounded-lg px-3 py-2 text-sm text-zinc-900 dark:text-white focus:outline-none focus:border-red-650 transition-colors file:mr-4 file:py-1 file:px-2.5 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-zinc-200 file:text-zinc-700 dark:file:bg-zinc-800 dark:file:text-zinc-300 hover:file:bg-zinc-300 dark:hover:file:bg-zinc-700"
                                   :class="isDoneSubmitting ? 'opacity-50 cursor-not-allowed' : 'cursor-pointer'">
                        </div>
                    </template>
                </div>

                <div>
                    <label class="text-xs text-zinc-500 dark:text-zinc-400 font-semibold block mb-1">Screenshot QoS/Limiter (PNG/JPG/JPEG) <span class="text-red-500">*</span></label>
                    <input type="file" id="prov_qos_proof" accept="image/png, image/jpeg, image/jpg" required :disabled="isDoneSubmitting"
                           class="w-full bg-zinc-50 dark:bg-zinc-950 border border-zinc-200 dark:border-zinc-800 rounded-lg px-3 py-2 text-sm text-zinc-900 dark:text-white focus:outline-none focus:border-red-650 transition-colors file:mr-4 file:py-1 file:px-2.5 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-zinc-200 file:text-zinc-700 dark:file:bg-zinc-800 dark:file:text-zinc-300 hover:file:bg-zinc-300 dark:hover:file:bg-zinc-700"
                           :class="isDoneSubmitting ? 'opacity-50 cursor-not-allowed' : 'cursor-pointer'">
                </div>

                <div class="flex justify-end gap-3 pt-4 border-t border-zinc-200 dark:border-zinc-800">
                    <button type="button" @click="showProvisioningModal = false" :disabled="isDoneSubmitting"
                            class="bg-zinc-100 hover:bg-zinc-200 dark:bg-zinc-800 dark:hover:bg-zinc-700 text-xs font-semibold px-4 py-2.5 rounded-lg text-zinc-700 dark:text-zinc-300 transition-colors"
                            :class="isDoneSubmitting ? 'opacity-50 cursor-not-allowed' : 'cursor-pointer'">
                        Batal
                    </button>
                    <button type="submit" :disabled="isDoneSubmitting"
                            class="bg-amber-600 hover:bg-amber-500 text-xs font-semibold px-4 py-2.5 rounded-lg text-white transition-colors flex items-center gap-1.5 shadow-md"
                            :class="isDoneSubmitting ? 'opacity-50 cursor-not-allowed bg-amber-700 hover:bg-amber-700' : 'cursor-pointer'">
                        <!-- Spinner Icon -->
                        <svg x-show="isDoneSubmitting" class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" style="display: none;">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <!-- Send Icon -->
                        <svg x-show="!isDoneSubmitting" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                        </svg>
                        <span x-text="isDoneSubmitting ? 'Mengunggah...' : 'Kirim Uji Terima'"></span>
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
    </div>
    <x-changelog />
</body>
</html>
