<x-filament-panels::page>
    <div class="mx-auto max-w-4xl">
        <!-- Header -->
        <div class="mb-8 text-center">
            <h1 class="text-4xl font-bold text-gray-900 dark:text-white mb-2">
                📱 QR Code Scanner
            </h1>
            <p class="text-gray-600 dark:text-gray-400">Arahkan scanner ke QR code guru untuk mencatat kehadiran</p>
        </div>

        <!-- Scanner Input -->
        <div class="mb-8 bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                Scan QR Code
            </label>
            <input
                type="text"
                wire:model.live="qrCode"
                autofocus
                placeholder="Scanner akan input di sini..."
                class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-gray-700 dark:text-white"
            />
        </div>

        <!-- Message Display -->
        @if ($message)
            <div class="mb-8 p-4 rounded-lg border-l-4 text-center font-semibold
                {{ match($messageType) {
                    'success' => 'bg-green-50 dark:bg-green-900 border-green-500 text-green-800 dark:text-green-200',
                    'warning' => 'bg-yellow-50 dark:bg-yellow-900 border-yellow-500 text-yellow-800 dark:text-yellow-200',
                    'info' => 'bg-blue-50 dark:bg-blue-900 border-blue-500 text-blue-800 dark:text-blue-200',
                    default => 'bg-red-50 dark:bg-red-900 border-red-500 text-red-800 dark:text-red-200'
                } }}
            ">
                {{ $message }}
            </div>
        @endif

        <!-- Teacher Card Display -->
        @if ($teacherData)
            <div class="mb-8 bg-gradient-to-br rounded-2xl shadow-2xl p-8 text-white
                {{ match($teacherData['status']) {
                    'success' => 'from-green-400 to-green-600',
                    'no_schedule', 'wrong_time' => 'from-yellow-400 to-yellow-600',
                    'already_scanned' => 'from-blue-400 to-blue-600',
                    default => 'from-red-400 to-red-600'
                } }}
            ">

                <div class="grid grid-cols-1 md:grid-cols-3 gap-8 items-center">
                    <!-- Photo -->
                    <div class="flex justify-center md:justify-start">
                        @if ($teacherData['photo'])
                            <img
                                src="{{ Storage::url($teacherData['photo']) }}"
                                alt="{{ $teacherData['name'] }}"
                                class="w-40 h-40 rounded-full border-4 border-white shadow-lg object-cover"
                            />
                        @else
                            <div class="w-40 h-40 rounded-full border-4 border-white bg-white/20 flex items-center justify-center">
                                <svg class="w-20 h-20 text-white" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
                                </svg>
                            </div>
                        @endif
                    </div>

                    <!-- Teacher Info -->
                    <div class="md:col-span-2 space-y-4">
                        <div>
                            <p class="text-sm opacity-90">Nama Guru</p>
                            <h2 class="text-3xl font-bold">{{ $teacherData['name'] }}</h2>
                        </div>

                        <div>
                            <p class="text-sm opacity-90">NIP</p>
                            <p class="text-xl font-semibold">{{ $teacherData['nip'] ?? 'N/A' }}</p>
                        </div>

                        <div>
                            <p class="text-sm opacity-90">
                                @if($teacherData['status'] === 'success')
                                    Jam Mengajar
                                @else
                                    Status
                                @endif
                            </p>
                            <p class="text-xl font-semibold">{{ $teacherData['message'] }}</p>
                        </div>

                        @if($teacherData['status'] === 'success' && isset($teacherData['scanned_at']))
                            <div class="pt-4 border-t border-white/30">
                                <p class="text-sm opacity-90">Waktu Scan</p>
                                <p class="text-2xl font-bold">{{ $teacherData['scanned_at'] }}</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        @endif

        <!-- Stats Footer -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="bg-blue-50 dark:bg-blue-900 rounded-lg p-4 text-center">
                <p class="text-sm text-gray-600 dark:text-gray-400">Total Scan Hari Ini</p>
                <p class="text-3xl font-bold text-blue-600 dark:text-blue-400">{{ $scanCount }}</p>
            </div>
            <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4 text-center" x-data="clock()">
                <p class="text-sm text-gray-600 dark:text-gray-400">Waktu Saat Ini</p>
                <p class="text-3xl font-bold text-gray-700 dark:text-gray-300" x-text="time"></p>
            </div>
        </div>

        <!-- Tips -->
        <div class="mt-8 bg-blue-50 dark:bg-blue-900 border border-blue-200 dark:border-blue-700 rounded-lg p-4">
            <h3 class="font-semibold text-blue-900 dark:text-blue-100 mb-2">💡 Tips Penggunaan</h3>
            <ul class="text-sm text-blue-800 dark:text-blue-200 space-y-1">
                <li>• Arahkan scanner ke QR code guru</li>
                <li>• Sistem otomatis menyimpan data jika jadwal sesuai</li>
                <li>• Status akan ditampilkan secara real-time</li>
                <li>• Untuk mulai scan baru, silakan scan QR code berikutnya</li>
            </ul>
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
            const input = document.querySelector('input[data-model="qrCode"]');
            if (input) {
                input.focus();
            }
        });
    </script>
</x-filament-panels::page>
