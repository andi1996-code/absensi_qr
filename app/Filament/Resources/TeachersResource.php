<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TeachersResource\Pages;
use App\Filament\Resources\TeachersResource\RelationManagers;
use App\Models\Teachers;
use App\Models\PositionSalary;
use App\Services\QrCodeService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Tables\Actions\Action;
use Symfony\Component\HttpFoundation\StreamedResponse;

class TeachersResource extends Resource
{
    protected static ?string $model = Teachers::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationLabel = 'Guru';

    protected static ?string $navigationGroup = 'Master Data';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Jabatan & Gaji')
                    ->description('Pilih jabatan dan lihat tunjangan gaji')
                    ->schema([
                        Forms\Components\Select::make('position')
                            ->label('Jabatan')
                            ->options(function () {
                                return PositionSalary::active()
                                    ->pluck('position', 'position');
                            })
                            ->placeholder('Pilih jabatan...')
                            ->nullable()
                            ->searchable()
                            ->reactive()
                            ->helperText('Pilih jabatan untuk melihat tunjangan gaji otomatis'),

                        Forms\Components\TextInput::make('salary_adjustment')
                            ->label('Tunjangan Gaji (otomatis dari jabatan)')
                            ->prefix('Rp ')
                            ->readOnly()
                            ->disabled()
                            ->placeholder('Pilih jabatan terlebih dahulu')
                            ->formatStateUsing(function ($state, Forms\Get $get) {
                                $position = $get('position');
                                if (!$position) {
                                    return '-';
                                }

                                $positionSalary = PositionSalary::where('position', $position)
                                    ->where('is_active', true)
                                    ->first();

                                if (!$positionSalary) {
                                    return '-';
                                }

                                return number_format($positionSalary->salary_adjustment, 0, ',', '.');
                            }),

                        Forms\Components\Textarea::make('salary_notes')
                            ->label('Catatan Gaji (Opsional)')
                            ->rows(2)
                            ->placeholder('Catatan khusus mengenai gaji jabatan ini...')
                            ->nullable()
                            ->helperText('Untuk mencatat informasi tambahan tentang gaji'),
                    ])
                    ->columns(1),

                Forms\Components\Section::make('Informasi Guru')
                    ->description('Isi data lengkap guru')
                    ->schema([
                        Forms\Components\Placeholder::make('qr_info')
                            ->label('')
                            ->content('💡 QR Code akan di-generate otomatis setelah guru disimpan')
                            ->hidden(fn (string $operation) => $operation !== 'create'),

                        Forms\Components\TextInput::make('name')
                            ->label('Nama Guru')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('nip')
                            ->label('NIP')
                            ->nullable()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('qr_code')
                            ->label('QR Code')
                            ->unique(ignorable: fn ($record) => $record)
                            ->maxLength(255)
                            ->readOnly()
                            ->hidden(fn (string $operation) => $operation === 'create')
                            ->helperText('Auto-generated saat disimpan')
                            ->columnSpanFull(),

                        Forms\Components\TextInput::make('email')
                            ->label('Email')
                            ->email()
                            ->nullable()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('phone')
                            ->label('Nomor Telepon')
                            ->tel()
                            ->nullable()
                            ->maxLength(255),

                        Forms\Components\FileUpload::make('photo_path')
                            ->label('Foto Guru')
                            ->image()
                            ->maxSize(5120) // 5MB
                            ->nullable()
                            ->directory('teachers')
                            ->visibility('private'),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Status')
                    ->schema([
                        Forms\Components\Toggle::make('is_active')
                            ->label('Aktif')
                            ->default(true),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('photo_path')
                    ->label('Foto')
                    ->circular()
                    ->visibility('private'),

                Tables\Columns\TextColumn::make('name')
                    ->label('Nama Guru')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('nip')
                    ->label('NIP')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('qr_code')
                    ->label('QR Code')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('phone')
                    ->label('Telepon')
                    ->searchable(),

                Tables\Columns\TextColumn::make('position')
                    ->label('Jabatan')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Status')
                    ->boolean()
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Status Aktif'),
            ])
            ->actions([
                Action::make('download_qr')
                    ->label('Download QR')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('success')
                    ->action(function (Teachers $record) {
                        $qrPng = QrCodeService::generateImage($record->qr_code, 400);
                        return response()->streamDownload(
                            function () use ($qrPng) {
                                echo $qrPng;
                            },
                            $record->qr_code . '.png',
                            ['Content-Type' => 'image/png']
                        );
                    }),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('name');
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
            'index' => Pages\ListTeachers::route('/'),
            'create' => Pages\CreateTeachers::route('/create'),
            'edit' => Pages\EditTeachers::route('/{record}/edit'),
        ];
    }
}
