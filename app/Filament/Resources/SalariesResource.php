<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SalariesResource\Pages;
use App\Filament\Resources\SalariesResource\RelationManagers;
use App\Models\Salaries;
use App\Services\SalaryCalculationService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class SalariesResource extends Resource
{
    protected static ?string $model = Salaries::class;

    protected static ?string $navigationIcon = 'heroicon-o-currency-dollar';

    protected static ?string $navigationLabel = 'Gaji';

    protected static ?string $navigationGroup = 'Keuangan';

    protected static ?int $navigationSort = 6;
    public static function canCreate(): bool
    {
        return false;
    }





    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('📋 Data Gaji')
                    ->schema([
                        Forms\Components\Select::make('teacher_id')
                            ->relationship('teacher', 'name')
                            ->required()
                            ->disabled(fn($record) => $record !== null),

                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('year')
                                    ->label('Tahun')
                                    ->required()
                                    ->numeric()
                                    ->disabled(fn($record) => $record !== null),
                                Forms\Components\TextInput::make('month')
                                    ->label('Bulan')
                                    ->required()
                                    ->numeric()
                                    ->disabled(fn($record) => $record !== null),
                            ]),
                    ]),

                Forms\Components\Section::make('⏰ Jam Kerja')
                    ->schema([
                        Forms\Components\Grid::make(3)
                            ->schema([
                                Forms\Components\TextInput::make('total_scheduled_hours')
                                    ->label('Total Jam Dijadwalkan')
                                    ->required()
                                    ->numeric()
                                    ->disabled(),
                                Forms\Components\TextInput::make('attended_hours')
                                    ->label('Jam Hadir (@ Rp 7.500)')
                                    ->required()
                                    ->numeric()
                                    ->disabled(),
                                Forms\Components\TextInput::make('absent_hours')
                                    ->label('Jam Tidak Hadir (@ Rp 3.500)')
                                    ->required()
                                    ->numeric()
                                    ->disabled(),
                            ]),
                    ]),

                Forms\Components\Section::make('💰 Gaji')
                    ->schema([
                        Forms\Components\TextInput::make('total_amount')
                            ->label('Total Gaji')
                            ->required()
                            ->numeric()
                            ->disabled()
                            ->prefix('Rp ')
                            ->formatStateUsing(fn($state) => number_format($state, 0, ',', '.'))
                            ->dehydrateStateUsing(fn($state) => (int) str_replace(['.', ','], '', $state)),

                        Forms\Components\TextInput::make('additional_amount')
                            ->label('Tambahan Gaji')
                            ->numeric()
                            ->minValue(0)
                            ->prefix('Rp ')
                            ->default(0)
                            ->formatStateUsing(fn($state) => $state ? number_format($state, 0, ',', '.') : '0')
                            ->dehydrateStateUsing(fn($state) => $state ? (int) str_replace(['.', ','], '', $state) : 0),

                        Forms\Components\TextInput::make('additional_notes')
                            ->label('Keterangan Tambahan')
                            ->placeholder('Contoh: Bonus kehadiran, Tunjangan khusus'),

                        Forms\Components\TextInput::make('deductions_amount')
                            ->label('Potongan Gaji')
                            ->numeric()
                            ->minValue(0)
                            ->prefix('Rp ')
                            ->default(0)
                            ->formatStateUsing(fn($state) => $state ? number_format($state, 0, ',', '.') : '0')
                            ->dehydrateStateUsing(fn($state) => $state ? (int) str_replace(['.', ','], '', $state) : 0),

                        Forms\Components\TextInput::make('deductions_notes')
                            ->label('Keterangan Potongan')
                            ->placeholder('Contoh: BPJS, Pajak, Denda'),

                        Forms\Components\Toggle::make('is_paid')
                            ->label('Sudah Dibayar')
                            ->required(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('teacher.name')
                    ->label('👨‍🏫 Guru')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->grow(),

                Tables\Columns\TextColumn::make('month')
                    ->label('📅 Periode')
                    ->formatStateUsing(fn($record) => static::getMonthYear($record))
                    ->sortable()
                    ->alignment('center'),

                Tables\Columns\TextColumn::make('total_amount')
                    ->label('💵 Gaji Pokok')
                    ->money('IDR', locale: 'id_ID')
                    ->sortable()
                    ->alignment('right')
                    ->color('success'),

                Tables\Columns\TextColumn::make('additional_amount')
                    ->label('➕ Tambahan')
                    ->money('IDR', locale: 'id_ID')
                    ->sortable()
                    ->alignment('right')
                    ->color('info')
                    ->placeholder('Rp 0')
                    ->default('0'),

                Tables\Columns\TextColumn::make('additional_notes')
                    ->label('📝 Keterangan')
                    ->limit(30)
                    ->tooltip(fn ($record) => $record->additional_notes ?? '-')
                    ->placeholder('—')
                    ->wrap(),

                Tables\Columns\TextColumn::make('grand_total')
                    ->label('💰 TOTAL')
                    ->money('IDR', locale: 'id_ID')
                    ->sortable()
                    ->getStateUsing(fn ($record) => $record->total_amount + $record->additional_amount)
                    ->weight('bold')
                    ->alignment('right')
                    ->color('primary')
                    ->size('lg'),

                Tables\Columns\TextColumn::make('deductions_amount')
                    ->label('➖ Potongan')
                    ->money('IDR', locale: 'id_ID')
                    ->sortable()
                    ->alignment('right')
                    ->color('danger')
                    ->placeholder('Rp 0')
                    ->default('0'),

                Tables\Columns\TextColumn::make('deductions_notes')
                    ->label('📌 Ket. Potongan')
                    ->limit(30)
                    ->tooltip(fn ($record) => $record->deductions_notes ?? '-')
                    ->placeholder('—')
                    ->wrap(),

                Tables\Columns\TextColumn::make('net_salary')
                    ->label('💵 GAJI BERSIH')
                    ->money('IDR', locale: 'id_ID')
                    ->sortable()
                    ->getStateUsing(fn ($record) => ($record->total_amount + $record->additional_amount) - $record->deductions_amount)
                    ->weight('bold')
                    ->alignment('right')
                    ->color('success')
                    ->size('lg'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('month')
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
                    ->query(function (Builder $query, array $data) {
                        return $query->when(
                            $data['value'] ?? null,
                            fn(Builder $q) => $q->where('month', $data['value'])
                        );
                    }),
                Tables\Filters\SelectFilter::make('year')
                    ->query(function (Builder $query, array $data) {
                        return $query->when(
                            $data['value'] ?? null,
                            fn(Builder $q) => $q->where('year', $data['value'])
                        );
                    }),
                Tables\Filters\TernaryFilter::make('is_paid')
                    ->label('Status Pembayaran'),
            ])
            ->actions([
                Tables\Actions\Action::make('print')
                    ->label('📄 Cetak Slip')
                    ->color('info')
                    ->icon('heroicon-o-document-text')
                    ->url(fn(Salaries $record) => route('salaries.slip', $record->id))
                    ->openUrlInNewTab(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    /**
     * Format month and year
     */
    private static function getMonthYear(Salaries $record): string
    {
        $months = [
            1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr', 5 => 'Mei', 6 => 'Jun',
            7 => 'Jul', 8 => 'Agt', 9 => 'Sep', 10 => 'Okt', 11 => 'Nov', 12 => 'Des',
        ];
        return $months[$record->month] . ' ' . $record->year;
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
            'index' => Pages\ListSalaries::route('/'),
            'create' => Pages\CreateSalaries::route('/create'),
            'edit' => Pages\EditSalaries::route('/{record}/edit'),
        ];
    }
}
