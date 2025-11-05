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

> link demo bisa dilihat [di sini] https://wilayah.cahyadsn.com/

## KODEFIKASI DATA WILAYAH
Kode Wilayah Administrasi Pemerintahan adalah serangkaian angka dan titik yang menunjukkan Kode dan Data Wilayah Administrasi Pemerintahan Indonesia pada setiap daerah/wilayah mulai dari tingkat desa/kelurahan, kecamatan, kota/kabupaten, hingga provinsi yang digunakan untuk mempermudah dan mempercepat pengeloaan wilayah administrasi pemerintahan Republik Indonesia.

Berdasarkan Permendagri No 58 Tahun 2021 kodefikasi data wilayah administrasi pemerintahan dan pulau adalah sesuai aturan susunan sebagai berikut:

### KODE WILAYAH
**Kode Provinsi**
Kode untuk daerah provinsi terdiri dari 2 (dua) digit yaitu:
1. Digit pertama Kode untuk daerah provinsi didasarkan pada letak geografis Pulau/kepulauan Indonesia yang dimulai dari arah barat ke timur; dan
2. Digit kedua diisi sesuai dengan urutan pembentukan provinsi.

**Kode Kabupaten/Kota**
Kode untuk daerah kabupaten/kota terdiri dari 4 (empat) digit, yaitu:
1. Kode untuk daerah provinsi berjumlah 2 (dua) digit;
2. Kode untuk daerah kabupaten/kota berjumlah 2 (dua) digit yang ditulis secara berurutan;
3. Digit ketiga dan keempat dari 4 (empat) digit Kode untuk kabupaten diisi dengan angka 01 (nol  satu)  sampai  dengan  69  (enam  puluh sembilan); dan
4. Digit ketiga dan keempat dari 4 (empat) digit Kode untuk kota diisi dengan angka 71 (tujuh puluh satu) sampai dengan 99 (sembilan puluh sembilan).

**Kode Kecematan**
Kode  untuk  Kecamatan  berjumlah  6  (enam)  digit yang terdiri dari Kode untuk daerah provinsi 2 (dua) digit, Kode untuk daerah kabupaten/kota berjumlah 2 (dua) digit, dan Kode untuk Kecamatan berjumlah 2 (dua) digit yang ditulis secara berurutan.

**Kode Desa/Kelurahan**
Kode untuk Kelurahan dan Desa berjumlah 10 (sepuluh) digit, terdiri dari Kode untuk daerah provinsi berjumlah 2 (dua) digit, Kode untuk daerah kabupaten/kota berjumlah 2 (dua) digit, Kode untuk Kecamatan berjumlah 2 (dua) digit, dan Kode untuk Kelurahan dan Desa berjumlah 4 (empat) digit yang ditulis secara berurutan, sebagai berikut:
1. urutan pertama dari 4 (empat) digit Kode untuk Kelurahan menggunakan angka 1 (satu); dan
2. urutan pertama dari 4 (empat) digit Kode untuk Desa menggunakan angka 2 (dua).
3. urutan pertama dari 4 (empat) digit Kode untuk Desa adat menggunakan angka 3 (tiga).

### KODE PULAU
Kode Pulau berjumlah 9 (sembilan) digit terdiri dari:
1. Kode untuk daerah provinsi berjumlah 2 (dua) digit,
2. Kode untuk daerah kabupaten/kota berjumlah 2 (dua) digit; dan
3. Kode  Pulau  berjumlah  5  (lima)  digit  dengan urutan pertama menggunakan angka 4 (empat).

