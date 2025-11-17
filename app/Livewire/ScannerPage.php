<?php

namespace App\Livewire;

use App\Models\DurationSetting;
use App\Models\LessonAttendances;
use App\Models\ScheduleTime;
use App\Models\Teachers;
use App\Models\WeeklySchedules;
use Carbon\Carbon;
use Livewire\Component;

class ScannerPage extends Component
{
    public string $qrCode = '';
    public ?array $teacherData = null;
    public ?string $message = null;
    public ?string $messageType = null;
    public int $scanCount = 0;
    public bool $processing = false;
    public ?string $selectedClassRoom = null;
    public array $classRooms = [];
    public bool $classOptionsLoaded = false;

    public function mount(): void
    {
        $this->scanCount = LessonAttendances::whereDate('date', today())->where('status', 'present')->count();

        // Ensure no class is pre-selected on page load so placeholder shows
        $this->selectedClassRoom = null;
        $this->classOptionsLoaded = false;

        // Preload class options to avoid dropdown race conditions on first selection
        $this->loadClassRooms();

        // Don't auto-select a class on mount; keep placeholder on initial load.
    }

    public function loadClassRooms(): void
    {
        if ($this->classOptionsLoaded) {
            return;
        }

        $this->classRooms = WeeklySchedules::whereNotNull('class_room')
            ->distinct('class_room')
            ->orderBy('class_room')
            ->pluck('class_room')
            ->toArray();

        $this->classOptionsLoaded = true;
    }

    #[\Livewire\Attributes\On('qrCodeScanned')]
    public function handleQrCodeScanned(string $qrCode): void
    {
        // Require class selection
        if (empty($this->selectedClassRoom)) {
            $this->messageType = 'warning';
            $this->message = '❗ Pilih kelas terlebih dahulu sebelum melakukan scan.';
            return;
        }
        $this->processQrCode($qrCode);
    }

    public function updatedQrCode(): void
    {
        // Require class selection
        if (empty($this->selectedClassRoom)) {
            $this->messageType = 'warning';
            $this->message = '❗ Pilih kelas terlebih dahulu sebelum melakukan scan.';
            $this->qrCode = '';
            return;
        }
        // Skip jika masih processing atau empty
        if ($this->processing || empty($this->qrCode)) {
            return;
        }

        $qrCode = trim($this->qrCode);

        // Set processing flag
        $this->processing = true;

        // Process QR code
        $this->processQrCode($qrCode);

        // Clear input setelah process
        $this->qrCode = '';
    }

    public function updatedSelectedClassRoom($value): void
    {
        // Dispatch a browser event to re-focus the scanner input after the class selection is updated.
        // Livewire v3 uses $this->dispatch() to send browser events from PHP to JS
        $this->dispatch('scanner-focus');
    }

