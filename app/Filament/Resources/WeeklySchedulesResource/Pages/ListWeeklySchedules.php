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

    public function getHeader(): ?\Illuminate\Contracts\View\View
    {
        return view('filament.widgets.teachers-list-header', [
            'teachers' => \App\Models\Teachers::where('is_active', true)->orderBy('name')->get()
        ]);
    }

    public function getMaxContentWidth(): ?string
    {
        return 'full';
    }

    protected function getTableQuery(): Builder
    {
        return parent::getTableQuery()->with(['teacher', 'scheduleTime']);
    }
}
