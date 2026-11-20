<?php
/*
BISMILLAAHIRRAHMAANIRRAHIIM - In the Name of Allah, Most Gracious, Most Merciful
================================================================================
filename : geo_compare.php
purpose  : Side-by-side region comparison data provider (Feature P6)
create   : 2026-11-20
last edit: 2026-11-20 21:10:00
author   : cahya dsn
================================================================================
MIT License
copyright (c) 2017-2026 by cahya dsn; cahyadsn@gmail.com
================================================================================*/

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/geo_helpers.php';
require_once __DIR__ . '/geo_utils.php';

global $db, $tbl_wilayah, $tbl_pulau;
if (!isset($db) && isset($GLOBALS['db'])) {
    $db = $GLOBALS['db'];
}

$cache_dir = dirname(__DIR__) . '/cache';
if (!is_dir($cache_dir)) {
    @mkdir($cache_dir, 0755, true);
}

$table_wilayah = isset($tbl_wilayah) ? $tbl_wilayah : 'wilayah_level_1_2';
$table_pulau = isset($tbl_pulau) ? $tbl_pulau : 'wilayah_pulau';
$table_penduduk = 'wilayah_penduduk';
$table_luas = 'wilayah_luas';
$table_hierarchy = 'wilayah';

// Handle list request for autocomplete / dropdown options
if (isset($_GET['list']) && is_string($_GET['list'])) {
    $list_type = strtolower(trim($_GET['list']));
    if (!in_array($list_type, ['provinsi', 'kabkota'], true)) {
        http_response_code(400);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['status' => 'error', 'message' => 'Invalid list type. Allowed: provinsi, kabkota.'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $list_cache_file = $cache_dir . '/compare_list_' . $list_type . '.json';
    $cache_ttl = 604800; // 7 days

    if (file_exists($list_cache_file) && (time() - filemtime($list_cache_file) < $cache_ttl) && empty($_GET['nocache'])) {
        header('Content-Type: application/json; charset=utf-8');
        header('X-Content-Type-Options: nosniff');
        header('Cache-Control: public, max-age=86400');
        readfile($list_cache_file);
        exit;
    }

    if (!isset($db) || !$db) {
        http_response_code(500);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['status' => 'error', 'message' => 'Database connection unavailable'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $char_len = ($list_type === 'provinsi') ? 2 : 5;
    $stmtList = $db->prepare("SELECT kode, nama FROM {$table_wilayah} WHERE LENGTH(kode) = :len ORDER BY nama ASC");
    $stmtList->execute([':len' => $char_len]);
    $items = [];
    while ($row = $stmtList->fetchObject()) {
        $items[] = [
            'kode' => (string)$row->kode,
            'nama' => (string)$row->nama
        ];
    }

    $list_json = json_encode(['status' => 'success', 'data' => $items], JSON_UNESCAPED_UNICODE);
    @file_put_contents($list_cache_file, $list_json, LOCK_EX);

    header('Content-Type: application/json; charset=utf-8');
    header('X-Content-Type-Options: nosniff');
    header('Cache-Control: public, max-age=86400');
    echo $list_json;
    exit;
}

$raw_kode1 = isset($_GET['kode1']) && is_string($_GET['kode1']) ? trim($_GET['kode1']) : '';
$raw_kode2 = isset($_GET['kode2']) && is_string($_GET['kode2']) ? trim($_GET['kode2']) : '';

// Validation: regex ^[0-9]{2}(\.[0-9]{2})?$ and equal length
$valid1 = (bool)preg_match('/^[0-9]{2}(\.[0-9]{2})?$/', $raw_kode1);
$valid2 = (bool)preg_match('/^[0-9]{2}(\.[0-9]{2})?$/', $raw_kode2);

if (!$valid1 || !$valid2 || strlen($raw_kode1) !== strlen($raw_kode2)) {
    http_response_code(400);
    header('Content-Type: application/json; charset=utf-8');
    header('X-Content-Type-Options: nosniff');
    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid or mismatched region codes. Both must be provinces (2 digits) or both must be regencies/cities (5 characters, e.g. 32.01).'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$level = strlen($raw_kode1) === 2 ? 'provinsi' : 'kabkota';

$cache_file = $cache_dir . '/compare_' . $raw_kode1 . '_' . $raw_kode2 . '.json';
$cache_ttl = 604800; // 7 days

if (file_exists($cache_file) && (time() - filemtime($cache_file) < $cache_ttl) && empty($_GET['nocache'])) {
    header('Content-Type: application/json; charset=utf-8');
    header('X-Content-Type-Options: nosniff');
    header('Cache-Control: public, max-age=86400');
    readfile($cache_file);
    exit;
}

if (!isset($db) || !$db) {
    http_response_code(500);
    header('Content-Type: application/json; charset=utf-8');
    header('X-Content-Type-Options: nosniff');
    echo json_encode([
        'status' => 'error',
        'message' => 'Database connection unavailable'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

if (!function_exists('calcDeltaPercent')) {
    function calcDeltaPercent($val1, $val2) {
        $v1 = (float)$val1;
        $v2 = (float)$val2;
        if ($v2 == 0.0) {
            return $v1 > 0 ? 100.0 : 0.0;
        }
        return round((($v1 - $v2) / $v2) * 100, 2);
    }
}

if (!function_exists('calcRatioPair')) {
    function calcRatioPair($val1, $val2) {
        $v1 = max(0, (float)$val1);
        $v2 = max(0, (float)$val2);
        $sum = $v1 + $v2;
        if ($sum <= 0) {
            return [50.0, 50.0];
        }
        $r1 = round(($v1 / $sum) * 100, 1);
        $r2 = round(100.0 - $r1, 1);
        return [$r1, $r2];
    }
}

if (!function_exists('fetchComparisonEntity')) {
    function fetchComparisonEntity($db, $kode, $level, $table_wilayah, $table_pulau, $table_penduduk, $table_luas, $table_hierarchy) {
        // Fetch from table_wilayah
        $stmt = $db->prepare("SELECT kode, nama, ibukota, lat, lng, elv, tz, luas, penduduk, path FROM {$table_wilayah} WHERE kode = :kode LIMIT 1");
        $stmt->execute([':kode' => $kode]);
        $w = $stmt->fetchObject();

        if (!$w || empty($w->kode)) {
            return null;
        }

        $nama = (string)$w->nama;
        $ibukota = !empty($w->ibukota) ? (string)$w->ibukota : '';
        $lat = ($w->lat !== null && $w->lat !== '') ? (float)$w->lat : 0.0;
        $lng = ($w->lng !== null && $w->lng !== '') ? (float)$w->lng : 0.0;
        $elv = ($w->elv !== null && $w->elv !== '') ? (float)$w->elv : 0.0;
        $raw_tz = $w->tz !== null ? (int)$w->tz : 7;

        $tz_map = [7 => 'WIB', 8 => 'WITA', 9 => 'WIT'];
        $timezone = $tz_map[$raw_tz] ?? ($raw_tz > 0 ? 'UTC+' . $raw_tz : 'WIB');

        // Path validation and fallback
        $path = $w->path;
        if (empty($path) || (function_exists('isPathNearCentroid') && !isPathNearCentroid($path, $lat, $lng, $kode))) {
            if (function_exists('fallbackPathForCode')) {
                $path = json_encode(fallbackPathForCode($lat, $lng, $kode));
            }
        }

        // Fetch Luas (wilayah_luas table with fallback to table_wilayah)
        $luas_km2 = ($w->luas !== null && $w->luas !== '') ? (float)$w->luas : 0.0;
        try {
            $stmtLuas = $db->prepare("SELECT luas FROM {$table_luas} WHERE kode = :kode LIMIT 1");
            $stmtLuas->execute([':kode' => $kode]);
            $rowLuas = $stmtLuas->fetchObject();
            if ($rowLuas && $rowLuas->luas !== null && $rowLuas->luas !== '') {
                $luas_km2 = (float)$rowLuas->luas;
            }
        } catch (\Exception $e) {
            // keep fallback
        }

        // Fetch Penduduk (wilayah_penduduk table with fallback to table_wilayah)
        $penduduk = ($w->penduduk !== null && $w->penduduk !== '') ? (int)$w->penduduk : 0;
        $penduduk_pria = null;
        $penduduk_wanita = null;
        $sex_ratio = null;

        try {
            $stmtPenduduk = $db->prepare("SELECT pria, wanita, total FROM {$table_penduduk} WHERE kode = :kode LIMIT 1");
            $stmtPenduduk->execute([':kode' => $kode]);
            $rowPenduduk = $stmtPenduduk->fetchObject();
            if ($rowPenduduk && $rowPenduduk->total !== null) {
                $penduduk = (int)$rowPenduduk->total;
                $penduduk_pria = (int)$rowPenduduk->pria;
                $penduduk_wanita = (int)$rowPenduduk->wanita;
                if ($penduduk_wanita > 0) {
                    $sex_ratio = round(($penduduk_pria / $penduduk_wanita) * 100, 2);
                }
            }
        } catch (\Exception $e) {
            // keep fallback
        }

        // Kepadatan
        $kepadatan = ($luas_km2 > 0 && $penduduk > 0) ? round($penduduk / $luas_km2, 2) : 0.0;

        // Total Pulau
        $total_pulau = 0;
        try {
            $stmtPulau = $db->prepare("SELECT COUNT(*) AS cnt FROM {$table_pulau} WHERE kode LIKE :prefix");
            $stmtPulau->execute([':prefix' => addcslashes($kode, '%_\\') . '.%']);
            $rowPulau = $stmtPulau->fetchObject();
            if ($rowPulau && isset($rowPulau->cnt)) {
                $total_pulau = (int)$rowPulau->cnt;
            }
        } catch (\Exception $e) {
            $total_pulau = 0;
        }

        // Total kab, kota, kec, kel, desa from hierarchy table
        $total_kab = 0;
        $total_kota = 0;
        $total_kec = 0;
        $total_kel = 0;
        $total_desa = 0;

        try {
            if ($level === 'provinsi') {
                $sqlHierarchy = "SELECT 
                    SUM(CASE WHEN LENGTH(kode)=5 AND (SUBSTR(kode, 4, 2) < '70' OR LOWER(nama) LIKE 'kab%') THEN 1 ELSE 0 END) AS total_kab,
                    SUM(CASE WHEN LENGTH(kode)=5 AND (SUBSTR(kode, 4, 2) >= '70' OR LOWER(nama) LIKE 'kota%') THEN 1 ELSE 0 END) AS total_kota,
                    SUM(CASE WHEN LENGTH(kode)=8 THEN 1 ELSE 0 END) AS total_kec,
                    SUM(CASE WHEN LENGTH(kode)=13 AND SUBSTR(kode, 10, 1) = '1' THEN 1 ELSE 0 END) AS total_kel,
                    SUM(CASE WHEN LENGTH(kode)=13 AND SUBSTR(kode, 10, 1) IN ('2', '3') THEN 1 ELSE 0 END) AS total_desa
                FROM {$table_hierarchy}
                WHERE kode LIKE :prefix";
            } else {
                $sqlHierarchy = "SELECT 
                    0 AS total_kab,
                    0 AS total_kota,
                    SUM(CASE WHEN LENGTH(kode)=8 THEN 1 ELSE 0 END) AS total_kec,
                    SUM(CASE WHEN LENGTH(kode)=13 AND SUBSTR(kode, 10, 1) = '1' THEN 1 ELSE 0 END) AS total_kel,
                    SUM(CASE WHEN LENGTH(kode)=13 AND SUBSTR(kode, 10, 1) IN ('2', '3') THEN 1 ELSE 0 END) AS total_desa
                FROM {$table_hierarchy}
                WHERE kode LIKE :prefix";
            }
            $stmtH = $db->prepare($sqlHierarchy);
            $stmtH->execute([':prefix' => addcslashes($kode, '%_\\') . '.%']);
            $rowH = $stmtH->fetchObject();
            if ($rowH) {
                $total_kab = (int)($rowH->total_kab ?? 0);
                $total_kota = (int)($rowH->total_kota ?? 0);
                $total_kec = (int)($rowH->total_kec ?? 0);
                $total_kel = (int)($rowH->total_kel ?? 0);
                $total_desa = (int)($rowH->total_desa ?? 0);
            }
        } catch (\Exception $e) {
            // If table hierarchy fails or is not available, leave at 0
        }

        return [
            'kode' => $kode,
            'nama' => $nama,
            'ibukota' => $ibukota,
            'lat' => $lat,
            'lng' => $lng,
            'elv' => $elv,
            'timezone' => $timezone,
            'luas_km2' => $luas_km2,
            'penduduk' => $penduduk,
            'kepadatan' => $kepadatan,
            'penduduk_pria' => $penduduk_pria,
            'penduduk_wanita' => $penduduk_wanita,
            'sex_ratio' => $sex_ratio,
            'total_kab' => $total_kab,
            'total_kota' => $total_kota,
            'total_kec' => $total_kec,
            'total_kel' => $total_kel,
            'total_desa' => $total_desa,
            'total_pulau' => $total_pulau,
            'path' => $path
        ];
    }
}

$entity1 = fetchComparisonEntity($db, $raw_kode1, $level, $table_wilayah, $table_pulau, $table_penduduk, $table_luas, $table_hierarchy);
$entity2 = fetchComparisonEntity($db, $raw_kode2, $level, $table_wilayah, $table_pulau, $table_penduduk, $table_luas, $table_hierarchy);

if (!$entity1 || !$entity2) {
    http_response_code(404);
    header('Content-Type: application/json; charset=utf-8');
    header('X-Content-Type-Options: nosniff');
    echo json_encode([
        'status' => 'error',
        'message' => 'One or both regions could not be found.'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$comparison = [
    'luas_delta_percent' => calcDeltaPercent($entity1['luas_km2'], $entity2['luas_km2']),
    'penduduk_delta_percent' => calcDeltaPercent($entity1['penduduk'], $entity2['penduduk']),
    'kepadatan_delta_percent' => calcDeltaPercent($entity1['kepadatan'], $entity2['kepadatan']),
    'pulau_delta_percent' => calcDeltaPercent($entity1['total_pulau'], $entity2['total_pulau']),
    'kec_delta_percent' => calcDeltaPercent($entity1['total_kec'], $entity2['total_kec']),
    'desa_delta_percent' => calcDeltaPercent($entity1['total_desa'], $entity2['total_desa']),
    'kel_delta_percent' => calcDeltaPercent($entity1['total_kel'], $entity2['total_kel']),
    'ratios' => [
        'luas' => calcRatioPair($entity1['luas_km2'], $entity2['luas_km2']),
        'penduduk' => calcRatioPair($entity1['penduduk'], $entity2['penduduk']),
        'kepadatan' => calcRatioPair($entity1['kepadatan'], $entity2['kepadatan']),
        'pulau' => calcRatioPair($entity1['total_pulau'], $entity2['total_pulau']),
        'kec' => calcRatioPair($entity1['total_kec'], $entity2['total_kec']),
        'desa' => calcRatioPair($entity1['total_desa'], $entity2['total_desa']),
        'kel' => calcRatioPair($entity1['total_kel'], $entity2['total_kel'])
    ]
];

if ($level === 'provinsi') {
    $comparison['kab_delta_percent'] = calcDeltaPercent($entity1['total_kab'], $entity2['total_kab']);
    $comparison['kota_delta_percent'] = calcDeltaPercent($entity1['total_kota'], $entity2['total_kota']);
    $comparison['ratios']['kab'] = calcRatioPair($entity1['total_kab'], $entity2['total_kab']);
    $comparison['ratios']['kota'] = calcRatioPair($entity1['total_kota'], $entity2['total_kota']);
}

$response_data = [
    'status' => 'success',
    'generated_at' => gmdate('Y-m-d\TH:i:s\Z'),
    'level' => $level,
    'entities' => [
        'entity1' => $entity1,
        'entity2' => $entity2
    ],
    'comparison' => $comparison
];

$json_output = json_encode($response_data, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);

// Save to cache
if (isset($cache_file)) {
    @file_put_contents($cache_file, $json_output, LOCK_EX);
}

header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');
header('Cache-Control: public, max-age=86400');
echo $json_output;

