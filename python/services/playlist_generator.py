import random
from .user_data import get_artist_new_tracks, get_tracks_from_genres
from .filters import filter_tracks_by_genres
from services.recommendation_ia import extract_audio_features, cluster_tracks
from spotipy import Spotify


_avatar_cache: dict[str, str] = {}


def album_cover(track: dict) -> str:
    """
    Retourne la première image d’album si présente, sinon ''.
    Utilise les données déjà présentes dans le dict `track`.
    """
    imgs = track.get("album", {}).get("images", [])
    return imgs[0]["url"] if imgs else ""


def artist_avatar(sp: Spotify, artist_id: str) -> str:
    """
    Récupère la première image de l’artiste via /v1/artists/{id}.
    Met en cache le résultat pour chaque artist_id.
    """
    if artist_id in _avatar_cache:
        return _avatar_cache[artist_id]
    try:
        data = sp.artist(artist_id)
        imgs = data.get("images", [])
        url = imgs[0]["url"] if imgs else ""
    except Exception:
        url = ""
    _avatar_cache[artist_id] = url
    return url


def build_track_pool(sp, all_artists, genres, n_artist_tracks=10, n_genre_tracks=20):
    track_pool = []
    for artist in all_artists:
        tracks = get_artist_new_tracks(sp, artist["id"], max_albums=3)[:n_artist_tracks]
        track_pool.extend(tracks)

    genre_track_pool = get_tracks_from_genres(sp, genres[:6], max_per_genre=n_genre_tracks)
    track_pool.extend(genre_track_pool)

    # Supprimer les doublons
    seen = set()
    unique_tracks = []
    for t in track_pool:
        if t["id"] not in seen:
            seen.add(t["id"])
            unique_tracks.append(t)

    return unique_tracks


def generate_playlists(sp, themes_config, all_artists, genres, artist_blacklist=None, genre_blacklist=None):
    print("🎧 Récupération des artistes et genres...")

    # ✅ Construction du pool de morceaux
    print("📦 Création du pool de morceaux général...")
    full_pool = build_track_pool(sp, all_artists, genres)

    # ✅ Application des filtres
    filtered_pool = full_pool
    filters_applied = []
    if artist_blacklist:
        filtered_pool = [
            t for t in filtered_pool
            if t["artist"].lower() not in [a.lower() for a in artist_blacklist]
        ]
        filters_applied.append(f"Blacklist artistes: {artist_blacklist}")

    if genre_blacklist:
        filtered_pool = filter_tracks_by_genres(sp, filtered_pool, allowed_genres=None)
        filtered_pool = [
            t for t in filtered_pool
            if all(
                g.lower() not in [gg.lower() for gg in genre_blacklist]
                for g in sp.artists([t["artist_id"]])["artists"][0].get("genres", [])
            )
        ]
        filters_applied.append(f"Blacklist genres: {genre_blacklist}")

    if filters_applied:
        print(f"⚠️ Filtres appliqués : {', '.join(filters_applied)}")

    # ✅ Étape 1 : extraction des audio features
    enriched_pool = extract_audio_features(filtered_pool, sp)

    # ✅ Étape 2 : clustering des morceaux
    clustered_tracks = []
    if enriched_pool:
        clustered_tracks = cluster_tracks(enriched_pool, n_clusters=4)
    else:
        print("⚠️ Aucun morceau enrichi, clustering impossible.")

    # ✅ Génération des playlists
    print("\n🎼 Génération des playlists :\n")
    playlists = {}
    for theme, config in themes_config.items():

        themed_pool = filtered_pool
        if config.get("genres"):
            themed_pool = filter_tracks_by_genres(sp, filtered_pool, config["genres"])

        if len(themed_pool) < 5:
            print(f"⚠️ Pas assez de morceaux pour '{theme}', utilisation du pool complet.")
            themed_pool = filtered_pool

        size = config.get("size", 20)
        if theme.lower() != "découverte" and clustered_tracks:
            n_ia = int(size * 0.4)
            n_classic = size - n_ia

            classic_sample = random.sample(themed_pool, min(len(themed_pool), n_classic))
            ia_sample = random.sample(clustered_tracks, min(len(clustered_tracks), n_ia))

            playlist = classic_sample + ia_sample
            random.shuffle(playlist)
        else:
            playlist = random.sample(themed_pool, min(len(themed_pool), size))

        for t in playlist:
            # Cover album
            t["image"] = album_cover(t)
            # Avatar artiste
            aid = t.get("artist_id")
            t["artist_image"] = artist_avatar(sp, aid) if aid else ""
            # URI Spotify
            t["uri"] = t.get("uri") or f"spotify:track:{t.get('id', '')}"
            # Nom de l’artiste (utile pour l’API)
            if not t.get("artist"):
                art_list = t.get("artists", [])
                t["artist"] = art_list[0]["name"] if art_list else ""

        playlists[theme] = playlist
        print(f"\n🎵 Playlist '{theme}' ({len(playlist)} morceaux) :")
        for i, t in enumerate(playlist, 1):
            print(f"{i}. {t['name']} — {t['artist']}")

    return playlists