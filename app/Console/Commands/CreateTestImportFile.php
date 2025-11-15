<?php

namespace App\Console\Commands;

use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Console\Command;

class CreateTestImportFile extends Command
{
    protected $signature = 'test:create-import-file';
    protected $description = 'Create a test Excel file for teacher import';

    public function handle()
    {
        $data = [
            ['Budi Santoso', '198501121990011001', 'budi@school.com', '081234567890', 'Kepala Sekolah'],
            ['Siti Nurhaliza', '198602152005012002', 'siti@school.com', '082345678901', 'Guru Kelas'],
            ['Ahmad Wijaya', '198703201995022003', 'ahmad@school.com', '083456789012', 'Guru Mapel'],
            ['Rina Kusuma', '198804252003032004', 'rina@school.com', '084567890123', 'Guru Kelas'],
            ['Dedi Gunawan', '198905301998042005', 'dedi@school.com', '085678901234', 'Guru Mapel'],
        ];

        $filePath = storage_path('app/public/data-guru-test.xlsx');

        // Create simple spreadsheet
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Headers
        $headers = ['Nama', 'NIP', 'Email', 'Telepon', 'Jabatan'];
        $sheet->fromArray([$headers], null, 'A1');

        // Data
        $row = 2;
        foreach ($data as $item) {
            $sheet->fromArray([$item], null, 'A' . $row);
            $row++;
        }

        // Style header
        $sheet->getStyle('A1:E1')->getFont()->setBold(true);
        $sheet->getStyle('A1:E1')->getFill()->setFillType('solid')->getStartColor()->setRGB('4472C4');
        $sheet->getStyle('A1:E1')->getFont()->getColor()->setRGB('FFFFFF');

        // Set column widths
        $sheet->getColumnDimension('A')->setWidth(20);
        $sheet->getColumnDimension('B')->setWidth(18);
        $sheet->getColumnDimension('C')->setWidth(20);
        $sheet->getColumnDimension('D')->setWidth(15);
        $sheet->getColumnDimension('E')->setWidth(15);

        // Save
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $writer->save($filePath);

        $this->info('✅ Test file created: ' . $filePath);
        $this->info('📂 File location: storage/app/public/data-guru-test.xlsx');
        $this->info('');
        $this->info('Data in file:');
        $this->table(['Nama', 'NIP', 'Email', 'Telepon', 'Jabatan'], $data);
    }
}
