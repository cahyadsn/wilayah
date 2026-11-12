<?php
/*
BISMILLAAHIRRAHMAANIRRAHIIM - In the Name of Allah, Most Gracious, Most Merciful
================================================================================
filename : geo_thematic.php
purpose  : Thematic choropleth data provider for density & demographics (P3)
create   : 2026-11-12
last edit: 2026-11-12 08:50:00
author   : cahya dsn
================================================================================
MIT License
copyright (c) 2017-2026 by cahya dsn; cahyadsn@gmail.com
================================================================================*/

require_once __DIR__ . '/db.php';
global $db;
if (!isset($db) && isset($GLOBALS['db'])) {
    $db = $GLOBALS['db'];
}

$raw_prov = isset($_GET['prov']) && is_string($_GET['prov']) ? trim($_GET['prov']) : '';
$metric = isset($_GET['metric']) && is_string($_GET['metric']) ? strtolower(trim($_GET['metric'])) : 'density';
if ($metric !== 'sex_ratio') {
    $metric = 'density';
}

// Validate prov parameter: empty, '0', 'all', or 2-digit province code
$is_national = ($raw_prov === '' || $raw_prov === '0' || strtolower($raw_prov) === 'all');
if (!$is_national && !preg_match('/^[0-9]{2}$/', $raw_prov)) {
    http_response_code(400);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['error' => 'Invalid province code'], JSON_UNESCAPED_UNICODE);
    exit;
}

$prov_kode = $is_national ? '0' : $raw_prov;
$cache_dir = dirname(__DIR__) . '/cache';
if (!is_dir($cache_dir)) {
    @mkdir($cache_dir, 0755, true);
}

$cache_key = 'thematic_' . ($is_national ? 'national' : $prov_kode) . '_' . $metric;
$cache_file = $cache_dir . '/' . $cache_key . '.json';
$cache_ttl = 604800; // 7 days

if (file_exists($cache_file) && (time() - filemtime($cache_file) < $cache_ttl) && empty($_GET['nocache'])) {
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: public, max-age=86400');
    readfile($cache_file);
    exit;
}

if (!isset($db) || !$db) {
    http_response_code(500);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['error' => 'Database connection unavailable'], JSON_UNESCAPED_UNICODE);
    exit;
}

// Helper to determine array depth
if (!function_exists('getGeoArrayDepth')) {
    function getGeoArrayDepth($arr) {
        if (!is_array($arr) || empty($arr)) return 0;
        return 1 + getGeoArrayDepth(reset($arr));
    }
}

// Convert [lat, lng] to [lng, lat] for RFC 7946 GeoJSON
if (!function_exists('geoLatLngToLngLat')) {
    function geoLatLngToLngLat($coords) {
        if (!is_array($coords) || empty($coords)) return [];
        if (isset($coords[0]) && isset($coords[1]) && is_numeric($coords[0]) && is_numeric($coords[1])) {
            return [(float)$coords[1], (float)$coords[0]];
        }
        return array_map('geoLatLngToLngLat', $coords);
    }
}

// Convert DB path coordinate rings to GeoJSON geometry
if (!function_exists('pathToGeoJsonGeometry')) {
    function pathToGeoJsonGeometry($path) {
        if (empty($path)) return null;
        $decoded = is_string($path) ? json_decode($path, true) : $path;
        if (!is_array($decoded) || empty($decoded)) return null;

        $depth = getGeoArrayDepth($decoded);
        if ($depth === 2) {
            return [
                'type' => 'Polygon',
                'coordinates' => [ geoLatLngToLngLat($decoded) ]
            ];
        } elseif ($depth === 3) {
            $multi = [];
            foreach ($decoded as $ring) {
                $multi[] = [ geoLatLngToLngLat($ring) ];
            }
            return [
                'type' => 'MultiPolygon',
                'coordinates' => $multi
            ];
        } elseif ($depth === 4) {
            return [
                'type' => 'MultiPolygon',
                'coordinates' => geoLatLngToLngLat($decoded)
            ];
        }
        return null;
    }
}

// Check available tables for joins
$has_penduduk = false;
$has_luas = false;
try {
    $check_p = $db->query("SHOW TABLES LIKE 'wilayah_penduduk'");
    $has_penduduk = ($check_p && $check_p->rowCount() > 0);
    $check_l = $db->query("SHOW TABLES LIKE 'wilayah_luas'");
    $has_luas = ($check_l && $check_l->rowCount() > 0);
} catch (Exception $e) {
    // Graceful fallback for mock PDO / restricted environments
}

