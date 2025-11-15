<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SalaryGenerationResource\Pages;
use App\Models\Teachers;
use App\Models\Salaries;
use App\Services\SalaryCalculationService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;

class SalaryGenerationResource extends Resource
{
    protected static ?string $model = Salaries::class;

    protected static ?string $navigationIcon = 'heroicon-o-currency-dollar';

    protected static ?string $navigationLabel = 'Generate Gaji';

    protected static ?string $modelLabel = 'Generate Gaji';

    protected static ?string $pluralModelLabel = 'Generate Gaji';

    protected static ?string $navigationGroup = 'Keuangan';

    protected static ?int $navigationSort = 1;

    protected static bool $shouldRegisterNavigation = false;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Pilih Guru & Periode')
                    ->schema([
                        Forms\Components\Select::make('teacher_id')
                            ->label('Pilih Guru')
                            ->options(Teachers::where('is_active', true)->pluck('name', 'id'))
                            ->required()
                            ->searchable()
                            ->live()
                            ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                self::updateSalaryCalculation($state, $get, $set);
                            }),

                        Forms\Components\Select::make('month')
                            ->label('Bulan')
                            ->options([
                                1 => 'Januari',
                                2 => 'Februari',
                                3 => 'Maret',
                                4 => 'April',
                                5 => 'Mei',
                                6 => 'Juni',
                                7 => 'Juli',
                                8 => 'Agustus',
                                9 => 'September',
                                10 => 'Oktober',
                                11 => 'November',
                                12 => 'Desember',
                            ])
                            ->default(now()->month)
                            ->required()
                            ->live()
                            ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                self::updateSalaryCalculation($get('teacher_id'), $get, $set);
                            }),

                        Forms\Components\TextInput::make('year')
                            ->label('Tahun')
                            ->default(now()->year)
                            ->required()
                            ->numeric()
                            ->minValue(2020)
                            ->maxValue(2030)
                            ->live()
                            ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                self::updateSalaryCalculation($get('teacher_id'), $get, $set);
                            }),
                    ])->columns(3),

                Forms\Components\Section::make('Perhitungan Gaji')
                    ->schema([
                        Forms\Components\Placeholder::make('scheduled_hours')
                            ->label('Total Jam Terjadwal')
                            ->content(fn ($get) => $get('total_scheduled_hours') ? $get('total_scheduled_hours') . ' jam' : '-'),

                        Forms\Components\Placeholder::make('attended_hours')
                            ->label('Jam Hadir')
                            ->content(fn ($get) => $get('attended_hours') ? $get('attended_hours') . ' jam' : '-'),

                        Forms\Components\Placeholder::make('absent_hours')
                            ->label('Jam Tidak Hadir')
                            ->content(fn ($get) => $get('absent_hours') ? $get('absent_hours') . ' jam' : '-'),

                        Forms\Components\Placeholder::make('base_salary')
                            ->label('Gaji Pokok')
                            ->content(fn ($get) => $get('base_salary') ? 'Rp ' . number_format($get('base_salary'), 0, ',', '.') : '-'),

                        Forms\Components\Placeholder::make('position_allowance')
                            ->label('Tunjangan Jabatan')
                            ->content(fn ($get) => $get('position_allowance') ? 'Rp ' . number_format($get('position_allowance'), 0, ',', '.') : '-'),
                    ])->columns(2)->visible(fn ($get) => $get('teacher_id') !== null),

                Forms\Components\Section::make('Tambahan Gaji')
                    ->schema([
                        Forms\Components\TextInput::make('additional_amount')
                            ->label('Tambahan Gaji (Rp)')
                            ->numeric()
                            ->prefix('Rp')
                            ->default(0)
                            ->minValue(0)
                            ->live()
                            ->helperText('Masukkan jumlah tambahan gaji jika ada'),

                        Forms\Components\Textarea::make('additional_notes')
                            ->label('Keterangan Tambahan')
                            ->rows(3)
                            ->helperText('Jelaskan untuk apa tambahan gaji ini (mis: bonus, lembur, dll)'),
                    ])->columns(1),

                Forms\Components\Section::make('Ringkasan')
                    ->schema([
                        Forms\Components\Placeholder::make('total_amount')
                            ->label('Total Gaji')
                            ->content(function ($get) {
                                $baseSalary = $get('base_salary') ?? 0;
                                $positionAllowance = $get('position_allowance') ?? 0;
                                $additional = $get('additional_amount') ?? 0;
                                $grandTotal = $baseSalary + $positionAllowance + $additional;
                                return $grandTotal > 0 ? 'Rp ' . number_format($grandTotal, 0, ',', '.') : '-';
                            }),
                    ])->visible(fn ($get) => $get('teacher_id') !== null),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('teacher.name')
                    ->label('Guru')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('month')
                    ->label('Bulan')
                    ->formatStateUsing(fn ($state) => [
                        1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
                        5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
                        9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
                    ][$state] ?? $state)
                    ->sortable(),

                Tables\Columns\TextColumn::make('year')
                    ->label('Tahun')
                    ->sortable(),

                Tables\Columns\TextColumn::make('total_amount')
                    ->label('Gaji Pokok')
                    ->money('IDR')
                    ->sortable(),

                Tables\Columns\TextColumn::make('additional_amount')
                    ->label('Tambahan')
                    ->money('IDR')
                    ->placeholder('Rp 0')
                    ->sortable(),

                Tables\Columns\TextColumn::make('additional_notes')
                    ->label('Keterangan')
                    ->limit(40)
                    ->tooltip(fn ($record) => $record->additional_notes)
                    ->placeholder('-'),

                Tables\Columns\TextColumn::make('grand_total')
                    ->label('Total Gaji')
                    ->money('IDR')
                    ->sortable()
                    ->getStateUsing(fn ($record) => $record->total_amount + $record->additional_amount)
                    ->weight('bold'),

                Tables\Columns\IconColumn::make('is_paid')
                    ->label('Dibayar')
                    ->boolean()
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('month')
                    ->label('Bulan')
                    ->options([
                        1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
                        5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
                        9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
                    ]),

                Tables\Filters\SelectFilter::make('year')
                    ->label('Tahun'),

                Tables\Filters\SelectFilter::make('teacher')
                    ->relationship('teacher', 'name')
                    ->label('Guru'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
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
            'index' => Pages\ListSalaryGenerations::route('/'),
            'create' => Pages\CreateSalaryGeneration::route('/create'),
            'edit' => Pages\EditSalaryGeneration::route('/{record}/edit'),
        ];
    }

    /**
     * Update salary calculation fields when teacher, month, or year changes
     */
    protected static function updateSalaryCalculation($teacherId, callable $get, callable $set): void
    {
        if (!$teacherId) {
            // Reset all calculation fields
            $set('total_scheduled_hours', null);
            $set('attended_hours', null);
            $set('absent_hours', null);
            $set('base_salary', null);
            $set('position_allowance', null);
            return;
        }

        $teacher = Teachers::find($teacherId);
        if (!$teacher) return;

        $month = $get('month') ?? now()->month;
        $year = $get('year') ?? now()->year;

        $service = new SalaryCalculationService();
        $calculation = $service->calculateBaseSalary($teacher, $year, $month);

        $set('total_scheduled_hours', $calculation['scheduled_hours']);
        $set('attended_hours', $calculation['attended_hours']);
        $set('absent_hours', $calculation['absent_hours']);
        $set('base_salary', $calculation['base_salary']);
        $set('position_allowance', $calculation['position_allowance']);
    }
}
