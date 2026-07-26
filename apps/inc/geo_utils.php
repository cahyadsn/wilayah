<?php
/*
BISMILLAAHIRRAHMAANIRRAHIIM - In the Name of Allah, Most Gracious, Most Merciful
================================================================================
filename : geo_utils.php
purpose  : shared geo utility functions
author   : cahya dsn
================================================================================
This program is free software; you can redistribute it and/or modify it under the
terms of the MIT License.
================================================================================*/

function isPathNearCentroid($path, $lat, $lng, $kode) {
    if (empty($path) || $lat === null || $lng === null) return false;
    $coords = is_string($path) ? json_decode($path, true) : $path;
    if (!is_array($coords) || empty($coords)) return false;

    $points = (isset($coords[0][0]) && is_numeric($coords[0][0])) ? $coords : (is_array($coords[0]) ? $coords[0] : array());
    if (empty($points)) return false;

    $lats = array_column($points, 0);
    $lngs = array_column($points, 1);

    if (count($lats) !== count($points) || count($lngs) !== count($points)) {
        $latMin = $latMax = (float)($points[0][0] ?? 0);
        $lngMin = $lngMax = (float)($points[0][1] ?? 0);
        foreach ($points as $pt) {
            if (!is_array($pt) || count($pt) < 2) continue;
            $plat = (float)$pt[0];
            $plng = (float)$pt[1];
            if ($plat < $latMin) $latMin = $plat;
            if ($plat > $latMax) $latMax = $plat;
            if ($plng < $lngMin) $lngMin = $plng;
            if ($plng > $lngMax) $lngMax = $plng;
        }
    } else {
        $latMin = (float)min($lats);
        $latMax = (float)max($lats);
        $lngMin = (float)min($lngs);
        $lngMax = (float)max($lngs);
    }

    $centerLat = ($latMin + $latMax) / 2;
    $centerLng = ($lngMin + $lngMax) / 2;
    $codeLen = strlen($kode);
    $threshold = ($codeLen >= 13 ? 0.03 : ($codeLen >= 8 ? 0.08 : 2.5));

    return abs($centerLat - (float)$lat) <= $threshold && abs($centerLng - (float)$lng) <= $threshold;
}
