# WILAYAH
Kode dan Data Wilayah Administrasi Pemerintahan dan Pulau Indonesia sesuai Kepmendagri No 300.2.2-2138 Tahun 2025 menggunakan PHP+MySQL+AJaX

## DEMO
tautan demo web [apps versi 2.8](https://wilayah.cahyadsn.com/apps)

## SCREENSHOT
[![screenshot](https://github.com/cahyadsn/wilayah/blob/master/apps/img/250525.png?raw=true 'wilayah apps web demo v2.8')](https://wilayah.cahyadsn.com/apps)

Kode dan Data Wilayah Pemerintahan Indonesia  dalam database :
- **db/wilayah.sql** sesuai dengan Kepmendagri No 300.2.2-2138 Tahun 2025 
- **db/wilayah_level_1_2.sql** sesuai Kepmendagri No 300.2.2-2138 Tahun 2025 untuk data provinsi dan kab/kota dengan koordinat,elevation,timezone,luas, jumlah penduduk dan boundaries
- **db/archive/wilayah_2023.sql** sesuai Kepmendagri No 100.1.1-6117 Tahun 2022
- **db/archive/wilayah_2022.sql** sesuai Permendagri No 58 Tahun 2021 (_revised by Kepmendagri No. 050-145 Tahun 2022_)
- **db/archive/wilayah_2020.sql** sesuai Permendagri No. 72 Tahun 2019 (_revised by Kepmendagri No.146.1-4717 Tahun 2020_)
- **db/archive/wilayah_2018.sql** sesuai Permendagri No 137 tahun 2017
- **db/archive/wilayah_2016.sql** sesuai Permendagri No 56 Tahun 2015
- **db/archive/wilayah_level_1_2-2022.sql** sesuai Kepmendagri No 100.1.1-6117 Tahun 2022 untuk data provinsi dan kab/kota dengan koordinat,elevation,timezone,luas, jumlah penduduk dan boundaries (mysql)
- **db/archive/wilayah_level_1_2_postgresl.sql** sesuai Kepmendagri No 100.1.1-6117 Tahun 2022 untuk data provinsi dan kab/kota dengan koordinat,elevation,timezone,luas, jumlah penduduk dan boundaries (postgresql)

Kode dan Data Pulau Indonesia dalam database :
- **db/wilayah_pulau.sql** sesuai Kepmendagri No 300.2.2-2138 Tahun 2025
- **db/archive/pulau_2023.sql** sesuai Kepmendagri No 100.1.1-6117 Tahun 2022
- **db/archive/pulau_2022.sql** sesuai Permendagri No 58 Tahun 2021 (revised by Kepmendagri No. 050-145 Tahun 2022)

Data Jumlah Penduduk Indonesia per propinsi dan per kabupaten/kota dalam database
- **db/wilayah_penduduk.sql** sesuai Kepmendagri No 300.2.2-2138 Tahun 2025 *

Data Luas wilayah Indonesia per popinsi dan per kabupaten/kota dalam database
- **db/wilayah_luas.sql** sesuai dengan Kepmendagri No 300.2.2-2138 Tahun 2025 dan Badan Informasi Geospasial berdasarkan Surat Deputi Bidang Informasi Geospasial Dasar Nomor B-16.10/DIGD-BIG/IGD.04.04/12/2024, Tanggal 16 Desember 2024, Hal Penghitungan Luas Wilayah di Seluruh Indonesia

## TAUTAN TERKAIT
- Data kodepos vs kode wilayah administrasi pemerintahan Indoensia : https://github.com/cahyadsn/wilayah_kodepos
- Data referensi Kode dan Data Wilayah Administrasi Pemerintahan dan Pulau Indonesia : https://github.com/cahyadsn/wilayah_ref
- Data boundaries/polygon berdasarkan kode wilayah adminsitrasi pemerintahan Indonesia : https://github.com/cahyadsn/wilayah_boundaries

[![GitHub license](https://img.shields.io/badge/license-MIT-blue.svg)](LICENSE)
[![GitHub issues](https://img.shields.io/github/issues/cahyadsn/wilayah.svg)](https://github.com/cahyadsn/wilayah/issues)
[![GitHub forks](https://img.shields.io/github/forks/cahyadsn/wilayah.svg)](https://github.com/cahyadsn/wilayah/network)
[![GitHub stars](https://img.shields.io/github/stars/cahyadsn/wilayah.svg)](https://github.com/cahyadsn/wilayah/stargazers)
[![GitHub last commit](https://img.shields.io/github/last-commit/google/skia.svg?style=flat)]()
[![Donate](https://img.shields.io/badge/$-support-ff69b4.svg?style=flat)](https://paypal.me/cahyadwiana)

## Data Kepmendagri No 300.2.2-2138 Tahun 2025
Database Data dan Kode Wilayah Administrasi Pemerintahan sesuai Kepmendagri No 300.2.2-2138 Tahun 2025 (wilayah.sql)

| id_prov | nama                       | kab   | kota  | kec  | kel   | desa  |    luas     | penduduk  | pulau  |
|---------|----------------------------|------:|------:|------|------:|------:|------------:|----------:|-------:|
| 11      | Aceh*                      |   18  |    5  | 290  |    0  | 6500  |   56835.019 |   5623479 |   365* |
| 12      | Sumatera Utara             |   25  |    8  | 455  |  693  | 5417  |   72437.755 |  15640905 |   228  |
| 13      | Sumatera Barat             |   12  |    7  | 179  |  230  | 1035  |   42107.674 |   5820359 |   219  |
| 14      | Riau                       |   10  |    2  | 172  |  271  | 1591  |   89900.780 |   7099297 |   144  |
| 15      | Jambi                      |    9  |    2  | 144  |  171  | 1414  |   49023.037 |   3834439 |    14  |
| 16      | Sumatera Selatan*          |   13  |    4  | 241  |  403  | 2856* |   86771.918 |   9064690 |    24  |
| 17      | Bengkulu                   |    9  |    1  | 129  |  172  | 1341  |   20122.210 |   2127957 |     9  |
| 18      | Lampung                    |   13  |    2  | 229  |  205  | 2446  |   33570.758 |   9144263 |   172  |
| 19      | Kepulauan Bangka Belitung* |    6  |    1  |  47  |   84  |  309  |   16670.225 |   1549562 |   501* |
| 21      | Kepulauan Riau             |    5  |    2  |  80  |  144  |  275  |    8170.375 |   2271890 |  2028  |
| 31      | DKI Jakarta                |    1  |    5  |  44  |  267  |    0  |     661.530 |  11038216 |   113  |
| 32      | Jawa Barat                 |   18  |    9  | 627  |  646  | 5311  |   37053.331 |  51316378 |    30  |
| 33      | Jawa Tengah                |   29  |    6  | 576  |  753  | 7810  |   34347.428 |  38430645 |    71  |
| 34      | DI Yogyakarta*             |    4  |    1  |  78  |   46  |  392  |    3170.363 |   3743365 |    37* |
| 35      | Jawa Timur*                |   29  |    9  | 666  |  773  | 7721  |   48055.876 |  41919906 |   538* |
| 36      | Banten*                    |    4  |    4  | 155  |  314  | 1238  |    9355.763 |  12881374 |    80* |
| 51      | Bali*                      |    8  |    1  |  57  |   80  |  636  |    5582.827 |   4375263 |    41* |
| 52      | Nusa Tenggara Barat*       |    8  |    2  | 117  |  145  | 1021  |   19631.991 |   5751295 |   430* |
| 53      | Nusa Tenggara Timur*       |   21  |    1  | 315  |  305  | 3137  |   46378.105 |   5700772 |   653* |
| 61      | Kalimantan Barat*          |   12  |    2  | 174  |   99  | 2046  |  147018.063 |   5646268 |   260* |
| 62      | Kalimantan Tengah          |   13  |    1  | 136  |  139  | 1432  |  153430.363 |   2825290 |    71  |
| 63      | Kalimantan Selatan         |   11  |    2  | 156  |  144  | 1872  |   37125.426 |   4305281 |   165  |
| 64      | Kalimantan Timur*          |    7  |    3  | 105  |  197  |  841  |  126951.758 |   4123303 |   244* |
| 65      | Kalimantan Utara           |    4  |    1  |  55  |   35  |  447  |   69900.886 |    770627 |   196  |
| 71      | Sulawesi Utara*            |   11  |    4  | 171  |  332  | 1507  |   14488.429 |   2645291 |   382* |
| 72      | Sulawesi Tengah*           |   12  |    1  | 177* |  175  | 1842  |   61496.983 |   3219494 |  1600* |
| 73      | Sulawesi Selatan*          |   21  |    3  | 313  |  793  | 2266  |   45323.975 |   9528276 |   394* |
| 74      | Sulawesi Tenggara*         |   15  |    2  | 221  |  377* | 1908  |   36139.303 |   2824589 |   591* |
| 75      | Gorontalo                  |    5  |    1  |  77  |   72  |  657  |   12024.982 |   1250960 |   127  |
| 76      | Sulawesi Barat             |    6  |    0  |  69  |   73  |  575  |   16590.667 |   1466741 |    69  |
| 81      | Maluku*                    |    9  |    2  | 119* |   35  | 1200  |   46133.832 |   1935586 |  1422* |
| 82      | Maluku Utara*              |    8  |    2  | 118  |  118  | 1067  |   31465.977 |   1394231 |   975* |
| 91      | Papua*                     |    8  |    1  | 105  |   51  |  948  |   81383.315 |   1102360 |   544* |
| 92      | Papua Barat*               |    7* |    0* |  91* |   21* |  803* |   60308.590 |    576255 |  1498* |
| 93      | Papua Selatan              |    4  |    0  |  82  |   13  |  677  |  117858.969 |    562220 |     7  |
| 94      | Papua Tengah               |    8  |    0  | 131  |   36  | 1172  |   61079.587 |   1369112 |    50  |
| 95      | Papua Pegunungan           |    8  |    0  | 252  |   10  | 2617  |   52508.656 |   1470518 |     0  |
| 96+     | Papua Barat Daya+          |    5+ |    1+ | 132+ |   74+ |  939+ |   39103.058 |    623186 |  3082+ |
|         | TOTAL*                     |  416  |   98  |7285* | 8496* |75266* | 1890179.784 | 284973643 | 17380* |

**NOTE** :

)* data mengalami perubahan dari data sebelumnya (data Kepmendagri No. 100.1.1-6117 Tahun 2022)

)+ data baru dari Kepmendagri No. 300.2.2-2138 Tahun 2025

- Nama Provinsi diurutkan sesuai dengan Kode Wilayah Administrasi Pemerintahan
- Luas wilayah (dalam km persegi) bersumber dari Badan Informasi Geospasial berdasarkan Surat Deputi Bidang Informasi Geospasial Dasar Nomor B-16.10/DIGD-BIG/IGD.04.04/12/2024, Tanggal 16 Desember 2024, Hal Penghitungan Luas Wilayah di Seluruh Indonesia
- Jumlah penduduk bersumber dari Ditjen Kependudukan dan Pencatatan Sipil Kemendagri (Data Kependudukan Semester II Bulan Desember Tahun 2024)
- Jumlah pulau termasuk 6 pulau besar ( Sumatera, Jawa, Kalimantan, Sulawesi, Timor, dan Papua) 
- Sumber data pulau Gazeter Republik Indonesia (GRI) Tahun 2024 yang diterbitkan oleh Badan Informasi Geospasial (BIG)
- Jumlah pulau termasuk 6 pulau besar ( Sumatera, Jawa, Kalimantan, Sulawesi, Timor, dan Papua

link demo bisa dilihat [di sini] https://wilayah.cahyadsn.com/

## Data Kepmendagri No 100.1.1-6117 Tahun 2022
Database Data dan Kode Wilayah Administrasi Pemerintahan sesuai Kepmendagrin No 100.1.1-6117 Tahun 2022 (wilayah.sql)

| id_prov | nama                      | kab   | kota  | kec  | kel   | desa  | pulau  |
|---------|---------------------------|------:|------:|------|------:|------:|-------:|
| 11      | Aceh*                     |   18  |    5  | 290  |    0  | 6500* |   363  |
| 12      | Sumatera Utara            |   25  |    8  | 455  |  693  | 5417  |   229  |
| 13      | Sumatera Barat*           |   12  |    7  | 179  |  230  | 1035* |   219* |
| 14      | Riau                      |   10  |    2  | 172  |  271  | 1591  |   144  |
| 15      | Jambi*                    |    9  |    2  | 144  |  171* | 1414* |    14  |
| 16      | Sumatera Selatan*         |   13  |    4  | 241  |  403* | 2855* |    24  |
| 17      | Bengkulu                  |    9  |    1  | 129  |  172  | 1341  |     9  |
| 18      | Lampung*                  |   13  |    2  | 229  |  205  | 2446* |   172  |
| 19      | Kepulauan Bangka Belitung |    6  |    1  |  47  |   84  |  309  |   507  |
| 21      | Kepulauan Riau*           |    5  |    2  |  80* |  144* |  275  |  2028* |
| 31      | DKI Jakarta               |    1  |    5  |  44  |  267  |    0  |   113  |
| 32      | Jawa Barat*               |   18  |    9  | 627  |  646* | 5311* |    30  |
| 33      | Jawa Tengah*              |   29  |    6  | 576  |  753  | 7810* |    71  |
| 34      | DI Yogyakarta             |    4  |    1  |  78  |   46  |  392  |    33  |
| 35      | Jawa Timur*               |   29  |    9  | 666  |  773* | 7721* |   512* |
| 36      | Banten                    |    4  |    4  | 155  |  314  | 1238  |    81  |
| 51      | Bali                      |    8  |    1  |  57  |   80  |  636  |    34  |
| 52      | Nusa Tenggara Barat*      |    8  |    2  | 117  |  145  | 1021* |   403  |
| 53      | Nusa Tenggara Timur*      |   21  |    1  | 315  |  305* | 3137* |   609* |
| 61      | Kalimantan Barat*         |   12  |    2  | 174  |   99  | 2046* |   251* |
| 62      | Kalimantan Tengah*        |   13  |    1  | 136  |  139  | 1432  |    71* |
| 63      | Kalimantan Selatan*       |   11  |    2  | 156  |  144  | 1872* |   165* |
| 64      | Kalimantan Timur*         |    7  |    3  | 105  |  197  |  841  |   243  |
| 65      | Kalimantan Utara          |    4  |    1  |  55  |   35  |  447  |   196  |
| 71      | Sulawesi Utara*           |   11  |    4  | 171  |  332  | 1507  |   353* |
| 72      | Sulawesi Tengah           |   12  |    1  | 175  |  175  | 1842  |  1572  |
| 73      | Sulawesi Selatan*         |   21  |    3  | 313* |  793  | 2266* |   370* |
| 74      | Sulawesi Tenggara*        |   15  |    2  | 221* |  379* | 1908  |   590  |
| 75      | Gorontalo                 |    5  |    1  |  77  |   72  |  657  |   127  |
| 76      | Sulawesi Barat            |    6  |    0  |  69  |   73  |  575  |    69  |
| 81      | Maluku*                   |    9  |    2  | 118  |   35  | 1200* |  1388* |
| 82      | Maluku Utara*             |    8  |    2  | 118  |  118  | 1067* |   901* |
| 91      | Papua*                    |    8* |    1  | 105* |   51* |  948* |   527* |
| 92      | Papua Barat               |   12  |    1  | 218  |   95  | 1742  |  4520* |
| 93+     | Papua Selatan+            |    4+ |    0  |  82+ |   13+ |  677+ |     7+ |
| 94+     | Papua Tengah+             |    8+ |    0  | 131+ |   36+ | 1172+ |    50+ |
| 95+     | Papua Pegunungan+         |    8+ |    0  | 252+ |   10+ | 2617+ |     0  |
|         | TOTAL*                    |  416  |   98  |7277* | 8498* |75265* | 17001* |

)* data mengalami perubahan dari data sebelumnya (data Kepmendagri No. 050-145 Tahun 2022)

)+ data baru dari Kepmendagri No. 100.1.1-6117 Tahun 2022

) ** Jumlah pulau termasuk 6 pulau besar ( Sumatera, Jawa, Kalimantan, Sulawesi, Timor, dan Papua
## Data Kepmendagri No 050-145 Tahun 2022
Database Data dan Kode Wilayah Administrasi Pemerintahan sesuai dengan Kepmendagri No 100.1.1-6117 Tahun 2022 (wilayah.sql)

| id_prov | nama                      | kab  | kota | kec | kel  | desa | pulau |
|---------|---------------------------|-----:|-----:|-----|-----:|-----:|------:|
| 11      | Aceh*                     |   18 |    5 | 290 |    0 | 6497 |   363 |
| 12      | Sumatera Utara*           |   25 |    8 | 455 |  693 | 5417 |   229 |
| 13      | Sumatera Barat*           |   12 |    7 | 179 |  230 |  929 |   218 |
| 14      | Riau*                     |   10 |    2 | 172 |  271 | 1591 |   144 |
| 15      | Jambi*                    |    9 |    2 | 144 |  165 | 1399 |    14 |
| 16      | Sumatera Selatan*         |   13 |    4 | 241 |  395 | 2853 |    24 |
| 17      | Bengkulu*                 |    9 |    1 | 129 |  172 | 1341 |     9 |
| 18      | Lampung*                  |   13 |    2 | 229 |  205 | 2435 |   172 |
| 19      | Kepulauan Bangka Belitung*|    6 |    1 |  47 |   84 |  309 |   507 |
| 21      | Kepulauan Riau*           |    5 |    2 |  76 |  142 |  275 |  2025 |
| 31      | DKI Jakarta               |    1 |    5 |  44 |  267 |    0 |   113 |
| 32      | Jawa Barat                |   18 |    9 | 627 |  645 | 5312 |    30 |
| 33      | Jawa Tengah               |   29 |    6 | 576 |  753 | 7809 |    71 |
| 34      | DI Yogyakarta             |    4 |    1 |  78 |   46 |  392 |    33 |
| 35      | Jawa Timur                |   29 |    9 | 666 |  777 | 7724 |   504 |
| 36      | Banten*                   |    4 |    4 | 155 |  314 | 1238 |    81 |
| 51      | Bali                      |    8 |    1 |  57 |   80 |  636 |    34 |
| 52      | Nusa Tenggara Barat       |    8 |    2 | 117 |  145 | 1005 |   403 |
| 53      | Nusa Tenggara Timur*      |   21 |    1 | 315 |  327 | 3026 |   600 |
| 61      | Kalimantan Barat          |   12 |    2 | 174 |   99 | 2031 |   249 |
| 62      | Kalimantan Tengah*        |   13 |    1 | 136 |  139 | 1432 |    69 |
| 63      | Kalimantan Selatan*       |   11 |    2 | 156 |  144 | 1864 |   158 |
| 64      | Kalimantan Timur*         |    7 |    3 | 105 |  197 |  841 |   243 |
| 65      | Kalimantan Utara*         |    4 |    1 |  55 |   35 |  447 |   196 |
| 71      | Sulawesi Utara            |   11 |    4 | 171 |  332 | 1507 |   329 |
| 72      | Sulawesi Tengah           |   12 |    1 | 175 |  175 | 1842 |  1572 | 
| 73      | Sulawesi Selatan*         |   21 |    3 | 311 |  793 | 2255 |   355 |
| 74      | Sulawesi Tenggara*        |   15 |    2 | 220 |  378 | 1908 |   590 |
| 75      | Gorontalo                 |    5 |    1 |  77 |   72 |  657 |   127 |
| 76      | Sulawesi Barat            |    6 |    0 |  69 |   73 |  575 |    69 |
| 81      | Maluku                    |    9 |    2 | 118 |   35 | 1198 |  1337 |  
| 82      | Maluku Utara*             |    8 |    2 | 118 |  118 | 1063 |   837 |
| 91      | Papua*                    |   28 |    1 | 566 |  110 | 5411 |   547 |
| 92      | Papua Barat               |   12 |    1 | 218 |   95 | 1742 |  4514 |
|         | TOTAL*                    |  416 |   98 |7266 | 8506 |74961 | 16772 |

)* data mengalami perubahan dari data sebelumnya (data permendagri No 72 Tahun 2019/Kepmendagri No.146.1-4717 Tahun 2020)

) ** Jumlah pulau termasuk 6 pulau besar ( Sumatera, Jawa, Kalimantan, Sulawesi, Timor, dan Papua

# Data Kepmendagri No 146.1-4717 Tahun 2020
Database Data dan Kode Wilayah Administrasi Pemerintahan sesuai Permendagri No 72 Tahun 2019  dengan revisi berdasarkan KepmendagrinNo 146.1-4717 Tahun 2020 (wilayah_2020.sql)

| id_prov | nama                      | kab  | kota | kec | kel  | desa |
|---------|---------------------------|-----:|-----:|-----|-----:|-----:|
| 11      | Aceh                      |   18 |    5 | 289 |    0 | 6497 |
| 12      | Sumatera Utara*           |   25 |    8 | 444 |  693 | 5417 |
| 13      | Sumatera Barat            |   12 |    7 | 179 |  230 |  928 |
| 14      | Riau*                     |   10 |    2 | 166 |  268 | 1591 |
| 15      | Jambi                     |    9 |    2 | 141 |  163 | 1399 |
| 16      | Sumatera Selatan*         |   13 |    4 | 236 |  386 | 2853 |
| 17      | Bengkulu*                 |    9 |    1 | 128 |  172 | 1341 |
| 18      | Lampung                   |   13 |    2 | 228 |  205 | 2435 |
| 19      | Kepulauan Bangka Belitung |    6 |    1 |  47 |   82 |  309 |
| 21      | Kepulauan Riau*           |    5 |    2 |  70 |  142 |  275 |
| 31      | DKI Jakarta               |    1 |    5 |  44 |  267 |    0 |
| 32      | Jawa Barat                |   18 |    9 | 627 |  645 | 5312 |
| 33      | Jawa Tengah*              |   29 |    6 | 576 |  753 | 7809 |
| 34      | DI Yogyakarta             |    4 |    1 |  78 |   46 |  392 |
| 35      | Jawa Timur                |   29 |    9 | 666 |  777 | 7724 |
| 36      | Banten                    |    4 |    4 | 155 |  313 | 1238 |
| 51      | Bali                      |    8 |    1 |  57 |   80 |  636 |
| 52      | Nusa Tenggara Barat*      |    8 |    2 | 117 |  145 |  995 |
| 53      | Nusa Tenggara Timur       |   21 |    1 | 309 |  327 | 3026 |
| 61      | Kalimantan Barat          |   12 |    2 | 174 |   99 | 2031 |
| 62      | Kalimantan Tengah         |   13 |    1 | 136 |  139 | 1432 |
| 63      | Kalimantan Selatan        |   11 |    2 | 153 |  144 | 1864 |
| 64      | Kalimantan Timur          |    7 |    3 | 103 |  197 |  841 |
| 65      | Kalimantan Utara          |    4 |    1 |  53 |   35 |  447 |
| 71      | Sulawesi Utara            |   11 |    4 | 171 |  332 | 1507 |
| 72      | Sulawesi Tengah           |   12 |    1 | 175 |  175 | 1842 |
| 73      | Sulawesi Selatan*         |   21 |    3 | 311 |  792 | 2255 |
| 74      | Sulawesi Tenggara*        |   15 |    2 | 219 |  377 | 1911 |
| 75      | Gorontalo*                |    5 |    1 |  77 |   72 |  657 |
| 76      | Sulawesi Barat            |    6 |    0 |  69 |   73 |  575 |
| 81      | Maluku                    |    9 |    2 | 118 |   35 | 1198 |
| 82      | Maluku Utara*             |    8 |    2 | 116 |  118 | 1063 |
| 91      | Papua                     |   28 |    1 | 560 |  110 | 5411 |
| 92      | Papua Barat*              |   12 |    1 | 218 |   95 | 1742 |
|         | TOTAL*                    |  416 |   98 |7230 | 8488 |74953 |

)* data mengalami perubahan dari data sebelumnya (data permendagri No 137 Tahun 2017)

## Data Permendagri No 137 Tahun 2017
Database Data dan Kode Wilayah Administrasi Pemerintahan sesuai Permendagri No 137 Tahun 2017 (wilayah_2018.sql)

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

)* data tidak mengalami perubahan dari sebelumnya (data Permendagri No 56 Tahun 2015)

)** terdapat 11 data kelurahan di Kota Sorong yang tidak ada namanya (hanya berupa kode wilayah saja -- tidak dimasukkan dalam database, sehingga hanya di sajikan 95 data kelurahan di provinsi Papua Barat, 8479 data kelurahan secara Nasional)

## Data Permendagri no 56 tahun 2015
Database daerah sesuai Permendagri no 56 tahun 2015 (wilayah_2016.sql)

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


## Referensi
- Dokumen Referensi : [https://github.com/cahyadsn/wilayah_ref](https://github.com/cahyadsn/wilayah_ref)
- Keputusan Menteri Dalam Negeri Nomor 100.1.1-6117 Tahun 2022 Tentang Pemberian dan Pemutakhiran Kode, Data Wilayah Adminstrasi Pemerintahan, dan Pulau (Ditetapkan pada 9 November 2022) * 
- UU No 14 Tahun 2022 tentang Pembentukan Provinsi Papua Selatan (LN.2022/No.157, TLN No.6803, jdih.setneg.go.id: 15 hlm., 25 Juli 2022)
- UU No 15 Tahun 2022 tentang Pembentukan Provinsi Papua Tengah (LN.2022/No.158, TLN No.6804, jdih.setneg.go.id: 14 hlm., 25 Juli 2022)
- UU No 16 Tahun 2022 tentang Pembentukan Provinsi Papua Pegunungan (LN.2022/No.159, TLN No.6805, jdih.setneg.go.id: 14 hlm., 25 Juli 2022)
- Keputusan Menteri Dalam Negeri Nomor 050-145 Tahun 2022 Tentang Pemberian Kode, Data Wilayah Administrasi Pemerintahan, Dan Pulau Tahun 2021 (Kepmendagri No. 050-145 Tahun 2022, [https://www.kemendagri.go.id/arsip/detail/10857/keputusan-menteri-dalam-negeri-nomor-050145-tahun-2022-tentang-pemberian-kode-data-wilayah-administrasi-pemerintahan-dan-pulau-tahun-2021](https://www.kemendagri.go.id/arsip/detail/10857keputusan-menteri-dalam-negeri-nomor-050145-tahun-2022-tentang-pemberian-kode-data-wilayah-administrasi-pemerintahan-dan-pulau-tahun-2021) (Ditetapkan pada tanggal 14 Februari 2022)
- Peraturan Menteri Dalam Negeri Republik Indonesia Nomor 58 Tahun 2021 Tentang Kode, Data Wilayah Administrasi Pemerintahan, Dan Pulau [https://paralegal.id/peraturan/peraturan-menteri-dalam-negeri-nomor-58-tahun-2021/](https://paralegal.id/peraturan/peraturan-menteri-dalam-negeri-nomor-58-tahun-2021/) (Permendagri No.58 2021, Ditetapkan pada tanggal 13 Desember 2021,Berita Negara Tahun 2021 Nomor 1391)
- Penetapan Nama, Kode Dan Jumlah Desa Seluruh Indonesia Tahun 2020 (Kepmendagri No. 146.1-4717 - 2020) [http://binapemdes.kemendagri.go.id/produkhukum/detil/keputusan-menteri-dalam-negeri-nomor-1461-4717-tahun-2020](http://binapemdes.kemendagri.go.id/produkhukum/detil/keputusan-menteri-dalam-negeri-nomor-1461-4717-tahun-2020) (Ditetapkan pada tanggal 21 Desember 2020)
- Kode dan Data Wilayah Administrasi Pemerintahan (Permendagri No.72-2019) [https://www.kemendagri.go.id/page/read/48/peraturan-menteri-dalam-negeri-no72-tahun-2019](https://www.kemendagri.go.id/page/read/48/peraturan-menteri-dalam-negeri-no72-tahun-2019) (Berita Negara Republik Indonesia Tahun 2019 Nomor 1327, Ditetapkan pada tanggal 8 Oktober 2019)
- Kode dan Data Wilayah Administrasi Pemerintahan (Permendagri No.137-2017) [http://www.kemendagri.go.id/produk-hukum/2018/01/18/kode-dan-data-wilayah-administrasi-pemerintahan-tahun-2017]( http://www.kemendagri.go.id/produk-hukum/2018/01/18/kode-dan-data-wilayah-administrasi-pemerintahan-tahun-2017) (Berita Negara Republik Indonesia Tahun 2017 Nomor 1955, Ditetapkan pada tanggal 27 Desember 2017)
- Kode dan Data Wilayah Administrasi Pemerintahan (Permendagri No.56-2015) [http://www.kemendagri.go.id/pages/data-wilayah](http://www.kemendagri.go.id/pages/data-wilayah) (Berita Negara Republik Indonesia Tahun 2015 Nomor 1045, Ditetapkan pada tanggal 29 Juni 2015)


## Change Log
## RELEASE v2025.7 [2025-07-04]
- update data jumlah penduduk di **db/wilayah_penduduk.sql**, Jumlah penduduk bersumbor dari Ditjen Kependudukan dan Pencatatan Sipil Kemendagri (Data Kependudukan Semester I Bulan DesemberTahun 2024)  2025-07-04
- update data pulau di **db/wilayah_pulau.sql** berdasar dari Sumber data pulau Gazeter Republik Indonesia (GRI) Tahun 2024 yang diterbitkan oleh Badan Informasi Geospasial (BIG) 2025-07-04
- update data luas wilayah di **db/wilayah_level_1_2.sql** berdasar dari Badan Informasi Geospasial berdasarkan Surat Deputi Bidang Informasi Geospasial Dasar Nomor B-16.10/DIGD-BIG/IGD.04.04/12/2024, Tanggal 16 Desember 2024, Hal Penghitungan Luas Wilayah di Seluruh Indonesia 2025-07-04
- menambahkan data luas wilayah Indonesia per propinsi, per kabupaten/kota berdasarkan di **db/wilayah_luas.sql**, data Badan Informasi Geospasial berdasarkan Surat Deputi Bidang Informasi Geospasial Dasar Nomor B-16.10/DIGD-BIG/IGD.04.04/12/2024, Tanggal 16 Desember 2024, Hal Penghitungan Luas Wilayah di Seluruh Indonesia 2025-07-04


## RELEASE v2025.6 [2025-06-27]
- menambahkan data jumlah penduduk Indonesia per propinsi, per kabupaten/kota 2025-06-27
- verifikasi data kode wilayah prov. Papua,Papua Barat,Papua Selatam,Papua Tengah,Papua Pegunungan,Papua Barat Daya 2025-06-27
- verifikasi data kode wilayah prov. Gorontalo,Sulbar,Maluku,Malut 2025-06-26
- verifikasi data kode wilayah prov. Sulut,Sulteng,Sulsel,Sultra 2025-06-25
- verifikasi data kode wilayah prov. Kalsel,Kaltim,Kaltara 2025-06-23
- verifikasi data kode wilayah prov. Kalbar,Kalteng 2025-06-20
- verifikasi data kode wilayah prov. Banten,Bali,NTB,NTT 2025-06-19
- verifikasi data kode wilayah prov. Jawa Timur 2025-06-18
- verifikasi data kode wilayah prov. Jawa Barat, Jawa Tengah, DIY 2025-06-17
- verifikasi data kode wilayah prov. DKI Jakarta 2025-06-14
- verifikasi data kode wilayah prov. Kep Babel, Kepri 2025-06-13
- verifikasi data kode wilayah prov. Sumatera Selatan,Bengkulu,Lampung 2025-06-12
- verifikasi data kode wilayah prov. Riau 2025-06-11
- verifikasi data kode wilayah prov. Sumatera Barat,Jambi 2025-06-10
- verifikasi data kode wilayah prov. Sumatera Utara 2025-06-09
- verifikasi data kode wilayah prov. Aceh 2025-06-08
- update data kode dan perubahan nama pulau di provinsi-provinsi wilayah sulawesi,maluku,papua 2025-06-04
- update data kode dan perubahan nama pulau di provinsi-provinsi wilayah balinusra, kalimantan 2025-06-03
- update data kode dan perubahan nama pulau di provinsi-provinsi wilayah jawa 2025-06-02
- update data kode dan perubahan nama pulau di provinsi-provinsi wilayah jawa 2025-06-02
- update data kode dan perubahan nama pulau di provinsi-provinsi wilayah sumatera 2025-06-02
- update data kode pulau prov Maluku, Malut 2025-05-31
- update data kode pulau prov Sulut,Sulteng,Sulsel,Sultra 2025-05-30
- update data kode pulau prov Kalbar, Kalteng,Kalsel,Kaltim,Kaltara 2025-05-30
- update data kode pulau prov Jawa Timur,Banten,Bali,NTB,NTT 2025-05-29
- update data kode pulau prov Bangka Belitung dan DI Yogyakarta 2025-05-28
- update data kode wilayah dan pulau prov Aceh dan Sumatera Selatan 2025-05-26
- update data kode wilayah prov Sulteng dan Maluku (pemekaran kecamatan) 2025-05-25 
- update data kode wilayah provinsi Papua Barat dan Papua Barat Daya 2025-05-25
- update web apps demo ke v2.8 (Data sesuai Kepmendagri No 300.2.2-2138 tahun 2025) 2025-05-25

- update web apps demo ke v2.7 2025-05-13
- update web demo ke v3.0 beta 1 2025-03-17
    - update main web demo dengan data data berdasarkan Kepmendagri No 100.1.1.6117 Tahun 2022
    - menampilkan data kode wilayah di sidebar 
    - menampilkan data kodepos
    - menampilkan data kode wilayah di sidebar
- update penambahan kekurangan data 1 kelurahan di prov Riau dan 1 desa di prov Sulawesi Selatan 2025-03-09
- update wilayah_level_1_2.sql dan wilayah_level_1_2_postgresl.sql untuk boundaries kota Sabang 2025-02-23
- update library jquery js ke zepto js 2025-01-27
- update data desa di prov Papua Barat (perubahan nama dan ppindahan kecamatan 2024-12-31)
- update data desa di prov Riau (perpindahan kecamatan 2024-12-31)
- update data desa/kelurahan di prov Sulawesi Selatan (perubahan nama dan status 2024-12-31)
- update data desa di prov Kalimantan Utara (perpindahan kecamatan 2024-12-31)
- update data desa di prov Jawa Tengah (perpindahan kecamatan 2024-12-31)
- update data desa di prov Jawa Barat (perubahan nama dan perpindahan kecamatan 2024-12-31)
- update data desa di prov Jambi (perubahan nama dan perpindahan kecamatan 2024-12-31)
- update data desa di prov Papua (perubahan nama, status desa adat dan perpindahan kecamatan 2024-12-31)
- update data desa di prov Sumatera Utara (perubahan nama dan perpindahan kecamatan 2024-12-31)
- update web demo ke v2.6 dengan data berdasarkan Kepmendagri No 100.1.1.6117 Tahun 2022 (done 2024-05-15)
    - data level 1 dan 2 berdasarkan Kepmendagri No 100.1.1.6117 Tahun 2022
- update data wilayah_level_1_2.sql berdasarkan Kepmendagri No 100.1.1.6117 Tahun 2022 (done 2024-04-30)
- verify perubahan dan perbaikan data (done)
- update data kode wilayah berdasarkan Kepmendagri No 100.1.1.6117 Tahun 2022 (done)
- update data kode pulau berdasarkan Kepmendagri No 100.1.1.6117 Tahun 2022 (done)
- memindahkan database kode wilayah dan pulau sebelumnya ke folder db/archive (done)
- update web demo ke v2.5.1 (done)
    - encrypted polygon data
- update web demo ke v2.4 (done)
- update dan penambahan data jumlah penduduk dan luas wilayah data wilayah level 1 dan 2 (done)
- update data based on Permendagri No.58 Tahun 2021 &  Kepmendagri No. 050-145 Tahun 2022 (done)
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

## INFORMASI
- data kode wilayah pemerintahan diperoleh dari website Kemendagri di https://jdih.kemendagri.go.id/dokumen/view?id=1937
- data polygon diperoleh dari website BIG(Badan Informasi Geospatial) di https://tanahair.indonesia.go.id
- data latitude/longitude dari google maps

## DONASI
- untuk donasi via transfer
    - Bank BCA Digital (Blu) (501) 000 576 776 186
    - Bank Jago (542) 5003 5796 1022
    - Bank Sinarmas (153) 005 462 4719
    - Bank Syariah Indonesia (BSI) 821-342-5550
- untuk donasi via PayPal [https://paypal.me/cahyadwiana]
- untuk donasi via QRIS CAHYADSN ID1022183125288 :

![screenshot](https://github.com/cahyadsn/wilayah/blob/master/docs/qr_code.cahyadsn.png?raw=true 'Donasi via QRIS CAHYADSN')

[di sini]: https://wilayah.cahyadsn.com/

## Contact
+ facebook (https://m.facebook.com/cahya.dsn)
+ email (cahyadsn@gmail.com)
