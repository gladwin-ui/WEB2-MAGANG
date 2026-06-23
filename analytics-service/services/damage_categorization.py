# Damage categorization based on root cause and repair action text

CATEGORIES = {
    "Hubungan Pendek/Short Circuit": [
        'short', 'hubung singkat', 'arus pendek', 'konslet', 'terbakar', 
        'meledak', 'circuit', 'hubung singkat', 'short circuit'
    ],
    "Overheat/Panas Berlebih": [
        'panas', 'overheat', 'melting', 'meleleh', 'suhu', 'heat', 
        'thermal', 'cooling', 'kipas'
    ],
    "Korosi/Kelembapan": [
        'lembap', 'korosi', 'corrosion', 'air', 'basah', 'humidity', 
        'rust', 'berembun', 'embun', 'air hujan'
    ],
    "Kesalahan Pemasangan": [
        'pemasangan', 'salah pasang', 'kabel tertukar', 'installation', 
        'wiring', 'longgar', 'kendor', 'salah sambung', 'salah rakit', 
        'connector longgar', 'human error'
    ],
    "Kualitas Komponen": [
        'kualitas', 'cacat pabrik', 'defect', 'retak', 'kembung', 'pecah', 
        'solderan retak', 'cold solder', 'resoldering', 'solderan', 'solder'
    ]
}

def categorize_damage(root_cause: str, repair_action: str) -> str:
    combined_text = f"{root_cause or ''} {repair_action or ''}".lower()
    
    # Track match counts for each category
    scores = {}
    for category, keywords in CATEGORIES.items():
        score = sum(1 for kw in keywords if kw in combined_text)
        if score > 0:
            scores[category] = score
            
    if not scores:
        return "Lain-lain"
        
    # Return the category with highest match score
    return max(scores, key=scores.get)
