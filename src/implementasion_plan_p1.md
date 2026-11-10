<!--
filename : implementasion_plan_p1.md
purpose  : Implementation Plan for Feature P1 - Global Search Autocomplete
created  : 2026-11-10
last edit: 2026-11-10 07:35:00
author   : cahya dsn & AI Assistant
================================================================================
MIT License
copyright (c) 2017-2026 by cahya dsn; cahyadsn@gmail.com
================================================================================
-->

# Rencana Implementasi P1: Global Search Autocomplete

## 1. Ringkasan Eksekutif (Executive Summary)
Fitur **Global Search Autocomplete** memungkinkan pengguna untuk mencari wilayah administrasi (Provinsi, Kabupaten/Kota, Kecamatan, Kelurahan/Desa) maupun pulau secara langsung melalui sebuah kotak pencarian global instan. Pengguna tidak perlu lagi mengklik 4 tingkat dropdown hierarki secara manual atau mencari titik di peta secara acak.

---

## 2. Tujuan & Nilai Manfaat (User Stories & Benefits)
- **Sebagai pengguna**, saya ingin mengetik nama wilayah (misal: "Menteng", "Sleman", "Rondo") atau kode wilayah/pulau (misal: "31.71", "11.01.40001") agar aplikasi langsung menemukan dan memfokuskan peta ke lokasi tersebut.
- **Efisiensi Navigasi**: Memangkas waktu pencarian dari 4-5 kali klik dropdown menjadi 1 kali ketik (pencarian instan < 300ms).
- **Akurasi**: Menampilkan hierarki lengkap pada hasil rekomendasi (contoh: `Menteng (Kecamatan) - Kota Jakarta Pusat, DKI Jakarta`).

---

## 3. Komponen Arsitektur & File yang Terlibat
1. **Backend Endpoint**:
   - `apps/inc/geo_search.php` (baru): Endpoint pencarian cepat dengan debounce dan rate-limiting ringan.
   - Mengakses tabel `wilayah` (kode, nama) dan `wilayah_pulau` (kode, nama, status, lat, lng).
2. **Frontend View & Controller**:
   - `apps/index.php`: Penambahan elemen `<div class="global-search-container">` pada bagian navbar atau di atas panel dropdown.
   - `apps/js/wilayah.js` / `apps/inc/geo_js.php`: Handler event `input` (debounce 250-300ms), navigasi keyboard (`ArrowDown`, `ArrowUp`, `Enter`, `Escape`), serta integrasi dengan cascading dropdown & kamera Leaflet (`flyTo`).
   - `apps/css/styles.css`: Desain antarmuka dropdown hasil autocomplete dengan styling responsif dan dukungan mode gelap/terang (*theme-aware*).
3. **Caching**:
   - `apps/cache/`: Caching query pencarian populer berdurasi 24 jam untuk kata kunci dengan panjang >= 3 karakter.

---

## 4. Spesifikasi Teknis & Desain API

### Endpoint: `GET apps/inc/geo_search.php`
- **Parameter**:
  - `q` (string, minimal 2 karakter, maksimal 50 karakter, alfanumerik & spasi/titik)
  - `limit` (integer, default: 8, max: 15)
- **Query Optimization**:
  ```sql
  -- Pencarian Wilayah Administrasi
  SELECT kode, nama, 'wilayah' AS tipe
  FROM wilayah
  WHERE nama LIKE :prefix OR kode LIKE :prefix_code
  LIMIT :limit;

  -- Pencarian Pulau (opsional union / separate section)
  SELECT kode, nama, lat, lng, 'pulau' AS tipe
  FROM wilayah_pulau
  WHERE nama LIKE :prefix OR kode LIKE :prefix_code
  LIMIT 5;
  ```
- **Contoh Respon JSON**:
  ```json
  {
    "status": true,
    "query": "menteng",
    "results": [
      {
        "kode": "31.71.06",
        "nama": "Menteng",
        "tipe": "kecamatan",
        "parent": "Kota Administrasi Jakarta Pusat, DKI Jakarta",
        "lat": -6.1965,
        "lng": 106.8327
      },
      {
        "kode": "31.71.06.1001",
        "nama": "Menteng",
        "tipe": "kelurahan",
        "parent": "Kecamatan Menteng, Kota Jakarta Pusat",
        "lat": -6.1950,
        "lng": 106.8330
      }
    ]
  }
  ```

---

## 5. Rencana Tahapan Eksekusi (Phase Roadmap)

### Fase 1: Backend & Optimasi Basis Data
- [ ] Buat file `apps/inc/geo_search.php`.
- [ ] Tambahkan sanitasi input ketat (mencegah karakter wildcard berlebihan `%%` dan serangan injection).
- [ ] Optimalkan indexing MySQL jika diperlukan (`INDEX idx_nama (nama)` pada tabel `wilayah` dan `wilayah_pulau`).
- [ ] Format data kembalian menjadi struktur hierarki yang informatif (mengetahui parent provinsi & kabupaten).

### Fase 2: Antarmuka Pengguna (UI/UX)
- [ ] Letakkan kolom pencarian pada header/navbar aplikasi atau di atas sidebar kiri.
- [ ] Tambahkan ikon pencarian, tombol *clear* (silang), dan indikator *loading spinner*.
- [ ] Buat container dropdown daftar hasil (*flyout card*) yang mengapung di bawah kotak input.
- [ ] Terapkan styling responsif yang menyesuaikan tema (`theme-light` dan `theme-dark`).

### Fase 3: Integrasi Interaksi Peta & Dropdown Cascade
- [ ] Saat hasil diklik:
  - Ekstrak kode wilayah.
  - Panggil fungsi `applyReverseSelection` atau trigger sinkronisasi cascade dropdown (Provinsi &rarr; Kota &rarr; Kec &rarr; Kel).
  - Arahkan kamera peta Leaflet (`map.flyTo([lat, lng], zoomLevel)`).
  - Tampilkan marker pin dan popup ringkasan informasi wilayah/pulau tersebut.
- [ ] Dukungan aksesibilitas keyboard (`Tab`, Panah Atas/Bawah, `Enter` untuk memilih, `Escape` untuk menutup).

### Fase 4: Pengujian & Validasi
- [ ] Uji query dengan karakter khusus (`"Kota Cimahi"`, `"D.I. Yogyakarta"`, `"31.7"`, nama dengan tanda petik/strip).
- [ ] Uji performa latency response: pastikan rata-rata response time < 80ms pada MySQL lokal.
- [ ] Uji keamanan terhadap XSS pada tampilan hasil pencarian (`escapeHtml`).
- [ ] Uji pada perangkat mobile (tampilan tidak menutupi seluruh peta secara permanen).

---

## 6. Pertimbangan Keamanan & Kinerja
1. **Pencegahan DoS / Slow Query**:
   - Wajibkan minimal 2 karakter sebelum pencarian dieksekusi.
   - Berikan batas `LIMIT 10` pada setiap query SQL.
2. **XSS Protection**:
   - Seluruh teks nama wilayah dan parent di-escape dengan `htmlspecialchars(..., ENT_QUOTES, 'UTF-8')` di backend dan `escapeHtml()` di JavaScript sebelum dirender ke DOM.
3. **Client-Side Debouncing**:
   - Implementasikan fungsi `debounce(fn, 250)` pada event handler input agar tidak membanjiri server dengan request tiap ketukan keyboard.
