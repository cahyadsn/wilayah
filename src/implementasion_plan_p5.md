<!--
filename : implementasion_plan_p5.md
purpose  : Implementation Plan for Feature P5 - Island Marker Clustering
created  : 2026-11-10
last edit: 2026-11-10 07:35:40
author   : cahya dsn & AI Assistant
================================================================================
MIT License
copyright (c) 2017-2026 by cahya dsn; cahyadsn@gmail.com
================================================================================
-->

# Rencana Implementasi P5: Cluster Marker Seluruh Pulau (Leaflet.markercluster)

## 1. Ringkasan Eksekutif (Executive Summary)
Fitur **Cluster Marker Seluruh Pulau** menghadirkan kemampuan visualisasi spasial masif untuk menampilkan seluruh titik sebaran pulau di wilayah aktif (provinsi atau kabupaten/kota) secara simultan di atas peta Leaflet.js menggunakan pustaka pengelompokan penanda (**Leaflet.markercluster**).

Pada versi aplikasi saat ini (v3.2.0), titik pulau baru muncul di peta ketika salah satu item pulau di klik pada panel daftar pulau. Dengan fitur clustering ini, provinsi kepulauan seperti Kepulauan Riau (2.408 pulau), Maluku (1.422 pulau), atau Bangka Belitung dapat ditampilkan seluruh sebaran pulaunya secara menakjubkan tanpa membebani memori peramban (*browser free of lag*).

---

## 2. Tujuan & Nilai Manfaat (User Stories & Benefits)
- **Sebagai pengguna**, saya ingin melihat visualisasi sebaran ratusan hingga ribuan pulau di Indonesia sekaligus untuk memahami karakteristik geografis kepulauan wilayah tersebut.
- **Performa Tinggi**: Mengelompokkan titik-titik pulau yang berdekatan menjadi satu *cluster bubble* angka, dan secara dinamis memecahnya (*spiderfy / zoom*) saat pengguna memperbesar peta.
- **Konektivitas Dua Arah**: Mengklik pulau pada sidebar kanan akan memicu *zoom* dan membuka marker pulau di peta, demikian pula mengklik marker di peta akan otomatis menyorot item terkait di panel daftar pulau.

---

## 3. Komponen Arsitektur & File yang Terlibat
1. **Frontend Libraries**:
   - `leaflet.markercluster` (CSS & JS) via CDN unpkg / local vendor:
     - `https://unpkg.com/leaflet.markercluster@1.5.3/dist/MarkerCluster.css`
     - `https://unpkg.com/leaflet.markercluster@1.5.3/dist/MarkerCluster.Default.css`
     - `https://unpkg.com/leaflet.markercluster@1.5.3/dist/leaflet.markercluster.js`
2. **Frontend Logic & Map Layer**:
   - `apps/inc/geo_js.php` / `apps/js/wilayah.js`:
     - Inisialisasi layer `L.markerClusterGroup()`.
     - Fungsi `populateIslandClusters(islandsData)`: Memetakan array data pulau yang sudah didapatkan dari `geo_ajax.php` ke dalam objek marker cluster.
     - Fungsi toggle visibility: Menambah atau menghapus layer cluster dari peta sesuai status sakelar pengguna.
3. **Antarmuka (UI)**:
   - `apps/index.php`: Penambahan sakelar toggle *"Tampilkan Semua di Peta"* pada bagian header `#islandPanel`.
   - `apps/css/styles.css`: Kostumisasi tema warna *cluster bubble* agar serasi dengan mode gelap dan mode terang aplikasi (*theme-aware marker clusters*).

---

## 4. Spesifikasi Teknis & Desain Komponen

### Struktur Penanda & Popup Marker
Setiap pulau di dalam kumpulan data memiliki atribut:
- `kode`: Kode pulau resmi Kemendagri (e.g. `11.01.40001`)
- `nama`: Nama pulau resmi
- `lat` & `lng`: Koordinat lintang dan bujur
- `status`: Kategori pulau (misal: Berpenduduk / Tidak Berpenduduk / Pulau Terluar)
- `luas`: Luas daratan pulau dalam $\text{km}^2$