## REFERENSI
- Dokumen Referensi : https://github.com/cahyadsn/wilayah_ref
- Keputusan Menteri Dalam Negeri Nomor 300.2.2-2138 tahun 2025 Tentang Pemberian Dan Pemutakhiran Kode, Data Wilayah Administrasi Pemerintahan, Dan Pulau (Ditetapkan pada 25 April 2025)
- Keputusan Menteri Dalam Negeri Nomor 100.1.1-6117 Tahun 2022 Tentang Pemberian dan Pemutakhiran Kode, Data Wilayah Adminstrasi Pemerintahan, dan Pulau (Ditetapkan pada 9 November 2022)
- UU No 29 Tahun 2022 tentang Pembentukan Provinsi Papua Barat Daya (_LN.2022/No.223, TLN No.6831, jdih.setneg.go.id: 15 hlm., 08 Desember 2022_)
- UU No 14 Tahun 2022 tentang Pembentukan Provinsi Papua Selatan (LN.2022/No.157, TLN No.6803, jdih.setneg.go.id: 15 hlm., 25 Juli 2022)
- UU No 15 Tahun 2022 tentang Pembentukan Provinsi Papua Tengah (LN.2022/No.158, TLN No.6804, jdih.setneg.go.id: 14 hlm., 25 Juli 2022)
- UU No 16 Tahun 2022 tentang Pembentukan Provinsi Papua Pegunungan (LN.2022/No.159, TLN No.6805, jdih.setneg.go.id: 14 hlm., 25 Juli 2022)
- Keputusan Menteri Dalam Negeri Nomor 050-145 Tahun 2022 Tentang Pemberian Kode, Data Wilayah Administrasi Pemerintahan, Dan Pulau Tahun 2021 (Kepmendagri No. 050-145 Tahun 2022, https://www.kemendagri.go.id/arsip/detail/10857/keputusan-menteri-dalam-negeri-nomor-050145-tahun-2022-tentang-pemberian-kode-data-wilayah-administrasi-pemerintahan-dan-pulau-tahun-2021 (Ditetapkan pada tanggal 14 Februari 2022)
- Peraturan Menteri Dalam Negeri Republik Indonesia Nomor 58 Tahun 2021 Tentang Kode, Data Wilayah Administrasi Pemerintahan, Dan Pulau https://paralegal.id/peraturan/peraturan-menteri-dalam-negeri-nomor-58-tahun-2021/ (Permendagri No.58 2021, Ditetapkan pada tanggal 13 Desember 2021,Berita Negara Tahun 2021 Nomor 1391)
- Penetapan Nama, Kode Dan Jumlah Desa Seluruh Indonesia Tahun 2020 (Kepmendagri No. 146.1-4717 - 2020) http://binapemdes.kemendagri.go.id/produkhukum/detil/keputusan-menteri-dalam-negeri-nomor-1461-4717-tahun-2020 (Ditetapkan pada tanggal 21 Desember 2020)
- Kode dan Data Wilayah Administrasi Pemerintahan (Permendagri No.72-2019) https://www.kemendagri.go.id/page/read/48/peraturan-menteri-dalam-negeri-no72-tahun-2019 (Berita Negara Republik Indonesia Tahun 2019 Nomor 1327, Ditetapkan pada tanggal 8 Oktober 2019)
- Kode dan Data Wilayah Administrasi Pemerintahan (Permendagri No.137-2017) http://www.kemendagri.go.id/produk-hukum/2018/01/18/kode-dan-data-wilayah-administrasi-pemerintahan-tahun-2017 (Berita Negara Republik Indonesia Tahun 2017 Nomor 1955, Ditetapkan pada tanggal 27 Desember 2017)
- Kode dan Data Wilayah Administrasi Pemerintahan (Permendagri No.56-2015) www.kemendagri.go.id/pages/data-wilayah (Berita Negara Republik Indonesia Tahun 2015 Nomor 1045, Ditetapkan pada tanggal 29 Juni 2015)

## TODO
- verifikasi/update main web data
- menambahkan API untuk data kode wilayah ke https://api.cahyadsn.com/ (dev)

## CHANGE LOG
- update README.md menambahkan informasi kodefikasi kode wilayah dan kode pulau 2025-11-03
- update main web ke v4.0 (https://wilayah.cahyadsn.com) dan update data sesuai kepmendari No 300.2.2-2138 Th 2025 2025-07-23
- verifikasi dan update data nama wilayah, menghilangkan double spasi 2025-07-17
- update perbaikan nama desa di kab. ACeh Tenggara, Aceh Utara, Mamberamo Raya 2025-07-17
- update data luas wilayah kabupaten Sarmi dan Mamberamo Raya 2025-07-06
- update data luas wilayah kabupaten Teluk Bintuni dan Manokwari Selatan 2025-07-05

[v2025.7]
- update data jumlah penduduk di **db/wilayah_penduduk.sql**, Jumlah penduduk bersumbor dari Ditjen Kependudukan dan Pencatatan Sipil Kemendagri (Data Kependudukan Semester I Bulan DesemberTahun 2024)  2025-07-04
- update data pulau di **db/wilayah_pulau.sql** berdasar dari Sumber data pulau Gazeter Republik Indonesia (GRI) Tahun 2024 yang diterbitkan oleh Badan Informasi Geospasial (BIG) 2025-07-04
- update data luas wilayah di **db/wilayah_level_1_2.sql** berdasar dari Badan Informasi Geospasial berdasarkan Surat Deputi Bidang Informasi Geospasial Dasar Nomor B-16.10/DIGD-BIG/IGD.04.04/12/2024, Tanggal 16 Desember 2024, Hal Penghitungan Luas Wilayah di Seluruh Indonesia 2025-07-04
- menambahkan data luas wilayah Indonesia per propinsi, per kabupaten/kota berdasarkan di **db/wilayah_luas.sql**, data Badan Informasi Geospasial berdasarkan Surat Deputi Bidang Informasi Geospasial Dasar Nomor B-16.10/DIGD-BIG/IGD.04.04/12/2024, Tanggal 16 Desember 2024, Hal Penghitungan Luas Wilayah di Seluruh Indonesia 2025-07-04

## INFORMASI
- data kode wilayah pemerintahan diperoleh dari https://drive.google.com/file/d/1o_m621D00TtwCwQMLn8XUnV3nolamPDm/view?usp=sharing
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
