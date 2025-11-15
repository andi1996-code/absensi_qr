<?php

namespace App\Imports;

use App\Models\Teachers;
use App\Services\QrCodeService;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;

class TeachersImport implements ToModel, WithHeadingRow, WithValidation, SkipsEmptyRows
{
    protected $rowCount = 0;

    /**
     * @param array $row
     *
     * @return Teachers|null
     */
    public function model(array $row)
    {
        $this->rowCount++;

        // Cast values to string - NIP bisa numeric
        // Sanitize input - case-insensitive key matching
        $name = trim((string)($row['nama'] ?? $row['name'] ?? $row['Nama'] ?? ''));
        $nip = $row['nip'] ?? $row['NIP'] ?? null; // Keep numeric if numeric
        $email = trim((string)($row['email'] ?? $row['Email'] ?? ''));
        $phone = trim((string)($row['telepon'] ?? $row['phone'] ?? $row['Telepon'] ?? ''));
        $position = trim((string)($row['jabatan'] ?? $row['position'] ?? $row['Jabatan'] ?? ''));

        // Skip if name is empty
        if (empty($name)) {
            return null;
        }

        // Generate QR code automatically
        $qrCode = $this->generateUniqueQrCode($name);

        // Create or update teacher
        $teacher = Teachers::updateOrCreate(
            ['qr_code' => $qrCode],
            [
                'name' => $name,
                'nip' => $nip !== null && $nip !== '0' && $nip !== 0 ? (string)$nip : null,
                'qr_code' => $qrCode,
                'email' => $email && $email !== '0' ? $email : null,
                'phone' => $phone && $phone !== '0' ? $phone : null,
                'position' => $position && $position !== '0' ? $position : null,
                'photo_path' => null,
                'is_active' => true,
            ]
        );

        return $teacher;
    }

    /**
     * Generate unique QR code for teacher
     */
    private function generateUniqueQrCode(string $name): string
    {
        $baseQr = 'QR_GURU_' . strtoupper(str_replace(' ', '_', $name));
        $qrCode = $baseQr;
        $counter = 1;

        while (Teachers::where('qr_code', $qrCode)->exists()) {
            $qrCode = $baseQr . '_' . $counter;
            $counter++;
        }

        return $qrCode;
    }

    /**
     * Validation rules - only validate if name exists
     */
    public function rules(): array
    {
        return [
            // Only validate email if provided - other fields nullable
            'email' => 'nullable|email|max:255',
        ];
    }

    /**
     * Custom validation messages
     */
    public function customValidationMessages()
    {
        return [
            'email.email' => 'Format email tidak valid',
            'email.max' => 'Email maksimal 255 karakter',
            'phone.max' => 'Nomor telepon maksimal 255 karakter',
            'nip.max' => 'NIP maksimal 255 karakter',
        ];
    }
}
