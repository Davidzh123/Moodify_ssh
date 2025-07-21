import os
from services.user_data import print_api_call_count, reset_api_call_count
import requests
from dotenv import load_dotenv
load_dotenv()

def main():
    print("Appel de l'API de génération de playlists...")

    blacklist_artists = input("Artistes à blacklister (séparés par des virgules) : ").strip()
    blacklist_genres = input("Genres à blacklister (séparés par des virgules) : ").strip()

    params = {
        "blacklist_artists": blacklist_artists,
        "blacklist_genres": blacklist_genres,
    }

    try:
        reset_api_call_count()
        response = requests.get(
            "http://127.0.0.1:8000/generate",
            params=params,
            timeout=15
        )
        response.raise_for_status()
        data = response.json()
        print("✅ Playlists générées :")
        for theme, tracks in data.items():
            print(f"\n🎧 {theme} ({len(tracks)} titres)")
            for track in tracks:
                print(f"- {track['name']} - {track['artist']}")
        print_api_call_count()
    except requests.exceptions.RequestException as e:
        print("❌ Erreur lors de l'appel à l'API :", e)


if __name__ == "__main__":
    main()