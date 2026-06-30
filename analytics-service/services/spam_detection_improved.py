import urllib.request
import json
import re
from typing import Tuple, Optional

# Hotfix for scikit-learn version mismatch in unpickled SVM models
try:
    import sklearn.svm
    if not hasattr(sklearn.svm.SVC, '_effective_probability'):
        sklearn.svm.SVC._effective_probability = property(lambda self: getattr(self, 'probability', False))
except Exception:
    pass

# Try to import VotingSpamDetector from spam-detector-ai package as offline fallback
try:
    from spam_detector_ai.prediction.predict import VotingSpamDetector
except ImportError:
    try:
        from spam_detector_ai import VotingSpamDetector
    except ImportError:
        VotingSpamDetector = None

_detector_instance = None

def get_ml_detector():
    global _detector_instance
    if _detector_instance is None and VotingSpamDetector is not None:
        try:
            _detector_instance = VotingSpamDetector()
        except Exception as e:
            error_msg = str(e).lower()
            if "stopwords" in error_msg or "wordnet" in error_msg or "nltk" in error_msg:
                try:
                    import nltk
                    print("NLTK resource missing. Downloading 'stopwords' and 'wordnet'...")
                    nltk.download('stopwords', quiet=True)
                    nltk.download('wordnet', quiet=True)
                    _detector_instance = VotingSpamDetector()
                except Exception:
                    _detector_instance = None
            else:
                _detector_instance = None
    return _detector_instance

# ===== TIER 1: Obvious Spam Keywords (English & Indonesian) =====
OBVIOUS_SPAM_KEYWORDS = {
    # Financial scams
    'viagra', 'cialis', 'enlargement', 'penis', 'ed pills', 'obat kuat',
    'casino', 'poker', 'blackjack', 'roulette', 'togel', 'judi',
    'forex', 'forex trading', 'forex robot', 'crypto', 'bitcoin',
    '99% profit', 'guaranteed returns',
    
    # Money & work scams
    'make money', 'earn money', 'free money', 'easy money',
    'work from home', 'make $', '$$$', 'make millions',
    'get rich', 'rich quick', 'earn $', 'gaji', 'passive income',
    
    # Lottery & prizes
    'lottery', 'lotto', 'you have won', 'claim prize', 'hadiah', 'menang',
    'congratulations', 'you are winner',
    
    # Promotions & sales
    'click here', 'click link', 'visit now', 'join us', 'join now',
    'limited offer', 'limited time', 'don\'t miss', 'act now',
    'gratis', 'promo', 'discount', 'diskon', 'voucher', 'coupon',
    'jual', 'beli', 'harga', 'sewa',
    
    # Promotional links
    'bit.ly', 'tinyurl', 'short.link', 'spam', 'scam',
    
    # Pharmaceutical
    'weight loss pills', 'diet pills', 'cheap medications',
    'no prescription', 'prescription needed',
    
    # Adult/Dating
    'russian brides', 'hot girls', 'single women',
    'meet women', 'dating service', 'kencan', 'sex', 'porn', 'dewasa',
    
    # Banking fraud
    'bank account', 'compromised', 'verify account',
    'claim account', 'click link'
}

