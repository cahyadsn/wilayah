<!--
filename : implementasion_plan_p3.md
purpose  : Implementation Plan for Feature P3 - Choropleth Map (Kepadatan Penduduk)
created  : 2026-11-10
last edit: 2026-11-12 09:00:00
author   : cahya dsn & AI Assistant
================================================================================
MIT License
copyright (c) 2017-2026 by cahya dsn; cahyadsn@gmail.com
================================================================================
-->

# Rencana Implementasi P3: Choropleth Map (Peta Tematik Kepadatan Penduduk & Demografi)

## 1. Ringkasan Eksekutif (Executive Summary)
Fitur **Choropleth Map** menghadirkan visualisasi tematik pada peta interaktif Leaflet.js dengan mewarnai poligon wilayah (tingkat provinsi maupun kabupaten/kota) berdasarkan indikator statistik turunan, khususnya **Kepadatan Penduduk (jiwa/km²)** dan **Rasio Jenis Kelamin (Sex Ratio)**.

Fitur ini memanfaatkan kekayaan data tabel `wilayah_penduduk` (jumlah pria, wanita, total) dan `wilayah_luas` yang telah tersedia di basis data repositori, mengubah representasi data angka menjadi visualisasi spasial yang informatif dan estetik.

---

## 2. Tujuan & Nilai Manfaat (User Stories & Benefits)
- **Sebagai analis data / perencana tata kota / peneliti**, saya ingin melihat sebaran kepadatan penduduk antar kabupaten/kota dalam suatu provinsi secara spasial dengan gradasi warna yang jelas.
- **Sebagai pengguna umum**, saya dapat segera mengenali pusat keramaian dan konsentrasi populasi tanpa harus membaca tabel statistik mentah.
- **Interaktivitas Maksimal**: *Hover* pada poligon menampilkan ringkasan statistik instan, dan klik pada poligon langsung memilih dan memperbesar wilayah tersebut.

---

## 3. Komponen Arsitektur & File yang Terlibat
1. **Backend Data Aggregation**:
   - `apps/inc/geo_thematic.php` (baru): Endpoint agregasi poligon + demografi seluruh kabupaten/kota di bawah suatu provinsi.
   - Query gabungan (JOIN) antara `wilayah_level_1_2`, `wilayah_penduduk`, dan `wilayah_luas`.
2. **Frontend Map Layer & Legend**:
   - `apps/inc/geo_js.php` / `apps/js/wilayah.js`:
     - Fungsi kalkulasi kelas interval (Jenks Natural Breaks atau Quantile classification).
     - Palet warna ColorBrewer (misalnya skala `YlOrRd` untuk kepadatan atau `PuBu` untuk rasio gender).
     - Komponen kontrol Leaflet `L.control` untuk legenda peta (*Map Legend* dinamis).
     - Layer group kloroplet khusus (`thematicLayerGroup`).
3. **Antarmuka Kontrol Layer**:
   - `apps/index.php`: Penambahan tombol sakelar layer (*Layer Switcher* / *Thematic Mode Toggle*) pada antarmuka peta.
   - `apps/css/styles.css`: Desain kartu legenda peta yang rapi, responsif, dan adaptif terhadap tema terang/gelap.

---

## 4. Spesifikasi Teknis & Desain Skema

### Rumus Perhitungan
$$\text{Kepadatan Penduduk} = \frac{\text{Jumlah Total Penduduk}}{\text{Luas Wilayah (km}^2\text{)}}$$

$$\text{Sex Ratio} = \left(\frac{\text{Jumlah Penduduk Pria}}{\text{Jumlah Penduduk Wanita}}\right) \times 100$$

