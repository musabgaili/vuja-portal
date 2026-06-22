<?php

namespace App\Providers;

use Illuminate\Support\Facades\URL;
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
        // Force HTTPS for URL/asset generation outside local/testing so links,
        // redirects and the session cookie's Secure flag behave correctly behind
        // the production TLS-terminating proxy.
        if (! $this->app->environment('local', 'testing')) {
            URL::forceScheme('https');
        }
    }
}