$prov_nama = 'Indonesia';
if (!$is_national) {
    try {
        $stmt_prov = $db->prepare("SELECT nama FROM wilayah_level_1_2 WHERE kode = :kode LIMIT 1");
        $stmt_prov->execute([':kode' => $prov_kode]);
        $row_prov = $stmt_prov->fetch(PDO::FETCH_ASSOC);
        if ($row_prov && !empty($row_prov['nama'])) {
            $prov_nama = $row_prov['nama'];
        }
    } catch (Exception $e) {
        // Fallback
    }
}

// Build query
$select_luas = $has_luas ? "COALESCE(l.luas, w.luas, 0)" : "COALESCE(w.luas, 0)";
$select_penduduk = $has_penduduk ? "COALESCE(p.total, w.penduduk, 0)" : "COALESCE(w.penduduk, 0)";
$select_pria = $has_penduduk ? "p.pria" : "NULL";
$select_wanita = $has_penduduk ? "p.wanita" : "NULL";

$joins = "";
if ($has_penduduk) {
    $joins .= " LEFT JOIN wilayah_penduduk p ON w.kode = p.kode";
}
if ($has_luas) {
    $joins .= " LEFT JOIN wilayah_luas l ON w.kode = l.kode";
}

if ($is_national) {
    $sql = "
        SELECT 
            w.kode, 
            w.nama, 
            w.lat, 
            w.lng, 
            w.path,
            {$select_luas} AS luas,
            {$select_penduduk} AS penduduk,
            {$select_pria} AS pria,
            {$select_wanita} AS wanita
        FROM wilayah_level_1_2 w
        {$joins}
        WHERE CHAR_LENGTH(w.kode) = 2
        ORDER BY w.kode ASC
    ";
    $stmt = $db->prepare($sql);
    $stmt->execute();
} else {
    $sql = "
        SELECT 
            w.kode, 
            w.nama, 
            w.lat, 
            w.lng, 
            w.path,
            {$select_luas} AS luas,
            {$select_penduduk} AS penduduk,
            {$select_pria} AS pria,
            {$select_wanita} AS wanita
        FROM wilayah_level_1_2 w
        {$joins}
        WHERE w.kode LIKE :prefix AND CHAR_LENGTH(w.kode) = 5
        ORDER BY w.kode ASC
    ";
    $stmt = $db->prepare($sql);
    $stmt->execute([':prefix' => $prov_kode . '.%']);
}

$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

$features = [];
$values = [];

foreach ($rows as $r) {
    $luas = (float)($r['luas'] ?? 0);
    $penduduk = (float)($r['penduduk'] ?? 0);
    $pria = isset($r['pria']) && $r['pria'] !== null ? (int)$r['pria'] : null;
    $wanita = isset($r['wanita']) && $r['wanita'] !== null ? (int)$r['wanita'] : null;

    // Prevent division by zero
    $kepadatan = ($luas > 0) ? round($penduduk / $luas, 2) : 0.0;

    $sex_ratio = null;
    if ($wanita !== null && $wanita > 0 && $pria !== null) {
        $sex_ratio = round(($pria / $wanita) * 100, 2);
    }

    $val = ($metric === 'sex_ratio') ? ($sex_ratio ?? 100.0) : $kepadatan;
    $values[] = $val;

    $geometry = pathToGeoJsonGeometry($r['path']);

    $features[] = [
        'type' => 'Feature',
        'properties' => [
            'kode'       => $r['kode'],
            'nama'       => $r['nama'],
            'lat'        => isset($r['lat']) && is_numeric($r['lat']) ? (float)$r['lat'] : null,
            'lng'        => isset($r['lng']) && is_numeric($r['lng']) ? (float)$r['lng'] : null,
            'penduduk'   => (int)$penduduk,
            'luas'       => $luas,
            'kepadatan'  => $kepadatan,
            'pria'       => $pria,
            'wanita'     => $wanita,
            'sex_ratio'  => $sex_ratio,
            'value'      => $val
        ],
        'geometry' => $geometry
    ];
}

$min_val = !empty($values) ? min($values) : 0;
$max_val = !empty($values) ? max($values) : 0;
$avg_val = !empty($values) ? round(array_sum($values) / count($values), 2) : 0;

$response = [
    'type' => 'FeatureCollection',
    'properties' => [
        'prov_kode' => $prov_kode,
        'prov_nama' => $prov_nama,
        'metric'    => $metric,
        'unit'      => ($metric === 'sex_ratio') ? 'pria per 100 wanita' : 'jiwa/km²',
        'count'     => count($features),
        'min'       => $min_val,
        'max'       => $max_val,
        'avg'       => $avg_val
    ],
    'features' => $features
];

$output = json_encode($response, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

// Write to cache file
@file_put_contents($cache_file, $output);

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: public, max-age=86400');
echo $output;
