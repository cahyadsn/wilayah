<?php
/*
BISMILLAAHIRRAHMAANIRRAHIIM - In the Name of Allah, Most Gracious, Most Merciful
================================================================================
filename : GeoThematicTest.php
purpose  : PHPUnit test suite for geo_thematic.php (P3 Thematic Choropleth Map)
create   : 2026-11-12
last edit: 2026-11-12 08:55:00
author   : cahya dsn
================================================================================
MIT License
copyright (c) 2017-2026 by cahya dsn; cahyadsn@gmail.com
================================================================================*/

namespace Tests\Apps\Inc;

use PHPUnit\Framework\TestCase;
use PDO;

class GeoThematicTest extends TestCase
{
    private string $geoThematicFile;

    protected function setUp(): void
    {
        $this->geoThematicFile = realpath(__DIR__ . '/../../../apps/inc/geo_thematic.php');
        $_GET = [];

        // Ensure database connection is ready
        if (!isset($GLOBALS['db']) || !($GLOBALS['db'] instanceof PDO)) {
            require __DIR__ . '/../../../apps/inc/db.php';
            if (isset($db) && $db instanceof PDO) {
                $GLOBALS['db'] = $db;
            }
        }
    }

    protected function tearDown(): void
    {
        $_GET = [];
    }

    private function executeThematic(array $params = []): array
    {
        global $db;
        if (isset($GLOBALS['db'])) {
            $db = $GLOBALS['db'];
        }
        $_GET = $params;

        ob_start();
        $code = file_get_contents($this->geoThematicFile);
        $appsInc = str_replace('\\', '/', dirname($this->geoThematicFile));
        $code = str_replace('__DIR__', "'" . $appsInc . "'", $code);
        $code = str_replace('header(', '@header(', $code);
        $code = preg_replace('/http_response_code\(([0-9]+)\);/', '$http_code = $1;', $code);
        $code = str_replace('readfile(', 'echo file_get_contents(', $code);
        $code = str_replace('exit;', 'return;', $code);
        $http_code = 200;

        $oldCwd = getcwd();
        chdir(dirname($this->geoThematicFile));
        try {
            eval('?>' . $code);
        } finally {
            chdir($oldCwd);
        }
        $raw = ob_get_clean();

        return [
            'code' => $http_code,
            'json' => json_decode($raw, true),
            'raw' => $raw
        ];
    }

    public function testInvalidProvinceCodeReturns400(): void
    {
        $invalidCodes = ['abc', '1', '123', '32.01', '<script>', '..', '3a'];
        foreach ($invalidCodes as $code) {
            $res = $this->executeThematic(['prov' => $code]);
            $this->assertEquals(400, $res['code'], "Expected 400 for code: {$code}");
            $this->assertIsArray($res['json']);
            $this->assertArrayHasKey('error', $res['json']);
            $this->assertEquals('Invalid province code', $res['json']['error']);
        }
    }

    public function testNationalDensityFeatureCollection(): void
    {
        $res = $this->executeThematic([
            'prov' => '0',
            'metric' => 'density',
            'nocache' => '1'
        ]);

        $this->assertEquals(200, $res['code']);
        $this->assertIsArray($res['json']);
        $this->assertEquals('FeatureCollection', $res['json']['type']);
        $this->assertArrayHasKey('features', $res['json']);
        $this->assertCount(38, $res['json']['features'], 'Expected 38 Indonesian provinces');

        $first = $res['json']['features'][0];
        $this->assertEquals('Feature', $first['type']);
        $this->assertArrayHasKey('properties', $first);
        $this->assertArrayHasKey('geometry', $first);

        $props = $first['properties'];
        $this->assertArrayHasKey('kode', $props);
        $this->assertArrayHasKey('nama', $props);
        $this->assertArrayHasKey('penduduk', $props);
        $this->assertArrayHasKey('luas', $props);
        $this->assertArrayHasKey('kepadatan', $props);
        $this->assertArrayHasKey('sex_ratio', $props);

        $this->assertContains($first['geometry']['type'], ['Polygon', 'MultiPolygon']);
        $this->assertNotEmpty($first['geometry']['coordinates']);

        // Check RFC 7946 coordinates [lng, lat] (Indonesia longitude ~95 to 141)
        $sampleRing = $first['geometry']['type'] === 'Polygon'
            ? $first['geometry']['coordinates'][0]
            : $first['geometry']['coordinates'][0][0];
        $firstCoord = $sampleRing[0];
        $this->assertIsArray($firstCoord);
        $this->assertGreaterThan(90, $firstCoord[0], 'Longitude should be first coordinate and > 90');
    }

    public function testNationalSexRatioMetric(): void
    {
        $res = $this->executeThematic([
            'prov' => 'all',
            'metric' => 'sex_ratio',
            'nocache' => '1'
        ]);

        $this->assertEquals(200, $res['code']);
        $this->assertIsArray($res['json']);
        $this->assertCount(38, $res['json']['features']);

        foreach ($res['json']['features'] as $feat) {
            $props = $feat['properties'];
            $this->assertGreaterThan(0, $props['sex_ratio']);
            $this->assertGreaterThan(50, $props['sex_ratio']);
            $this->assertLessThan(150, $props['sex_ratio']);
        }
    }

    public function testRegionalRegenciesInProvince32(): void
    {
        $res = $this->executeThematic([
            'prov' => '32',
            'metric' => 'density',
            'nocache' => '1'
        ]);

        $this->assertEquals(200, $res['code']);
        $this->assertIsArray($res['json']);
        $this->assertEquals('FeatureCollection', $res['json']['type']);
        $this->assertCount(27, $res['json']['features'], 'Expected 27 regencies and cities in West Java (32)');

        foreach ($res['json']['features'] as $feat) {
            $this->assertStringStartsWith('32.', $feat['properties']['kode']);
            $this->assertGreaterThan(0, $feat['properties']['penduduk']);
            $this->assertGreaterThan(0, $feat['properties']['luas']);
            $this->assertGreaterThan(0, $feat['properties']['kepadatan']);
        }
    }

    public function testThematicFileCaching(): void
    {
        $cacheFile = dirname($this->geoThematicFile, 2) . '/cache/thematic_31_density.json';
        if (file_exists($cacheFile)) {
            @unlink($cacheFile);
        }

        // 1st call: creates cache file
        $res1 = $this->executeThematic([
            'prov' => '31',
            'metric' => 'density'
        ]);
        $this->assertEquals(200, $res1['code']);
        $this->assertFileExists($cacheFile);

        // 2nd call: reads from cache file
        $res2 = $this->executeThematic([
            'prov' => '31',
            'metric' => 'density'
        ]);
        $this->assertEquals(200, $res2['code']);
        $this->assertEquals($res1['json'], $res2['json']);

        // Cleanup
        if (file_exists($cacheFile)) {
            @unlink($cacheFile);
        }
    }
}
