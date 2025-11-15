<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DurationSettingResource\Pages;
use App\Filament\Resources\DurationSettingResource\RelationManagers;
use App\Models\DurationSetting;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class DurationSettingResource extends Resource
{
    protected static ?string $model = DurationSetting::class;

    protected static ?string $navigationIcon = 'heroicon-o-clock';

    protected static ?string $navigationLabel = 'Durasi Pelajaran';

    protected static ?string $navigationGroup = 'Konfigurasi';

    protected static ?int $navigationSort = 8;

    protected static bool $shouldRegisterNavigation = false;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('⏱️ Durasi Pelajaran')
                    ->description('Tentukan durasi setiap jam pelajaran. Sistem akan menghitung jam dengan memperhitungkan istirahat dan shalat dzuhur.')
                    ->schema([
                        Forms\Components\TextInput::make('lesson_duration_minutes')
                            ->label('Durasi Per Jam (menit)')
                            ->required()
                            ->numeric()
                            ->minValue(20)
                            ->maxValue(60)
                            ->default(45)
                            ->helperText('Rentang: 20-60 menit. Contoh: 35, 40, 45')
                            ->reactive()
                            ->afterStateUpdated(function ($state, $set) {
                                // Trigger preview update
                            }),

                        Forms\Components\Placeholder::make('schedule_preview')
                            ->label('Preview Jadwal Pelajaran')
                            ->content(function ($get) {
                                $duration = $get('lesson_duration_minutes') ?? 45;
                                return static::generateSchedulePreview($duration);
                            })
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('lesson_duration_minutes')
                    ->label('Durasi Per Jam')
                    ->formatStateUsing(fn($state) => $state . ' menit')
                    ->sortable(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Diperbarui')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->paginated(false);
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
            'index' => Pages\ListDurationSettings::route('/'),
            'create' => Pages\CreateDurationSetting::route('/create'),
            'edit' => Pages\EditDurationSetting::route('/{record}/edit'),
        ];
    }

    /**
     * Generate schedule preview with breaks
     */
    private static function generateSchedulePreview(int $duration): string
    {
        $schedule = [];
        $schedule[] = "**Jadwal Pelajaran (Durasi: {$duration} menit per jam)**";
        $schedule[] = "";

        $currentTime = \Carbon\Carbon::createFromTimeString('08:00');

        for ($hour = 1; $hour <= 9; $hour++) {
            $start = $currentTime->copy();
            $end = $start->copy()->addMinutes($duration);

            $schedule[] = "Jam Ke-{$hour}: {$start->format('H:i')} - {$end->format('H:i')}";

            // Add breaks after specific hours
            if ($hour == 3) {
                $schedule[] = "**Istirahat: 09:45 - 10:00**";
                $currentTime = \Carbon\Carbon::createFromTimeString('10:00');
            } elseif ($hour == 6) {
                $schedule[] = "**Shalat Dzuhur: 11:45 - 12:25**";
                $currentTime = \Carbon\Carbon::createFromTimeString('12:25');
            } else {
                $currentTime = $end;
            }
        }

        return implode("\n", $schedule);
    }
}
