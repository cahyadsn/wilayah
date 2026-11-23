<?php
/*
BISMILLAAHIRRAHMAANIRRAHIIM - In the Name of Allah, Most Gracious, Most Merciful
================================================================================
filename : build_timeline_snapshot.php
purpose  : Compile historical regional evolution timeline snapshots (2016-2025)
create   : 2026-11-23
last edit: 2026-11-23 14:30:00
author   : cahya dsn
================================================================================
MIT License
copyright (c) 2017-2026 by cahya dsn; cahyadsn@gmail.com
================================================================================*/

declare(strict_types=1);

$rootDir = dirname(__DIR__);
$archiveDir = $rootDir . '/db/archive';
$dbDir = $rootDir . '/db';
$cacheDir = $rootDir . '/apps/cache';

if (!is_dir($cacheDir)) {
    @mkdir($cacheDir, 0755, true);
}

echo "Building timeline snapshots...\n";

function countSqlFileEntities(string $filePath): array
{
    $counts = [
        'total_provinsi' => 0,
        'total_kab_kota' => 0,
        'total_kab'      => 0,
        'total_kota'     => 0,
        'total_kecamatan'=> 0,
        'total_desa_kel' => 0,
        'total_desa'     => 0,
        'total_kelurahan'=> 0,
        'provinces'      => []
    ];

    if (!file_exists($filePath)) {
        return $counts;
    }

    $handle = fopen($filePath, 'r');
    if (!$handle) {
        return $counts;
    }

    while (($line = fgets($handle)) !== false) {
        $trimmed = trim($line);
        if (preg_match('/^\(["\']([0-9]+(?:\.[0-9]+)*)["\'],\s*["\'](.*?)["\']\)/', $trimmed, $m)) {
            $kode = $m[1];
            $nama = $m[2];
            $len = strlen($kode);

            if ($len === 2) {
                $counts['total_provinsi']++;
                $counts['provinces'][] = ['kode' => $kode, 'nama' => $nama];
            } elseif ($len === 5) {
                $counts['total_kab_kota']++;
                $part = substr($kode, 3, 2);
                if ((int)$part >= 70 || stripos($nama, 'kota') !== false) {
                    $counts['total_kota']++;
                } else {
                    $counts['total_kab']++;
                }
            } elseif ($len === 8) {
                $counts['total_kecamatan']++;
            } elseif ($len === 13) {
                $counts['total_desa_kel']++;
                $sub = substr($kode, 9, 1);
                if ($sub === '1') {
                    $counts['total_kelurahan']++;
                } else {
                    $counts['total_desa']++;
                }
            }
        }
    }

    fclose($handle);
    return $counts;
}

function countIslandSqlFile(string $filePath): int
{
    if (!file_exists($filePath)) {
        return 0;
    }
    $cnt = 0;
    $handle = fopen($filePath, 'r');
    if (!$handle) {
        return 0;
    }
    while (($line = fgets($handle)) !== false) {
        if (preg_match('/^\(["\'][0-9]{2}\.[0-9]{2}\.[0-9]{5}["\']/', trim($line))) {
            $cnt++;
        }
    }
    fclose($handle);
    return $cnt;
}

// 1. Snapshot 2016
echo "Processing 2016...\n";
$s2016 = countSqlFileEntities($archiveDir . '/wilayah_2016.sql');

// 2. Snapshot 2018
echo "Processing 2018...\n";
$s2018 = countSqlFileEntities($archiveDir . '/wilayah_2018.sql');

// 3. Snapshot 2020
echo "Processing 2020...\n";
$s2020 = countSqlFileEntities($archiveDir . '/wilayah_2020.sql');

// 4. Snapshot 2022
echo "Processing 2022...\n";
$s2022 = countSqlFileEntities($archiveDir . '/wilayah_2022.sql');
$p2022 = countIslandSqlFile($archiveDir . '/pulau_2022.sql');

// 5. Snapshot 2023
echo "Processing 2023...\n";
$s2023 = countSqlFileEntities($archiveDir . '/wilayah_2023.sql');
$p2023 = countIslandSqlFile($archiveDir . '/pulau_2023.sql');

