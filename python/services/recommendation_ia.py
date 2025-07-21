# services/recommendation_ia.py
import time

import spotipy
from sklearn.cluster import KMeans
from sklearn.preprocessing import StandardScaler
from typing import List, Dict
import numpy as np
import random

def extract_audio_features(tracks, sp):
    if not tracks:
        print("⚠️ Aucun morceau fourni.")
        return []

    track_ids = [t["id"] for t in tracks if t.get("id")]
    track_ids = list(set(track_ids))  # enlever doublons

    print(f"🔍 {len(track_ids)} IDs valides à analyser.")

    if len(track_ids) == 0:
        print("❌ Aucun ID Spotify valide.")
        return []

    features = []
    for i in range(0, len(track_ids), 100):
        batch = track_ids[i:i+100]
        try:
            audio_features = sp.audio_features(batch)
            if audio_features is None:
                print(f"❌ API a renvoyé None pour le batch {i//100 + 1}. Token invalide ?")
                continue
            features.extend([f for f in audio_features if f is not None])
        except Exception as e:
            print(f"⚠️ Erreur lors de l’appel audio_features (batch {i//100 + 1}) : {e}")
            continue

    print(f"✅ {len(features)} morceaux avec audio features extraites.")
    return features

def build_feature_matrix(tracks: List[Dict]) -> np.ndarray:
    """Construit une matrice numpy à partir des features utiles"""
    feature_keys = ["danceability", "energy", "valence", "tempo", "acousticness", "instrumentalness"]
    matrix = []
    for track in tracks:
        f = track.get("features", {})
        row = [f.get(k, 0.0) for k in feature_keys]
        matrix.append(row)
    return np.array(matrix)

def cluster_tracks(tracks: List[Dict], n_clusters: int = 4):
    """Clusterise les morceaux et retourne le cluster majoritaire et les morceaux correspondants"""

    if not tracks:
        print("⚠️ Aucun morceau à clusteriser.")
        return []

    matrix = build_feature_matrix(tracks)

    # Vérification : s'assurer que matrix est bien 2D et non vide
    if matrix.size == 0 or matrix.shape[0] == 0:
        print("⚠️ Matrice vide : aucun feature utilisable pour le clustering.")
        return []

    if matrix.ndim != 2:
        print("⚠️ Matrice invalide : reshape nécessaire.")
        matrix = matrix.reshape(-1, 1)

    # Scaler
    scaler = StandardScaler()
    try:
        X_scaled = scaler.fit_transform(matrix)
    except ValueError as e:
        print(f"❌ Erreur lors du scaling : {e}")
        return []

    # Clustering
    try:
        kmeans = KMeans(n_clusters=n_clusters, random_state=42)
        clusters = kmeans.fit_predict(X_scaled)
    except ValueError as e:
        print(f"❌ Erreur lors du clustering : {e}")
        return []

    # Attribution des clusters aux tracks
    for i, track in enumerate(tracks):
        track["cluster"] = int(clusters[i])

    # Analyse du cluster dominant
    counts = np.bincount(clusters)
    dominant_cluster = int(np.argmax(counts))

    # Debug terminal
    print("🎯 Répartition des clusters :", counts.tolist())
    print("⭐ Cluster préféré :", dominant_cluster)
    print("📊 Moyenne des caractéristiques du cluster préféré :")
    cluster_matrix = X_scaled[clusters == dominant_cluster]
    means = cluster_matrix.mean(axis=0)
    keys = ["danceability", "energy", "valence", "tempo", "acousticness", "instrumentalness"]
    for k, m in zip(keys, means):
        print(f"{k:15}: {m:.2f}")

    return [t for t in tracks if t["cluster"] == dominant_cluster]
