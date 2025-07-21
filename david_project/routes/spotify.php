<?php

use App\Http\Controllers\SpotifyController;
use Illuminate\Support\Facades\Route;

/*
|------------------------------------------------------------------------
|  Routes d’association Spotify (réservées aux utilisateurs connectés)
|------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {

    // ► Bouton “Associer mon compte Spotify”
    Route::get('/auth/spotify/redirect',  [SpotifyController::class, 'redirect'])
        ->name('spotify.redirect');

    // ► URL de rappel définie dans le dashboard Spotify
    Route::get('/auth/spotify/callback',  [SpotifyController::class, 'callback'])
        ->name('spotify.callback');

    // ► Bouton “Déconnecter Spotify”
    Route::delete('/auth/spotify/unlink', [SpotifyController::class, 'unlink'])
        ->name('spotify.unlink');
});
