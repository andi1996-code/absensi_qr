<?php

namespace App\Filament\Resources\LessonAttendancesResource\Pages;

use App\Filament\Resources\LessonAttendancesResource;
use Filament\Resources\Pages\CreateRecord;

class CreateLessonAttendances extends CreateRecord
{
    protected static string $resource = LessonAttendancesResource::class;

    // All validation logic is now handled in LessonAttendancesResource::processQrScan()
}
