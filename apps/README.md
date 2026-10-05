# wilayah v3.2.0
Aplikasi web visualisasi dan pencarian Kode serta Data Wilayah Administrasi Pemerintahan Indonesia dan Pulau sesuai Kepmendagri No 300.2.2-2430 Tahun 2025 dengan PHP + MySQL + AJaX + Leaflet.js.

[![GitHub license](https://img.shields.io/badge/license-MIT-blue.svg)](LICENSE)
[![GitHub issues](https://img.shields.io/github/issues/cahyadsn/wilayah.svg)](https://github.com/cahyadsn/wilayah/issues)
[![GitHub forks](https://img.shields.io/github/forks/cahyadsn/wilayah.svg)](https://github.com/cahyadsn/wilayah/network)
[![GitHub stars](https://img.shields.io/github/stars/cahyadsn/wilayah.svg)](https://github.com/cahyadsn/wilayah/stargazers)

---

## DEMO
Tautan demo web [apps versi 3.2.0](https://wilayah.cahyadsn.com/apps)

## SCREENSHOT
[![screenshot](https://github.com/cahyadsn/wilayah/blob/master/apps/img/2026_06_03_14_03_28.png?raw=true 'wilayah apps web demo v3.2.0')](https://wilayah.cahyadsn.com/apps)

---

## Ringkasan Data Wilayah (Level 1 & 2)
Aplikasi ini menggunakan basis data tabel `wilayah_level_1_2` (provinsi & kabupaten/kota) dan `wilayah_pulau` (daftar pulau) sesuai standar Kepmendagri No 300.2.2-2430 Tahun 2025 yang dilengkapi dengan titik koordinat (latitude/longitude), elevasi, zona waktu, batas poligon (boundaries), luas wilayah, dan estimasi jumlah penduduk.

| id_prov | nama                      | kab  | kota |
|:-------:|---------------------------|-----:|-----:|
| 11      | Aceh                      |   18 |    5 |
| 12      | Sumatera Utara            |   25 |    8 |
| 13      | Sumatera Barat            |   12 |    7 |
| 14      | Riau                      |   10 |    2 |
| 15      | Jambi                     |    9 |    2 |
| 16      | Sumatera Selatan          |   13 |    4 |
| 17      | Bengkulu                  |    9 |    1 |
| 18      | Lampung                   |   13 |    2 |
| 19      | Kepulauan Bangka Belitung |    6 |    1 |
| 21      | Kepulauan Riau            |    5 |    2 |
| 31      | DKI Jakarta               |    1 |    5 |
| 32      | Jawa Barat                |   18 |    9 |
| 33      | Jawa Tengah               |   29 |    6 |
| 34      | DI Yogyakarta             |    4 |    1 |
| 35      | Jawa Timur                |   29 |    9 |
| 36      | Banten                    |    4 |    4 |
| 51      | Bali                      |    8 |    1 |
| 52      | Nusa Tenggara Barat       |    8 |    2 |
| 53      | Nusa Tenggara Timur       |   21 |    1 |
| 61      | Kalimantan Barat          |   12 |    2 |
| 62      | Kalimantan Tengah         |   13 |    1 |
| 63      | Kalimantan Selatan        |   11 |    2 |
| 64      | Kalimantan Timur          |    7 |    3 |
| 65      | Kalimantan Utara          |    4 |    1 |
| 71      | Sulawesi Utara            |   11 |    4 |
| 72      | Sulawesi Tengah           |   12 |    1 |
| 73      | Sulawesi Selatan          |   21 |    3 |
| 74      | Sulawesi Tenggara         |   15 |    2 |
| 75      | Gorontalo                 |    5 |    1 |
| 76      | Sulawesi Barat            |    6 |    0 |
| 81      | Maluku                    |    9 |    2 |
| 82      | Maluku Utara              |    8 |    2 |
| 91      | Papua                     |    8 |    1 |
| 92      | Papua Barat               |    7 |    0 |
| 93      | Papua Selatan             |    4 |    0 |
| 94      | Papua Tengah              |    8 |    0 |
| 95      | Papua Pegunungan          |    8 |    0 |
| 96      | Papua Barat Daya          |    5 |    1 |
|         | **TOTAL**                 | **416** | **98** |

---

## Panduan Instalasi (Installation Guide)

### 1. Prasyarat Sistem (Prerequisites)
Pastikan lingkungan server / lokal Anda telah memenuhi persyaratan berikut:
- **PHP**: Versi `>= 8.0` (disarankan PHP 8.1 / 8.2 / 8.3)
- **Ekstensi PHP**: `pdo`, `pdo_mysql`, `json`, `session`, `mbstring`, `fileinfo`
- **Database Server**: MySQL `>= 5.7` / MySQL 8.0+ atau MariaDB `>= 10.3`
- **Web Server**: Apache / Nginx / Laragon / XAMPP / PHP Built-in Server
- **Composer**: (Opsional, untuk menjalankan test suite PHPUnit)

---

### 2. Langkah-Langkah Instalasi

#### Langkah 1: Kloning Repositori
Kloning repositori kode sumber ke direktori web server lokal Anda:
```bash
git clone https://github.com/cahyadsn/wilayah.git
cd wilayah
```

#### Langkah 2: Pembuatan dan Impor Database
1. Buat database baru di MySQL/MariaDB (misalnya dengan nama `wilayah`):
   ```sql
   CREATE DATABASE wilayah CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   ```
2. Impor tabel-tabel SQL yang dibutuhkan aplikasi:
   ```bash
   # Impor tabel level 1-2 (Provinsi & Kabupaten/Kota + koordinat & poligon) [WAJIB]
   mysql -u root -p wilayah < db/wilayah_level_1_2.sql

   # Impor tabel daftar pulau Indonesia [WAJIB untuk fitur panel pulau v3.2.0]
   mysql -u root -p wilayah < db/wilayah_pulau.sql

   # (Opsional) Impor tabel lengkap hierarki wilayah sampai kecamatan & kelurahan/desa
   mysql -u root -p wilayah < db/wilayah.sql

   # (Opsional) Impor data luas wilayah & kependudukan
   mysql -u root -p wilayah < db/wilayah_luas.sql
   mysql -u root -p wilayah < db/wilayah_penduduk.sql
   ```

#### Langkah 3: Konfigurasi Environment (`.env`)
Salin file template konfigurasi `.env.example` ke `.env`:
```bash
# Pada direktori apps/
cp apps/.env.example apps/.env
```
Buka file `apps/.env` dan sesuaikan kredensial database Anda:
```env
DB_HOST=localhost
DB_USER=root
DB_PASS=
DB_NAME=wilayah
APP_VER=3.2.0
```
> *Catatan: File `apps/inc/db.php` otomatis memuat variabel dari `apps/.env` atau `.env` root.*

#### Langkah 4: Pengaturan Izin Direktori Cache
Aplikasi menggunakan caching berbasis file untuk respon AJAX dan opsi dropdown. Pastikan folder cache memiliki izin tulis (writable):
```bash
# Linux / macOS
chmod -R 775 apps/cache/
```
*(Untuk Windows / Laragon / XAMPP, pastikan folder `apps/cache/` dapat diakses dan ditulis oleh user web server).*

---

### 3. Menjalankan Aplikasi

#### Opsi A: Menggunakan PHP Built-in Server (Praktis & Cepat)
Jalankan server PHP langsung dari root repositori:
```bash
php -S localhost:8000
```
Buka web browser dan akses:
```
http://localhost:8000/apps/
```

#### Opsi B: Menggunakan Web Server Stack (Laragon / XAMPP / Apache / Nginx)
- **Laragon**: Tempatkan repositori pada folder `C:/laragon/www/wilayah` atau `D:/laragon/repo/wilayah` dengan virtual host, lalu akses:
  ```
  http://localhost/wilayah/apps/
  # atau jika menggunakan virtual host:
  http://wilayah.test/apps/
  ```
- **XAMPP**: Tempatkan folder pada `C:/xampp/htdocs/wilayah` dan buka:
  ```
  http://localhost/wilayah/apps/
  ```

---

### 4. Menjalankan Pengujian Unit (Testing)
Untuk menjalankan pengujian otomatis PHPUnit:
```bash
# Install dependency pengujian jika belum terpasang
composer install

# Jalankan seluruh test suite
vendor/bin/phpunit
```

---

## Fitur Utama (Key Features)

1. **Peta Interaktif (Leaflet.js)**:
   - Menampilkan visualisasi batas poligon (boundaries) provinsi dan kabupaten/kota secara otomatis.
   - Pilihan multi-basemap (OpenStreetMap, CartoDB Positron, CartoDB Dark Matter, OpenTopoMap, Esri World Imagery).
   - Marker centroid dengan informasi koordinat, elevasi, luas wilayah, dan jumlah penduduk.
2. **Panel Daftar Pulau Interaktif (v3.2.0)**:
   - Panel sidebar kanan (`#islandPanel`) menampilkan daftar seluruh pulau di bawah provinsi atau kabupaten/kota yang sedang aktif.
   - Dilengkapi badge counter jumlah pulau (`#islandCount`), filter pencarian instan nama/kode pulau (`#islandSearch`), serta tombol toggle collapse dengan persistensi status di `localStorage`.
   - Interaksi peta satu klik: mengklik item pulau akan mengarahkan kamera peta (`flyTo`), membuat marker pulau, dan menampilkan popup info status & luas pulau.
3. **Dropdown Hierarkis Cepat (AJAX)**:
   - Pemilihan provinsi dan kabupaten/kota dinamis dan responsif berbasis AJAX.
4. **Pencarian Terbalik (Reverse Geocoding)**:
   - Mengidentifikasi wilayah administrasi berdasarkan koordinat klik pada peta menggunakan algoritma Ray-Casting (*point-in-polygon*).
5. **Dukungan Tema (Light & Dark Mode)**:
   - Tampilan antarmuka modern yang mendukung pergantian tema terang dan gelap.
6. **Keamanan & Optimasi**:
   - Proteksi CSRF Token pada request AJAX dan form login/logout.
   - Sanitasi input dan prepared statement PDO untuk mencegah SQL Injection & XSS.
   - Caching file tingkat server untuk respon query wilayah dan kompresi aset CSS/JS.

---

## Struktur Direktori `apps/`

```text
apps/
├── .env                  # Konfigurasi database & versi lokal
├── .env.example          # Template konfigurasi environment
├── index.php             # Halaman utama aplikasi (v3.2.0)
├── login.php             # Halaman login otentikasi aman
├── logout.php            # Endpoint logout & penghapusan session
├── README.md             # Dokumentasi dan panduan instalasi
├── cache/                # Direktori cache respon query & dropdown
├── css/                  # Berkas stylesheet CSS (styles.css, styles.min.css, dll.)
├── js/                   # Berkas JavaScript aplikasi (zepto.min.js, leaflet.js, ajax.js, dll.)
├── img/                  # Aset gambar, ikon, dan screenshot
└── inc/                  # Komponen backend PHP
    ├── db.php            # Handler koneksi database PDO & loader .env
    ├── session.php       # Manajemen session & helper CSRF token
    ├── geo_ajax.php      # Endpoint AJAX data wilayah & pulau
    ├── geo_helpers.php   # Helper query data provinsi & pulau
    ├── geo_js.php        # Generator skrip JS konfigurasi dinamis
    ├── geo_utils.php     # Helper kalkulasi geospasial
    └── reverse_lookup.php# Algoritma reverse geocoding
```

---

## Referensi
- Dokumen Referensi : https://github.com/cahyadsn/wilayah_ref
- Keputusan Menteri Dalam Negeri Nomor 300.2.2-2430 tahun 2025 Tentang Perubahan Atas Keputusan Menteri Dalam Negeri Nomor 300.2.2-2138 Таhun 2025 Tentang Pemberian Dan Pemutakhiran Kode, Data Wilayah Administrasi Pemerintahan, Dan Pulau (Ditetapkan pada 23 Juni 2025)
- Keputusan Menteri Dalam Negeri Nomor 300.2.2-2138 tahun 2025 Tentang Pemberian Dan Pemutakhiran Kode, Data Wilayah Administrasi Pemerintahan, Dan Pulau (Ditetapkan pada 25 April 2025)
- Keputusan Menteri Dalam Negeri Nomor 100.1.1-6117 Tahun 2022 Tentang Pemberian dan Pemutakhiran Kode, Data Wilayah Adminstrasi Pemerintahan, dan Pulau (Ditetapkan pada 9 November 2022)
- UU No 14 Tahun 2022 tentang Pembentukan Provinsi Papua Selatan (LN.2022/No.157, TLN No.6803, jdih.setneg.go.id: 15 hlm., 25 Juli 2022)
- UU No 15 Tahun 2022 tentang Pembentukan Provinsi Papua Tengah (LN.2022/No.158, TLN No.6804, jdih.setneg.go.id: 14 hlm., 25 Juli 2022)
- UU No 16 Tahun 2022 tentang Pembentukan Provinsi Papua Pegunungan (LN.2022/No.159, TLN No.6805, jdih.setneg.go.id: 14 hlm., 25 Juli 2022)
- Keputusan Menteri Dalam Negeri Nomor 050-145 Tahun 2022 Tentang Pemberian Kode, Data Wilayah Administrasi Pemerintahan, Dan Pulau Tahun 2021 (Kepmendagri No. 050-145 Tahun 2022, https://www.kemendagri.go.id/arsip/detail/10857/keputusan-menteri-dalam-negeri-nomor-050145-tahun-2022-tentang-pemberian-kode-data-wilayah-administrasi-pemerintahan-dan-pulau-tahun-2021 (Ditetapkan pada tanggal 14 Februari 2022)
- Peraturan Menteri Dalam Negeri Republik Indonesia Nomor 58 Tahun 2021 Tentang Kode, Data Wilayah Administrasi Pemerintahan, Dan Pulau https://paralegal.id/peraturan/peraturan-menteri-dalam-negeri-nomor-58-tahun-2021/ (Permendagri No.58 2021, Ditetapkan pada tanggal 13 Desember 2021,Berita Negara Tahun 2021 Nomor 1391)
- Penetapan Nama, Kode Dan Jumlah Desa Seluruh Indonesia Tahun 2020 (Kepmendagri No. 146.1-4717 - 2020) http://binapemdes.kemendagri.go.id/produkhukum/detil/keputusan-menteri-dalam-negeri-nomor-1461-4717-tahun-2020 (Ditetapkan pada tanggal 21 Desember 2020)
- Kode dan Data Wilayah Administrasi Pemerintahan (Permendagri No.72-2019) https://www.kemendagri.go.id/page/read/48/peraturan-menteri-dalam-negeri-no72-tahun-2019 (Berita Negara Republik Indonesia Tahun 2019 Nomor 1327, Ditetapkan pada tanggal 8 Oktober 2019)
- Kode dan Data Wilayah Administrasi Pemerintahan (Permendagri No.137-2017) http://www.kemendagri.go.id/produk-hukum/2018/01/18/kode-dan-data-wilayah-administrasi-pemerintahan-tahun-2017 (Berita Negara Republik Indonesia Tahun 2017 Nomor 1955, Ditetapkan pada tanggal 27 Desember 2017)
- Kode dan Data Wilayah Administrasi Pemerintahan (Permendagri No.56-2015) www.kemendagri.go.id/pages/data-wilayah (Berita Negara Republik Indonesia Tahun 2015 Nomor 1045, Ditetapkan pada tanggal 29 Juni 2015)

---

## Catatan Rilis (Changelog)

### v3.2.0 (2026-10-05)
- **Interactive Island Panel (Daftar Pulau) pada Right Sidebar**:
  - Menambahkan sidebar panel interaktif kanan (`#islandPanel`) yang menampilkan daftar kode dan nama pulau sesuai lokasi aktif provinsi atau kabupaten/kota.
  - Menambahkan badge jumlah pulau (`#islandCount`), judul lokasi aktif (`#islandLocationName`), form pencarian instan (`#islandSearch`), dan daftar pulau (`#islandList`).
  - Integrasi interaktif peta: mengklik item pulau akan menggeser peta (`map.flyTo`), menambahkan marker dengan popup detail (nama, kode, status, luas), dan memperbarui koordinat status bar.
  - Menambahkan tombol toggle collapse (`#islandToggle`) dengan persistensi status pada `localStorage`.
  - Mengonfigurasi tabel pulau `$tbl_pulau = "wilayah_pulau";` di `apps/inc/db.php`.
  - Menambahkan fungsi `getIslandsForCode($db, $tbl_pulau, $kode)` di `apps/inc/geo_helpers.php` dan menyatukan respon data pulau pada `apps/inc/geo_ajax.php`.
  - Menambahkan gaya CSS responsif pada `apps/css/styles.css` dan `apps/css/styles.min.css`.
  - Menambahkan unit test PHPUnit di `tests/inc/GeoHelpersTest.php` dan `tests/apps/index_php_test.php`.
- **Panduan Instalasi Komprehensif**:
  - Menambahkan panduan instalasi lengkap untuk web server stack (Laragon/XAMPP/Apache/Nginx) dan PHP built-in server pada `apps/README.md`.
- **Peningkatan Versi Aplikasi**:
  - Pemutakhiran versi aplikasi menjadi `3.2.0` pada `apps/.env`, `apps/.env.example`, `apps/index.php`, dan dokumentasi.

### v3.1.0 (2026-10-01)
- **Konfigurasi Environment (.env) & Dynamic Versioning**:
  - Menambahkan dukungan file `.env` untuk kredensial database dan versi aplikasi (`APP_VER`).
  - Mengganti kredensial database hardcoded di `apps/inc/db.php` dengan environment loader dinamis.

### v3.0.1 (2026-06-11)
- **Optimasi Kinerja & Minifikasi Aset**:
  - Implementasi minifikasi CSS dan JavaScript (`styles.min.css`, `zepto.min.js`).
  - Optimasi strategi caching server-side dan browser.
  - Perbaikan keamanan CSRF token dan sanitasi input.

---

## DONASI
- Untuk donasi via transfer bank:
  - **Bank BCA Digital (Blu)**: `(501) 000 576 776 186`
  - **Bank Jago**: `(542) 5003 5796 1022`
  - **Bank Sinarmas**: `(153) 005 462 4719`
  - **Bank Syariah Indonesia (BSI)**: `821-342-5550`
- Donasi via PayPal: [https://paypal.me/cahyadwiana](https://paypal.me/cahyadwiana)
- Donasi via QRIS CAHYADSN (ID1022183125288):

![Donasi via QRIS CAHYADSN](https://github.com/cahyadsn/wilayah/blob/master/docs/qr_code.cahyadsn.png?raw=true 'Donasi via QRIS CAHYADSN')
