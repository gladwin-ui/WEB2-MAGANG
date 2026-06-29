#!/usr/bin/env python3
"""Test spam detection accuracy with sample data"""

from services.spam_detection_improved import is_spam_improved
import sys

# 50 obvious spam samples
SPAM_SAMPLES = [
    "BUY VIAGRA ONLINE!!!",
    "CLICK HERE FOR FREE MONEY",
    "Make $5000 per week!!!",
    "!!!SPAM ALERT!!!",
    "www.spam-click.com VISIT NOW",
    "FREE IPHONE 15 CLICK HERE",
    "Earn cash from home immediately",
    "Get rich quick with this one simple trick",
    "Verify your bank account credentials now",
    "You have won a free $1000 Walmart gift card",
    "Increase your cryptocurrency wallet balance",
    "Invest $100 and get $1000 daily payout",
    "Special discount on weight loss pills",
    "Join this exclusive casino club for free spins",
    "Claim your free bitcoin bonus today",
    "Work 2 hours a day and earn a full-time salary",
    "Instant approval personal loans without credit check",
    "Cheap knockoff designer watches on sale",
    "Best price online for cialis and levitra",
    "Unlock restricted Netflix movies using this VPN",
    "This is not a drill! You won a sweepstakes prize",
    "Single ladies in your area are waiting for you",
    "Enlarge your body parts naturally and safely",
    "Make money while sleeping with autopilot trading",
    "Download cracked software and games for free",
    "Get thousands of free Instagram followers instantly",
    "Win a brand new Tesla Model 3 this weekend",
    "Lowest mortgage rates in history, apply now",
    "How to cure cancer with this secret herb",
    "Get a free degree from an accredited university",
    "Get paid for searching the web, register today",
    "Affordable prescription meds shipped directly to you",
    "Double your money in 24 hours guaranteed",
    "Free premium accounts for popular streaming sites",
    "Make easy passive income from your smartphone",
    "Get cheap airline tickets anywhere in the world",
    "No credit history? No problem! Buy a car now",
    "Learn how to hack facebook accounts easily",
    "Exclusive invitation to VIP investment program",
    "Buy high-quality fake degrees and diplomas online",
    "Get free energy using this simple generator device",
    "Lose 10 pounds in 3 days without dieting",
    "Earn daily payouts by simply typing captchas",
    "The government doesn't want you to know this secret",
    "Become a millionaire within 6 months starting now",
    "Cheap prescription glasses, buy 1 get 1 free",
    "Make money by reviewing local restaurants",
    "Claim your unclaimed property cash payout today",
    "Get your free credit report without registration",
    "Your social security number has been suspended, call now"
]

# 10 legitimate bug samples
LEGITIMATE_SAMPLES = [
    "Kapasitor meledak karena tegangan berlebih pada mainboard",
    "Solderan retak akibat thermal cycling di lingkungan panas",
    "Koneksi tidak stabil saat dilakukan pengujian lapangan di posko",
    "Data logger tidak menyimpan log aktivitas setelah siklus sleep-wake",
    "Indikator LED tidak menyala merah pada unit produk utama",
    "Ketidaksesuaian antara datasheet dan implementasi aktual jalur input",
    "DCDC short hubung singkat setelah dinyalakan selama 10 jam",
    "Lensa berembun saat digunakan di luar ruangan dengan kelembapan tinggi",
    "Data logger tidak menyimpan data sensor ke memori eksternal",
    "Overtemperature shutdown palsu akibat sensor suhu terlalu sensitif"
]

def test_spam_detection(api_key=None, model_name="Local Python Rule-Based & Context-Aware"):
    """Test accuracy on sample data"""
    
    print(f"\n{'='*50}")
    print(f"SPAM DETECTION TEST")
    print(f"Model: {model_name}")
    print(f"{'='*50}\n")
    
    # Test spam samples
    spam_detected = 0
    total_spam = len(SPAM_SAMPLES[:15])
    print(f"Testing {total_spam} spam samples...")
    for i, sample in enumerate(SPAM_SAMPLES[:15], 1):
        is_spam, reason, confidence = is_spam_improved(sample, api_key)
        status = "[OK]" if is_spam else "[FAIL]"
        if i <= 10:  # Print first 10 for display
            print(f"{status} Spam Sample {i}: {sample[:40]}... (confidence: {confidence})")
        if is_spam:
            spam_detected += 1
            
    print(f"\nSpam Detection Rate: {spam_detected}/{total_spam} ({round(spam_detected/total_spam*100, 2)}%)")
    
    # Test legitimate samples
    false_positives = 0
    total_legit = len(LEGITIMATE_SAMPLES)
    print(f"\n{'='*50}\n")
    print(f"Testing {total_legit} legitimate samples...")
    for i, sample in enumerate(LEGITIMATE_SAMPLES, 1):
        is_spam, reason, confidence = is_spam_improved(sample, api_key)
        status = "[OK]" if not is_spam else "[FAIL]"
        print(f"{status} Legit Sample {i}: {sample[:40]}... (is_spam: {is_spam})")
        if is_spam:
            false_positives += 1
            
    print(f"\nFalse Positive Rate: {false_positives}/{total_legit} ({round(false_positives/total_legit*100, 2)}%)")
    print(f"{'='*50}\n")
 
if __name__ == "__main__":
    api_key = sys.argv[1] if len(sys.argv) > 1 else None
    model_name = sys.argv[2] if len(sys.argv) > 2 else "Local Python Rule-Based & Context-Aware"
    
    test_spam_detection(api_key, model_name)
