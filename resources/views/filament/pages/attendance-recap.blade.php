@php
    $attendanceDetails = count($this->attendance_data) > 0 ? $this->attendance_data : $this->getAttendanceDetails();
    $summaryData = $this->getSummaryData();
@endphp

<x-filament::page>
    <div class="space-y-6">
        <!-- Form Filter -->
        <div class="bg-white dark:bg-gray-900 rounded-lg shadow p-6">
            {{ $this->form }}
        </div>

        <!-- Summary Cards -->
        @if($this->selected_date)
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4">
                <!-- Duha Count -->
                <div class="bg-gradient-to-br from-purple-50 to-purple-100 dark:from-purple-900/20 dark:to-purple-900/40 rounded-lg shadow p-4 border border-purple-200 dark:border-purple-800">
                    <div class="text-sm font-medium text-purple-600 dark:text-purple-400 mb-2">
                        🌅 Absen Duha
                    </div>
                    <div class="text-3xl font-bold text-purple-900 dark:text-purple-100">
                        {{ $summaryData['duha_count'] }}
                    </div>
                </div>

                <!-- Duha Late -->
                <div class="bg-gradient-to-br from-red-50 to-red-100 dark:from-red-900/20 dark:to-red-900/40 rounded-lg shadow p-4 border border-red-200 dark:border-red-800">
                    <div class="text-sm font-medium text-red-600 dark:text-red-400 mb-2">
                        ⏰ Terlambat
                    </div>
                    <div class="text-3xl font-bold text-red-900 dark:text-red-100">
                        {{ $summaryData['duha_late'] }}
                    </div>
                </div>

                <!-- Departure Count -->
                <div class="bg-gradient-to-br from-orange-50 to-orange-100 dark:from-orange-900/20 dark:to-orange-900/40 rounded-lg shadow p-4 border border-orange-200 dark:border-orange-800">
                    <div class="text-sm font-medium text-orange-600 dark:text-orange-400 mb-2">
                        🚪 Absen Pulang
                    </div>
                    <div class="text-3xl font-bold text-orange-900 dark:text-orange-100">
                        {{ $summaryData['departure_count'] }}
                    </div>
                </div>

                <!-- Lesson Count -->
                <div class="bg-gradient-to-br from-blue-50 to-blue-100 dark:from-blue-900/20 dark:to-blue-900/40 rounded-lg shadow p-4 border border-blue-200 dark:border-blue-800">
                    <div class="text-sm font-medium text-blue-600 dark:text-blue-400 mb-2">
                        📚 Mengajar
                    </div>
                    <div class="text-3xl font-bold text-blue-900 dark:text-blue-100">
                        {{ $summaryData['lesson_count'] }}
                    </div>
                </div>

                <!-- Attendance Percentage -->
                <div class="bg-gradient-to-br from-green-50 to-green-100 dark:from-green-900/20 dark:to-green-900/40 rounded-lg shadow p-4 border border-green-200 dark:border-green-800">
                    <div class="text-sm font-medium text-green-600 dark:text-green-400 mb-2">
                        ✅ Kehadiran
                    </div>
                    <div class="text-3xl font-bold text-green-900 dark:text-green-100">
                        {{ $summaryData['attendance_percentage'] }}
                    </div>
                </div>
            </div>

            <!-- Attendance Details Table -->
            <div class="bg-white dark:bg-gray-900 rounded-lg shadow overflow-hidden">
                <div class="p-6 border-b border-gray-200 dark:border-gray-800">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                        @if($this->filter_type === 'daily')
                            📅 Detail Absen Harian
                        @elseif($this->filter_type === 'weekly')
                            📅 Detail Absen Mingguan
                        @else
                            📅 Detail Absen Bulanan
                        @endif
                        @if(!$this->teacher_id)
                            - Semua Guru
                        @endif
                    </h3>
                </div>

                @if(count($attendanceDetails) > 0)
                    <div class="overflow-x-auto">
                        <table class="w-full divide-y divide-gray-200 dark:divide-gray-800">
                            <thead class="bg-gray-50 dark:bg-gray-800">
                                <tr>
                                    @if(!$this->teacher_id)
                                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900 dark:text-white">
                                            Guru
                                        </th>
                                    @endif
                                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900 dark:text-white">
                                        Tipe
                                    </th>
                                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900 dark:text-white">
                                        Tanggal
                                    </th>
                                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900 dark:text-white">
                                        Hari
                                    </th>
                                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900 dark:text-white">
                                        Waktu Scan
                                    </th>
                                    <th class="px-6 py-3 text-center text-sm font-semibold text-gray-900 dark:text-white">
                                        Status
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                                @foreach($attendanceDetails as $detail)
                                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
                                        @if(!$this->teacher_id)
                                            <td class="px-6 py-4 text-sm text-gray-900 dark:text-gray-100">
                                                {{ $detail['teacher_name'] }}
                                            </td>
                                        @endif
                                        <td class="px-6 py-4 text-sm">
                                            @if($detail['type'] === 'DUHA')
                                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-purple-100 dark:bg-purple-900 text-purple-800 dark:text-purple-100">
                                                    🌅 DUHA
                                                </span>
                                            @elseif($detail['type'] === 'PULANG')
                                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-orange-100 dark:bg-orange-900 text-orange-800 dark:text-orange-100">
                                                    🚪 PULANG
                                                </span>
                                            @else
                                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-100">
                                                    📚 PELAJARAN
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-900 dark:text-gray-100">
                                            {{ $detail['date'] }}
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-400">
                                            {{ $detail['day'] }}
                                        </td>
                                        <td class="px-6 py-4 text-sm font-mono text-gray-900 dark:text-gray-100">
                                            {{ $detail['time'] }}
                                        </td>
                                        <td class="px-6 py-4 text-center text-sm">
                                            @if($detail['badge_color'] === 'danger')
                                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-red-100 dark:bg-red-900 text-red-800 dark:text-red-100">
                                                    ⛔ {{ $detail['status'] }}
                                                </span>
                                            @elseif($detail['badge_color'] === 'warning')
                                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-yellow-100 dark:bg-yellow-900 text-yellow-800 dark:text-yellow-100">
                                                    ⚠️ {{ $detail['status'] }}
                                                </span>
                                            @elseif($detail['badge_color'] === 'info')
                                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-100">
                                                    ℹ️ {{ $detail['status'] }}
                                                </span>
                                            @else
                                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-100">
                                                    ✅ {{ $detail['status'] }}
                                                </span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="p-6 text-center text-gray-500 dark:text-gray-400">
                        📭 Tidak ada data absen untuk periode ini
                    </div>
                @endif
            </div>
        @else
            <div class="bg-blue-50 dark:bg-blue-900/20 rounded-lg border border-blue-200 dark:border-blue-800 p-6 text-center">
                <div class="text-blue-900 dark:text-blue-100">
                    💡 Silakan pilih tanggal untuk melihat rekap absen
                </div>
            </div>
        @endif
    </div>
</x-filament::page>
