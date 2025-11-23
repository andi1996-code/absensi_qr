<h2 class="text-xl font-bold tracking-tight">Buat Jadwal Mingguan</h2>
<div class="mt-2">
    <div class="mt-2 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach($teachers as $teacher)
            <div class="flex items-center justify-between gap-6 rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-900">
                <div>
                    <div class="text-sm font-semibold text-gray-900 dark:text-white">{{ $teacher->name }}</div>
                    <div class="text-xs text-gray-500 dark:text-gray-400">{{ $teacher->position ?? '-' }}</div>
                </div>

                <div class="flex items-center gap-2">
                    <a href="{{ route('schedule-builder') }}?teacher_id={{ $teacher->id }}" class="fi-button fi-button-primary inline-flex items-center gap-2 rounded px-4 py-3 text-sm font-medium">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                        Buat Jadwal
                    </a>
                </div>
            </div>
        @endforeach
    </div>
</div>
