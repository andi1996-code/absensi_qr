<?php

namespace App\Livewire;

use App\Models\DuhaAttendances;
use App\Models\DepartureAttendances;
use App\Models\Teachers;
use Carbon\Carbon;
use Livewire\Component;

class DuhaScannerPage extends Component
{
    public string $qrCode = '';
    public ?array $teacherData = null;
    public ?string $message = null;
    public ?string $messageType = null;
    public int $scanCount = 0;
    public bool $processing = false;
    public string $scanMode = 'duha'; // 'duha' or 'departure'
    public int $departureCount = 0;

    public function mount(): void
    {
        $this->scanCount = DuhaAttendances::whereDate('date', today())->count();
        $this->departureCount = DepartureAttendances::whereDate('date', today())->count();
    }

    public function setScanMode(string $mode): void
    {
        $this->scanMode = $mode;
        $this->resetDisplay();
    }

    public function resetDisplay(): void
    {
        $this->teacherData = null;
        $this->message = null;
        $this->messageType = null;
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

            // Check if teacher is active
            if (!$teacher->is_active) {
                $this->teacherData = [
                    'name' => $teacher->name,
                    'nip' => $teacher->nip,
                    'photo' => $teacher->photo_path,
                    'status' => 'inactive',
                    'message' => 'Guru tidak aktif',
                ];
                $this->messageType = 'warning';
                $this->message = '⚠️ Guru ' . $teacher->name . ' tidak aktif';
                $this->processing = false;
                return;
            }

            // Process berdasarkan scan mode
            if ($this->scanMode === 'duha') {
                $this->processDuhaAttendance($teacher, $now);
            } else {
                $this->processDepartureAttendance($teacher, $now);
            }

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            // QR Code tidak ditemukan
            $this->teacherData = null;
            $this->messageType = 'danger';
            $this->message = '❌ QR Code TIDAK DITEMUKAN! Silakan periksa kembali atau hubungi admin.';

            // Reset processing flag even on error
            $this->processing = false;
        } catch (\Exception $e) {
            $this->teacherData = null;
            $this->messageType = 'danger';
            $this->message = '❌ Terjadi kesalahan: ' . $e->getMessage();

            // Reset processing flag even on error
            $this->processing = false;
        }
    }

    private function processDuhaAttendance($teacher, $now): void
    {
        // Validasi: Duha bisa scan mulai jam 6.00 pagi
        $duhaStartTime = $now->copy()->setTime(6, 0, 0);

        if ($now->lessThan($duhaStartTime)) {
            $this->teacherData = [
                'name' => $teacher->name,
                'nip' => $teacher->nip,
                'photo' => $teacher->photo_path,
                'status' => 'not_time',
                'message' => 'Absen Duha mulai jam 06:00',
            ];
            $this->messageType = 'warning';
            $this->message = '⏰ Absen Duha bisa dilakukan mulai jam 06:00. Waktu sekarang: ' . $now->format('H:i:s');
            $this->processing = false;
            return;
        }

        // Check if already scanned today
        $alreadyScanned = DuhaAttendances::where('teacher_id', $teacher->id)
            ->whereDate('date', today())
            ->first();

        if ($alreadyScanned) {
            $lateMinutes = $alreadyScanned->is_late;
            $lateStatus = $lateMinutes > 0 ? "⏰ TERLAMBAT {$lateMinutes} MENIT" : '✅ TEPAT WAKTU';
            $this->teacherData = [
                'name' => $teacher->name,
                'nip' => $teacher->nip,
                'photo' => $teacher->photo_path,
                'status' => 'already_scanned',
                'message' => 'Sudah scan Duha pukul ' . $alreadyScanned->scanned_at->format('H:i:s'),
                'late_status' => $lateStatus,
            ];
            $this->messageType = 'info';
            $this->message = '⏱️ ' . $teacher->name . ' sudah melakukan scan Duha';
            $this->processing = false;
            return;
        }

        // Hitung berapa menit terlambat (dari jam 08:00 - waktu absen max)
        $duhaMaxTime = $now->copy()->setTime(8, 0, 0); // Waktu absen maksimal: 08:00
        $lateMinutes = 0;

        if ($now->greaterThan($duhaMaxTime)) {
            // Terlambat - hitung selisih dalam detik kemudian convert ke menit
            $diffInSeconds = $now->diffInSeconds($duhaMaxTime);
            $lateMinutes = (int) ceil($diffInSeconds / 60);
        }

        // SUCCESS: Create duha attendance record (tetap dibuat walaupun terlambat)
        DuhaAttendances::create([
            'teacher_id' => $teacher->id,
            'date' => today(),
            'scanned_at' => $now,
            'is_late' => $lateMinutes,
        ]);

        $lateStatus = $lateMinutes > 0 ? "⏰ TERLAMBAT {$lateMinutes} MENIT" : '✅ TEPAT WAKTU';
        $this->teacherData = [
            'name' => $teacher->name,
            'nip' => $teacher->nip,
            'photo' => $teacher->photo_path,
            'status' => 'success',
            'message' => 'Absen Duha Berhasil',
            'scanned_at' => $now->format('H:i:s'),
            'late_status' => $lateStatus,
        ];
        $this->messageType = 'success';
        $this->message = 'Absen Duha berhasil dicatat!';
        $this->scanCount++;

        // Reset processing flag untuk allow scan berikutnya
        $this->processing = false;
    }

    private function processDepartureAttendance($teacher, $now): void
    {
        // Validasi: Guru harus sudah absen Duha terlebih dahulu
        $duhaAttendance = DuhaAttendances::where('teacher_id', $teacher->id)
            ->whereDate('date', today())
            ->first();

        if (!$duhaAttendance) {
            $this->teacherData = [
                'name' => $teacher->name,
                'nip' => $teacher->nip,
                'photo' => $teacher->photo_path,
                'status' => 'not_checked_in',
                'message' => 'Belum melakukan absen Duha',
            ];
            $this->messageType = 'danger';
            $this->message = '❌ ' . $teacher->name . ' belum melakukan absen Duha! Silakan scan untuk absen masuk terlebih dahulu.';
            $this->processing = false;
            return;
        }

        // Validasi: Pulang bisa scan minimal jam 14:10
        $departureMinTime = $now->copy()->setTime(14, 10, 0);

        if ($now->lessThan($departureMinTime)) {
            $this->teacherData = [
                'name' => $teacher->name,
                'nip' => $teacher->nip,
                'photo' => $teacher->photo_path,
                'status' => 'not_time',
                'message' => 'Absen Pulang mulai jam 14:10',
            ];
            $this->messageType = 'warning';
            $this->message = '⏰ Absen Pulang bisa dilakukan mulai jam 14:10. Waktu sekarang: ' . $now->format('H:i:s');
            $this->processing = false;
            return;
        }

        // Check if already scanned departure today
        $alreadyScanned = DepartureAttendances::where('teacher_id', $teacher->id)
            ->whereDate('date', today())
            ->first();

        if ($alreadyScanned) {
            $earlyMinutes = $alreadyScanned->is_late;
            $earlyStatus = $earlyMinutes > 0 ? "⚡ PULANG AWAL {$earlyMinutes} MENIT" : '✅ NORMAL';
            $this->teacherData = [
                'name' => $teacher->name,
                'nip' => $teacher->nip,
                'photo' => $teacher->photo_path,
                'status' => 'already_scanned',
                'message' => 'Sudah scan Pulang pukul ' . $alreadyScanned->scanned_at->format('H:i:s'),
                'late_status' => $earlyStatus,
            ];
            $this->messageType = 'info';
            $this->message = '⏱️ ' . $teacher->name . ' sudah melakukan scan Pulang';
            $this->processing = false;
            return;
        }

        // Check if departure is early (before 14:10 - waktu minimal pulang)
        $departureNormalTime = $now->copy()->setTime(14, 10, 0);
        $earlyMinutes = 0;

        if ($now->lessThan($departureNormalTime)) {
            // Pulang awal - hitung selisih dalam detik kemudian convert ke menit
            $diffInSeconds = $departureNormalTime->diffInSeconds($now);
            $earlyMinutes = (int) ceil($diffInSeconds / 60);
        }

        // SUCCESS: Create departure attendance record
        DepartureAttendances::create([
            'teacher_id' => $teacher->id,
            'date' => today(),
            'scanned_at' => $now,
            'is_late' => $earlyMinutes, // menit pulang awal (0 jika tidak pulang awal)
        ]);

        $earlyStatus = $earlyMinutes > 0 ? "⚡ PULANG AWAL {$earlyMinutes} MENIT" : '✅ NORMAL';
        $this->teacherData = [
            'name' => $teacher->name,
            'nip' => $teacher->nip,
            'photo' => $teacher->photo_path,
            'status' => 'success',
            'message' => 'Absen Pulang Berhasil',
            'scanned_at' => $now->format('H:i:s'),
            'late_status' => $earlyStatus,
        ];
        $this->messageType = 'success';
        $this->message = 'Absen Pulang berhasil dicatat!';
        $this->departureCount++;

        // Reset processing flag untuk allow scan berikutnya
        $this->processing = false;
    }

    #[\Livewire\Attributes\Layout('components.layouts.blank')]
    public function render()
    {
        return view('livewire.duha-scanner-page');
    }
}
