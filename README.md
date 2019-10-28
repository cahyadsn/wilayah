# wilayah
Kode dan Data Wilayah Administrasi Pemerintahah Indonesia sesuai Permendagri No 137 Tahun 2017 dengan PHP+MySQL+AJaX

(Kode dan Data Wilayah Pemerintahan Indonesia  dalam db wilayah.sql sesuai Permendagri No 56 Tahun 2015, utk database terbaru gunakan wilayah_2018.sql yg sesuai dengan Permendagri No 137 tahun 2017)

[![GitHub license](https://img.shields.io/badge/license-MIT-blue.svg)](LICENSE)
[![GitHub issues](https://img.shields.io/github/issues/cahyadsn/wilayah.svg)](https://github.com/cahyadsn/wilayah/issues)
[![GitHub forks](https://img.shields.io/github/forks/cahyadsn/wilayah.svg)](https://github.com/cahyadsn/wilayah/network)
[![GitHub stars](https://img.shields.io/github/stars/cahyadsn/wilayah.svg)](https://github.com/cahyadsn/wilayah/stargazers)

Database Data dan Kode Wilayah Administrasi Pemerintahan sesuai Permendagri No 137 Tahun 2017

| id_prov | nama                      | kab  | kota | kec | kel  | desa |
|---------|---------------------------|-----:|-----:|-----|-----:|-----:|
| 11      | Aceh                      |   18 |    5 | 289 |    0 | 6497 |
| 12      | Sumatera Utara            |   25 |    8 | 444 |  693 | 5417 |
| 13      | Sumatera Barat            |   12 |    7 | 179 |  230 |  928 |
| 14      | Riau                      |   10 |    2 | 166 |  268 | 1591 |
| 15      | Jambi*                    |    9 |    2 | 141 |  163 | 1399 |
| 16      | Sumatera Selatan          |   13 |    4 | 236 |  386 | 2853 |
| 17      | Bengkulu*                 |    9 |    1 | 128 |  172 | 1341 |
| 18      | Lampung                   |   13 |    2 | 228 |  205 | 2435 |
| 19      | Kepulauan Bangka Belitung |    6 |    1 |  47 |   82 |  309 |
| 21      | Kepulauan Riau*           |    5 |    2 |  70 |  141 |  275 |
| 31      | DKI Jakarta*              |    1 |    5 |  44 |  267 |    0 |
| 32      | Jawa Barat                |   18 |    9 | 627 |  645 | 5312 |
| 33      | Jawa Tengah*              |   29 |    6 | 573 |  750 | 7809 |
| 34      | DI Yogyakarta*            |    4 |    1 |  78 |   46 |  392 |
| 35      | Jawa Timur                |   29 |    9 | 666 |  777 | 7724 |
| 36      | Banten*                   |    4 |    4 | 155 |  313 | 1238 |
| 51      | Bali*                     |    8 |    1 |  57 |   80 |  636 |
| 52      | Nusa Tenggara Barat*      |    8 |    2 | 116 |  142 |  995 |
| 53      | Nusa Tenggara Timur       |   21 |    1 | 309 |  327 | 3026 |
| 61      | Kalimantan Barat          |   12 |    2 | 174 |   99 | 2031 |
| 62      | Kalimantan Tengah         |   13 |    1 | 136 |  139 | 1432 |
| 63      | Kalimantan Selatan        |   11 |    2 | 153 |  144 | 1864 |
| 64      | Kalimantan Timur          |    7 |    3 | 103 |  197 |  841 |
| 65      | Kalimantan Utara          |    4 |    1 |  53 |   35 |  447 |
| 71      | Sulawesi Utara            |   11 |    4 | 171 |  332 | 1507 |
| 72      | Sulawesi Tengah*          |   12 |    1 | 175 |  175 | 1842 |
| 73      | Sulawesi Selatan          |   21 |    3 | 307 |  792 | 2255 |
| 74      | Sulawesi Tenggara         |   15 |    2 | 219 |  377 | 1915 |
| 75      | Gorontalo*                |    5 |    1 |  77 |   72 |  657 |
| 76      | Sulawesi Barat            |    6 |    0 |  69 |   73 |  575 |
| 81      | Maluku                    |    9 |    2 | 118 |   35 | 1198 |
| 82      | Maluku Utara              |    8 |    2 | 115 |  117 | 1063 |
| 91      | Papua                     |   28 |    1 | 560 |  110 | 5411 |
| 92      | Papua Barat**             |   12 |    1 | 218 |  106 | 1742 |
|         | TOTAL                     |  416 |   98 |7201 | 8490 |74957 |

)* data tidak mengalami perubahan dari sebelumnya

)** terdapat 11 data kelurahan di Kota Sorong yang tidak ada namanya (hanya berupa kode wilayah saja -- tidak dimasukkan dalam database, sehingga hanya di sajikan 95 data kelurahan di provinsi Papua Barat, 8479 data kelurahan secara Nasional)

Database daerah sesuai Permendagri no 56 tahun 2015

| id_prov | nama                      | kab  | kota | kec | kel  | desa |
|---------|---------------------------|-----:|-----:|-----|-----:|-----:|
| 11      | Aceh                      |   18 |    5 | 289 |    0 | 6474 |
| 12      | Sumatera Utara            |   25 |    8 | 436 |  692 | 5418 |
| 13      | Sumatera Barat            |   12 |    7 | 179 |  245 |  880 |
| 14      | Riau                      |   10 |    2 | 163 |  243 | 1592 |
| 15      | Jambi                     |    9 |    2 | 141 |  163 | 1399 |
| 16      | Sumatera Selatan          |   13 |    4 | 231 |  377 | 2859 |
| 17      | Bengkulu                  |    9 |    1 | 128 |  172 | 1341 |
| 18      | Lampung                   |   13 |    2 | 227 |  205 | 2435 |
| 19      | Kepulauan Bangka Belitung |    6 |    1 |  47 |   78 |  309 |
| 21      | Kepulauan Riau            |    5 |    2 |  70 |  141 |  275 |
| 31      | DKI Jakarta               |    1 |    5 |  44 |  267 |    0 |
| 32      | Jawa Barat                |   18 |    9 | 626 |  643 | 5319 |
| 33      | Jawa Tengah               |   29 |    6 | 573 |  750 | 7809 |
| 34      | DI Yogyakarta             |    4 |    1 |  78 |   46 |  392 |
| 35      | Jawa Timur                |   29 |    9 | 664 |  777 | 7724 |
| 36      | Banten                    |    4 |    4 | 155 |  313 | 1238 |
| 51      | Bali                      |    8 |    1 |  57 |   80 |  636 |
| 52      | Nusa Tenggara Barat       |    8 |    2 | 116 |  142 |  995 |
| 53      | Nusa Tenggara Timur       |   21 |    1 | 306 |  318 | 2995 |
| 61      | Kalimantan Barat          |   12 |    2 | 174 |   99 | 1977 |
| 62      | Kalimantan Tengah         |   13 |    1 | 136 |  138 | 1434 |
| 63      | Kalimantan Selatan        |   11 |    2 | 152 |  143 | 1866 |
| 64      | Kalimantan Timur          |    7 |    3 | 103 |  196 |  836 |
| 65      | Kalimantan Utara          |    4 |    1 |  50 |   35 |  447 |
| 71      | Sulawesi Utara            |   11 |    4 | 167 |  332 | 1505 |
| 72      | Sulawesi Tengah           |   12 |    1 | 175 |  175 | 1842 |
| 73      | Sulawesi Selatan          |   21 |    3 | 306 |  785 | 2253 |
| 74      | Sulawesi Tenggara         |   15 |    2 | 212 |  377 | 1846 |
| 75      | Gorontalo                 |    5 |    1 |  77 |   72 |  657 |
| 76      | Sulawesi Barat            |    6 |    0 |  69 |   71 |  576 |
| 81      | Maluku                    |    9 |    2 | 118 |   33 | 1198 |
| 82      | Maluku Utara              |    8 |    2 | 115 |  117 | 1064 |
| 91      | Papua                     |   28 |    1 | 558 |  110 | 5419 |
| 92      | Papua Barat               |   12 |    1 | 218 |   95 | 1744 |
|         | TOTAL                     |  416 |   98 |7160 | 8430 |74754 |

link demo bisa dilihat [di sini] http://cahyadsn.phpindonesia.id/wilayah/ (data sesuai permendagri no 137 tahun 2017)

## Referensi
- Kode dan Data Wilayah Administrasi Pemerintahan (Permendagri No.56-2015) www.kemendagri.go.id/pages/data-wilayah
- Kode dan Data Wilayah Administrasi Pemerintah (Permendagri No 137 -2017) http://www.kemendagri.go.id/produk-hukum/2018/01/18/kode-dan-data-wilayah-administrasi-pemerintahan-tahun-2017

## To Do
- update data ke kode dan data wilayah berdasarkan permendagri No 137 tahun 2017 smp dengan tingkat kelurahan/desa (done)
- on progress, convert data dari pdf -> xlsx (done) , xlsx->csv (done) , csv->sql(done) , import sql to db (done), validasi data di db dengan source (done)

## Request Data
- data tambahan berupa koordinat latitude/longitude, polygon boundaries, kodepos, timezone utk beberapa wilayah sudah tersedia, namun tidak termasuk dalam publish ini. Yang memerlukan silakan kontak/inbox/pm
- data lat/long/timezone dan polygon boundaries wilayah yg sudah cukup lengkap tersedia untuk wilayah provinsi DKI Jakarta dan DI Yogyakarta

## Pengaplikasian Lain

1. [https://github.com/codenoid/Data-Wilayah.js](https://github.com/codenoid/Data-Wilayah.js) (Untuk JavaScript, tinggal panggil dan siap pakai, jadikan cache agar lebih cepat)

## Donasi
untuk donasi via [paypal], atau bca 1451332193

[di sini]: http://cahyadsn.dev.php.or.id/wilayah/
[paypal]: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=K6YRM43CZ44UQ
