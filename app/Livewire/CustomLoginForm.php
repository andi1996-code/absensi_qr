<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Checkbox;
use Filament\Facades\Filament;

class CustomLoginForm extends Component implements HasForms
{
    use InteractsWithForms;

    public $email = '';
    public $password = '';
    public $remember = false;

    public $error = '';

    protected function getFormSchema(): array
    {
        return [
            TextInput::make('email')
                ->label('Email')
                ->required()
                ->email()
                ->autocomplete('email')
                ->placeholder('contoh@email.com'),

            TextInput::make('password')
                ->label('Password')
                ->required()
                ->password()
                ->autocomplete('current-password'),

            Checkbox::make('remember')
                ->label('Ingat saya'),
        ];
    }

    public function login()
    {
        $this->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $credentials = [
            'email' => $this->email,
            'password' => $this->password,
        ];

        if (Auth::guard('web')->attempt($credentials, $this->remember)) {
            session()->regenerate();

            // Debug: Log successful login
            \Illuminate\Support\Facades\Log::info('User logged in successfully', [
                'user_id' => Auth::id(),
                'authenticated' => Auth::check(),
            ]);

            return redirect()->intended(Filament::getUrl());
        }

        $this->error = 'Email atau password salah.';
    }

    public function render()
    {
        return view('livewire.custom-login-form');
    }
}
