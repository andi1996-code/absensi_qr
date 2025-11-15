<?php

namespace App\Livewire;

use App\Models\DurationSetting;
use App\Models\LessonAttendances;
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

    public function mount(): void
    {
        $this->scanCount = LessonAttendances::whereDate('date', today())->count();
    }

    #[\Livewire\Attributes\On('qrCodeScanned')]
    public function handleQrCodeScanned(string $qrCode): void
    {
        $this->processQrCode($qrCode);
    }

    public function updatedQrCode(): void
    {
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

    private function processQrCode(string $qrCode): void
    {
        try {
            // Find teacher by QR code
            $teacher = Teachers::where('qr_code', $qrCode)->firstOrFail();

            // Get current time info
            $now = now();
            $currentHour = $this->getCurrentHour();
            $dayOfWeek = $now->dayOfWeek;

            // Check if teacher has schedule today
            $schedules = WeeklySchedules::where('teacher_id', $teacher->id)
                ->where('day_of_week', $dayOfWeek)
                ->get();

            if ($schedules->isEmpty()) {
                $this->teacherData = [
                    'name' => $teacher->name,
                    'nip' => $teacher->nip,
                    'photo' => $teacher->photo,
                    'status' => 'no_schedule',
                    'message' => 'Tidak memiliki jadwal mengajar hari ini',
                ];
                $this->messageType = 'warning';
                $this->message = '⚠️ Guru ' . $teacher->name . ' tidak memiliki jadwal mengajar hari ini';
                $this->processing = false;
                return;
            }

            // Check if current time matches any schedule
            $hasCurrentHour = $schedules->contains('hour_number', $currentHour);

            if (!$hasCurrentHour) {
                $totalHours = $schedules->count();
                $this->teacherData = [
                    'name' => $teacher->name,
                    'nip' => $teacher->nip,
                    'photo' => $teacher->photo,
                    'status' => 'wrong_time',
                    'message' => "Jam Ke-{$currentHour} bukan jadwal mengajar (Total {$totalHours} jam hari ini)",
                ];
                $this->messageType = 'warning';
                $this->message = "⚠️ Guru {$teacher->name} tidak ada pelajaran jam ini";
                $this->processing = false;
                return;
            }

            // Check if already scanned this hour
            $alreadyScanned = LessonAttendances::where('teacher_id', $teacher->id)
                ->whereDate('date', today())
                ->where('hour_number', $currentHour)
                ->first();

            if ($alreadyScanned) {
                $this->teacherData = [
                    'name' => $teacher->name,
                    'nip' => $teacher->nip,
                    'photo' => $teacher->photo,
                    'status' => 'already_scanned',
                    'message' => 'Sudah scan di jam ini pukul ' . $alreadyScanned->scanned_at->format('H:i:s'),
                ];
                $this->messageType = 'info';
                $this->message = '⏱️ ' . $teacher->name . ' sudah melakukan scan';
                $this->processing = false;
                return;
            }

            // SUCCESS: Create attendance record
            LessonAttendances::create([
                'teacher_id' => $teacher->id,
                'date' => today(),
                'hour_number' => $currentHour,
                'scanned_at' => $now,
            ]);

            $timeRange = $this->getTimeRange($currentHour);
            $this->teacherData = [
                'name' => $teacher->name,
                'nip' => $teacher->nip,
                'photo' => $teacher->photo,
                'status' => 'success',
                'message' => "Jam Ke-{$currentHour} ({$timeRange})",
                'scanned_at' => $now->format('H:i:s'),
            ];
            $this->messageType = 'success';
            $this->message = '✅ Absen berhasil dicatat!';
            $this->scanCount++;

            // Reset processing flag untuk allow scan berikutnya
            $this->processing = false;

        } catch (\Exception $e) {
            $this->teacherData = null;
            $this->messageType = 'danger';
            $this->message = '❌ ' . $e->getMessage();

            // Reset processing flag even on error
            $this->processing = false;
        }
    }

    private function getCurrentHour(): int
    {
        $now = now();
        $lessonStarts = $this->getLessonStartTimes();

        foreach ($lessonStarts as $hour => $startTime) {
            $start = Carbon::createFromTimeString($startTime);
            $nextHour = $hour + 1;
            $end = isset($lessonStarts[$nextHour])
                ? Carbon::createFromTimeString($lessonStarts[$nextHour])
                : $start->copy()->addMinutes(35); // Last lesson 35 minutes

            if ($now >= $start && $now < $end) {
                return $hour;
            }
        }

        // If after last lesson, return last hour
        return count($lessonStarts);
    }

    private function getLessonStartTimes(): array
    {
        $durationMinutes = DurationSetting::query()->latest()->value('lesson_duration_minutes') ?? 45;

        $times = [];
        $currentTime = Carbon::createFromTimeString('08:00');

        for ($hour = 1; $hour <= 9; $hour++) {
            $times[$hour] = $currentTime->format('H:i');
            $end = $currentTime->copy()->addMinutes($durationMinutes);

            if ($hour == 3) {
                // After break
                $currentTime = Carbon::createFromTimeString('10:00');
            } elseif ($hour == 6) {
                // After dzuhur
                $currentTime = Carbon::createFromTimeString('12:25');
            } else {
                $currentTime = $end;
            }
        }

        return $times;
    }

    private function getTimeRange(int $hour): string
    {
        $lessonStarts = $this->getLessonStartTimes();

        if (!isset($lessonStarts[$hour])) {
            return 'Waktu tidak tersedia';
        }

        $start = Carbon::createFromTimeString($lessonStarts[$hour]);
        $nextHour = $hour + 1;
        $durationMinutes = DurationSetting::query()->latest()->value('lesson_duration_minutes') ?? 45;
        $end = isset($lessonStarts[$nextHour])
            ? Carbon::createFromTimeString($lessonStarts[$nextHour])
            : $start->copy()->addMinutes($durationMinutes);

        return $start->format('H:i') . ' - ' . $end->format('H:i');
    }

    #[\Livewire\Attributes\Layout('components.layouts.blank')]
    public function render()
    {
        return view('livewire.scanner-page');
    }
}
