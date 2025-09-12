# Normalize DB - Wilayah SQL

Folder ini berisi program Python untuk menormalisasi data wilayah Indonesia dari format hierarkis menjadi 4 tabel yang terstruktur.

## Struktur Folder

```
normalize_db/
└── wilayah_sql/
    ├── normalize_wilayah.py      # Program utama normalisasi
    ├── test_database.py          # Script testing database
    ├── advanced_queries.py       # Contoh query kompleks
    ├── requirements.txt          # Dependencies (standard library only)
    ├── NORMALIZATION_README.md   # Dokumentasi lengkap
    ├── PROJECT_SUMMARY.md        # Ringkasan project
    ├── README.md                 # File ini
    └── normalized_data/          # Output folder (dibuat otomatis)
        ├── *.csv                 # File CSV hasil normalisasi
        ├── *.sql                 # File SQL dengan DDL lengkap
        ├── wilayah_normalized.db # Database SQLite
        └── normalization_report.txt # Laporan hasil
```

## Quick Start

1. **Normalisasi Data**

   ```bash
   cd normalize_db/wilayah_sql
   python normalize_wilayah.py
   ```

2. **Test Database**

   ```bash
   python test_database.py
   ```

3. **Jalankan Query Lanjutan**
   ```bash
   python advanced_queries.py
   ```

## Input & Output

### Input

- File: `../../db/wilayah.sql` (relatif dari folder ini)
- Format: SQL dengan data hierarkis wilayah Indonesia
- Records: 88,745 total

### Output

- **4 Tabel Ternormalisasi**: provinsi, kabupaten_kota, kecamatan, desa_kelurahan
- **Format**: CSV, SQL, SQLite Database
- **Laporan**: Summary lengkap hasil normalisasi

## Hasil Normalisasi

| Tabel          | Records | Deskripsi                                        |
| -------------- | ------- | ------------------------------------------------ |
| provinsi       | 38      | Data 38 provinsi Indonesia                       |
| kabupaten_kota | 501     | Data kabupaten/kota dengan FK ke provinsi        |
| kecamatan      | 7,069   | Data kecamatan dengan FK ke kabupaten & provinsi |
| desa_kelurahan | 81,137  | Data desa/kelurahan dengan FK ke semua level     |

## Database Schema

```sql
-- Tabel utama dengan relasi foreign key
provinsi (kode_provinsi PK, nama_provinsi)
    ↑
kabupaten_kota (kode_kabupaten_kota PK, kode_provinsi FK, nama_kabupaten_kota)
    ↑
kecamatan (kode_kecamatan PK, kode_kabupaten_kota FK, kode_provinsi FK, nama_kecamatan)
    ↑
desa_kelurahan (kode_desa_kelurahan PK, kode_kecamatan FK, kode_kabupaten_kota FK, kode_provinsi FK, nama_desa_kelurahan)
```

## Keunggulan

- ✅ **Struktur Optimal**: Database ternormalisasi dengan foreign keys
- ✅ **Performa Tinggi**: Index strategis untuk query cepat
- ✅ **Integritas Data**: Constraints untuk konsistensi data
- ✅ **Multi Format**: Output CSV, SQL, dan SQLite
- ✅ **Ready to Use**: Siap untuk produksi dan integrasi

## Author

**sukmaajidigital**

## License

MIT License - Lihat file LICENSE di root repository
