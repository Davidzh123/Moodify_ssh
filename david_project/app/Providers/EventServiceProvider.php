<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use SocialiteProviders\Manager\SocialiteWasCalled;
use SocialiteProviders\Spotify\SpotifyExtendSocialite;

class EventServiceProvider extends ServiceProvider
{
    /**
     * Les événements écoutés par votre appli.
     */
    protected $listen = [
        // À chaque appel de Socialite, on injecte notre extension Spotify
        SocialiteWasCalled::class => [
            SpotifyExtendSocialite::class.'@handle',
        ],
    ];

    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        parent::boot();
    }
}
