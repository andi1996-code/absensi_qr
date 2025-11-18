<?php

namespace App\Filament\Resources\WeeklySchedulesResource\Pages;

use App\Filament\Resources\WeeklySchedulesResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Widgets\TeachersListWidget;

class ListWeeklySchedules extends ListRecords
{
    protected static string $resource = WeeklySchedulesResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Actions\Action::make('schedule-builder')
            //     ->label('📊 Lihat Tabel Grid')
            //     ->url(route('schedule-builder'))
            //     ->button()
            //     ->icon('heroicon-o-table-cells'),

            // Actions\CreateAction::make()
            //     ->label('Tambah Jadwal'),
        ];
    }

    public function getHeaderWidgets(): array
    {
        return [
            TeachersListWidget::class,
        ];
    }

    protected function getTableQuery(): Builder
    {
        return parent::getTableQuery()->with(['teacher', 'scheduleTime']);
    }
}
