# Severity recommendation service based on bug description keywords

CRITICAL_KEYWORDS = {
    'terbakar': "komponen terbakar/berasap",
    'meledak': "komponen meledak",
    'short': "hubung singkat/arus pendek",
    'hubung singkat': "hubung singkat/arus pendek",
    'bahaya': "potensi bahaya keselamatan",
    'ledakan': "kejadian ledakan/pecah ekstrem",
    'asap': "terdeteksi asap/panas ekstrem",
    'fire': "fire/burning component",
    'smoke': "smoke detected",
    'explosion': "explosion event"
}

MAJOR_KEYWORDS = {
    'retak': "keretakan fisik pada modul/PCB",
    'rusak': "modul/alat rusak fungsional",
    'kembung': "kapasitor/baterai kembung",
    'berembun': "lensa/kaca berembun mengganggu sensor",
    'bocor': "kebocoran cairan/arus",
    'mati': "alat mati total/tidak merespon",
    'gagal': "gagal operasi/sistem error",
    'error': "sistem software/hardware error",
    'fault': "terdeteksi fault code",
    'overheat': "suhu operasional melampaui batas (overheat)",
    'korosi': "kelembapan/korosi pada pin",
    'corroded': "corroded pins/pads"
}

MINOR_KEYWORDS = {
    'gores': "goresan ringan pada casing",
    'lecet': "lecet/cacat kosmetik",
    'kotor': "debu/kotoran eksternal",
    'longgar': "skrup/kabel eksternal agak longgar",
    'loose': "loose screw/connector",
    'scratch': "cosmetic scratch",
    'dirty': "dirty casing/glass exterior",
    'slow': "respon sedikit lambat",
    'tampilan': "minor UI/display alignment"
}

def recommend_severity(text: str) -> tuple[str, str]:
    if not text or not isinstance(text, str):
        return "Major", "Deskripsi kosong. Default ke Major."
        
    text_lower = text.lower()
    
    # Check Critical
    for kw, desc in CRITICAL_KEYWORDS.items():
        if kw in text_lower:
            return "Critical", f"AI merekomendasikan Critical karena terdeteksi indikasi '{desc}' ('{kw}')."
            
    # Check Major
    for kw, desc in MAJOR_KEYWORDS.items():
        if kw in text_lower:
            return "Major", f"AI merekomendasikan Major karena terdeteksi indikasi '{desc}' ('{kw}')."
            
    # Check Minor
    for kw, desc in MINOR_KEYWORDS.items():
        if kw in text_lower:
            return "Minor", f"AI merekomendasikan Minor karena terdeteksi indikasi '{desc}' ('{kw}')."
            
    # Default fallback
    return "Major", "Tidak ditemukan kata kunci tingkat keparahan spesifik. Rekomendasi default ke Major."
