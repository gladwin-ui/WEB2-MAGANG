import urllib.request
import json
import re
from typing import Optional, Tuple

def is_spam_report(text: str, api_key: Optional[str]) -> Tuple[bool, Optional[str]]:
    if not text or not isinstance(text, str):
        return True, "Teks laporan kosong atau tidak valid"

    # If API Key is missing, empty, or the default placeholder, do not detect spam.
    if not api_key or api_key == "your-gemini-api-key-here" or api_key.strip() == "":
        return False, None

    url = f"https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key={api_key}"
    
    prompt = (
        "You are a manufacturing bug report auditor. Analyze the following bug report description written by internal factory staff:\n"
        f"\"\"\"\n{text}\n\"\"\"\n"
        "Determine if the report is spam. Spam includes:\n"
        "- Test data or dummy text (e.g., 'test', 'testing', 'coba', 'dummy', 'asdf', '123')\n"
        "- Random gibberish or repetitive keyboard typing (e.g., 'aaaa', 'qwerty', 'dsedsed')\n"
        "- Completely non-substantive or meaningless entries.\n\n"
        "Respond strictly in JSON format with two fields:\n"
        "- \"is_spam\": boolean\n"
        "- \"spam_reason\": string (a short explanation in Indonesian of why it is spam, or null if it is not spam).\n\n"
        "Do not include any markdown formatting (like ```json) or explanation outside the JSON."
    )

    headers = {
        "Content-Type": "application/json"
    }

    payload = {
        "contents": [{
            "parts": [{
                "text": prompt
            }]
        }]
    }

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
                # Clean up markdown code block wrapper if present
                if text_response.startswith("```"):
                    text_response = re.sub(r"^```(?:json)?\n|```$", "", text_response, flags=re.MULTILINE).strip()
                
                parsed = json.loads(text_response)
                is_spam = bool(parsed.get("is_spam", False))
                spam_reason = parsed.get("spam_reason")
                return is_spam, spam_reason
    except Exception as e:
        print(f"Error calling Gemini API for spam detection: {e}")
    
    return False, None
