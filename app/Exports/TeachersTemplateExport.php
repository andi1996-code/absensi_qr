<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class TeachersTemplateExport implements FromArray, WithHeadings, WithStyles
{
    /**
     * @return \Illuminate\Support\Collection
     */
    public function array(): array
    {
        // Return example data rows - NIP as number
        return [
            ['Budi Hartono', 198501121990011004, 'budi.hartono@school.com', '081234567890', 'Guru Kelas'],
            ['Siti Nrulkiza', 198602152005012333, 'siti.nrulkiza@school.com', '082345678901', 'Kepala Sekolah'],
            ['Ahmad Satya', 198703201995022444, 'ahmad.satya@school.com', '083456789012', 'Guru Mapel'],
        ];
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'Nama *',
            'NIP',
            'Email',
            'Telepon',
            'Jabatan',
        ];
    }

    /**
     * @param Worksheet $sheet
     * @return array
     */
    public function styles(Worksheet $sheet)
    {
        // Set column widths
        $sheet->getColumnDimension('A')->setWidth(25);
        $sheet->getColumnDimension('B')->setWidth(20);
        $sheet->getColumnDimension('C')->setWidth(30);
        $sheet->getColumnDimension('D')->setWidth(15);
        $sheet->getColumnDimension('E')->setWidth(20);

        // Set NIP column as number format
        $sheet->getStyle('B2:B4')->getNumberFormat()->setFormatCode('0');

        // Center align header
        return [
            1 => [
                'font' => [
                    'bold' => true,
                    'size' => 12,
                ],
                'fill' => [
                    'fillType' => 'solid',
                    'startColor' => [
                        'rgb' => '4472C4',
                    ],
                ],
                'font' => [
                    'bold' => true,
                    'color' => [
                        'rgb' => 'FFFFFF',
                    ],
                ],
            ],
        ];
    }
}
