<!--
filename : implementasion_plan_p2.md
purpose  : Implementation Plan for Feature P2 - Shareable URL & Deep Linking
created  : 2026-11-10
last edit: 2026-11-11 08:45:00
author   : cahya dsn & AI Assistant
================================================================================
MIT License
copyright (c) 2017-2026 by cahya dsn; cahyadsn@gmail.com
================================================================================
-->

# Rencana Implementasi P2: Shareable URL & Deep Linking (Permalinks)

## 1. Ringkasan Eksekutif (Executive Summary)
Fitur **Shareable URL & Deep Linking** memungkinkan keadaan aktif aplikasi (kode wilayah terpilih, pulau aktif, posisi koordinat peta, tingkat zoom, dan preferensi tema) tersinkronisasi ke dalam parameter URL peramban (*browser URL parameters* atau URL Hash) menggunakan `history.replaceState` / `pushState`.

Dengan fitur ini, pengguna dapat dengan mudah membagikan tautan langsung ke rekan kerja, publikasi ilmiah, atau media sosial, dan penerima tautan akan langsung melihat wilayah atau pulau yang dimaksud persis seperti tampilan pengirim.

---

## 2. Tujuan & Nilai Manfaat (User Stories & Benefits)
- **Sebagai pengguna**, saya ingin menyalin URL atau mengklik tombol "Bagikan Tautan" sehingga saya dapat mengirimkan referensi wilayah/pulau tertentu kepada orang lain.
- **Sebagai penerima tautan**, saya ingin membuka link dan langsung mendapati dropdown terisi secara otomatis, batas poligon tergambar, dan kamera peta terpusat pada wilayah/pulau tersebut tanpa perlu mengulang pemilihan manual.
- **Konsistensi Navigasi**: Mendukung tombol *Back* dan *Forward* pada browser pengguna untuk riwayat penjelajahan wilayah.

---

## 3. Komponen Arsitektur & File yang Terlibat
1. **Frontend State & URL Synchronizer**:
   - `apps/js/wilayah.js` / `apps/inc/geo_js.php`:
     - Modul `urlStateManager`: Membaca parameter saat pertama kali halaman dimuat (*initial hydration*).
     - Modul `urlUpdater`: Memperbarui URL tanpa me-*reload* halaman ketika wilayah atau pulau dipilih.
2. **Antarmuka (UI)**:
   - `apps/index.php`: Penambahan tombol "Salin Tautan" (*Share Button*) di samping judul wilayah atau panel info bar (`#infoStrip`).
   - `apps/css/styles.css`: Gaya tampilan tombol share dan animasi *toast notification* ("Tautan berhasil disalin ke clipboard!").
3. **Backend PHP Validation**:
   - `apps/index.php`: Validasi awal terhadap parameter URL `kode` dan `pulau` sebelum dirender ke HTML / JavaScript untuk mencegah injeksi skrip (XSS) atau nilai parameter ilegal.

---

## 4. Spesifikasi Teknis & Desain URL

### Format URL
Dukungan format query parameters yang bersih dan mudah dibaca:
```text
https://wilayah.cahyadsn.com/apps/?kode=32.73&theme=dark
https://wilayah.cahyadsn.com/apps/?pulau=11.01.40001
https://wilayah.cahyadsn.com/apps/?lat=-6.9147&lng=107.6098&zoom=12
```

### Definisi Parameter
| Parameter | Tipe Data | Format / Contoh | Keterangan |
| :--- | :--- | :--- | :--- |
| `kode` | String | `^[0-9]+(\.[0-9]+)*$` (e.g. `32`, `32.73`, `32.73.01`, `32.73.01.1001`) | Kode wilayah Kemendagri |
| `pulau` | String | `^[0-9]{2}\.[0-9]{2}\.[0-9]{5}$` (e.g. `11.01.40001`) | Kode pulau resmi |
| `theme` | String | `light` atau `dark` | Preferensi tema warna |
| `lat` & `lng` | Float | Desimal presisi 4-5 digit | Koordinat titik pusat peta (opsional jika ada `kode`) |
| `z` / `zoom`| Integer | `5` s/d `18` | Tingkat perbesaran peta |

