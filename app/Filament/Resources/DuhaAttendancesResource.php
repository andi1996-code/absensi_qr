<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DuhaAttendancesResource\Pages;
use App\Filament\Resources\DuhaAttendancesResource\RelationManagers;
use App\Models\DuhaAttendances;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class DuhaAttendancesResource extends Resource
{
    protected static ?string $model = DuhaAttendances::class;

    protected static ?string $navigationIcon = 'heroicon-o-check-circle';

    protected static ?string $navigationLabel = 'Absensi Dhuha';

    protected static ?string $navigationGroup = 'Absensi';

    protected static ?int $navigationSort = 3;

    protected static bool $shouldRegisterNavigation = true;

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
                Forms\Components\Section::make('📝 Edit Absensi Dhuha')
                    ->description('Edit waktu scan absensi dhuha jika ada kendala teknis')
                    ->schema([
                        Forms\Components\Placeholder::make('teacher_info')
                            ->label('Guru')
                            ->content(fn($get, $record) => $record ? $record->teacher->name . ' (' . $record->teacher->nip . ')' : 'Loading...'),

                        Forms\Components\Placeholder::make('date_info')
                            ->label('Tanggal')
                            ->content(fn($get, $record) => $record ? \Carbon\Carbon::parse($record->date)->format('d M Y') : 'Loading...'),

                        Forms\Components\DateTimePicker::make('scanned_at')
                            ->label('Waktu Scan')
                            ->default(fn($record) => $record ? $record->scanned_at : now())
                            ->required()
                            ->helperText('Waktu ketika absensi dhuha dicatat. Bisa diubah jika ada kendala teknis seperti server error.')
                            ->columnSpanFull(),

                        Forms\Components\Hidden::make('is_late')
                            ->default(0),
                    ])
                    ->columns(1)
                    ->visible(fn($context) => $context === 'edit'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('teacher.name')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('scanned_at')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('is_late')
                    ->label('Terlambat (Menit)')
                    ->sortable()
                    ->formatStateUsing(function ($state) {
                        if ($state > 0) {
                            return "{$state} menit";
                        }
                        return 'Tepat waktu';
                    })
                    ->color(function ($state) {
                        return $state > 0 ? 'danger' : 'success';
                    }),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                // Tidak ada bulk actions - hanya view
            ]);
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
            'index' => Pages\ListDuhaAttendances::route('/'),
            'edit' => Pages\EditDuhaAttendances::route('/{record}/edit'),
        ];
    }
}