### Desain Template Popup Marker
```html
<div class="island-popup">
  <div class="island-popup-title">🏝️ Pulau Rondo</div>
  <div class="island-popup-meta">
    <div><strong>Kode:</strong> <code>11.01.40001</code></div>
    <div><strong>Status:</strong> <span class="badge">Pulau Terluar</span></div>
    <div><strong>Luas:</strong> 0.82 km²</div>
    <div><strong>Koordinat:</strong> 6.07472° N, 95.11306° E</div>
  </div>
</div>
```

### Konfigurasi Efisiensi MarkerCluster
```javascript
var islandClusterGroup = L.markerClusterGroup({
    chunkedLoading: true,         // Mencegah freeze browser saat merender > 2.000 marker
    chunkInterval: 100,           // Waktu jeda antar-chunk pemrosesan (ms)
    chunkDelay: 50,
    maxClusterRadius: 50,         // Radius piksel pengelompokan titik
    spiderfyOnMaxZoom: true,      // Tampilan jaring laba-laba saat zoom maksimal
    showCoverageOnHover: false,   // Nonaktifkan poligon coverage untuk efisiensi render
    zoomToBoundsOnClick: true
});
```

---

## 5. Rencana Tahapan Eksekusi (Phase Roadmap)

### Fase 1: Integrasi Asset Leaflet.markercluster
- [ ] Daftarkan stylesheet dan skrip `leaflet.markercluster` ke dalam header dan footer `apps/index.php`.
- [ ] Buat fallback lokal atau pastikan CDN memiliki atribut `integrity` dan `crossorigin`.

### Fase 2: Pengendalian Layer Cluster di JavaScript
- [ ] Tambahkan variabel global `islandClusterGroup` di `apps/inc/geo_js.php`.
- [ ] Modifikasi fungsi `updateIslandPanel(pulauList, locationName)`:
  - Jika sakelar *"Tampilkan Semua Pulau"* aktif, jalankan `populateIslandClusters(pulauList)`.
  - Hapus dan bersihkan marker lama (`islandClusterGroup.clearLayers()`) saat berganti wilayah.

### Fase 3: Komponen Antarmuka Sakelar (UI Toggle Switch)
- [ ] Pasang sakelar toggle di bawah badge jumlah pulau pada `#islandPanel`:
  ```html
  <div class="island-cluster-toggle">
    <label class="switch">
      <input type="checkbox" id="toggleShowAllIslands">
      <span class="slider round"></span>
    </label>
    <span class="toggle-label">Sebarkan di peta</span>
  </div>
  ```
- [ ] Simpan status preferensi sakelar di `localStorage` (`wilayahShowAllIslands`).

### Fase 4: Penataan Gaya & Sinkronisasi Dua Arah
- [ ] Modifikasi warna default icon cluster (hijau/kuning/oranye) menggunakan CSS variabel sistem agar elegan dalam mode gelap (*dark mode*).
- [ ] Sinkronisasi saat marker di dalam cluster diklik: sorot otomatis (*auto-scroll* dan *add class active*) elemen pulau yang bersangkutan pada list sidebar kanan.

### Fase 5: Pengujian & Validasi
- [ ] Uji stres (*Stress Test*) pada provinsi dengan pulau terbanyak (Kepulauan Riau: ~2.408 pulau; Maluku: ~1.422 pulau).
- [ ] Pastikan tidak ada kebocoran memori (*memory leak*) ketika pengguna berganti-ganti provinsi secara cepat.
- [ ] Uji responsivitas pada perangkat ponsel pintar (FPS tetap stabil saat *pinch-to-zoom* peta).

---

## 6. Pertimbangan Keamanan & Kinerja
1. **Chunked Loading**:
   - Wajib mengaktifkan opsi `chunkedLoading: true` pada Leaflet.markercluster untuk membagi beban pembuatan DOM marker ke beberapa event loop interval.
2. **XSS Sanitization pada Popup**:
   - Sanitasi data nama pulau dan catatan pulau sebelum dimasukkan ke dalam template popup string menggunakan `escapeHtml()`.
3. **Pembersihan Layer Saat Transisi**:
   - Selalu panggil `islandClusterGroup.clearLayers()` sebelum memproses data baru guna menghindari penumpukan objek marker di memori browser.
