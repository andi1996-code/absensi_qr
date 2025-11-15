<?php

namespace App\Filament\Resources;

use App\Models\PositionSalary;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PositionSalaryResource extends Resource
{
    protected static ?string $model = PositionSalary::class;

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';

    protected static ?string $navigationLabel = 'Gaji Jabatan';

    protected static ?string $modelLabel = 'Gaji Jabatan';

    protected static ?string $pluralModelLabel = 'Gaji Jabatan';

    protected static ?string $navigationGroup = 'Master Data';

    protected static ?int $navigationSort = 5;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Data Gaji Jabatan')
                    ->description('Kelola tunjangan gaji untuk setiap jabatan')
                    ->schema([
                        Forms\Components\TextInput::make('position')
                            ->label('Nama Jabatan')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('e.g., Kepala Sekolah, Guru Kelas')
                            ->helperText('Masukkan nama jabatan yang sesuai dengan daftar jabatan guru'),

                        Forms\Components\TextInput::make('salary_adjustment')
                            ->label('Tunjangan Gaji')
                            ->required()
                            ->numeric()
                            ->inputMode('decimal')
                            ->prefix('Rp ')
                            ->helperText('Masukkan nominal tunjangan gaji (contoh: 100000)')
                            ->minValue(0),

                        Forms\Components\Textarea::make('description')
                            ->label('Keterangan')
                            ->rows(3)
                            ->placeholder('Keterangan tentang tunjangan ini...')
                            ->helperText('Optional: Catatan tentang tunjangan gaji'),

                        Forms\Components\Toggle::make('is_active')
                            ->label('Aktif')
                            ->default(true)
                            ->helperText('Hanya tunjangan aktif yang akan digunakan dalam perhitungan gaji'),
                    ])
                    ->columns(1),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('position')
                    ->label('Jabatan')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('salary_adjustment')
                    ->label('Tunjangan Gaji')
                    ->money('IDR', locale: 'id')
                    ->sortable()
                    ->alignEnd(),

                Tables\Columns\TextColumn::make('description')
                    ->label('Keterangan')
                    ->limit(50)
                    ->searchable(),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Status')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->sortable(),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Diubah')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Status')
                    ->placeholder('Semua')
                    ->trueLabel('Hanya Aktif')
                    ->falseLabel('Hanya Nonaktif'),
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

    public static function getPages(): array
    {
        return [
            'index' => \App\Filament\Resources\PositionSalaryResource\Pages\ListPositionSalaries::route('/'),
            'create' => \App\Filament\Resources\PositionSalaryResource\Pages\CreatePositionSalary::route('/create'),
            'edit' => \App\Filament\Resources\PositionSalaryResource\Pages\EditPositionSalary::route('/{record}/edit'),
        ];
    }
}
