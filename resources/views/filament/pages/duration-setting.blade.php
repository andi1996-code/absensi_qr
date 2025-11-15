@php
    use Filament\Support\Enums\MaxWidth;
@endphp

<x-filament-panels::page>
    <div class="space-y-6">
        <!-- Info Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="bg-blue-50 dark:bg-blue-900/20 p-4 rounded-lg border border-blue-200 dark:border-blue-800">
                <div class="flex items-center gap-3">
                    <div class="text-3xl">⏰</div>
                    <div>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Durasi Saat Ini</p>
                        <p class="font-semibold text-gray-900 dark:text-white text-2xl">{{ $data['lesson_duration_minutes'] ?? 45 }} menit</p>
                    </div>
                </div>
            </div>

            <div class="bg-purple-50 dark:bg-purple-900/20 p-4 rounded-lg border border-purple-200 dark:border-purple-800">
                <div class="flex items-center gap-3">
                    <div class="text-3xl">📚</div>
                    <div>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Total Jam Per Hari</p>
                        <p class="font-semibold text-gray-900 dark:text-white text-2xl">
                            9 jam pelajaran
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Form -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <form wire:submit="save" class="space-y-6">
                {{ $this->form }}

                <div class="flex gap-3 pt-4">
                    <button type="submit"
                        style="background-color: #2563eb !important; color: white !important; padding: 12px 32px !important; font-weight: bold !important; font-size: 16px !important; border-radius: 8px !important; box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1) !important; transition: all 0.3s !important; cursor: pointer !important; border: none !important;"
                        onmouseover="this.style.backgroundColor='#1d4ed8 !important'; this.style.transform='scale(1.05)';"
                        onmouseout="this.style.backgroundColor='#2563eb !important'; this.style.transform='scale(1)';">
                        💾 Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>

        <!-- Preview Schedule -->
        <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg p-6">
            <h3 class="font-semibold text-green-900 dark:text-green-100 mb-4 flex items-center gap-2">
                <span class="text-2xl">📋</span>
                Jadwal Pelajaran Harian
            </h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @php
                    $duration = (int)($data['lesson_duration_minutes'] ?? 45);
                    $currentTime = \Carbon\Carbon::createFromTimeString('08:00');
                @endphp

                @for ($hour = 1; $hour <= 9; $hour++)
                    @php
                        $start = $currentTime->copy();
                        $end = $start->copy()->addMinutes($duration);
                    @endphp

                    <div class="p-3 rounded {{ $hour <= 3 || ($hour >= 4 && $hour <= 6) || $hour >= 7 ? 'bg-white dark:bg-gray-700 border-l-4 border-green-500' : 'bg-yellow-50 dark:bg-yellow-900/20 border-l-4 border-yellow-500' }}">
                        @if($hour == 4)
                            <div class="flex items-center gap-2 mb-2">
                                <div class="text-xl">☕</div>
                                <div class="font-semibold text-yellow-900 dark:text-yellow-100">Istirahat</div>
                            </div>
                            <div class="text-sm text-yellow-700 dark:text-yellow-300">09:45 - 10:00</div>
                        @elseif($hour == 7)
                            <div class="flex items-center gap-2 mb-2">
                                <div class="text-xl">🕌</div>
                                <div class="font-semibold text-yellow-900 dark:text-yellow-100">Shalat Dzuhur</div>
                            </div>
                            <div class="text-sm text-yellow-700 dark:text-yellow-300">11:45 - 12:25</div>
                        @else
                            <div class="flex items-center gap-2 mb-2">
                                <div class="text-lg font-bold text-green-600 dark:text-green-400">{{ $hour }}</div>
                                <div class="font-semibold text-gray-900 dark:text-white">Jam Ke-{{ $hour }}</div>
                            </div>
                            <div class="text-sm text-gray-600 dark:text-gray-400">
                                {{ $start->format('H:i') }} - {{ $end->format('H:i') }}
                            </div>
                        @endif
                    </div>

                    @php
                        if ($hour == 3) {
                            // After break
                            $currentTime = \Carbon\Carbon::createFromTimeString('10:00');
                        } elseif ($hour == 6) {
                            // After dzuhur
                            $currentTime = \Carbon\Carbon::createFromTimeString('12:25');
                        } else {
                            $currentTime = $end;
                        }
                    @endphp
                @endfor
            </div>
        </div>

        <!-- Warning -->
        <div class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg p-4">
            <div class="flex gap-3">
                <div class="text-2xl">⚠️</div>
                <div>
                    <p class="font-semibold text-yellow-900 dark:text-yellow-100 mb-2">Catatan Penting:</p>
                    <ul class="text-sm text-yellow-800 dark:text-yellow-200 space-y-1">
                        <li>✓ Perubahan durasi akan berlaku untuk perhitungan absensi selanjutnya</li>
                        <li>✓ Data absensi yang sudah ada tidak akan berubah otomatis</li>
                        <li>✓ Gunakan nilai antara 20-60 menit</li>
                        <li>✓ Sistem memperhitungkan istirahat (09:45-10:00) dan shalat dzuhur (11:45-12:25)</li>
                        <li>✓ Total 9 jam pelajaran per hari dari 08:00 sampai 14:10</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</x-filament-panels::page>
