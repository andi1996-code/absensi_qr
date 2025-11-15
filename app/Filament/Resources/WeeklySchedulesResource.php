<?php

namespace App\Filament\Resources;

use App\Filament\Resources\WeeklySchedulesResource\Pages;
use App\Models\WeeklySchedules;
use App\Models\ScheduleTime;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class WeeklySchedulesResource extends Resource
{
    protected static ?string $model = WeeklySchedules::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar';

    protected static ?string $navigationLabel = 'Jadwal Mingguan';

    protected static ?string $navigationGroup = 'Master Data';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Jadwal Mingguan Guru')
                    ->description('Atur jam mengajar guru per minggu. Bisa menambah multiple jam dalam satu hari.')
                    ->schema([
                        Forms\Components\Select::make('teacher_id')
                            ->label('Pilih Guru')
                            ->relationship('teacher', 'name')
                            ->required()
                            ->searchable()
                            ->preload(),

                        Forms\Components\Repeater::make('schedules')
                            ->label('Daftar Jadwal')
                            ->schema([
                                Forms\Components\Select::make('day_of_week')
                                    ->label('Hari')
                                    ->options([
                                        1 => 'Senin',
                                        2 => 'Selasa',
                                        3 => 'Rabu',
                                        4 => 'Kamis',
                                        5 => 'Jumat',
                                        6 => 'Sabtu',
                                        7 => 'Minggu',
                                    ])
                                    ->required(),

                                Forms\Components\Select::make('schedule_time_id')
                                    ->label('Jam Pelajaran')
                                    ->options(function () {
                                        return ScheduleTime::where('is_lesson', true)
                                            ->get()
                                            ->mapWithKeys(fn ($schedule) => [
                                                $schedule->id => $schedule->label
                                            ]);
                                    })
                                    ->searchable()
                                    ->preload()
                                    ->required(),

                                Forms\Components\Hidden::make('hour_number')
                                    ->default(0),
                            ])
                            ->columns(2)
                            ->addActionLabel('+ Tambah Jam')
                            ->minItems(1)
                            ->collapsible()
                            ->visible(fn ($livewire) => $livewire instanceof \Filament\Resources\Pages\CreateRecord),
                    ])
                    ->columns(1),

                // Form untuk edit: tetap pakai single select
                Forms\Components\Section::make('Edit Jadwal')
                    ->description('Edit jadwal per jam')
                    ->schema([
                        Forms\Components\Select::make('day_of_week')
                            ->label('Hari')
                            ->options([
                                1 => 'Senin',
                                2 => 'Selasa',
                                3 => 'Rabu',
                                4 => 'Kamis',
                                5 => 'Jumat',
                                6 => 'Sabtu',
                                7 => 'Minggu',
                            ])
                            ->required(),

                        Forms\Components\Select::make('schedule_time_id')
                            ->label('Jam Pelajaran')
                            ->options(function () {
                                return ScheduleTime::where('is_lesson', true)
                                    ->get()
                                    ->mapWithKeys(fn ($schedule) => [
                                        $schedule->id => $schedule->label
                                    ]);
                            })
                            ->searchable()
                            ->preload()
                            ->required(),

                        Forms\Components\Hidden::make('hour_number')
                            ->default(0),
                    ])
                    ->columns(2)
                    ->visible(fn ($livewire) => ! ($livewire instanceof \Filament\Resources\Pages\CreateRecord)),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('teacher.name')
                    ->label('Guru')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->width('40%'),

                Tables\Columns\TextColumn::make('day_of_week')
                    ->label('Hari')
                    ->formatStateUsing(fn (int $state) => [
                        1 => 'Senin',
                        2 => 'Selasa',
                        3 => 'Rabu',
                        4 => 'Kamis',
                        5 => 'Jumat',
                        6 => 'Sabtu',
                        7 => 'Minggu',
                    ][$state] ?? 'Unknown')
                    ->sortable()
                    ->alignment('center')
                    ->width('25%'),

                Tables\Columns\TextColumn::make('scheduleTime.label')
                    ->label('Jam Pelajaran')
                    ->sortable()
                    ->badge()
                    ->color('info')
                    ->width('25%'),
            ])
            ->striped()
            ->filters([
                Tables\Filters\SelectFilter::make('teacher_id')
                    ->label('Filter Guru')
                    ->relationship('teacher', 'name')
                    ->searchable(),

                Tables\Filters\SelectFilter::make('day_of_week')
                    ->label('Filter Hari')
                    ->options([
                        1 => 'Senin',
                        2 => 'Selasa',
                        3 => 'Rabu',
                        4 => 'Kamis',
                        5 => 'Jumat',
                        6 => 'Sabtu',
                        7 => 'Minggu',
                    ]),
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
            ->defaultSort('teacher.name');
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
            'index' => Pages\ListWeeklySchedules::route('/'),
            'create' => Pages\CreateWeeklySchedules::route('/create'),
            'edit' => Pages\EditWeeklySchedules::route('/{record}/edit'),
        ];
    }
}

