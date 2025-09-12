# Wilayah Database Normalization Tools

**BISMILLAAHIRRAHMAANIRRAHIIM** - In the Name of Allah, Most Gracious, Most Merciful

> Kumpulan tools untuk menormalisasi data wilayah Indonesia dari struktur hierarchical menjadi struktur relational yang terorganisir.

## 📁 Struktur Direktori

```
normalize_db/
├── wilayah/            # Normalisasi data utama wilayah hierarchical
├── wilayah_level_1_2/      # Normalisasi data geografis level 1-2
├── wilayah_luas/           # Normalisasi data luas wilayah
├── wilayah_penduduk/       # Normalisasi data penduduk
└── wilayah_pulau/          # Normalisasi data pulau/koordinat
```

## 🎯 Deskripsi Tools

### 1. **wilayah** - Data Wilayah Utama

- **File SQL**: `db/wilayah.sql` (88,745 records)
- **Output Tables**: 4 tables (provinsi, kabupaten_kota, kecamatan, desa_kelurahan)
- **Fitur**: Struktur hierarchical lengkap dengan foreign key constraints

### 2. **wilayah_level_1_2** - Data Geografis

- **File SQL**: `db/wilayah_level_1_2.sql` (552 records)
- **Output Tables**: 2 tables (provinsi_geo, kabupaten_kota_geo)
- **Fitur**: Koordinat, elevasi, timezone, luas, penduduk, polygon boundaries

### 3. **wilayah_luas** - Data Luas Wilayah

- **File SQL**: `db/wilayah_luas.sql` (552 records)
- **Output Tables**: 2 tables (provinsi_luas, kabupaten_kota_luas)
- **Fitur**: Data luas area dari BIG (Badan Informasi Geospasial)

### 4. **wilayah_penduduk** - Data Penduduk

- **File SQL**: `db/wilayah_penduduk.sql` (553 records)
- **Output Tables**: 2 tables (provinsi_penduduk, kabupaten_kota_penduduk)
- **Fitur**: Data penduduk pria, wanita, dan total per wilayah

### 5. **wilayah_pulau** - Data Pulau/Koordinat

- **File SQL**: `db/wilayah_pulau.sql` (17,222 records)
- **Output Tables**: 3 tables (provinsi_pulau, kabupaten_kota_pulau, pulau_locations)
- **Fitur**: Koordinat pulau-pulau di Indonesia dengan detail lokasi

## 🚀 Cara Penggunaan

### Menjalankan Semua Normalisasi

```powershell
# 1. Normalisasi data utama wilayah
cd wilayah
python normalize_wilayah.py

# 2. Normalisasi data geografis
cd ../wilayah_level_1_2
python normalize_wilayah_level_1_2.py

# 3. Normalisasi data luas
cd ../wilayah_luas
python normalize_wilayah_luas.py

# 4. Normalisasi data penduduk
cd ../wilayah_penduduk
python normalize_wilayah_penduduk.py

# 5. Normalisasi data pulau
cd ../wilayah_pulau
python normalize_wilayah_pulau.py
```

### Menjalankan dengan Parameter Custom

```powershell
python normalize_wilayah.py path/to/input.sql -o output_directory
```

## 📊 Output Files

Setiap tool menghasilkan:

1. **CSV Files** - Format untuk import ke aplikasi lain
2. **SQL Files** - Complete table structure + data
3. **SQLite Database** - Ready-to-use database file
4. **Normalization Report** - Summary dan statistik

## 🛠️ Persyaratan Sistem

- **Python 3.6+**
- **Standard Library**: sqlite3, csv, os, re, typing
- **OS**: Windows/Linux/macOS

## 📄 Data Sources

- **Sumber Data**: Kepmendagri No 300.2.2-2138 Tahun 2025
- **Data Luas**: Badan Informasi Geospasial (BIG)
- **Data Geografis**: Koordinat dan boundaries resmi
- **Data Penduduk**: Data demografis terbaru per wilayah

## 📈 Statistik Database

| Dataset     | Records | Tables | Features               |
| ----------- | ------- | ------ | ---------------------- |
| Wilayah SQL | 88,745  | 4      | Hierarchical structure |
| Level 1-2   | 552     | 2      | Geographic data        |
| Luas        | 552     | 2      | Area measurements      |
| Penduduk    | 553     | 2      | Population data        |
| Pulau       | 17,222  | 3      | Island coordinates     |

## 🔗 Integration Examples

### Join Data Across Tables

```sql
-- Gabungkan data wilayah dengan data luas dan penduduk
SELECT
    w.nama_provinsi,
    l.luas,
    p.penduduk_total
FROM provinsi w
JOIN provinsi_luas l ON w.kode_provinsi = l.kode_provinsi
JOIN provinsi_penduduk p ON w.kode_provinsi = p.kode_provinsi;
```

### Geographic Analysis

```sql
-- Analisis geografis dengan koordinat
SELECT
    g.nama_provinsi,
    g.latitude,
    g.longitude,
    COUNT(pl.id) as jumlah_pulau
FROM provinsi_geo g
LEFT JOIN pulau_locations pl ON g.kode_provinsi = pl.kode_provinsi
GROUP BY g.kode_provinsi;
```

## 📞 Support & Contact

- **Author**: sukmaajidigital with GitHub Copilot
- **License**: MIT License
- **Created**: 2025-09-13

---

_"Dan Dialah yang menciptakan langit dan bumi dengan hak. Dan pada hari Dia mengatakan: 'Jadilah', maka jadilah. Firman-Nya adalah kebenaran." - Al-An'am: 73_
