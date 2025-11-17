#!/usr/bin/env python3
"""
BISMILLAAHIRRAHMAANIRRAHIIM - In the Name of Allah, Most Gracious, Most Merciful
================================================================================
filename : normalize_wilayah_level_1_2.py
purpose  : Normalize wilayah_level_1_2 data from single table to normalized tables
note     : Converts hierarchical wilayah level 1-2 data with geographic info
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

class WilayahLevel12Normalizer:
    def __init__(self, input_sql_file: str, output_dir: str = "normalized_data"):
        self.input_sql_file = input_sql_file
        self.output_dir = output_dir
        self.wilayah_data = []
        
        # Create output directory if not exists
        if not os.path.exists(output_dir):
            os.makedirs(output_dir)
    
    def parse_sql_file(self):
        """Parse SQL file and extract INSERT statements"""
        print("Parsing wilayah_level_1_2 SQL file...")
        
        with open(self.input_sql_file, 'r', encoding='utf-8') as file:
            content = file.read()
        
        # Find all INSERT INTO wilayah_level_1_2 statements
        insert_pattern = r"INSERT INTO `?wilayah_level_1_2`?\s*\([^)]+\)\s*VALUES\s*(.*?);"
        matches = re.findall(insert_pattern, content, re.DOTALL)
        
        for match in matches:
            # Parse individual value tuples
            value_pattern = r"\('([^']+)',\s*'([^']+)',\s*'([^']*)',\s*([^,]+),\s*([^,]+),\s*([^,]+),\s*([^,]+),\s*([^,]+),\s*([^,]+),\s*'([^']*)',\s*([^)]+)\)"
            values = re.findall(value_pattern, match)
            
            for row in values:
                # Helper function to safely convert values
                def safe_float(value):
                    try:
                        return float(value.strip()) if value.strip() and value.strip().upper() != 'NULL' else 0.0
                    except (ValueError, AttributeError):
                        return 0.0
                
                def safe_int(value):
                    try:
                        return int(value.strip()) if value.strip() and value.strip().upper() != 'NULL' else 0
                    except (ValueError, AttributeError):
                        return 0
                
                self.wilayah_data.append({
                    'kode': row[0],
                    'nama': row[1],
                    'ibukota': row[2] if row[2] else None,
                    'lat': safe_float(row[3]),
                    'lng': safe_float(row[4]),
                    'elv': safe_float(row[5]),
                    'tz': safe_int(row[6]),
                    'luas': safe_float(row[7]),
                    'penduduk': safe_float(row[8]),
                    'path': row[9] if row[9] else None,
                    'status': safe_int(row[10])
                })
        
        print(f"Parsed {len(self.wilayah_data)} records")
    
    def categorize_data(self) -> Dict[str, List[Dict]]:
        """Categorize data based on kode length and pattern"""
        print("Categorizing data...")
        
        provinsi = []
        kabupaten_kota = []
        
        for item in self.wilayah_data:
            kode = item['kode']
            
            # Count dots to determine level
            dot_count = kode.count('.')
            
            if dot_count == 0 and len(kode) == 2:
                # Provinsi: 2 digits, no dots
                provinsi.append({
                    'kode_provinsi': kode,
                    'nama_provinsi': item['nama'],
                    'ibukota': item['ibukota'],
                    'lat': item['lat'],
                    'lng': item['lng'],
                    'elv': item['elv'],
                    'tz': item['tz'],
                    'luas': item['luas'],
                    'penduduk': item['penduduk'],
                    'path': item['path'],
                    'status': item['status']
                })
            
            elif dot_count == 1:
                # Kabupaten/Kota: XX.XX format
                parts = kode.split('.')
                kode_provinsi = parts[0]
                kabupaten_kota.append({
                    'kode_kabupaten_kota': kode,
                    'kode_provinsi': kode_provinsi,
                    'nama_kabupaten_kota': item['nama'],
                    'ibukota': item['ibukota'],
                    'lat': item['lat'],
                    'lng': item['lng'],
                    'elv': item['elv'],
                    'tz': item['tz'],
                    'luas': item['luas'],
                    'penduduk': item['penduduk'],
                    'path': item['path'],
                    'status': item['status']
                })
        
        print(f"Provinsi: {len(provinsi)} records")
        print(f"Kabupaten/Kota: {len(kabupaten_kota)} records")
        
        return {
            'provinsi_geo': provinsi,
            'kabupaten_kota_geo': kabupaten_kota
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
            'provinsi_geo': """