// 6. Snapshot 2025 (current)
echo "Processing 2025...\n";
$s2025 = countSqlFileEntities($dbDir . '/wilayah.sql');
$p2025 = countIslandSqlFile($dbDir . '/wilayah_pulau.sql');

// Extract pre-2023 Papua boundaries from wilayah_level_1_2-2022.sql for historical map rendering
echo "Extracting 2022 historical Papua polygons...\n";
$papua2022Path = null;
$papuaBarat2022Path = null;
$fileLevel2022 = $archiveDir . '/wilayah_level_1_2-2022.sql';
if (file_exists($fileLevel2022)) {
    $handle = fopen($fileLevel2022, 'r');
    if ($handle) {
        while (($line = fgets($handle)) !== false) {
            if (strpos($line, "('91','PAPUA'") !== false) {
                if (preg_match("/'(\[\[\[.*?\]\]\])'/s", $line, $pm)) {
                    $papua2022Path = $pm[1];
                }
            } elseif (strpos($line, "('92','PAPUA BARAT'") !== false) {
                if (preg_match("/'(\[\[\[.*?\]\]\])'/s", $line, $pm)) {
                    $papuaBarat2022Path = $pm[1];
                }
            }
        }
        fclose($handle);
    }
}

$snapshots = [
    [
        'year' => 2016,
        'regulasi' => 'Permendagri No. 56 Tahun 2015',
        'short_title' => 'Permendagri 56/2015',
        'tanggal_penetapan' => '2015-08-04',
        'total_provinsi' => $s2016['total_provinsi'] ?: 34,
        'total_kab_kota' => $s2016['total_kab_kota'] ?: 514,
        'total_kab'      => $s2016['total_kab'] ?: 416,
        'total_kota'     => $s2016['total_kota'] ?: 98,
        'total_kecamatan'=> $s2016['total_kecamatan'] ?: 7094,
        'total_desa_kel' => $s2016['total_desa_kel'] ?: 82030,
        'total_desa'     => $s2016['total_desa'] ?: 73709,
        'total_kelurahan'=> $s2016['total_kelurahan'] ?: 8321,
        'total_pulau'    => 13466,
        'highlights'     => 'Rujukan awal era pasca-reformasi dan pemekaran Kalimantan Utara. Terdiri dari 34 Provinsi dan 514 Kabupaten/Kota.',
        'doc_url'        => 'https://github.com/cahyadsn/wilayah_ref/blob/master/Permendagri%20No%2056%20Tahun%202015.pdf',
        'has_dob'        => false,
        'new_regions'    => [],
        'provinces'      => $s2016['provinces']
    ],
    [
        'year' => 2018,
        'regulasi' => 'Permendagri No. 137 Tahun 2017',
        'short_title' => 'Permendagri 137/2017',
        'tanggal_penetapan' => '2017-12-27',
        'total_provinsi' => $s2018['total_provinsi'] ?: 34,
        'total_kab_kota' => $s2018['total_kab_kota'] ?: 514,
        'total_kab'      => $s2018['total_kab'] ?: 416,
        'total_kota'     => $s2018['total_kota'] ?: 98,
        'total_kecamatan'=> $s2018['total_kecamatan'] ?: 7201,
        'total_desa_kel' => $s2018['total_desa_kel'] ?: 83344,
        'total_desa'     => $s2018['total_desa'] ?: 74957,
        'total_kelurahan'=> $s2018['total_kelurahan'] ?: 8387,
        'total_pulau'    => 16056,
        'highlights'     => 'Standardisasi nama rupabumi dan kodifikasi wilayah administrasi nasional rujukan Permendagri 137/2017.',
        'doc_url'        => 'https://github.com/cahyadsn/wilayah_ref/blob/master/Permendagri%20No%20137%20Tahun%202017.pdf',
        'has_dob'        => false,
        'new_regions'    => [],
        'provinces'      => $s2018['provinces']
    ],
    [
        'year' => 2020,
        'regulasi' => 'Permendagri No. 72 Tahun 2019',
        'short_title' => 'Permendagri 72/2019',
        'tanggal_penetapan' => '2019-10-25',
        'total_provinsi' => $s2020['total_provinsi'] ?: 34,
        'total_kab_kota' => $s2020['total_kab_kota'] ?: 514,
        'total_kab'      => $s2020['total_kab'] ?: 416,
        'total_kota'     => $s2020['total_kota'] ?: 98,
        'total_kecamatan'=> $s2020['total_kecamatan'] ?: 7230,
        'total_desa_kel' => $s2020['total_desa_kel'] ?: 83441,
        'total_desa'     => $s2020['total_desa'] ?: 75005,
        'total_kelurahan'=> $s2020['total_kelurahan'] ?: 8436,
        'total_pulau'    => 16671,
        'highlights'     => 'Pemutakhiran kodifikasi daerah dan penyesuaian toponimi nasional serta penambahan desa definitif.',
        'doc_url'        => 'https://github.com/cahyadsn/wilayah_ref/blob/master/Permendagri%20No%2072%20Tahun%202019.pdf',
        'has_dob'        => false,
        'new_regions'    => [],
        'provinces'      => $s2020['provinces']
    ],
    [
        'year' => 2022,
        'regulasi' => 'Permendagri No. 58/2021 & Kepmendagri 050-145/2022',
        'short_title' => 'Kepmendagri 050-145/2022',
        'tanggal_penetapan' => '2022-02-14',
        'total_provinsi' => $s2022['total_provinsi'] ?: 34,
        'total_kab_kota' => $s2022['total_kab_kota'] ?: 514,
        'total_kab'      => $s2022['total_kab'] ?: 416,
        'total_kota'     => $s2022['total_kota'] ?: 98,
        'total_kecamatan'=> $s2022['total_kecamatan'] ?: 7266,
        'total_desa_kel' => $s2022['total_desa_kel'] ?: 83467,
        'total_desa'     => $s2022['total_desa'] ?: 75005,
        'total_kelurahan'=> $s2022['total_kelurahan'] ?: 8462,
        'total_pulau'    => $p2022 ?: 16772,
        'highlights'     => 'Penetapan mutakhir data pulau dan batas administratif sebelum pembentukan DOB Papua. 16.772 pulau resmi terdaftar.',
        'doc_url'        => 'https://github.com/cahyadsn/wilayah_ref/blob/master/Kepmendagri%20No%20050-145%20Tahun%202022.pdf',
        'has_dob'        => false,
        'new_regions'    => [],
        'provinces'      => $s2022['provinces']
    ],
    [
        'year' => 2023,
        'regulasi' => 'UU No. 14, 15, 16, 29 / 2022 & Kepmendagri No. 100.1.1-6117 / 2022',
        'short_title' => 'Pemekaran DOB Papua (Kepmendagri 100.1.1-6117/2022)',
        'tanggal_penetapan' => '2022-11-09',
        'total_provinsi' => $s2023['total_provinsi'] ?: 38,
        'total_kab_kota' => $s2023['total_kab_kota'] ?: 514,
        'total_kab'      => $s2023['total_kab'] ?: 416,
        'total_kota'     => $s2023['total_kota'] ?: 98,
        'total_kecamatan'=> $s2023['total_kecamatan'] ?: 7277,
        'total_desa_kel' => $s2023['total_desa_kel'] ?: 83794,
        'total_desa'     => $s2023['total_desa'] ?: 75317,
        'total_kelurahan'=> $s2023['total_kelurahan'] ?: 8477,
        'total_pulau'    => $p2023 ?: 17001,
        'highlights'     => 'Pemekaran bersejarah 4 Provinsi Baru di Tanah Papua (DOB Papua Selatan, Papua Tengah, Papua Pegunungan, Papua Barat Daya). Indonesia resmi memiliki 38 Provinsi.',
        'doc_url'        => 'https://github.com/cahyadsn/wilayah_ref/blob/master/Kepmendagri%20No%20100.1.1-6117%20Tahun%202022.pdf',
        'has_dob'        => true,
        'new_regions'    => [
            [
                'kode' => '93',
                'nama' => 'PAPUA SELATAN',
                'ibukota' => 'Merauke',
                'dasar_hukum' => 'UU No. 14 Tahun 2022',
                'asal' => 'Pemekaran dari Provinsi Papua (Kab. Merauke, Boven Digoel, Mappi, Asmat)',
                'lat' => -7.498,
                'lng' => 139.400
            ],
            [
                'kode' => '94',
                'nama' => 'PAPUA TENGAH',
                'ibukota' => 'Nabire',
                'dasar_hukum' => 'UU No. 15 Tahun 2022',
                'asal' => 'Pemekaran dari Provinsi Papua (Kab. Nabire, Puncak Jaya, Paniai, Mimika, Puncak, Dogiyai, Intan Jaya, Deiyai)',
                'lat' => -3.548,
                'lng' => 136.230
            ],
            [
                'kode' => '95',
                'nama' => 'PAPUA PEGUNUNGAN',
                'ibukota' => 'Wamena (Jayawijaya)',
                'dasar_hukum' => 'UU No. 16 Tahun 2022',
                'asal' => 'Pemekaran dari Provinsi Papua (Kab. Jayawijaya, Pegunungan Bintang, Yahukimo, Tolikara, Mamberamo Tengah, Yalimo, Lanny Jaya, Nduga)',
                'lat' => -4.083,
                'lng' => 138.944
            ],
            [
                'kode' => '96',
                'nama' => 'PAPUA BARAT DAYA',
                'ibukota' => 'Sorong',
                'dasar_hukum' => 'UU No. 29 Tahun 2022',
                'asal' => 'Pemekaran dari Provinsi Papua Barat (Kota Sorong, Kab. Sorong, Sorong Selatan, Maybrat, Tambrauw, Raja Ampat)',
                'lat' => -0.876,
                'lng' => 131.256
            ]
        ],
        'provinces'      => $s2023['provinces']
    ],
    [
        'year' => 2025,
        'regulasi' => 'Kepmendagri No. 300.2.2-2430 Tahun 2025',
        'short_title' => 'Kepmendagri 300.2.2-2430/2025',
        'tanggal_penetapan' => '2025-02-10',
        'total_provinsi' => $s2025['total_provinsi'] ?: 38,
        'total_kab_kota' => $s2025['total_kab_kota'] ?: 514,
        'total_kab'      => $s2025['total_kab'] ?: 416,
        'total_kota'     => $s2025['total_kota'] ?: 98,
        'total_kecamatan'=> $s2025['total_kecamatan'] ?: 7285,
        'total_desa_kel' => $s2025['total_desa_kel'] ?: 83762,
        'total_desa'     => $s2025['total_desa'] ?: 75266,
        'total_kelurahan'=> $s2025['total_kelurahan'] ?: 8496,
        'total_pulau'    => $p2025 ?: 17380,
        'highlights'     => 'Regulasi administratif termutakhir. Rekor 17.380 pulau terverifikasi baku, 38 Provinsi, 7.285 Kecamatan, dan 83.762 Desa/Kelurahan.',
        'doc_url'        => 'https://github.com/cahyadsn/wilayah_ref',
        'has_dob'        => false,
        'new_regions'    => [],
        'provinces'      => $s2025['provinces']
    ]
];

$output = [
    'version'       => '1.0.0',
    'generated_at'  => gmdate('Y-m-d\TH:i:s\Z'),
    'years'         => [2016, 2018, 2020, 2022, 2023, 2025],
    'dob_milestone' => 2023,
    'historical_paths' => [
        'papua_2022'       => $papua2022Path,
        'papua_barat_2022' => $papuaBarat2022Path
    ],
    'snapshots'     => $snapshots
];

$jsonFile = $cacheDir . '/timeline_snapshot.json';
$jsonContent = json_encode($output, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
file_put_contents($jsonFile, $jsonContent, LOCK_EX);

echo "Successfully wrote timeline snapshot to: {$jsonFile} (" . strlen($jsonContent) . " bytes)\n";
