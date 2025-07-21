# API.py
from fastapi import FastAPI, HTTPException
from fastapi.middleware.cors import CORSMiddleware
from pydantic import BaseModel
from spotipy import Spotify
from spotipy.exceptions import SpotifyException
from dotenv import load_dotenv

load_dotenv()

from services.user_data import get_top_artists, get_recent_artists, get_user_genres
from services.playlist_generator import generate_playlists

app = FastAPI()
app.add_middleware(
    CORSMiddleware,
    allow_origins=["http://localhost:8000"],
    allow_methods=["*"],
    allow_headers=["*"],
)

class PlaylistRequest(BaseModel):
    access_token: str
    blacklist_artists: list[str] = []
    blacklist_genres:  list[str] = []
    blacklist_tracks:  list[dict] = []

@app.get("/health")
async def health_check():
    return {"status": "ok"}

@app.post("/playlist/recommend")
async def recommend_playlist(req: PlaylistRequest):
    # 1) Client Spotify
    try:
        sp = Spotify(auth=req.access_token)
    except Exception:
        raise HTTPException(401, "Jeton invalide ou expiré")

    # 2) Top & récent
    try:
        top_artists    = get_top_artists(sp, limit=20)
        recent_artists = get_recent_artists(sp, limit=50)
    except SpotifyException as e:
        if getattr(e, "http_status", None) == 403:
            raise HTTPException(401, "Insufficient client scope – reauthenticate")
        raise HTTPException(502, f"Erreur interne Spotify : {e}")

    # 3) Concatène et déduplique les artistes
    all_artists = []
    seen = set()
    for a in recent_artists + top_artists:
        aid = a.get("id")
        if aid and aid not in seen:
            seen.add(aid)
            all_artists.append(a)

    # 4) Genres favoris de l’utilisateur
    genres = get_user_genres(sp, all_artists)

    # 5) Configuration des thèmes (ajout de genres)
    themes_config = {
        "default": {"size": 40, "genres": []},

        "sport": {
            "size": 20,
            "genres": [
                # existants
                "work-out","us rap","club"

            ]
        },

        "calme": {
            "size": 20,
            "genres": [
                "ambient", "acoustic", "classical", "chill"
            ]
        },

        "entrainant": {
            "size": 20,
            "genres": [
                "rock", "indie", "punk", "metal", "dance-rock", "electronica",
            ]
        },

        "découverte": {
            "size": 20,
            "genres": ["alternative", "electronic", "experimental", "world"]
        },
    }

    # 6) Appelle le générateur
    playlists = generate_playlists(
        sp,
        themes_config,
        all_artists,
        genres,
        artist_blacklist=req.blacklist_artists,
        genre_blacklist=req.blacklist_genres
    )

    # 7) Sérialisation JSON
    return {
        theme: [
            {
                "name":         track.get("name"),
                "artist":       track.get("artist", ""),
                "id":           track.get("id", ""),
                "artist_id":    track.get("artist_id", ""),
                "uri":          track.get("uri", ""),
                "image":        track.get("image", ""),
                "artist_image": track.get("artist_image", ""),
            }
            for track in tracks
        ]
        for theme, tracks in playlists.items()
    }
