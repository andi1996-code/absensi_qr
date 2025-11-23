<?php

namespace App\Filament\Widgets;

use App\Models\Teachers;
use Filament\Widgets\Widget;

class TeachersListWidget extends Widget
{
    protected static ?string $heading = 'Daftar Guru';

    protected static string $view = 'filament.widgets.teachers-list';

    protected int | string | array $columnSpan = 'full';

    public function getTeachers()
    {
        return Teachers::where('is_active', true)
            ->orderBy('name')
            ->get();
    }
}
