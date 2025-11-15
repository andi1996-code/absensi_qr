<x-filament-panels::page>
    <div class="space-y-6">
        <!-- Header -->
        <x-filament::section>
            <x-slot name="heading">
                📊 Jadwal Guru - Tabel Grid
            </x-slot>
            <x-slot name="description">
                Klik cell untuk menambah/menghapus jadwal dengan cepat
            </x-slot>
        </x-filament::section>

        <!-- Teacher Selection -->
        <x-filament::section>
            <x-slot name="heading">
                Pilih Guru
            </x-slot>

            <div class="space-y-4">
                <select
                    wire:model.live="selectedTeacherId"
                    class="fi-input block w-full md:w-96 rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm shadow-sm transition duration-75 placeholder:text-gray-400 focus:border-primary-500 focus:ring-1 focus:ring-inset focus:ring-primary-500 disabled:bg-gray-50 disabled:text-gray-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder:text-gray-400 dark:focus:border-primary-500 dark:focus:ring-primary-500">
                    <option value="">-- Pilih Guru --</option>
                    @foreach($this->getTeachers() as $teacher)
                        <option value="{{ $teacher->id }}">{{ $teacher->name }}</option>
                    @endforeach
                </select>

                @if($this->selectedTeacherId)
                    <div class="fi-notification fi-notification-success relative w-full rounded-lg border border-green-200 bg-green-50 p-4 text-sm text-green-800 dark:border-green-900 dark:bg-green-950 dark:text-green-200">
                        <div class="flex items-center gap-3">
                            <svg class="h-5 w-5 flex-shrink-0" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                            </svg>
                            <div class="flex flex-col gap-1">
                                <span class="font-semibold">Total Jam Mengajar</span>
                                <span>{{ $this->getTotalScheduledHours() }} jam/minggu</span>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </x-filament::section>

        <!-- Schedule Matrix -->
        @if($this->selectedTeacherId)
            <x-filament::section>
                <x-slot name="heading">
                    Jadwal Mingguan
                </x-slot>

                <div class="overflow-x-auto rounded-lg border border-gray-200 dark:border-gray-700">
                    <table class="w-full">
                        <thead class="divide-x divide-gray-200 bg-gray-50 dark:divide-gray-700 dark:bg-gray-900">
                            <tr>
                                <th class="px-4 py-3 text-left text-sm font-semibold text-gray-900 dark:text-white w-24">Jam Ke-</th>
                                @foreach(['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'] as $day)
                                    <th class="px-4 py-3 text-center text-sm font-semibold text-gray-900 dark:text-white">{{ $day }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            @foreach($this->getScheduleMatrix() as $row)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-800">
                                    <td class="px-4 py-3 font-medium text-gray-900 dark:text-white">{{ $row['jam_ke'] }}</td>

                                    @foreach($row['days'] as $day)
                                        <td class="px-4 py-3 text-center">
                                            <button
                                                wire:click="toggleSchedule({{ $day['day_num'] }}, {{ $row['jam_ke'] }})"
                                                class="inline-flex items-center justify-center w-10 h-10 rounded-lg font-bold transition-all duration-200 @if($day['has_schedule']) bg-green-600 hover:bg-green-700 shadow-lg dark:bg-green-700 dark:hover:bg-green-800 @else bg-gray-200 hover:bg-gray-300 dark:bg-gray-700 dark:hover:bg-gray-600 @endif"
                                                title="@if($day['has_schedule'])Hapus jadwal @else Tambah jadwal @endif">
                                                @if($day['has_schedule'])
                                                    <svg class="w-5 h-5 text-gray-900 dark:text-white" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                                    </svg>
                                                @endif
                                            </button>
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Legend -->
                <div class="mt-6 grid grid-cols-2 gap-4">
                    <div class="flex items-center gap-3 rounded-lg border border-blue-200 bg-blue-50 p-4 dark:border-blue-900 dark:bg-blue-950">
                        <div class="h-6 w-6 rounded-md bg-green-600 dark:bg-green-700"></div>
                        <span class="text-sm font-medium text-gray-900 dark:text-white">Jadwal Aktif</span>
                    </div>
                    <div class="flex items-center gap-3 rounded-lg border border-gray-200 bg-gray-50 p-4 dark:border-gray-700 dark:bg-gray-900">
                        <div class="h-6 w-6 rounded-md bg-gray-200 dark:bg-gray-700"></div>
                        <span class="text-sm font-medium text-gray-900 dark:text-white">Tidak Ada Jadwal</span>
                    </div>
                </div>
            </x-filament::section>

        @else
            <x-filament::section>
                <div class="flex flex-col items-center justify-center py-12">
                    <svg class="h-16 w-16 text-gray-400 dark:text-gray-600 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <p class="text-lg text-gray-500 dark:text-gray-400">Pilih guru untuk melihat jadwal mingguan</p>
                </div>
            </x-filament::section>
        @endif
    </div>
</x-filament-panels::page>
