<?php

namespace App\Filament\Resources\WeeklySchedulesResource\Pages;

use App\Filament\Resources\WeeklySchedulesResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListWeeklySchedules extends ListRecords
{
    protected static string $resource = WeeklySchedulesResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('schedule-builder')
                ->label('📊 Lihat Tabel Grid')
                ->url(route('schedule-builder'))
                ->button()
                ->icon('heroicon-o-table-cells'),

            Actions\CreateAction::make()
                ->label('Tambah Jadwal'),
        ];
    }

    protected function getTableQuery(): Builder
    {
        return parent::getTableQuery()->with(['teacher', 'scheduleTime']);
    }
}
