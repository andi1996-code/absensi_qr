<?php

namespace App\Providers;

use App\Models\Teachers;
use App\Observers\TeachersObserver;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Teachers::observe(TeachersObserver::class);
    }
}
