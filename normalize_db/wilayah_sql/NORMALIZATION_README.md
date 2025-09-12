# Wilayah Data Normalization Tool

Program Python untuk menormalisasi data wilayah Indonesia dari satu tabel menjadi 4 tabel terpisah yang lebih terstruktur dan efisien.

## Deskripsi

Tool ini mengkonversi data wilayah hierarkis dari file `wilayah.sql` menjadi 4 tabel yang dinormalisasi:

1. **provinsi** - Data provinsi
2. **kabupaten_kota** - Data kabupaten/kota dengan relasi ke provinsi
3. **kecamatan** - Data kecamatan dengan relasi ke kabupaten/kota dan provinsi
4. **desa_kelurahan** - Data desa/kelurahan dengan relasi ke semua level di atasnya

## Fitur

- ✅ Parse file SQL otomatis
- ✅ Kategorisasi data berdasarkan hierarki kode wilayah
- ✅ Export ke format CSV
- ✅ Generate file SQL dengan struktur tabel yang dinormalisasi
- ✅ Buat database SQLite siap pakai
- ✅ Laporan summary lengkap
- ✅ Foreign key constraints untuk integritas data
- ✅ Index untuk performa query yang optimal

## Persyaratan Sistem

- Python 3.6 atau lebih baru
- Tidak memerlukan library eksternal (menggunakan standard library)

## Cara Penggunaan

### 1. Penggunaan Dasar

```bash
python normalize_wilayah.py
```

Ini akan memproses file `db/wilayah.sql` dan menghasilkan output di folder `normalized_data/`.

### 2. Dengan Parameter Custom

```bash
python normalize_wilayah.py db/wilayah.sql -o output_folder
```

### 3. Help

```bash
python normalize_wilayah.py -h
```

## Output yang Dihasilkan

Dalam folder output (default: `normalized_data/`), akan dibuat:

### File CSV

- `provinsi.csv`
- `kabupaten_kota.csv`
- `kecamatan.csv`
- `desa_kelurahan.csv`

### File SQL

- `provinsi.sql`
- `kabupaten_kota.sql`
- `kecamatan.sql`
- `desa_kelurahan.sql`

### Database

- `wilayah_normalized.db` - SQLite database siap pakai

### Laporan

- `normalization_report.txt` - Summary hasil normalisasi

## Struktur Database Hasil Normalisasi

### Tabel `provinsi`

```sql
CREATE TABLE provinsi (
    kode_provinsi VARCHAR(2) PRIMARY KEY,
    nama_provinsi VARCHAR(100) NOT NULL
);
```

### Tabel `kabupaten_kota`

```sql
CREATE TABLE kabupaten_kota (
    kode_kabupaten_kota VARCHAR(5) PRIMARY KEY,
    kode_provinsi VARCHAR(2) NOT NULL,
    nama_kabupaten_kota VARCHAR(100) NOT NULL,
    FOREIGN KEY (kode_provinsi) REFERENCES provinsi(kode_provinsi)
);
```

### Tabel `kecamatan`

```sql
CREATE TABLE kecamatan (
    kode_kecamatan VARCHAR(8) PRIMARY KEY,
    kode_kabupaten_kota VARCHAR(5) NOT NULL,
    kode_provinsi VARCHAR(2) NOT NULL,
    nama_kecamatan VARCHAR(100) NOT NULL,
    FOREIGN KEY (kode_kabupaten_kota) REFERENCES kabupaten_kota(kode_kabupaten_kota)
);
```

### Tabel `desa_kelurahan`

```sql
CREATE TABLE desa_kelurahan (
    kode_desa_kelurahan VARCHAR(13) PRIMARY KEY,
    kode_kecamatan VARCHAR(8) NOT NULL,
    kode_kabupaten_kota VARCHAR(5) NOT NULL,
    kode_provinsi VARCHAR(2) NOT NULL,
    nama_desa_kelurahan VARCHAR(100) NOT NULL,
    FOREIGN KEY (kode_kecamatan) REFERENCES kecamatan(kode_kecamatan)
);
```

## Contoh Query

Setelah normalisasi, Anda dapat melakukan query yang lebih efisien:

```sql
-- Mendapatkan semua kabupaten di provinsi Aceh
SELECT * FROM kabupaten_kota WHERE kode_provinsi = '11';

-- Mendapatkan hierarki lengkap untuk suatu desa
SELECT
    p.nama_provinsi,
    k.nama_kabupaten_kota,
    kc.nama_kecamatan,
    d.nama_desa_kelurahan
FROM desa_kelurahan d
JOIN kecamatan kc ON d.kode_kecamatan = kc.kode_kecamatan
JOIN kabupaten_kota k ON d.kode_kabupaten_kota = k.kode_kabupaten_kota
JOIN provinsi p ON d.kode_provinsi = p.kode_provinsi
WHERE d.kode_desa_kelurahan = '11.01.01.2001';

-- Menghitung jumlah desa per kecamatan
SELECT
    kc.nama_kecamatan,
    COUNT(d.kode_desa_kelurahan) as jumlah_desa
FROM kecamatan kc
LEFT JOIN desa_kelurahan d ON kc.kode_kecamatan = d.kode_kecamatan
GROUP BY kc.kode_kecamatan, kc.nama_kecamatan
ORDER BY jumlah_desa DESC;
```

## Keunggulan Normalisasi

1. **Efisiensi Storage**: Menghilangkan redundansi data
2. **Performa Query**: Index yang optimal untuk pencarian
3. **Integritas Data**: Foreign key constraints menjamin konsistensi
4. **Fleksibilitas**: Mudah untuk query spesifik per level wilayah
5. **Scalability**: Struktur yang mudah dikembangkan

## Troubleshooting

### Error: Input file not found

Pastikan file `db/wilayah.sql` ada di lokasi yang benar atau sesuaikan path input.

### Error: Permission denied

Pastikan program memiliki akses tulis ke folder output.

### Database error

Pastikan tidak ada proses lain yang menggunakan file database output.

## Lisensi

MIT License - Lihat file LICENSE untuk detail lengkap.

## Kontribusi

Kontribusi sangat diterima! Silakan buat issue atau pull request.

---

**Catatan**: Program ini menggunakan data sesuai Kepmendagri No 300.2.2-2138 Tahun 2025.