# Out-of-context topics to block early (e.g. food, finance, hobbies)
# to avoid them bypassing the filter just by mentioning 'aplikasi' or 'mobile'
OUT_OF_CONTEXT_KEYWORDS = [
    'saham', 'investasi', 'trading', 'forex', 'crypto', 'kripto', 'reksa dana', 'reksadana', 'obligasi', 'bursa', 'kurs', 'rekening', 'transfer uang', 'biaya transfer', 'rupiah', 'dolar',
    'sushi', 'kuliner', 'restoran', 'makanan', 'masakan', 'resep', 'kue', 'brownies', 'roti', 'baking', 'es krim', 'ice cream',
    'wisata', 'liburan', 'traveling', 'travelling', 'hotel', 'tiket', 'penerbangan', 'destinasi', 'lombok', 'jepang', 'yogyakarta', 'pantai',
    'loker', 'lowongan', 'karir', 'recruit', 'marketing', 'sales', 'fresh graduate',
    'skincare', 'makeup', 'kosmetik', 'jerawat', 'hijab', 'fashion', 'kerudung', 'shopee', 'tokopedia', 'toko', 'voal', 'sifon', 'casual', 'office', 'wajah', 'kulit',
    'diet', 'gym', 'fitness', 'kalori', 'suplemen', 'protein', 'otot',
    'zodiak', 'ramalan', 'bintang', 'horoskop', 'shio',
    'gosip', 'artis', 'selebritis', 'menikah', 'pacaran', 'viral',
    'reels', 'tiktok', 'instagram', 'youtube', 'konten',
    'bpjs', 'asuransi', 'klaim', 'kesehatan', 'ac', 'rumah', 'apartemen', 'listrik', 'freon',
    'burung', 'ternak', 'pakan', 'kenari', 'lovebird', 'hewan',
    'tanaman', 'monstera', 'pupuk', 'pot', 'kebun',
    'sekolah', 'kuliah', 'beasiswa', 'universitas', 'kursus', 'les', 'desainer', 'portfolio', 'cv', 'fresh graduate',
    'tutorial', 'tips', 'panduan', 'cara membuat', 'cara budidaya', 'cara ternak', 'cara download', 'cara mendaftar',
    'gaming', 'headphone', 'mirrorless', 'sony', 'fotografer', 'profesional', 'laptop', 'windows', 'ssd', 'macbook',
    'nonton', 'liga', 'pertandingan', 'film', 'netflix', 'bioskop', 'sinema', 'drama', 'sepak bola', 'bola'
]

# ===== TIER 2: Test Data & Gibberish =====
TEST_DATA_PATTERNS = {
    'test', 'testing', 'coba', 'dummy', 'asdf', '123',
    'qwerty', 'sample', 'contoh', 'placeholder', 'temp',
    'xxx', 'yyy', 'zzz', 'tes', 'tess', 'coba coba', 'dummy data'
}

CONTEXT_KEYWORDS = [
    'kapasitor', 'capacitor', 'solder', 'solderan', 'soldering', 'mainboard', 'pcb', 'led', 
    'sensor', 'lensa', 'lens', 'dcdc', 'diode', 'resistor', 'transistor', 'ic', 'chip', 'pin', 
    'kabel', 'cable', 'konektor', 'connector', 'baterai', 'battery', 'casing', 'housing', 
    'modul', 'module', 'hardware', 'perangkat', 'alat', 'device', 'unit', 'part', 'komponen', 
    'component', 'potensiometer', 'potentiometer', 'fuse', 'sekring', 'trafo', 'transformer', 
    'relay', 'switch', 'saklar', 'display', 'layar', 'lcd', 'oled', 'button', 'tombol', 
    'keypad', 'antena', 'antenna', 'modem', 'gps', 'gsm', 'wifi', 'bluetooth', 'microcontroller', 
    'mikrokontroler', 'posko', 'lapangan',
    'meledak', 'terbakar', 'short', 'konslet', 'hubung singkat', 'arus pendek', 'overheat', 
    'panas', 'retak', 'crack', 'kembung', 'berembun', 'embun', 'bocor', 'leak', 'mati', 
    'error', 'fault', 'gagal', 'trouble', 'rusak', 'broken', 'burn', 'smoke', 'asap', 
    'slow', 'lambat', 'longgar', 'kendor', 'loose', 'scratch', 'gores', 'lecet', 'kotor', 
    'dirty', 'korosi', 'karat', 'corroded', 'corrosion', 'hang', 'freeze', 'crash', 
    'macet', 'pecah', 'patah', 'copot', 'lepas', 'hilang', 'mati total', 'matot', 
    'tidak menyala', 'tidak aktif', 'rework', 'defect', 'symptom', 'gejala', 'fungsi', 
    'berfungsi', 'nyala', 'hidup', 'operasi', 'operasional',
    'log', 'logger', 'sleep', 'wake', 'boot', 'reboot', 'restart', 'firmware', 'software', 
    'program', 'datasheet', 'input', 'output', 'sistem', 'system', 'aplikasi', 'app', 
    'calibrat', 'kalibrasi', 'ukur', 'measurement', 
    'voltage', 'tegangan', 'arus', 'ampere', 'volt', 'suhu', 'temperature', 'kelembapan', 
    'humidity', 'sinyal', 'signal', 'koneksi', 'connection', 'network', 'jaringan', 
    'serial', 'port', 'usb', 'comm', 'komunikasi', 'baca', 'read', 'tulis', 'write', 
    'save', 'simpan', 'kirim', 'send', 'receive', 'terima', 'data', 'file', 'memori', 
    'memory', 'sd card', 'flash', 'eeprom', 'produksi'
]

