from services.user_data import get_artist_new_tracks, get_tracks_from_genres
'''
def build_track_pool(sp, artists, genres, n_artist_tracks=10, n_genre_tracks=20):
    pool = []

    for artist in artists:
        tracks = get_artist_new_tracks(sp, artist["id"], max_tracks=n_artist_tracks)
        for t in tracks:
            pool.append({
                "id": t["id"],
                "name": t["name"],
                "artist": t["artists"][0]["name"],
                "artist_id": t["artists"][0]["id"]
            })

    for genre in genres:
        genre_tracks = get_tracks_from_genres(sp, genre, max_tracks=n_genre_tracks)
        for t in genre_tracks:
            pool.append({
                "id": t["id"],
                "name": t["name"],
                "artist": t["artists"][0]["name"],
                "artist_id": t["artists"][0]["id"]
            })

    return pool
'''