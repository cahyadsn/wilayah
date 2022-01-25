# WILAYAH
Kode dan Data Wilayah Administrasi Pemerintahah Indonesia sesuai Permendagri No 58 Tahun 2021 dengan PHP+MySQL+AJaX

Kode dan Data Wilayah Pemerintahan Indonesia  dalam database :
- wilayah_2022.sql sesuai Permendagri No 58 Tahun 2021*
- wilayah_2020.sql sesuai Permendagri No. 72 Tahun 2019 (revised by Kepmendagri No.146.1-4717 Tahun 2020) 
- wilayah_2018.sql sesuai Permendagri No 137 tahun 2017
- wilayah.sql sesuai Permendagri No 56 Tahun 2015
- wilayah_level_1_2.sql sesuai Permendagri No. 72 Tahun 2019 untuk data provinsi dan kab/kota dengan koordinat,timezone dan boundaries

[![GitHub license](https://img.shields.io/badge/license-MIT-blue.svg)](LICENSE)
[![GitHub issues](https://img.shields.io/github/issues/cahyadsn/wilayah.svg)](https://github.com/cahyadsn/wilayah/issues)
[![GitHub forks](https://img.shields.io/github/forks/cahyadsn/wilayah.svg)](https://github.com/cahyadsn/wilayah/network)
[![GitHub stars](https://img.shields.io/github/stars/cahyadsn/wilayah.svg)](https://github.com/cahyadsn/wilayah/stargazers)

## Data Kepmendagri No 146.1-4717 Tahun 2020
Database Data dan Kode Wilayah Administrasi Pemerintahan sesuai Permendagri No 72 Tahun 2019  dengan revisi berdasarkan KepmendagrinNo 146.1-4717 Tahun 2020 (wilayah_2020.sql)

| id_prov | nama                      | kab  | kota | kec | kel  | desa |
|---------|---------------------------|-----:|-----:|-----|-----:|-----:|
| 11      | Aceh                      |   18 |    5 | 289 |    0 | 6497 |
| 12      | Sumatera Utara            |   25 |    8 | 444 |  693 | 5417 |
| 13      | Sumatera Barat            |   12 |    7 | 179 |  230 |  928 |
| 14      | Riau                      |   10 |    2 | 166 |  268 | 1591 |
| 15      | Jambi                     |    9 |    2 | 141 |  163 | 1399 |
| 16      | Sumatera Selatan          |   13 |    4 | 236 |  386 | 2853 |
| 17      | Bengkulu                  |    9 |    1 | 128 |  172 | 1341 |
| 18      | Lampung                   |   13 |    2 | 228 |  205 | 2435 |
| 19      | Kepulauan Bangka Belitung |    6 |    1 |  47 |   82 |  309 |
| 21      | Kepulauan Riau            |    5 |    2 |  70 |  142 |  275 |
| 31      | DKI Jakarta               |    1 |    5 |  44 |  267 |    0 |
| 32      | Jawa Barat                |   18 |    9 | 627 |  645 | 5312 |
| 33      | Jawa Tengah               |   29 |    6 | 576 |  753 | 7809 |
| 34      | DI Yogyakarta             |    4 |    1 |  78 |   46 |  392 |
| 35      | Jawa Timur                |   29 |    9 | 666 |  777 | 7724 |
| 36      | Banten                    |    4 |    4 | 155 |  313 | 1238 |
| 51      | Bali                      |    8 |    1 |  57 |   80 |  636 |
| 52      | Nusa Tenggara Barat*      |    8 |    2 | 117 |  145 | 1005 |
| 53      | Nusa Tenggara Timur       |   21 |    1 | 309 |  327 | 3026 |
| 61      | Kalimantan Barat          |   12 |    2 | 174 |   99 | 2031 |
| 62      | Kalimantan Tengah *       |   13 |    1 | 136 |  139 | 1433 |
| 63      | Kalimantan Selatan        |   11 |    2 | 153 |  144 | 1864 |
| 64      | Kalimantan Timur          |    7 |    3 | 103 |  197 |  841 |
| 65      | Kalimantan Utara          |    4 |    1 |  53 |   35 |  447 |
| 71      | Sulawesi Utara            |   11 |    4 | 171 |  332 | 1507 |
| 72      | Sulawesi Tengah           |   12 |    1 | 175 |  175 | 1842 |
| 73      | Sulawesi Selatan          |   21 |    3 | 311 |  792 | 2255 |
| 74      | Sulawesi Tenggara*        |   15 |    2 | 219 |  377 | 1908 |
| 75      | Gorontalo                 |    5 |    1 |  77 |   72 |  657 |
| 76      | Sulawesi Barat            |    6 |    0 |  69 |   73 |  575 |
| 81      | Maluku                    |    9 |    2 | 118 |   35 | 1198 |
| 82      | Maluku Utara              |    8 |    2 | 116 |  118 | 1063 |
| 91      | Papua                     |   28 |    1 | 560 |  110 | 5411 |
| 92      | Papua Barat               |   12 |    1 | 218 |   95 | 1742 |
|         | TOTAL*                    |  416 |   98 |7230 | 8488 |74961 |

)* data mengalami perubahan dari data sebelumnya (data permendagri No 72 Tahun 2019)


link demo bisa dilihat [di sini] https://wilayah.cahyadsn.com/v2.3/ (data sesuai permendagri no 137 tahun 2017, level 1 dan 2)

## Referensi
- Peraturan Menteri Dalam Negeri Republik Indonesia Nomor 58 Tahun 2021 Tentang Kode, Data Wilayah Administrasi Pemerintahan, Dan Pulau (Permendagri No.58 2021, Ditetapkan pada tanggal 13 Desember 2021,Berita Negara Tahun 2021 Nomor 1391)
- Penetapan Nama, Kode Dan Jumlah Desa Seluruh Indonesia Tahun 2020 (Kepmendagri No. 146.1-4717 - 2020) http://binapemdes.kemendagri.go.id/produkhukum/detil/keputusan-menteri-dalam-negeri-nomor-1461-4717-tahun-2020
- Kode dan Data Wilayah Administrasi Pemerintahan (Permendagri No.72-2019) https://www.kemendagri.go.id/page/read/48/peraturan-menteri-dalam-negeri-no72-tahun-2019 (Berita Negara Republik Indonesia Tahun 2019 Nomor 1327, Ditetapkan pada tanggal 8 Oktober 2019)
- Kode dan Data Wilayah Administrasi Pemerintahan (Permendagri No.137-2017) http://www.kemendagri.go.id/produk-hukum/2018/01/18/kode-dan-data-wilayah-administrasi-pemerintahan-tahun-2017 (Berita Negara Republik Indonesia Tahun 2017 Nomor 1955, Ditetapkan pada tanggal 27 Desember 2017)
- Kode dan Data Wilayah Administrasi Pemerintahan (Permendagri No.56-2015) www.kemendagri.go.id/pages/data-wilayah (Berita Negara Republik Indonesia Tahun 2015 Nomor 1045, Ditetapkan pada tanggal 29 Juni 2015)

## To Do
- update data based on Permendagri No.58 Tahun 2021 (*on progress)
- update data based on Kepmendagri No.146.1-4717 Tahun 2020 (done)
- update web demo ke v2.3 (done)
- update data di web demo (done)
- penambahan data elevation data wilayah level 1 dan 2 (done)
- update data perubahan nama kecamatan, kelurahan/desa (done)
- update data di web demo v2 (level 1 dan 2) (done)
- meng-update data boundaries data wilayah_level_1_2.sql (done)
- meng-update data latitude/longitude data wilayah_level_1_2.sql (done)
- menambahkan database wilayal_level_1_2.sql (data level provinsi dan kab/kota dengan koordinat, timezone dan boundaries) (done)
- update data ke kode dan data wilayah berdasarkan permendagri No 72 tahun 2019 smp dengan tingkat kelurahan/desa (done)
- update data ke kode dan data wilayah berdasarkan permendagri No 137 tahun 2017 smp dengan tingkat kelurahan/desa (done)
- on progress, convert data dari pdf -> xlsx (done) , xlsx->csv (done) , csv->sql(done) , import sql to db (done), validasi data di db dengan source (done)

## Request Data
- data tambahan berupa koordinat latitude/longitude, polygon boundaries, kodepos, timezone utk beberapa wilayah sudah tersedia, namun tidak termasuk dalam publish ini.
- data lat/long/timezone dan polygon boundaries wilayah yg sudah cukup lengkap tersedia untuk wilayah provinsi DKI Jakarta dan DI Yogyakarta
- data polygon diperoleh dari website BIG(Badan Informasi Geospatial) di https://tanahair.indonesia.go.id

## Donasi
- untuk donasi via Bank Syariah Indonesia (BSI) 821-342-5550
- untuk donasi via PayPal [https://paypal.me/cahyadwiana]

[di sini]: https://wilayah.cahyadsn.com/v2/
