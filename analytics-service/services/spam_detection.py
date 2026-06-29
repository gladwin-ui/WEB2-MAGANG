import re
from typing import Optional, Tuple

# Hotfix for scikit-learn version mismatch in unpickled SVM models
try:
    import sklearn.svm
    if not hasattr(sklearn.svm.SVC, '_effective_probability'):
        sklearn.svm.SVC._effective_probability = property(lambda self: getattr(self, 'probability', False))
except Exception:
    pass

# Try to import VotingSpamDetector from spam-detector-ai package
try:
    from spam_detector_ai.prediction.predict import VotingSpamDetector
except ImportError:
    try:
        from spam_detector_ai import VotingSpamDetector
    except ImportError:
        VotingSpamDetector = None

# Global detector instance for lazy loading
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
                    # Retry initialization after download
                    _detector_instance = VotingSpamDetector()
                    print("VotingSpamDetector successfully initialized after downloading NLTK resources.")
                except Exception as nltk_ex:
                    print(f"Warning: Failed to auto-download NLTK data: {nltk_ex}")
                    print(f"Warning: Failed to initialize VotingSpamDetector: {e}")
                    _detector_instance = None
            else:
                print(f"Warning: Failed to initialize VotingSpamDetector: {e}")
                _detector_instance = None
    return _detector_instance

# List of keywords indicating spam, off-topic, or promotions
SPAM_KEYWORDS = [
    # Commercial/Sales
    r'\bviagra\b', r'\bcialis\b', r'\blevitra\b', r'\bpill\b', r'\bpills\b', r'\bobat\b',
    r'\bdiet\b', r'\bweight loss\b', r'\bmurah\b', r'\bdiskon\b', r'\bdiscount\b', r'\bpromo\b',
    r'\bsale\b', r'\bbuy\b', r'\bcheap\b', r'\bshop\b', r'\bbelanja\b', r'\btoko\b', r'\bstore\b', r'\bjual\b', r'\bbeli\b', r'\bharga\b', r'\bsewa\b',
    r'\bvoucher\b', r'\bcoupon\b', r'\btravel\b', r'\bhotel\b', r'\btiket\b', r'\bticket\b',
    r'\bairline\b', r'\bwisata\b', r'\bliburan\b', r'\bcasinoclub\b',
    
    # Financial/Scams/Gambling
    r'\bmoney\b', r'\bcash\b', r'\brich\b', r'\bdollar\b', r'\brupiah\b', r'\bpayout\b', r'\bpayouts\b',
    r'\bearn\b', r'\bincome\b', r'\bgaji\b', r'\buang\b', r'\bdana\b', r'\bpinjaman\b', r'\bpinjol\b',
    r'\bkredit\b', r'\bcredit\b', r'\bbank\b', r'\brekening\b', r'\binvest\b', r'\binvestasi\b',
    r'\binvestment\b', r'\bbitcoin\b', r'\bcryptocurrency\b', r'\bcrypto\b', r'\bbtc\b', r'\bethereum\b',
    r'\btrading\b', r'\bsaham\b', r'\bcasino\b', r'\bslot\s*(gacor|online|judi|maxwin)\b', r'\bjudi\b', r'\btogel\b', r'\bfree spins\b',
    r'\bbonus\b', r'\bmenang\b', r'\bwin\b', r'\bprize\b', r'\bgift card\b', r'\bsweepstakes\b',
    r'\bjackpot\b', r'\bbetting\b', r'\bpassive income\b',
    
    # Adult/Social Media
    r'\bfollower\b', r'\bfollowers\b', r'\blike\b', r'\bsubscribe\b', r'\binstagram\b', r'\bfacebook\b',
    r'\btiktok\b', r'\byoutube\b', r'\bmedsos\b', r'\bsingle ladies\b', r'\bdating\b', r'\bkencan\b',
    r'\bsex\b', r'\badult\b', r'\bporn\b', r'\bdewasa\b', r'\benlarge\b',
    
    # Job/Academics
    r'\bwork from home\b', r'\btyping captchas\b', r'\bjob\b', r'\bcareer\b', r'\bkarir\b', r'\bloker\b',
    r'\blowongan kerja\b', r'\bdegree\b', r'\bdiploma\b', r'\bijazah\b', r'\bkuliah\b', r'\buniversitas\b',
    
    # Other spam/malicious
    r'\bhack\b', r'\bcracked\b', r'\bnetflix\b', r'\bvpn\b', r'\bfree energy\b', r'\bcancer cure\b',
    r'\bherbs\b', r'\bherb\b'
]

