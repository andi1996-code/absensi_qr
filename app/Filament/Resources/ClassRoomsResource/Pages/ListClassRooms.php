<?php

namespace App\Filament\Resources\ClassRoomsResource\Pages;

use App\Filament\Resources\ClassRoomsResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Actions;

class ListClassRooms extends ListRecords
{
    protected static string $resource = ClassRoomsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Tambah Kelas'),
        ];
    }
}
