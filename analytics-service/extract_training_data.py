"""
STEP 1: Extract Training Data dari Database
=============================================
Jalankan script ini di komputer lokal Anda (tempat MySQL database berjalan).

Cara pakai:
  cd analytics-service
  python extract_training_data.py

Output: training_data.csv (upload ke Google Colab nanti)

PENTING: Pastikan Anda sudah import SQL test files ke database:
  - bug_spam_testing_100rows.sql (idbug 1057-1156)
  - bug_testing_70normal_30spam_vacation.sql (idbug 1300-1399)
"""

import csv
import sys

# ============================================================
# KONFIGURASI DATABASE - Sesuaikan dengan setup Anda
# ============================================================
DB_CONFIG = {
    "host": "127.0.0.1",
    "user": "root",
    "password": "",          # Isi password MySQL Anda
    "database": "bugtrack_mfg" # Nama database Anda
}

# ============================================================
# LABEL MAPPING
# Sesuaikan ID range dengan data yang sudah Anda import
# ============================================================
SPAM_ID_RANGES = [
    # (start_id, end_id, label, description)
    # 0 = normal/genuine, 1 = spam
    
    # Dari bug_spam_testing_100rows.sql
    (1057, 1106, 0, "Normal manufacturing bugs"),
    (1107, 1156, 1, "Obvious spam (VIAGRA, CASINO, etc)"),
    
    # Dari bug_testing_70normal_30spam_vacation.sql
    (1300, 1369, 0, "Normal manufacturing bugs"),
    (1370, 1399, 1, "Vacation/story spam"),
]

# ============================================================
# TAMBAHAN: Data existing di database (opsional)
# Jika Anda punya data bug ASLI dari PT Hariff, tambahkan range-nya
# Semua data asli biasanya normal (label 0)
# ============================================================
# Uncomment dan sesuaikan jika ada data real:
# SPAM_ID_RANGES.append((1, 1056, 0, "Real PT Hariff bug data"))


def extract_with_mysql_connector():
    """Extract menggunakan mysql-connector-python"""
    try:
        import mysql.connector
    except ImportError:
        print("Installing mysql-connector-python...")
        import subprocess
        subprocess.check_call([sys.executable, "-m", "pip", "install", "mysql-connector-python"])
        import mysql.connector
    
    conn = mysql.connector.connect(**DB_CONFIG)
    cursor = conn.cursor()
    
    all_data = []
    
    for start_id, end_id, label, desc in SPAM_ID_RANGES:
        query = """
            SELECT 
                id,
                COALESCE(title, '') as bug_title,
                COALESCE(description, '') as bugdesc,
                COALESCE(environment, '') as bugenvi,
                COALESCE(reported_by, '') as bugcreatedby
            FROM bugs
            WHERE id BETWEEN %s AND %s
        """
        cursor.execute(query, (start_id, end_id))
        rows = cursor.fetchall()
        
        for row in rows:
            bug_id, title, desc_text, env, created_by = row
            # Gabungkan semua field teks menjadi 1 string
            combined = f"{title} {desc_text} {env}".strip()
            if combined:
                all_data.append({
                    "id": bug_id,
                    "text": combined,
                    "is_spam": label,
                    "source": desc
                })
        
        print(f"  [{desc}] ID {start_id}-{end_id}: {len(rows)} rows (label={label})")
    
    cursor.close()
    conn.close()
    
    return all_data


def extract_with_pymysql():
    """Fallback: Extract menggunakan pymysql"""
    try:
        import pymysql
    except ImportError:
        print("Installing pymysql...")
        import subprocess
        subprocess.check_call([sys.executable, "-m", "pip", "install", "pymysql"])
        import pymysql
    
    conn = pymysql.connect(**DB_CONFIG)
    cursor = conn.cursor()
    
    all_data = []
    
    for start_id, end_id, label, desc in SPAM_ID_RANGES:
        query = """
            SELECT 
                id,
                COALESCE(title, '') as bug_title,
                COALESCE(description, '') as bugdesc,
                COALESCE(environment, '') as bugenvi,
                COALESCE(reported_by, '') as bugcreatedby
            FROM bugs
            WHERE id BETWEEN %s AND %s
        """
        cursor.execute(query, (start_id, end_id))
        rows = cursor.fetchall()
        
        for row in rows:
            bug_id, title, desc_text, env, created_by = row
            combined = f"{title} {desc_text} {env}".strip()
            if combined:
                all_data.append({
                    "id": bug_id,
                    "text": combined,
                    "is_spam": label,
                    "source": desc
                })
        
        print(f"  [{desc}] ID {start_id}-{end_id}: {len(rows)} rows (label={label})")
    
    cursor.close()
    conn.close()
    
    return all_data


def save_csv(data, filename="training_data.csv"):
    """Save ke CSV"""
    with open(filename, "w", newline="", encoding="utf-8") as f:
        writer = csv.DictWriter(f, fieldnames=["id", "text", "is_spam", "source"])
        writer.writeheader()
        writer.writerows(data)
    print(f"\n✓ Saved: {filename}")


def main():
    print("=" * 50)
    print("EXTRACT TRAINING DATA")
    print("=" * 50)
    print()
    
    # Try mysql-connector first, fallback to pymysql
    try:
        print("Connecting with mysql-connector...")
        data = extract_with_mysql_connector()
    except Exception as e1:
        print(f"mysql-connector failed: {e1}")
        try:
            print("Trying pymysql...")
            data = extract_with_pymysql()
        except Exception as e2:
            print(f"pymysql juga failed: {e2}")
            print("\nPastikan:")
            print("  1. MySQL/MariaDB sudah running")
            print("  2. Database 'mfg_record' ada")
            print("  3. Tabel 'bugs' ada")
            print("  4. Password di DB_CONFIG benar")
            sys.exit(1)
    
    if not data:
        print("\n⚠ Tidak ada data ditemukan!")
        print("Pastikan SQL test files sudah di-import ke database.")
        sys.exit(1)
    
    # Summary
    normal_count = sum(1 for d in data if d["is_spam"] == 0)
    spam_count = sum(1 for d in data if d["is_spam"] == 1)
    
    print(f"\n{'=' * 50}")
    print(f"SUMMARY")
    print(f"{'=' * 50}")
    print(f"Total samples:  {len(data)}")
    print(f"Normal (ham):   {normal_count}")
    print(f"Spam:           {spam_count}")
    print(f"Spam ratio:     {spam_count/len(data)*100:.1f}%")
    
    # Save
    save_csv(data)
    
    print(f"\n✓ File 'training_data.csv' siap!")
    print(f"  → Upload file ini ke Google Colab untuk training.")
    print(f"{'=' * 50}")


if __name__ == "__main__":
    main()
