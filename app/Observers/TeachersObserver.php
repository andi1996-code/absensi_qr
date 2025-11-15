<?php

namespace App\Observers;

use App\Models\Teachers;
use Illuminate\Support\Str;

class TeachersObserver
{
    /**
     * Handle the Teachers "creating" event.
     */
    public function creating(Teachers $teacher): void
    {
        // Auto-generate QR code jika kosong
        if (empty($teacher->qr_code)) {
            $teacher->qr_code = $this->generateUniqueQrCode();
        }
    }

    /**
     * Generate unique QR code
     */
    private function generateUniqueQrCode(): string
    {
        do {
            $qrCode = 'QR-' . strtoupper(Str::random(10));
        } while (Teachers::where('qr_code', $qrCode)->exists());

        return $qrCode;
    }
}
