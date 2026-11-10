<!--
filename : implementasion_plan_p4.md
purpose  : Implementation Plan for Feature P4 - Spatial Data Export (GeoJSON / KML / CSV)
created  : 2026-11-10
last edit: 2026-11-10 07:35:30
author   : cahya dsn & AI Assistant
================================================================================
MIT License
copyright (c) 2017-2026 by cahya dsn; cahyadsn@gmail.com
================================================================================
-->

# Rencana Implementasi P4: Unduh Poligon & Data Spasial (GeoJSON, KML, CSV)

## 1. Ringkasan Eksekutif (Executive Summary)
Fitur **Unduh Poligon & Data Spasial** menyediakan kemampuan bagi pengguna untuk mengekspor data wilayah administrasi aktif (beserta batas koordinat poligonnya) maupun daftar pulau ke dalam berbagai format standar industri geospasial dan analisis data: **GeoJSON** (RFC 7946), **KML** (Google Earth), dan **CSV** (Comma-Separated Values).

Fitur ini menjawab kebutuhan mendesak para pengembang GIS, surveyor pemetaan, akademisi, dan analis kebijakan yang membutuhkan data batas wilayah Kemendagri siap pakai untuk diolah di perangkat lunak eksternal seperti QGIS, ArcGIS, Google Earth Pro, atau Python/R.

---

## 2. Tujuan & Nilai Manfaat (User Stories & Benefits)
- **Sebagai analis GIS**, saya ingin mengunduh poligon batas provinsi atau kabupaten dalam format GeoJSON atau KML agar dapat langsung saya kombinasikan dengan layer peta kerja saya di QGIS.
- **Sebagai pengembang aplikasi**, saya ingin mengekspor daftar seluruh pulau di suatu kabupaten dalam format CSV/GeoJSON untuk diimpor ke basis data proyek saya.
- **Portabilitas & Interoperabilitas**: Menghilangkan kebutuhan untuk melakukan scraping atau ekstraksi manual dari database dump mentah.

---

## 3. Komponen Arsitektur & File yang Terlibat
1. **Backend Generator & Export Engine**:
   - `apps/inc/geo_export.php` (baru): Generator file unduhan yang mendukung konversi data database ke format GeoJSON, KML, dan CSV secara *on-the-fly*.
   - Mengambil data dari `wilayah_level_1_2`, `wilayah_pulau`, dan `wilayah`.
2. **Frontend UI Components**:
   - `apps/index.php`: Penambahan tombol aksi *"Export Data / Unduh"* di samping info bar (`#infoStrip`) dan panel daftar pulau (`#islandPanel`).
   - Modal atau dropdown menu pilihan format unduhan (GeoJSON, KML, CSV).
   - `apps/css/styles.css`: Gaya tombol unduh dan ikon format file (*file badges*).

---

## 4. Spesifikasi Teknis & Desain Output Format

### Endpoint: `GET apps/inc/geo_export.php?kode={kode}&format={geojson|kml|csv}&target={boundary|islands|stats}`

### 1. Format GeoJSON (`format=geojson`)
- **MIME Type**: `application/geo+json; charset=utf-8`
- **Struktur Standar (RFC 7946)**:
  ```json
  {
    "type": "Feature",
    "properties": {
      "kode": "32.73",
      "nama": "Kota Bandung",
      "provinsi": "Jawa Barat",
      "luas_km2": 167.31,
      "penduduk": 2527854,
      "sumber": "Kepmendagri No 300.2.2-2430 Tahun 2025"
    },
    "geometry": {
      "type": "Polygon",
      "coordinates": [[[107.56, -6.91], ...]]
    }
  }
  ```

### 2. Format KML (`format=kml`)
- **MIME Type**: `application/vnd.google-earth.kml+xml; charset=utf-8`
- **Struktur**:
  ```xml
  <?xml version="1.0" encoding="UTF-8"?>
  <kml xmlns="http://www.opengis.net/kml/2.2">
    <Document>
      <name>Kota Bandung (32.73)</name>
      <Placemark>
        <name>Kota Bandung</name>
        <description>Luas: 167.31 km² | Penduduk: 2.527.854 jiwa</description>
        <Polygon>
          <outerBoundaryIs>
            <LinearRing>
              <coordinates>
                107.56,-6.91,0 107.58,-6.92,0 ...
              </coordinates>
            </LinearRing>
          </outerBoundaryIs>
        </Polygon>
      </Placemark>
    </Document>
  </kml>
  ```

### 3. Format CSV (`format=csv`)
- **MIME Type**: `text/csv; charset=utf-8`
- **Output Daftar Pulau**:
  ```csv
  kode,nama,lat,lng,status,luas_km2
  "11.01.40001","Pulau Rondo",6.07472,95.11306,"BP",0.82
  ```

---

## 5. Rencana Tahapan Eksekusi (Phase Roadmap)

### Fase 1: Generator Backend
- [ ] Buat file `apps/inc/geo_export.php`.
- [ ] Implementasikan fungsi konversi koordinat format poligon database (`[[lat, lng], ...]`) ke format GeoJSON standard (`[[lng, lat], ...]`).
- [ ] Implementasikan fungsi konversi poligon ke format KML `<LinearRing><coordinates>`.
- [ ] Buat stream generator CSV menggunakan `fputcsv()` untuk efisiensi alokasi memori server.
- [ ] Terapkan header unduhan yang aman:
  ```php
  header('Content-Type: ' . $mime_type);
  header('Content-Disposition: attachment; filename="' . $safe_filename . '"');
  header('X-Content-Type-Options: nosniff');
  ```

### Fase 2: Komponen Antarmuka Pengguna
- [ ] Tambahkan tombol dropdown *"Unduh Data"* pada panel informasi wilayah:
  - 📥 *Unduh Batas Poligon (.geojson)*
  - 🌍 *Unduh Batas Wilayah (.kml)*
  - 📊 *Unduh Profil Statistik (.csv)*
- [ ] Tambahkan tombol *"Unduh CSV Pulau"* pada bagian atas panel daftar pulau (`#islandPanel`).

### Fase 3: Integrasi & Validasi Data
- [ ] Pastikan koordinat poligon berurutan secara benar (Longitude lebih dulu untuk GeoJSON: `[lng, lat]`, berbeda dengan Leaflet: `[lat, lng]`).
- [ ] Tangani penanganan poligon bertipe MultiPolygon (wilayah kepulauan yang memiliki banyak pulau pecahan batas).

### Fase 4: Pengujian & Validasi
- [ ] Buka file `.geojson` yang diunduh pada [geojson.io](https://geojson.io) atau QGIS untuk memastikan topologi tidak rusak.
- [ ] Buka file `.kml` pada Google Earth Pro / Google Maps My Maps untuk menguji keselarasan proyeksi bumi (WGS84).
- [ ] Uji unduhan CSV di Microsoft Excel memastikan format UTF-8 (BOM UTF-8) terbaca dengan rapi tanpa masalah karakter aneh.

---

## 6. Pertimbangan Keamanan & Kinerja
1. **Pencegahan Header Injection (CRLF)**:
   - Sanitasi nama file unduhan secara ketat menggunakan regex `preg_replace('/[^a-zA-Z0-9_\-]/', '_', $nama)` sebelum dimasukkan ke dalam header `Content-Disposition`.
2. **Efisiensi Penggunaan Memori (Memory Exhaustion Prevention)**:
   - Hindari menampung string puluhan ribu koordinat dalam satu variabel besar di PHP; gunakan streaming langsung dengan `php://output` saat mengekspor data berskala besar.