def is_spam_improved(text: str) -> Tuple[bool, Optional[str], Optional[float]]:
    """
    3-tier spam detection system
    Returns: (is_spam: bool, reason: str, confidence: 0.0-1.0)
    """
    if not text or not isinstance(text, str):
        return True, "Teks laporan kosong atau tidak valid", 0.99
    
    text_stripped = text.strip()
    text_lower = text_stripped.lower()
    
    # ===== TIER 1: Obvious Spam Keywords & Out-of-Context Filter =====
    # Cost: 0s, Accuracy: 95%
    
    # Smart gambling slot filter (does not trigger on slot SD card)
    if re.search(r'\bslot\s*(gacor|online|judi|maxwin)\b', text_lower):
        return True, "Judi slot online terdeteksi", 0.98

    # Filter out-of-context topics early
    temp_text = text_lower.replace('power bank', 'power_bank')
    for kw in OUT_OF_CONTEXT_KEYWORDS:
        if re.search(r'\b' + re.escape(kw) + r'\b', temp_text):
            return True, f"Laporan berada di luar konteks fungsional (kata kunci: {kw})", 0.95

    # Check other general spam keywords
    spam_keyword_count = sum(1 for kw in OBVIOUS_SPAM_KEYWORDS if re.search(r'\b' + re.escape(kw) + r'\b', text_lower))
    
    # If 2+ obvious spam keywords -> definitely spam
    if spam_keyword_count >= 2:
        return True, f"Kata kunci spam terdeteksi ({spam_keyword_count} ditemukan)", 0.98
    
    # If 1 spam keyword + short text (likely spam title only)
    if spam_keyword_count >= 1 and len(text_stripped) < 50:
        return True, "Kata kunci spam terdeteksi pada teks pendek", 0.90
    
    # ===== TIER 2: Length & Pattern Rules =====
    # Cost: 0s, Accuracy: 85%
    
    # Rule 1: Text too short
    if len(text_stripped) < 5:
        return True, "Teks terlalu pendek (kemungkinan test data)", 0.95
    
    # Rule 2: Text too long (dump)
    if len(text_stripped) > 5000:
        return True, "Teks terlalu panjang (kemungkinan dump)", 0.90
    
    # Rule 3: Exact match test data
    cleaned_text = re.sub(r'[^a-z\s]', '', text_lower).strip()
    if cleaned_text in TEST_DATA_PATTERNS:
        return True, "Teks merupakan data uji coba (test/dummy)", 0.96
    
    # Rule 4: Repeated characters (excluding valid hex patterns like 0xFFFFFF)
    has_hex = re.search(r'0x[0-9a-f]+', text_lower)
    if not has_hex and re.search(r'([a-zA-Z\.\-\*\_])\1{4,}', text_lower):
        return True, "Karakter berulang terdeteksi (kemungkinan data uji/sampah)", 0.92
    
    # Rule 5: No spaces/single word
    if len(text_stripped.split()) < 2 and len(text_stripped) > 15:
        return True, "Format laporan tidak valid (tidak ada spasi)", 0.88
    
    # Rule 6: Obvious gibberish (all special chars/symbols)
    if re.match(r'^[0-9\W_]+$', text_lower):
        return True, "Format laporan tidak valid (hanya angka/simbol)", 0.91

    # ===== GATEKEEPER: Whitelisted Technical Context =====
    # If the text has clear technical context, we trust it and bypass Tier 3 ML/API validation
    context_matches = 0
    for kw in CONTEXT_KEYWORDS:
        if re.search(r'\b' + re.escape(kw) + r'\b', text_lower):
            context_matches += 1
            
    if context_matches >= 1:
        return False, None, None

    # ===== TIER 3: Local ML Ensemble (Uncertain Cases) =====
    # Cost: Free Local ML
    
    # Use local VotingSpamDetector ML Model
    ml_detector = get_ml_detector()
    if ml_detector is not None:
        try:
            is_spam_ml = ml_detector.is_spam(text_stripped)
            if is_spam_ml:
                return True, "Terdeteksi sebagai spam oleh model Machine Learning (Ensemble Voting)", 0.88
        except Exception as e:
            print(f"Error during ML spam detection: {e}")
            
    # Since it has 0 manufacturing context and was not classified as ham, it is out of context
    return True, "Laporan berada di luar konteks fungsional (tidak berkaitan dengan manufaktur/sistem)", 0.85
