<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Http;

class SpotifyAccount extends Model
{
    protected $fillable = [
        'user_id',
        'spotify_id',
        'display_name',
        'email',
        'avatar',
        'profile_url',
        'access_token',
        'refresh_token',
        'expires_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Rafraîchit le token Spotify et met à jour expires_at.
     *
     * @param  SpotifyAccount  $account
     * @return string  Le nouveau access_token
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
