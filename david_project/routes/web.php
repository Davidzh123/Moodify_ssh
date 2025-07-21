<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\SpotifyController;
use App\Http\Controllers\PlaylistController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn() => view('welcome'));

Route::get('/dashboard', [HomeController::class, 'home'])
     ->name('home');

Route::middleware('auth')->group(function () {
    Route::get('/auth/spotify/redirect',  [SpotifyController::class, 'redirect'])
         ->name('spotify.redirect');
    Route::get('/auth/spotify/callback',  [SpotifyController::class, 'callback'])
         ->name('spotify.callback');
    Route::delete('/auth/spotify/unlink', [SpotifyController::class, 'unlink'])
         ->name('spotify.unlink');

    Route::post('/playlist/recommend', [PlaylistController::class, 'recommend'])
         ->name('playlist.recommend');
    Route::get('/playlist',            [PlaylistController::class, 'show'])
         ->name('playlist.show');

    Route::post('/playlist/save',      [PlaylistController::class, 'save'])
         ->name('playlist.save');
     Route::delete('/playlist/destroy', [PlaylistController::class, 'destroy'])
          ->name('playlist.destroy');   // ⇐ même nom, mais POST accep­té

     Route::get('/playlist/edit', [PlaylistController::class, 'edit'])
     ->name('playlist.edit')
     ->middleware('auth');

    Route::get('/spotify/refresh',     [PlaylistController::class, 'refreshSpotify'])
         ->name('spotify.refresh');
    Route::get('/playlist/need-spotify',[PlaylistController::class, 'needSpotify'])
         ->name('playlist.needSpotify');
     Route::post('/playlist/spotify-save', [PlaylistController::class, 'saveToSpotify'])
     ->name('playlist.saveSpotify');
});

require __DIR__ . '/auth.php';
