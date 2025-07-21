import time
from .user_data import safe_spotify_call

def filter_tracks_by_genres(sp, tracks, allowed_genres):
    if not allowed_genres:
        return tracks
    filtered = []
    track_ids = [t["id"] for t in tracks]
    for i in range(0, len(track_ids), 50):
        batch_ids = track_ids[i:i+50]
        batch_tracks = safe_spotify_call(sp.tracks, batch_ids)["tracks"]
        artist_ids = [t["artists"][0]["id"] for t in batch_tracks]
        batch_artists = safe_spotify_call(sp.artists, artist_ids)["artists"]
        for track, artist, original in zip(batch_tracks, batch_artists, tracks[i:i+50]):
            if any(g in allowed_genres for g in artist.get("genres", [])):
                filtered.append(original)
        time.sleep(0.2)
    return filtered

def exclude_artists(tracks, artist_names_to_exclude):
    exclude_lower = [a.lower() for a in artist_names_to_exclude]
    return [t for t in tracks if t["artist"].lower() not in exclude_lower]

def exclude_genres(sp, tracks, genres_to_exclude):
    if not genres_to_exclude:
        return tracks
    filtered = []
    track_ids = [t["id"] for t in tracks]
    for i in range(0, len(track_ids), 50):
        batch_ids = track_ids[i:i+50]
        batch_tracks = safe_spotify_call(sp.tracks, batch_ids)["tracks"]
        artist_ids = [t["artists"][0]["id"] for t in batch_tracks]
        batch_artists = safe_spotify_call(sp.artists, artist_ids)["artists"]
        for track, artist, original in zip(batch_tracks, batch_artists, tracks[i:i+50]):
            if not any(g in genres_to_exclude for g in artist.get("genres", [])):
                filtered.append(original)
        time.sleep(0.2)
    return filtered

def display_available_artists(tracks):
    return sorted({t["artist"] for t in tracks})

def display_available_genres(sp, tracks):
    genre_count = {}
    track_ids = [t["id"] for t in tracks]
    for i in range(0, len(track_ids), 50):
        batch_ids = track_ids[i:i+50]
        batch_tracks = safe_spotify_call(sp.tracks, batch_ids)["tracks"]
        artist_ids = [t["artists"][0]["id"] for t in batch_tracks]
        batch_artists = safe_spotify_call(sp.artists, artist_ids)["artists"]
        for artist in batch_artists:
            for genre in artist.get("genres", []):
                genre_count[genre] = genre_count.get(genre, 0) + 1
        time.sleep(0.2)
    return sorted(genre_count.items(), key=lambda x: x[1], reverse=True)
