<?php

namespace App\Filament\Pages;

use App\Models\SchoolProfile;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Pages\Page;
use Filament\Notifications\Notification;

class SchoolProfilePage extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-building-library';

    protected static ?string $navigationLabel = 'Profil Sekolah';

    protected static ?string $navigationGroup = 'Konfigurasi';

    protected static ?int $navigationSort = 8;

    protected static string $view = 'filament.pages.school-profile';

    protected static ?string $title = 'Profil Sekolah';

    public ?array $data = [];

    public function mount(): void
    {
        $profile = SchoolProfile::first();

        if ($profile) {
            $this->form->fill($profile->toArray());
        } else {
            // Create default if not exists
            $profile = SchoolProfile::create([
                'name' => 'Nama Sekolah',
                'npsn' => '',
                'address' => '',
                'phone' => '',
                'email' => '',
                'logo_path' => '',
                'header_text' => '',
            ]);
            $this->form->fill($profile->toArray());
        }
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('📋 Informasi Sekolah')
                    ->description('Kelola informasi profil sekolah. Data ini akan ditampilkan di slip gaji dan laporan.')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nama Sekolah')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('npsn')
                            ->label('NPSN')
                            ->maxLength(255)
                            ->helperText('Nomor Pokok Sekolah Nasional'),

                        Forms\Components\Textarea::make('address')
                            ->label('Alamat')
                            ->maxLength(255),

                        Forms\Components\TextInput::make('phone')
                            ->label('Telepon')
                            ->tel()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('email')
                            ->label('Email')
                            ->email()
                            ->maxLength(255),
                    ]),

                Forms\Components\Section::make('🎨 Logo & Header')
                    ->description('Kustomisasi tampilan visual sekolah')
                    ->schema([
                        Forms\Components\FileUpload::make('logo_path')
                            ->label('Logo Sekolah')
                            ->image()
                            ->imageResizeMode('cover')
                            ->imageCropAspectRatio('1:1')
                            ->maxSize(5120)
                            ->directory('school-logos')
                            ->visibility('public')
                            ->helperText('Format: JPG, PNG. Max: 5MB. Ukuran ideal: 1:1 (square)'),

                        Forms\Components\Textarea::make('header_text')
                            ->label('Teks Header')
                            ->columnSpanFull()
                            ->helperText('Teks tambahan yang ditampilkan di atas nama sekolah'),
                    ]),
            ])
            ->statePath('data')
            ->inlineLabel();
    }

    public function save(): void
    {
        $data = $this->form->getState();

        $profile = SchoolProfile::first();
        if ($profile) {
            $profile->update($data);
            $message = 'Profil sekolah berhasil diperbarui';
        } else {
            SchoolProfile::create($data);
            $message = 'Profil sekolah berhasil dibuat';
        }

        Notification::make()
            ->success()
            ->title('✅ Profil Sekolah Disimpan')
            ->body($message)
            ->send();
    }
}
