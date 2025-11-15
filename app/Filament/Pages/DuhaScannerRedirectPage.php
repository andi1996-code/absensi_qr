<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

class DuhaScannerRedirectPage extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-camera';

    protected static ?string $navigationLabel = 'Scanner Masuk - Pulang';

    protected static ?string $navigationGroup = 'Attendance';

    protected static ?int $navigationSort = 2;

    protected static string $view = 'filament.pages.duha-scanner-redirect';

    protected static ?string $title = 'Scanner Dhuha';

    public function mount()
    {
        // Redirect ke halaman fullscreen scanner
        return redirect('/duha-scanner');
    }
}