# List of out-of-context topics to block early (e.g. food, finance, hobbies)
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

# List of keywords indicating genuine manufacturing/technical context
CONTEXT_KEYWORDS = [
    # Physical/Electrical Components
    'kapasitor', 'capacitor', 'solder', 'solderan', 'soldering', 'mainboard', 'pcb', 'led', 
    'sensor', 'lensa', 'lens', 'dcdc', 'diode', 'resistor', 'transistor', 'ic', 'chip', 'pin', 
    'kabel', 'cable', 'konektor', 'connector', 'baterai', 'battery', 'casing', 'housing', 
    'modul', 'module', 'hardware', 'perangkat', 'alat', 'device', 'unit', 'part', 'komponen', 
    'component', 'potensiometer', 'potentiometer', 'fuse', 'sekring', 'trafo', 'transformer', 
    'relay', 'switch', 'saklar', 'display', 'layar', 'lcd', 'oled', 'button', 'tombol', 
    'keypad', 'antena', 'antenna', 'modem', 'gps', 'gsm', 'wifi', 'bluetooth', 'microcontroller', 
    'mikrokontroler', 'posko', 'lapangan',
    
    # Defect Symptoms / Physical changes
    'meledak', 'terbakar', 'short', 'konslet', 'hubung singkat', 'arus pendek', 'overheat', 
    'panas', 'retak', 'crack', 'kembung', 'berembun', 'embun', 'bocor', 'leak', 'mati', 
    'error', 'fault', 'gagal', 'trouble', 'rusak', 'broken', 'burn', 'smoke', 'asap', 
    'slow', 'lambat', 'longgar', 'kendor', 'loose', 'scratch', 'gores', 'lecet', 'kotor', 
    'dirty', 'korosi', 'karat', 'corroded', 'corrosion', 'hang', 'freeze', 'crash', 
    'macet', 'pecah', 'patah', 'copot', 'lepas', 'hilang', 'mati total', 'matot', 
    'tidak menyala', 'tidak aktif', 'rework', 'defect', 'symptom', 'gejala', 'fungsi', 
    'berfungsi', 'nyala', 'hidup', 'jalan', 'running', 'operasi', 'operasional',
    
    # Systems / Software / Testing
    'log', 'logger', 'sleep', 'wake', 'boot', 'reboot', 'restart', 'firmware', 'software', 
    'program', 'datasheet', 'input', 'output', 'sistem', 'system', 'aplikasi', 'app', 
    'uji', 'pengujian', 'testing', 'test', 'calibrat', 'kalibrasi', 'ukur', 'measurement', 
    'voltage', 'tegangan', 'arus', 'ampere', 'volt', 'suhu', 'temperature', 'kelembapan', 
    'humidity', 'sinyal', 'signal', 'koneksi', 'connection', 'network', 'jaringan', 
    'serial', 'port', 'usb', 'comm', 'komunikasi', 'baca', 'read', 'tulis', 'write', 
    'save', 'simpan', 'kirim', 'send', 'receive', 'terima', 'data', 'file', 'memori', 
    'memory', 'sd card', 'flash', 'eeprom', 'produksi'
]

