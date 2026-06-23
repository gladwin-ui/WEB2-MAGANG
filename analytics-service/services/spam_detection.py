import re

SPAM_KEYWORDS = [
    'test', 'testing', 'asdf', 'xxx', 'qwerty', 'coba', 'dummy', 'placeholder',
    'aaaa', 'bbbb', 'cccc', 'dddd', 'eeee', 'ffff', 'gggg', 'hhhh', 'iiii',
    'jjjj', 'kkkk', 'llll', 'mmmm', 'nnnn', 'oooo', 'pppp', 'qqqq', 'rrrr',
    'ssss', 'tttt', 'uuuu', 'vvvv', 'wwww', 'xxxx', 'yyyy', 'zzzz'
]

def is_spam_report(text: str) -> tuple[bool, str | None]:
    if not text or not isinstance(text, str):
        return True, "Deskripsi kosong atau tidak valid"
        
    text_stripped = text.strip()
    
    # 1. Check for too short description
    if len(text_stripped) < 5:
        return True, "Deskripsi terlalu pendek (minimal 5 karakter)"
        
    # 2. Check for placeholder keywords (exact or substring matching)
    text_lower = text_stripped.lower()
    for kw in SPAM_KEYWORDS:
        if kw == text_lower or (len(kw) >= 4 and kw in text_lower):
            return True, f"Mengandung placeholder atau kata tidak substantif: '{kw}'"
            
    # 3. Check for repetitive character patterns (e.g., aaaaaaa, eeeeeee, ssssss)
    # Match any character repeated 4 or more times continuously
    rep_char_match = re.search(r'(.)\1{3,}', text_lower)
    if rep_char_match:
        return True, f"Terdeteksi pengulangan karakter berlebih: '{rep_char_match.group(0)}'"
        
    # 4. Check for repeating word/syllable pattern (e.g., desdsedsedseds, dsedsedse, kokokokoko)
    # Check if a 2-4 char pattern repeats 3 or more times
    rep_pattern_match = re.search(r'(.{2,4})\1{2,}', text_lower)
    if rep_pattern_match:
        return True, f"Terdeteksi pola kata berulang: '{rep_pattern_match.group(0)}'"
        
    # 5. Check character diversity ratio (unique chars vs length)
    # For very repetitive/gibberish typing, unique characters count is very low
    if len(text_lower) >= 10:
        unique_chars = len(set(text_lower))
        ratio = len(text_lower) / unique_chars
        # If ratio of length to unique chars is greater than 4.5 and text is long, it's suspicious
        # unless it is standard words. Let's make it safe: ratio > 6.0
        if ratio > 6.0 and unique_chars <= 4:
            return True, f"Keberagaman karakter terlalu rendah (mencurigakan)"
            
    return False, None
