<div
    class="w-screen h-screen bg-gradient-to-br from-slate-900 via-purple-900 to-slate-900 flex flex-col p-6 overflow-hidden">
    <!-- Header - Premium Style (match Duha) -->
    <div class="flex items-center justify-between mb-2 sm:mb-3 md:mb-4 lg:mb-6">
        <div class="text-center flex-1">
            <div class="flex-row items-center justify-center gap-1 sm:gap-2 md:gap-3 mb-0.5 sm:mb-1 md:mb-2">
                {{-- <div
                    class="w-6 sm:w-8 md:w-10 lg:w-12 h-6 sm:h-8 md:h-10 lg:h-12 bg-gradient-to-br from-purple-400 to-blue-500 rounded-lg flex items-center justify-center">
                    <span class="text-sm sm:text-base md:text-lg lg:text-2xl">🌅</span>
                </div> --}}
                <h1
                    class="text-lg sm:text-2xl md:text-3xl lg:text-5xl font-black bg-gradient-to-r from-purple-300 to-blue-300 bg-clip-text text-transparent">
                    Absensi Guru</h1>
                <h1
                    class="text-lg sm:text-2xl md:text-3xl lg:text-5xl font-black bg-gradient-to-r from-purple-300 to-blue-300 bg-clip-text text-transparent">
                    SMK DARUL FIKRI PUGUNG</h1>
            </div>
            <p class="text-[10px] sm:text-xs md:text-sm lg:text-base text-slate-300 font-medium">Scan QR code guru untuk
                mencatat kehadiran guru mengajar</p>
        </div>
    </div>

    <!-- Main Content - Flexible Layout -->
    <div class="flex-1 flex flex-col lg:flex-row gap-6 overflow-hidden">

        <!-- Left Column: Scanner & Message -->
        <div class="flex flex-col gap-4 lg:w-1/3">
            <!-- Scanner Input -->
            <div
                class="bg-white/[0.07] backdrop-blur-2xl rounded-3xl shadow-2xl p-6 border border-white/10 hover:border-white/20 transition-all duration-300">
                <label class="block text-sm font-semibold text-white mb-3 text-center">🔍 Scan QR Code</label>
                <!-- Class Room Selection -->
                <div class="mb-4">
                    <label class="block text-sm font-semibold text-white mb-2" for="class-select">

                        <label class="block text-sm font-semibold text-white mb-2">🏫 Pilih Kelas<span
                                class="text-red-500">*</span>
                            <span class="sr-only">Required</span></label>
                        <div class="relative">
                            <select id="class-select" wire:model.lazy="selectedClassRoom"
                                class="w-full px-4 py-3 pr-10 text-lg border-2 rounded-xl appearance-none focus:ring-2 focus:ring-sky-500 focus:border-transparent bg-slate-700/40 text-white placeholder-gray-400 transition-colors border-slate-600 hover:bg-slate-700">
                                <option value="" disabled selected class="bg-slate-700 text-white">Pilih Kelas
                                </option>
                                @foreach (\App\Models\WeeklySchedules::whereNotNull('class_room')->distinct('class_room')->orderBy('class_room')->pluck('class_room') as $classRoom)
                                    <option value="{{ $classRoom }}" class="bg-slate-800 text-white">
                                        {{ $classRoom }}</option>
                                @endforeach
                            </select>
                            <div class="pointer-events-none absolute inset-y-0 right-3 flex items-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-white" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 9l-7 7-7-7" />
                                </svg>
                            </div>
                        </div>
                        <p class="text-xs text-gray-300 mt-1">Pilih kelas untuk filter scan</p>
                </div>
                <input type="text" id="scanner-input" wire:model.live.debounce.300ms="qrCode"
                    @if (empty($selectedClassRoom)) disabled @endif autofocus
                    placeholder="{{ empty($selectedClassRoom) ? 'Pilih kelas terlebih dahulu' : ($processing ? 'Processing...' : 'Scanner akan input...') }}"
                    class="w-full px-4 py-3 text-lg border-2 rounded-xl focus:ring-4 focus:ring-blue-400 focus:border-transparent bg-white/5 text-white placeholder-gray-400 transition-all {{ empty($selectedClassRoom) ? 'opacity-60 cursor-not-allowed' : '' }} {{ $processing ? 'border-green-400 opacity-70' : 'border-white/30' }}" />
                @if ($processing)
                    <p class="text-xs text-green-300 mt-2 text-center">⏳ Memproses scan...</p>
                @endif
            </div>

            <!-- Message Display -->
            @if ($message)
                <div
                    class="p-4 rounded-2xl border-l-4 text-center text-sm font-bold
                    {{ match ($messageType) {
                        'success' => 'bg-green-500/20 border-green-400 text-green-200 backdrop-blur-md',
                        'warning' => 'bg-yellow-500/20 border-yellow-400 text-yellow-200 backdrop-blur-md',
                        'info' => 'bg-blue-500/20 border-blue-400 text-blue-200 backdrop-blur-md',
                        default => 'bg-red-500/20 border-red-400 text-red-200 backdrop-blur-md',
                    } }}
                ">
                    {{ $message }}
                </div>
            @endif
        </div>

        <!-- Right Column: Teacher Card - Always Visible -->
        <div class="lg:w-2/3 flex items-start justify-start overflow-hidden">
            <div class="w-full group self-start">
                @if ($teacherData)
                    <div class="relative rounded-lg sm:rounded-2xl md:rounded-3xl overflow-hidden shadow-2xl border transition-all duration-500
                     {{ $teacherData
                         ? match ($teacherData['status']) {
                             'success'
                                 => 'bg-gradient-to-br from-green-400/10 to-emerald-600/10 border-green-400/30 group-hover:border-green-400/50 group-hover:shadow-green-500/20',
                             'no_schedule', 'wrong_time' => 'bg-gradient-to-br from-yellow-400/10 to-amber-600/10 border-yellow-400/30',
                             'already_scanned' => 'bg-gradient-to-br from-blue-400/10 to-cyan-600/10 border-blue-400/30',
                             default => 'bg-gradient-to-br from-red-400/10 to-rose-600/10 border-red-400/30',
                         }
                         : 'bg-gradient-to-br from-slate-700/10 to-slate-800/10 border border-slate-600' }}"
                        style="box-shadow: 0 20px 60px rgba(0,0,0,0.3)">
                        <div
                            class="absolute inset-0 bg-gradient-to-br from-white/0 via-white/[0.01] to-white/[0.03] pointer-events-none">
                        </div>
                        <div class="relative p-2 sm:p-3 md:p-6 lg:p-12">
                            <div class="grid grid-cols-1 sm:grid-cols-4 gap-2 sm:gap-3 md:gap-6 lg:gap-8 items-center">
                                <div class="sm:col-span-1 flex justify-center">
                                    <div class="relative">
                                        @if ($teacherData['photo'])
                                            <img src="{{ Storage::url($teacherData['photo']) }}"
                                                alt="{{ $teacherData['name'] }}"
                                                class="w-24 sm:w-32 md:w-40 lg:w-48 h-24 sm:h-32 md:h-40 lg:h-48 rounded-lg border-2 sm:border-3 md:border-4 border-white/20 shadow-2xl object-cover ring-2 sm:ring-3 md:ring-4 ring-purple-500/20" />
                                        @else
                                            <div
                                                class="w-24 sm:w-32 md:w-40 lg:w-48 h-24 sm:h-32 md:h-40 lg:h-48 rounded-lg border-2 sm:border-3 md:border-4 border-white/20 bg-gradient-to-br from-slate-600 to-slate-700 flex items-center justify-center shadow-2xl ring-2 sm:ring-3 md:ring-4 ring-purple-500/20">
                                                <svg class="w-12 sm:w-16 md:w-20 lg:w-24 h-12 sm:h-16 md:h-20 lg:h-24 text-slate-400"
                                                    fill="currentColor" viewBox="0 0 24 24">
                                                    <path
                                                        d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z" />
                                                </svg>
                                            </div>
                                        @endif
                                        <div
                                            class="absolute -bottom-2 -right-2 {{ $teacherData['status'] === 'success' ? 'bg-gradient-to-r from-green-400 to-emerald-500' : 'bg-gradient-to-r from-blue-400 to-cyan-500' }} rounded-full p-1 sm:p-2 md:p-3 shadow-xl text-base sm:text-lg md:text-2xl">
                                            {{ $teacherData['status'] === 'success' ? '✓' : 'ℹ' }}
                                        </div>
                                    </div>
                                </div>
                                <div class="sm:col-span-3 space-y-1.5 sm:space-y-2 md:space-y-4 lg:space-y-6">
                                    <div>
                                        <p
                                            class="text-[8px] sm:text-xs md:text-sm font-bold text-slate-300 uppercase tracking-widest mb-0.5 sm:mb-1">
                                            Nama Guru</p>
                                        <h2
                                            class="text-base sm:text-lg md:text-2xl lg:text-5xl font-black bg-gradient-to-r from-white to-slate-200 bg-clip-text text-transparent line-clamp-2">
                                            {{ $teacherData['name'] }}</h2>
                                    </div>

                                    <div class="grid grid-cols-2 gap-1.5 sm:gap-2 md:gap-4 lg:gap-6">
                                        <div>
                                            <p
                                                class="text-[8px] sm:text-xs md:text-sm font-bold text-slate-300 uppercase tracking-widest mb-0.5 sm:mb-1">
                                                NIP</p>
                                            <p
                                                class="text-[9px] sm:text-xs md:text-base lg:text-2xl font-bold text-slate-200 truncate">
                                                {{ $teacherData['nip'] ?? 'N/A' }}</p>
                                        </div>
                                        @if ($teacherData['status'] === 'success' && isset($teacherData['scanned_at']))
                                            <div>
                                                <p
                                                    class="text-[8px] sm:text-xs md:text-sm font-bold text-slate-300 uppercase tracking-widest mb-0.5 sm:mb-1">
                                                    Waktu</p>
                                                <p
                                                    class="text-[9px] sm:text-xs md:text-base lg:text-2xl font-bold text-purple-300 font-mono">
                                                    {{ $teacherData['scanned_at'] }}</p>
                                            </div>
                                        @endif
                                    </div>

                                    <div class="pt-1 sm:pt-2 md:pt-4 border-t border-white/10">
                                        <div class="grid grid-cols-2 gap-1 sm:gap-2 md:gap-3 lg:gap-4">
                                            <div>
                                                <p
                                                    class="text-[8px] sm:text-xs font-bold text-slate-400 uppercase tracking-widest mb-0.5 sm:mb-1">
                                                    Status</p>
                                                <p
                                                    class="text-[9px] sm:text-xs md:text-sm font-bold text-slate-100 line-clamp-1">
                                                    {{ $teacherData['message'] }}</p>
                                            </div>
                                            @if (isset($teacherData['late_status']))
                                                <div
                                                    class="bg-white/5 rounded-lg sm:rounded-lg md:rounded-xl p-1 sm:p-2 md:p-3 backdrop-blur-sm border border-white/10">
                                                    <p
                                                        class="text-[8px] sm:text-xs font-bold text-slate-400 uppercase tracking-widest mb-0.5 sm:mb-1">
                                                        Waktu</p>
                                                    <p
                                                        class="text-[9px] sm:text-xs md:text-sm lg:text-2xl font-black {{ str_contains($teacherData['late_status'], 'TERLAMBAT') ? 'text-red-300' : (str_contains($teacherData['late_status'], 'AWAL') ? 'text-orange-300' : 'text-green-300') }}">
                                                        {{ str_contains($teacherData['late_status'], 'TERLAMBAT') ? '❌ ' . trim(str_replace('⏰ TERLAMBAT', '', $teacherData['late_status'])) : (str_contains($teacherData['late_status'], 'AWAL') ? '⚡ ' . trim(str_replace('⚡ PULANG AWAL', '', $teacherData['late_status'])) : '✅ TEPAT') }}
                                                    </p>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @else
                    <!-- Empty Card State -->
                    <div
                        class="w-full rounded-lg sm:rounded-2xl md:rounded-3xl overflow-hidden shadow-2xl border border-white/10 bg-gradient-to-br from-white/5 to-white/0 backdrop-blur-2xl self-start">
                        <div class="flex flex-col items-center justify-center py-16">
                            <svg class="w-24 h-24 text-slate-500 mb-6 opacity-50" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <p class="text-xl text-slate-400 font-semibold text-center">Menunggu scan QR code...</p>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Stats Footer - Compact -->
    <div class="grid grid-cols-2 gap-4 mt-4">
        <div
            class="bg-gradient-to-br from-purple-500/10 to-blue-500/10 backdrop-blur-md rounded-2xl p-4 text-center border border-purple-400/20 hover:border-purple-400/30 transition-all duration-300">
            <p class="text-xs text-blue-100 mb-1">Total Scan</p>
            <p class="text-3xl font-bold text-blue-300">{{ $scanCount }}</p>
        </div>
        <div class="bg-gray-500/20 backdrop-blur-md rounded-2xl p-4 text-center border border-gray-400/30"
            x-data="clock()">
            <p class="text-xs text-gray-100 mb-1">Waktu</p>
            <p class="text-3xl font-bold text-gray-300" x-text="time"></p>
        </div>
    </div>

    <script>
        function clock() {
            return {
                time: new Date().toLocaleTimeString('id-ID', {
                    hour: '2-digit',
                    minute: '2-digit',
                    second: '2-digit'
                }),
                init() {
                    this.updateTime();
                    setInterval(() => this.updateTime(), 1000);
                },
                updateTime() {
                    this.time = new Date().toLocaleTimeString('id-ID', {
                        hour: '2-digit',
                        minute: '2-digit',
                        second: '2-digit'
                    });
                }
            }
        }

        function initScannerBindings() {
            const input = document.getElementById('scanner-input');
            const classSelect = document.getElementById('class-select');
            let scanTimeout;

            if (!input) return;

            // Fokuskan input scanner jika tidak dalam keadaan disabled
            if (!input.disabled) input.focus();

            // Auto focus kembali setelah blur, kecuali user sedang interaksi dengan elemen lain seperti SELECT/INPUT/TEXTAREA/BUTTON
            input.onblur = function() {
                setTimeout(() => {
                    const active = document.activeElement;
                    if (active && ['INPUT', 'SELECT', 'TEXTAREA', 'BUTTON'].includes(active.tagName)) {
                        return;
                    }
                    if (classSelect && (active === classSelect || classSelect.contains(active))) {
                        return;
                    }
                    if (!input.disabled) input.focus();
                }, 100);
            };

            // Jika pengguna mengubah pilihan kelas, kembalikan fokus ke input scanner setelah sedikit delay agar Livewire bisa menerima perubahan
            if (classSelect) {
                classSelect.onchange = function() {
                    setTimeout(() => {
                        input.focus();
                    }, 200);
                };
            }

            // Handle input - auto reset setelah scan
            input.oninput = function() {
                // Clear previous timeout
                if (scanTimeout) {
                    clearTimeout(scanTimeout);
                }

                // Set new timeout untuk auto clear input setelah 500ms (setelah QR selesai ter-scan)
                scanTimeout = setTimeout(() => {
                    if (input.value.trim() !== '') {
                        // Input akan di-clear otomatis oleh Livewire
                        // Tapi pastikan focus tetap di input
                        setTimeout(() => {
                            input.value = '';
                            if (!input.disabled) input.focus();
                        }, 100);
                    }
                }, 500);
            };
        }

        document.addEventListener('DOMContentLoaded', initScannerBindings);
        document.addEventListener('livewire:load', initScannerBindings);
        if (window.Livewire && typeof window.Livewire.hook === 'function') {
            Livewire.hook('message.processed', (message, component) => {
                initScannerBindings();
            });
        } else {
            document.addEventListener('livewire:update', initScannerBindings);
        }
    </script>
</div>
