<?php
/*
BISMILLAAHIRRAHMAANIRRAHIIM - In the Name of Allah, Most Gracious, Most Merciful
================================================================================
filename : geo_helpers.php
purpose  : Shared geometric helper functions
create   : 2026-06-17
last edit: 2026-10-05 14:34:45
author   : cahya dsn
================================================================================
MIT License
copyright (c) 2026 by cahya dsn; cahyadsn@gmail.com
================================================================================*/

function fallbackBox($lat, $lng, $delta = 0.01) {
    return array(
        array((float)$lat - $delta, (float)$lng - $delta),
        array((float)$lat + $delta, (float)$lng - $delta),
        array((float)$lat + $delta, (float)$lng + $delta),
        array((float)$lat - $delta, (float)$lng + $delta)
    );
}

function fallbackPathForCode($lat, $lng, $kode) {
    $codeLen = strlen($kode);
    $delta = ($codeLen >= 13 ? 0.004 : ($codeLen >= 8 ? 0.008 : 0.01));
    return fallbackBox($lat, $lng, $delta);
}

function getProvinceOptionsHTML($db, $tbl_wilayah, $cache_file) {
    $cache_ttl = 86400;
    if (file_exists($cache_file) && (time() - filemtime($cache_file) < $cache_ttl)) {
        return file_get_contents($cache_file);
    }

    $query = $db->prepare("SELECT kode,nama FROM {$tbl_wilayah} WHERE CHAR_LENGTH(kode)=2 ORDER BY nama");
    $query->execute();
    $arr = [];
    while ($data = $query->fetchObject()) {
        $kode = htmlspecialchars($data->kode, ENT_QUOTES, 'UTF-8');
        $nama = htmlspecialchars($data->nama, ENT_QUOTES, 'UTF-8');
        $arr[] = '<option value="' . $kode . '">' . $nama . '</option>';
    }
    $html = implode('', $arr);
    file_put_contents($cache_file, $html, LOCK_EX);
    return $html;
}

function getIslandsForCode($db, $tbl_pulau, $kode) {
    if (empty($kode) || !is_string($kode)) {
        return [];
    }
    $n = strlen($kode);
    if ($n !== 2 && $n !== 5) {
        return [];
    }
    try {
        $query = $db->prepare("SELECT kode, nama, lat, lng, status, luas FROM {$tbl_pulau} WHERE kode LIKE CONCAT(:id, '.%') ORDER BY nama ASC");
        $query->execute([':id' => addcslashes($kode, '%_\\')]);
        $islands = [];
        while ($p = $query->fetchObject()) {
            $islands[] = [
                'kode' => (string)$p->kode,
                'nama' => (string)$p->nama,
                'lat' => ($p->lat !== null && $p->lat !== '') ? (float)$p->lat : null,
                'lng' => ($p->lng !== null && $p->lng !== '') ? (float)$p->lng : null,
                'status' => $p->status !== null ? (string)$p->status : '',
                'luas' => ($p->luas !== null && $p->luas !== '') ? (float)$p->luas : null
            ];
        }
        return $islands;
    } catch (\Exception $e) {
        return [];
    }
}

