<?php
/*
BISMILLAAHIRRAHMAANIRRAHIIM - In the Name of Allah, Most Gracious, Most Merciful
================================================================================
filename : geo_export.php
purpose  : Spatial and tabular data exporter (GeoJSON, KML, CSV) (P4)
create   : 2026-11-14
last edit: 2026-11-14 20:05:00
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

$raw_kode = isset($_GET['kode']) && is_string($_GET['kode']) ? trim($_GET['kode']) : '';
$format = isset($_GET['format']) && is_string($_GET['format']) ? strtolower(trim($_GET['format'])) : '';
$target = isset($_GET['target']) && is_string($_GET['target']) ? strtolower(trim($_GET['target'])) : '';
$is_preview = !empty($_GET['preview']);

// Normalize target
if ($target === 'data') {
    $target = 'stats';
}
if (!in_array($target, ['boundary', 'islands', 'stats'], true)) {
    $target = ($format === 'csv') ? 'stats' : 'boundary';
}

// Normalize format
if (!in_array($format, ['geojson', 'kml', 'csv'], true)) {
    $format = ($target === 'stats' || $target === 'islands') ? 'csv' : 'geojson';
}

// Validate kode: empty, '0', 'all', or standard region/island code pattern
$is_all = ($raw_kode === '' || $raw_kode === '0' || strtolower($raw_kode) === 'all');
if (!$is_all) {
    $is_region = preg_match('/^[0-9]{2}(\.[0-9]{2}){0,3}$/', $raw_kode);
    $is_island = preg_match('/^[0-9]{2}\.[0-9]{2}\.[0-9]{5}$/', $raw_kode);
    if (!$is_region && !$is_island) {
        http_response_code(400);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['error' => 'Invalid code parameter'], JSON_UNESCAPED_UNICODE);
        exit;
    }
}

if (!isset($db) || !$db) {
    http_response_code(500);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['error' => 'Database connection unavailable'], JSON_UNESCAPED_UNICODE);
    exit;
}

// --- Helper Functions ---

if (!function_exists('getGeoExportDepth')) {
    function getGeoExportDepth($arr) {
        if (!is_array($arr) || empty($arr)) return 0;
        return 1 + getGeoExportDepth(reset($arr));
    }
}

if (!function_exists('geoExportLatLngToLngLat')) {
    function geoExportLatLngToLngLat($coords) {
        if (!is_array($coords) || empty($coords)) return [];
        if (isset($coords[0]) && isset($coords[1]) && is_numeric($coords[0]) && is_numeric($coords[1])) {
            return [(float)$coords[1], (float)$coords[0]];
        }
        return array_map('geoExportLatLngToLngLat', $coords);
    }
}

if (!function_exists('geoExportCloseRing')) {
    function geoExportCloseRing(&$ring) {
        if (count($ring) > 0 && ($ring[0][0] !== end($ring)[0] || $ring[0][1] !== end($ring)[1])) {
            $ring[] = $ring[0];
        }
    }
}

if (!function_exists('pathToExportGeoJsonGeometry')) {
    function pathToExportGeoJsonGeometry($path) {
        if (empty($path)) return null;
        $decoded = is_string($path) ? json_decode($path, true) : $path;
        if (!is_array($decoded) || empty($decoded)) return null;

        $depth = getGeoExportDepth($decoded);
        if ($depth === 2) {
            $ring = geoExportLatLngToLngLat($decoded);
            geoExportCloseRing($ring);
            return [
                'type' => 'Polygon',
                'coordinates' => [ $ring ]
            ];
        } elseif ($depth === 3) {
            $multi = [];
            foreach ($decoded as $part) {
                $ring = geoExportLatLngToLngLat($part);
                geoExportCloseRing($ring);
                $multi[] = [ $ring ];
            }
            return [
                'type' => 'MultiPolygon',
                'coordinates' => $multi
            ];
        } elseif ($depth === 4) {
            return [
                'type' => 'MultiPolygon',
                'coordinates' => geoExportLatLngToLngLat($decoded)
            ];
        }
        return null;
    }
}

if (!function_exists('ringToKmlCoordinatesString')) {
    function ringToKmlCoordinatesString($ring) {
        $coords = [];
        foreach ($ring as $pt) {
            if (isset($pt[0]) && isset($pt[1])) {
                $coords[] = (float)$pt[1] . ',' . (float)$pt[0] . ',0';
            }
        }
        if (!empty($coords) && $coords[0] !== end($coords)) {
            $coords[] = $coords[0];
        }
        return implode(' ', $coords);
    }
}

if (!function_exists('pathToExportKmlGeometry')) {
    function pathToExportKmlGeometry($path) {
        if (empty($path)) return '';
        $decoded = is_string($path) ? json_decode($path, true) : $path;
        if (!is_array($decoded) || empty($decoded)) return '';

        $depth = getGeoExportDepth($decoded);
        if ($depth === 2) {
            $coordStr = ringToKmlCoordinatesString($decoded);
            return "<Polygon><outerBoundaryIs><LinearRing><coordinates>{$coordStr}</coordinates></LinearRing></outerBoundaryIs></Polygon>";
        } elseif ($depth === 3) {
            $polys = [];
            foreach ($decoded as $part) {
                $coordStr = ringToKmlCoordinatesString($part);
                $polys[] = "<Polygon><outerBoundaryIs><LinearRing><coordinates>{$coordStr}</coordinates></LinearRing></outerBoundaryIs></Polygon>";
            }
            return "<MultiGeometry>" . implode('', $polys) . "</MultiGeometry>";
        }
        return '';
    }
}

if (!function_exists('sanitizeExportFilename')) {
    function sanitizeExportFilename($str) {
        $clean = preg_replace('/[^a-zA-Z0-9_\-]/', '_', strtolower(trim($str)));
        $clean = preg_replace('/_+/', '_', $clean);
        return trim($clean, '_');
    }
}

if (!function_exists('getRegionTypeFromCode')) {
    function getRegionTypeFromCode($kode) {
        $len = strlen($kode);
        if ($len === 2) return 'Provinsi';
        if ($len === 5) {
            $parts = explode('.', $kode);
            $sub = isset($parts[1]) ? (int)$parts[1] : 0;
            return ($sub >= 71) ? 'Kota' : 'Kabupaten';
        }
        if ($len === 8) return 'Kecamatan';
        if ($len === 13) {
            $parts = explode('.', $kode);
            $sub = isset($parts[3]) ? (int)$parts[3] : 0;
            if ($sub >= 3000) return 'Desa Adat';
            if ($sub >= 2000) return 'Desa';
            return 'Kelurahan';
        }
        if ($len === 11) return 'Pulau';
        return 'Wilayah';
    }
}

// --- Fetch Region Information ---

$region_info = null;
$clean_name = 'indonesia';
$safe_code = $is_all ? 'all' : sanitizeExportFilename($raw_kode);

if (!$is_all) {
    $st = $db->prepare("
        SELECT 
            w.kode, 
            w.nama, 
            w.lat, 
            w.lng, 
            w.path,
            COALESCE(l.luas, w.luas, 0) as luas,
            COALESCE(p.total, w.penduduk, 0) as penduduk,
            p.pria,
            p.wanita
        FROM wilayah_level_1_2 w
        LEFT JOIN wilayah_penduduk p ON w.kode = p.kode
        LEFT JOIN wilayah_luas l ON w.kode = l.kode
        WHERE w.kode = ?
        LIMIT 1
    ");
    $st->execute([$raw_kode]);
    $region_info = $st->fetch(PDO::FETCH_ASSOC);

    // Fallback if not found in level 1-2 (e.g. Level 3/4 or island)
    if (!$region_info) {
        // Check island
        $st_p = $db->prepare("SELECT * FROM wilayah_pulau WHERE kode = ? LIMIT 1");
        $st_p->execute([$raw_kode]);
        $p_info = $st_p->fetch(PDO::FETCH_ASSOC);
        if ($p_info) {
            $region_info = [
                'kode' => $p_info['kode'],
                'nama' => $p_info['nama'],
                'lat' => $p_info['lat'],
                'lng' => $p_info['lng'],
                'path' => null,
                'luas' => $p_info['luas'],
                'penduduk' => 0,
                'pria' => null,
                'wanita' => null
            ];
            $target = 'islands';
        } else {
            // Check basic wilayah table
            $st_w = $db->prepare("SELECT kode, nama FROM wilayah WHERE kode = ? LIMIT 1");
            $st_w->execute([$raw_kode]);
            $w_info = $st_w->fetch(PDO::FETCH_ASSOC);
            if ($w_info) {
                $region_info = [
                    'kode' => $w_info['kode'],
                    'nama' => $w_info['nama'],
                    'lat' => null,
                    'lng' => null,
                    'path' => null,
                    'luas' => 0,
                    'penduduk' => 0,
                    'pria' => null,
                    'wanita' => null
                ];
            }
        }
    }

    if ($region_info && !empty($region_info['nama'])) {
        $clean_name = sanitizeExportFilename($region_info['nama']);
    }
}

// --- Determine Export Filename & MIME Headers ---

$file_ext = ($format === 'kml') ? 'kml' : (($format === 'csv') ? 'csv' : 'geojson');
$safe_filename = "wilayah_{$safe_code}_{$clean_name}_{$target}.{$file_ext}";

$content_types = [
    'geojson' => 'application/geo+json; charset=utf-8',
    'kml'     => 'application/vnd.google-earth.kml+xml; charset=utf-8',
    'csv'     => 'text/csv; charset=utf-8'
];

header('Content-Type: ' . $content_types[$format]);
header('X-Content-Type-Options: nosniff');
if (!$is_preview) {
    header('Content-Disposition: attachment; filename="' . $safe_filename . '"');
}

// ============================================================
// EXPORT TARGET: ISLANDS
// ============================================================
if ($target === 'islands') {
    $sql = "SELECT kode, nama, lat, lng, status, luas, notes FROM wilayah_pulau";
    $params = [];
    if (!$is_all) {
        if (strlen($raw_kode) === 11) {
            $sql .= " WHERE kode = ?";
            $params[] = $raw_kode;
        } else {
            $sql .= " WHERE kode LIKE ?";
            $params[] = $raw_kode . '%';
        }
    }
    $sql .= " ORDER BY kode ASC";
    if ($is_all) {
        $sql .= " LIMIT 5000"; // Safeguard national island batch
    }

    $st = $db->prepare($sql);
    $st->execute($params);
    $islands = $st->fetchAll(PDO::FETCH_ASSOC);

    if ($format === 'csv') {
        echo "\xEF\xBB\xBF"; // UTF-8 BOM
        $out = fopen('php://output', 'w');
        fputcsv($out, ['kode', 'nama', 'lat', 'lng', 'status', 'luas_km2', 'notes']);
        foreach ($islands as $row) {
            fputcsv($out, [
                $row['kode'],
                $row['nama'],
                $row['lat'],
                $row['lng'],
                $row['status'],
                $row['luas'],
                $row['notes']
            ]);
        }
        fclose($out);
        exit;
    } elseif ($format === 'kml') {
        echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        echo '<kml xmlns="http://www.opengis.net/kml/2.2">' . "\n";
        echo '<Document>' . "\n";
        echo '  <name>' . htmlspecialchars("Daftar Pulau - {$clean_name} ({$safe_code})", ENT_XML1, 'UTF-8') . '</name>' . "\n";
        foreach ($islands as $row) {
            $lng = (float)$row['lng'];
            $lat = (float)$row['lat'];
            $name = htmlspecialchars($row['nama'], ENT_XML1, 'UTF-8');
            $desc = htmlspecialchars("Kode: {$row['kode']} | Status: {$row['status']} | Luas: {$row['luas']} km²", ENT_XML1, 'UTF-8');
            echo "  <Placemark>\n";
            echo "    <name>{$name}</name>\n";
            echo "    <description>{$desc}</description>\n";
            echo "    <Point><coordinates>{$lng},{$lat},0</coordinates></Point>\n";
            echo "  </Placemark>\n";
        }
        echo '</Document>' . "\n";
        echo '</kml>';
        exit;
    } else { // GeoJSON
        $features = [];
        foreach ($islands as $row) {
            $features[] = [
                'type' => 'Feature',
                'properties' => [
                    'kode'     => $row['kode'],
                    'nama'     => $row['nama'],
                    'status'   => $row['status'],
                    'luas_km2' => (float)$row['luas'],
                    'notes'    => $row['notes'],
                    'sumber'   => 'Kepmendagri No 300.2.2-2430 Tahun 2025'
                ],
                'geometry' => [
                    'type' => 'Point',
                    'coordinates' => [(float)$row['lng'], (float)$row['lat']]
                ]
            ];
        }
        echo json_encode([
            'type' => 'FeatureCollection',
            'features' => $features
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }
}

// ============================================================
// EXPORT TARGET: STATS / TABULAR DATA
// ============================================================
if ($target === 'stats') {
    // If national, export all 38 provinces; if province, export its regencies; if regency, export its districts
    $sql = "
        SELECT 
            w.kode, 
            w.nama, 
            w.lat, 
            w.lng, 
            COALESCE(l.luas, w.luas, 0) as luas,
            COALESCE(p.total, w.penduduk, 0) as penduduk,
            p.pria,
            p.wanita
        FROM wilayah_level_1_2 w
        LEFT JOIN wilayah_penduduk p ON w.kode = p.kode
        LEFT JOIN wilayah_luas l ON w.kode = l.kode
    ";
    $params = [];

    if ($is_all) {
        $sql .= " WHERE LENGTH(w.kode) = 2 ORDER BY w.kode ASC";
    } elseif (strlen($raw_kode) === 2) {
        $sql .= " WHERE w.kode LIKE ? AND LENGTH(w.kode) = 5 ORDER BY w.kode ASC";
        $params[] = $raw_kode . '.%';
    } else {
        $sql .= " WHERE w.kode = ? LIMIT 1";
        $params[] = $raw_kode;
    }

    $st = $db->prepare($sql);
    $st->execute($params);
    $stats = $st->fetchAll(PDO::FETCH_ASSOC);

    if ($format === 'csv') {
        echo "\xEF\xBB\xBF";
        $out = fopen('php://output', 'w');
        fputcsv($out, ['kode', 'nama', 'tipe', 'lat', 'lng', 'luas_km2', 'penduduk', 'pria', 'wanita', 'sex_ratio', 'kepadatan_jiwa_km2']);
        foreach ($stats as $row) {
            $luas = (float)$row['luas'];
            $pop = (int)$row['penduduk'];
            $pria = $row['pria'] !== null ? (int)$row['pria'] : null;
            $wanita = $row['wanita'] !== null ? (int)$row['wanita'] : null;
            $density = ($luas > 0) ? round($pop / $luas, 2) : 0;
            $sex_ratio = ($wanita > 0 && $pria !== null) ? round(($pria / $wanita) * 100, 2) : null;

            fputcsv($out, [
                $row['kode'],
                $row['nama'],
                getRegionTypeFromCode($row['kode']),
                $row['lat'],
                $row['lng'],
                $luas,
                $pop,
                $pria,
                $wanita,
                $sex_ratio,
                $density
            ]);
        }
        fclose($out);
        exit;
    } elseif ($format === 'kml') {
        echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        echo '<kml xmlns="http://www.opengis.net/kml/2.2">' . "\n";
        echo '<Document>' . "\n";
        echo '  <name>' . htmlspecialchars("Profil Statistik - {$clean_name} ({$safe_code})", ENT_XML1, 'UTF-8') . '</name>' . "\n";
        foreach ($stats as $row) {
            $name = htmlspecialchars($row['nama'], ENT_XML1, 'UTF-8');
            $luas = (float)$row['luas'];
            $pop = (int)$row['penduduk'];
            $density = ($luas > 0) ? round($pop / $luas, 2) : 0;
            $desc = htmlspecialchars("Kode: {$row['kode']} | Tipe: " . getRegionTypeFromCode($row['kode']) . " | Luas: {$luas} km² | Penduduk: {$pop} | Kepadatan: {$density} jiwa/km²", ENT_XML1, 'UTF-8');
            $lng = (float)$row['lng'];
            $lat = (float)$row['lat'];
            echo "  <Placemark>\n";
            echo "    <name>{$name}</name>\n";
            echo "    <description>{$desc}</description>\n";
            echo "    <Point><coordinates>{$lng},{$lat},0</coordinates></Point>\n";
            echo "  </Placemark>\n";
        }
        echo '</Document>' . "\n";
        echo '</kml>';
        exit;
    } else { // GeoJSON
        $features = [];
        foreach ($stats as $row) {
            $luas = (float)$row['luas'];
            $pop = (int)$row['penduduk'];
            $pria = $row['pria'] !== null ? (int)$row['pria'] : null;
            $wanita = $row['wanita'] !== null ? (int)$row['wanita'] : null;
            $density = ($luas > 0) ? round($pop / $luas, 2) : 0;
            $sex_ratio = ($wanita > 0 && $pria !== null) ? round(($pria / $wanita) * 100, 2) : null;

            $features[] = [
                'type' => 'Feature',
                'properties' => [
                    'kode'                => $row['kode'],
                    'nama'                => $row['nama'],
                    'tipe'                => getRegionTypeFromCode($row['kode']),
                    'luas_km2'            => $luas,
                    'penduduk'            => $pop,
                    'pria'                => $pria,
                    'wanita'              => $wanita,
                    'sex_ratio'           => $sex_ratio,
                    'kepadatan_jiwa_km2'  => $density,
                    'sumber'              => 'Kepmendagri No 300.2.2-2430 Tahun 2025'
                ],
                'geometry' => [
                    'type' => 'Point',
                    'coordinates' => [(float)$row['lng'], (float)$row['lat']]
                ]
            ];
        }
        echo json_encode([
            'type' => 'FeatureCollection',
            'features' => $features
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }
}

// ============================================================
// EXPORT TARGET: BOUNDARY (POLYGON)
// ============================================================

// National / All: export all 38 provinces boundaries
if ($is_all) {
    $st = $db->query("
        SELECT 
            w.kode, 
            w.nama, 
            w.lat, 
            w.lng, 
            w.path,
            COALESCE(l.luas, w.luas, 0) as luas,
            COALESCE(p.total, w.penduduk, 0) as penduduk,
            p.pria,
            p.wanita
        FROM wilayah_level_1_2 w
        LEFT JOIN wilayah_penduduk p ON w.kode = p.kode
        LEFT JOIN wilayah_luas l ON w.kode = l.kode
        WHERE LENGTH(w.kode) = 2
        ORDER BY w.kode ASC
    ");
    $regions = $st->fetchAll(PDO::FETCH_ASSOC);

    if ($format === 'kml') {
        echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        echo '<kml xmlns="http://www.opengis.net/kml/2.2">' . "\n";
        echo '<Document>' . "\n";
        echo '  <name>Batas Provinsi Seluruh Indonesia</name>' . "\n";
        foreach ($regions as $r) {
            $geomKml = pathToExportKmlGeometry($r['path']);
            if (empty($geomKml)) continue;
            $name = htmlspecialchars($r['nama'], ENT_XML1, 'UTF-8');
            $desc = htmlspecialchars("Kode: {$r['kode']} | Luas: {$r['luas']} km² | Penduduk: {$r['penduduk']} jiwa", ENT_XML1, 'UTF-8');
            echo "  <Placemark>\n";
            echo "    <name>{$name}</name>\n";
            echo "    <description>{$desc}</description>\n";
            echo "    {$geomKml}\n";
            echo "  </Placemark>\n";
        }
        echo '</Document>' . "\n";
        echo '</kml>';
        exit;
    } elseif ($format === 'csv') {
        echo "\xEF\xBB\xBF";
        $out = fopen('php://output', 'w');
        fputcsv($out, ['kode', 'nama', 'tipe', 'lat', 'lng', 'luas_km2', 'penduduk', 'has_polygon']);
        foreach ($regions as $r) {
            fputcsv($out, [
                $r['kode'],
                $r['nama'],
                'Provinsi',
                $r['lat'],
                $r['lng'],
                $r['luas'],
                $r['penduduk'],
                !empty($r['path']) ? 'Ya' : 'Tidak'
            ]);
        }
        fclose($out);
        exit;
    } else { // GeoJSON FeatureCollection
        $features = [];
        foreach ($regions as $r) {
            $geom = pathToExportGeoJsonGeometry($r['path']);
            if (!$geom) continue;
            $luas = (float)$r['luas'];
            $pop = (int)$r['penduduk'];
            $features[] = [
                'type' => 'Feature',
                'properties' => [
                    'kode'                => $r['kode'],
                    'nama'                => $r['nama'],
                    'tipe'                => 'Provinsi',
                    'luas_km2'            => $luas,
                    'penduduk'            => $pop,
                    'kepadatan_jiwa_km2'  => ($luas > 0) ? round($pop / $luas, 2) : 0,
                    'sumber'              => 'Kepmendagri No 300.2.2-2430 Tahun 2025'
                ],
                'geometry' => $geom
            ];
        }
        echo json_encode([
            'type' => 'FeatureCollection',
            'features' => $features
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }
}

// Single Region Boundary
if (!$region_info) {
    http_response_code(404);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['error' => 'Region not found'], JSON_UNESCAPED_UNICODE);
    exit;
}

$luas = (float)$region_info['luas'];
$pop = (int)$region_info['penduduk'];
$pria = $region_info['pria'] !== null ? (int)$region_info['pria'] : null;
$wanita = $region_info['wanita'] !== null ? (int)$region_info['wanita'] : null;
$density = ($luas > 0) ? round($pop / $luas, 2) : 0;
$sex_ratio = ($wanita > 0 && $pria !== null) ? round(($pria / $wanita) * 100, 2) : null;
$tipe = getRegionTypeFromCode($region_info['kode']);

if ($format === 'csv') {
    echo "\xEF\xBB\xBF";
    $out = fopen('php://output', 'w');
    fputcsv($out, ['kode', 'nama', 'tipe', 'lat', 'lng', 'luas_km2', 'penduduk', 'pria', 'wanita', 'sex_ratio', 'kepadatan_jiwa_km2']);
    fputcsv($out, [
        $region_info['kode'],
        $region_info['nama'],
        $tipe,
        $region_info['lat'],
        $region_info['lng'],
        $luas,
        $pop,
        $pria,
        $wanita,
        $sex_ratio,
        $density
    ]);
    fclose($out);
    exit;
} elseif ($format === 'kml') {
    $geomKml = pathToExportKmlGeometry($region_info['path']);
    if (empty($geomKml) && $region_info['lat'] !== null && $region_info['lng'] !== null) {
        $geomKml = "<Point><coordinates>{$region_info['lng']},{$region_info['lat']},0</coordinates></Point>";
    }

    echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
    echo '<kml xmlns="http://www.opengis.net/kml/2.2">' . "\n";
    echo '<Document>' . "\n";
    echo '  <name>' . htmlspecialchars("{$region_info['nama']} ({$region_info['kode']})", ENT_XML1, 'UTF-8') . '</name>' . "\n";
    echo "  <Placemark>\n";
    echo '    <name>' . htmlspecialchars($region_info['nama'], ENT_XML1, 'UTF-8') . '</name>' . "\n";
    echo '    <description>' . htmlspecialchars("Tipe: {$tipe} | Luas: {$luas} km² | Penduduk: {$pop} jiwa | Kepadatan: {$density} jiwa/km²", ENT_XML1, 'UTF-8') . "</description>\n";
    if (!empty($geomKml)) {
        echo "    {$geomKml}\n";
    }
    echo "  </Placemark>\n";
    echo '</Document>' . "\n";
    echo '</kml>';
    exit;
} else { // GeoJSON Feature
    $geometry = pathToExportGeoJsonGeometry($region_info['path']);
    if (!$geometry && $region_info['lat'] !== null && $region_info['lng'] !== null) {
        $geometry = [
            'type' => 'Point',
            'coordinates' => [(float)$region_info['lng'], (float)$region_info['lat']]
        ];
    }

    $feature = [
        'type' => 'Feature',
        'properties' => [
            'kode'                => $region_info['kode'],
            'nama'                => $region_info['nama'],
            'tipe'                => $tipe,
            'lat'                 => $region_info['lat'] !== null ? (float)$region_info['lat'] : null,
            'lng'                 => $region_info['lng'] !== null ? (float)$region_info['lng'] : null,
            'luas_km2'            => $luas,
            'penduduk'            => $pop,
            'pria'                => $pria,
            'wanita'              => $wanita,
            'sex_ratio'           => $sex_ratio,
            'kepadatan_jiwa_km2'  => $density,
            'sumber'              => 'Kepmendagri No 300.2.2-2430 Tahun 2025'
        ],
        'geometry' => $geometry
    ];

    echo json_encode($feature, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;
}
