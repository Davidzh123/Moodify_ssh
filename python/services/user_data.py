#from spotify_api.auth import auth_manager
from spotipy import Spotify
from spotipy.exceptions import SpotifyException
import time

api_call_count = 0

def safe_spotify_call(callable_func, *args, **kwargs):
    global api_call_count
    while True:
        try:
            api_call_count += 1
            return callable_func(*args, **kwargs)
        except SpotifyException as e:
            if e.http_status == 429:
                retry_after = int(e.headers.get("Retry-After", 5))
                print(f"Limite atteinte. Pause de {retry_after}s...")
                time.sleep(retry_after)
            else:
                raise

'''
def get_spotify_client():
    token_info = auth_manager.get_cached_token()
    if not token_info:
        print("🛑 Aucune session trouvée. Lance l'autorisation manuelle :")
        print("➡️ Ouvre ce lien :", auth_manager.get_authorize_url())
        code = input("📥 Colle ici le code d'autorisation : ").strip()
        token_info = auth_manager.get_access_token(code)
    return Spotify(auth=token_info["access_token"])
'''
def get_top_artists(sp, limit=10, time_range="long_term"):
    results = safe_spotify_call(sp.current_user_top_artists, limit=limit, time_range=time_range)
    return results["items"]

def get_recent_artists(sp, limit=50):
    results = safe_spotify_call(sp.current_user_recently_played, limit=limit)
    artist_count = {}
    for item in results["items"]:
        artist = item["track"]["artists"][0]
        artist_count[artist["id"]] = {
            "id": artist["id"],
            "name": artist["name"],
            "count": artist_count.get(artist["id"], {}).get("count", 0) + 1
        }
    return sorted(artist_count.values(), key=lambda x: x["count"], reverse=True)

def get_user_genres(sp, artists):
    artist_ids = [a["id"] for a in artists]
    info = safe_spotify_call(sp.artists, artist_ids)["artists"]
    genre_count = {}
    for artist in info:
        for genre in artist.get("genres", []):
            genre_count[genre] = genre_count.get(genre, 0) + 1
    sorted_genres = sorted(genre_count.items(), key=lambda x: x[1], reverse=True)
    return [g for g, _ in sorted_genres]

def get_artist_new_tracks(sp, artist_id, max_albums=3):
    # 1) Récupère les albums (avec leurs images)
    albums_data = safe_spotify_call(
        sp.artist_albums,
        artist_id,
        limit=max_albums,
        album_type="album,single"
    )["items"]

    track_list = []
    for album in albums_data:
        images = album.get("images", [])  # <-- on stocke les images de l'album

        # 2) Récupère les pistes de l'album
        album_tracks = safe_spotify_call(sp.album_tracks, album["id"])["items"]

        for track in album_tracks:
            track_list.append({
                "id":        track["id"],
                "name":      track["name"],
                "artist":    track["artists"][0]["name"],
                "artist_id": track["artists"][0]["id"],
                # 3) On embarque l'objet album contenant les images
                "album": {
                    "images": images
                },
                # Si tu veux plus tard le cover plus petit/grand, tu pourras filtrer ici
            })
    return track_list

def get_tracks_from_genres(sp, genres, max_per_genre=10):
    track_pool = []
    for genre in genres:
        results = safe_spotify_call(sp.search, q=f"genre:{genre}", type="track", limit=max_per_genre)
        for item in results["tracks"]["items"]:
            track_pool.append({
                "id": item["id"],
                "name": item["name"],
                "artist": item["artists"][0]["name"],
                "artist_id": item["artists"][0]["id"],
                "album" : item["album"]
            })
    return track_pool


def print_api_call_count():
    global api_call_count
    print(f"Nombre total de requêtes API Spotify effectuées : {api_call_count}")

def reset_api_call_count():
    global api_call_count
    api_call_count = 0