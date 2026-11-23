<?php
/*
BISMILLAAHIRRAHMAANIRRAHIIM - In the Name of Allah, Most Gracious, Most Merciful
================================================================================
filename : GeoTimelineTest.php
purpose  : PHPUnit test suite for geo_timeline.php (Feature P7 Timeline)
create   : 2026-11-23
last edit: 2026-11-23 14:30:00
author   : cahya dsn
================================================================================
MIT License
copyright (c) 2017-2026 by cahya dsn; cahyadsn@gmail.com
================================================================================*/

declare(strict_types=1);

namespace Tests\Apps\Inc;

use PHPUnit\Framework\TestCase;

class GeoTimelineTest extends TestCase
{
    private string $geoTimelineFile;
    private string $cacheFile;

    protected function setUp(): void
    {
        $this->geoTimelineFile = dirname(__DIR__, 3) . '/apps/inc/geo_timeline.php';
        $this->cacheFile = dirname(__DIR__, 3) . '/apps/cache/timeline_snapshot.json';
    }

    protected function tearDown(): void
    {
        $_GET = [];
    }

    private function executeTimeline(array $params = []): array
    {
        $_GET = $params;

        ob_start();
        $code = file_get_contents($this->geoTimelineFile);
        $code = preg_replace('/declare\s*\(\s*strict_types\s*=\s*1\s*\)\s*;/', '', $code);
        $appsInc = str_replace('\\', '/', dirname($this->geoTimelineFile));
        $code = str_replace('__DIR__', "'" . $appsInc . "'", $code);
        $code = str_replace('header(', '@header(', $code);
        $code = preg_replace('/http_response_code\(([0-9]+)\);/', '$http_code = $1;', $code);
        $code = str_replace('exit;', 'return;', $code);
        $http_code = 200;

        $oldCwd = getcwd();
        chdir(dirname($this->geoTimelineFile));
        try {
            eval('?>' . $code);
        } finally {
            chdir($oldCwd);
        }
        $raw = ob_get_clean();

        $data = json_decode($raw, true) ?? [];
        return [
            'code' => $http_code,
            'data' => $data,
            'raw'  => $raw
        ];
    }

    public function testSnapshotJsonFileExistsAndValid(): void
    {
        $this->assertFileExists($this->cacheFile, 'timeline_snapshot.json must exist');
        $content = file_get_contents($this->cacheFile);
        $this->assertNotEmpty($content);

        $json = json_decode($content, true);
        $this->assertIsArray($json, 'timeline_snapshot.json must decode into valid array');
        $this->assertArrayHasKey('version', $json);
        $this->assertArrayHasKey('years', $json);
        $this->assertArrayHasKey('snapshots', $json);
        $this->assertArrayHasKey('dob_milestone', $json);
        $this->assertArrayHasKey('historical_paths', $json);

        $expectedYears = [2016, 2018, 2020, 2022, 2023, 2025];
        $this->assertEquals($expectedYears, $json['years']);
        $this->assertCount(6, $json['snapshots']);
    }

    public function testSnapshotHistoricalProgressionAndPapuaDob(): void
    {
        $json = json_decode(file_get_contents($this->cacheFile), true);
        $snapshots = $json['snapshots'];

        // Map by year
        $byYear = [];
        foreach ($snapshots as $s) {
            $byYear[$s['year']] = $s;
        }

        // 2016 Permendagri 56/2015
        $this->assertArrayHasKey(2016, $byYear);
        $this->assertSame(34, $byYear[2016]['total_provinsi']);
        $this->assertStringContainsString('56', $byYear[2016]['regulasi']);

        // 2022 Permendagri 58/2021
        $this->assertArrayHasKey(2022, $byYear);
        $this->assertSame(34, $byYear[2022]['total_provinsi']);

        // 2023 UU 14, 15, 16, 29 / 2022
        $this->assertArrayHasKey(2023, $byYear);
        $this->assertNotEmpty($byYear[2023]['new_regions']);
        $this->assertCount(4, $byYear[2023]['new_regions']);

        $dobCodes = array_column($byYear[2023]['new_regions'], 'kode');
        $this->assertContains('93', $dobCodes); // Papua Selatan
        $this->assertContains('94', $dobCodes); // Papua Tengah
        $this->assertContains('95', $dobCodes); // Papua Pegunungan
        $this->assertContains('96', $dobCodes); // Papua Barat Daya

        // 2025 Kepmendagri 300.2.2-2430 / 2025
        $this->assertArrayHasKey(2025, $byYear);
        $this->assertSame(38, $byYear[2025]['total_provinsi']);
        $this->assertSame(17374, $byYear[2025]['total_pulau']);
    }