CREATE TABLE IF NOT EXISTS provinsi_geo (
    kode_provinsi VARCHAR(2) PRIMARY KEY,
    nama_provinsi VARCHAR(100) NOT NULL,
    ibukota VARCHAR(100),
    lat DOUBLE COMMENT 'latitude in degrees',
    lng DOUBLE COMMENT 'longitude in degrees',
    elv FLOAT NOT NULL DEFAULT 0 COMMENT 'elevation in meters',
    tz TINYINT COMMENT 'timezone in hour',
    luas DOUBLE COMMENT 'area in km2',
    penduduk DOUBLE COMMENT 'population',
    path LONGTEXT COMMENT 'boundaries/polygon area',
    status TINYINT
);
CREATE INDEX idx_provinsi_geo_nama ON provinsi_geo (nama_provinsi);
CREATE INDEX idx_provinsi_geo_ibukota ON provinsi_geo (ibukota);
""",
            'kabupaten_kota_geo': """
CREATE TABLE IF NOT EXISTS kabupaten_kota_geo (
    kode_kabupaten_kota VARCHAR(5) PRIMARY KEY,
    kode_provinsi VARCHAR(2) NOT NULL,
    nama_kabupaten_kota VARCHAR(100) NOT NULL,
    ibukota VARCHAR(100),
    lat DOUBLE COMMENT 'latitude in degrees',
    lng DOUBLE COMMENT 'longitude in degrees',
    elv FLOAT NOT NULL DEFAULT 0 COMMENT 'elevation in meters',
    tz TINYINT COMMENT 'timezone in hour',
    luas DOUBLE COMMENT 'area in km2',
    penduduk DOUBLE COMMENT 'population',
    path LONGTEXT COMMENT 'boundaries/polygon area',
    status TINYINT,
    FOREIGN KEY (kode_provinsi) REFERENCES provinsi_geo(kode_provinsi)
);
CREATE INDEX idx_kabupaten_kota_geo_nama ON kabupaten_kota_geo (nama_kabupaten_kota);
CREATE INDEX idx_kabupaten_kota_geo_provinsi ON kabupaten_kota_geo (kode_provinsi);
CREATE INDEX idx_kabupaten_kota_geo_ibukota ON kabupaten_kota_geo (ibukota);
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
purpose  : Normalized {category} data with geographic information
note     : Data {category.title()} sesuai Kepmendagri No 300.2.2-2138 Tahun 2025
create   : 2025-09-13
author   : sukmaajidigital (normalized from original data)
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
                    
                    # Write values
                    values_list = []
                    for record in data:
                        values = []
                        for col in columns:
                            val = record[col]
                            if val is None:
                                values.append('NULL')
                            elif isinstance(val, str):
                                # Escape quotes and handle long text
                                escaped_val = val.replace("'", "''")
                                values.append(f"'{escaped_val}'")
                            else:
                                values.append(str(val))
                        values_list.append(f"({', '.join(values)})")
                    
                    sqlfile.write(',\n'.join(values_list))
                    sqlfile.write(";\n")
            
            print(f"Created {sql_file}")
    
    def create_database(self, categorized_data: Dict[str, List[Dict]]):
        """Create SQLite database with normalized tables"""
        print("Creating SQLite database...")
        
        db_file = os.path.join(self.output_dir, "wilayah_level_1_2_normalized.db")
        
        # Remove existing database
        if os.path.exists(db_file):
            os.remove(db_file)
        
        conn = sqlite3.connect(db_file)
        cursor = conn.cursor()
        
        try:
            # Create tables in order (respecting foreign key constraints)
            table_order = ['provinsi_geo', 'kabupaten_kota_geo']
            
            for table_name in table_order:
                data = categorized_data.get(table_name, [])
                if not data:
                    continue
                
                # Create table based on data structure
                if table_name == 'provinsi_geo':
                    cursor.execute("""
                        CREATE TABLE provinsi_geo (
                            kode_provinsi TEXT PRIMARY KEY,
                            nama_provinsi TEXT NOT NULL,
                            ibukota TEXT,
                            lat REAL,
                            lng REAL,
                            elv REAL NOT NULL DEFAULT 0,
                            tz INTEGER,
                            luas REAL,
                            penduduk REAL,
                            path TEXT,
                            status INTEGER
                        )
                    """)
                    
                    # Insert data
                    for record in data:
                        cursor.execute("""
                            INSERT INTO provinsi_geo 
                            (kode_provinsi, nama_provinsi, ibukota, lat, lng, elv, tz, luas, penduduk, path, status) 
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                        """, (
                            record['kode_provinsi'], record['nama_provinsi'], record['ibukota'],
                            record['lat'], record['lng'], record['elv'], record['tz'],
                            record['luas'], record['penduduk'], record['path'], record['status']
                        ))
                
                elif table_name == 'kabupaten_kota_geo':
                    cursor.execute("""
                        CREATE TABLE kabupaten_kota_geo (
                            kode_kabupaten_kota TEXT PRIMARY KEY,
                            kode_provinsi TEXT NOT NULL,
                            nama_kabupaten_kota TEXT NOT NULL,
                            ibukota TEXT,
                            lat REAL,
                            lng REAL,
                            elv REAL NOT NULL DEFAULT 0,
                            tz INTEGER,
                            luas REAL,
                            penduduk REAL,
                            path TEXT,
                            status INTEGER,
                            FOREIGN KEY (kode_provinsi) REFERENCES provinsi_geo(kode_provinsi)
                        )
                    """)
                    
                    # Insert data
                    for record in data:
                        cursor.execute("""
                            INSERT INTO kabupaten_kota_geo 
                            (kode_kabupaten_kota, kode_provinsi, nama_kabupaten_kota, ibukota, lat, lng, elv, tz, luas, penduduk, path, status) 
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                        """, (
                            record['kode_kabupaten_kota'], record['kode_provinsi'], record['nama_kabupaten_kota'],
                            record['ibukota'], record['lat'], record['lng'], record['elv'], record['tz'],
                            record['luas'], record['penduduk'], record['path'], record['status']
                        ))
                
                print(f"Created table {table_name} with {len(data)} records")
            
            # Create indexes for better performance
            indexes = [
                "CREATE INDEX idx_provinsi_geo_nama ON provinsi_geo (nama_provinsi)",
                "CREATE INDEX idx_provinsi_geo_ibukota ON provinsi_geo (ibukota)",
                "CREATE INDEX idx_kabupaten_kota_geo_nama ON kabupaten_kota_geo (nama_kabupaten_kota)",
                "CREATE INDEX idx_kabupaten_kota_geo_provinsi ON kabupaten_kota_geo (kode_provinsi)",
                "CREATE INDEX idx_kabupaten_kota_geo_ibukota ON kabupaten_kota_geo (ibukota)"
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
            f.write("WILAYAH LEVEL 1-2 DATA NORMALIZATION REPORT\n")
            f.write("=" * 55 + "\n\n")
            f.write(f"Generated on: 2025-09-13\n")
            f.write(f"Source file: {self.input_sql_file}\n\n")
            
            f.write("RECORD COUNTS:\n")
            f.write("-" * 20 + "\n")
            total_records = 0
            for category, data in categorized_data.items():
                count = len(data)
                total_records += count
                f.write(f"{category.title():<25}: {count:>8,} records\n")
            
            f.write(f"{'Total':<25}: {total_records:>8,} records\n\n")
            
            f.write("OUTPUT FILES:\n")
            f.write("-" * 20 + "\n")
            f.write("CSV Files:\n")
            for category in categorized_data.keys():
                f.write(f"  - {category}.csv\n")
            
            f.write("\nSQL Files:\n")
            for category in categorized_data.keys():
                f.write(f"  - {category}.sql\n")
            
            f.write("\nDatabase:\n")
            f.write("  - wilayah_level_1_2_normalized.db (SQLite)\n")
            
            f.write("\nNORMALIZATION SCHEMA:\n")
            f.write("-" * 30 + "\n")
            f.write("1. provinsi_geo (kode_provinsi, nama_provinsi, ibukota, lat, lng, elv, tz, luas, penduduk, path, status)\n")
            f.write("2. kabupaten_kota_geo (kode_kabupaten_kota, kode_provinsi, nama_kabupaten_kota, ibukota, lat, lng, elv, tz, luas, penduduk, path, status)\n")
        
        print(f"Summary report created: {report_file}")
    
    def normalize(self):
        """Main method to perform normalization"""
        print("Starting wilayah level 1-2 data normalization...")
        print("=" * 55)
        
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
        
        print("\n" + "=" * 55)
        print("Normalization completed successfully!")
        print(f"Output files saved in: {self.output_dir}/")


def main():
    """Main function"""
    import argparse
    
    parser = argparse.ArgumentParser(description='Normalize wilayah level 1-2 data into geographic tables')
    parser.add_argument('input_file', help='Input SQL file path (default: ../../db/wilayah_level_1_2.sql)', 
                       nargs='?', default='../../db/wilayah_level_1_2.sql')
    parser.add_argument('-o', '--output', help='Output directory (default: normalized_data)', 
                       default='normalized_data')
    
    args = parser.parse_args()
    
    if not os.path.exists(args.input_file):
        print(f"Error: Input file '{args.input_file}' not found!")
        return 1
    
    try:
        normalizer = WilayahLevel12Normalizer(args.input_file, args.output)
        normalizer.normalize()
        return 0
    except Exception as e:
        print(f"Error: {e}")
        return 1


if __name__ == "__main__":
    exit(main())