    private function processQrCode(string $qrCode): void
    {
        try {
            // Require class selection (double-check in case dev triggers directly)
            if (empty($this->selectedClassRoom)) {
                $this->teacherData = null;
                $this->messageType = 'warning';
                $this->message = '❗ Pilih kelas terlebih dahulu sebelum melakukan scan.';
                $this->processing = false;
                return;
            }

            // Cari teacher berdasarkan qr code, jika tidak ditemukan berikan notifikasi user-friendly
            $teacher = Teachers::where('qr_code', $qrCode)->first();

            if (!$teacher) {
                $this->teacherData = [
                    'name' => 'Tidak Ditemukan',
                    'nip' => 'N/A',
                    'photo' => null,
                    'status' => 'not_found',
                    'message' => 'Guru tidak ditemukan untuk QR code ini',
                ];
                $this->messageType = 'danger';
                $this->message = '❌ Data guru tidak ditemukan. Pastikan QR code terdaftar.';
                $this->processing = false;
                return;
            }

            // Get current time info
            $now = now();
            $currentHour = $this->getCurrentHour();
            $dayOfWeek = $now->dayOfWeek;

            if ($currentHour == 0) {
                $this->teacherData = [
                    'name' => $teacher->name,
                    'nip' => $teacher->nip,
                    'photo' => $teacher->photo_path,
                    'status' => 'no_lesson_time',
                    'message' => 'Saat ini bukan waktu pelajaran. Absen sesi hanya dapat dilakukan saat jam pelajaran aktif.',
                ];
                $this->messageType = 'warning';
                $this->message = '⚠️ Saat ini bukan jam pelajaran aktif. Silakan lakukan scan saat jam pelajaran.';
                $this->processing = false;
                return;
            }

            // Check if teacher has schedule today
            $schedules = WeeklySchedules::where('teacher_id', $teacher->id)
                ->where('day_of_week', $dayOfWeek)
                ->get();

            if ($schedules->isEmpty()) {
                $this->teacherData = [
                    'name' => $teacher->name,
                    'nip' => $teacher->nip,
                    'photo' => $teacher->photo_path,
                    'status' => 'no_schedule',
                    'message' => 'Guru tidak memiliki jadwal mengajar hari ini',
                ];
                $this->messageType = 'warning';
                $this->message = '⚠️ Guru ' . $teacher->name . ' tidak memiliki jadwal mengajar hari ini. Pastikan memilih kelas yang benar atau hubungi admin.';
                $this->processing = false;
                return;
            }

            // Get schedule blocks
            $blocks = $this->getScheduleBlocks($schedules);

            // Filter blocks by selected class room if specified
            if ($this->selectedClassRoom) {
                $blocks = array_filter($blocks, function ($block) {
                    return $block['class_room'] === $this->selectedClassRoom;
                });

                // Jika setelah filter tidak ada block, berarti guru tidak ada jadwal di kelas terpilih
                if (empty($blocks)) {
                    $this->teacherData = [
                        'name' => $teacher->name,
                        'nip' => $teacher->nip,
                        'photo' => $teacher->photo_path,
                        'status' => 'no_schedule_for_class',
                        'message' => "Guru {$teacher->name} tidak punya jadwal di kelas {$this->selectedClassRoom}",
                    ];
                    $this->messageType = 'warning';
                    $this->message = "❌ Guru {$teacher->name} tidak ada jadwal di kelas {$this->selectedClassRoom} hari ini. Silakan pilih kelas lain atau hubungi admin.";
                    $this->processing = false;
                    return;
                }
            }

            // Find the block that contains current hour
            $currentBlock = null;
            foreach ($blocks as $block) {
                if (in_array($currentHour, $block['hours'])) {
                    $currentBlock = $block;
                    break;
                }
            }

            if (!$currentBlock) {
                $totalHours = $schedules->count();
                $this->teacherData = [
                    'name' => $teacher->name,
                    'nip' => $teacher->nip,
                    'photo' => $teacher->photo_path,
                    'status' => 'wrong_time',
                    'message' => "Jam ke-{$currentHour} bukan jadwal mengajar (Total {$totalHours} jam hari ini)",
                ];
                $this->messageType = 'warning';
                $this->message = "⚠️ Guru {$teacher->name} tidak ada pelajaran pada jam ke-{$currentHour}. Silakan cek jadwal atau pilih kelas lain.";
                $this->processing = false;
                return;
            }

            // Check if already processed this block (any hour in block has record)
            $existingRecords = LessonAttendances::where('teacher_id', $teacher->id)
                ->whereDate('date', today())
                ->whereIn('hour_number', $currentBlock['hours'])
                ->get();

            if ($existingRecords->isNotEmpty()) {
                $scannedAtRecord = $existingRecords->whereNotNull('scanned_at')->first();
                $scannedAtStr = $scannedAtRecord && $scannedAtRecord->scanned_at ? Carbon::parse($scannedAtRecord->scanned_at)->format('H:i:s') : null;

                $this->teacherData = [
                    'name' => $teacher->name,
                    'nip' => $teacher->nip,
                    'photo' => $teacher->photo_path,
                    'status' => 'already_scanned',
                    'message' => 'Sudah scan untuk sesi ini',
                ];
                $this->messageType = 'info';
                if ($scannedAtStr) {
                    $this->message = '⏱️ ' . $teacher->name . ' sudah melakukan scan untuk sesi ini pada ' . $scannedAtStr . ' (hari ini).';
                } else {
                    $this->message = '⏱️ ' . $teacher->name . ' sudah melakukan scan untuk sesi ini hari ini.';
                }
                $this->processing = false;
                return;
            }

            // Determine statuses based on scan hour
            $minHour = min($currentBlock['hours']);
            $statuses = [];
            if ($currentHour == $minHour) {
                // Scan at first hour: all present
                foreach ($currentBlock['hours'] as $hour) {
                    $statuses[$hour] = 'present';
                }
            } elseif ($currentHour == $minHour + 1) {
                // Scan at second hour: first absent, rest present
                foreach ($currentBlock['hours'] as $hour) {
                    $statuses[$hour] = ($hour == $minHour) ? 'absent' : 'present';
                }
            } else {
                // For other cases, assume present for current and after, but since example only first/second, maybe error
                $this->messageType = 'danger';
                $this->message = '❌ Scan hanya diperbolehkan pada jam pertama atau kedua dari satu sesi jam berturut-turut. Silakan lakukan scan pada jam pertama atau kedua sesi.';
                $this->processing = false;
                return;
            }

            // Create attendance records for the block
            foreach ($statuses as $hour => $status) {
                LessonAttendances::create([
                    'teacher_id' => $teacher->id,
                    'date' => today(),
                    'hour_number' => $hour,
                    'scanned_at' => $status === 'present' ? $now : null,
                    'status' => $status,
                ]);
            }

            $timeRange = $this->getTimeRange($currentHour);
            $classInfo = $this->selectedClassRoom ? " di {$this->selectedClassRoom}" : '';

            // Build a readable summary of statuses (Jam X: Present/Absent)
            $statusSummaryParts = [];
            foreach ($statuses as $hour => $status) {
                $statusSummaryParts[] = "Jam {$hour}: " . ucfirst($status);
            }
            $statusSummary = implode(', ', $statusSummaryParts);

            $blockHoursStr = implode(', ', $currentBlock['hours']);

            $this->teacherData = [
                'name' => $teacher->name,
                'nip' => $teacher->nip,
                'photo' => $teacher->photo_path,
                'status' => 'success',
                'message' => "Sesi {$currentBlock['class_room']} Jam Ke-{$currentHour} ({$timeRange})",
                'scanned_at' => $now->format('H:i:s'),
            ];
            $this->messageType = 'success';
            $this->message = '✅ Absen sesi berhasil dicatat untuk kelas ' . $currentBlock['class_room'] . ' (Jam ' . $blockHoursStr . '). ' . $statusSummary;
            $this->scanCount += count($statuses);

            // Reset processing flag
            $this->processing = false;
            $this->selectedClassRoom = null;
        } catch (\Exception $e) {
            // Log the error for debugging, but show a generic error message to the user
            \Illuminate\Support\Facades\Log::error('ScannerPage processQrCode error: ' . $e->getMessage(), ['qrCode' => $qrCode]);
            $this->teacherData = null;
            $this->messageType = 'danger';
            $this->message = '❌ Terjadi kesalahan saat memproses QR code. Coba lagi.';

            // Reset processing flag even on error
            $this->processing = false;
            $this->selectedClassRoom = null;
        }
    }

