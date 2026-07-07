"""
Similarity clustering untuk mengelompokkan laporan bug yang mirip.
Metode: TF-IDF + Agglomerative Clustering (cosine distance).
Menggantikan metode trigram yang terlalu ketat.
"""

from typing import List, Dict
import numpy as np
from sklearn.feature_extraction.text import TfidfVectorizer
from sklearn.cluster import AgglomerativeClustering

# ============================================================
# KONFIGURASI — mudah disetel
# ============================================================
DISTANCE_THRESHOLD = 0.72   # cosine distance; makin KECIL makin ketat (mirip banget baru nyatu)
                           # 0.6 ≈ cosine similarity 0.4. Turunkan ke 0.5 jika terlalu longgar,
                           # naikkan ke 0.7 jika terlalu sedikit yang terkelompok.
NAME_WORD_COUNT = 2        # jumlah kata dominan untuk nama kelompok

STOPWORDS = {
    # Indonesia
    'di','ke','dari','pada','untuk','dengan','dalam','atas','oleh','saat','ketika',
    'yang','dan','atau','juga','akan','telah','sudah','ini','itu','ada','adalah',
    'sebuah','suatu','para','nya','sebagai','karena','agar','supaya','namun','tetapi',
    'tapi','jika','kalau','maka','bila','terjadi','terdapat','secara','hingga','sampai',
    'setelah','sebelum','selama','antara','unit','per','tiap','setiap',
    # Inggris
    'the','a','an','in','on','at','to','for','of','with','and','or','is','are','was',
    'were','be','been','this','that','it','as','by','from','when','while','after','before',
}


def cluster_reports(texts: List[str]) -> List[Dict]:
    """
    Kelompokkan laporan yang mirip.

    Args:
        texts: list teks laporan (title+description, atau root_cause)

    Returns:
        list [{'label': str, 'count': int}, ...] urut desc by count (TOP 5)
    """
    # Bersihkan & filter teks kosong
    clean = [t.strip() for t in texts if t and t.strip()]
    if not clean:
        return []

    # Edge case: cuma 1 laporan
    if len(clean) == 1:
        return [{'label': _name_from_texts(clean), 'count': 1}]

    # TF-IDF vectorization
    try:
        vectorizer = TfidfVectorizer(
            stop_words=list(STOPWORDS),
            lowercase=True,
            min_df=1,
            ngram_range=(1, 2),
        )
        tfidf_matrix = vectorizer.fit_transform(clean)
    except ValueError:
        # Semua kata ke-filter (misal semua stopword) → tiap teks kelompok sendiri
        return _fallback_individual(clean)

    # Jika hanya 1 fitur/dokumen efektif, fallback
    if tfidf_matrix.shape[0] < 2 or tfidf_matrix.shape[1] == 0:
        return _fallback_individual(clean)

    # Agglomerative clustering dengan cosine distance
    clustering = AgglomerativeClustering(
        n_clusters=None,
        distance_threshold=DISTANCE_THRESHOLD,
        metric='cosine',
        linkage='average',
    )
    try:
        labels = clustering.fit_predict(tfidf_matrix.toarray())
    except Exception:
        return _fallback_individual(clean)

    # Kelompokkan teks berdasarkan cluster label
    clusters: Dict[int, List[str]] = {}
    for idx, cluster_id in enumerate(labels):
        clusters.setdefault(int(cluster_id), []).append(clean[idx])

    # Bentuk hasil: nama (2 kata dominan) + count
    result = []
    for cluster_id, members in clusters.items():
        name = _name_from_texts(members, vectorizer)
        result.append({'label': name, 'count': len(members)})

    # Urut desc by count, ambil top 5
    result.sort(key=lambda x: x['count'], reverse=True)
    return result[:5]


def _name_from_texts(texts: List[str], vectorizer=None) -> str:
    """Ambil NAME_WORD_COUNT kata dominan sebagai nama kelompok."""
    combined = ' '.join(texts).lower()
    # Tokenisasi sederhana, buang stopword & kata pendek/angka
    import re
    words = re.sub(r'[^\w\s]', ' ', combined).split()
    freq = {}
    for w in words:
        if w in STOPWORDS: continue
        if len(w) <= 2 and not any(c.isdigit() for c in w): continue
        if w.isdigit(): continue
        freq[w] = freq.get(w, 0) + 1

    if not freq:
        return "Tanpa Keterangan"

    # Ambil kata paling sering muncul
    top = sorted(freq.items(), key=lambda x: x[1], reverse=True)[:NAME_WORD_COUNT]
    return ' '.join(w.capitalize() for w, _ in top)


def _fallback_individual(texts: List[str]) -> List[Dict]:
    """Fallback: tiap teks jadi kelompok sendiri (top 5)."""
    result = [{'label': _name_from_texts([t]), 'count': 1} for t in texts]
    return result[:5]
