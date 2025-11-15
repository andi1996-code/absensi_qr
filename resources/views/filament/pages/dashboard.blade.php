@php
    $data = $this->getDashboardData();
@endphp

<x-filament-panels::page>
    <div class="space-y-6">
        <!-- Header Welcome Card -->
        <div class="bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-blue-900/20 dark:to-indigo-900/20 p-8 rounded-lg border border-blue-200 dark:border-blue-800">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-2">
                        Selamat Datang di Sistem Absensi QR 👋
                    </h1>
                    @if($data['schoolProfile'])
                        <p class="text-lg text-gray-600 dark:text-gray-300">
                            {{ $data['schoolProfile']->name }}
                        </p>
                    @endif
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-2">
                        {{ now()->format('l, d F Y') }}
                    </p>
                </div>
                <div class="text-6xl">📊</div>
            </div>
        </div>

        <!-- Stats Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <!-- Total Guru -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 border-l-4 border-blue-500">
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
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 border-l-4 border-green-500">
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
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 border-l-4 border-yellow-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-1">Gaji Bulan Ini</p>
                        <p class="text-2xl font-bold text-gray-900 dark:text-white">
                            Rp {{ number_format($data['monthSalaries'], 0, ',', '.') }}
                        </p>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">
                            {{ now()->format('F Y') }}
                        </p>
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
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-xl font-bold text-gray-900 dark:text-white">Total Honor Sepanjang Masa</h2>
                <div class="text-3xl">🏆</div>
            </div>
            <div class="flex items-baseline gap-2">
                <span class="text-4xl font-bold text-blue-600 dark:text-blue-400">
                    Rp {{ number_format($data['totalSalaries'], 0, ',', '.') }}
                </span>
                <span class="text-sm text-gray-500 dark:text-gray-400">
                    Kumulatif dari semua guru
                </span>
            </div>
        </div>

        <!-- Quick Links -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <a href="{{ route('duha-scanner') }}" class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-6 hover:shadow-lg transition cursor-pointer">
                <div class="text-3xl mb-3">📷</div>
                <h3 class="font-semibold text-gray-900 dark:text-white mb-1">Scanner Dhuha</h3>
                <p class="text-sm text-gray-600 dark:text-gray-400">Buka scanner absensi pagi</p>
            </a>

            <a href="{{ route('filament.admin.resources.salaries.index') }}" class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg p-6 hover:shadow-lg transition cursor-pointer">
                <div class="text-3xl mb-3">💰</div>
                <h3 class="font-semibold text-gray-900 dark:text-white mb-1">Kelola Gaji</h3>
                <p class="text-sm text-gray-600 dark:text-gray-400">Lihat dan kelola gaji guru</p>
            </a>

            <a href="{{ route('filament.admin.resources.teachers.index') }}" class="bg-purple-50 dark:bg-purple-900/20 border border-purple-200 dark:border-purple-800 rounded-lg p-6 hover:shadow-lg transition cursor-pointer">
                <div class="text-3xl mb-3">👨‍🏫</div>
                <h3 class="font-semibold text-gray-900 dark:text-white mb-1">Data Guru</h3>
                <p class="text-sm text-gray-600 dark:text-gray-400">Kelola informasi guru</p>
            </a>
        </div>
    </div>
</x-filament-panels::page>
