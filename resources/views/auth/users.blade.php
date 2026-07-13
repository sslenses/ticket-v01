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
    <title>Technical Ticket Network - Manajemen Pengguna</title>
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
    @include('layouts.spa-script')
</head>
<body class="min-h-screen flex flex-col bg-zinc-50 dark:bg-zinc-950 text-zinc-900 dark:text-zinc-50 transition-colors duration-300">
    <div id="app-root" class="min-h-screen flex flex-col" x-data="{
          currentUserRole: '{{ auth()->user()->role }}',
          currentUserId: {{ auth()->user()->id }},
          showEditModal: false,
          isCreateMode: false,
          editUser: { id: null, name: '', email: '', role: 'user', password: '' },

          openCreateModal() {
              this.isCreateMode = true;
              this.editUser = { id: null, name: '', email: '', role: 'user', password: '' };
              this.showEditModal = true;
          },

          openEditModal(user) {
              this.isCreateMode = false;
              this.editUser = { id: user.id, name: user.name, email: user.email, role: user.role, password: '' };
              this.showEditModal = true;
          },

          async submitUserForm() {
              if (this.isCreateMode) {
                  await this.createUser();
              } else {
                  await this.updateUser();
              }
          },

          async createUser() {
              try {
                  const response = await fetch('/api/users', {
                      method: 'POST',
                      headers: {
                          'Content-Type': 'application/json',
                          'Accept': 'application/json',
                          'X-CSRF-TOKEN': '{{ csrf_token() }}'
                      },
                      body: JSON.stringify({
                          name: this.editUser.name,
                          email: this.editUser.email,
                          role: this.editUser.role,
                          password: this.editUser.password
                      })
                  });

                  if (!response.ok) {
                      const err = await response.json();
                      alert('Gagal membuat pengguna: ' + (err.message || 'Error validasi'));
                      return;
                  }

                  window.reloadPage();
              } catch (e) {
                  alert('Gagal menghubungi server.');
              }
          },

          async updateUser() {
              try {
                  const response = await fetch(`/api/users/${this.editUser.id}`, {
                      method: 'PATCH',
                      headers: {
                          'Content-Type': 'application/json',
                          'Accept': 'application/json',
                          'X-CSRF-TOKEN': '{{ csrf_token() }}'
                      },
                      body: JSON.stringify({
                          name: this.editUser.name,
                          email: this.editUser.email,
                          role: this.editUser.role,
                          password: this.editUser.password
                      })
                  });

                  if (!response.ok) {
                      const err = await response.json();
                      alert('Gagal memperbarui pengguna: ' + (err.message || 'Error validasi'));
                      return;
                  }

                  window.reloadPage();
              } catch (e) {
                  alert('Gagal menghubungi server.');
              }
          },

          async deleteUser(userId) {
              if (userId === this.currentUserId) {
                  alert('Anda tidak dapat menghapus akun Anda sendiri.');
                  return;
              }
              if (!confirm('Apakah Anda yakin ingin menghapus pengguna ini? Tindakan ini permanen.')) {
                  return;
              }

              try {
                  const response = await fetch(`/api/users/${userId}`, {
                      method: 'DELETE',
                      headers: {
                          'Content-Type': 'application/json',
                          'Accept': 'application/json',
                          'X-CSRF-TOKEN': '{{ csrf_token() }}'
                      }
                  });

                  if (!response.ok) {
                      const err = await response.json();
                      alert('Gagal menghapus pengguna: ' + (err.message || 'Error'));
                      return;
                  }

                  window.reloadPage();
              } catch (e) {
                  alert('Gagal menghubungi server.');
              }
          }
      }">

    <!-- Top Navigation -->
        @include('layouts.header', ['activeMenu' => 'users'])

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
            <h1 class="text-3xl font-extrabold text-zinc-900 dark:text-white tracking-tight">Manajemen Pengguna</h1>
            <p class="text-zinc-550 dark:text-zinc-500 text-sm mt-1">Kelola dan audit semua akun yang terdaftar serta peran sistem.</p>
        </div>

        <!-- User List Table Section -->
        <section class="rounded-2xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900/60 backdrop-blur-xl p-5 md:p-8 shadow-sm dark:shadow-2xl transition-colors">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
                <h2 class="text-xl font-bold flex items-center gap-2 text-zinc-900 dark:text-white">
                    <span class="w-2 h-5 rounded bg-red-600 inline-block"></span>
                    Pengguna Terdaftar & Peran Sistem
                </h2>
                @if(auth()->user()->hasRole('admin'))
                    <button @click="openCreateModal()"
                            class="bg-red-600 hover:bg-red-500 text-xs font-semibold px-4 py-2.5 rounded-lg text-white transition-all cursor-pointer flex items-center gap-1.5 shadow-md active:scale-95">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                        </svg>
                        Tambah Pengguna Baru
                    </button>
                @endif
            </div>

            <!-- Desktop View: Table Layout -->
            <div class="hidden md:block overflow-x-auto">
                <table class="w-full border-collapse text-left">
                    <thead>
                        <tr class="border-b border-zinc-200 dark:border-zinc-700 text-xs font-bold text-zinc-600 dark:text-zinc-400 uppercase tracking-wider">
                            <th class="py-3 px-4">Nama</th>
                            <th class="py-3 px-4">Email</th>
                            <th class="py-3 px-4">Peran</th>
                            <th class="py-3 px-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-200 dark:divide-zinc-800 text-sm text-zinc-800 dark:text-zinc-200">
                        @foreach($users as $user)
                        <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/50 transition-colors duration-200">
                            <td class="py-4 px-4 font-bold text-zinc-900 dark:text-white select-all">
                                {{ $user->name }}
                            </td>
                            <td class="py-4 px-4 text-zinc-700 dark:text-zinc-300 font-mono">
                                {{ $user->email }}
                            </td>
                            <td class="py-4 px-4">
                                <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold border capitalize"
                                      :class="{
                                          'bg-red-50 dark:bg-red-500/10 text-red-700 dark:text-red-400 border-red-200 dark:border-red-500/20': '{{ $user->role }}' === 'admin',
                                          'bg-cyan-50 dark:bg-cyan-500/10 text-cyan-700 dark:text-cyan-400 border-cyan-200 dark:border-cyan-500/20': '{{ $user->role }}' === 'dest_manager',
                                          'bg-emerald-50 dark:bg-emerald-500/10 text-emerald-700 dark:text-emerald-400 border-emerald-200 dark:border-emerald-500/20': '{{ $user->role }}' === 'teknisi',
                                          'bg-zinc-50 dark:bg-zinc-500/10 text-zinc-700 dark:text-zinc-400 border-zinc-200 dark:border-zinc-500/20': '{{ $user->role }}' === 'user'
                                      }">
                                    {{ str_replace('_', ' ', $user->role) }}
                                </span>
                            </td>
                            <td class="py-4 px-4 text-right flex justify-end gap-2 items-center">
                                @if (auth()->user()->hasRole('admin') || (auth()->user()->hasRole('dest_manager') && $user->role !== 'admin'))
                                    <button @click="openEditModal({ id: {{ $user->id }}, name: '{{ addslashes($user->name) }}', email: '{{ addslashes($user->email) }}', role: '{{ $user->role }}' })"
                                            class="text-red-600 hover:text-red-950 dark:text-red-400 dark:hover:text-red-200 font-semibold text-xs px-2.5 py-1.5 rounded-lg border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900/50 hover:bg-zinc-50 dark:hover:bg-zinc-800 transition-all cursor-pointer inline-flex items-center gap-1 shadow-sm">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-3.5 h-3.5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                                        </svg>
                                        Ubah
                                    </button>
                                @endif
                                @if (auth()->user()->hasRole('admin') && auth()->id() !== $user->id)
                                    <button @click="deleteUser({{ $user->id }})"
                                            class="text-red-600 hover:text-red-950 dark:text-red-400 dark:hover:text-red-200 font-semibold text-xs px-2.5 py-1.5 rounded-lg border border-red-200 dark:border-red-500/20 bg-red-500/5 hover:bg-red-50 dark:hover:bg-red-950/30 transition-all cursor-pointer inline-flex items-center gap-1 shadow-sm">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-3.5 h-3.5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                        </svg>
                                        Hapus
                                    </button>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Mobile View: Card List Layout -->
            <div class="block md:hidden space-y-4">
                @foreach($users as $user)
                    <div class="rounded-xl border border-zinc-200 dark:border-zinc-800 bg-zinc-50/50 dark:bg-zinc-900/50 p-4 shadow-sm flex flex-col gap-3">
                        <div class="flex items-center justify-between">
                            <span class="font-bold text-sm text-zinc-900 dark:text-white">{{ $user->name }}</span>
                            <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold border capitalize"
                                  :class="{
                                      'bg-red-50 dark:bg-red-500/10 text-red-700 dark:text-red-400 border-red-200 dark:border-red-500/20': '{{ $user->role }}' === 'admin',
                                      'bg-cyan-50 dark:bg-cyan-500/10 text-cyan-700 dark:text-cyan-400 border-cyan-200 dark:border-cyan-500/20': '{{ $user->role }}' === 'dest_manager',
                                      'bg-emerald-50 dark:bg-emerald-500/10 text-emerald-700 dark:text-emerald-400 border-emerald-200 dark:border-emerald-500/20': '{{ $user->role }}' === 'teknisi',
                                      'bg-zinc-50 dark:bg-zinc-500/10 text-zinc-700 dark:text-zinc-400 border-zinc-200 dark:border-zinc-500/20': '{{ $user->role }}' === 'user'
                                  }">
                                {{ str_replace('_', ' ', $user->role) }}
                            </span>
                        </div>
                        <div class="text-xs text-zinc-500 dark:text-zinc-400 flex flex-col gap-1.5 pt-2 border-t border-zinc-200 dark:border-zinc-800">
                            <div>
                                <span class="text-zinc-400 dark:text-zinc-500 uppercase font-semibold text-[9px] block">Alamat Email</span>
                                <span class="font-mono text-zinc-800 dark:text-zinc-300 text-xs">{{ $user->email }}</span>
                            </div>
                        </div>
                        <div class="border-t border-zinc-200 dark:border-zinc-800 pt-3 flex justify-end gap-2">
                            @if (auth()->user()->hasRole('admin') || (auth()->user()->hasRole('dest_manager') && $user->role !== 'admin'))
                                <button @click="openEditModal({ id: {{ $user->id }}, name: '{{ addslashes($user->name) }}', email: '{{ addslashes($user->email) }}', role: '{{ $user->role }}' })"
                                        class="text-red-600 hover:text-red-950 dark:text-red-400 dark:hover:text-red-200 font-semibold text-xs px-2.5 py-1.5 rounded-lg border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900/50 hover:bg-zinc-50 dark:hover:bg-zinc-800 transition-all cursor-pointer inline-flex items-center gap-1 shadow-sm">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-3.5 h-3.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                                    </svg>
                                    Ubah
                                </button>
                            @endif
                            @if (auth()->user()->hasRole('admin') && auth()->id() !== $user->id)
                                <button @click="deleteUser({{ $user->id }})"
                                        class="text-red-600 hover:text-red-950 dark:text-red-400 dark:hover:text-red-200 font-semibold text-xs px-2.5 py-1.5 rounded-lg border border-red-200 dark:border-red-500/20 bg-red-500/5 hover:bg-red-50 dark:hover:bg-red-950/30 transition-all cursor-pointer inline-flex items-center gap-1 shadow-sm">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-3.5 h-3.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                    </svg>
                                    Hapus
                                </button>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </section>
        
    </main>

    <!-- Edit User Modal -->
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
                <h3 class="text-lg font-bold text-zinc-900 dark:text-white" x-text="isCreateMode ? 'Tambah Pengguna Baru' : 'Edit Profil Pengguna'"></h3>
                <button @click="showEditModal = false" class="text-zinc-400 hover:text-zinc-700 dark:text-zinc-500 dark:hover:text-zinc-300 cursor-pointer">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <form @submit.prevent="submitUserForm" class="space-y-4">
                <div>
                    <label class="text-xs text-zinc-600 dark:text-zinc-400 font-semibold block mb-1">Nama Lengkap</label>
                    <input type="text" x-model="editUser.name" required placeholder="misal John Doe"
                           class="w-full bg-zinc-50 dark:bg-zinc-950 border border-zinc-200 dark:border-zinc-800 rounded-lg px-3 py-2 text-sm text-zinc-900 dark:text-white focus:outline-none focus:border-red-600 transition-colors">
                </div>

                <div>
                    <label class="text-xs text-zinc-600 dark:text-zinc-400 font-semibold block mb-1">Alamat Email</label>
                    <input type="email" x-model="editUser.email" required placeholder="misal john@contoh.com"
                           class="w-full bg-zinc-50 dark:bg-zinc-950 border border-zinc-200 dark:border-zinc-800 rounded-lg px-3 py-2 text-sm text-zinc-900 dark:text-white focus:outline-none focus:border-red-600 transition-colors">
                </div>

                <!-- Role dropdown (Visible only to Admin role) -->
                <template x-if="currentUserRole === 'admin'">
                    <div>
                        <label class="text-xs text-zinc-600 dark:text-zinc-400 font-semibold block mb-1">Peran Pengujian</label>
                        <select x-model="editUser.role" required
                                class="w-full bg-zinc-50 dark:bg-zinc-950 border border-zinc-200 dark:border-zinc-800 rounded-lg px-3 py-2 text-sm text-zinc-900 dark:text-white focus:outline-none focus:border-red-600 transition-colors">
                            <option class="text-zinc-900 dark:text-white bg-white dark:bg-zinc-950" value="user">User (Biasa)</option>
                            <option class="text-zinc-900 dark:text-white bg-white dark:bg-zinc-950" value="teknisi">Teknisi</option>
                            <option class="text-zinc-900 dark:text-white bg-white dark:bg-zinc-950" value="dest_manager">Manajer Destinasi</option>
                            <option class="text-zinc-900 dark:text-white bg-white dark:bg-zinc-950" value="admin">Akun Admin</option>
                        </select>
                    </div>
                </template>

                <div x-data="{ showPassword: false }">
                    <label class="text-xs text-zinc-600 dark:text-zinc-400 font-semibold block mb-1" x-text="isCreateMode ? 'Kata Sandi' : 'Kata Sandi Baru (Kosongkan jika tidak ingin diubah)'"></label>
                    <div class="relative">
                        <input :type="showPassword ? 'text' : 'password'" x-model="editUser.password" :required="isCreateMode" placeholder="••••••••"
                               class="w-full bg-zinc-50 dark:bg-zinc-950 border border-zinc-200 dark:border-zinc-800 rounded-lg pl-3 pr-10 py-2 text-sm text-zinc-900 dark:text-white focus:outline-none focus:border-red-600 transition-colors">
                        <button type="button" @click="showPassword = !showPassword" class="absolute inset-y-0 right-0 flex items-center pr-3 text-zinc-400 dark:text-zinc-500 hover:text-zinc-600 dark:hover:text-zinc-400 focus:outline-none cursor-pointer">
                            <!-- Eye icon (shown when password is hidden) -->
                            <svg x-show="!showPassword" class="h-4.5 w-4.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                            </svg>
                            <!-- Eye slash icon (shown when password is visible) -->
                            <svg x-show="showPassword" class="h-4.5 w-4.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="display: none;">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 0 0 1.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.451 10.451 0 0 1 12 4.5c4.756 0 8.773 3.162 10.065 7.498a10.522 10.522 0 0 1-4.293 5.774M6.228 6.228 3 3m3.228 3.228 3.65 3.65m7.894 7.894L21 21m-3.228-3.228-3.65-3.65m0 0a3 3 0 1 0-4.243-4.243m4.242 4.242L9.88 9.88" />
                            </svg>
                        </button>
                    </div>
                </div>

                <div class="flex justify-end gap-3 pt-4 border-t border-zinc-200 dark:border-zinc-800">
                    <button type="button" @click="showEditModal = false"
                            class="bg-zinc-100 hover:bg-zinc-200 dark:bg-zinc-800 dark:hover:bg-zinc-700 text-xs font-semibold px-4 py-2.5 rounded-lg text-zinc-700 dark:text-zinc-300 transition-colors cursor-pointer">
                        Batal
                    </button>
                    <button type="submit"
                            class="bg-red-600 hover:bg-red-500 text-xs font-semibold px-4 py-2.5 rounded-lg text-white transition-colors cursor-pointer"
                            x-text="isCreateMode ? 'Buat Pengguna' : 'Simpan Perubahan'">
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
    <x-changelog />
</body>
</html>
