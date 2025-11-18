<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ClassRoomsResource\Pages;
use App\Models\ClassRooms;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ClassRoomsResource extends Resource
{
    protected static ?string $model = ClassRooms::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-office';

    protected static ?string $navigationLabel = 'Kelas';
    protected static ?string $navigationGroup = 'Master Data';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('name')
                ->label('Nama Kelas')
                ->required()
                ->maxLength(255),

            Forms\Components\Hidden::make('code')
                ->default(null),

            Forms\Components\Textarea::make('description')
                ->label('Keterangan')
                ->rows(3)
                ->nullable(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('name')
                ->label('Nama Kelas')
                ->searchable()
                ->sortable(),

            // Kode tidak ditampilkan karena kita hanya menggunakan nama kelas

            Tables\Columns\TextColumn::make('description')
                ->label('Keterangan')
                ->limit(40),
        ])
        ->filters([])
        ->actions([
            Tables\Actions\EditAction::make(),
            Tables\Actions\DeleteAction::make(),
        ])
        ->bulkActions([
            Tables\Actions\DeleteBulkAction::make(),
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListClassRooms::route('/'),
            'create' => Pages\CreateClassRooms::route('/create'),
            'edit' => Pages\EditClassRooms::route('/{record}/edit'),
        ];
    }
}
