<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Laravel\Passport\Passport;

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
        // The CMS authenticates with the OAuth2 password grant (email +
        // password exchanged for an access/refresh token pair). Passport
        // disables this grant by default, so it must be enabled explicitly.
        Passport::enablePasswordGrant();
    }
}
