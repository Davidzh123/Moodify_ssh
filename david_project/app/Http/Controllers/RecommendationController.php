<?php

namespace App\Http\Controllers;

use App\Models\Recommendation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

class RecommendationController extends Controller
{
    /**
     * Toggle « Je n’aime pas » pour un artiste, puis régénère la playlist.
     */
    public function toggleArtist(Request $request, Recommendation $rec, $artist)
    {
        // S'assurer que l'utilisateur propriétaire peut modifier
        $this->authorize('update', $rec);

        // Ajouter l'artiste à la blacklist s'il n'y est pas déjà
        $black = $rec->blacklist_artists;
        if (! in_array($artist, $black)) {
            $black[] = $artist;
            $rec->update(['blacklist_artists' => array_values($black)]);
        }

        // Appel au micro-service Python avec nouveau filtre
        $token = Auth::user()->spotifyAccount->access_token;
        $response = Http::withToken($token)
            ->post(rtrim(config('services.python.url'), '/') . '/playlist/recommend', [
                'access_token'      => $token,
                'blacklist_artists' => $rec->blacklist_artists,
                'blacklist_genres'  => $rec->blacklist_genres,
            ])
            ->throw();

        // Mettre à jour le JSON stocké
        $rec->update(['data' => $response->json()]);

        return back()->with('status', 'Playlist régénérée selon vos choix.');
    }
}
