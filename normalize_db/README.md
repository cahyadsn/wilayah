# Wilayah Indonesia - Database Normalization with Python

> tools python untuk normalisasi database yang ada, untuk memudahkan pengolahan. secara relational database

## Daftar Isi

- [📁 Struktur Direktori](#-struktur-direktori)
- [🎯 Deskripsi Tools](#-deskripsi-tools)
- [🚀 Quick Start](#-quick-start)
- [📊 Detail Output Files](#-detail-output-files)
- [🛠️ Persyaratan Sistem](#️-persyaratan-sistem)
- [📖 Dokumentasi Lengkap](#-dokumentasi-lengkap)
- [🔗 Contoh Integrasi Database](#-contoh-integrasi-database)
- [📈 Statistik & Performance](#-statistik--performance)
- [🔧 Troubleshooting](#-troubleshooting)
- [📞 Support & Kontribusi](#-support--kontribusi)

## 📁 Struktur Direktori

```
normalize_db/
├── README.md                   # Dokumentasi global (file ini)
├── wilayah/                    # Tool utama - data hierarchical wilayah
│   └── normalize_wilayah.py
├── wilayah_level_1_2/          # Tool geografis - koordinat & boundaries
│   └── normalize_wilayah_level_1_2.py
├── wilayah_luas/               # Tool area - data luas wilayah BIG
│   └── normalize_wilayah_luas.py
├── wilayah_penduduk/           # Tool demografi - data penduduk
│   └── normalize_wilayah_penduduk.py
└── wilayah_pulau/              # Tool geografis - koordinat pulau
    └── normalize_wilayah_pulau.py
```

## Deskripsi

### 1. **wilayah** - Data Wilayah Utama

- **File Source**: `../db/wilayah.sql`
- **Output Tables**: 4 tables
  - `provinsi`
  - `kabupaten_kota`
  - `kecamatan`
  - `desa_kelurahan`
- **Fitur Khusus**:
  - Struktur hierarchical lengkap
  - Foreign key constraints
  - Indexing optimal untuk query cepat
  - Validasi integritas data

### 2. **wilayah_level_1_2** - Data Geografis & Boundaries 🗺️

- **File Source**: `../db/wilayah_level_1_2.sql`
- **Output Tables**: 2 tables
  - `provinsi_geo`
  - `kabupaten_kota_geo`
- **Fitur Khusus**:
  - Koordinat latitude/longitude
  - Data elevasi dan timezone
  - Polygon boundaries untuk mapping
  - Data luas dan penduduk terintegrasi

### 3. **wilayah_luas** - Data Luas Wilayah BIG 📏

- **File Source**: `../db/wilayah_luas.sql`
- **Output Tables**: 2 tables
  - `provinsi_luas`
  - `kabupaten_kota_luas`
- **Fitur Khusus**:
  - Data resmi dari BIG (Badan Informasi Geospasial)
  - Pengukuran area dalam km²
  - Kompatibel dengan sistem GIS
  - Validasi konsistensi area

### 4. **wilayah_penduduk** - Data Demografi 👥

- **File Source**: `../db/wilayah_penduduk.sql`
- **Output Tables**: 2 tables
  - `provinsi_penduduk`
  - `kabupaten_kota_penduduk`
- **Fitur Khusus**:
  - Data penduduk pria dan wanita terpisah
  - Total penduduk per wilayah
  - Indexing untuk analisis demografi
  - Format standar untuk integrasi BI

### 5. **wilayah_pulau** - Koordinat Pulau Indonesia 🏝️

- **File Source**: `../db/wilayah_pulau.sql`
- **Output Tables**: 3 tables
  - `provinsi_pulau`
  - `kabupaten_kota_pulau`
  - `pulau_locations`
- **Fitur Khusus**:
  - Koordinat detail 17,222+ pulau
  - Metadata lokasi dan catatan
  - Optimized untuk aplikasi mapping
  - Spatial indexing untuk query geografis

## 🚀 Quick Start

### Menjalankan Semua Tools (Batch Processing)

```powershell
# Clone atau navigate ke directory
cd d:\repository\wilayah\normalize_db

# 1. Data utama wilayah (PRIORITAS UTAMA)
cd wilayah
python normalize_wilayah.py
cd ..

# 2. Data geografis
cd wilayah_level_1_2
python normalize_wilayah_level_1_2.py
cd ..

# 3. Data luas wilayah
cd wilayah_luas
python normalize_wilayah_luas.py
cd ..

# 4. Data penduduk
cd wilayah_penduduk
python normalize_wilayah_penduduk.py
cd ..

# 5. Data pulau/koordinat
cd wilayah_pulau
python normalize_wilayah_pulau.py
cd ..
```

### Menjalankan Tool Spesifik

```powershell
# Format umum
cd [folder_tool]
python [nama_script.py] [input_file] -o [output_directory]

# Contoh dengan custom parameter
cd wilayah
python normalize_wilayah.py ../../db/wilayah.sql -o my_output_folder
```

### Parameter Command Line

Semua tools mendukung parameter berikut:

```powershell
python normalize_wilayah.py --help           # Bantuan lengkap
python normalize_wilayah.py input.sql        # Input file custom
python normalize_wilayah.py -o output_dir    # Output directory custom
```

## Detail Output Files

Setiap tool menghasilkan struktur output yang konsisten:

### Struktur Output Folder

```
normalized_data/
├── *.csv                          # File CSV untuk import
├── *.sql                          # File SQL dengan struktur + data
├── [nama_tool]_normalized.db       # Database SQLite siap pakai
└── normalization_report.txt       # Laporan lengkap proses
```

### Format File Yang Dihasilkan

#### 1. **CSV Files**

- Format standard untuk import ke Excel, LibreOffice, atau database lain
- Encoding UTF-8 untuk karakter Indonesia
- Header kolom yang jelas dan konsisten

#### 2. **SQL Files**

- Complete table structure dengan constraints
- Foreign key relationships
- Optimized indexing
- Data lengkap dengan INSERT statements
- Kompatibel dengan MySQL, PostgreSQL, SQL Server

#### 3. **SQLite Database**

- Database siap pakai tanpa setup tambahan
- Foreign key constraints aktif
- Indexing untuk performa optimal
- Compatible dengan semua SQLite clients

#### 4. **Normalization Report**

- Statistik lengkap proses normalizasi
- Jumlah records per tabel
- Informasi sumber data
- Waktu eksekusi dan performance metrics

## System Requirements

### Python Dependencies

```python
# Built-in libraries only - NO external dependencies required!
import sqlite3    # Database operations
import csv        # CSV file handling
import os         # File system operations
import re         # Regular expressions
import typing     # Type hints
```

### Tested Environments

- ✅ Windows 10/11 dengan PowerShell
- ✅ Windows 10/11 dengan Command Prompt
- ✅ Linux Ubuntu 20.04+
- ✅ macOS 10.15+
- ✅ Python 3.6, 3.7, 3.8, 3.9, 3.10, 3.11

## Dokumentasi

### Source Data Information

| Dataset               | Source Authority                       | Format             | Last Updated |
| --------------------- | -------------------------------------- | ------------------ | ------------ |
| wilayah.sql           | Kepmendagri No 300.2.2-2138 Tahun 2025 | Hierarchical SQL   | 2025         |
| wilayah_level_1_2.sql | Kemendagri + BIG                       | Geographic SQL     | 2025         |
| wilayah_luas.sql      | BIG (Badan Informasi Geospasial)       | Area Measurements  | 2024         |
| wilayah_penduduk.sql  | Kemendagri Demographics                | Population Data    | 2025         |
| wilayah_pulau.sql     | Geographic Survey                      | Island Coordinates | 2024         |

### Database Schema Design

#### Primary Keys & Relationships

```sql
-- Hierarchical structure
provinsi.kode_provinsi → kabupaten_kota.kode_provinsi
kabupaten_kota.kode_kabupaten_kota → kecamatan.kode_kabupaten_kota
kecamatan.kode_kecamatan → desa_kelurahan.kode_kecamatan

-- Cross-dataset relationships
provinsi.kode_provinsi = provinsi_geo.kode_provinsi
provinsi.kode_provinsi = provinsi_luas.kode_provinsi
provinsi.kode_provinsi = provinsi_penduduk.kode_provinsi
```

#### Indexing Strategy

- **Primary Keys**: Automatic unique indexes
- **Foreign Keys**: Indexed for JOIN performance
- **Name Fields**: Text search indexes
- **Numeric Fields**: Range query indexes
- **Geographic Coordinates**: Spatial indexes where applicable

## 🔗 Contoh Integrasi Database

### 1. Analisis Lengkap Per Provinsi

```sql
SELECT
    w.nama_provinsi,
    w.ibu_kota,
    l.luas as luas_km2,
    p.penduduk_total,
    p.penduduk_pria,
    p.penduduk_wanita,
    g.latitude,
    g.longitude,
    COUNT(pl.id) as jumlah_pulau,
    ROUND(p.penduduk_total / l.luas, 2) as kepadatan_per_km2
FROM provinsi w
LEFT JOIN provinsi_luas l ON w.kode_provinsi = l.kode_provinsi
LEFT JOIN provinsi_penduduk p ON w.kode_provinsi = p.kode_provinsi
LEFT JOIN provinsi_geo g ON w.kode_provinsi = g.kode_provinsi
LEFT JOIN pulau_locations pl ON w.kode_provinsi = pl.kode_provinsi
GROUP BY w.kode_provinsi
ORDER BY p.penduduk_total DESC;
```

### 2. Query Geographic Proximity

```sql
-- Cari pulau dalam radius tertentu dari koordinat
SELECT
    p.nama_pulau,
    p.latitude,
    p.longitude,
    pr.nama_provinsi,
    kk.nama_kabupaten_kota
FROM pulau_locations p
JOIN provinsi_pulau pr ON p.kode_provinsi = pr.kode_provinsi
JOIN kabupaten_kota_pulau kk ON p.kode_kabupaten_kota = kk.kode_kabupaten_kota
WHERE p.latitude BETWEEN -6.5 AND -6.0
  AND p.longitude BETWEEN 106.5 AND 107.0
ORDER BY p.latitude, p.longitude;
```

### 3. Analisis Demografi Multi-Level

```sql
-- Perbandingan penduduk kabupaten vs kecamatan
SELECT
    kk.nama_kabupaten_kota,
    kkp.penduduk_total as penduduk_kabupaten,
    COUNT(k.kode_kecamatan) as jumlah_kecamatan,
    AVG(kp.penduduk_total) as rata_rata_penduduk_kecamatan,
    MAX(kp.penduduk_total) as kecamatan_terbesar
FROM kabupaten_kota kk
LEFT JOIN kabupaten_kota_penduduk kkp ON kk.kode_kabupaten_kota = kkp.kode_kabupaten_kota
LEFT JOIN kecamatan k ON kk.kode_kabupaten_kota = k.kode_kabupaten_kota
LEFT JOIN kecamatan_penduduk kp ON k.kode_kecamatan = kp.kode_kecamatan
GROUP BY kk.kode_kabupaten_kota
HAVING COUNT(k.kode_kecamatan) > 0
ORDER BY kkp.penduduk_total DESC;
```

### 4. Export Data untuk GIS Applications

```sql
-- Format untuk import ke QGIS, ArcGIS, atau Google Earth
SELECT
    CONCAT(g.nama_provinsi, ' - ', g.nama_kabupaten_kota) as name,
    g.latitude as lat,
    g.longitude as lng,
    l.luas as area_km2,
    p.penduduk_total as population,
    g.elevation as altitude_m,
    g.timezone as tz
FROM kabupaten_kota_geo g
LEFT JOIN kabupaten_kota_luas l ON g.kode_kabupaten_kota = l.kode_kabupaten_kota
LEFT JOIN kabupaten_kota_penduduk p ON g.kode_kabupaten_kota = p.kode_kabupaten_kota
WHERE g.latitude != 0 AND g.longitude != 0
ORDER BY g.nama_provinsi, g.nama_kabupaten_kota;
```

## 📈 View Test Statistik & Performance

### Dataset Overview

| Tool              | Input Records | Output Tables | Processing Time\* | Output Size\* |
| ----------------- | ------------- | ------------- | ----------------- | ------------- |
| wilayah           | 88,745        | 4             | ~30-45 sec        | ~15-20 MB     |
| wilayah_level_1_2 | 552           | 2             | ~2-3 sec          | ~150 KB       |
| wilayah_luas      | 552           | 2             | ~2-3 sec          | ~100 KB       |
| wilayah_penduduk  | 553           | 2             | ~2-3 sec          | ~120 KB       |
| wilayah_pulau     | 17,222        | 3             | ~8-12 sec         | ~2-3 MB       |

\*Performance pada Intel i5, 8GB RAM, SSD storage

### Total Coverage

- **Total Records**: 107,624 records
- **Total Tables**: 13 normalized tables
- **Geographic Coverage**: Seluruh Indonesia (34 provinsi + 3 provinsi baru)
- **Administrative Levels**: 4 levels (Provinsi → Kabupaten/Kota → Kecamatan → Desa/Kelurahan)
- **Island Coverage**: 17,222+ pulau/island locations

### Quality Metrics

- **Data Integrity**: 100% foreign key constraints
- **Completeness**: ~99.5% complete data (beberapa koordinat kosong normal)
- **Accuracy**: Data resmi dari sumber pemerintah
- **Consistency**: Format kode wilayah standar nasional

## Detail Kontributor

### Author & Maintainer

- **Fork Repository**: [sukmaajidigital/wilayah](https://github.com/sukmaajidigital/wilayah)

### Kontribusi

1. **Bug Reports**: Buat issue di GitHub dengan detail error
2. **Feature Requests**: Diskusikan di GitHub Issues
3. **Pull Requests**: Welcome untuk improvements
4. **Documentation**: Bantuan dokumentasi selalu diapresiasi

### Resources & Links

- **Data Source**: [github.com/cahyadsn/wilayah](https://github.com/cahyadsn/wilayah)

---
