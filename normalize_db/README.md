# Normalize DB

Folder ini berisi berbagai tools dan utilities untuk normalisasi database wilayah Indonesia.

## Struktur

```
normalize_db/
├── README.md           # File ini
└── wilayah_sql/        # Tools normalisasi data wilayah SQL
    ├── normalize_wilayah.py      # Program utama
    ├── test_database.py          # Testing tools
    ├── advanced_queries.py       # Query examples
    ├── NORMALIZATION_README.md   # Dokumentasi lengkap
    ├── PROJECT_SUMMARY.md        # Summary project
    └── normalized_data/          # Output folder
```

## Wilayah SQL Normalizer

Tool utama untuk mengkonversi data wilayah hierarkis menjadi struktur database yang optimal.

### Features

- Parse data wilayah dari file SQL
- Normalisasi ke 4 tabel terpisah (provinsi, kabupaten/kota, kecamatan, desa/kelurahan)
- Export ke CSV, SQL, dan SQLite database
- Foreign key constraints dan indexing optimal
- Testing dan query tools

### Quick Start

```bash
cd wilayah_sql/
python normalize_wilayah.py
```

## Rencana Pengembangan

Folder ini dapat diperluas untuk tools normalisasi database lainnya:

- **wilayah_mongodb/** - Tools untuk MongoDB normalization
- **wilayah_postgresql/** - Tools untuk PostgreSQL specific features
- **wilayah_api/** - API endpoints untuk data wilayah
- **data_validation/** - Tools validasi data wilayah

## Author

**sukmaajidigital** - Repository maintainer

## License

MIT License