### Desain Endpoint: `GET apps/inc/geo_thematic.php?prov={kode_prov}&metric={density|sex_ratio}`
- **Contoh Respon JSON (GeoJSON FeatureCollection)**:
  ```json
  {
    "type": "FeatureCollection",
    "properties": {
      "prov_kode": "32",
      "prov_nama": "Jawa Barat",
      "metric": "density",
      "unit": "jiwa/km²"
    },
    "features": [
      {
        "type": "Feature",
        "properties": {
          "kode": "32.73",
          "nama": "Kota Bandung",
          "penduduk": 2527854,
          "luas": 167.31,
          "kepadatan": 15108.8,
          "pria": 1275000,
          "wanita": 1252854,
          "sex_ratio": 101.76
        },
        "geometry": {
          "type": "Polygon",
          "coordinates": [...]
        }
      }
    ]
  }
  ```

### Skala Warna (Color Scale - YlOrRd)
- Kepadatan Sangat Rendah (< 250 jiwa/km²): `#ffffb2`
- Kepadatan Rendah (250 – 750 jiwa/km²): `#fecc5c`
- Kepadatan Sedang (750 – 1.500 jiwa/km²): `#fd8d3c`
- Kepadatan Tinggi (1.500 – 5.000 jiwa/km²): `#f03b20`
- Kepadatan Sangat Tinggi (> 5.000 jiwa/km²): `#bd0026`

---

## 5. Rencana Tahapan Eksekusi (Phase Roadmap)

### Fase 1: Backend Endpoint & Cache Data Tematik
- [x] Buat file `apps/inc/geo_thematic.php`.
- [x] Siapkan query SQL berkinerja tinggi yang menggabungkan geometri poligon dengan data kependudukan dan luas wilayah.
- [x] Terapkan file caching JSON di `apps/cache/thematic_{prov}_{metric}.json` dengan masa berlaku (TTL) 7 hari.

### Fase 2: Kontrol Layer & Legenda Peta (Leaflet Integration)
- [x] Buat kontrol toggle di pojok kanan atas peta: *"Mode Tampilan: Standar | Kepadatan Penduduk | Rasio Gender"*.
- [x] Buat fungsi JavaScript `getColor(density)` dan `style(feature)`.
- [x] Tambahkan kontrol legenda dinamis Leaflet (`L.control({position: 'bottomleft'})`) yang menampilkan rentang nilai dan warna gradasi terkait.

### Fase 3: Interaksi Hover & Tooltip Informatif
- [x] Implementasikan event `mouseover`:
  - Sorot batas poligon dengan garis tepi lebih tebal (`weight: 3.5`, `color: '#38bdf8'`).
  - Munculkan floating info tooltip/box dengan rincian:
    - Nama Kab/Kota & Kode
    - Kepadatan: `X jiwa/km²`
    - Total Populasi: `Y jiwa` (Pria: `...`, Wanita: `...`)
    - Total Luas: `Z km²`
- [x] Implementasikan event `mouseout`: Mengembalikan styling poligon ke bentuk semula.
- [x] Implementasikan event `click`: Langsung memilih kabupaten tersebut ke dalam dropdown cascade dan membuka daftar pulau kabupaten terkait.

### Fase 4: Pengujian & Validasi
- [x] Uji terhadap provinsi dengan kontras wilayah ekstrem (misal DKI Jakarta vs Kalimantan Tengah).
- [x] Uji jika ada wilayah yang memiliki luas = 0 atau `null` (mencegah *Division by Zero*).
- [x] Uji performa render GeoJSON banyak poligon (harus responsif di bawah 60 FPS saat hover).

---

## 6. Pertimbangan Kinerja & Optimasi
1. **Pencegahan Division by Zero**:
   - Tangani secara eksplisit di backend jika `luas <= 0` dengan mengembalikan nilai `0` atau `null` agar tidak memicu exception fatal.
2. **Penyederhanaan Geometri (Polygon Simplification)**:
   - Poligon yang digunakan untuk level kloroplet provinsi dapat disederhanakan toleransinya (*Douglas-Peucker algorithm*) untuk mempercepat rendering Canvas/SVG di Leaflet.
