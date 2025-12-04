<div class="w-screen h-screen bg-gradient-to-br from-slate-900 via-purple-900 to-slate-900 flex flex-col p-2 sm:p-3 md:p-4 lg:p-6 overflow-hidden">


    <!-- Header with Fullscreen Button -->
    <div class="flex items-center justify-between mb-1.5 sm:mb-2 md:mb-3 lg:mb-4 w-full">
        <!-- Header - Selamat Datang Section -->
        <div class="flex-1">
            <x-header-welcome />
        </div>

        <!-- Fullscreen Button - Right Corner -->
        <button
            id="fullscreenBtn"
            onclick="toggleFullscreen()"
            class="shrink-0 group px-2 sm:px-2.5 md:px-3 py-1 sm:py-1.5 md:py-2 rounded-lg bg-white/10 hover:bg-white/20 backdrop-blur-sm border border-white/20 hover:border-white/30 transition-all duration-300 text-slate-200 hover:text-white ml-2 sm:ml-3 md:ml-4"
            title="Toggle Fullscreen"
        >
            <svg class="w-4 sm:w-5 md:w-5 h-4 sm:h-5 md:h-5" fill="currentColor" viewBox="0 0 24 24">
                <path d="M7 14H5v5h5v-2H7v-3zm-2-4h2V7h3V5H5v5zm12 7h-3v2h5v-5h-2v3zM14 5v2h3v3h2V5h-5z"/>
            </svg>
        </button>
    </div>

    <!-- Mode Selector - Premium Toggle -->
    <div class="flex gap-1 sm:gap-1.5 md:gap-2 justify-center mb-2 sm:mb-2.5 md:mb-3 lg:mb-4 flex-wrap">
        <button
            wire:click="setScanMode('duha')"
            class="group px-2 sm:px-3 md:px-4 lg:px-5 py-1 sm:py-1.5 md:py-2 rounded-full font-bold transition-all duration-300 flex items-center gap-0.5 sm:gap-1 md:gap-1.5 text-[9px] sm:text-[11px] md:text-xs lg:text-sm
            {{ $scanMode === 'duha'
                ? 'bg-gradient-to-r from-purple-500 to-blue-500 text-white shadow-lg shadow-purple-500/50 scale-105'
                : 'bg-white/10 text-slate-200 hover:bg-white/20 backdrop-blur-sm border border-white/20' }}"
        >
            <span class="text-xs sm:text-sm md:text-base lg:text-lg">🌅</span>
            <span class="hidden sm:inline">Duha (Masuk)</span>
            <span class="sm:hidden">Duha</span>
        </button>
        <button
            wire:click="setScanMode('departure')"
            class="group px-2 sm:px-3 md:px-4 lg:px-5 py-1 sm:py-1.5 md:py-2 rounded-full font-bold transition-all duration-300 flex items-center gap-0.5 sm:gap-1 md:gap-1.5 text-[9px] sm:text-[11px] md:text-xs lg:text-sm
            {{ $scanMode === 'departure'
                ? 'bg-gradient-to-r from-orange-500 to-red-500 text-white shadow-lg shadow-orange-500/50 scale-105'
                : 'bg-white/10 text-slate-200 hover:bg-white/20 backdrop-blur-sm border border-white/20' }}"
        >
            <span class="text-xs sm:text-sm md:text-base lg:text-lg">🚪</span>
            <span class="hidden sm:inline">Pulang (Keluar)</span>
            <span class="sm:hidden">Pulang</span>
        </button>
    </div>

    <!-- Main Content - Premium Layout -->
    <div class="flex-1 flex flex-col lg:flex-row gap-2 sm:gap-2.5 md:gap-3 lg:gap-4 overflow-hidden min-h-0">
        <!-- Left Column: Scanner Input -->
        <div class="flex flex-col gap-2 sm:gap-2.5 md:gap-3 lg:w-1/4 order-2 lg:order-1 min-h-0">
            <!-- Scanner Input Card -->
            <div class="bg-white/[0.07] backdrop-blur-2xl rounded-lg sm:rounded-xl md:rounded-2xl shadow-2xl p-2 sm:p-3 md:p-4 lg:p-5 border border-white/10 hover:border-white/20 transition-all duration-300">
                <div class="flex items-center gap-1.5 sm:gap-2 mb-1 sm:mb-1.5 md:mb-2">
                    <div class="w-1.5 h-1.5 sm:w-2 sm:h-2 bg-purple-400 rounded-full animate-pulse"></div>
                    <label class="text-[9px] sm:text-[11px] md:text-xs font-bold text-slate-200 uppercase tracking-widest">🔍 Scan</label>
                </div>
                <input
                    type="text"
                    id="scanner-input"
                    wire:model.live.debounce.300ms="qrCode"
                    autofocus
                    placeholder="{{ $processing ? 'Processing...' : 'Scan QR...' }}"
                    class="w-full px-2 sm:px-3 md:px-4 py-1.5 sm:py-2 md:py-2.5 text-[10px] sm:text-xs md:text-sm font-medium border-2 rounded-lg sm:rounded-xl md:rounded-2xl focus:ring-4 focus:ring-purple-400/50 focus:border-transparent bg-white/5 text-white placeholder-slate-400 transition-all duration-300 {{ $processing ? 'border-green-400/50 bg-green-400/5' : 'border-white/20 hover:border-white/30' }}"
                />
                @if($processing)
                    <div class="flex items-center gap-1.5 mt-1 sm:mt-1.5 md:mt-2 text-green-300 text-[9px] sm:text-[11px] font-bold">
                        <div class="w-1.5 h-1.5 sm:w-2 sm:h-2 bg-green-400 rounded-full animate-pulse"></div>
                        Processing...
                    </div>
                @endif
            </div>

            <!-- Message Display - Dynamic -->
            @if ($message)
                <div class="rounded-lg sm:rounded-xl md:rounded-2xl overflow-hidden shadow-2xl border backdrop-blur-xl text-center py-1.5 sm:py-2 md:py-2.5 px-2 sm:px-3 md:px-4 font-bold text-[9px] sm:text-[11px] md:text-xs transition-all duration-300
                    {{ match($messageType) {
                        'success' => 'bg-gradient-to-r from-green-500/20 to-emerald-500/20 border-green-400/30 text-green-200',
                        'warning' => 'bg-gradient-to-r from-yellow-500/20 to-amber-500/20 border-yellow-400/30 text-yellow-200',
                        'info' => 'bg-gradient-to-r from-blue-500/20 to-cyan-500/20 border-blue-400/30 text-blue-200',
                        'danger' => 'bg-gradient-to-r from-red-600/40 to-rose-600/40 border-red-400/50 text-red-100 ring-2 ring-red-400/40 animate-pulse',
                        default => 'bg-gradient-to-r from-red-500/20 to-rose-500/20 border-red-400/30 text-red-200'
                    } }}
                ">
                    <div class="flex items-center justify-center gap-1 sm:gap-1.5">
                        <span class="text-xs sm:text-sm md:text-base">
                            @if($messageType === 'danger')
                                ⛔
                            @elseif($messageType === 'success')
                                ✅
                            @elseif($messageType === 'warning')
                                ⚠️
                            @else
                                ℹ️
                            @endif
                        </span>
                        <span>{{ $message }}</span>
                    </div>
                </div>
            @endif
        </div>

        <!-- Right Column: Teacher Card - Premium Design -->
        <div class="lg:w-3/4 flex items-start justify-start overflow-hidden order-1 lg:order-2 min-h-0">
            @if ($teacherData)
                <div class="w-full group self-start">
                    <div class="relative rounded-lg sm:rounded-xl md:rounded-2xl overflow-hidden shadow-2xl border transition-all duration-500
                        {{ match($teacherData['status']) {
                            'success' => 'bg-gradient-to-br from-green-400/10 to-emerald-600/10 border-green-400/30 group-hover:border-green-400/50 group-hover:shadow-green-500/20',
                            'already_scanned' => 'bg-gradient-to-br from-blue-400/10 to-cyan-600/10 border-blue-400/30',
                            'not_time' => 'bg-gradient-to-br from-yellow-400/10 to-amber-600/10 border-yellow-400/30',
                            default => 'bg-gradient-to-br from-red-400/10 to-rose-600/10 border-red-400/30'
                        } }}
                    " style="box-shadow: 0 16px 40px rgba(0,0,0,0.3)">
                        <div class="absolute inset-0 bg-gradient-to-br from-white/0 via-white/[0.01] to-white/[0.03] pointer-events-none"></div>

                        <div class="relative p-2 sm:p-3 md:p-4 lg:p-6">
                            <div class="grid grid-cols-1 sm:grid-cols-4 gap-2 sm:gap-3 md:gap-4 lg:gap-5 items-center">
                                <!-- Photo Section -->
                                <div class="sm:col-span-1 flex justify-center">
                                    <div class="relative">
                                        @if ($teacherData['photo'])
                                            <img
                                                src="{{ Storage::url($teacherData['photo']) }}"
                                                alt="{{ $teacherData['name'] }}"
                                                class="w-20 sm:w-24 md:w-28 lg:w-32 h-20 sm:h-24 md:h-28 lg:h-32 rounded-lg sm:rounded-xl md:rounded-2xl border-2 sm:border-3 md:border-4 border-white/20 shadow-2xl object-cover ring-2 sm:ring-3 md:ring-4 ring-purple-500/20"
                                            />
                                        @else
                                            <div class="w-20 sm:w-24 md:w-28 lg:w-32 h-20 sm:h-24 md:h-28 lg:h-32 rounded-lg sm:rounded-xl md:rounded-2xl border-2 sm:border-3 md:border-4 border-white/20 bg-gradient-to-br from-slate-600 to-slate-700 flex items-center justify-center shadow-2xl ring-2 sm:ring-3 md:ring-4 ring-purple-500/20">
                                                <svg class="w-10 sm:w-12 md:w-14 lg:w-16 h-10 sm:h-12 md:h-14 lg:h-16 text-slate-400" fill="currentColor" viewBox="0 0 24 24">
                                                    <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
                                                </svg>
                                            </div>
                                        @endif
                                        <!-- Status Badge -->
                                        <div class="absolute -bottom-2 -right-2
                                            {{ $teacherData['status'] === 'success' ? 'bg-gradient-to-r from-green-400 to-emerald-500' : 'bg-gradient-to-r from-blue-400 to-cyan-500' }}
                                            rounded-full p-1 sm:p-1.5 md:p-2 shadow-xl text-base sm:text-lg md:text-xl">
                                            {{ $teacherData['status'] === 'success' ? '✓' : 'ℹ' }}
                                        </div>
                                    </div>
                                </div>

                                <!-- Info Section -->
                                <div class="sm:col-span-3 space-y-1.5 sm:space-y-2 md:space-y-3 lg:space-y-4">
                                    <!-- Name -->
                                    <div>
                                        <p class="text-[8px] sm:text-[10px] md:text-xs font-bold text-slate-300 uppercase tracking-widest mb-0.5 sm:mb-1">👤 Nama</p>
                                        <h2 class="text-base sm:text-lg md:text-xl lg:text-3xl font-black bg-gradient-to-r from-white to-slate-200 bg-clip-text text-transparent line-clamp-2">
                                            {{ $teacherData['name'] }}
                                        </h2>
                                    </div>

                                    <!-- Mode Info Badge -->
                                    <div class="flex items-center gap-1.5 pt-0.5">
                                        <span class="text-[8px] sm:text-[10px] px-2 py-1 rounded-full font-bold {{ $scanMode === 'duha' ? 'bg-purple-500/30 text-purple-200' : 'bg-orange-500/30 text-orange-200' }}">
                                            {{ $scanMode === 'duha' ? '🌅 DUHA (06:00-07:00)' : '🚪 PULANG (13:00+)' }}
                                        </span>
                                    </div>

                                    <!-- NIP & Scan Time -->
                                    <div class="grid grid-cols-2 gap-1.5 sm:gap-2 md:gap-3 lg:gap-4">
                                        <div>
                                            <p class="text-[8px] sm:text-[10px] md:text-xs font-bold text-slate-300 uppercase tracking-widest mb-0.5 sm:mb-1">📍 NIP</p>
                                            <p class="text-[9px] sm:text-xs md:text-sm lg:text-lg font-bold text-slate-200 truncate">{{ $teacherData['nip'] ?? 'N/A' }}</p>
                                        </div>

                                        @if($teacherData['status'] === 'success' && isset($teacherData['scanned_at']))
                                            <div>
                                                <p class="text-[8px] sm:text-[10px] md:text-xs font-bold text-slate-300 uppercase tracking-widest mb-0.5 sm:mb-1">⏰ Waktu</p>
                                                <p class="text-[9px] sm:text-xs md:text-sm lg:text-lg font-bold text-purple-300 font-mono">{{ $teacherData['scanned_at'] }}</p>
                                            </div>
                                        @endif
                                    </div>

                                    <!-- Status & Late Info -->
                                    <div class="pt-1 sm:pt-1.5 md:pt-2.5 border-t border-white/10">
                                        <div class="grid grid-cols-2 gap-1 sm:gap-1.5 md:gap-2 lg:gap-3">
                                            <div>
                                                <p class="text-[8px] sm:text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-0.5 sm:mb-1">Status</p>
                                                <p class="text-[9px] sm:text-xs md:text-sm font-bold text-slate-100 line-clamp-1">{{ $teacherData['message'] }}</p>
                                            </div>
                                            @if(isset($teacherData['late_status']))
                                                <div class="bg-white/5 rounded-lg sm:rounded-lg md:rounded-xl p-1 sm:p-1.5 md:p-2 backdrop-blur-sm border border-white/10">
                                                    <p class="text-[8px] sm:text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-0.5 sm:mb-1">Waktu</p>
                                                    <p class="text-[9px] sm:text-xs md:text-sm lg:text-lg font-black
                                                        {{ str_contains($teacherData['late_status'], 'TERLAMBAT') ? 'text-red-300' : (str_contains($teacherData['late_status'], 'AWAL') ? 'text-orange-300' : 'text-green-300') }}
                                                    ">
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
                </div>
            @else
                <!-- Empty State - Premium Design -->
                <div class="w-full rounded-lg sm:rounded-xl md:rounded-2xl overflow-hidden shadow-2xl border border-white/10 bg-gradient-to-br from-white/5 to-white/0 backdrop-blur-2xl self-start">
                    <div class="flex flex-col items-center justify-center py-4 sm:py-6 md:py-8 lg:py-10 px-3 sm:px-4 md:px-6">
                        <div class="relative mb-2 sm:mb-3 md:mb-4">
                            <div class="absolute inset-0 bg-gradient-to-r from-purple-500 to-blue-500 rounded-full blur-2xl opacity-20 animate-pulse"></div>
                            <div class="relative w-14 sm:w-16 md:w-20 lg:w-24 h-14 sm:h-16 md:h-20 lg:h-24 bg-gradient-to-br from-purple-500/20 to-blue-500/20 rounded-full flex items-center justify-center border-2 border-white/20">
                                <svg class="w-7 sm:w-8 md:w-10 lg:w-12 h-7 sm:h-8 md:h-10 lg:h-12 text-purple-300 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4v16m8-8H4"></path>
                                </svg>
                            </div>
                        </div>
                        <h3 class="text-sm sm:text-base md:text-lg lg:text-xl font-black bg-gradient-to-r from-purple-300 to-blue-300 bg-clip-text text-transparent mb-1 sm:mb-1.5 md:mb-2 text-center">
                            Siap untuk Scan
                        </h3>
                        <p class="text-[9px] sm:text-xs md:text-sm lg:text-base text-slate-300 text-center max-w-md font-medium">
                            Arahkan QR code guru ke scanner
                        </p>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- Stats Footer - Premium Style -->
    <div class="grid grid-cols-3 gap-1.5 sm:gap-2 md:gap-2.5 lg:gap-3 mt-2 sm:mt-2.5 md:mt-3 lg:mt-4">
        <div class="group bg-gradient-to-br from-purple-500/10 to-blue-500/10 backdrop-blur-2xl rounded-lg sm:rounded-lg md:rounded-xl p-2 sm:p-2.5 md:p-3 lg:p-4 border border-purple-400/20 hover:border-purple-400/40 transition-all duration-300 hover:shadow-lg hover:shadow-purple-500/10 cursor-pointer">
            <div class="flex items-center justify-between mb-0.5 sm:mb-0.5 md:mb-1">
                <p class="text-[8px] sm:text-[10px] md:text-xs font-bold text-slate-300 uppercase tracking-wider">🌅 Duha</p>
                <div class="w-1 h-1 sm:w-1.5 sm:h-1.5 bg-purple-400 rounded-full group-hover:animate-pulse"></div>
            </div>
            <p class="text-lg sm:text-xl md:text-2xl lg:text-3xl font-black bg-gradient-to-r from-purple-300 to-blue-300 bg-clip-text text-transparent">{{ $scanCount }}</p>
        </div>

        <div class="group bg-gradient-to-br from-orange-500/10 to-red-500/10 backdrop-blur-2xl rounded-lg sm:rounded-lg md:rounded-xl p-2 sm:p-2.5 md:p-3 lg:p-4 border border-orange-400/20 hover:border-orange-400/40 transition-all duration-300 hover:shadow-lg hover:shadow-orange-500/10 cursor-pointer">
            <div class="flex items-center justify-between mb-0.5 sm:mb-0.5 md:mb-1">
                <p class="text-[8px] sm:text-[10px] md:text-xs font-bold text-slate-300 uppercase tracking-wider">🚪 Pulang</p>
                <div class="w-1 h-1 sm:w-1.5 sm:h-1.5 bg-orange-400 rounded-full group-hover:animate-pulse"></div>
            </div>
            <p class="text-lg sm:text-xl md:text-2xl lg:text-3xl font-black bg-gradient-to-r from-orange-300 to-red-300 bg-clip-text text-transparent">{{ $departureCount }}</p>
        </div>

        <div class="group bg-gradient-to-br from-slate-500/10 to-cyan-500/10 backdrop-blur-2xl rounded-lg sm:rounded-lg md:rounded-xl p-2 sm:p-2.5 md:p-3 lg:p-4 border border-slate-400/20 hover:border-slate-400/40 transition-all duration-300 hover:shadow-lg hover:shadow-cyan-500/10 cursor-pointer" x-data="clock()">
            <div class="flex items-center justify-between mb-0.5 sm:mb-0.5 md:mb-1">
                <p class="text-[8px] sm:text-[10px] md:text-xs font-bold text-slate-300 uppercase tracking-wider">⏱️ Waktu</p>
                <div class="w-1 h-1 sm:w-1.5 sm:h-1.5 bg-cyan-400 rounded-full animate-pulse"></div>
            </div>
            <p class="text-base sm:text-lg md:text-xl lg:text-3xl font-black bg-gradient-to-r from-cyan-300 to-blue-300 bg-clip-text text-transparent font-mono" x-text="time"></p>
        </div>
    </div>

    <script>
        function toggleFullscreen() {
            const elem = document.documentElement;
            const btn = document.getElementById('fullscreenBtn');

            if (!document.fullscreenElement) {
                // Enter fullscreen
                elem.requestFullscreen().catch(err => {
                    console.error('Error entering fullscreen:', err);
                });
            } else {
                // Exit fullscreen
                document.exitFullscreen();
            }
        }

        // Update button icon when fullscreen changes
        document.addEventListener('fullscreenchange', function() {
            const btn = document.getElementById('fullscreenBtn');
            if (document.fullscreenElement) {
                btn.innerHTML = '<svg class="w-4 sm:w-5 md:w-6 h-4 sm:h-5 md:h-6" fill="currentColor" viewBox="0 0 24 24"><path d="M5 16h3v3h2v-5H5v2zm3-8H5v2h5V5H8v3zm6 11h2v-3h3v-2h-5v5zm2-11V5h-2v5h5V8h-3z"/></svg>';
            } else {
                btn.innerHTML = '<svg class="w-4 sm:w-5 md:w-6 h-4 sm:h-5 md:h-6" fill="currentColor" viewBox="0 0 24 24"><path d="M7 14H5v5h5v-2H7v-3zm-2-4h2V7h3V5H5v5zm12 7h-3v2h5v-5h-2v3zM14 5v2h3v3h2V5h-5z"/></svg>';
            }
        });

        function clock() {
            return {
                time: new Date().toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', second: '2-digit' }),
                init() {
                    this.updateTime();
                    setInterval(() => this.updateTime(), 1000);
                },
                updateTime() {
                    this.time = new Date().toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
                }
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            const input = document.getElementById('scanner-input');
            let scanTimeout;

            if (input) {
                input.focus();

                input.addEventListener('blur', function() {
                    setTimeout(() => {
                        input.focus();
                    }, 100);
                });

                input.addEventListener('input', function() {
                    if (scanTimeout) {
                        clearTimeout(scanTimeout);
                    }

                    scanTimeout = setTimeout(() => {
                        if (input.value.trim() !== '') {
                            setTimeout(() => {
                                input.value = '';
                                input.focus();
                            }, 100);
                        }
                    }, 500);
                });
            }
        });
    </script>
</div>
