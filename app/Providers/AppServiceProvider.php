<?php

namespace App\Providers;

use Illuminate\Support\Carbon;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Fechas en español (por ejemplo, "10 de noviembre de 2026").
        Carbon::setLocale(config('app.locale'));
    }
}
