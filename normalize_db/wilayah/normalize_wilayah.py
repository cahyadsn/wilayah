#!/usr/bin/env python3
"""
BISMILLAAHIRRAHMAANIRRAHIIM - In the Name of Allah, Most Gracious, Most Merciful
================================================================================
filename : normalize_wilayah.py
purpose  : Normalize wilayah data from single table to 4 normalized tables
note     : Converts hierarchical wilayah data into separate tables for 
           provinsi, kabupaten/kota, kecamatan, and desa/kelurahan
create   : 2025-09-13
author   : sukmaajidigital
================================================================================
This program is free software; you can redistribute it and/or modify it under the
terms of the MIT License.
"""

import sqlite3
import csv
import os
import re
from typing import Dict, List, Tuple

class WilayahNormalizer:
    def __init__(self, input_sql_file: str, output_dir: str = "normalized_data"):
        self.input_sql_file = input_sql_file
        self.output_dir = output_dir
        self.wilayah_data = []
        
        # Create output directory if not exists
        if not os.path.exists(output_dir):
            os.makedirs(output_dir)
    
    def parse_sql_file(self):
        """Parse SQL file and extract INSERT statements"""
        print("Parsing SQL file...")
        
        with open(self.input_sql_file, 'r', encoding='utf-8') as file:
            content = file.read()
        
        # Find all INSERT VALUES statements
        insert_pattern = r"INSERT INTO wilayah \(kode, nama\)\s*VALUES\s*(.*?);"
        matches = re.findall(insert_pattern, content, re.DOTALL)
        
        for match in matches:
            # Parse individual value pairs
            value_pattern = r"\('([^']+)',\s*'([^']+)'\)"
            values = re.findall(value_pattern, match)
            
            for kode, nama in values:
                self.wilayah_data.append({
                    'kode': kode,
                    'nama': nama
                })
        
        print(f"Parsed {len(self.wilayah_data)} records")
    
    def categorize_data(self) -> Dict[str, List[Dict]]:
        """Categorize data based on kode length and pattern"""
        print("Categorizing data...")
        
        provinsi = []
        kabupaten_kota = []
        kecamatan = []
        desa_kelurahan = []
        
        for item in self.wilayah_data:
            kode = item['kode']
            nama = item['nama']
            
            # Count dots to determine level
            dot_count = kode.count('.')
            
            if dot_count == 0 and len(kode) == 2:
                # Provinsi: 2 digits, no dots
                provinsi.append({
                    'kode_provinsi': kode,
                    'nama_provinsi': nama
                })
            
            elif dot_count == 1:
                # Kabupaten/Kota: XX.XX format
                parts = kode.split('.')
                kode_provinsi = parts[0]
                kabupaten_kota.append({
                    'kode_kabupaten_kota': kode,
                    'kode_provinsi': kode_provinsi,
                    'nama_kabupaten_kota': nama
                })
            
            elif dot_count == 2:
                # Kecamatan: XX.XX.XX format
                parts = kode.split('.')
                kode_provinsi = parts[0]
                kode_kabupaten_kota = f"{parts[0]}.{parts[1]}"
                kecamatan.append({
                    'kode_kecamatan': kode,
                    'kode_kabupaten_kota': kode_kabupaten_kota,
                    'kode_provinsi': kode_provinsi,
                    'nama_kecamatan': nama
                })
            
            elif dot_count == 3:
                # Desa/Kelurahan: XX.XX.XX.XXXX format
                parts = kode.split('.')
                kode_provinsi = parts[0]
                kode_kabupaten_kota = f"{parts[0]}.{parts[1]}"
                kode_kecamatan = f"{parts[0]}.{parts[1]}.{parts[2]}"
                desa_kelurahan.append({
                    'kode_desa_kelurahan': kode,
                    'kode_kecamatan': kode_kecamatan,
                    'kode_kabupaten_kota': kode_kabupaten_kota,
                    'kode_provinsi': kode_provinsi,
                    'nama_desa_kelurahan': nama
                })
        
        print(f"Provinsi: {len(provinsi)} records")
        print(f"Kabupaten/Kota: {len(kabupaten_kota)} records")
        print(f"Kecamatan: {len(kecamatan)} records")
        print(f"Desa/Kelurahan: {len(desa_kelurahan)} records")
        
        return {
            'provinsi': provinsi,
            'kabupaten_kota': kabupaten_kota,
            'kecamatan': kecamatan,
            'desa_kelurahan': desa_kelurahan
        }
    
    def create_csv_files(self, categorized_data: Dict[str, List[Dict]]):
        """Create CSV files for each category"""
        print("Creating CSV files...")
        
        for category, data in categorized_data.items():
            if not data:
                continue
                
            csv_file = os.path.join(self.output_dir, f"{category}.csv")
            
            with open(csv_file, 'w', newline='', encoding='utf-8') as csvfile:
                if data:
                    fieldnames = data[0].keys()
                    writer = csv.DictWriter(csvfile, fieldnames=fieldnames)
                    writer.writeheader()
                    writer.writerows(data)
            
            print(f"Created {csv_file} with {len(data)} records")
    
    def create_sql_files(self, categorized_data: Dict[str, List[Dict]]):
        """Create SQL files for each normalized table"""
        print("Creating SQL files...")
        
        # SQL table definitions
        table_definitions = {
            'provinsi': """
CREATE TABLE IF NOT EXISTS provinsi (
    kode_provinsi VARCHAR(2) PRIMARY KEY,
    nama_provinsi VARCHAR(100) NOT NULL
);
CREATE INDEX idx_provinsi_nama ON provinsi (nama_provinsi);
""",
            'kabupaten_kota': """
CREATE TABLE IF NOT EXISTS kabupaten_kota (
    kode_kabupaten_kota VARCHAR(5) PRIMARY KEY,
    kode_provinsi VARCHAR(2) NOT NULL,
    nama_kabupaten_kota VARCHAR(100) NOT NULL,
    FOREIGN KEY (kode_provinsi) REFERENCES provinsi(kode_provinsi)
);
CREATE INDEX idx_kabupaten_kota_nama ON kabupaten_kota (nama_kabupaten_kota);
CREATE INDEX idx_kabupaten_kota_provinsi ON kabupaten_kota (kode_provinsi);
""",
            'kecamatan': """
CREATE TABLE IF NOT EXISTS kecamatan (
    kode_kecamatan VARCHAR(8) PRIMARY KEY,
    kode_kabupaten_kota VARCHAR(5) NOT NULL,
    kode_provinsi VARCHAR(2) NOT NULL,
    nama_kecamatan VARCHAR(100) NOT NULL,
    FOREIGN KEY (kode_kabupaten_kota) REFERENCES kabupaten_kota(kode_kabupaten_kota),
    FOREIGN KEY (kode_provinsi) REFERENCES provinsi(kode_provinsi)
);
CREATE INDEX idx_kecamatan_nama ON kecamatan (nama_kecamatan);
CREATE INDEX idx_kecamatan_kabupaten ON kecamatan (kode_kabupaten_kota);
CREATE INDEX idx_kecamatan_provinsi ON kecamatan (kode_provinsi);
""",
            'desa_kelurahan': """
CREATE TABLE IF NOT EXISTS desa_kelurahan (
    kode_desa_kelurahan VARCHAR(13) PRIMARY KEY,
    kode_kecamatan VARCHAR(8) NOT NULL,
    kode_kabupaten_kota VARCHAR(5) NOT NULL,
    kode_provinsi VARCHAR(2) NOT NULL,
    nama_desa_kelurahan VARCHAR(100) NOT NULL,
    FOREIGN KEY (kode_kecamatan) REFERENCES kecamatan(kode_kecamatan),
    FOREIGN KEY (kode_kabupaten_kota) REFERENCES kabupaten_kota(kode_kabupaten_kota),
    FOREIGN KEY (kode_provinsi) REFERENCES provinsi(kode_provinsi)
);
CREATE INDEX idx_desa_kelurahan_nama ON desa_kelurahan (nama_desa_kelurahan);
CREATE INDEX idx_desa_kelurahan_kecamatan ON desa_kelurahan (kode_kecamatan);
CREATE INDEX idx_desa_kelurahan_kabupaten ON desa_kelurahan (kode_kabupaten_kota);
CREATE INDEX idx_desa_kelurahan_provinsi ON desa_kelurahan (kode_provinsi);
"""
        }
        
        for category, data in categorized_data.items():
            if not data:
                continue
                
            sql_file = os.path.join(self.output_dir, f"{category}.sql")
            
            with open(sql_file, 'w', encoding='utf-8') as sqlfile:
                # Write header comment
                sqlfile.write(f"""/*
BISMILLAAHIRRAHMAANIRRAHIIM - In the Name of Allah, Most Gracious, Most Merciful
================================================================================
filename : {category}.sql
purpose  : Normalized {category} data
note     : Data {category.title()} sesuai Kepmendagri No 300.2.2-2138 Tahun 2025
create   : 2025-09-13
author   : sukmaajidigital
================================================================================
*/

""")
                
                # Write table definition
                sqlfile.write(f"-- Table structure for {category}\n")
                sqlfile.write(table_definitions[category])
                sqlfile.write("\n")
                
                # Write data
                sqlfile.write(f"-- Data for table {category}\n")
                
                if data:
                    # Get column names
                    columns = list(data[0].keys())
                    columns_str = ', '.join(columns)
                    
                    sqlfile.write(f"INSERT INTO {category} ({columns_str}) VALUES\n")
                    
                    # Write values in batches of 1000 for better performance
                    batch_size = 1000
                    for i in range(0, len(data), batch_size):
                        batch = data[i:i + batch_size]
                        values_list = []
                        
                        for record in batch:
                            values = [f"'{str(record[col]).replace(chr(39), chr(39)+chr(39))}'" for col in columns]
                            values_list.append(f"({', '.join(values)})")
                        
                        sqlfile.write(',\n'.join(values_list))
                        
                        # Add semicolon for last batch or comma for continuation
                        if i + batch_size >= len(data):
                            sqlfile.write(";\n")
                        else:
                            sqlfile.write(",\n")
                            # Start new INSERT statement for next batch
                            sqlfile.write(f"INSERT INTO {category} ({columns_str}) VALUES\n")
            
            print(f"Created {sql_file}")
    
    def create_database(self, categorized_data: Dict[str, List[Dict]]):
        """Create SQLite database with normalized tables"""
        print("Creating SQLite database...")
        
        db_file = os.path.join(self.output_dir, "wilayah_normalized.db")
        
        # Remove existing database
        if os.path.exists(db_file):
            os.remove(db_file)
        
        conn = sqlite3.connect(db_file)
        cursor = conn.cursor()
        
        try:
            # Create tables in order (respecting foreign key constraints)
            table_order = ['provinsi', 'kabupaten_kota', 'kecamatan', 'desa_kelurahan']
            
            for table_name in table_order:
                data = categorized_data.get(table_name, [])
                if not data:
                    continue
                
                # Create table based on data structure
                if table_name == 'provinsi':
                    cursor.execute("""
                        CREATE TABLE provinsi (
                            kode_provinsi TEXT PRIMARY KEY,
                            nama_provinsi TEXT NOT NULL
                        )
                    """)
                    
                    # Insert data
                    for record in data:
                        cursor.execute(
                            "INSERT INTO provinsi (kode_provinsi, nama_provinsi) VALUES (?, ?)",
                            (record['kode_provinsi'], record['nama_provinsi'])
                        )
                
                elif table_name == 'kabupaten_kota':
                    cursor.execute("""
                        CREATE TABLE kabupaten_kota (
                            kode_kabupaten_kota TEXT PRIMARY KEY,
                            kode_provinsi TEXT NOT NULL,
                            nama_kabupaten_kota TEXT NOT NULL,
                            FOREIGN KEY (kode_provinsi) REFERENCES provinsi(kode_provinsi)
                        )
                    """)
                    
                    # Insert data
                    for record in data:
                        cursor.execute(
                            "INSERT INTO kabupaten_kota (kode_kabupaten_kota, kode_provinsi, nama_kabupaten_kota) VALUES (?, ?, ?)",
                            (record['kode_kabupaten_kota'], record['kode_provinsi'], record['nama_kabupaten_kota'])
                        )
                
                elif table_name == 'kecamatan':
                    cursor.execute("""
                        CREATE TABLE kecamatan (
                            kode_kecamatan TEXT PRIMARY KEY,
                            kode_kabupaten_kota TEXT NOT NULL,
                            kode_provinsi TEXT NOT NULL,
                            nama_kecamatan TEXT NOT NULL,
                            FOREIGN KEY (kode_kabupaten_kota) REFERENCES kabupaten_kota(kode_kabupaten_kota),
                            FOREIGN KEY (kode_provinsi) REFERENCES provinsi(kode_provinsi)
                        )
                    """)
                    
                    # Insert data
                    for record in data:
                        cursor.execute(
                            "INSERT INTO kecamatan (kode_kecamatan, kode_kabupaten_kota, kode_provinsi, nama_kecamatan) VALUES (?, ?, ?, ?)",
                            (record['kode_kecamatan'], record['kode_kabupaten_kota'], record['kode_provinsi'], record['nama_kecamatan'])
                        )
                
                elif table_name == 'desa_kelurahan':
                    cursor.execute("""
                        CREATE TABLE desa_kelurahan (
                            kode_desa_kelurahan TEXT PRIMARY KEY,
                            kode_kecamatan TEXT NOT NULL,
                            kode_kabupaten_kota TEXT NOT NULL,
                            kode_provinsi TEXT NOT NULL,
                            nama_desa_kelurahan TEXT NOT NULL,
                            FOREIGN KEY (kode_kecamatan) REFERENCES kecamatan(kode_kecamatan),
                            FOREIGN KEY (kode_kabupaten_kota) REFERENCES kabupaten_kota(kode_kabupaten_kota),
                            FOREIGN KEY (kode_provinsi) REFERENCES provinsi(kode_provinsi)
                        )
                    """)
                    
                    # Insert data
                    for record in data:
                        cursor.execute(
                            "INSERT INTO desa_kelurahan (kode_desa_kelurahan, kode_kecamatan, kode_kabupaten_kota, kode_provinsi, nama_desa_kelurahan) VALUES (?, ?, ?, ?, ?)",
                            (record['kode_desa_kelurahan'], record['kode_kecamatan'], record['kode_kabupaten_kota'], record['kode_provinsi'], record['nama_desa_kelurahan'])
                        )
                
                print(f"Created table {table_name} with {len(data)} records")
            
            # Create indexes for better performance
            indexes = [
                "CREATE INDEX idx_provinsi_nama ON provinsi (nama_provinsi)",
                "CREATE INDEX idx_kabupaten_kota_nama ON kabupaten_kota (nama_kabupaten_kota)",
                "CREATE INDEX idx_kabupaten_kota_provinsi ON kabupaten_kota (kode_provinsi)",
                "CREATE INDEX idx_kecamatan_nama ON kecamatan (nama_kecamatan)",
                "CREATE INDEX idx_kecamatan_kabupaten ON kecamatan (kode_kabupaten_kota)",
                "CREATE INDEX idx_desa_kelurahan_nama ON desa_kelurahan (nama_desa_kelurahan)",
                "CREATE INDEX idx_desa_kelurahan_kecamatan ON desa_kelurahan (kode_kecamatan)"
            ]
            
            for index_sql in indexes:
                cursor.execute(index_sql)
            
            conn.commit()
            print(f"SQLite database created: {db_file}")
            
        except Exception as e:
            print(f"Error creating database: {e}")
            conn.rollback()
        finally:
            conn.close()
    
    def generate_summary_report(self, categorized_data: Dict[str, List[Dict]]):
        """Generate summary report"""
        report_file = os.path.join(self.output_dir, "normalization_report.txt")
        
        with open(report_file, 'w', encoding='utf-8') as f:
            f.write("WILAYAH DATA NORMALIZATION REPORT\n")
            f.write("=" * 50 + "\n\n")
            f.write(f"Generated on: 2025-09-13\n")
            f.write(f"Source file: {self.input_sql_file}\n\n")
            
            f.write("RECORD COUNTS:\n")
            f.write("-" * 20 + "\n")
            total_records = 0
            for category, data in categorized_data.items():
                count = len(data)
                total_records += count
                f.write(f"{category.title():<20}: {count:>8,} records\n")
            
            f.write(f"{'Total':<20}: {total_records:>8,} records\n\n")
            
            f.write("OUTPUT FILES:\n")
            f.write("-" * 20 + "\n")
            f.write("CSV Files:\n")
            for category in categorized_data.keys():
                f.write(f"  - {category}.csv\n")
            
            f.write("\nSQL Files:\n")
            for category in categorized_data.keys():
                f.write(f"  - {category}.sql\n")
            
            f.write("\nDatabase:\n")
            f.write("  - wilayah_normalized.db (SQLite)\n")
            
            f.write("\nNORMALIZATION SCHEMA:\n")
            f.write("-" * 30 + "\n")
            f.write("1. provinsi (kode_provinsi, nama_provinsi)\n")
            f.write("2. kabupaten_kota (kode_kabupaten_kota, kode_provinsi, nama_kabupaten_kota)\n")
            f.write("3. kecamatan (kode_kecamatan, kode_kabupaten_kota, kode_provinsi, nama_kecamatan)\n")
            f.write("4. desa_kelurahan (kode_desa_kelurahan, kode_kecamatan, kode_kabupaten_kota, kode_provinsi, nama_desa_kelurahan)\n")
        
        print(f"Summary report created: {report_file}")
    
    def normalize(self):
        """Main method to perform normalization"""
        print("Starting wilayah data normalization...")
        print("=" * 50)
        
        # Step 1: Parse SQL file
        self.parse_sql_file()
        
        # Step 2: Categorize data
        categorized_data = self.categorize_data()
        
        # Step 3: Create output files
        self.create_csv_files(categorized_data)
        self.create_sql_files(categorized_data)
        self.create_database(categorized_data)
        
        # Step 4: Generate report
        self.generate_summary_report(categorized_data)
        
        print("\n" + "=" * 50)
        print("Normalization completed successfully!")
        print(f"Output files saved in: {self.output_dir}/")


def main():
    """Main function"""
    import argparse
    
    parser = argparse.ArgumentParser(description='Normalize wilayah data into 4 separate tables')
    parser.add_argument('input_file', help='Input SQL file path (default: ../../db/wilayah.sql)', 
                       nargs='?', default='../../db/wilayah.sql')
    parser.add_argument('-o', '--output', help='Output directory (default: normalized_data)', 
                       default='normalized_data')
    
    args = parser.parse_args()
    
    if not os.path.exists(args.input_file):
        print(f"Error: Input file '{args.input_file}' not found!")
        return 1
    
    try:
        normalizer = WilayahNormalizer(args.input_file, args.output)
        normalizer.normalize()
        return 0
    except Exception as e:
        print(f"Error: {e}")
        return 1


if __name__ == "__main__":
    exit(main())