    public function testActionOverviewDefault(): void
    {
        // Default without action is overview
        $res = $this->executeTimeline(['action' => 'overview']);
        $this->assertSame(200, $res['code']);
        $this->assertSame('success', $res['data']['status']);
        $this->assertArrayHasKey('snapshots', $res['data']);
        $this->assertCount(6, $res['data']['snapshots']);

        $first = $res['data']['snapshots'][0];
        $this->assertArrayHasKey('year', $first);
        $this->assertArrayHasKey('regulasi', $first);
        $this->assertArrayHasKey('total_provinsi', $first);
        $this->assertArrayHasKey('total_pulau', $first);
    }

    public function testActionDetailValidYear(): void
    {
        $res = $this->executeTimeline(['action' => 'detail', 'year' => '2020']);
        $this->assertSame(200, $res['code']);
        $this->assertSame('success', $res['data']['status']);
        $this->assertSame(2020, $res['data']['data']['year']);
        $this->assertSame(34, $res['data']['data']['total_provinsi']);
    }

    public function testActionDetailMissingOrInvalidYearReturns400(): void
    {
        // Missing year
        $res1 = $this->executeTimeline(['action' => 'detail']);
        $this->assertSame(400, $res1['code']);
        $this->assertSame('error', $res1['data']['status']);

        // Invalid year
        $res2 = $this->executeTimeline(['action' => 'detail', 'year' => '1995']);
        $this->assertSame(400, $res2['code']);
        $this->assertSame('error', $res2['data']['status']);
    }

    public function testActionDobReturnsFourPapuaProvinces(): void
    {
        $res = $this->executeTimeline(['action' => 'dob']);
        $this->assertSame(200, $res['code']);
        $this->assertSame('success', $res['data']['status']);
        $this->assertSame(2023, $res['data']['milestone_year']);
        $this->assertArrayHasKey('regions', $res['data']);
        $this->assertCount(4, $res['data']['regions']);

        $kodes = array_column($res['data']['regions'], 'kode');
        $this->assertEquals(['93', '94', '95', '96'], $kodes);
    }

    public function testActionPathsPreDobAndPostDob(): void
    {
        // Pre-DOB: 2022
        $resPre = $this->executeTimeline(['action' => 'paths', 'year' => '2022']);
        $this->assertSame(200, $resPre['code']);
        $this->assertSame('success', $resPre['data']['status']);
        $this->assertTrue($resPre['data']['data']['is_pre_dob']);
        $this->assertEquals(['91', '92'], $resPre['data']['data']['replaced_regions']);
        $this->assertNotNull($resPre['data']['data']['papua_2022']);
        $this->assertNotNull($resPre['data']['data']['papua_barat_2022']);

        // Post-DOB: 2025
        $resPost = $this->executeTimeline(['action' => 'paths', 'year' => '2025']);
        $this->assertSame(200, $resPost['code']);
        $this->assertSame('success', $resPost['data']['status']);
        $this->assertFalse($resPost['data']['data']['is_pre_dob']);
        $this->assertEmpty($resPost['data']['data']['replaced_regions']);

        // Invalid year for paths
        $resInv = $this->executeTimeline(['action' => 'paths', 'year' => '2010']);
        $this->assertSame(400, $resInv['code']);
    }

    public function testInvalidActionReturns400(): void
    {
        $res = $this->executeTimeline(['action' => 'nonexistent_action']);
        $this->assertSame(400, $res['code']);
        $this->assertSame('error', $res['data']['status']);
    }
}
