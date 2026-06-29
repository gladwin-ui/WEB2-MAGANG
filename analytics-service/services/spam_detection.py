import time
import urllib.request
import json
import re
from typing import Optional, Tuple

def is_spam_report(text: str, api_key: Optional[str], model_name: Optional[str] = "gemini-2.0-flash-lite") -> Tuple[bool, Optional[str], Optional[float]]:
    """
    Detect spam in bug report with retry logic & confidence scoring.
    
    Returns: (is_spam: bool, spam_reason: Optional[str], confidence: Optional[float])
    """
    if not text or not isinstance(text, str):
        return True, "Teks laporan kosong atau tidak valid", 0.95
    
    text = text.strip()
    if len(text) < 5:
        return True, "Teks terlalu pendek (kemungkinan test data)", 0.90
    
    if len(text) > 5000:
        return True, "Teks terlalu panjang (kemungkinan dump)", 0.90
    
    if not api_key or api_key == "your-gemini-api-key-here" or api_key.strip() == "":
        return False, None, None

    # Improved prompt (manufacturing-specific)
    prompt = (
        "You are manufacturing bug report auditor for PT Hariff. "
        "Analyze if this is SPAM or GENUINE defect.\n\n"
        
        "SPAM includes:\n"
        "1. Test/dummy data: 'test','testing','coba','asdf','123',repeated chars\n"
        "2. Off-topic: Sales,promotions,unrelated services\n"
        "3. URL/Link spam: shortened URLs,suspicious domains\n"
        "4. Promotional: 'Join group','Click here','Limited offer'\n"
        "5. Gibberish: No coherent sentence,random chars\n\n"
        
        "GENUINE bugs describe: Component defects,symptoms,environment,reproduction steps\n\n"
        
        f"Report:\n{text}\n\n"
        
        "JSON only:\n{\"is_spam\": boolean, \"confidence\": 0.0-1.0, \"reason\": \"Indonesian\"}"
    )

    if not model_name or model_name.strip() == "":
        model_name = "gemini-2.0-flash-lite"

    url = f"https://generativelanguage.googleapis.com/v1beta/models/{model_name}:generateContent?key={api_key}"
    headers = {"Content-Type": "application/json"}
    payload = {"contents": [{"parts": [{"text": prompt}]}]}
    
    # Retry logic with exponential backoff
    max_retries = 3
    for attempt in range(max_retries):
        try:
            req = urllib.request.Request(
                url,
                data=json.dumps(payload).encode("utf-8"),
                headers=headers,
                method="POST"
            )
            
            with urllib.request.urlopen(req, timeout=10) as response:
                res_data = json.loads(response.read().decode("utf-8"))
                candidates = res_data.get("candidates", [])
                
                if candidates:
                    text_response = candidates[0]["content"]["parts"][0]["text"].strip()
                    if text_response.startswith("```"):
                        text_response = re.sub(r"^```(?:json)?\n|```$", "", text_response, flags=re.MULTILINE).strip()
                    
                    parsed = json.loads(text_response)
                    is_spam = bool(parsed.get("is_spam", False))
                    confidence = float(parsed.get("confidence", 0.5))
                    spam_reason = parsed.get("reason")
                    
                    return is_spam, spam_reason, confidence
        
        except urllib.error.HTTPError as e:
            if e.code == 429:  # Rate limit
                if attempt < max_retries - 1:
                    wait_time = 2 ** attempt  # 1s, 2s, 4s
                    print(f"Rate limited (429). Retry {attempt+1}/{max_retries} after {wait_time}s")
                    time.sleep(wait_time)
                    continue
                else:
                    print(f"Rate limited after {max_retries} retries")
                    return False, "API rate limited", 0.0
            
            elif e.code == 404:
                print(f"Endpoint not found (404). Check API key & URL")
                return False, "Endpoint invalid", 0.0
            
            elif e.code == 401:
                print(f"Unauthorized (401). API key invalid/expired")
                return False, "API key invalid", 0.0
            
            else:
                print(f"HTTP {e.code}: {e.reason}")
                if attempt < max_retries - 1:
                    time.sleep(2 ** attempt)
                    continue
                return False, f"HTTP {e.code} error", 0.0
        
        except Exception as e:
            print(f"Error calling Gemini API: {e}")
            if attempt < max_retries - 1:
                time.sleep(2 ** attempt)
                continue
            return False, str(e), 0.0
    
    return False, None, None