def is_spam_report(text: str, api_key: Optional[str] = None, model_name: Optional[str] = None) -> Tuple[bool, Optional[str], Optional[float]]:
    """
    Detect spam in bug report with hybrid logic: Machine Learning (spam-detector-ai) + local context rules.
    
    Returns: (is_spam: bool, spam_reason: Optional[str], confidence: Optional[float])
    """
    if not text or not isinstance(text, str):
        return True, "Teks laporan kosong atau tidak valid", 0.95
    
    text_stripped = text.strip()
    if len(text_stripped) < 5:
        return True, "Teks terlalu pendek (kemungkinan test data)", 0.90
    
    if len(text_stripped) > 5000:
        return True, "Teks terlalu panjang (kemungkinan dump)", 0.90
        
    text_lower = text_stripped.lower()
    
    # 1. Repeated characters or gibberish check (ignoring hex numbers like 0xFFFFFF)
    has_hex = re.search(r'0x[0-9a-f]+', text_lower)
    if not has_hex and re.search(r'([a-zA-Z\.\-\*\_])\1{4,}', text_lower):
        return True, "Karakter berulang terdeteksi (kemungkinan data uji/sampah)", 0.90
        
    # Only digits or symbols
    if re.match(r'^[0-9\W_]+$', text_lower):
        return True, "Format laporan tidak valid (hanya angka/simbol)", 0.90

    # 2. Standard dummy/test words
    cleaned_text = re.sub(r'[^a-z\s]', '', text_lower).strip()
    test_words = {'test', 'testing', 'coba', 'cobain', 'asdf', 'qwerty', 'dummy', 'hello world', 'tes', 'tess', 'coba coba', 'dummy data'}
    if cleaned_text in test_words or any(word == cleaned_text for word in test_words):
        return True, "Teks merupakan data uji coba (test/dummy)", 0.95

    # 3. URL and link check
    has_url = re.search(r'https?://\S+|www\.\S+|\b\w+\.(com|net|org|info|xyz|biz|cc|co\.id)\b', text_lower)
    has_click_call = any(phrase in text_lower for phrase in ['click here', 'klik disini', 'visit now', 'kunjungi', 'daftar sekarang', 'register now', 'join now', 'gabung sekarang'])
    if has_url or has_click_call:
        return True, "Tautan/URL atau ajakan promosi terdeteksi", 0.95

    # 3b. Out-of-context check (filtering out general Indonesian spam themes early)
    # We replace "power bank" with "power_bank" to avoid "bank" from triggering a false positive
    temp_text = text_lower.replace('power bank', 'power_bank')
    for kw in OUT_OF_CONTEXT_KEYWORDS:
        if re.search(r'\b' + re.escape(kw) + r'\b', temp_text):
            return True, f"Laporan berada di luar konteks fungsional (kata kunci: {kw})", 0.95

    # 4. Context-Aware check (verify if it has ANY relevant manufacturing/technical keyword)
    # Using word boundaries (\b) to avoid partial matches (e.g. 'ic' matching in 'trick' or 'quick')
    context_matches = 0
    for kw in CONTEXT_KEYWORDS:
        if re.search(r'\b' + re.escape(kw) + r'\b', text_lower):
            context_matches += 1
    
    # If the text has technical manufacturing context, we trust it and bypass the general ML model
    # (since the ML model is trained on English spam and yields a high false-positive rate on Indonesian tech terms)
    if context_matches >= 1:
        return False, None, None

    # 5. Machine Learning Spam Detection via spam-detector-ai package
    # (Only runs if the text has ZERO manufacturing context)
    ml_detector = get_ml_detector()
    if ml_detector is not None:
        try:
            is_spam_ml = ml_detector.is_spam(text_stripped)
            if is_spam_ml:
                return True, "Terdeteksi sebagai spam oleh model Machine Learning (Ensemble Voting)", 0.88
        except Exception as e:
            print(f"Error during ML spam detection: {e}")

    # 6. Local Rule-Based Keyword Check (as secondary classifier for out-of-context text)
    spam_matches = []
    for pattern in SPAM_KEYWORDS:
        if re.search(pattern, text_lower):
            spam_matches.append(pattern)
            
    if len(spam_matches) > 0:
        matched_keywords = ", ".join([p.replace(r'\b', '') for p in spam_matches[:3]])
        return True, f"Terdeteksi kata kunci promosi/iklan/spam ({matched_keywords})", 0.90

    # Since it has 0 manufacturing context and was not classified as ham, it is out of context
    return True, "Laporan berada di luar konteks fungsional (tidak berkaitan dengan manufaktur/sistem)", 0.85