    private function getCurrentHour(): int
    {
        $now = now()->format('H:i:s');
        $scheduleTime = ScheduleTime::where('is_lesson', true)
            ->where('start_time', '<=', $now)
            ->where('end_time', '>', $now)
            ->first();

        return $scheduleTime ? $scheduleTime->hour_number : 0; // Return 0 if no lesson time
    }

    private function getLessonStartTimes(): array
    {
        // Deprecated: now using ScheduleTime model
        return [];
    }

    private function getTimeRange(int $hour): string
    {
        $scheduleTime = ScheduleTime::where('hour_number', $hour)->first();

        if (!$scheduleTime) {
            return 'Waktu tidak tersedia';
        }

        return $scheduleTime->formatted_start_time . ' - ' . $scheduleTime->formatted_end_time;
    }

    private function getScheduleBlocks($schedules): array
    {
        $blocks = [];
        $grouped = $schedules->groupBy('class_room');

        foreach ($grouped as $classRoom => $classSchedules) {
            $hours = $classSchedules->pluck('hour_number')->sort()->values()->toArray();
            // Assume consecutive hours are a block
            $blockHours = [];
            $currentBlock = [];
            foreach ($hours as $hour) {
                if (empty($currentBlock) || $hour == end($currentBlock) + 1) {
                    $currentBlock[] = $hour;
                } else {
                    if (!empty($currentBlock)) {
                        $blockHours[] = $currentBlock;
                        $currentBlock = [];
                    }
                    $currentBlock[] = $hour;
                }
            }
            if (!empty($currentBlock)) {
                $blockHours[] = $currentBlock;
            }

            foreach ($blockHours as $block) {
                $blocks[] = [
                    'class_room' => $classRoom,
                    'hours' => $block,
                ];
            }
        }

        return $blocks;
    }

    private static function getScheduleInfoFromScan(?int $teacherId, ?string $selectedClassRoom = null): string
    {
        if (!$teacherId) {
            return '⏳ Menunggu scan QR code guru...';
        }

        $teacher = \App\Models\Teachers::find($teacherId);
        if (!$teacher) {
            return '❌ Guru tidak ditemukan';
        }

        $dayOfWeek = now()->dayOfWeek;
        $currentHour = static::getCurrentHour();
        $dayName = now()->format('l');

        $schedules = WeeklySchedules::where('teacher_id', $teacherId)
            ->where('day_of_week', $dayOfWeek)
            ->get();

        if ($schedules->isEmpty()) {
            return "❌ {$teacher->name} tidak punya jadwal pada {$dayName}";
        }

        // Filter by selected class room if specified
        if ($selectedClassRoom) {
            $schedules = $schedules->where('class_room', $selectedClassRoom);
        }

        // Check if jam ini ada jadwal
        $hasCurrentHour = $schedules->contains('hour_number', $currentHour);

        if (!$hasCurrentHour) {
            $totalHours = $schedules->count();
            $classInfo = $selectedClassRoom ? " di kelas {$selectedClassRoom}" : '';
            return "❌ {$teacher->name} tidak ada pelajaran jam ke-{$currentHour}{$classInfo}, tapi punya {$totalHours} jam hari ini";
        }

        $timeRange = static::getTimeRange($currentHour);
        $classInfo = $selectedClassRoom ? " di {$selectedClassRoom}" : '';
        return "✅ {$teacher->name} - Jam Ke-{$currentHour} ({$timeRange}){$classInfo}";
    }

    #[\Livewire\Attributes\Layout('components.layouts.blank')]
    public function render()
    {
        return view('livewire.scanner-page');
    }
}
