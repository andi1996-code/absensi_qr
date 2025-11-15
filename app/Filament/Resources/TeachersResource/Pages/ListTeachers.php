<?php

namespace App\Filament\Resources\TeachersResource\Pages;

use App\Filament\Resources\TeachersResource;
use App\Imports\TeachersImport;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Maatwebsite\Excel\Facades\Excel;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Storage;

class ListTeachers extends ListRecords
{
    protected static string $resource = TeachersResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
            Actions\Action::make('import')
                ->label('Import Excel')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('success')
                ->form([
                    \Filament\Forms\Components\FileUpload::make('file')
                        ->label('Upload File Excel')
                        ->required()
                        ->acceptedFileTypes(['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'application/vnd.ms-excel'])
                        ->maxSize(10240)
                        ->helperText('Format: .xlsx atau .xls. Ukuran maksimal 10MB')
                        ->disk('public')
                        ->preserveFilenames(),
                    \Filament\Forms\Components\Placeholder::make('template_info')
                        ->label('')
                        ->content('📥 Silakan download template di bawah untuk format yang benar')
                        ->columnSpanFull(),
                ])
                ->action(function (array $data) {
                    try {
                        $fileData = $data['file'] ?? null;

                        if (!$fileData) {
                            throw new \Exception('File tidak ditemukan dari form');
                        }

                        // Handle both array and string responses
                        $fileName = null;
                        if (is_array($fileData)) {
                            $fileName = $fileData[0] ?? null;
                        } else {
                            $fileName = $fileData;
                        }

                        if (!$fileName) {
                            throw new \Exception('Nama file kosong');
                        }

                        // Try multiple paths
                        $possiblePaths = [
                            storage_path('app/public/' . $fileName),
                            storage_path('app/public') . '/' . $fileName,
                            public_path($fileName),
                            $fileName,
                        ];

                        $fullPath = null;
                        foreach ($possiblePaths as $path) {
                            if (file_exists($path)) {
                                $fullPath = $path;
                                break;
                            }
                        }

                        // If still not found, list files in public storage
                        if (!$fullPath) {
                            $publicDir = storage_path('app/public');
                            if (is_dir($publicDir)) {
                                $files = scandir($publicDir);

                                // Try to find any xlsx file
                                $xlsxFiles = array_filter($files, fn($f) => str_ends_with($f, '.xlsx'));
                                if (!empty($xlsxFiles)) {
                                    $fullPath = $publicDir . '/' . reset($xlsxFiles);
                                }
                            }
                        }

                        if (!$fullPath || !file_exists($fullPath)) {
                            throw new \Exception('File tidak ditemukan. Cek logs untuk detail.');
                        }

                        // Import data
                        $import = new TeachersImport();
                        Excel::import($import, $fullPath);

                        // Clean up
                        try {
                            if (file_exists($fullPath)) {
                                @unlink($fullPath);
                            }
                        } catch (\Exception $e) {
                            // Silently ignore cleanup errors
                        }

                        Notification::make()
                            ->title('✅ Import Berhasil!')
                            ->body('Data guru berhasil diimport dari Excel.')
                            ->success()
                            ->send();

                        $this->redirect($this->getResource()::getUrl('index'));
                    } catch (\Exception $e) {
                        Notification::make()
                            ->title('❌ Import Gagal!')
                            ->body('Error: ' . $e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),
            Actions\Action::make('download_template')
                ->label('Template Excel')
                ->icon('heroicon-o-document-arrow-down')
                ->color('info')
                ->url(fn () => route('teachers.template'))
                ->openUrlInNewTab(),
        ];
    }
}

