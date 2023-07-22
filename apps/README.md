# wilayah v2.4
Aplikasi sederhana menggunakan Kode dan Data Wilayah Administrasi Pemerintahah Indonesia sesuai Permendagri No 58 Tahun 2021 dengan PHP+MySQL+AJaX

(Kode dan Data Wilayah Pemerintahan Indonesia  dalam db wilayah.sql sesuai Permendagri No 58 Tahun 2021, utk aplikasi ini hanya menggunakan data level 1 dan 2 (provinsi dan kabupaten/kota) di /db/archive/wilayah_level_1_2.sql yang sesuai dengan Permendagri No 58 tahun 2022, dengan penambahan data lat/long/elv/timezone,boundaries/polygon,luas dan jumlah penduduk)

[![GitHub license](https://img.shields.io/badge/license-MIT-blue.svg)](LICENSE)
[![GitHub issues](https://img.shields.io/github/issues/cahyadsn/wilayah.svg)](https://github.com/cahyadsn/wilayah/issues)
[![GitHub forks](https://img.shields.io/github/forks/cahyadsn/wilayah.svg)](https://github.com/cahyadsn/wilayah/network)
[![GitHub stars](https://img.shields.io/github/stars/cahyadsn/wilayah.svg)](https://github.com/cahyadsn/wilayah/stargazers)

Database Data dan Kode Wilayah Administrasi Pemerintahan sesuai Permendagri No 58 Tahun 2021 untuk tingak Provinsi dan Kota/Kabupaten beserta data latitude/longitude (koordinat) dan polygon boundaries-nya dan aplikasi sederhananya
Untuk aplikasi wilayah v2.4 ini mengguanakan database dengan nama tabel wilayah_level_1_2 yang terdapat di folder /db/archive/wilayah_level_1_2.sql

Sesuaikan data konfigurasi database yang ada di apps/inc/db.php

Sesuaikan juga MapQuest API KEY yang digunakan di file apps/inc/geo_js.php pada bagian

L.mapquest.key = '<MAPQUEST_KEY_HERE>';


| id_prov | nama                      | kab  | kota |
|---------|---------------------------|-----:|-----:|
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
| 91      | Papua                     |   28 |    1 |
| 92      | Papua Barat               |   12 |    1 |
|         | TOTAL                     |  416 |   98 |


link demo bisa dilihat [di sini] https://wilayah.cahyadsn.com/v2.4/ (data sesuai permendagri no 58 tahun 2021)

## Referensi
- Dokumen Referensi : https://github.com/cahyadsn/wilayah_ref
- Keputusan Menteri Dalam Negeri Nomor 050-145 Tahun 2022 Tentang Pemberian Kode, Data Wilayah Administrasi Pemerintahan, Dan Pulau Tahun 2021 (Kepmendagri No. 050-145 Tahun 2022, https://www.kemendagri.go.id/arsip/detail/10857/keputusan-menteri-dalam-negeri-nomor-050145-tahun-2022-tentang-pemberian-kode-data-wilayah-administrasi-pemerintahan-dan-pulau-tahun-2021 (Ditetapkan pada tanggal 14 Februari 2022)
- Peraturan Menteri Dalam Negeri Republik Indonesia Nomor 58 Tahun 2021 Tentang Kode, Data Wilayah Administrasi Pemerintahan, Dan Pulau https://paralegal.id/peraturan/peraturan-menteri-dalam-negeri-nomor-58-tahun-2021/ (Permendagri No.58 2021, Ditetapkan pada tanggal 13 Desember 2021,Berita Negara Tahun 2021 Nomor 1391)
- Penetapan Nama, Kode Dan Jumlah Desa Seluruh Indonesia Tahun 2020 (Kepmendagri No. 146.1-4717 - 2020) http://binapemdes.kemendagri.go.id/produkhukum/detil/keputusan-menteri-dalam-negeri-nomor-1461-4717-tahun-2020 (Ditetapkan pada tanggal 21 Desember 2020)
- Kode dan Data Wilayah Administrasi Pemerintahan (Permendagri No.72-2019) https://www.kemendagri.go.id/page/read/48/peraturan-menteri-dalam-negeri-no72-tahun-2019 (Berita Negara Republik Indonesia Tahun 2019 Nomor 1327, Ditetapkan pada tanggal 8 Oktober 2019)
- Kode dan Data Wilayah Administrasi Pemerintahan (Permendagri No.137-2017) http://www.kemendagri.go.id/produk-hukum/2018/01/18/kode-dan-data-wilayah-administrasi-pemerintahan-tahun-2017 (Berita Negara Republik Indonesia Tahun 2017 Nomor 1955, Ditetapkan pada tanggal 27 Desember 2017)
- Kode dan Data Wilayah Administrasi Pemerintahan (Permendagri No.56-2015) www.kemendagri.go.id/pages/data-wilayah (Berita Negara Republik Indonesia Tahun 2015 Nomor 1045, Ditetapkan pada tanggal 29 Juni 2015)

## Donasi
- untuk donasi via Bank Syariah Indonesia (BSI) 821-342-5550
- untuk donasi via PayPal [https://paypal.me/cahyadwiana]
- untuk donasi via Ko-fi [https://ko-fi.com/cahyadsn]

[di sini]: http://wilayah.cahyadsn.com/v2.4/

