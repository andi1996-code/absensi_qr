<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ScheduleTimeResource\Pages;
use App\Models\ScheduleTime;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ScheduleTimeResource extends Resource
{
    protected static ?string $model = ScheduleTime::class;

    protected static ?string $navigationIcon = 'heroicon-o-clock';

    protected static ?string $navigationLabel = 'Jadwal Jam Pelajaran';

    protected static ?string $navigationGroup = 'Master Data';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Jadwal Jam Pelajaran')
                    ->description('Atur waktu dan durasi jam pelajaran')
                    ->schema([
                        Forms\Components\TextInput::make('hour_number')
                            ->label('Nomor Slot')
                            ->numeric()
                            ->required()
                            ->unique(ignorable: fn ($record) => $record)
                            ->helperText('1-11 (untuk jam pelajaran dan istirahat/solat)'),

                        Forms\Components\TextInput::make('label')
                            ->label('Label')
                            ->placeholder('e.g., Jam ke 1, Istirahat, Solat Dzuhur')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\TimePicker::make('start_time')
                            ->label('Jam Mulai')
                            ->required()
                            ->format('H:i'),

                        Forms\Components\TimePicker::make('end_time')
                            ->label('Jam Selesai')
                            ->required()
                            ->format('H:i'),

                        Forms\Components\Toggle::make('is_lesson')
                            ->label('Jam Pelajaran?')
                            ->helperText('Centang jika ini adalah jam pelajaran (bukan istirahat/solat)')
                            ->default(true),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('hour_number')
                    ->label('Slot')
                    ->sortable(),

                Tables\Columns\TextColumn::make('label')
                    ->label('Label')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('formatted_start_time')
                    ->label('Mulai')
                    ->getStateUsing(fn ($record) => $record->formatted_start_time),

                Tables\Columns\TextColumn::make('formatted_end_time')
                    ->label('Selesai')
                    ->getStateUsing(fn ($record) => $record->formatted_end_time),

                Tables\Columns\TextColumn::make('duration_minutes')
                    ->label('Durasi (Menit)')
                    ->getStateUsing(fn ($record) => $record->duration_minutes),

                Tables\Columns\IconColumn::make('is_lesson')
                    ->label('Jam Pelajaran')
                    ->boolean(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_lesson')
                    ->label('Jam Pelajaran'),
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
            ->defaultSort('hour_number');
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
            'index' => Pages\ListScheduleTimes::route('/'),
            'create' => Pages\CreateScheduleTime::route('/create'),
            'edit' => Pages\EditScheduleTime::route('/{record}/edit'),
        ];
    }
}
