#!/usr/bin/env python3
"""
Test script untuk database wilayah yang telah dinormalisasi
"""

import sqlite3
import os

def test_database():
    db_file = "normalized_data/wilayah_normalized.db"
    
    if not os.path.exists(db_file):
        print(f"Database file {db_file} not found!")
        return
    
    conn = sqlite3.connect(db_file)
    cursor = conn.cursor()
    
    print("TESTING NORMALIZED WILAYAH DATABASE")
    print("=" * 50)
    
    # Test 1: Count records in each table
    print("\n1. RECORD COUNTS:")
    print("-" * 20)
    
    tables = ['provinsi', 'kabupaten_kota', 'kecamatan', 'desa_kelurahan']
    
    for table in tables:
        cursor.execute(f"SELECT COUNT(*) FROM {table}")
        count = cursor.fetchone()[0]
        print(f"{table:<20}: {count:>8,} records")
    
    # Test 2: Sample data from each table
    print("\n2. SAMPLE DATA:")
    print("-" * 20)
    
    print("\nProvinsi (first 5):")
    cursor.execute("SELECT * FROM provinsi LIMIT 5")
    for row in cursor.fetchall():
        print(f"  {row[0]} - {row[1]}")
    
    print("\nKabupaten/Kota di Aceh (first 5):")
    cursor.execute("SELECT * FROM kabupaten_kota WHERE kode_provinsi = '11' LIMIT 5")
    for row in cursor.fetchall():
        print(f"  {row[0]} - {row[2]}")
    
    # Test 3: Join query test
    print("\n3. JOIN QUERY TEST:")
    print("-" * 20)
    
    print("\nHierarki lengkap untuk beberapa desa:")
    cursor.execute("""
        SELECT 
            p.nama_provinsi,
            k.nama_kabupaten_kota,
            kc.nama_kecamatan,
            d.nama_desa_kelurahan
        FROM desa_kelurahan d
        JOIN kecamatan kc ON d.kode_kecamatan = kc.kode_kecamatan
        JOIN kabupaten_kota k ON d.kode_kabupaten_kota = k.kode_kabupaten_kota
        JOIN provinsi p ON d.kode_provinsi = p.kode_provinsi
        WHERE d.kode_provinsi = '11'
        LIMIT 5
    """)
    
    for row in cursor.fetchall():
        print(f"  {row[0]} > {row[1]} > {row[2]} > {row[3]}")
    
    # Test 4: Aggregation query
    print("\n4. AGGREGATION TEST:")
    print("-" * 20)
    
    print("\nJumlah kabupaten/kota per provinsi:")
    cursor.execute("""
        SELECT 
            p.nama_provinsi,
            COUNT(k.kode_kabupaten_kota) as jumlah_kabupaten_kota
        FROM provinsi p
        LEFT JOIN kabupaten_kota k ON p.kode_provinsi = k.kode_provinsi
        GROUP BY p.kode_provinsi, p.nama_provinsi
        ORDER BY jumlah_kabupaten_kota DESC
        LIMIT 10
    """)
    
    for row in cursor.fetchall():
        print(f"  {row[0]:<30}: {row[1]:>3} kabupaten/kota")
    
    # Test 5: Specific search
    print("\n5. SEARCH TEST:")
    print("-" * 20)
    
    print("\nMencari desa yang mengandung kata 'Baro':")
    cursor.execute("""
        SELECT 
            d.nama_desa_kelurahan,
            kc.nama_kecamatan,
            k.nama_kabupaten_kota
        FROM desa_kelurahan d
        JOIN kecamatan kc ON d.kode_kecamatan = kc.kode_kecamatan
        JOIN kabupaten_kota k ON d.kode_kabupaten_kota = k.kode_kabupaten_kota
        WHERE d.nama_desa_kelurahan LIKE '%Baro%'
        LIMIT 10
    """)
    
    for row in cursor.fetchall():
        print(f"  {row[0]} ({row[1]}, {row[2]})")
    
    print("\n" + "=" * 50)
    print("Database test completed successfully!")
    
    conn.close()

if __name__ == "__main__":
    test_database()