---

## 5. Rencana Tahapan Eksekusi (Phase Roadmap)

### Fase 1: Parsing Parameter URL & Initial Hydration
- [x] Buat fungsi JavaScript `getUrlParams()`:
  ```javascript
  function getUrlParams() {
      var searchParams = new URLSearchParams(window.location.search);
      return {
          kode: searchParams.get('kode') || null,
          pulau: searchParams.get('pulau') || null,
          theme: searchParams.get('theme') || null,
          zoom: searchParams.get('zoom') || null
      };
  }
  ```
- [x] Tambahkan sanitasi regex ketat pada parameter `kode` dan `pulau` sebelum diproses.
- [x] Jika terdapat parameter `kode`, panggil rantai `applyReverseSelection()` atau cascade selector untuk memuat data provinsi hingga level yang ditentukan, gambar poligon, dan sesuaikan peta.
- [x] Jika terdapat parameter `pulau`, muat parent kabupaten/provinsi pulau tersebut, aktifkan panel pulau, sorot item pulau, dan tempatkan marker pulau.

### Fase 2: Sinkronisasi Status ke URL (URL Sync on User Action)
- [x] Pasang listener pada event perubahan pilihan wilayah:
  - Saat `prop`, `kota`, `kec`, atau `kel` berubah, panggil:
    ```javascript
    function updateUrlState(kode, pulauId) {
        var url = new URL(window.location);
        if (kode) url.searchParams.set('kode', kode);
        else url.searchParams.delete('kode');
        if (pulauId) url.searchParams.set('pulau', pulauId);
        else url.searchParams.delete('pulau');
        window.history.replaceState({}, '', url);
    }
    ```
- [x] Pasang listener saat pulau di klik pada panel pulau: tambahkan parameter `pulau=KODE_PULAU`.
- [x] Pasang listener pada tombol toggle theme: perbarui parameter `theme`.

### Fase 3: Tombol "Bagikan / Salin Tautan" & Toast Notification
- [x] Tambahkan tombol ikon share (`<button id="btnShareLink">`) di bagian header atau overlay bar peta.
- [x] Gunakan Web Share API (`navigator.share`) jika tersedia pada browser mobile modern, atau fallback ke Clipboard API (`navigator.clipboard.writeText(window.location.href)`).
- [x] Buat komponen Toast notifikasi ringan tanpa dependensi eksternal (muncul selama 2.5 detik).

### Fase 4: Pengujian & Validasi
- [x] Uji *Cold Start*: buka URL dengan kode level 1 (provinsi `11`), level 2 (kabupaten `32.73`), level 3 (kecamatan), dan level 4 (kelurahan/desa).
- [x] Uji kode pulau terluar atau pulau kecil (memastikan panel pulau terbuka dan marker tersemat).
- [x] Uji keamanan: masukkan karakter berbahaya pada URL (e.g. `?kode=<script>alert(1)</script>`) dan pastikan diblokir atau diabaikan tanpa eror.
- [x] Uji tombol browser *Back* / *Forward* (`popstate` event handler).

---

## 6. Pertimbangan Keamanan & Kinerja
1. **Pencegahan XSS melalui URL**:
   - Jangan pernah melakukan `innerHTML = urlParam`.
   - Lakukan validasi pola regex ketat `^[0-9]+(\.[0-9]+)*$` sebelum mengirim request AJAX atau memodifikasi atribut DOM.
2. **Kinerja / History Pollution**:
   - Gunakan `history.replaceState` untuk interaksi umum agar riwayat back-button peramban pengguna tidak dipenuhi ratusan perubahan kecil saat menggeser peta. Gunakan `history.pushState` hanya pada pergantian wilayah signifikan.
