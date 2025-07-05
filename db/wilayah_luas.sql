/*
BISMILLAAHIRRAHMAANIRRAHIIM - In the Name of Allah, Most Gracious, Most Merciful
================================================================================
filename : db/wilayah.sql
purpose  :
note     : Data Kode Wilayah sesuai Kepmendagri No 300.2.2-2138 Tahun 2025
           Data Luas Wilaah  berdasar dari Badan Informasi Geospasial berdasarkan 
           Surat Deputi Bidang Informasi Geospasial Dasar 
           Nomor B-16.10/DIGD-BIG/IGD.04.04/12/2024, Tanggal 16 Desember 2024, 
           Hal Penghitungan Luas Wilayah di Seluruh Indonesia
create   : 2025-07-04 07:52:21
last edit: 2025-07-05 23:21:45
author   : cahya dsn
================================================================================
This program is free software; you can redistribute it and/or modify it under the
terms of the MIT License.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.

See the MIT License for more details

copyright (c) 2025 by cahya dsn; cahyadsn@gmail.com
================================================================================*/
--
-- Table structure for table wilayah_luas
--
DROP TABLE IF EXISTS wilayah_luas;
CREATE TABLE wilayah_luas (
  kode varchar(13) NOT NULL,
  nama varchar(100) DEFAULT NULL,
  luas double NOT NULL,
  PRIMARY KEY (kode)
);
CREATE INDEX luas_nama_idx ON wilayah_luas(nama);
--
-- Dumping data for table wilayah_luas
--

