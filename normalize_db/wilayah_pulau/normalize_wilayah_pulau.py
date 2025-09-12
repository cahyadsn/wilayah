#!/usr/bin/env python3
"""
BISMILLAAHIRRAHMAANIRRAHIIM - In the Name of Allah, Most Gracious, Most Merciful
================================================================================
filename : normalize_wilayah_pulau.py
purpose  : Normalize wilayah_pulau data from single table to normalized tables
note     : Converts pulau (island) data into normalized relational structure
create   : 2025-09-13
author   : sukmaajidigital with GitHub Copilot
================================================================================
This program is free software; you can redistribute it and/or modify it under the
terms of the MIT License.
"""

import sqlite3
import csv
import os
import re
from typing import Dict, List, Tuple

class WilayahPulauNormalizer:
    def __init__(self, input_sql_file: str, output_dir: str = "normalized_data"):
        self.input_sql_file = input_sql_file
        self.output_dir = output_dir
        self.pulau_data = []
        
        # Create output directory if not exists
        if not os.path.exists(output_dir):
            os.makedirs(output_dir)
    
    def parse_sql_file(self):
        """Parse SQL file and extract INSERT statements"""
        print("Parsing wilayah_pulau SQL file...")
        
        with open(self.input_sql_file, 'r', encoding='utf-8') as file:
            content = file.read()
        
        # Find all INSERT INTO wilayah_pulau statements
        insert_pattern = r"INSERT INTO wilayah_pulau\s*\([^)]+\)\s*VALUES\s*(.*?);"
        matches = re.findall(insert_pattern, content, re.DOTALL)
        
        for match in matches:
            # Parse individual value tuples - handle different data formats
            # Some entries might have NULL values or different formats
            value_pattern = r"\('([^']*)',\s*'([^']*)',\s*'([^']*)',\s*'([^']*)',\s*'([^']*)'\)"
            values = re.findall(value_pattern, match)
            
            for row in values:
                # Extract province and kabupaten from kode (format: XX.XX.XXXXX)
                kode_parts = row[0].split('.')
                if len(kode_parts) >= 2:
                    kode_provinsi = kode_parts[0]
                    kode_kabupaten_kota = f"{kode_parts[0]}.{kode_parts[1]}"
                    
                    # Clean and parse coordinates
                    try:
                        latitude = float(row[2].strip()) if row[2].strip() and row[2].strip() != 'NULL' else 0.0
                        longitude = float(row[3].strip()) if row[3].strip() and row[3].strip() != 'NULL' else 0.0
                    except (ValueError, IndexError):
                        latitude = 0.0
                        longitude = 0.0
                    
                    # For now, we'll create placeholder names for provinsi and kabupaten
                    # In a real scenario, we'd want to join with other tables to get proper names
                    self.pulau_data.append({
                        'kode_pulau': row[0].strip(),
                        'nama_pulau': row[1].strip(),
                        'kode_provinsi': kode_provinsi,
                        'nama_provinsi': f"Provinsi {kode_provinsi}",  # Placeholder
                        'kode_kabupaten_kota': kode_kabupaten_kota,
                        'nama_kabupaten_kota': f"Kabupaten/Kota {kode_kabupaten_kota}",  # Placeholder
                        'latitude': latitude,
                        'longitude': longitude,
                        'notes': row[4].strip() if len(row) > 4 else ''
                    })
        
        print(f"Parsed {len(self.pulau_data)} records")
    
    def categorize_data(self) -> Dict[str, List[Dict]]:
        """Categorize data into normalized tables"""
        print("Categorizing data...")
        
        # Use sets to avoid duplicates, then convert to lists
        provinsi_set = set()
        kabupaten_kota_set = set()
        pulau_locations = []
        
        for item in self.pulau_data:
            kode_provinsi = item['kode_provinsi']
            nama_provinsi = item['nama_provinsi']
            kode_kabupaten_kota = item['kode_kabupaten_kota']
            nama_kabupaten_kota = item['nama_kabupaten_kota']
            
            # Add to provinsi set
            provinsi_set.add((kode_provinsi, nama_provinsi))
            
            # Add to kabupaten/kota set
            kabupaten_kota_set.add((kode_kabupaten_kota, kode_provinsi, nama_kabupaten_kota))
            
            # Add pulau location (keep all records as each represents a unique island location)
            pulau_locations.append({
                'id': len(pulau_locations) + 1,  # Auto-increment ID
                'kode_pulau': item['kode_pulau'],
                'nama_pulau': item['nama_pulau'],
                'kode_provinsi': kode_provinsi,
                'kode_kabupaten_kota': kode_kabupaten_kota,
                'latitude': item['latitude'],
                'longitude': item['longitude'],
                'notes': item.get('notes', '')
            })
        
        # Convert sets to sorted lists
        provinsi_pulau = [
            {
                'kode_provinsi': kode,
                'nama_provinsi': nama
            }
            for kode, nama in sorted(provinsi_set)
        ]
        
        kabupaten_kota_pulau = [
            {
                'kode_kabupaten_kota': kode_kab,
                'kode_provinsi': kode_prov,
                'nama_kabupaten_kota': nama_kab
            }
            for kode_kab, kode_prov, nama_kab in sorted(kabupaten_kota_set)
        ]
        
        print(f"Provinsi Pulau: {len(provinsi_pulau)} records")
        print(f"Kabupaten/Kota Pulau: {len(kabupaten_kota_pulau)} records")
        print(f"Pulau Locations: {len(pulau_locations)} records")
        
        return {
            'provinsi_pulau': provinsi_pulau,
            'kabupaten_kota_pulau': kabupaten_kota_pulau,
            'pulau_locations': pulau_locations
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
            'provinsi_pulau': """
CREATE TABLE IF NOT EXISTS provinsi_pulau (
    kode_provinsi VARCHAR(2) PRIMARY KEY,
    nama_provinsi VARCHAR(100) NOT NULL
);
CREATE INDEX idx_provinsi_pulau_nama ON provinsi_pulau (nama_provinsi);
""",
            'kabupaten_kota_pulau': """
CREATE TABLE IF NOT EXISTS kabupaten_kota_pulau (
    kode_kabupaten_kota VARCHAR(5) PRIMARY KEY,
    kode_provinsi VARCHAR(2) NOT NULL,
    nama_kabupaten_kota VARCHAR(100) NOT NULL,
    FOREIGN KEY (kode_provinsi) REFERENCES provinsi_pulau(kode_provinsi)
);
CREATE INDEX idx_kabupaten_kota_pulau_nama ON kabupaten_kota_pulau (nama_kabupaten_kota);
CREATE INDEX idx_kabupaten_kota_pulau_provinsi ON kabupaten_kota_pulau (kode_provinsi);
""",
            'pulau_locations': """
CREATE TABLE IF NOT EXISTS pulau_locations (
    id INTEGER PRIMARY KEY AUTO_INCREMENT,
    kode_pulau VARCHAR(15) NOT NULL,
    nama_pulau VARCHAR(100) NOT NULL,
    kode_provinsi VARCHAR(2) NOT NULL,
    kode_kabupaten_kota VARCHAR(5) NOT NULL,
    latitude DOUBLE NOT NULL DEFAULT 0,
    longitude DOUBLE NOT NULL DEFAULT 0,
    notes VARCHAR(50),
    FOREIGN KEY (kode_provinsi) REFERENCES provinsi_pulau(kode_provinsi),
    FOREIGN KEY (kode_kabupaten_kota) REFERENCES kabupaten_kota_pulau(kode_kabupaten_kota)
);
CREATE INDEX idx_pulau_locations_kode ON pulau_locations (kode_pulau);
CREATE INDEX idx_pulau_locations_nama ON pulau_locations (nama_pulau);
CREATE INDEX idx_pulau_locations_provinsi ON pulau_locations (kode_provinsi);
CREATE INDEX idx_pulau_locations_kabupaten ON pulau_locations (kode_kabupaten_kota);
CREATE INDEX idx_pulau_locations_coordinates ON pulau_locations (latitude, longitude);
CREATE INDEX idx_pulau_locations_latitude ON pulau_locations (latitude);
CREATE INDEX idx_pulau_locations_longitude ON pulau_locations (longitude);
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
purpose  : Normalized {category} data from pulau (island) dataset
note     : Data {category.title()} - Island/Pulau geographic locations
           Contains coordinate data for islands across Indonesia
create   : 2025-09-13
author   : sukmaajidigital with GitHub Copilot (normalized from original data)
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
                            values = []
                            for col in columns:
                                value = record[col]
                                if isinstance(value, str):
                                    values.append(f"'{value.replace(chr(39), chr(39)+chr(39))}'")
                                else:
                                    values.append(str(value))
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
        
        db_file = os.path.join(self.output_dir, "wilayah_pulau_normalized.db")
        
        # Remove existing database
        if os.path.exists(db_file):
            os.remove(db_file)
        
        conn = sqlite3.connect(db_file)
        cursor = conn.cursor()
        
        try:
            # Create tables in order (respecting foreign key constraints)
            table_order = ['provinsi_pulau', 'kabupaten_kota_pulau', 'pulau_locations']
            
            for table_name in table_order:
                data = categorized_data.get(table_name, [])
                if not data:
                    continue
                
                # Create table based on data structure
                if table_name == 'provinsi_pulau':
                    cursor.execute("""
                        CREATE TABLE provinsi_pulau (
                            kode_provinsi TEXT PRIMARY KEY,
                            nama_provinsi TEXT NOT NULL
                        )
                    """)
                    
                    # Insert data
                    for record in data:
                        cursor.execute(
                            "INSERT INTO provinsi_pulau (kode_provinsi, nama_provinsi) VALUES (?, ?)",
                            (record['kode_provinsi'], record['nama_provinsi'])
                        )
                
                elif table_name == 'kabupaten_kota_pulau':
                    cursor.execute("""
                        CREATE TABLE kabupaten_kota_pulau (
                            kode_kabupaten_kota TEXT PRIMARY KEY,
                            kode_provinsi TEXT NOT NULL,
                            nama_kabupaten_kota TEXT NOT NULL,
                            FOREIGN KEY (kode_provinsi) REFERENCES provinsi_pulau(kode_provinsi)
                        )
                    """)
                    
                    # Insert data
                    for record in data:
                        cursor.execute(
                            "INSERT INTO kabupaten_kota_pulau (kode_kabupaten_kota, kode_provinsi, nama_kabupaten_kota) VALUES (?, ?, ?)",
                            (record['kode_kabupaten_kota'], record['kode_provinsi'], record['nama_kabupaten_kota'])
                        )
                
                elif table_name == 'pulau_locations':
                    cursor.execute("""
                        CREATE TABLE pulau_locations (
                            id INTEGER PRIMARY KEY AUTOINCREMENT,
                            kode_pulau TEXT NOT NULL,
                            nama_pulau TEXT NOT NULL,
                            kode_provinsi TEXT NOT NULL,
                            kode_kabupaten_kota TEXT NOT NULL,
                            latitude REAL NOT NULL DEFAULT 0,
                            longitude REAL NOT NULL DEFAULT 0,
                            notes TEXT,
                            FOREIGN KEY (kode_provinsi) REFERENCES provinsi_pulau(kode_provinsi),
                            FOREIGN KEY (kode_kabupaten_kota) REFERENCES kabupaten_kota_pulau(kode_kabupaten_kota)
                        )
                    """)
                    
                    # Insert data
                    for record in data:
                        cursor.execute(
                            "INSERT INTO pulau_locations (kode_pulau, nama_pulau, kode_provinsi, kode_kabupaten_kota, latitude, longitude, notes) VALUES (?, ?, ?, ?, ?, ?, ?)",
                            (record['kode_pulau'], record['nama_pulau'], record['kode_provinsi'], record['kode_kabupaten_kota'], record['latitude'], record['longitude'], record.get('notes', ''))
                        )
                
                print(f"Created table {table_name} with {len(data)} records")
            
            # Create indexes for better performance
            indexes = [
                "CREATE INDEX idx_provinsi_pulau_nama ON provinsi_pulau (nama_provinsi)",
                "CREATE INDEX idx_kabupaten_kota_pulau_nama ON kabupaten_kota_pulau (nama_kabupaten_kota)",
                "CREATE INDEX idx_kabupaten_kota_pulau_provinsi ON kabupaten_kota_pulau (kode_provinsi)",
                "CREATE INDEX idx_pulau_locations_kode ON pulau_locations (kode_pulau)",
                "CREATE INDEX idx_pulau_locations_nama ON pulau_locations (nama_pulau)",
                "CREATE INDEX idx_pulau_locations_provinsi ON pulau_locations (kode_provinsi)",
                "CREATE INDEX idx_pulau_locations_kabupaten ON pulau_locations (kode_kabupaten_kota)",
                "CREATE INDEX idx_pulau_locations_coordinates ON pulau_locations (latitude, longitude)",
                "CREATE INDEX idx_pulau_locations_latitude ON pulau_locations (latitude)",
                "CREATE INDEX idx_pulau_locations_longitude ON pulau_locations (longitude)"
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
            f.write("WILAYAH PULAU DATA NORMALIZATION REPORT\n")
            f.write("=" * 50 + "\n\n")
            f.write(f"Generated on: 2025-09-13\n")
            f.write(f"Source file: {self.input_sql_file}\n")
            f.write("Data source: Island/Pulau geographic coordinates dataset\n\n")
            
            f.write("RECORD COUNTS:\n")
            f.write("-" * 20 + "\n")
            total_records = 0
            for category, data in categorized_data.items():
                count = len(data)
                total_records += count
                f.write(f"{category.title():<25}: {count:>8,} records\n")
            
            f.write(f"{'Total':<25}: {total_records:>8,} records\n\n")
            
            # Calculate coordinate statistics
            pulau_data = categorized_data.get('pulau_locations', [])
            if pulau_data:
                valid_coords = [p for p in pulau_data if p['latitude'] != 0 or p['longitude'] != 0]
                min_lat = min(p['latitude'] for p in valid_coords) if valid_coords else 0
                max_lat = max(p['latitude'] for p in valid_coords) if valid_coords else 0
                min_lng = min(p['longitude'] for p in valid_coords) if valid_coords else 0
                max_lng = max(p['longitude'] for p in valid_coords) if valid_coords else 0
                
                f.write("GEOGRAPHIC STATISTICS:\n")
                f.write("-" * 25 + "\n")
                f.write(f"Valid Coordinates       : {len(valid_coords):>8,} locations\n")
                f.write(f"Latitude Range          : {min_lat:>8.4f} to {max_lat:>8.4f}\n")
                f.write(f"Longitude Range         : {min_lng:>8.4f} to {max_lng:>8.4f}\n\n")
            
            f.write("OUTPUT FILES:\n")
            f.write("-" * 20 + "\n")
            f.write("CSV Files:\n")
            for category in categorized_data.keys():
                f.write(f"  - {category}.csv\n")
            
            f.write("\nSQL Files:\n")
            for category in categorized_data.keys():
                f.write(f"  - {category}.sql\n")
            
            f.write("\nDatabase:\n")
            f.write("  - wilayah_pulau_normalized.db (SQLite)\n")
            
            f.write("\nNORMALIZATION SCHEMA:\n")
            f.write("-" * 30 + "\n")
            f.write("1. provinsi_pulau (kode_provinsi, nama_provinsi)\n")
            f.write("2. kabupaten_kota_pulau (kode_kabupaten_kota, kode_provinsi, nama_kabupaten_kota)\n")
            f.write("3. pulau_locations (id, kode_provinsi, nama_provinsi, kode_kabupaten_kota, nama_kabupaten_kota, latitude, longitude)\n")
            
            f.write("\nNOTES:\n")
            f.write("-" * 10 + "\n")
            f.write("- Each record in pulau_locations represents a unique island coordinate\n")
            f.write("- Latitude and longitude are in decimal degrees\n")
            f.write("- Zero coordinates (0.0, 0.0) indicate missing or invalid location data\n")
        
        print(f"Summary report created: {report_file}")
    
    def normalize(self):
        """Main method to perform normalization"""
        print("Starting wilayah pulau data normalization...")
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
    
    parser = argparse.ArgumentParser(description='Normalize wilayah pulau data into 3 separate tables')
    parser.add_argument('input_file', help='Input SQL file path (default: ../../db/wilayah_pulau.sql)', 
                       nargs='?', default='../../db/wilayah_pulau.sql')
    parser.add_argument('-o', '--output', help='Output directory (default: normalized_data)', 
                       default='normalized_data')
    
    args = parser.parse_args()
    
    if not os.path.exists(args.input_file):
        print(f"Error: Input file '{args.input_file}' not found!")
        return 1
    
    try:
        normalizer = WilayahPulauNormalizer(args.input_file, args.output)
        normalizer.normalize()
        return 0
    except Exception as e:
        print(f"Error: {e}")
        return 1


if __name__ == "__main__":
    exit(main())