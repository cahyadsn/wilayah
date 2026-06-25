<?php
/*
BISMILLAAHIRRAHMAANIRRAHIIM - In the Name of Allah, Most Gracious, Most Merciful
================================================================================
filename : geo_helpers.php
purpose  : Shared geometric helper functions
================================================================================
*/

function fallbackBox($lat, $lng, $delta = 0.01) {
    return json_encode(array(
        array((float)$lat - $delta, (float)$lng - $delta),
        array((float)$lat + $delta, (float)$lng - $delta),
        array((float)$lat + $delta, (float)$lng + $delta),
        array((float)$lat - $delta, (float)$lng + $delta)
    ));
}

function fallbackPathForCode($lat, $lng, $kode) {
    $codeLen = strlen($kode);
    $delta = ($codeLen >= 13 ? 0.004 : ($codeLen >= 8 ? 0.008 : 0.01));
    return fallbackBox($lat, $lng, $delta);
}
