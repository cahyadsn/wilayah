<?php
/*
BISMILLAAHIRRAHMAANIRRAHIIM - In the Name of Allah, Most Gracious, Most Merciful
================================================================================
filename : geo_search.php
purpose  : Fast global search autocomplete for regions and islands
create   : 2026-11-10
last edit: 2026-11-10 08:26:00
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

$response = [
    'status'  => false,
    'query'   => '',
    'count'   => 0,
    'results' => []
];

$rawQuery = isset($_GET['q']) && is_string($_GET['q']) ? trim($_GET['q']) : '';
$output = null;

// Validation: minimum 2 chars, max 50 chars, safe characters only
if (mb_strlen($rawQuery, 'UTF-8') < 2 || mb_strlen($rawQuery, 'UTF-8') > 50 || !preg_match('/^[\p{L}\p{N}\s\.\-\']+$/u', $rawQuery)) {
    $output = json_encode($response, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);
} else {
    $limit = isset($_GET['limit']) && is_numeric($_GET['limit']) ? (int)$_GET['limit'] : 8;
    if ($limit < 1) $limit = 1;
    if ($limit > 15) $limit = 15;

    $cache_dir = dirname(__DIR__) . '/cache';
    if (!is_dir($cache_dir)) {
        @mkdir($cache_dir, 0755, true);
    }

    $cache_key = hash('sha256', 'search_' . mb_strtolower($rawQuery, 'UTF-8') . '_' . $limit);
    $cache_file = $cache_dir . '/search_cache_' . $cache_key . '.json';
    $cache_ttl = 86400; // 24 hours

    if (file_exists($cache_file) && (time() - filemtime($cache_file) < $cache_ttl)) {
        $cached = file_get_contents($cache_file);
        if ($cached !== false) {
            $output = $cached;
        }
    }

    if ($output === null) {
        try {
            $term = $rawQuery;
            $escaped = addcslashes($term, '%_\\');
            $isCodeQuery = preg_match('/^[0-9]/', $term);

            $wilayahRows = [];
            $islandRows = [];

            if ($isCodeQuery) {
                // Search by code
                $stmtW = $db->prepare("
                    SELECT kode, nama, 'wilayah' AS category 
                    FROM wilayah 
                    WHERE kode LIKE :prefix_code 
                    ORDER BY CHAR_LENGTH(kode) ASC, kode ASC 
                    LIMIT :limit
                ");
                $stmtW->bindValue(':prefix_code', $escaped . '%', PDO::PARAM_STR);
                $stmtW->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
                $stmtW->execute();
                $wilayahRows = $stmtW->fetchAll(PDO::FETCH_ASSOC);

                // Search island by code (e.g. 11.01.40001)
                $stmtP = $db->prepare("
                    SELECT kode, nama, lat, lng, status, luas, 'pulau' AS category 
                    FROM wilayah_pulau 
                    WHERE kode LIKE :prefix_code 
                    ORDER BY kode ASC 
                    LIMIT 4
                ");
                $stmtP->bindValue(':prefix_code', $escaped . '%', PDO::PARAM_STR);
                $stmtP->execute();
                $islandRows = $stmtP->fetchAll(PDO::FETCH_ASSOC);
            } else {
                // 1. Search by name prefix first (indexed)
                $stmtW1 = $db->prepare("
                    SELECT kode, nama, 'wilayah' AS category 
                    FROM wilayah 
                    WHERE nama LIKE :prefix 
                    ORDER BY CHAR_LENGTH(kode) ASC, nama ASC 
                    LIMIT :limit
                ");
                $stmtW1->bindValue(':prefix', $escaped . '%', PDO::PARAM_STR);
                $stmtW1->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
                $stmtW1->execute();
                $wilayahRows = $stmtW1->fetchAll(PDO::FETCH_ASSOC);

                // If fewer results than limit, search contains
                $countW = count($wilayahRows);
                if ($countW < $limit) {
                    $remaining = $limit - $countW;
                    $excludeKodes = array_column($wilayahRows, 'kode');

                    if (empty($excludeKodes)) {
                        $stmtW2 = $db->prepare("
                            SELECT kode, nama, 'wilayah' AS category 
                            FROM wilayah 
                            WHERE nama LIKE :contain 
                            ORDER BY CHAR_LENGTH(kode) ASC, nama ASC 
                            LIMIT :limit
                        ");
                        $stmtW2->bindValue(':contain', '%' . $escaped . '%', PDO::PARAM_STR);
                        $stmtW2->bindValue(':limit', (int)$remaining, PDO::PARAM_INT);
                        $stmtW2->execute();
                        $moreW = $stmtW2->fetchAll(PDO::FETCH_ASSOC);
                    } else {
                        $placeholders = implode(',', array_fill(0, count($excludeKodes), '?'));
                        $stmtW2 = $db->prepare("
                            SELECT kode, nama, 'wilayah' AS category 
                            FROM wilayah 
                            WHERE nama LIKE ? AND kode NOT IN ($placeholders) 
                            ORDER BY CHAR_LENGTH(kode) ASC, nama ASC 
                            LIMIT " . (int)$remaining
                        );
                        $params = array_merge(['%' . $escaped . '%'], $excludeKodes);
                        $stmtW2->execute($params);
                        $moreW = $stmtW2->fetchAll(PDO::FETCH_ASSOC);
                    }
                    $wilayahRows = array_merge($wilayahRows, $moreW);
                }

                // Search island by name
                $stmtP1 = $db->prepare("
                    SELECT kode, nama, lat, lng, status, luas, 'pulau' AS category 
                    FROM wilayah_pulau 
                    WHERE nama LIKE :prefix 
                    ORDER BY nama ASC 
                    LIMIT 3
                ");
                $stmtP1->bindValue(':prefix', $escaped . '%', PDO::PARAM_STR);
                $stmtP1->execute();
                $islandRows = $stmtP1->fetchAll(PDO::FETCH_ASSOC);

                if (count($islandRows) < 3) {
                    $remIsland = 3 - count($islandRows);
                    $excludeIslandKodes = array_column($islandRows, 'kode');
                    if (empty($excludeIslandKodes)) {
                        $stmtP2 = $db->prepare("
                            SELECT kode, nama, lat, lng, status, luas, 'pulau' AS category 
                            FROM wilayah_pulau 
                            WHERE nama LIKE :contain 
                            ORDER BY nama ASC 
                            LIMIT :limit
                        ");
                        $stmtP2->bindValue(':contain', '%' . $escaped . '%', PDO::PARAM_STR);
                        $stmtP2->bindValue(':limit', (int)$remIsland, PDO::PARAM_INT);
                        $stmtP2->execute();
                        $moreP = $stmtP2->fetchAll(PDO::FETCH_ASSOC);
                    } else {
                        $pPlaceholders = implode(',', array_fill(0, count($excludeIslandKodes), '?'));
                        $stmtP2 = $db->prepare("
                            SELECT kode, nama, lat, lng, status, luas, 'pulau' AS category 
                            FROM wilayah_pulau 
                            WHERE nama LIKE ? AND kode NOT IN ($pPlaceholders) 
                            ORDER BY nama ASC 
                            LIMIT " . (int)$remIsland
                        );
                        $pParams = array_merge(['%' . $escaped . '%'], $excludeIslandKodes);
                        $stmtP2->execute($pParams);
                        $moreP = $stmtP2->fetchAll(PDO::FETCH_ASSOC);
                    }
                    $islandRows = array_merge($islandRows, $moreP);
                }
            }

            $allRows = array_merge($wilayahRows, $islandRows);

            if (!empty($allRows)) {
                // Collect ancestor codes to fetch parent names
                $ancestorCodes = [];
                foreach ($allRows as $row) {
                    $kode = $row['kode'];
                    $len = strlen($kode);
                    if ($row['category'] === 'pulau') {
                        $parts = explode('.', $kode);
                        if (isset($parts[0])) $ancestorCodes[$parts[0]] = true;
                        if (isset($parts[0], $parts[1])) $ancestorCodes[$parts[0] . '.' . $parts[1]] = true;
                    } else {
                        if ($len >= 5) $ancestorCodes[substr($kode, 0, 2)] = true;
                        if ($len >= 8) $ancestorCodes[substr($kode, 0, 5)] = true;
                        if ($len >= 13) $ancestorCodes[substr($kode, 0, 8)] = true;
                    }
                }

                $names = [];
                if (!empty($ancestorCodes)) {
                    $placeholders = implode(',', array_fill(0, count($ancestorCodes), '?'));
                    $stmtA = $db->prepare("SELECT kode, nama FROM wilayah WHERE kode IN ($placeholders)");
                    $stmtA->execute(array_keys($ancestorCodes));
                    while ($a = $stmtA->fetch(PDO::FETCH_ASSOC)) {
                        $names[$a['kode']] = $a['nama'];
                    }
                }

                $results = [];
                foreach ($allRows as $row) {
                    $kode = $row['kode'];
                    $category = $row['category'];
                    $parentParts = [];
                    $chain = [];

                    if ($category === 'pulau') {
                        $parts = explode('.', $kode);
                        $provKode = $parts[0] ?? null;
                        $kabKode = isset($parts[0], $parts[1]) ? $parts[0] . '.' . $parts[1] : null;

                        if ($provKode && isset($names[$provKode])) {
                            $chain['prov'] = ['kode' => $provKode, 'nama' => $names[$provKode]];
                        }
                        if ($kabKode && isset($names[$kabKode])) {
                            $chain['kab'] = ['kode' => $kabKode, 'nama' => $names[$kabKode]];
                            $parentParts[] = $names[$kabKode];
                        }
                        if ($provKode && isset($names[$provKode])) {
                            $parentParts[] = $names[$provKode];
                        }

                        $statusLabel = !empty($row['status']) ? ' (' . $row['status'] . ')' : '';
                        $results[] = [
                            'kode'     => $kode,
                            'nama'     => $row['nama'],
                            'category' => 'pulau',
                            'tipe'     => 'Pulau' . $statusLabel,
                            'parent'   => implode(', ', $parentParts),
                            'lat'      => $row['lat'] !== null ? (float)$row['lat'] : null,
                            'lng'      => $row['lng'] !== null ? (float)$row['lng'] : null,
                            'status'   => $row['status'] ?? null,
                            'luas'     => $row['luas'] !== null ? (float)$row['luas'] : null,
                            'chain'    => $chain
                        ];
                    } else {
                        $len = strlen($kode);
                        $provKode = $len >= 2 ? substr($kode, 0, 2) : null;
                        $kabKode = $len >= 5 ? substr($kode, 0, 5) : null;
                        $kecKode = $len >= 8 ? substr($kode, 0, 8) : null;
                        $kelKode = $len >= 13 ? substr($kode, 0, 13) : null;

                        if ($provKode && isset($names[$provKode])) {
                            $chain['prov'] = ['kode' => $provKode, 'nama' => $names[$provKode]];
                        }
                        if ($kabKode && isset($names[$kabKode])) {
                            $chain['kab'] = ['kode' => $kabKode, 'nama' => $names[$kabKode]];
                        }
                        if ($kecKode && isset($names[$kecKode])) {
                            $chain['kec'] = ['kode' => $kecKode, 'nama' => $names[$kecKode]];
                        }
                        if ($kelKode) {
                            $chain['kel'] = ['kode' => $kelKode, 'nama' => $row['nama']];
                        }

                        $tipe = 'Wilayah';
                        if ($len === 2) {
                            $tipe = 'Provinsi';
                            $parentParts[] = 'Indonesia';
                        } elseif ($len === 5) {
                            $tipe = (stripos($row['nama'], 'KOTA') === 0) ? 'Kota' : 'Kabupaten';
                            if ($provKode && isset($names[$provKode])) $parentParts[] = $names[$provKode];
                        } elseif ($len === 8) {
                            $tipe = 'Kecamatan';
                            if ($kabKode && isset($names[$kabKode])) $parentParts[] = $names[$kabKode];
                            if ($provKode && isset($names[$provKode])) $parentParts[] = $names[$provKode];
                        } elseif ($len === 13) {
                            $tipe = 'Desa/Kelurahan';
                            if ($kecKode && isset($names[$kecKode])) $parentParts[] = 'Kec. ' . $names[$kecKode];
                            if ($kabKode && isset($names[$kabKode])) $parentParts[] = $names[$kabKode];
                            if ($provKode && isset($names[$provKode])) $parentParts[] = $names[$provKode];
                        }

                        $results[] = [
                            'kode'     => $kode,
                            'nama'     => $row['nama'],
                            'category' => 'wilayah',
                            'tipe'     => $tipe,
                            'parent'   => implode(', ', $parentParts),
                            'chain'    => $chain
                        ];
                    }
                }

                $response['status']  = true;
                $response['query']   = $term;
                $response['count']   = count($results);
                $response['results'] = $results;
            } else {
                $response['status']  = true;
                $response['query']   = $term;
                $response['count']   = 0;
                $response['results'] = [];
            }
        } catch (Exception $e) {
            error_log('geo_search error: ' . $e->getMessage());
            $response = [
                'status'  => false,
                'query'   => $rawQuery,
                'count'   => 0,
                'results' => [],
                'error'   => 'An error occurred during search'
            ];
        }

        $output = json_encode($response, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);
        if ($response['status'] && !empty($cache_file)) {
            @file_put_contents($cache_file, $output, LOCK_EX);
        }
    }
}

if (!headers_sent()) {
    header('Content-Type: application/json; charset=utf-8');
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: SAMEORIGIN');
    header('Referrer-Policy: strict-origin-when-cross-origin');
}

echo $output;
