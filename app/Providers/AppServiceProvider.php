<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        // Di produksi (di belakang proxy HTTPS Render), paksa semua URL & asset pakai https
        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }
    }
}
