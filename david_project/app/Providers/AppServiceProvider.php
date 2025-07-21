<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
{
    // Force l'enregistrement du driver Spotify
    $this->app->make('Laravel\Socialite\Contracts\Factory')
         ->extend('spotify', function ($app) {
             $config = $app['config']['services.spotify'];
             return new \SocialiteProviders\Spotify\Provider(
                 $app['request'], $config['client_id'], $config['client_secret'], $config['redirect']
             );
         });
}
}
