<div class="w-screen h-screen bg-gradient-to-br from-slate-900 to-slate-800 flex flex-col p-6 overflow-hidden">
    <!-- Header - Compact -->
    <div class="text-center mb-4">
        <h1 class="text-4xl font-bold text-white">📱 QR Code Scanner</h1>
        <p class="text-sm text-gray-300">Scan QR code guru untuk mencatat kehadiran</p>
    </div>

    <!-- Main Content - Flexible Layout -->
    <div class="flex-1 flex flex-col lg:flex-row gap-6 overflow-hidden">

        <!-- Left Column: Scanner & Message -->
        <div class="flex flex-col gap-4 lg:w-1/3">
            <!-- Scanner Input -->
            <div class="bg-white/10 backdrop-blur-md rounded-2xl shadow-2xl p-6 border border-white/20">
                <label class="block text-sm font-semibold text-white mb-3 text-center">🔍 Scan QR Code</label>
                <!-- Class Room Selection -->
                <div class="mb-4">
                    <label class="block text-sm font-semibold text-white mb-2">🏫 Pilih Kelas (Opsional)</label>
                    <select
                        wire:model.live="selectedClassRoom"
                        class="w-full px-4 py-3 text-lg border-2 rounded-xl focus:ring-4 focus:ring-blue-400 focus:border-transparent bg-white/5 text-white placeholder-gray-400 transition-all border-white/30"
                    >
                        <option value="">Semua Kelas</option>
                        @foreach(\App\Models\WeeklySchedules::distinct('class_room')->pluck('class_room') as $classRoom)
                            <option value="{{ $classRoom }}">{{ $classRoom }}</option>
                        @endforeach
                    </select>
                    <p class="text-xs text-gray-300 mt-1">Pilih kelas untuk filter scan (opsional)</p>
                </div>
                <input
                    type="text"
                    id="scanner-input"
                    wire:model.live.debounce.300ms="qrCode"
                    autofocus
                    placeholder="{{ $processing ? 'Processing...' : 'Scanner akan input...' }}"
                    class="w-full px-4 py-3 text-lg border-2 rounded-xl focus:ring-4 focus:ring-blue-400 focus:border-transparent bg-white/5 text-white placeholder-gray-400 transition-all {{ $processing ? 'border-green-400 opacity-70' : 'border-white/30' }}"
                />
                @if($processing)
                    <p class="text-xs text-green-300 mt-2 text-center">⏳ Memproses scan...</p>
                @endif
            </div>

            <!-- Message Display -->
            @if ($message)
                <div class="p-4 rounded-2xl border-l-4 text-center text-sm font-bold
                    {{ match($messageType) {
                        'success' => 'bg-green-500/20 border-green-400 text-green-200 backdrop-blur-md',
                        'warning' => 'bg-yellow-500/20 border-yellow-400 text-yellow-200 backdrop-blur-md',
                        'info' => 'bg-blue-500/20 border-blue-400 text-blue-200 backdrop-blur-md',
                        default => 'bg-red-500/20 border-red-400 text-red-200 backdrop-blur-md'
                    } }}
                ">
                    {{ $message }}
                </div>
            @endif
        </div>

        <!-- Right Column: Teacher Card - Always Visible -->
        <div class="lg:w-2/3 flex items-center justify-center overflow-hidden">
            <div class="w-full bg-gradient-to-br rounded-3xl shadow-2xl p-8 text-white h-fit
                {{ $teacherData ? match($teacherData['status']) {
                    'success' => 'from-green-400 to-green-600',
                    'no_schedule', 'wrong_time' => 'from-yellow-400 to-yellow-600',
                    'already_scanned' => 'from-blue-400 to-blue-600',
                    default => 'from-red-400 to-red-600'
                } : 'from-slate-700 to-slate-800 border border-slate-600' }}
            ">
                @if ($teacherData)
                    <!-- Teacher Data Available -->
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-6 items-center">
                        <!-- Photo -->
                        <div class="flex justify-center sm:col-span-1">
                            @if ($teacherData['photo'])
                                <img
                                    src="{{ Storage::url($teacherData['photo']) }}"
                                    alt="{{ $teacherData['name'] }}"
                                    class="w-40 h-40 rounded-full border-6 border-white shadow-2xl object-cover"
                                />
                            @else
                                <div class="w-40 h-40 rounded-full border-6 border-white bg-white/20 flex items-center justify-center">
                                    <svg class="w-20 h-20 text-white" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
                                    </svg>
                                </div>
                            @endif
                        </div>

                        <!-- Teacher Info -->
                        <div class="sm:col-span-2 space-y-4">
                            <div>
                                <p class="text-sm opacity-90">Nama Guru</p>
                                <h2 class="text-3xl font-bold">{{ $teacherData['name'] }}</h2>
                            </div>

                            <div>
                                <p class="text-sm opacity-90">NIP</p>
                                <p class="text-2xl font-semibold">{{ $teacherData['nip'] ?? 'N/A' }}</p>
                            </div>

                            <div>
                                <p class="text-sm opacity-90">
                                    @if($teacherData['status'] === 'success')
                                        Jam Mengajar
                                    @else
                                        Status
                                    @endif
                                </p>
                                <p class="text-2xl font-semibold">{{ $teacherData['message'] }}</p>
                            </div>

                            @if($teacherData['status'] === 'success' && isset($teacherData['scanned_at']))
                                <div class="pt-4 border-t-2 border-white/30">
                                    <p class="text-sm opacity-90">Waktu Scan</p>
                                    <p class="text-3xl font-bold">{{ $teacherData['scanned_at'] }}</p>
                                </div>
                            @endif
                        </div>
                    </div>
                @else
                    <!-- Empty Card State -->
                    <div class="flex flex-col items-center justify-center py-16">
                        <svg class="w-24 h-24 text-slate-500 mb-6 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <p class="text-xl text-slate-400 font-semibold text-center">
                            Menunggu scan QR code...
                        </p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Stats Footer - Compact -->
    <div class="grid grid-cols-2 gap-4 mt-4">
        <div class="bg-blue-500/20 backdrop-blur-md rounded-2xl p-4 text-center border border-blue-400/30">
            <p class="text-xs text-blue-100 mb-1">Total Scan</p>
            <p class="text-3xl font-bold text-blue-300">{{ $scanCount }}</p>
        </div>
        <div class="bg-gray-500/20 backdrop-blur-md rounded-2xl p-4 text-center border border-gray-400/30" x-data="clock()">
            <p class="text-xs text-gray-100 mb-1">Waktu</p>
            <p class="text-3xl font-bold text-gray-300" x-text="time"></p>
        </div>
    </div>

    <script>
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

                // Auto focus kembali setelah blur
                input.addEventListener('blur', function() {
                    setTimeout(() => {
                        input.focus();
                    }, 100);
                });

                // Handle input - auto reset setelah scan
                input.addEventListener('input', function() {
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
                                input.focus();
                            }, 100);
                        }
                    }, 500);
                });
            }
        });
    </script>
</div>
