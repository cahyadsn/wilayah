<?php
/*
BISMILLAAHIRRAHMAANIRRAHIIM - In the Name of Allah, Most Gracious, Most Merciful
================================================================================
filename : geo_proximity.php
purpose  : Spatial Proximity & Circular Buffer Engine for Islands and Regions (P8)
create   : 2026-11-25
last edit: 2026-11-25 07:00:00
author   : cahya dsn
================================================================================
MIT License
copyright (c) 2017-2026 by cahya dsn; cahyadsn@gmail.com
================================================================================*/

declare(strict_types=1);

global $db;
if (!isset($db) && isset($GLOBALS['db'])) {
    $db = $GLOBALS['db'];
}
if (!isset($db) || !($db instanceof PDO)) {
    require_once __DIR__ . '/db.php';
}

/**
 * Calculates Great-Circle distance using Haversine formula
 */
if (!function_exists('haversineDistance')) {
    function haversineDistance(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $earthRadiusKm = 6371.0;
        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);
        $a = sin($dLat / 2) * sin($dLat / 2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLng / 2) * sin($dLng / 2);
        $c = 2 * atan2(sqrt($a), sqrt(max(0.0, 1.0 - $a)));
        return $earthRadiusKm * $c;
    }
}

/**
 * Calculates initial bearing (azimuth) from point 1 to point 2 in degrees (0..360)
 */
if (!function_exists('bearingAzimuth')) {
    function bearingAzimuth(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $phi1 = deg2rad($lat1);
        $phi2 = deg2rad($lat2);
        $dLambda = deg2rad($lng2 - $lng1);
        $y = sin($dLambda) * cos($phi2);
        $x = cos($phi1) * sin($phi2) - sin($phi1) * cos($phi2) * cos($dLambda);
        $theta = atan2($y, $x);
        $bearing = fmod((rad2deg($theta) + 360.0), 360.0);
        return round($bearing, 1);
    }
}

/**
 * Converts degree bearing into 16-point compass rose direction
 */
if (!function_exists('compassDirection')) {
    function compassDirection(float $deg): string
    {
        $sectors = [
            'N', 'NNE', 'NE', 'ENE',
            'E', 'ESE', 'SE', 'SSE',
            'S', 'SSW', 'SW', 'WSW',
            'W', 'WNW', 'NW', 'NNW'
        ];
        $norm = fmod(($deg + 360.0), 360.0);
        $idx = (int)round($norm / 22.5) % 16;
        return $sectors[$idx];
    }
}