INSERT INTO wilayah_luas(kode,nama,luas)
VALUES
('11','Aceh',56835.019),
('11.01','Kabupaten Aceh Selatan',4174.211),
('11.02','Kabupaten Aceh Tenggara',4179.123),
('11.03','Kabupaten Aceh Timur',5409.158),
('11.04','Kabupaten Aceh Tengah',4468.417),
('11.05','Kabupaten Aceh Barat',2782.606),
('11.06','Kabupaten Aceh Besar',2891.227),
('11.07','Kabupaten Pidie',3177.063),
('11.08','Kabupaten Aceh Utara',2705.045),
('11.09','Kabupaten Simeulue',1828.851),
('11.1','Kabupaten Aceh Singkil',1851.615),
('11.11','Kabupaten Bireuen',1796.959),
('11.12','Kabupaten Aceh Barat Daya',1882.054),
('11.13','Kabupaten Gayo Lues',5541.285),
('11.14','Kabupaten Aceh Jaya',3870.566),
('11.15','Kabupaten Nagan Raya',3523.283),
('11.16','Kabupaten Aceh Tamiang',2187.817),
('11.17','Kabupaten Bener Meriah',1907.399),
('11.18','Kabupaten Pidie Jaya',938.868),
('11.71','Kota Banda Aceh',56.835),
('11.72','Kota Sabang',121.867),
('11.73','Kota Lhokseumawe',132.966),
('11.74','Kota Langsa',224.203),
('11.75','Kota Subulussalam',1183.601);
INSERT INTO wilayah_luas(kode,nama,luas)
VALUES
('12','Sumatera Utara',72437.755),
('12.01','Kabupaten Tapanuli Tengah',2307.716),
('12.02','Kabupaten Tapanuli Utara',3895.604),
('12.03','Kabupaten Tapanuli Selatan',4201.354),
('12.04','Kabupaten Nias',902.599),
('12.05','Kabupaten Langkat',6142.072),
('12.06','Kabupaten Karo',2206.876),
('12.07','Kabupaten Deli Serdang',2581.482),
('12.08','Kabupaten Simalungun',4601.477),
('12.09','Kabupaten Asahan',3738.42),
('12.1','Kabupaten Labuhanbatu',2772.346),
('12.11','Kabupaten Dairi',2083.604),
('12.12','Kabupaten Toba',2291.616),
('12.13','Kabupaten Mandailing Natal',6546.567),
('12.14','Kabupaten Nias Selatan',2531.59),
('12.15','Kabupaten Pakpak Bharat',1365.607),
('12.16','Kabupaten Humbang Hasundutan',2351.514),
('12.17','Kabupaten Samosir',1850.035),
('12.18','Kabupaten Serdang Bedagai',1949.978),
('12.19','Kabupaten Batu Bara',888.676),
('12.2','Kabupaten Padang Lawas Utara',3914.413),
('12.21','Kabupaten Padang Lawas',3914.413),
('12.22','Kabupaten Labuhanbatu Selatan',3079.61),
('12.23','Kabupaten Labuhanbatu Utara',3687.179),
('12.24','Kabupaten Nias Utara',1239.502),
('12.25','Kabupaten Nias Barat',464.65),
('12.71','Kota Medan',279.239),
('12.72','Kota Pematangsiantar',75.919),
('12.73','Kota Sibolga',12.645),
('12.74','Kota Tanjungbalai',60.072),
('12.75','Kota Binjai',93.77),
('12.76','Kota Tebing Tinggi',39.17),
('12.77','Kota Padangsidimpuan',159.298),
('12.78','Kota Gunungsitoli',208.742);
INSERT INTO wilayah_luas(kode,nama,luas)
VALUES
('13','Sumatera Barat',42107.674),
('13.01','Kabupaten Pesisir Selatan',6045.676),
('13.02','Kabupaten Solok',3590.404),
('13.03','Kabupaten Sijunjung',3150.58),
('13.04','Kabupaten Tanah Datar',1377.186),
('13.05','Kabupaten Padang Pariaman',1341.879),
('13.06','Kabupaten Agam',2225.34),
('13.07','Kabupaten Lima Puluh Kota',3273.405),
('13.08','Kabupaten Pasaman',3902.444),
('13.09','Kabupaten Kepulauan Mentawai',5973.917),
('13.10','Kabupaten Dharmasraya',2920.925),
('13.11','Kabupaten Solok Selatan',3282.144),
('13.12','Kabupaten Pasaman Barat',3851.883),
('13.71','Kota Padang',694.315),
('13.72','Kota Solok',58.72),
('13.73','Kota Sawahlunto',231.945),
('13.74','Kota Padang Panjang',23.56),
('13.75','Kota Bukittinggi',24.173),
('13.76','Kota Payakumbuh',74.552),
('13.77','Kota Pariaman',64.626);
INSERT INTO wilayah_luas(kode,nama,luas)
VALUES
('14','Riau',89900.78),
('14.01','Kabupaten Kampar',10352.803),
('14.02','Kabupaten Indragiri Hulu',7871.85),
('14.03','Kabupaten Bengkalis',8604.179),
('14.04','Kabupaten Indragiri Hilir',13518.47),
('14.05','Kabupaten  Pelalawan',13256.104),
('14.06','Kabupaten  Rokan Hulu',7658.149),
('14.07','Kabupaten  Rokan Hilir',9068.605),
('14.08','Kabupaten Siak',7803.566),
('14.09','Kabupaten Kuantan Singingi',5457.863),
('14.1','Kabupaten Kepulauan Meranti',3609.397),
('14.71','Kota Pekanbaru',638.331),
('14.72','Kota Dumai',2061.463);
INSERT INTO wilayah_luas(kode,nama,luas)
VALUES
('15','Jambi',49023.037),
('15.01','Kabupaten  Kerinci',3445.196),
('15.02','Kabupaten  Merangin',7540.118),
('15.03','Kabupaten Sarolangun',5935.894),
('15.04','Kabupaten Batanghari',5453.689),
('15.05','Kabupaten  Muaro Jambi',5159.623),
('15.06','Kabupaten Tanjung Jabung Barat',5544.567),
('15.07','Kabupaten Tanjung Jabung Timur',4544.575),
('15.08','Kabupaten Bungo',4760.827),
('15.09','Kabupaten Tebo',6103.737),
('15.71','Kota Jambi',169.887),
('15.72','Kota Sungai Penuh',364.924);
INSERT INTO wilayah_luas(kode,nama,luas)
VALUES
('16','Sumatera Selatan',86771.918),
('16.01','Kabupaten Ogan Komering Ulu',3774.497),
('16.02','Kabupaten Ogan Komering Ilir',17075.713),
('16.03','Kabupaten Muara Enim',6763.912),
('16.04','Kabupaten Lahat',4333.065),
('16.05','Kabupaten Musi Rawas',6122.588),
('16.06','Kabupaten Musi Banyuasin',14550.788),
('16.07','Kabupaten Banyuasin',12258.593),
('16.08','Kabupaten Ogan Komering Ulu Timur',3412.716),
('16.09','Kabupaten Ogan Komering Ulu Selatan',4369.252),
('16.10','Kabupaten Ogan Ilir',2302.858),
('16.11','Kabupaten Empat Lawang',2234.097),
('16.12','Kabupaten Penukal Abab Lematang Ilir',1842.563),
('16.13','Kabupaten Musi Rawas Utara',5937.803),
('16.71','Kota Palembang',352.523),
('16.72','Kota Pagar Alam',625.913),
('16.73','Kota Lubuk Linggau',367.726),
('16.74','Kota Prabumulih',447.311);
INSERT INTO wilayah_luas(kode,nama,luas)
VALUES
('17','Bengkulu',20122.21),
('17.01','Kabupaten Bengkulu Selatan',1219.063),
('17.02','Kabupaten Rejang Lebong',1559.419),
('17.03','Kabupaten Bengkulu Utara',4478.107),
('17.04','Kabupaten Kaur',2607.839),
('17.05','Kabupaten Seluma',2432.088),
('17.06','Kabupaten Mukomuko',4137.52),
('17.07','Kabupaten Lebong',1666.621),
('17.08','Kabupaten Kepahiang',738.965),
('17.09','Kabupaten Bengkulu Tengah',1132.405),
('17.71','Kota Bengkulu',150.183);
INSERT INTO wilayah_luas(kode,nama,luas)
VALUES
('18','Lampung',33570.758),
('18.01','Kabupaten Lampung Selatan',2227.357),
('18.02','Kabupaten Lampung Tengah',4559.57),
('18.03','Kabupaten Lampung Utara',2669.304),
('18.04','Kabupaten Lampung Barat',2110.101),
('18.05','Kabupaten Tulang Bawang',3118.613),
('18.06','Kabupaten Tanggamus',2947.187),
('18.07','Kabupaten Lampung Timur',3860.546),
('18.08','Kabupaten Way Kanan',3522.114),
('18.09','Kabupaten Pesawaran',1286.843),
('18.10','Kabupaten Pringsewu',617.192),
('18.11','Kabupaten Mesuji',2200.414),
('18.12','Kabupaten Tulang Bawang Barat',1257.088),
('18.13','Kabupaten Pesisir Barat',2937.496),
('18.71','Kota Bandar Lampung',183.719),
('18.72','Kota Metro',73.214);
INSERT INTO wilayah_luas(kode,nama,luas)
VALUES
('19','Kepulauan Bangka Belitung',16670.225),
('19.01','Kabupaten Bangka',3011.435),
('19.02','Kabupaten Belitung',2266.107),
('19.03','Kabupaten Bangka Selatan',3595.446),
('19.04','Kabupaten Bangka Tengah',2253.854),
('19.05','Kabupaten Bangka Barat',2853.411),
('19.06','Kabupaten Belitung Timur',2585.734),
('19.71','Kota Pangkal Pinang',104.238);
INSERT INTO wilayah_luas(kode,nama,luas)
VALUES
('21','Kepulauan Riau',8170.375),
('21.01','Kabupaten Bintan',1311.868),
('21.02','Kabupaten Karimun',916.331),
('21.03','Kabupaten Natuna',1978.01),
('21.04','Kabupaten Lingga',2173.63),
('21.05','Kabupaten Kepulauan Anambas',620.622),
('21.71','Kota Batam',1020.28),
('21.72','Kota Tanjung Pinang',149.634);
INSERT INTO wilayah_luas(kode,nama,luas)
VALUES
('31.0','Daerah Khusus Ibukota Jakarta',661.53),
('31.01','Kabupaten Administrasi Kepulauan Seribu',10.951),
('31.71','Kota Administrasi Jakarta Pusat',47.565),
('31.72','Kota Administrasi Jakarta Utara',147.534),
('31.73','Kota Administrasi Jakarta Barat',125.0),
('31.74','Kota Administrasi Jakarta Selatan',144.942),
('31.75','Kota Administrasi Jakarta Timur',185.538);
INSERT INTO wilayah_luas(kode,nama,luas)
VALUES
('32','Jawa Barat',37053.331),
('32.01','Kabupaten Bogor',2991.778),
('32.02','Kabupaten Sukabumi',4163.824),
('32.03','Kabupaten Cianjur',3631.969),
('32.04','Kabupaten Bandung',1740.843),
('32.05','Kabupaten Garut',3101.132),
('32.06','Kabupaten Tasikmalaya',2706.972),
('32.07','Kabupaten Ciamis',1595.936),
('32.08','Kabupaten Kuningan',1193.449),
('32.09','Kabupaten Cirebon',1073.772),
('32.10','Kabupaten Majalengka',1331.553),
('32.11','Kabupaten Sumedang',1566.204),
('32.12','Kabupaten Indramayu',2076.266),
('32.13','Kabupaten Subang',2168.909),
('32.14','Kabupaten Purwakarta',993.092),
('32.15','Kabupaten Karawang',1916.056),
('32.16','Kabupaten Bekasi',1255.001),
('32.17','Kabupaten Bandung Barat',1283.439),
('32.18','Kabupaten Pangandaran',1128.122),
('32.71','Kota Bogor',111.366),
('32.72','Kota Sukabumi',48.314),
('32.73','Kota Bandung',166.593),
('32.74','Kota Cirebon',39.398),
('32.75','Kota Bekasi',213.036),
('32.76','Kota Depok',199.906),
('32.77','Kota Cimahi',42.432),
('32.78','Kota Tasikmalaya',182.964),
('32.79','Kota Banjar',131.005);
INSERT INTO wilayah_luas(kode,nama,luas)
VALUES
('33','Jawa Tengah',34347.428),
('33.01','Kabupaten Cilacap',2325.173),
('33.02','Kabupaten Banyumas',1391.153),
('33.03','Kabupaten Purbalingga',805.757),
('33.04','Kabupaten Banjarnegara',1144.896),
('33.05','Kabupaten Kebumen',1333.902),
('33.06','Kabupaten Purworejo',1081.763),
('33.07','Kabupaten Wonosobo',1011.623),
('33.08','Kabupaten Magelang',1129.983),
('33.09','Kabupaten Boyolali',1096.613),
('33.10','Kabupaten Klaten',701.511),
('33.11','Kabupaten Sukoharjo',493.601),
('33.12','Kabupaten Wonogiri',1905.823),
('33.13','Kabupaten Karanganyar',802.848),
('33.14','Kabupaten Sragen',994.573),
('33.15','Kabupaten Grobogan',2023.849),
('33.16','Kabupaten Blora',1957.289),
('33.17','Kabupaten Rembang',1038.32),
('33.18','Kabupaten Pati',1576.69),
('33.19','Kabupaten Kudus',447.445),
('33.20','Kabupaten Jepara',1020.124),
('33.21','Kabupaten Demak',970.077),
('33.22','Kabupaten Semarang',1019.27),
('33.23','Kabupaten Temanggung',864.834),
('33.24','Kabupaten Kendal',1006.174),
('33.25','Kabupaten Batang',857.073),
('33.26','Kabupaten Pekalongan',892.578),
('33.27','Kabupaten Pemalang',1135.757),
('33.28','Kabupaten Tegal',983.713),
('33.29','Kabupaten Brebes',1754.77),
('33.71','Kota Magelang',18.558),
('33.72','Kota Surakarta',46.802),
('33.73','Kota Salatiga',54.983),
('33.74','Kota Semarang',374.747),
('33.75','Kota Pekalongan',46.076),
('33.76','Kota Tegal',39.08);
INSERT INTO wilayah_luas(kode,nama,luas)
VALUES
('34','Daerah Istimewa Yogyakarta',3170.363),
('34.01','Kabupaten Kulon Progo',576.803),
('34.02','Kabupaten Bantul',511.527),
('34.03','Kabupaten Gunungkidul',1475.465),
('34.04','Kabupaten Sleman',573.749),
('34.71','Kota Yogyakarta',32.819);
INSERT INTO wilayah_luas(kode,nama,luas)
VALUES
('35','Jawa Timur',48055.876),
('35.01','Kabupaten Pacitan',1434.818),
('35.02','Kabupaten Ponorogo',1418.618),
('35.03','Kabupaten Trenggalek',1249.367),
('35.04','Kabupaten Tulungagung',1144.856),
('35.05','Kabupaten Blitar',1745.383),
('35.06','Kabupaten Kediri',1523.563),
('35.07','Kabupaten Malang',3473.747),
('35.08','Kabupaten Lumajang',1796.489),
('35.09','Kabupaten Jember',3314.358),
('35.10','Kabupaten Banyuwangi',3593.849),
('35.11','Kabupaten Bondowoso',1554.992),
('35.12','Kabupaten Situbondo',1654.705),
('35.13','Kabupaten Probolinggo',1725.171),
('35.14','Kabupaten Pasuruan',1493.161),
('35.15','Kabupaten Sidoarjo',724.766),
('35.16','Kabupaten Mojokerto',984.38),
('35.17','Kabupaten Jombang',1109.835),
('35.18','Kabupaten Nganjuk',1289.067),
('35.19','Kabupaten Madiun',1113.627),
('35.20','Kabupaten Magetan',706.444),
('35.21','Kabupaten Ngawi',1395.804),
('35.22','Kabupaten Bojonegoro',2312.627),
('35.23','Kabupaten Tuban',1973.515),
('35.24','Kabupaten Lamongan',1752.989),
('35.25','Kabupaten Gresik',1264.009),
('35.26','Kabupaten Bangkalan',1302.64),
('35.27','Kabupaten Sampang',1229.165),
('35.28','Kabupaten Pamekasan',796.241),
('35.29','Kabupaten Sumenep',2083.048),
('35.71','Kota Kediri',67.232),
('35.72','Kota Blitar',33.203),
('35.73','Kota Malang',111.077),
('35.74','Kota Probolinggo',55.197),
('35.75','Kota Pasuruan',39.109),
('35.76','Kota Mojokerto',20.48),
('35.77','Kota Madiun',36.126),
('35.78','Kota Surabaya',338.048),
('35.79','Kota Batu',194.17);
INSERT INTO wilayah_luas(kode,nama,luas)
VALUES
('36','Banten',9355.763),
('36.01','Kabupaten Pandeglang',2771.488),
('36.02','Kabupaten Lebak',3312.298),
('36.03','Kabupaten Tangerang',1028.335),
('36.04','Kabupaten Serang',1471.543),
('36.71','Kota Tangerang',178.347),
('36.72','Kota Cilegon',162.578),
('36.73','Kota Serang',266.314),
('36.74','Kota Tangerang Selatan',164.86);
INSERT INTO wilayah_luas(kode,nama,luas)
VALUES
('51','Bali',5582.827),
('51.01','Kabupaten Jembrana',847.515),
('51.02','Kabupaten Tabanan',848.539),
('51.03','Kabupaten Badung',397.395),
('51.04','Kabupaten Gianyar',363.782),
('51.05','Kabupaten Klungkung',312.115),
('51.06','Kabupaten Bangli',526.764),
('51.07','Kabupaten Karangasem',838.899),
('51.08','Kabupaten Buleleng',1322.124),
('51.71','Kota Denpasar',125.694);
INSERT INTO wilayah_luas(kode,nama,luas)
VALUES
('52','Nusa Tenggara Barat',19631.991),
('52.01','Kabupaten Lombok Barat',919.44),
('52.02','Kabupaten Lombok Tengah',1167.029),
('52.03','Kabupaten Lombok Timur',1602.377),
('52.04','Kabupaten Sumbawa',6645.723),
('52.05','Kabupaten Dompu',2270.36),
('52.06','Kabupaten Bima',4208.861),
('52.07','Kabupaten Sumbawa Barat',1739.564),
('52.08','Kabupaten Lombok Utara',810.185),
('52.71','Kota Mataram',60.063),
('52.72','Kota Bima',208.389);
INSERT INTO wilayah_luas(kode,nama,luas)
VALUES
('53','Nusa Tenggara Timur',46378.105),
('53.01','Kabupaten Kupang',5130.659),
('53.02','Kab Timor Tengah Selatan',3931.747),
('53.03','Kabupaten Timor Tengah Utara',2622.293),
('53.04','Kabupaten Belu',1126.77),
('53.05','Kabupaten Alor',2918.753),
('53.06','Kabupaten Flores Timur',1741.064),
('53.07','Kabupaten Sikka',1669.713),
('53.08','Kabupaten Ende',2084.076),
('53.09','Kabupaten Ngada',1736.735),
('53.10','Kabupaten Manggarai',1343.335),
('53.11','Kabupaten Sumba Timur',6975.198),
('53.12','Kabupaten Sumba Barat',755.836),
('53.13','Kabupaten Lembata',1263.767),
('53.14','Kabupaten Rote Ndao',1277.256),
('53.15','Kabupaten Manggarai Barat',3120.028),
('53.16','Kabupaten Nagekeo',1397.969),
('53.17','Kabupaten Sumba Tengah',1786.436),
('53.18','Kabupaten Sumba Barat Daya',1381.943),
('53.19','Kabupaten Manggarai Timur',2389.499),
('53.20','Kabupaten Sabu Raijua',457.14),
('53.21','Kabupaten Malaka',1109.003),
('53.71','Kota Kupang',158.885);
INSERT INTO wilayah_luas(kode,nama,luas)
VALUES
('61','Kalimantan Barat',147018.063),
('61.01','Kabupaten Sambas',5928.221),
('61.02','Kabupaten Mempawah',1934.827),
('61.03','Kabupaten Sanggau',12452.224),
('61.04','Kabupaten Ketapang',30012.023),
('61.05','Kabupaten Sintang',22025.788),
('61.06','Kabupaten Kapuas Hulu',31318.246),
('61.07','Kabupaten Bengkayang',5488.322),
('61.08','Kabupaten Landak',8430.711),
('61.09','Kabupaten Sekadau',5979.044),
('61.10','Kabupaten Melawi',10122.513),
('61.11','Kabupaten Kayong Utara',4108.533),
('61.12','Kabupaten Kubu Raya',8549.057),
('61.71','Kota Pontianak',118.209),
('61.72','Kota Singkawang',550.345);
INSERT INTO wilayah_luas(kode,nama,luas)
VALUES
('62','Kalimantan Tengah',153430.363),
('62.01','Kabupaten Kotawaringin Barat',9475.854),
('62.02','Kabupaten Kotawaringin Timur',15541.88),
('62.03','Kabupaten Kapuas',17033.54),
('62.04','Kabupaten Barito Selatan',6267.084),
('62.05','Kabupaten Barito Utara',9984.808),
('62.06','Kabupaten Katingan',20380.503),
('62.07','Kabupaten Seruyan',15211.714),
('62.08','Kabupaten Sukamara',3310.13),
('62.09','Kabupaten Lamandau',7632.394),
('62.10','Kabupaten Gunung Mas',9305.756),
('62.11','Kabupaten Pulang Pisau',9650.158),
('62.12','Kabupaten Murung Raya',23575.328),
('62.13','Kabupaten Barito Timur',3212.515),
('62.71','Kota Palangkaraya',2848.699);
INSERT INTO wilayah_luas(kode,nama,luas)
VALUES
('63','Kalimantan Selatan',37125.426),
('63.01','Kabupaten Tanah Laut',3841.157),
('63.02','Kabupaten Kotabaru',9345.91),
('63.03','Kabupaten Banjar',4588.37),
('63.04','Kabupaten Barito Kuala',2425.829),
('63.05','Kabupaten Tapin',2155.939),
('63.06','Kabupaten Hulu Sungai Selatan',1697.224),
('63.07','Kabupaten Hulu Sungai Tengah',1573.538),
('63.08','Kabupaten Hulu Sungai Utara',907.491),
('63.09','Kabupaten Tabalong',3473.069),
('63.10','Kabupaten Tanah Bumbu',4884.861),
('63.11','Kabupaten Balangan',1828.513),
('63.71','Kota Banjarmasin',98.372),
('63.72','Kota Banjarbaru',305.153);
INSERT INTO wilayah_luas(kode,nama,luas)
VALUES
('64','Kalimantan Timur',126951.758),
('64.01','Kabupaten Paser',10780.677),
('64.02','Kabupaten Kutai Kartanegara',26912.065),
('64.03','Kabupaten Berau',20999.287),
('64.07','Kabupaten Kutai Barat',13611.168),
('64.08','Kabupaten Kutai Timur',31572.691),
('64.09','Kabupaten Penajam Paser Utara',3197.199),
('64.11','Kabupaten Mahakam Ulu',18492.238),
('64.71','Kota Balikpapan',509.45),
('64.72','Kota Samarinda',716.783),
('64.74','Kota Bontang',160.2);
INSERT INTO wilayah_luas(kode,nama,luas)
VALUES
('65','Kalimantan Utara',69900.886),
('65.01','Kabupaten Bulungan',13719.983),
('65.02','Kabupaten Malinau',38901.819),
('65.03','Kabupaten Nunukan',13549.868),
('65.04','Kabupaten Tana Tidung',3481.039),
('65.71','Kota Tarakan',248.177);
INSERT INTO wilayah_luas(kode,nama,luas)
VALUES
('71','Sulawesi Utara',14488.429),
('71.01','Kabupaten Bolaang Mongondow',3291.237),
('71.02','Kabupaten Minahasa',1128.25),
('71.03','Kabupaten Kepulauan Sangihe',596.459),
('71.04','Kabupaten Kepulauan Talaud',1009.738),
('71.05','Kabupaten Minahasa Selatan',1455.907),
('71.06','Kabupaten Minahasa Utara',992.981),
('71.07','Kabupaten Minahasa Tenggara',752.788),
('71.08','Kabupaten Bolaang Mongondow Utara',1643.863),
('71.09','Kabupaten Kep. Siau Tagulandang Biaro',215.611),
('71.10','Kabupaten Bolaang Mongondow Timur',859.683),
('71.11','Kabupaten Bolaang Mongondow Selatan',1772.832),
('71.71','Kota Manado',161.76),
('71.72','Kota Bitung',329.363),
('71.73','Kota Tomohon',169.064),
('71.74','Kota Kotamobagu',108.893);
INSERT INTO wilayah_luas(kode,nama,luas)
VALUES
('72','Sulawesi Tengah',61496.983),
('72.01','Kabupaten Banggai',8241.143),
('72.02','Kabupaten Poso',7544.965),
('72.03','Kabupaten Donggala',5122.736),
('72.04','Kabupaten Toli-Toli',3695.945),
('72.05','Kabupaten Buol',3719.205),
('72.06','Kabupaten Morowali',4433.535),
('72.07','Kabupaten Banggai Kepulauan',2378.048),
('72.08','Kabupaten Parigi Moutong',5800.059),
('72.09','Kabupaten Tojo Una Una',5565.953),
('72.10','Kabupaten Sigi',5225.435),
('72.11','Kabupaten Banggai Laut',680.306),
('72.12','Kabupaten Morowali Utara',8733.733),
('72.71','Kota Palu',355.92);
INSERT INTO wilayah_luas(kode,nama,luas)
VALUES
('73','Sulawesi Selatan',45323.975),
('73.01','Kabupaten Kepulauan Selayar',1163.383),
('73.02','Kabupaten Bulukumba',1175.562),
('73.03','Kabupaten Bantaeng',390.95),
('73.04','Kabupaten Jeneponto',795.131),
('73.05','Kabupaten Takalar',553.206),
('73.06','Kabupaten Gowa',1812.988),
('73.07','Kabupaten Sinjai',865.695),
('73.08','Kabupaten Bone',4568.033),
('73.09','Kabupaten Maros',1443.788),
('73.10','Kabupaten Pangkajene dan Kepulauan',883.553),
('73.11','Kabupaten Barru',1201.322),
('73.12','Kabupaten Soppeng',1385.546),
('73.13','Kabupaten Wajo',2505.492),
('73.14','Kabupaten Sidenreng Rappang',1935.326),
('73.15','Kabupaten Pinrang',1895.008),
('73.16','Kabupaten Enrekang',1806.816),
('73.17','Kabupaten Luwu',2901.696),
('73.18','Kabupaten Tana Toraja',2043.623),
('73.22','Kabupaten Luwu Utara',7422.257),
('73.24','Kabupaten Luwu Timur',6745.772),
('73.26','Kabupaten Toraja Utara',1289.134),
('73.71','Kota Makassar',177.174),
('73.72','Kota Parepare',89.626),
('73.73','Kota Palopo',272.894);
INSERT INTO wilayah_luas(kode,nama,luas)
VALUES
('74','Sulawesi Tenggara',36139.303),
('74.01','Kabupaten Kolaka',2958.87),
('74.02','Kabupaten Konawe',5352.922),
('74.03','Kabupaten Muna',1856.91),
('74.04','Kabupaten Buton',1664.982),
('74.05','Kabupaten Konawe Selatan',4234.214),
('74.06','Kabupaten Bombana',3291.922),
('74.07','Kabupaten Wakatobi',450.8),
('74.08','Kabupaten Kolaka Utara',2931.677),
('74.09','Kabupaten Konawe Utara',4219.214),
('74.10','Kabupaten Buton Utara',1752.37),
('74.11','Kabupaten Kolaka Timur',3992.531),
('74.12','Kabupaten Konawe Kepulauan',704.184),
('74.13','Kabupaten Muna Barat',816.717),
('74.14','Kabupaten Buton Tengah',835.562),
('74.15','Kabupaten Buton Selatan',516.479),
('74.71','Kota Kendari',265.845),
('74.72','Kota Bau Bau',294.104);
INSERT INTO wilayah_luas(kode,nama,luas)
VALUES
('75','Gorontalo',12024.982),
('75.01','Kabupaten Gorontalo',2160.183),
('75.02','Kabupaten Boalemo',1831.637),
('75.03','Kabupaten Bone Bolango',1888.935),
('75.04','Kabupaten Pohuwato',4370.656),
('75.05','Kabupaten Gorontalo Utara',1702.746),
('75.71','Kota Gorontalo',70.825);
INSERT INTO wilayah_luas(kode,nama,luas)
VALUES
('76','Sulawesi Barat',16590.667),
('76.01','Kabupaten Pasangkayu',2901.704),
('76.02','Kabupaten Mamuju',4942.041),
('76.03','Kabupaten Mamasa',3016.13),
('76.04','Kabupaten Polewali Mandar',2074.191),
('76.05','Kabupaten Majene',900.525),
('76.06','Kabupaten Mamuju Tengah',2756.076);
INSERT INTO wilayah_luas(kode,nama,luas)
VALUES
('81','Maluku',46133.832),
('81.01','Kabupaten Maluku Tengah',8253.421),
('81.02','Kabupaten Maluku Tenggara',1016.082),
('81.03','Kabupaten Kepulauan Tanimbar',4427.811),
('81.04','Kabupaten Buru',4914.066),
('81.05','Kabupaten Seram Bagian Timur',5725.513),
('81.06','Kabupaten Seram Bagian Barat',5014.238),
('81.07','Kabupaten Kepulauan Aru',8090.792),
('81.08','Kabupaten Maluku Barat Daya',4542.633),
('81.09','Kabupaten Buru Selatan',3677.854),
('81.71','Kota Ambon',236.713),
('81.72','Kota Tual',234.709);
INSERT INTO wilayah_luas(kode,nama,luas)
VALUES
('82','Maluku Utara',31465.977),
('82.01','Kabupaten Halmahera Barat',2237.391),
('82.02','Kabupaten Halmahera Tengah',2272.261),
('82.03','Kabupaten Halmahera Utara',3402.937),
('82.04','Kabupaten Halmahera Selatan',8102.0),
('82.05','Kabupaten Kepulauan Sula',1777.021),
('82.06','Kabupaten Halmahera Timur',6487.633),
('82.07','Kabupaten Pulau Morotai',2337.256),
('82.08','Kabupaten Pulau Taliabu',2984.011),
('82.71','Kota Ternate',162.04),
('82.72','Kota Tidore Kepulauan',1703.427);
INSERT INTO wilayah_luas(kode,nama,luas)
VALUES
('91','Papua',81383.315),
('91.03','Kabupaten Jayapura',14081.935),
('91.05','Kabupaten Kepulauan Yapen',2425.374),
('91.06','Kabupaten Biak Numfor',2258.863),
('91.1','Kabupaten Sarmi',14068.312),
('91.11','Kabupaten Keerom',9526.315),
('91.15','Kabupaten Waropen',10781.037),
('91.19','Kabupaten Supiori',660.681),
('91.2','Kabupaten Mamberamo Raya',26745.359),
('91.71','Kota Jayapura',835.439);
INSERT INTO wilayah_luas(kode,nama,luas)
VALUES
('92','Papua Barat',60308.59),
('92.02','Kabupaten Manokwari',2764.187),
('92.03','Kabupaten Fak Fak',9735.003),
('92.06','Kabupaten Teluk Bintuni',19946.579),
('92.07','Kabupaten Teluk Wondama',4847.951),
('92.08','Kabupaten Kaimana',17878.913),
('92.11','Kabupaten Manokwari Selatan',1837.146),
('92.12','Kabupaten Pegunungan Arfak',3298.811);
INSERT INTO wilayah_luas(kode,nama,luas)
VALUES
('93','Papua Selatan',117858.969),
('93.01','Kabupaten Merauke',45016.452),
('93.02','Kabupaten Boven Digoel',23558.272),
('93.03','Kabupaten Mappi',24265.584),
('93.04','Kabupaten Asmat',25018.661);
INSERT INTO wilayah_luas(kode,nama,luas)
VALUES
('94','Papua Tengah',61079.587),
('94.01','Kabupaten Nabire',11801.987),
('94.02','Kabupaten Puncak Jaya',5986.19),
('94.03','Kabupaten Paniai',5306.871),
('94.04','Kabupaten Mimika',18309.723),
('94.05','Kabupaten Puncak',7701.031),
('94.06','Kabupaten Dogiyai',3792.928),
('94.07','Kabupaten Intan Jaya',5334.445),
('94.08','Kabupaten Deiyai',2846.412);
INSERT INTO wilayah_luas(kode,nama,luas)
VALUES
('95','Papua Pegunungan',52508.656),
('95.01','Kabupaten Jayawijaya',2629.01),
('95.02','Kab Pegunungan Bintang',13751.915),
('95.03','Kabupaten Yahukimo',16365.938),
('95.04','Kabupaten Tolikara',4285.333),
('95.05','Kabupaten Mamberamo Tengah',4101.486),
('95.06','Kabupaten Yalimo',3148.308),
('95.07','Kabupaten Lanny Jaya',2339.775),
('95.08','Kabupaten Nduga',5886.891);
INSERT INTO wilayah_luas(kode,nama,luas)
VALUES
('96','Papua Barat Daya',39103.058),
('96.01','Kabupaten Sorong',7565.039),
('96.02','Kabupaten Sorong Selatan',6559.597),
('96.03','Kabupaten Raja Ampat',7425.239),
('96.04','Kabupaten Tambrauw',11961.745),
('96.05','Kabupaten Maybrat',5385.679),
('96.71','Kota Sorong',205.759);
