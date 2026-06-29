#!/usr/bin/env python3
"""Verify Gemini API endpoint & key validity"""

import urllib.request
import urllib.error
import json
import sys

def verify_gemini_api(api_key, model_name="gemini-2.0-flash-lite"):
    """Test if Gemini API key + endpoint working"""
    
    if len(api_key) < 30:
        return False, f"Invalid key format (too short: {len(api_key)} chars)"
    
    if not model_name:
        model_name = "gemini-2.0-flash-lite"
        
    endpoint = f"https://generativelanguage.googleapis.com/v1beta/models/{model_name}:generateContent?key={api_key}"
    payload = {
        "contents": [{
            "parts": [{
                "text": "Test: Is 'BUY VIAGRA' spam? Return JSON: {\"is_spam\": boolean}"
            }]
        }]
    }
    
    try:
        req = urllib.request.Request(
            endpoint,
            data=json.dumps(payload).encode('utf-8'),
            headers={'Content-Type': 'application/json'},
            method='POST'
        )
        
        response = urllib.request.urlopen(req, timeout=10)
        response_text = response.read().decode('utf-8')
        
        result = json.loads(response_text)
        
        if "candidates" in result and len(result["candidates"]) > 0:
            return True, "API working (200 OK)", result
        else:
            return False, "Invalid response format", result
    
    except urllib.error.HTTPError as e:
        if e.code == 401:
            return False, "API key invalid/expired (401)", None
        elif e.code == 404:
            return False, "Endpoint not found (404)", None
        elif e.code == 429:
            return False, "Rate limited (429) - Valid key but quota exceeded", None
        else:
            return False, f"HTTP {e.code}: {e.reason}", None
    
    except Exception as e:
        return False, f"Error: {str(e)}", None

if __name__ == "__main__":
    api_key = sys.argv[1] if len(sys.argv) > 1 else None
    model_name = sys.argv[2] if len(sys.argv) > 2 else "gemini-2.0-flash-lite"
    
    if not api_key:
        print("Usage: python3 verify_api.py YOUR_API_KEY [MODEL_NAME]")
        sys.exit(1)
    
    success, message, response = verify_gemini_api(api_key, model_name)
    
    print(f"\n{'='*50}")
    print(f"GEMINI API VERIFICATION")
    print(f"Model: {model_name}")
    print(f"{'='*50}")
    print(f"Status: {'VALID' if success else 'INVALID'}")
    print(f"Message: {message}")
    
    if response:
        print(f"\nResponse: {json.dumps(response, indent=2)}")
    
    print(f"{'='*50}\n")
    
    sys.exit(0 if success else 1)
