<?php

namespace App\Http\Controllers;

use App\Models\SpotifyAccount;
use App\Models\Recommendation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PlaylistController extends Controller
{
    /**
     * 1) RECOMMEND – appeller le micro-service Python et stocker la reco.
     */
    // app/Http/Controllers/PlaylistController.php

public function recommend(Request $request)
{
    Log::info('>>> recommend() déclenché');

    $user    = $request->user();
    $account = $user?->spotifyAccount;
    if (! $account) {
        return redirect()->route('home')
                         ->withErrors(['spotify' => 'Connecte ton compte Spotify.']);
    }

    // 1) Rafraîchir le token si nécessaire
    $token = now()->greaterThan($account->expires_at)
        ? SpotifyAccount::refreshToken($account)
        : $account->access_token;

    // 2) Récupérer le thème et la blacklist
    $theme            = $request->input('theme', 'all');
    $blacklistArtists = $request->input('blacklist_artists', []);

    // 3) Appeler le micro-service Python
    $payload = [
        'access_token'      => $token,
        'blacklist_artists' => $blacklistArtists,
        'blacklist_genres'  => $request->input('blacklist_genres', []),
        'blacklist_tracks'  => $request->input('blacklist_tracks', []),
    ];
    $apiBase = rtrim(config('services.moodify_api.base_url', ''), '/');
    $response = Http::timeout(60)
                    ->acceptJson()
                    ->withToken($token)
                    ->post("{$apiBase}/playlist/recommend", $payload);

    if ($response->failed()) {
        Log::error('API /playlist/recommend erreur', [
            'status' => $response->status(),
            'body'   => $response->body(),
        ]);
        return back()->withErrors(['api' => 'Service de recommandation indisponible.']);
    }

    // 4) Ne récupérer que le thème choisi (fusionner dans l’existant)
    $allPlaylists = $response->json();
    $rec = Recommendation::firstOrNew(['user_id' => $user->id]);
    $oldData = $rec->data ?? [];

    if ($theme !== 'all' && isset($allPlaylists[$theme])) {
        // on ne remplace que ce thème
        $newData = array_merge(
            $oldData,
            [ $theme => $allPlaylists[$theme] ]
        );
    } else {
        // on écrase tout si “all”
        $newData = $allPlaylists;
    }

    // 5) Sauvegarde
    $rec->fill([
        'data'              => $newData,
        'blacklist_artists' => $blacklistArtists,
        'blacklist_tracks'  => $payload['blacklist_tracks'],
    ])->save();

    // 6) Redirige vers l’édition, avec flash du thème
    return redirect()->route('playlist.edit')
                     ->with([
                         'status'       => 'Playlist générée avec succès !',
                         'currentTheme' => $theme,
                     ]);
}

    /**
     * 2) SHOW – affiche la dernière génération (ou vue vide).
     */
    public function show()
    {
        $rec = Auth::user()
                   ->recommendations()
                   ->latest('id')
                   ->first();

        return view('playlist-show', compact('rec'));
    }
    
    public function edit()
{
    $user = auth()->user();

    // On récupère la dernière recommandation
    $rec = $user->recommendations()
                ->latest('id')
                ->first();

    // Si pas de reco, on redirige vers le show pour générer une playlist
    if (! $rec) {
        return redirect()->route('playlist.show')
                         ->withErrors(['spotify' => 'Aucune playlist à modifier.']);
    }

    // Passe les données à la vue playlist-edit (tu l’as déjà créée)
    return view('playlist-edit', [
        'rec'    => $rec,
        'themes' => array_keys($rec->data),
    ]);
}

    /**
     * 3) SAVE – marque la playlist comme « enregistrée ».
     */
    public function save(Request $request)
    {
        $rec = $request->user()
                       ->recommendations()
                       ->latest('id')
                       ->first();

        if (! $rec) {
            return redirect()->route('playlist.show')
                             ->with('status', 'Aucune playlist à enregistrer.');
        }

        $rec->touch(); // met à jour updated_at
        return back()->with('status', 'Playlist enregistrée en base !');
    }

    /**
     * 4) DESTROY – supprime la dernière recommandation.
     */
    public function destroy(Request $request)
    {
        $rec = $request->user()
                       ->recommendations()
                       ->latest('id')
                       ->first();

        if ($rec) {
            $rec->delete();
            return back()->with('status', 'Playlist supprimée !');
        }

        return back()->with('status', 'Aucune playlist à supprimer.');
    }

    /**
     * 5) REFRESH SPOTIFY – rafraîchit manuellement le token.
     */
    public function refreshSpotify(Request $request)
    {
        $account = $request->user()->spotifyAccount;
        if (! $account) {
            return redirect()->route('home')
                             ->withErrors(['spotify' => 'Aucun compte Spotify à rafraîchir.']);
        }

        try {
            SpotifyAccount::refreshToken($account);
        } catch (\Throwable $e) {
            Log::error('refreshSpotify()', ['msg' => $e->getMessage()]);
            return back()->withErrors(['spotify' => 'Impossible de rafraîchir le token.']);
        }

        return back()->with('status', 'Token Spotify mis à jour !');
    }

    /**
     * 6) saveToSpotify – crée et peuple la playlist sur Spotify.
     */
    /**
 * 6) saveToSpotify – crée et peuple la playlist sur Spotify.
 */
/**
 * 6) saveToSpotify – crée et peuple la playlist sur Spotify.
 */
public function saveToSpotify(Request $request)
{
    // 0) Récupère l’utilisateur et son compte Spotify
    $user    = $request->user();
    $account = $user->spotifyAccount;
    if (! $account) {
        return back()->withErrors(['spotify' => 'Aucun compte Spotify associé.']);
    }

    // 1) Récupère (et rafraîchit si expiré) le token OAuth
    $token = now()->greaterThan($account->expires_at)
        ? SpotifyAccount::refreshToken($account)
        : $account->access_token;

    // 2) Récupère le thème choisi et la dernière recommandation
    $theme = $request->input('theme');
    $rec   = $user->recommendations()->latest('id')->first();
    if (! $rec || ! isset($rec->data[$theme])) {
        return back()->withErrors(['spotify' => 'Thème invalide.']);
    }

    // 3) Récupère l’ID Spotify de l’utilisateur
    $me = Http::withToken($token)
              ->get('https://api.spotify.com/v1/me')
              ->throw()
              ->json();
    $spotifyUserId = $me['id'] ?? abort(500, 'Impossible de récupérer votre ID Spotify.');

    // 4) Crée la playlist sur Spotify
    $name   = 'Moodify – ' . ucfirst($theme);
    $create = Http::withToken($token)
                  ->post("https://api.spotify.com/v1/users/{$spotifyUserId}/playlists", [
                      'name'        => $name,
                      'public'      => false,
                      'description' => "Playlist Moodify thème {$theme}",
                  ])
                  ->throw()
                  ->json();
    $playlistId = $create['id'] ?? abort(500, 'Aucun ID de playlist renvoyé.');

    // 5) Construit les URIs de piste
    $uris = collect($rec->data[$theme])
        ->map(function($track) {
            // si l'API Python fournit un champ 'uri', on l'utilise
            if (! empty($track['uri'])) {
                return $track['uri'];
            }
            // sinon on préfixe l'ID pour obtenir une URI valide
            if (! empty($track['id'])) {
                return 'spotify:track:' . $track['id'];
            }
            return null;
        })
        ->filter()
        ->values()
        ->all();

    // 6) Log pour debug
    \Log::info('DEBUG saveToSpotify → playlistId', ['playlistId' => $playlistId]);
    \Log::info('DEBUG saveToSpotify → track URIs',    ['uris'       => $uris]);

    // 7) Ajoute les pistes à la playlist
    $response = Http::withToken($token)
        ->post("https://api.spotify.com/v1/playlists/{$playlistId}/tracks", [
            'uris' => $uris,
        ]);

    if ($response->failed()) {
        \Log::error('Échec ajout pistes Spotify', [
            'status' => $response->status(),
            'body'   => $response->body(),
        ]);
        return back()->withErrors([
            'spotify' => 'Impossible d’ajouter les pistes à Spotify.'
        ]);
    }

    return back()->with('status', "Playlist « {$name} » créée sur Spotify !");
}


}
