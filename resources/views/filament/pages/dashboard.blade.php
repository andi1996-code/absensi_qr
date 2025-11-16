@php
    $data = $this->getDashboardData();
@endphp

<x-filament-panels::page>
    <div class="space-y-6">
        <!-- Header Welcome Card -->
        <div class="relative overflow-hidden bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-blue-900/20 dark:to-indigo-900/20 py-12 px-5 sm:py-16 sm:px-12 rounded-xl border border-blue-200 dark:border-blue-800 shadow-lg hover:shadow-2xl transition-transform transform hover:-translate-y-1">
            <div class="absolute top-0 right-0 -mt-14 -mr-16 w-56 h-56 rounded-full bg-white/20 dark:bg-indigo-900/30 blur-3xl opacity-30 pointer-events-none"></div>
            <div class="relative flex flex-col sm:flex-row sm:items-center justify-between gap-8">
                <div class="min-w-0">
                    <div class="flex items-center gap-4">
                        @if($data['schoolProfile'] && $data['schoolProfile']->logo_path)
                            <img src="{{ Storage::url($data['schoolProfile']->logo_path) }}" alt="{{ $data['schoolProfile']->name }}" class="h-16 w-16 rounded-md object-cover shadow-sm" />
                        @else
                            <div class="h-12 w-12 rounded-md bg-blue-100 dark:bg-blue-800 flex items-center justify-center text-xl">🏫</div>
                        @endif
                        <div class="min-w-0">
                            <h1 class="text-4xl font-bold text-gray-900 dark:text-white leading-tight">
                                Selamat Datang,
                                <span class="text-primary-600 dark:text-primary-400 ml-2">{{ Auth::user()?->name ?? (filament()->getUserName() ?? 'Admin') }}</span>
                            </h1>
                            @if($data['schoolProfile'] && $data['schoolProfile']->header_text)
                                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1 truncate">{{ $data['schoolProfile']->header_text }}</p>
                            @endif
                            <p class="text-base text-gray-500 dark:text-gray-400 mt-1">{{ now()->format('l, d F Y') }}</p>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <!-- Stats Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <!-- Total Guru -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6 border-l-8 border-blue-500 transform transition hover:-translate-y-1 hover:shadow-2xl">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-1">Total Guru</p>
                        <p class="text-3xl font-bold text-gray-900 dark:text-white">{{ $data['totalTeachers'] }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">
                            {{ $data['activeTeachers'] }} aktif ({{ $data['activeTeachersPercentage'] }}%)
                        </p>
                    </div>
                    <div class="text-4xl">👥</div>
                </div>
            </div>

            <!-- Guru Aktif -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6 border-l-8 border-green-500 transform transition hover:-translate-y-1 hover:shadow-2xl">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-1">Guru Aktif</p>
                        <p class="text-3xl font-bold text-gray-900 dark:text-white">{{ $data['activeTeachers'] }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">
                            Siap mengajar
                        </p>
                    </div>
                    <div class="text-4xl">✅</div>
                </div>
            </div>

            <!-- Total Gaji Bulan Ini -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6 border-l-8 border-yellow-500 transform transition hover:-translate-y-1 hover:shadow-2xl">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-1">Gaji Bulan Ini</p>
                        <div class="flex items-center gap-4">
                            <p class="text-2xl font-bold text-gray-900 dark:text-white">
                                Rp {{ number_format($data['monthSalaries'], 0, ',', '.') }}
                            </p>
                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-yellow-50 text-yellow-800 dark:bg-yellow-900/20 dark:text-yellow-300">+3.2%</span>
                        </div>
                        <!-- mini-sparkline -->
                        <svg class="mt-2 h-4 w-28 text-yellow-500 dark:text-yellow-400" viewBox="0 0 100 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <polyline points="0,14 12,10 24,12 36,6 48,8 60,4 72,7 84,2 96,6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" fill="none" opacity="0.85" />
                        </svg>
                    </div>
                    <div class="text-4xl">💰</div>
                </div>
            </div>

            {{-- <!-- Rata-rata Kehadiran -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 border-l-4 border-purple-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-1">Kehadiran</p>
                        <p class="text-3xl font-bold text-gray-900 dark:text-white">{{ $data['attendanceRate'] }}%</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">
                            Rata-rata hadir
                        </p>
                    </div>
                    <div class="text-4xl">📅</div>
                </div>
            </div> --}}
        </div>

        <!-- Total Gaji Sepanjang Masa -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6 border border-transparent dark:border-gray-700 hover:shadow-2xl transform transition hover:-translate-y-1">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-xl font-bold text-gray-900 dark:text-white">Total Honor Sepanjang Masa</h2>
                <div class="text-3xl">🏆</div>
            </div>
            <div class="flex items-baseline gap-3">
                <span class="text-4xl font-bold text-blue-600 dark:text-blue-400">
                    Rp {{ number_format($data['totalSalaries'], 0, ',', '.') }}
                </span>
                <span class="text-sm text-gray-500 dark:text-gray-400">
                    Kumulatif dari semua guru
                </span>
            </div>
        </div>

        <!-- Quick Links -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <a href="{{ route('duha-scanner') }}" class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-xl p-6 transform transition hover:scale-105 hover:shadow-lg cursor-pointer">
                <div class="text-3xl mb-3">📷</div>
                <h3 class="font-semibold text-gray-900 dark:text-white mb-1">Scanner Dhuha</h3>
                <p class="text-sm text-gray-600 dark:text-gray-400">Buka scanner absensi pagi</p>
            </a>

            <a href="{{ route('filament.admin.resources.salaries.index') }}" class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-xl p-6 transform transition hover:scale-105 hover:shadow-lg cursor-pointer">
                <div class="text-3xl mb-3">💰</div>
                <h3 class="font-semibold text-gray-900 dark:text-white mb-1">Kelola Gaji</h3>
                <p class="text-sm text-gray-600 dark:text-gray-400">Lihat dan kelola gaji guru</p>
            </a>

            <a href="{{ route('filament.admin.resources.teachers.index') }}" class="bg-purple-50 dark:bg-purple-900/20 border border-purple-200 dark:border-purple-800 rounded-xl p-6 transform transition hover:scale-105 hover:shadow-lg cursor-pointer">
                <div class="text-3xl mb-3">👨‍🏫</div>
                <h3 class="font-semibold text-gray-900 dark:text-white mb-1">Data Guru</h3>
                <p class="text-sm text-gray-600 dark:text-gray-400">Kelola informasi guru</p>
            </a>
        </div>
    </div>
</x-filament-panels::page>
