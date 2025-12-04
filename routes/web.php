<?php

use Illuminate\Support\Facades\Route;
use App\Filament\Resources\WeeklySchedulesResource\Pages\ScheduleBuilderWeeklySchedules;
use App\Livewire\ScannerPage;
use App\Livewire\DuhaScannerPage;
use App\Http\Controllers\SalaryController;
use App\Http\Controllers\TeacherImportController;

// Redirect root ke admin login
Route::get('/', function () {
    return redirect('/admin/login-admin');
});

// Schedule Builder Route - register di Filament panel
Route::middleware(['web', 'auth'])->prefix('admin')->group(function () {
    Route::get('/schedule-builder', ScheduleBuilderWeeklySchedules::class)->name('schedule-builder');
});

// Scanner Page - Full screen view
Route::middleware(['web', 'auth'])->get('/scanner', ScannerPage::class)->name('scanner');

// Duha Scanner Page - Full screen view
Route::middleware(['web', 'auth'])->get('/duha-scanner', DuhaScannerPage::class)->name('duha-scanner');

// Salary Routes
Route::middleware(['web', 'auth'])->group(function () {
    Route::get('/salaries/{salary}/slip', [SalaryController::class, 'slip'])->name('salaries.slip');
});

// Teacher Import Template Route
Route::middleware(['web', 'auth'])->group(function () {
    Route::get('/teachers/template', [TeacherImportController::class, 'downloadTemplate'])->name('teachers.template');
});

