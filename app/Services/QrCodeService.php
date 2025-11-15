<?php

namespace App\Services;

use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Illuminate\Support\Facades\Storage;

class QrCodeService
{
    /**
     * Generate QR Code sebagai PNG (high quality)
     */
    public static function generateImage(string $qrCode, int $size = 300): string
    {
        try {
            // Create QR code
            $qrCode = new QrCode($qrCode);
            $qrCode->setSize($size);
            $qrCode->setMargin(10);

            // Write PNG
            $writer = new PngWriter();
            $result = $writer->write($qrCode);

            // Return PNG data
            return $result->getString();
        } catch (\Exception $e) {
            // Fallback: return error image
            return static::generateErrorPng($size);
        }
    }

    /**
     * Generate error PNG as fallback
     */
    private static function generateErrorPng(int $size): string
    {
        $image = imagecreatetruecolor($size, $size);
        $white = imagecolorallocate($image, 255, 255, 255);
        $red = imagecolorallocate($image, 255, 0, 0);

        imagefill($image, 0, 0, $white);
        imagestring($image, 5, 10, $size / 2, 'Error generating QR', $red);

        ob_start();
        imagepng($image);
        $pngData = ob_get_clean();
        imagedestroy($image);

        return $pngData;
    }

    /**
     * Save QR to storage
     */
    public static function saveToStorage(string $qrCode, string $filename = null): string
    {
        $filename = $filename ?? $qrCode . '.png';
        $path = 'qr-codes/' . $filename;

        $data = static::generateImage($qrCode);
        Storage::disk('public')->put($path, $data);

        return $path;
    }
}

