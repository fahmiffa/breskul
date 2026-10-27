<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;

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
        $appUrl = config('app.url');

        if (parse_url($appUrl, PHP_URL_HOST) !== 'localhost'
            && parse_url($appUrl, PHP_URL_HOST) !== '127.0.0.1') {
            URL::forceScheme('https');
        }
    }
}