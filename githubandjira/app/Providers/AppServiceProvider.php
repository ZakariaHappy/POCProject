<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Laravel\Socialite\Facades\Socialite;
use App\Providers\JiraSocialiteProvider;

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
    public function boot()
    {
        Socialite::extend('jira-oauth', function ($app) {
            $config = $app['config']['services.jira'];

            return Socialite::buildProvider(JiraSocialiteProvider::class, $config);
        });
    }
}
