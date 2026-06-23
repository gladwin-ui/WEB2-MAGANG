# Domain-specific sentiment analysis for manufacturing bug reports

NEG_KEYWORDS = [
    'short', 'terbakar', 'meledak', 'rusak', 'cacat', 'retak', 'kembung', 
    'berembun', 'bocor', 'overheat', 'panas', 'mati', 'error', 'fault', 
    'gagal', 'trouble', 'hubung singkat', 'retak', 'broken', 'burn', 'leak'
]

POS_KEYWORDS = [
    'aman', 'normal', 'berhasil', 'lancar', 'oke', 'bagus', 'berfungsi', 
    'diperbaiki', 'sukses', 'baik', 'lancar', 'clean', 'working'
]

def analyze_sentiment(text: str) -> tuple[str, float]:
    if not text or not isinstance(text, str):
        return "neutral", 0.0
        
    text_lower = text.lower()
    neg_count = sum(1 for kw in NEG_KEYWORDS if kw in text_lower)
    pos_count = sum(1 for kw in POS_KEYWORDS if kw in text_lower)
    
    total = neg_count + pos_count
    if total == 0:
        return "neutral", 0.0
        
    # score ranges from -1.0 to 1.0
    score = (pos_count - neg_count) / total
    
    if score < -0.2:
        label = "negative"
    elif score > 0.2:
        label = "positive"
    else:
        label = "neutral"
        
    return label, float(round(score, 2))
