<?php

namespace App\Filament\Pages;

use App\Models\DurationSetting;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Pages\Page;
use Filament\Notifications\Notification;

class DurationSettingPage extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-clock';

    protected static ?string $navigationLabel = 'Durasi Pelajaran';

    protected static ?string $navigationGroup = 'Konfigurasi';

    protected static ?int $navigationSort = 9;

    protected static bool $shouldRegisterNavigation = false;

    protected static string $view = 'filament.pages.duration-setting';

    protected static ?string $title = 'Pengaturan Durasi Pelajaran';

    public ?array $data = [];

    public function mount(): void
    {
        $setting = DurationSetting::first();

        if ($setting) {
            $this->form->fill($setting->toArray());
        } else {
            // Create default if not exists
            $setting = DurationSetting::create(['lesson_duration_minutes' => 45]);
            $this->form->fill($setting->toArray());
        }
    }

    public function form(Form $form): Form
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
                            ->reactive(),
                    ]),
            ])
            ->statePath('data')
            ->inlineLabel();
    }

    public function save(): void
    {
        $data = $this->form->getState();

        $setting = DurationSetting::first();
        if ($setting) {
            $setting->update($data);
        } else {
            DurationSetting::create($data);
        }

        Notification::make()
            ->success()
            ->title('✅ Durasi Pelajaran Disimpan')
            ->body('Durasi jam pelajaran berhasil diperbarui menjadi ' . $data['lesson_duration_minutes'] . ' menit')
            ->send();
    }
}
