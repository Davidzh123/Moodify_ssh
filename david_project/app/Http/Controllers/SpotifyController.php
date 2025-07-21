<?php

namespace App\Http\Controllers;

use App\Models\SpotifyAccount;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Laravel\Socialite\Facades\Socialite;

class SpotifyController extends Controller
{
    /**
     * 1) Lance le flow OAuth Spotify pour lier (ou relier) un compte.
     */
    public function redirect(): RedirectResponse
    {
        return Socialite::driver('spotify')
            ->scopes([
                'user-read-private', 
                'user-read-email',
                'playlist-read-private',
                'user-top-read',  // indispensable pour /me/top/artists
                'playlist-modify-private',   // ← ajouté
                'playlist-modify-public',               
                'user-read-recently-played',  // pour /me/player/recently-played
            ])
            ->with(['show_dialog' => 'true']) // force toujours la pop-up
            ->stateless()
            ->redirect();
    }

    /**
     * 2) Callback après autorisation Spotify.
     *    Enregistre ou met à jour le compte et ses tokens.
     */
    public function callback(): RedirectResponse
    {
        $spotifyUser = Socialite::driver('spotify')->stateless()->user();
        $user = Auth::user();

        if (! $user) {
            return redirect()
                ->route('login.create')
                ->withErrors('Connecte-toi d’abord pour associer ton compte Spotify.');
        }

        SpotifyAccount::updateOrCreate(
            ['user_id' => $user->id],
            [
                'spotify_id'    => $spotifyUser->id,
                'display_name'  => $spotifyUser->nickname ?? $spotifyUser->name,
                'email'         => $spotifyUser->email,
                'avatar'        => $spotifyUser->avatar
                                   ?? ($spotifyUser->user['images'][0]['url'] ?? null),
                'profile_url'   => $spotifyUser->user['external_urls']['spotify'] ?? null,
                'access_token'  => $spotifyUser->token,
                'refresh_token' => $spotifyUser->refreshToken,
                'expires_at'    => now()->addSeconds($spotifyUser->expiresIn),
            ]
        );

        return redirect()->route('home')
                         ->with('status', 'Compte Spotify associé avec succès.');
    }

    /**
     * 3) Déliaison du compte Spotify.
     */
    public function unlink(): RedirectResponse
    {
        $user = Auth::user();
        $user->spotifyAccount?->delete();

        return back()->with('status', 'Compte Spotify délié.');
    }

    /**
     * 4) Rafraîchit le token OAuth à partir du refresh_token.
     *
     * @param  SpotifyAccount  $account
     * @return string  Le nouvel access_token
     */
    public static function refreshToken(SpotifyAccount $account): string
    {
        $response = Http::asForm()->post('https://accounts.spotify.com/api/token', [
            'grant_type'    => 'refresh_token',
            'refresh_token' => $account->refresh_token,
            'client_id'     => config('services.spotify.client_id'),
            'client_secret' => config('services.spotify.client_secret'),
        ]);

        if ($response->failed()) {
            abort(401, 'Impossible de rafraîchir le token Spotify.');
        }

        $data = $response->json();
        $account->update([
            'access_token' => $data['access_token'],
            'expires_at'   => now()->addSeconds($data['expires_in']),
        ]);

        return $data['access_token'];
    }
}
