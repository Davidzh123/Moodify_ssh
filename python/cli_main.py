#from spotify_api.auth import get_spotify_client
from services.user_data import get_top_artists, get_recent_artists, get_user_genres
from services.playlist_generator import generate_playlists


def ask_blacklist_choice(prompt, available_items):
    print(prompt)
    print("Entrez les noms séparés par des virgules, ou rien pour passer.")
    print("Items disponibles :", ", ".join(available_items))
    raw = input("> ").strip()
    if not raw:
        return []
    chosen = [x.strip() for x in raw.split(",") if x.strip() in available_items]
    return chosen


def main():
    sp = get_spotify_client()

    print("Chargement des artistes favoris et genres...")
    top_artists = get_top_artists(sp, limit=20)
    recent_artists = get_recent_artists(sp, limit=50)

    # Fusion sans doublons
    all_artists = []
    seen = set()
    for a in recent_artists + top_artists:
        if a["id"] not in seen:
            seen.add(a["id"])
            all_artists.append(a)

    genres = get_user_genres(sp, all_artists)

    themes_config = {
        "default": {"size": 40, "genres": []},
        "sport": {"size": 20, "genres": ["pop", "dance"]},
        "calme": {"size": 20, "genres": ["ambient", "acoustic"]},
        "entrainant": {"size": 20, "genres": ["rock", "indie"]},
        "découverte": {"size": 20, "genres": ["alternative", "electronic"]},
    }

    # Génération initiale sans filtres
    print("\n🎵 Génération initiale des playlists sans filtres...")
    generate_playlists(sp, themes_config, all_artists, genres)

    # Demander si utilisateur veut modifier la playlist default (daily)
    print("\nVoulez-vous modifier la playlist 'default' avec des blacklists ? (o/n)")
    if input("> ").lower() == "o":
        # Préparer liste d'artistes et genres pour choix utilisateur
        available_artists = sorted({a["name"] for a in all_artists})
        available_genres = sorted(genres)

        artist_blacklist = ask_blacklist_choice("Blacklist artistes (choix parmi les artistes) :", available_artists)
        genre_blacklist = ask_blacklist_choice("Blacklist genres (choix parmi les genres) :", available_genres)

        if not artist_blacklist and not genre_blacklist:
            print("Aucun filtre choisi, génération sans filtres.")
        else:
            print("\n🎵 Génération des playlists avec filtres appliqués...")
            generate_playlists(sp, themes_config, all_artists, genres, artist_blacklist, genre_blacklist)



if __name__ == "__main__":
    main()
