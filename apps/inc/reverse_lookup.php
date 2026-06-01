<?php
/*
Reverse lookup wilayah by clicked map coordinate.
Prefer polygon containment on a small centroid-nearest candidate set, then fallback to nearest centroid.
*/
require_once "db.php";
header('Content-Type: application/json');

function pointInRing($lat, $lng, $ring) {
    $inside = false;
    $count = count($ring);
    if ($count < 3) return false;

    for ($i = 0, $j = $count - 1; $i < $count; $j = $i++) {
        $yi = (float) $ring[$i][0];
        $xi = (float) $ring[$i][1];
        $yj = (float) $ring[$j][0];
        $xj = (float) $ring[$j][1];

        $intersect = (($yi > $lat) !== ($yj > $lat))
            && ($lng < ($xj - $xi) * ($lat - $yi) / (($yj - $yi) ?: 1e-12) + $xi);
        if ($intersect) $inside = !$inside;
    }

    return $inside;
}

function pointInPath($lat, $lng, $pathJson) {
    if (empty($pathJson)) return false;
    $coords = json_decode($pathJson, true);
    if (!is_array($coords) || empty($coords)) return false;

    if (isset($coords[0][0]) && is_numeric($coords[0][0])) {
        return pointInRing($lat, $lng, $coords);
    }

    foreach ($coords as $ring) {
        if (is_array($ring) && pointInRing($lat, $lng, $ring)) return true;
    }

    return false;
}

function pathLooksNearCentroid($pathJson, $lat, $lng, $kode) {
    if (empty($pathJson)) return false;
    $coords = json_decode($pathJson, true);
    if (!is_array($coords) || empty($coords)) return false;
    $points = (isset($coords[0][0]) && is_numeric($coords[0][0])) ? $coords : (is_array($coords[0]) ? $coords[0] : array());
    if (empty($points)) return false;

    $latMin = $latMax = (float) $points[0][0];
    $lngMin = $lngMax = (float) $points[0][1];
    foreach ($points as $pt) {
        if (!is_array($pt) || count($pt) < 2) continue;
        $plat = (float) $pt[0];
        $plng = (float) $pt[1];
        if ($plat < $latMin) $latMin = $plat;
        if ($plat > $latMax) $latMax = $plat;
        if ($plng < $lngMin) $lngMin = $plng;
        if ($plng > $lngMax) $lngMax = $plng;
    }

    $centerLat = ($latMin + $latMax) / 2;
    $centerLng = ($lngMin + $lngMax) / 2;
    $codeLen = strlen($kode);
    $threshold = ($codeLen >= 13 ? 0.03 : ($codeLen >= 8 ? 0.08 : 2.5));

    return abs($centerLat - (float) $lat) <= $threshold && abs($centerLng - (float) $lng) <= $threshold;
}

function fallbackPathForCode($lat, $lng, $kode) {
    $codeLen = strlen($kode);
    $delta = ($codeLen >= 13 ? 0.004 : ($codeLen >= 8 ? 0.008 : 0.01));
    return json_encode(array(
        array($lat - $delta, $lng - $delta),
        array($lat + $delta, $lng - $delta),
        array($lat + $delta, $lng + $delta),
        array($lat - $delta, $lng + $delta)
    ));
}

function effectiveCandidatePath($candidate) {
    if (!empty($candidate['path'])
        && pathLooksNearCentroid($candidate['path'], $candidate['lat'], $candidate['lng'], $candidate['kode'])) {
        return $candidate['path'];
    }

    if (isset($candidate['lat'], $candidate['lng'], $candidate['kode'])) {
        return fallbackPathForCode((float) $candidate['lat'], (float) $candidate['lng'], $candidate['kode']);
    }

    return null;
}

function buildChain($kode, $names) {
    $prov = substr($kode, 0, 2);
    $kab = strlen($kode) >= 5 ? substr($kode, 0, 5) : null;
    $kec = strlen($kode) >= 8 ? substr($kode, 0, 8) : null;
    $kel = strlen($kode) >= 13 ? substr($kode, 0, 13) : null;

    return array(
        'prov' => array('kode' => $prov, 'nama' => isset($names[$prov]) ? $names[$prov] : null),
        'kab' => $kab ? array('kode' => $kab, 'nama' => isset($names[$kab]) ? $names[$kab] : null) : null,
        'kec' => $kec ? array('kode' => $kec, 'nama' => isset($names[$kec]) ? $names[$kec] : null) : null,
        'kel' => $kel ? array('kode' => $kel, 'nama' => isset($names[$kel]) ? $names[$kel] : null) : null,
    );
}

$response = array('status' => false, 'error' => 'invalid request');
$lat = isset($_GET['lat']) ? (float) $_GET['lat'] : null;
$lng = isset($_GET['lng']) ? (float) $_GET['lng'] : null;

if ($lat === null || $lng === null) {
    echo json_encode($response);
    exit;
}

try {
    $sql = "SELECT kode, nama, lat, lng, path,
                   (6371 * ACOS(
                       COS(RADIANS(:lat)) * COS(RADIANS(lat)) * COS(RADIANS(lng) - RADIANS(:lng)) +
                       SIN(RADIANS(:lat)) * SIN(RADIANS(lat))
                   )) AS distance_km
            FROM wilayah_level_1_2
            WHERE lat IS NOT NULL AND lng IS NOT NULL
            ORDER BY distance_km ASC
            LIMIT 30";

    $query = $db->prepare($sql);
    $query->execute(array(':lat' => $lat, ':lng' => $lng));
    $candidates = $query->fetchAll(PDO::FETCH_ASSOC);

    if (!$candidates) {
        echo json_encode(array('status' => false, 'error' => 'location not found'));
        exit;
    }

    $nearest = $candidates[0];
    $matched = null;
    foreach ($candidates as $candidate) {
        $candidatePath = effectiveCandidatePath($candidate);
        if (!empty($candidatePath) && pointInPath($lat, $lng, $candidatePath)) {
            $matched = $candidate;
            break;
        }
    }
    $picked = $matched ?: $nearest;

    $kode = $picked['kode'];
    $prov = substr($kode, 0, 2);
    $kab = strlen($kode) >= 5 ? substr($kode, 0, 5) : null;
    $kec = strlen($kode) >= 8 ? substr($kode, 0, 8) : null;
    $kel = strlen($kode) >= 13 ? substr($kode, 0, 13) : null;

    $codes = array_filter(array($prov, $kab, $kec, $kel));
    $placeholders = implode(',', array_fill(0, count($codes), '?'));
    $nameQuery = $db->prepare("SELECT kode, nama FROM {$tbl_wilayah} WHERE kode IN ($placeholders)");
    $nameQuery->execute(array_values($codes));
    $names = array();
    while ($row = $nameQuery->fetch(PDO::FETCH_ASSOC)) {
        $names[$row['kode']] = $row['nama'];
    }

    $response = array(
        'status' => true,
        'data' => array(
            'clicked' => array('lat' => $lat, 'lng' => $lng),
            'nearest' => array(
                'kode' => $picked['kode'],
                'nama' => $picked['nama'],
                'lat' => (float) $picked['lat'],
                'lng' => (float) $picked['lng'],
                'distance_km' => isset($picked['distance_km']) ? round((float) $picked['distance_km'], 3) : null,
                'match_type' => $matched ? 'polygon' : 'centroid'
            ),
            'chain' => buildChain($picked['kode'], $names),
        )
    );
} catch (Throwable $e) {
    $response = array('status' => false, 'error' => $e->getMessage());
}

echo json_encode($response);
