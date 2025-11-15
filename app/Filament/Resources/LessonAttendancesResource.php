<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LessonAttendancesResource\Pages;
use App\Filament\Resources\LessonAttendancesResource\RelationManagers;
use App\Models\DurationSetting;
use App\Models\LessonAttendances;
use App\Models\ScheduleTime;
use App\Models\WeeklySchedules;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class LessonAttendancesResource extends Resource
{
    protected static ?string $model = LessonAttendances::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check';

    protected static ?string $navigationLabel = 'Absensi Pelajaran';

    protected static ?string $navigationGroup = 'Absensi';

    protected static ?int $navigationSort = 4;

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit($record): bool
    {
        return true;
    }

    public static function canDelete($record): bool
    {
        return false;
    }

    public static function canDeleteAny(): bool
    {
        return false;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('📝 Edit Absensi Pelajaran')
                    ->description('Edit waktu scan absensi jika ada kendala teknis')
                    ->schema([
                        Forms\Components\Placeholder::make('teacher_info')
                            ->label('Guru')
                            ->content(fn($get, $record) => $record ? $record->teacher->name . ' (' . $record->teacher->nip . ')' : 'Loading...'),

                        Forms\Components\Placeholder::make('lesson_info')
                            ->label('Jam Pelajaran')
                            ->content(fn($get, $record) => $record ? 'Jam Ke-' . $record->hour_number . ' (' . static::getTimeRange($record->hour_number) . ')' : 'Loading...'),

                        Forms\Components\Placeholder::make('date_info')
                            ->label('Tanggal')
                            ->content(fn($get, $record) => $record ? \Carbon\Carbon::parse($record->date)->format('d M Y') : 'Loading...'),

                        Forms\Components\DateTimePicker::make('scanned_at')
                            ->label('Waktu Scan')
                            ->default(fn($record) => $record ? $record->scanned_at : now())
                            ->required()
                            ->helperText('Waktu ketika absensi dicatat. Bisa diubah jika ada kendala teknis seperti server error.')
                            ->columnSpanFull(),

                        Forms\Components\Select::make('status')
                            ->label('Status Absensi')
                            ->options([
                                'present' => 'Hadir',
                                'absent' => 'Alpha',
                            ])
                            ->default('present')
                            ->required()
                            ->helperText('Status kehadiran: Hadir atau Alpha'),
                    ])
                    ->columns(1)
                    ->visible(fn($context) => $context === 'edit'),

                // Hidden fields for create form (scan)
                Forms\Components\Section::make(' Scan QR Code Guru')
                    ->description('Arahkan kode QR ke scanner')
                    ->schema([
                        Forms\Components\TextInput::make('qr_code')
                            ->label('Scan QR Code')
                            ->placeholder('Scan QR code di sini...')
                            ->autofocus()
                            ->required()
                            ->reactive()
                            ->afterStateUpdated(function ($state, $set) {
                                if ($state) {
                                    static::processQrScan($state, $set);
                                }
                            })
                            ->helperText('QR code akan otomatis diproses')
                            ->columnSpanFull(),

                        Forms\Components\Placeholder::make('teacher_name')
                            ->label('Nama Guru')
                            ->content(fn($get) => $get('teacher_id')
                                ? \App\Models\Teachers::find($get('teacher_id'))?->name
                                : '⏳ Menunggu scan...'),

                        Forms\Components\Placeholder::make('schedule_info')
                            ->label('Status Jadwal')
                            ->content(fn($get) => static::getScheduleInfoFromScan($get('teacher_id')))
                            ->columnSpanFull(),

                        Forms\Components\DateTimePicker::make('scanned_at')
                            ->label('Waktu Scan')
                            ->default(fn() => now())
                            ->required()
                            ->helperText('Waktu ketika absensi dicatat. Bisa diubah jika ada kendala teknis.')
                            ->columnSpanFull(),
                    ])
                    ->columns(1)
                    ->hidden(fn($context) => $context === 'edit'),

                // Hidden fields
                Forms\Components\Hidden::make('teacher_id'),
                Forms\Components\Hidden::make('date')
                    ->default(fn() => now()->toDateString()),
                Forms\Components\Hidden::make('hour_number')
                    ->default(fn() => static::getCurrentHour()),
            ]);
    }

    /**
     * Process QR scan - extract teacher_id dari QR code
     * QR format: QR-XXXXXXXXXX
     * Cari guru dengan qr_code ini, lalu validasi jadwal
     */
    private static function processQrScan($qrCode, $set): void
    {
        try {
            // Find guru by QR code
            $teacher = \App\Models\Teachers::where('qr_code', $qrCode)->firstOrFail();
            $set('teacher_id', $teacher->id);

            // Get current hour number
            $currentHour = static::getCurrentHour();
            $set('hour_number', $currentHour);

            // Validasi: Apakah guru punya jadwal jam ini?
            $dayOfWeek = now()->dayOfWeek;
            $hasSchedule = WeeklySchedules::where('teacher_id', $teacher->id)
                ->where('day_of_week', $dayOfWeek)
                ->where('hour_number', $currentHour)
                ->exists();

            if (!$hasSchedule) {
                \Filament\Notifications\Notification::make()
                    ->warning()
                    ->title('Jadwal Tidak Cocok')
                    ->body($teacher->name . ' tidak memiliki jadwal di jam ini')
                    ->send();
                $set('teacher_id', null);
                return;
            }

            // Check duplikat scan
            $alreadyScanned = LessonAttendances::where('teacher_id', $teacher->id)
                ->whereDate('date', now())
                ->where('hour_number', $currentHour)
                ->exists();

            if ($alreadyScanned) {
                \Filament\Notifications\Notification::make()
                    ->warning()
                    ->title('Sudah Scan')
                    ->body($teacher->name . ' sudah scan jam ini hari ini')
                    ->send();
                $set('teacher_id', null);
                return;
            }

            // Jadwal cocok - auto save
            \Filament\Notifications\Notification::make()
                ->success()
                ->title('✅ Scan Berhasil')
                ->body($teacher->name . ' - Jam Ke-' . $currentHour)
                ->send();

            // Auto create record
            LessonAttendances::create([
                'teacher_id' => $teacher->id,
                'date' => now()->toDateString(),
                'hour_number' => $currentHour,
                'scanned_at' => now(),
            ]);

            // Reset form untuk scan berikutnya
            $set('qr_code', '');
            $set('teacher_id', null);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            \Filament\Notifications\Notification::make()
                ->danger()
                ->title('❌ QR Tidak Valid')
                ->body('QR code tidak ditemukan di database')
                ->send();
        } catch (\Exception $e) {
            \Filament\Notifications\Notification::make()
                ->danger()
                ->title('❌ Error')
                ->body($e->getMessage())
                ->send();
        }
    }

    /**
     * Get current jam ke (1-9) based on time, considering breaks
     */
    private static function getCurrentHour(): int
    {
        $now = now()->format('H:i:s');
        $scheduleTime = ScheduleTime::where('is_lesson', true)
            ->where('start_time', '<=', $now)
            ->where('end_time', '>', $now)
            ->first();

        return $scheduleTime ? $scheduleTime->hour_number : 0;
    }

    /**
     * Get lesson start times array
     */
    private static function getLessonStartTimes(): array
    {
        // Deprecated: now using ScheduleTime model
        return [];
    }

    /**
     * Get schedule info dari scan
     */
    private static function getScheduleInfoFromScan(?int $teacherId): string
    {
        if (!$teacherId) {
            return '⏳ Menunggu scan QR code guru...';
        }

        $teacher = \App\Models\Teachers::find($teacherId);
        if (!$teacher) {
            return '❌ Guru tidak ditemukan';
        }

        $dayOfWeek = now()->dayOfWeek;
        $currentHour = static::getCurrentHour();
        $dayName = now()->format('l');

        $schedules = WeeklySchedules::where('teacher_id', $teacherId)
            ->where('day_of_week', $dayOfWeek)
            ->get();

        if ($schedules->isEmpty()) {
            return "❌ {$teacher->name} tidak punya jadwal pada {$dayName}";
        }

        // Check if jam ini ada jadwal
        $hasCurrentHour = $schedules->contains('hour_number', $currentHour);

        if (!$hasCurrentHour) {
            $totalHours = $schedules->count();
            return "❌ {$teacher->name} tidak ada pelajaran jam ke-{$currentHour}, tapi punya {$totalHours} jam hari ini";
        }

        $timeRange = static::getTimeRange($currentHour);
        return "✅ {$teacher->name} - Jam Ke-{$currentHour} ({$timeRange})";
    }

    /**
     * Get time range dari jam ke
     */
    private static function getTimeRange(int $hour): string
    {
        $scheduleTime = ScheduleTime::where('hour_number', $hour)->first();

        if (!$scheduleTime) {
            return 'Waktu tidak tersedia';
        }

        return $scheduleTime->formatted_start_time . ' - ' . $scheduleTime->formatted_end_time;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('teacher.name')
                    ->label('Guru')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('date')
                    ->label('Tanggal')
                    ->date('d M Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('hour_number')
                    ->label('Jam Ke')
                    ->formatStateUsing(fn($state) => 'Jam Ke-' . $state)
                    ->sortable(),

                Tables\Columns\TextColumn::make('scanned_at')
                    ->label('Waktu Scan')
                    ->dateTime('H:i:s')
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y H:i')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->formatStateUsing(fn($state) => $state === 'present' ? 'Hadir' : 'Alpha')
                    ->badge()
                    ->color(fn($state) => $state === 'present' ? 'success' : 'danger')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('teacher')
                    ->relationship('teacher', 'name')
                    ->label('Filter Guru'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('scanned_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLessonAttendances::route('/'),
            'create' => Pages\CreateLessonAttendances::route('/create'),
            'edit' => Pages\EditLessonAttendances::route('/{record}/edit'),
        ];
    }
}
