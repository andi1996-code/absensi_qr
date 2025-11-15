<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

class ScannerPage extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-camera';
    protected static ?string $navigationLabel = 'Scanner Mengajar';
    protected static ?string $navigationGroup = 'Attendance';
    protected static string $view = 'filament.pages.scanner-page';
    protected static ?int $navigationSort = 1;

    // Override URL slug
    public static function getSlug(): string
    {
        return 'scanner';
    }

    // Show in navigation
    protected static bool $shouldRegisterNavigation = true;

    public function mount(): void
    {
        // Redirect ke standalone scanner page dengan URL dinamis
        $url = route('scanner');
        redirect($url);
    }
}
