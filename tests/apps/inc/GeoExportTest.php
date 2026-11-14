<?php
/*
BISMILLAAHIRRAHMAANIRRAHIIM - In the Name of Allah, Most Gracious, Most Merciful
================================================================================
filename : GeoExportTest.php
purpose  : PHPUnit test suite for geo_export.php (P4 Spatial Data Exporter)
create   : 2026-11-14
last edit: 2026-11-14 20:05:00
author   : cahya dsn
================================================================================
MIT License
copyright (c) 2017-2026 by cahya dsn; cahyadsn@gmail.com
================================================================================*/

namespace Tests\Apps\Inc;

use PHPUnit\Framework\TestCase;
use PDO;

class GeoExportTest extends TestCase
{
    private string $geoExportFile;

    protected function setUp(): void
    {
        $this->geoExportFile = realpath(__DIR__ . '/../../../apps/inc/geo_export.php');
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

    private function executeExport(array $params = []): array
    {
        global $db;
        if (isset($GLOBALS['db'])) {
            $db = $GLOBALS['db'];
        }
        $_GET = $params;

        ob_start();
        $code = file_get_contents($this->geoExportFile);
        $appsInc = str_replace('\\', '/', dirname($this->geoExportFile));
        $code = str_replace('__DIR__', "'" . $appsInc . "'", $code);
        $code = str_replace('header(', '@header(', $code);
        $code = preg_replace('/http_response_code\(([0-9]+)\);/', '$http_code = $1;', $code);
        $code = str_replace('exit;', 'return;', $code);
        $http_code = 200;

        $oldCwd = getcwd();
        chdir(dirname($this->geoExportFile));
        try {
            eval('?>' . $code);
        } finally {
            chdir($oldCwd);
        }
        $raw = ob_get_clean();

        return [
            'code' => $http_code,
            'raw'  => $raw,
            'json' => json_decode($raw, true)
        ];
    }

    public function testInvalidParametersReturn400(): void
    {
        $invalidCodes = ['abc', '1', '123', '32.abc', '<script>', '..', '32.01.01.2001.99'];
        foreach ($invalidCodes as $code) {
            $res = $this->executeExport(['kode' => $code]);
            $this->assertEquals(400, $res['code'], "Expected 400 for code: {$code}");
            $this->assertIsArray($res['json']);
            $this->assertArrayHasKey('error', $res['json']);
            $this->assertEquals('Invalid code parameter', $res['json']['error']);
        }
    }

    public function testExportBoundaryGeoJsonSingleRegion(): void
    {
        $res = $this->executeExport([
            'kode'   => '32.73',
            'format' => 'geojson',
            'target' => 'boundary',
            'preview'=> '1'
        ]);

        $this->assertEquals(200, $res['code']);
        $this->assertIsArray($res['json']);
        $this->assertEquals('Feature', $res['json']['type']);
        $this->assertArrayHasKey('properties', $res['json']);
        $this->assertArrayHasKey('geometry', $res['json']);

        $props = $res['json']['properties'];
        $this->assertEquals('32.73', $props['kode']);
        $this->assertEquals('KOTA BANDUNG', $props['nama']);
        $this->assertEquals('Kota', $props['tipe']);
        $this->assertArrayHasKey('luas_km2', $props);
        $this->assertArrayHasKey('penduduk', $props);
        $this->assertArrayHasKey('kepadatan_jiwa_km2', $props);

        $geom = $res['json']['geometry'];
        $this->assertEquals('Polygon', $geom['type']);
        $this->assertNotEmpty($geom['coordinates']);
        $ring = $geom['coordinates'][0];
        $this->assertGreaterThan(10, count($ring));

        // RFC 7946: [lng, lat]
        $firstCoord = $ring[0];
        $this->assertGreaterThan(100, $firstCoord[0], 'Longitude must be first and > 100 in Indonesia');
        $this->assertLessThan(0, $firstCoord[1], 'Latitude must be negative for Bandung');

        // Ring must be closed
        $this->assertEquals($ring[0], end($ring), 'LinearRing must be closed');
    }

    public function testExportBoundaryKmlSingleRegion(): void
    {
        $res = $this->executeExport([
            'kode'   => '32.73',
            'format' => 'kml',
            'target' => 'boundary',
            'preview'=> '1'
        ]);

        $this->assertEquals(200, $res['code']);
        $xml = simplexml_load_string($res['raw']);
        $this->assertNotFalse($xml, 'Output must be well-formed KML XML');
        $this->assertStringContainsString('KOTA BANDUNG', (string)$xml->Document->name);
        $this->assertStringContainsString('<Polygon>', $res['raw']);
        $this->assertStringContainsString('<LinearRing>', $res['raw']);
        $this->assertStringContainsString('<coordinates>', $res['raw']);
    }

    public function testExportBoundaryMultiPolygonAceh(): void
    {
        $res = $this->executeExport([
            'kode'   => '11',
            'format' => 'geojson',
            'target' => 'boundary',
            'preview'=> '1'
        ]);

        $this->assertEquals(200, $res['code']);
        $this->assertIsArray($res['json']);
        $this->assertEquals('Feature', $res['json']['type']);
        $this->assertEquals('MultiPolygon', $res['json']['geometry']['type']);
        $this->assertGreaterThan(1, count($res['json']['geometry']['coordinates']));

        // Test KML MultiGeometry
        $resKml = $this->executeExport([
            'kode'   => '11',
            'format' => 'kml',
            'target' => 'boundary',
            'preview'=> '1'
        ]);
        $this->assertEquals(200, $resKml['code']);
        $this->assertStringContainsString('<MultiGeometry>', $resKml['raw']);
    }

    public function testExportBoundaryNationalAllProvinces(): void
    {
        $res = $this->executeExport([
            'kode'   => 'all',
            'format' => 'geojson',
            'target' => 'boundary',
            'preview'=> '1'
        ]);

        $this->assertEquals(200, $res['code']);
        $this->assertIsArray($res['json']);
        $this->assertEquals('FeatureCollection', $res['json']['type']);
        $this->assertGreaterThanOrEqual(34, count($res['json']['features']));
    }

    public function testExportIslandsCsv(): void
    {
        $res = $this->executeExport([
            'kode'   => '11',
            'format' => 'csv',
            'target' => 'islands',
            'preview'=> '1'
        ]);

        $this->assertEquals(200, $res['code']);
        $this->assertStringStartsWith("\xEF\xBB\xBF", $res['raw'], 'CSV must include UTF-8 BOM');

        $lines = explode("\n", trim(substr($res['raw'], 3)));
        $this->assertGreaterThan(50, count($lines), 'Aceh should have many islands');

        $header = str_getcsv($lines[0]);
        $this->assertEquals(['kode', 'nama', 'lat', 'lng', 'status', 'luas_km2', 'notes'], $header);

        $firstRow = str_getcsv($lines[1]);
        $this->assertStringStartsWith('11.', $firstRow[0]);
        $this->assertNotEmpty($firstRow[1]); // nama
    }

    public function testExportIslandsGeoJson(): void
    {
        $res = $this->executeExport([
            'kode'   => '11.01',
            'format' => 'geojson',
            'target' => 'islands',
            'preview'=> '1'
        ]);

        $this->assertEquals(200, $res['code']);
        $this->assertIsArray($res['json']);
        $this->assertEquals('FeatureCollection', $res['json']['type']);
        $this->assertNotEmpty($res['json']['features']);

        $first = $res['json']['features'][0];
        $this->assertEquals('Feature', $first['type']);
        $this->assertEquals('Point', $first['geometry']['type']);
        $this->assertCount(2, $first['geometry']['coordinates']);
        $this->assertArrayHasKey('kode', $first['properties']);
        $this->assertArrayHasKey('nama', $first['properties']);
        $this->assertArrayHasKey('status', $first['properties']);
    }

    public function testExportStatsCsv(): void
    {
        $res = $this->executeExport([
            'kode'   => '32',
            'format' => 'csv',
            'target' => 'stats',
            'preview'=> '1'
        ]);

        $this->assertEquals(200, $res['code']);
        $this->assertStringStartsWith("\xEF\xBB\xBF", $res['raw']);

        $lines = explode("\n", trim(substr($res['raw'], 3)));
        $this->assertCount(28, $lines, 'Header + 27 regencies and cities in West Java');

        $header = str_getcsv($lines[0]);
        $this->assertContains('kepadatan_jiwa_km2', $header);
        $this->assertContains('sex_ratio', $header);
    }
}
