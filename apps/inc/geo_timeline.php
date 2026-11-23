<?php
/*
BISMILLAAHIRRAHMAANIRRAHIIM - In the Name of Allah, Most Gracious, Most Merciful
================================================================================
filename : geo_timeline.php
purpose  : Historical Regional Evolution Timeline Data Provider (Feature P7)
create   : 2026-11-23
last edit: 2026-11-23 14:30:00
author   : cahya dsn
================================================================================
MIT License
copyright (c) 2017-2026 by cahya dsn; cahyadsn@gmail.com
================================================================================*/

declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');
header('Cache-Control: public, max-age=86400');

$cacheFile = dirname(__DIR__) . '/cache/timeline_snapshot.json';

if (!file_exists($cacheFile)) {
    // If cache does not exist, attempt to rebuild via tool if available
    $builderFile = dirname(dirname(__DIR__)) . '/tools/build_timeline_snapshot.php';
    if (file_exists($builderFile)) {
        @include_once $builderFile;
    }
}

$rawJson = file_exists($cacheFile) ? file_get_contents($cacheFile) : null;
$timelineData = $rawJson ? json_decode($rawJson, true) : null;

if (!$timelineData || !isset($timelineData['snapshots'])) {
    http_response_code(500);
    echo json_encode([
        'status'  => 'error',
        'message' => 'Timeline snapshot data unavailable'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$action = isset($_GET['action']) && is_string($_GET['action']) ? strtolower(trim($_GET['action'])) : 'overview';
$yearParam = isset($_GET['year']) ? (int)$_GET['year'] : null;

$validYears = $timelineData['years'] ?? [2016, 2018, 2020, 2022, 2023, 2025];
$validActions = ['overview', 'detail', 'dob', 'paths'];

if (!in_array($action, $validActions, true)) {
    http_response_code(400);
    echo json_encode([
        'status'  => 'error',
        'message' => 'Invalid action parameter. Allowed actions: ' . implode(', ', $validActions)
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// 1. Action: DOB (Daerah Otonomi Baru Papua 2022/2023)
if ($action === 'dob') {
    $dobSnapshot = null;
    foreach ($timelineData['snapshots'] as $snap) {
        if ($snap['year'] === 2023 && !empty($snap['new_regions'])) {
            $dobSnapshot = $snap;
            break;
        }
    }

    echo json_encode([
        'status'        => 'success',
        'milestone_year'=> 2023,
        'title'         => 'Pemekaran 4 Provinsi Baru di Tanah Papua',
        'dasar_hukum'   => 'UU No. 14, 15, 16, dan 29 Tahun 2022',
        'penetapan'     => '2022-11-09',
        'regions'       => $dobSnapshot['new_regions'] ?? []
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

// 2. Action: Paths (Historical polygon paths for map time-travel)
if ($action === 'paths') {
    if (!$yearParam || !in_array($yearParam, $validYears, true)) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Valid year parameter required for paths.'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // Pre-2023 years (2016, 2018, 2020, 2022) had unified Papua (91) & Papua Barat (92)
    $isPreDob = ($yearParam < 2023);
    $paths = [];

    if ($isPreDob && !empty($timelineData['historical_paths'])) {
        $paths = [
            'is_pre_dob' => true,
            'replaced_regions' => ['91', '92'],
            'papua_2022' => $timelineData['historical_paths']['papua_2022'] ? json_decode($timelineData['historical_paths']['papua_2022'], true) : null,
            'papua_barat_2022' => $timelineData['historical_paths']['papua_barat_2022'] ? json_decode($timelineData['historical_paths']['papua_barat_2022'], true) : null
        ];
    } else {
        $paths = [
            'is_pre_dob' => false,
            'replaced_regions' => []
        ];
    }

    echo json_encode([
        'status' => 'success',
        'year'   => $yearParam,
        'data'   => $paths
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

// 3. Action: Detail for a specific year
if ($action === 'detail' || $yearParam !== null) {
    if (!$yearParam || !in_array($yearParam, $validYears, true)) {
        http_response_code(400);
        echo json_encode([
            'status'  => 'error',
            'message' => 'Invalid or missing year parameter. Allowed years: ' . implode(', ', $validYears)
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $found = null;
    foreach ($timelineData['snapshots'] as $snap) {
        if ($snap['year'] === $yearParam) {
            $found = $snap;
            break;
        }
    }

    if (!$found) {
        http_response_code(404);
        echo json_encode(['status' => 'error', 'message' => 'Year snapshot not found'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    echo json_encode([
        'status' => 'success',
        'data'   => $found
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

// 4. Default / Action: Overview
$overviewList = [];
foreach ($timelineData['snapshots'] as $snap) {
    $overviewList[] = [
        'year'              => $snap['year'],
        'regulasi'          => $snap['regulasi'],
        'short_title'       => $snap['short_title'] ?? $snap['regulasi'],
        'tanggal_penetapan' => $snap['tanggal_penetapan'] ?? '',
        'total_provinsi'    => $snap['total_provinsi'],
        'total_kab_kota'    => $snap['total_kab_kota'],
        'total_kab'         => $snap['total_kab'] ?? 0,
        'total_kota'        => $snap['total_kota'] ?? 0,
        'total_kecamatan'   => $snap['total_kecamatan'],
        'total_desa_kel'    => $snap['total_desa_kel'],
        'total_desa'        => $snap['total_desa'] ?? 0,
        'total_kelurahan'   => $snap['total_kelurahan'] ?? 0,
        'total_pulau'       => $snap['total_pulau'],
        'highlights'        => $snap['highlights'],
        'doc_url'           => $snap['doc_url'] ?? '',
        'has_dob'           => $snap['has_dob'] ?? false,
        'new_regions_count' => count($snap['new_regions'] ?? [])
    ];
}

echo json_encode([
    'status'       => 'success',
    'version'      => $timelineData['version'] ?? '1.0.0',
    'years'        => $validYears,
    'dob_milestone'=> $timelineData['dob_milestone'] ?? 2023,
    'snapshots'    => $overviewList
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
exit;