// 1. Validate mandatory input coordinates (lat, lng)
if (!isset($_GET['lat']) || !isset($_GET['lng']) || !is_numeric($_GET['lat']) || !is_numeric($_GET['lng'])) {
    http_response_code(400);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'status'  => 'error',
        'message' => "Parameter 'lat' dan 'lng' wajib diisi dengan koordinat numerik yang valid."
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$centerLat = (float)$_GET['lat'];
$centerLng = (float)$_GET['lng'];

if ($centerLat < -90.0 || $centerLat > 90.0 || $centerLng < -180.0 || $centerLng > 180.0) {
    http_response_code(400);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'status'  => 'error',
        'message' => "Koordinat di luar rentang valid: lat [-90, 90], lng [-180, 180]."
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// 2. Parse radius, limit, target, and format
$rawRadius = $_GET['radius_km'] ?? $_GET['radius'] ?? 25.0;
$radiusKm = is_numeric($rawRadius) ? (float)$rawRadius : 25.0;
if ($radiusKm < 0.1) $radiusKm = 0.1;
if ($radiusKm > 500.0) $radiusKm = 500.0; // Security limit to prevent full-table denial of service

$rawLimit = $_GET['limit'] ?? 50;
$limit = is_numeric($rawLimit) ? (int)$rawLimit : 50;
if ($limit < 1) $limit = 1;
if ($limit > 200) $limit = 200;

$target = isset($_GET['target']) && is_string($_GET['target']) ? strtolower(trim($_GET['target'])) : 'islands';
if (!in_array($target, ['islands', 'regions', 'all'], true)) {
    $target = 'islands';
}

$format = isset($_GET['format']) && is_string($_GET['format']) ? strtolower(trim($_GET['format'])) : 'json';
if (!in_array($format, ['json', 'csv'], true)) {
    $format = 'json';
}

// 3. Compute Bounding Box Filter
$deltaLat = $radiusKm / 111.0;
$latMin = max(-90.0, $centerLat - $deltaLat);
$latMax = min(90.0, $centerLat + $deltaLat);

$cosLat = cos(deg2rad(max(-89.9, min(89.9, $centerLat))));
$safeCos = max(0.0001, abs($cosLat));
$deltaLng = $radiusKm / (111.0 * $safeCos);
$lngMin = max(-180.0, $centerLng - $deltaLng);
$lngMax = min(180.0, $centerLng + $deltaLng);

$items = [];

// 4. Query Islands if requested
if ($target === 'islands' || $target === 'all') {
    try {
        $stmtIslands = $db->prepare("
            SELECT kode, nama, lat, lng, status, luas
            FROM wilayah_pulau
            WHERE lat BETWEEN :lat_min AND :lat_max
              AND lng BETWEEN :lng_min AND :lng_max
        ");
        $stmtIslands->execute([
            ':lat_min' => $latMin,
            ':lat_max' => $latMax,
            ':lng_min' => $lngMin,
            ':lng_max' => $lngMax
        ]);

        while ($row = $stmtIslands->fetch(PDO::FETCH_ASSOC)) {
            $rLat = (float)$row['lat'];
            $rLng = (float)$row['lng'];
            $dKm = haversineDistance($centerLat, $centerLng, $rLat, $rLng);

            if ($dKm <= $radiusKm) {
                $bearing = bearingAzimuth($centerLat, $centerLng, $rLat, $rLng);
                $dir = compassDirection($bearing);

                $items[] = [
                    'kode'        => (string)$row['kode'],
                    'nama'        => (string)$row['nama'],
                    'type'        => 'island',
                    'lat'         => round($rLat, 5),
                    'lng'         => round($rLng, 5),
                    'status'      => $row['status'] ?? null,
                    'luas_km2'    => $row['luas'] !== null ? round((float)$row['luas'], 4) : null,
                    'distance_km' => round($dKm, 2),
                    'distance_nm' => round($dKm / 1.852, 2),
                    'bearing_deg' => $bearing,
                    'direction'   => $dir
                ];
            }
        }
    } catch (\Throwable $e) {
        // Handled below if empty
    }
}

// 5. Query Regions (Provinsi / Kab / Kota) if requested
if ($target === 'regions' || $target === 'all') {
    try {
        $stmtRegions = $db->prepare("
            SELECT kode, nama, lat, lng, luas, penduduk
            FROM wilayah_level_1_2
            WHERE lat BETWEEN :lat_min AND :lat_max
              AND lng BETWEEN :lng_min AND :lng_max
        ");
        $stmtRegions->execute([
            ':lat_min' => $latMin,
            ':lat_max' => $latMax,
            ':lng_min' => $lngMin,
            ':lng_max' => $lngMax
        ]);

        while ($row = $stmtRegions->fetch(PDO::FETCH_ASSOC)) {
            $rLat = (float)$row['lat'];
            $rLng = (float)$row['lng'];
            $dKm = haversineDistance($centerLat, $centerLng, $rLat, $rLng);

            if ($dKm <= $radiusKm) {
                $bearing = bearingAzimuth($centerLat, $centerLng, $rLat, $rLng);
                $dir = compassDirection($bearing);
                $isProv = (strlen((string)$row['kode']) === 2);

                $items[] = [
                    'kode'        => (string)$row['kode'],
                    'nama'        => (string)$row['nama'],
                    'type'        => $isProv ? 'provinsi' : 'kabkota',
                    'lat'         => round($rLat, 5),
                    'lng'         => round($rLng, 5),
                    'status'      => null,
                    'luas_km2'    => $row['luas'] !== null ? round((float)$row['luas'], 2) : null,
                    'penduduk'    => isset($row['penduduk']) ? (int)$row['penduduk'] : null,
                    'distance_km' => round($dKm, 2),
                    'distance_nm' => round($dKm / 1.852, 2),
                    'bearing_deg' => $bearing,
                    'direction'   => $dir
                ];
            }
        }
    } catch (\Throwable $e) {
        // Handled below
    }
}

// 6. Sort by nearest distance first (ascending)
usort($items, function(array $a, array $b): int {
    if ($a['distance_km'] === $b['distance_km']) {
        return strcmp($a['nama'], $b['nama']);
    }
    return ($a['distance_km'] < $b['distance_km']) ? -1 : 1;
});

$totalFound = count($items);
$slicedItems = array_slice($items, 0, $limit);

// 7. Output according to requested format
if ($format === 'csv') {
    $filename = sprintf('buffer_radius_%s_%s_%skm.csv', round($centerLat, 4), round($centerLng, 4), $radiusKm);
    header('Content-Type: text/csv; charset=UTF-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Cache-Control: public, max-age=3600');

    // UTF-8 BOM for Excel
    echo "\xEF\xBB\xBF";
    $fp = fopen('php://output', 'w');
    fputcsv($fp, ['Kode', 'Nama', 'Tipe', 'Status', 'Latitude', 'Longitude', 'Jarak_km', 'Jarak_NM', 'Arah_Derajat', 'Mata_Angin', 'Luas_km2']);

    foreach ($slicedItems as $item) {
        fputcsv($fp, [
            $item['kode'],
            $item['nama'],
            $item['type'],
            $item['status'] ?? '-',
            $item['lat'],
            $item['lng'],
            $item['distance_km'],
            $item['distance_nm'],
            $item['bearing_deg'],
            $item['direction'],
            $item['luas_km2'] ?? '-'
        ]);
    }
    fclose($fp);
    exit;
}

// JSON Output
header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');
header('Cache-Control: public, max-age=3600');

$islandsList = array_values(array_filter($slicedItems, function(array $i): bool {
    return ($i['type'] ?? '') === 'island';
}));
$regionsList = array_values(array_filter($slicedItems, function(array $i): bool {
    return ($i['type'] ?? '') === 'region';
}));

$response = [
    'status'       => 'success',
    'center'       => [
        'lat' => round($centerLat, 5),
        'lng' => round($centerLng, 5)
    ],
    'radius_km'    => $radiusKm,
    'radius_nm'    => round($radiusKm / 1.852, 2),
    'target'       => $target,
    'total_found'  => $totalFound,
    'returned'     => count($slicedItems),
    'bounding_box' => [
        'lat_min' => round($latMin, 5),
        'lat_max' => round($latMax, 5),
        'lng_min' => round($lngMin, 5),
        'lng_max' => round($lngMax, 5)
    ],
    'items'        => $slicedItems,
    'results'      => $slicedItems
];

if ($target === 'islands') {
    $response['islands'] = $islandsList;
} elseif ($target === 'regions') {
    $response['regions'] = $regionsList;
} else {
    $response['islands'] = $islandsList;
    $response['regions'] = $regionsList;
}

echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
exit;
