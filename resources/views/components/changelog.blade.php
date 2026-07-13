<div x-data="{ open: false }" @open-changelog.window="open = true">
    <div x-show="open" class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-zinc-950/80 backdrop-blur-sm"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95" style="display: none;">
         
         <div class="bg-white dark:bg-zinc-900 rounded-2xl w-full max-w-lg border border-zinc-200 dark:border-zinc-800 shadow-2xl overflow-hidden flex flex-col max-h-[85vh]">
            <div class="px-6 py-4 border-b border-zinc-200 dark:border-zinc-800 flex justify-between items-center bg-zinc-50 dark:bg-zinc-950/50">
                <h3 class="font-display text-lg font-bold text-zinc-900 dark:text-zinc-100">Changelog & Update</h3>
                <button @click="open = false" class="text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-300 transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            
            <div class="p-6 overflow-y-auto flex-1 space-y-6">
                <!-- Version 1.1.0 -->
                <div class="relative pl-4 border-l-2 border-emerald-500/30">
                    <div class="absolute -left-[5px] top-1.5 w-2 h-2 rounded-full bg-emerald-500 ring-4 ring-white dark:ring-zinc-900"></div>
                    <h4 class="font-bold text-zinc-900 dark:text-zinc-100 text-sm">Version 1.1.0 <span class="text-xs font-normal text-zinc-500 ml-2">13 Juli 2026</span></h4>
                    <ul class="mt-2 text-sm text-zinc-600 dark:text-zinc-400 list-disc list-inside space-y-1">
                        <li>Menambahkan tab khusus <b>Detail Teknis</b> pada formulir <i>Ubah Detail Tiket</i>.</li>
                        <li>Memungkinkan pengubahan data bukti teknis seperti foto pengujian BTest ganda, IP, dan pengaturan QoS dengan dukungan file multiple.</li>
                        <li>Menambahkan tombol dan fitur aksi <b>Kembalikan Step</b> (<i>Rollback</i>) khusus untuk akun ber-<i>role</i> Admin.</li>
                    </ul>
                </div>
                <!-- Version 1.0.9 -->
                <div class="relative pl-4 border-l-2 border-zinc-200 dark:border-zinc-800">
                    <div class="absolute -left-[5px] top-1.5 w-2 h-2 rounded-full bg-zinc-200 dark:bg-zinc-700 ring-4 ring-white dark:ring-zinc-900"></div>
                    <h4 class="font-bold text-zinc-900 dark:text-zinc-100 text-sm">Version 1.0.9 <span class="text-xs font-normal text-zinc-500 ml-2">10 Juli 2026</span></h4>
                    <ul class="mt-2 text-sm text-zinc-600 dark:text-zinc-400 list-disc list-inside space-y-1">
                        <li>Memperbaiki kendala tampilan berkedip (<i>Flash of Unstyled Content</i>) antara warna terang dan gelap saat memuat ulang halaman. Transisi <i>Dark Mode</i> kini mulus seketika.</li>
                    </ul>
                </div>

                <!-- Version 1.0.8 -->
                <div class="relative pl-4 border-l-2 border-zinc-200 dark:border-zinc-800">
                    <div class="absolute -left-[5px] top-1.5 w-2 h-2 rounded-full bg-zinc-200 dark:bg-zinc-700 ring-4 ring-white dark:ring-zinc-900"></div>
                    <h4 class="font-bold text-zinc-900 dark:text-zinc-100 text-sm">Version 1.0.8 <span class="text-xs font-normal text-zinc-500 ml-2">10 Juli 2026</span></h4>
                    <ul class="mt-2 text-sm text-zinc-600 dark:text-zinc-400 list-disc list-inside space-y-1">
                        <li>Mengatur ulang zona waktu (<i>timezone</i>) sistem dan database menjadi <b>Waktu Indonesia Barat (WIB)</b>.</li>
                        <li>Memperbarui kolom unggahan <i>Screenshot BTest</i> menjadi input dinamis; Teknisi kini dapat melampirkan lebih dari satu gambar pengujian secara bertahap.</li>
                    </ul>
                </div>

                <!-- Version 1.0.7 -->
                <div class="relative pl-4 border-l-2 border-zinc-200 dark:border-zinc-800">
                    <div class="absolute -left-[5px] top-1.5 w-2 h-2 rounded-full bg-zinc-200 dark:bg-zinc-700 ring-4 ring-white dark:ring-zinc-900"></div>
                    <h4 class="font-bold text-zinc-900 dark:text-zinc-100 text-sm">Version 1.0.7 <span class="text-xs font-normal text-zinc-500 ml-2">09 Juli 2026</span></h4>
                    <ul class="mt-2 text-sm text-zinc-600 dark:text-zinc-400 list-disc list-inside space-y-1">
                        <li>Menerapkan arsitektur SPA (Single Page Application) penuh untuk navigasi mulus tanpa muat ulang (<i>reload</i>) halaman.</li>
                        <li>Memperbaiki susunan tabel "Antrean & Status Tiket" agar kembali ke standar sistem.</li>
                        <li>Merapikan tata letak Daftar Tiket, menghilangkan tumpang-tindih (<i>overlap</i>) pada <i>progress bar</i>, dan membatasi kolom grid pada layar responsif.</li>
                        <li>Mengubah Kartu Tiket menjadi tautan interaktif yang langsung menuju ke halaman detail tiket menggunakan integrasi SPA.</li>
                        <li>Menambahkan mode Layar Penuh (<i>Fullscreen</i>) khusus pada Daftar Tiket untuk pengalaman <i>monitoring</i> yang lebih bersih.</li>
                    </ul>
                </div>

                <!-- Version 1.0.6 -->
                <div class="relative pl-4 border-l-2 border-zinc-200 dark:border-zinc-800">
                    <div class="absolute -left-[5px] top-1.5 w-2 h-2 rounded-full bg-zinc-200 dark:bg-zinc-700 ring-4 ring-white dark:ring-zinc-900"></div>
                    <h4 class="font-bold text-zinc-900 dark:text-zinc-100 text-sm">Version 1.0.6 <span class="text-xs font-normal text-zinc-500 ml-2">09 Juli 2026</span></h4>
                    <ul class="mt-2 text-sm text-zinc-600 dark:text-zinc-400 list-disc list-inside space-y-1">
                        <li>Penyeragaman <b>style scrollbar</b> (ukuran dan bentuk) di seluruh halaman.</li>
                        <li>Penyeragaman <b>font (huruf) header</b> menggunakan Inter & Outfit di semua halaman.</li>
                        <li>Penyesuaian <b>warna indikator aktif (merah)</b> pada menu navigasi tiket.</li>
                        <li>Penambahan akses bagi <b>Teknisi</b> untuk mengeksekusi tahapan kerja (Step 2 dan 3).</li>
                    </ul>
                </div>

                <!-- Version 1.0.5 -->
                <div class="relative pl-4 border-l-2 border-emerald-500/30">
                    <div class="absolute -left-[5px] top-1.5 w-2 h-2 rounded-full bg-emerald-500 ring-4 ring-white dark:ring-zinc-900"></div>
                    <h4 class="font-bold text-zinc-900 dark:text-zinc-100 text-sm">Version 1.0.5 <span class="text-xs font-normal text-zinc-500 ml-2">09 Juli 2026</span></h4>
                    <ul class="mt-2 text-sm text-zinc-600 dark:text-zinc-400 list-disc list-inside space-y-1">
                        <li>Menambahkan menu Daftar Tiket dengan progress bar keseluruhan secara interaktif.</li>
                        <li>Memasukkan informasi Nama Pengguna ke dalam Daftar Tiket.</li>
                        <li>Mengubah logika tiket jenis Survey (SRV-) untuk *bypass* persetujuan biaya dan wajib catatan saat *done*.</li>
                    </ul>
                </div>

                <!-- Version 1.0.4 -->
                <div class="relative pl-4 border-l-2 border-zinc-200 dark:border-zinc-800">
                    <div class="absolute -left-[5px] top-1.5 w-2 h-2 rounded-full bg-zinc-200 dark:bg-zinc-700 ring-4 ring-white dark:ring-zinc-900"></div>
                    <h4 class="font-bold text-zinc-900 dark:text-zinc-100 text-sm">Version 1.0.4 <span class="text-xs font-normal text-zinc-500 ml-2">08 Juli 2026</span></h4>
                    <ul class="mt-2 text-sm text-zinc-600 dark:text-zinc-400 list-disc list-inside space-y-1">
                        <li>Mengubah hak akses menu <i>Users</i> menjadi khusus untuk <b>Admin</b> saja.</li>
                        <li>Mengganti role <i>Staff</i> menjadi <b>Teknisi</b>.</li>
                    </ul>
                </div>

                <!-- Version 1.0.3 -->
                <div class="relative pl-4 border-l-2 border-zinc-200 dark:border-zinc-800">
                    <div class="absolute -left-[5px] top-1.5 w-2 h-2 rounded-full bg-zinc-200 dark:bg-zinc-700 ring-4 ring-white dark:ring-zinc-900"></div>
                    <h4 class="font-bold text-zinc-900 dark:text-zinc-100 text-sm">Version 1.0.3 <span class="text-xs font-normal text-zinc-500 ml-2">08 Juli 2026</span></h4>
                    <ul class="mt-2 text-sm text-zinc-600 dark:text-zinc-400 list-disc list-inside space-y-1">
                        <li>Mengganti dan memperbesar ukuran logo aplikasi di semua halaman dengan dukungan <i>Dark Mode</i>.</li>
                        <li>Memperbarui favicon dengan ikon kustom.</li>
                        <li>Mengganti label input "Metro" menjadi "Layanan" dengan <i>placeholder</i> baru (Metro / IPT / T2IX).</li>
                        <li>Memperbaiki logika alur persetujuan tiket untuk kode awalan "UP-" (PO Uplink) yang sebelumnya mengalami <i>Invalid state transition</i>.</li>
                    </ul>
                </div>

                <!-- Version 1.0.2 -->
                <div class="relative pl-4 border-l-2 border-zinc-200 dark:border-zinc-800">
                    <div class="absolute -left-[5px] top-1.5 w-2 h-2 rounded-full bg-zinc-200 dark:bg-zinc-700 ring-4 ring-white dark:ring-zinc-900"></div>
                    <h4 class="font-bold text-zinc-900 dark:text-zinc-100 text-sm">Version 1.0.2 <span class="text-xs font-normal text-zinc-500 ml-2">07 Juli 2026</span></h4>
                    <ul class="mt-2 text-sm text-zinc-600 dark:text-zinc-400 list-disc list-inside space-y-1">
                        <li>Menambahkan tombol <strong>Templat Cepat PO Uplink</strong> (dengan kode tiket awalan UP-).</li>
                        <li>Menambahkan fitur <strong>Tambah Catatan</strong> pada halaman detail tiket yang akan tersimpan dalam riwayat transisi.</li>
                    </ul>
                </div>

                <!-- Version 1.0.1 -->
                <div class="relative pl-4 border-l-2 border-zinc-200 dark:border-zinc-800">
                    <div class="absolute -left-[5px] top-1.5 w-2 h-2 rounded-full bg-zinc-200 dark:bg-zinc-700 ring-4 ring-white dark:ring-zinc-900"></div>
                    <h4 class="font-bold text-zinc-900 dark:text-zinc-100 text-sm">Version 1.0.1 <span class="text-xs font-normal text-zinc-500 ml-2">06 Juli 2026</span></h4>
                    <ul class="mt-2 text-sm text-zinc-600 dark:text-zinc-400 list-disc list-inside space-y-1">
                        <li>Memperbaiki *syntax tag Blade* (<code>@@endif</code> tanpa pasangan) yang menyebabkan error 500 pada halaman detail tiket.</li>
                        <li>Memperbarui tag penutup <code>@@endif</code> menjadi <code>@@endforelse</code> pada *nested loops* di halaman detail tiket.</li>
                        <li>Memperbarui tag <code>@@can</code> untuk mengecek hak akses ditutup dengan <code>@@endcan</code> yang sebelumnya salah ditutup menggunakan <code>@@endif</code>.</li>
                        <li>Menambahkan komponen Modal Changelog untuk riwayat update aplikasi.</li>
                    </ul>
                </div>

                <!-- Version 1.0.0 -->
                <div class="relative pl-4 border-l-2 border-red-500/30">
                    <div class="absolute -left-[5px] top-1.5 w-2 h-2 rounded-full bg-red-500 ring-4 ring-white dark:ring-zinc-900"></div>
                    <h4 class="font-bold text-zinc-900 dark:text-zinc-100 text-sm">Version 1.0.0 <span class="text-xs font-normal text-zinc-500 ml-2">06 Juli 2026</span></h4>
                    <ul class="mt-2 text-sm text-zinc-600 dark:text-zinc-400 list-disc list-inside space-y-1">
                        <li>Perilisan awal (Initial Release) aplikasi Technical Ticket Network.</li>
                        <li>Sistem manajemen tiket interaktif dengan status dinamis.</li>
                        <li>Role & Hak Akses pengguna yang fleksibel (Admin, Staff, Destination Manager).</li>
                        <li>Autogenerate UUID untuk keamanan tautan tiket.</li>
                        <li>Dashboard responsif dengan dark/light mode terintegrasi.</li>
                        <li>Fitur pencarian tiket real-time.</li>
                    </ul>
                </div>
            </div>
            
            <div class="px-6 py-4 border-t border-zinc-200 dark:border-zinc-800 bg-zinc-50 dark:bg-zinc-950/50 flex justify-end">
                <button @click="open = false" class="px-4 py-2 bg-zinc-200 hover:bg-zinc-300 dark:bg-zinc-800 dark:hover:bg-zinc-700 text-zinc-800 dark:text-zinc-200 text-sm font-semibold rounded-xl transition-colors">
                    Tutup
                </button>
            </div>
         </div>
    </div>
</div>
