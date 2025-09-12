#!/usr/bin/env python3
"""
Contoh query kompleks untuk database wilayah yang telah dinormalisasi
"""

import sqlite3
import os

def advanced_queries():
    db_file = "normalized_data/wilayah_normalized.db"
    
    if not os.path.exists(db_file):
        print(f"Database file {db_file} not found!")
        return
    
    conn = sqlite3.connect(db_file)
    cursor = conn.cursor()
    
    print("ADVANCED QUERIES - NORMALIZED WILAYAH DATABASE")
    print("=" * 60)
    
    # Query 1: Statistik per provinsi
    print("\n1. STATISTIK WILAYAH PER PROVINSI:")
    print("-" * 40)
    
    cursor.execute("""
        SELECT 
            p.nama_provinsi,
            COUNT(DISTINCT k.kode_kabupaten_kota) as jml_kabupaten_kota,
            COUNT(DISTINCT kc.kode_kecamatan) as jml_kecamatan,
            COUNT(DISTINCT d.kode_desa_kelurahan) as jml_desa_kelurahan
        FROM provinsi p
        LEFT JOIN kabupaten_kota k ON p.kode_provinsi = k.kode_provinsi
        LEFT JOIN kecamatan kc ON k.kode_kabupaten_kota = kc.kode_kabupaten_kota
        LEFT JOIN desa_kelurahan d ON kc.kode_kecamatan = d.kode_kecamatan
        GROUP BY p.kode_provinsi, p.nama_provinsi
        ORDER BY jml_desa_kelurahan DESC
        LIMIT 10
    """)
    
    print(f"{'Provinsi':<25} {'Kab/Kota':<8} {'Kecamatan':<10} {'Desa/Kel':<10}")
    print("-" * 60)
    for row in cursor.fetchall():
        print(f"{row[0]:<25} {row[1]:<8} {row[2]:<10} {row[3]:<10}")
    
    # Query 2: Kabupaten dengan kecamatan terbanyak
    print("\n\n2. KABUPATEN/KOTA DENGAN KECAMATAN TERBANYAK:")
    print("-" * 50)
    
    cursor.execute("""
        SELECT 
            k.nama_kabupaten_kota,
            p.nama_provinsi,
            COUNT(kc.kode_kecamatan) as jml_kecamatan
        FROM kabupaten_kota k
        JOIN provinsi p ON k.kode_provinsi = p.kode_provinsi
        LEFT JOIN kecamatan kc ON k.kode_kabupaten_kota = kc.kode_kabupaten_kota
        GROUP BY k.kode_kabupaten_kota, k.nama_kabupaten_kota, p.nama_provinsi
        ORDER BY jml_kecamatan DESC
        LIMIT 15
    """)
    
    for row in cursor.fetchall():
        print(f"{row[0]:<35} ({row[1]:<20}): {row[2]:>3} kecamatan")
    
    # Query 3: Kecamatan dengan desa terbanyak
    print("\n\n3. KECAMATAN DENGAN DESA/KELURAHAN TERBANYAK:")
    print("-" * 50)
    
    cursor.execute("""
        SELECT 
            kc.nama_kecamatan,
            k.nama_kabupaten_kota,
            p.nama_provinsi,
            COUNT(d.kode_desa_kelurahan) as jml_desa
        FROM kecamatan kc
        JOIN kabupaten_kota k ON kc.kode_kabupaten_kota = k.kode_kabupaten_kota
        JOIN provinsi p ON kc.kode_provinsi = p.kode_provinsi
        LEFT JOIN desa_kelurahan d ON kc.kode_kecamatan = d.kode_kecamatan
        GROUP BY kc.kode_kecamatan, kc.nama_kecamatan, k.nama_kabupaten_kota, p.nama_provinsi
        ORDER BY jml_desa DESC
        LIMIT 15
    """)
    
    for row in cursor.fetchall():
        print(f"{row[0]:<25} ({row[1]:<30}, {row[2]}): {row[3]:>3} desa")
    
    # Query 4: Pencarian berdasarkan nama
    print("\n\n4. PENCARIAN WILAYAH BERDASARKAN NAMA:")
    print("-" * 45)
    
    search_terms = ['Jakarta', 'Surabaya', 'Medan', 'Bandung', 'Makassar']
    
    for term in search_terms:
        print(f"\nMencari '{term}':")
        
        # Cari di provinsi
        cursor.execute("SELECT nama_provinsi FROM provinsi WHERE nama_provinsi LIKE ?", (f'%{term}%',))
        provinsi_results = cursor.fetchall()
        if provinsi_results:
            for row in provinsi_results:
                print(f"  Provinsi: {row[0]}")
        
        # Cari di kabupaten/kota
        cursor.execute("""
            SELECT k.nama_kabupaten_kota, p.nama_provinsi 
            FROM kabupaten_kota k 
            JOIN provinsi p ON k.kode_provinsi = p.kode_provinsi 
            WHERE k.nama_kabupaten_kota LIKE ?
        """, (f'%{term}%',))
        kabupaten_results = cursor.fetchall()
        if kabupaten_results:
            for row in kabupaten_results:
                print(f"  Kab/Kota: {row[0]} ({row[1]})")
        
        # Cari di kecamatan
        cursor.execute("""
            SELECT kc.nama_kecamatan, k.nama_kabupaten_kota, p.nama_provinsi 
            FROM kecamatan kc 
            JOIN kabupaten_kota k ON kc.kode_kabupaten_kota = k.kode_kabupaten_kota
            JOIN provinsi p ON kc.kode_provinsi = p.kode_provinsi 
            WHERE kc.nama_kecamatan LIKE ?
            LIMIT 3
        """, (f'%{term}%',))
        kecamatan_results = cursor.fetchall()
        if kecamatan_results:
            for row in kecamatan_results:
                print(f"  Kecamatan: {row[0]} ({row[1]}, {row[2]})")
        
        if not (provinsi_results or kabupaten_results or kecamatan_results):
            print(f"  Tidak ditemukan wilayah dengan nama '{term}'")
    
    # Query 5: Hierarki lengkap untuk wilayah tertentu
    print("\n\n5. HIERARKI LENGKAP UNTUK KODE TERTENTU:")
    print("-" * 45)
    
    test_codes = ['11.01.01.2001', '32.01.01.2001', '73.01.01.2001']
    
    for code in test_codes:
        cursor.execute("""
            SELECT 
                d.kode_desa_kelurahan,
                d.nama_desa_kelurahan,
                kc.nama_kecamatan,
                k.nama_kabupaten_kota,
                p.nama_provinsi
            FROM desa_kelurahan d
            JOIN kecamatan kc ON d.kode_kecamatan = kc.kode_kecamatan
            JOIN kabupaten_kota k ON d.kode_kabupaten_kota = k.kode_kabupaten_kota
            JOIN provinsi p ON d.kode_provinsi = p.kode_provinsi
            WHERE d.kode_desa_kelurahan = ?
        """, (code,))
        
        result = cursor.fetchone()
        if result:
            print(f"\nKode: {result[0]}")
            print(f"  Desa/Kelurahan: {result[1]}")
            print(f"  Kecamatan     : {result[2]}")
            print(f"  Kabupaten/Kota: {result[3]}")
            print(f"  Provinsi      : {result[4]}")
        else:
            print(f"\nKode {code}: Tidak ditemukan")
    
    # Query 6: Analisis distribusi nama
    print("\n\n6. ANALISIS DISTRIBUSI NAMA DESA/KELURAHAN:")
    print("-" * 50)
    
    print("\nKata yang paling sering muncul di nama desa/kelurahan:")
    cursor.execute("""
        SELECT 
            CASE 
                WHEN nama_desa_kelurahan LIKE '%Baro%' THEN 'Baro'
                WHEN nama_desa_kelurahan LIKE '%Baru%' THEN 'Baru'
                WHEN nama_desa_kelurahan LIKE '%Dalam%' THEN 'Dalam'
                WHEN nama_desa_kelurahan LIKE '%Jaya%' THEN 'Jaya'
                WHEN nama_desa_kelurahan LIKE '%Makmur%' THEN 'Makmur'
                WHEN nama_desa_kelurahan LIKE '%Sari%' THEN 'Sari'
                WHEN nama_desa_kelurahan LIKE '%Indah%' THEN 'Indah'
                WHEN nama_desa_kelurahan LIKE '%Maju%' THEN 'Maju'
                WHEN nama_desa_kelurahan LIKE '%Damai%' THEN 'Damai'
                WHEN nama_desa_kelurahan LIKE '%Sejahtera%' THEN 'Sejahtera'
            END as kata_kunci,
            COUNT(*) as jumlah
        FROM desa_kelurahan
        WHERE nama_desa_kelurahan LIKE '%Baro%' 
           OR nama_desa_kelurahan LIKE '%Baru%'
           OR nama_desa_kelurahan LIKE '%Dalam%'
           OR nama_desa_kelurahan LIKE '%Jaya%'
           OR nama_desa_kelurahan LIKE '%Makmur%'
           OR nama_desa_kelurahan LIKE '%Sari%'
           OR nama_desa_kelurahan LIKE '%Indah%'
           OR nama_desa_kelurahan LIKE '%Maju%'
           OR nama_desa_kelurahan LIKE '%Damai%'
           OR nama_desa_kelurahan LIKE '%Sejahtera%'
        GROUP BY kata_kunci
        ORDER BY jumlah DESC
    """)
    
    for row in cursor.fetchall():
        print(f"  '{row[0]}': {row[1]:>5} desa/kelurahan")
    
    print("\n" + "=" * 60)
    print("Advanced queries completed!")
    
    conn.close()

if __name__ == "__main__":
    advanced_queries()