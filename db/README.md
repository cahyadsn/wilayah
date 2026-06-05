# Database Wilayah Indonesia

Direktori ini berisi skrip SQL untuk mengimpor data Kode dan Data Wilayah Administrasi Pemerintahan serta Pulau Indonesia ke dalam database MySQL/MariaDB. Data ini disusun berdasarkan Keputusan Menteri Dalam Negeri (Kepmendagri) terbaru.

## Daftar File Utama

| File | Deskripsi |
| --- | --- |
| `wilayah.sql` | Data utama kode dan nama wilayah (Provinsi, Kab/Kota, Kecamatan, Desa/Kelurahan) sesuai Kepmendagri No 300.2.2-2430 Tahun 2025. |
| `wilayah_level_1_2.sql` | Data wilayah tingkat 1 (Provinsi) dan tingkat 2 (Kabupaten/Kota) dilengkapi dengan koordinat, elevasi, zona waktu, luas, jumlah penduduk, dan boundaries. |
| `wilayah_pulau.sql` | Data kode dan nama pulau sesuai Kepmendagri No 300.2.2-2430 Tahun 2025. |
| `wilayah_penduduk.sql` | Data jumlah penduduk (pria, wanita, total) per wilayah sesuai Kepmendagri No 300.2.2-2430 Tahun 2025. |
| `wilayah_luas.sql` | Data luas wilayah per provinsi dan kabupaten/kota sesuai Kepmendagri No 300.2.2-2430 Tahun 2025 dan Badan Informasi Geospasial (BIG). |

## Struktur Tabel Utama

### Tabel `wilayah`
Digunakan dalam `wilayah.sql`.
```sql
CREATE TABLE wilayah (
    kode varchar(13) NOT NULL,
    nama varchar(100) NOT NULL,
    PRIMARY KEY (kode)
);
```

### Tabel `wilayah_pulau`
Digunakan dalam `wilayah_pulau.sql`.
```sql
CREATE TABLE wilayah_pulau (
    kode VARCHAR(11),
    nama VARCHAR(255),
    lat FLOAT,
    lng FLOAT,
    status VARCHAR(3),
    luas FLOAT DEFAULT NULL,
    notes TEXT,
    PRIMARY KEY (kode)
);
```

### Tabel `wilayah_penduduk`
Digunakan dalam `wilayah_penduduk.sql`.
```sql
CREATE TABLE wilayah_penduduk (
    kode varchar(13) NOT NULL,
    nama varchar(100) NOT NULL,
    pria integer NOT NULL,
    wanita integer NOT NULL,
    total integer NOT NULL,
    PRIMARY KEY (kode)
);
```

### Tabel `wilayah_luas`
Digunakan dalam `wilayah_luas.sql`.
```sql
CREATE TABLE wilayah_luas (
    kode varchar(13) NOT NULL,
    nama varchar(100) DEFAULT NULL,
    luas double NOT NULL,
    PRIMARY KEY (kode)
);
```

## Arsip Data (Folder `archive/`)

Folder `archive/` berisi data historis dari tahun-tahun sebelumnya untuk keperluan referensi atau perbandingan:

- **Wilayah:**
  - `wilayah_2023.sql` (Kepmendagri No. 100.1.1-6117 Tahun 2022)
  - `wilayah_2022.sql` (Permendagri No. 58 Tahun 2021)
  - `wilayah_2020.sql` (Permendagri No. 72 Tahun 2019)
  - `wilayah_2018.sql` (Permendagri No. 137 Tahun 2017)
  - `wilayah_2016.sql` (Permendagri No. 56 Tahun 2015)
  - `wilayah_level_1_2-2022.sql` (MySQL)
  - `wilayah_level_1_2_postgresql.sql` (PostgreSQL)

- **Pulau:**
  - `pulau_2025.sql` (Kepmendagri No 300.2.2-2138 Tahun 2025)
  - `pulau_2023.sql` (Kepmendagri No 100.1.1-6117 Tahun 2022)
  - `pulau_2022.sql` (Permendagri No 58 Tahun 2021)

---
**Author:** cahya dsn (cahyadsn@gmail.com)
**License:** MIT License
