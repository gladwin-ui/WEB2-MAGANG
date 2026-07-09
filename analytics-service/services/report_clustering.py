"""
Similarity clustering untuk mengelompokkan laporan bug yang mirip.
Metode: Pencocokan overlap minimal 2 kata bermakna (berulang) antar laporan
+ normalisasi sinonim teknis + penamaan frasa utuh tanpa kata menggantung.
"""

import re
from typing import List, Dict, Set

STOPWORDS = {
    # Indonesia (dasar & kata sambung)
    'di','ke','dari','pada','untuk','dengan','dalam','atas','oleh','saat','ketika',
    'yang','dan','atau','juga','akan','telah','sudah','ini','itu','ada','adalah',
    'sebuah','suatu','para','nya','sebagai','karena','agar','supaya','namun','tetapi',
    'tapi','jika','kalau','maka','bila','terjadi','terdapat','secara','hingga','sampai',
    'setelah','sebelum','selama','antara','unit','per','tiap','setiap','bisa','dapat',
    # Kata negasi & kata kerja transitif netral yang menggantung jika bersanding dengan kata benda
    'tidak','bukan','kurang','belum','lagi','akibat','disebabkan','menyebabkan',
    'mengakibatkan','akibatnya','mengalami','terhadap','berupa',
    'adanya','terjadinya','penyebab','laporan','dilaporkan','melaporkan',
    'membaca','terbaca','mendeteksi','terdeteksi','menampilkan','tampil',
    'mengirim','menerima','berjalan','bekerja','beroperasi','berfungsi',
    'melakukan','memberikan','menghasilkan','menjadi',
    # Inggris
    'the','a','an','in','on','at','to','for','of','with','and','or','is','are','was',
    'were','be','been','this','that','it','as','by','from','when','while','after','before',
    'due','caused','causing','failure','issue','problem','result','resulting','not','no',
    'read','reading','detect','detecting','display','work','working',
}

SYNONYMS = {
    'termal': 'thermal',
    'mekanis': 'mekanik',
    'solderan': 'solder',
    'retakan': 'retak',
    'menua': 'aging',
    'korsleting': 'short',
    'korslet': 'short',
    'battery': 'baterai',
    'batere': 'baterai',
    'batre': 'baterai',
    'capacitor': 'kapasitor',
}


def _clean_words(text: str) -> List[str]:
    """Bersihkan teks, normalisasi sinonim, dan hapus stopword/kata gantung."""
    words = re.sub(r'[^\w\s]', ' ', text.lower()).split()
    clean = []
    for w in words:
        w = SYNONYMS.get(w, w)
        if w in STOPWORDS:
            continue
        if len(w) <= 2 and not any(c.isdigit() for c in w):
            continue
        if w.isdigit():
            continue
        clean.append(w)
    return clean


def cluster_reports(texts: List[str]) -> List[Dict]:
    """
    Kelompokkan laporan yang memiliki minimal 2 kata bermakna yang cocok (berulang).
    """
    clean_texts = [t.strip() for t in texts if t and t.strip()]
    if not clean_texts:
        return []

    # Tokenisasi tiap laporan menjadi set kata bersih
    doc_words: List[Set[str]] = [_clean_words(t) for t in clean_texts]
    n = len(clean_texts)

    # Disjoint-set / Connected components clustering berdasarkan kecocokan >= 2 kata
    parent = list(range(n))

    def find(i: int) -> int:
        if parent[i] != i:
            parent[i] = find(parent[i])
        return parent[i]

    def union(i: int, j: int):
        root_i = find(i)
        root_j = find(j)
        if root_i != root_j:
            parent[root_i] = root_j

    for i in range(n):
        set_i = set(doc_words[i])
        if not set_i:
            continue
        for j in range(i + 1, n):
            set_j = set(doc_words[j])
            if not set_j:
                continue
            # Jika ada minimal 2 kata cocok, atau 1 kata cocok untuk laporan pendek (cuma 1 kata)
            intersection = set_i.intersection(set_j)
            if len(intersection) >= 2 or (min(len(set_i), len(set_j)) == 1 and len(intersection) >= 1):
                union(i, j)

    # Kumpulkan anggota per cluster
    clusters: Dict[int, List[int]] = {}
    for i in range(n):
        root = find(i)
        clusters.setdefault(root, []).append(i)

    # Beri nama cluster dan hitung anggotanya
    group_counts: Dict[str, int] = {}
    for members in clusters.values():
        member_texts = [clean_texts[idx] for idx in members]
        label = _name_from_texts(member_texts)
        group_counts[label] = group_counts.get(label, 0) + len(members)

    # Urutkan desc berdasarkan jumlah laporan, ambil Top 5
    sorted_groups = sorted(group_counts.items(), key=lambda x: x[1], reverse=True)[:5]
    return [{'label': label, 'count': count} for label, count in sorted_groups]


def _name_from_texts(texts: List[str]) -> str:
    """
    Menghasilkan nama kelompok utuh yang mencerminkan:
    [Kata Benda/Subjek] + [Kata Sifat/Keterangan/Keadaan]
    Secara tegas melarang kata berulang/kembar (misal 'Kapasitor Kapasitor', 'Sensor Sensor', 'Baterai Battery').
    """
    pair_scores = {}
    unigram_freq = {}

    for t in texts:
        clean = _clean_words(t)
        for w in clean:
            unigram_freq[w] = unigram_freq.get(w, 0) + 1

        # Hitung bigram berurutan (prioritas tinggi karena urutan alami subjek + keadaan)
        for i in range(len(clean) - 1):
            w1, w2 = clean[i], clean[i+1]
            if w1 == w2:
                continue
            pair = (w1, w2)
            pair_scores[pair] = pair_scores.get(pair, 0) + 3

        # Hitung co-occurrence pasangan kata beda dalam 1 laporan
        unique_words = list(dict.fromkeys(clean))
        for i in range(len(unique_words)):
            for j in range(i + 1, len(unique_words)):
                w1, w2 = unique_words[i], unique_words[j]
                if w1 == w2:
                    continue
                pair = (w1, w2)
                pair_scores[pair] = pair_scores.get(pair, 0) + 1

    # Prioritas 1: Pasangan 2 kata BERBEDA dengan skor tertinggi
    if pair_scores:
        best_pair = sorted(pair_scores.items(), key=lambda x: x[1], reverse=True)[0][0]
        return f"{best_pair[0].capitalize()} {best_pair[1].capitalize()}"

    # Prioritas 2: Jika hanya ada unigram tunggal
    if unigram_freq:
        top_words = sorted(unigram_freq.items(), key=lambda x: x[1], reverse=True)
        w1 = top_words[0][0].capitalize()
        if len(top_words) >= 2:
            w2 = top_words[1][0].capitalize()
            if w1.lower() != w2.lower():
                return f"{w1} {w2}"
        return w1

    return "Tanpa Keterangan"
