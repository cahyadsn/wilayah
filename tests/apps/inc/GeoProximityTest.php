<?php
/*
BISMILLAAHIRRAHMAANIRRAHIIM - In the Name of Allah, Most Gracious, Most Merciful
================================================================================
filename : GeoProximityTest.php
purpose  : PHPUnit test suite for geo_proximity.php (Feature P8 Spatial Buffer & Measurement)
create   : 2026-11-25
last edit: 2026-11-25 07:00:00
author   : cahya dsn
================================================================================
MIT License
copyright (c) 2017-2026 by cahya dsn; cahyadsn@gmail.com
================================================================================*/

declare(strict_types=1);

namespace Tests\Apps\Inc;

use PHPUnit\Framework\TestCase;
use PDO;

class MockProximityStatement
{
    private string $query;
    private array $islands;
    private array $regions;
    private ?array $params = null;
    private ?array $filteredRows = null;

    public function __construct(string $query, array $islands, array $regions)
    {
        $this->query = $query;
        $this->islands = $islands;
        $this->regions = $regions;
    }

    public function execute($params = null): bool
    {
        $this->params = $params ?? [];
        $this->filteredRows = null;
        return true;
    }

    public function fetch($mode = PDO::FETCH_ASSOC)
    {
        if ($this->filteredRows === null) {
            $this->filteredRows = $this->fetchAll($mode);
        }
        return array_shift($this->filteredRows) ?: false;
    }

    public function fetchAll($mode = PDO::FETCH_ASSOC): array
    {
        $latMin = (float)($this->params[':lat_min'] ?? -90);
        $latMax = (float)($this->params[':lat_max'] ?? 90);
        $lngMin = (float)($this->params[':lng_min'] ?? -180);
        $lngMax = (float)($this->params[':lng_max'] ?? 180);

        if (strpos($this->query, 'wilayah_pulau') !== false) {
            $filtered = [];
            foreach ($this->islands as $island) {
                $lat = (float)$island['lat'];
                $lng = (float)$island['lng'];
                if ($lat >= $latMin && $lat <= $latMax && $lng >= $lngMin && $lng <= $lngMax) {
                    $filtered[] = $island;
                }
            }
            return $filtered;
        }

        if (strpos($this->query, 'wilayah_level_1_2') !== false) {
            $filtered = [];
            foreach ($this->regions as $reg) {
                $lat = (float)$reg['lat'];
                $lng = (float)$reg['lng'];
                if ($lat >= $latMin && $lat <= $latMax && $lng >= $lngMin && $lng <= $lngMax) {
                    $filtered[] = $reg;
                }
            }
            return $filtered;
        }

        return [];
    }
}

class MockProximityPDO extends PDO
{
    private array $islands;
    private array $regions;

    public function __construct(array $islands, array $regions)
    {
        $this->islands = $islands;
        $this->regions = $regions;
    }

    #[\ReturnTypeWillChange]
    public function prepare($query, $options = [])
    {
        return new MockProximityStatement($query, $this->islands, $this->regions);
    }
}

class GeoProximityTest extends TestCase
{
    private string $geoProximityFile;
    private MockProximityPDO $mockPdo;

    protected function setUp(): void
    {
        $this->geoProximityFile = realpath(__DIR__ . '/../../../apps/inc/geo_proximity.php');
        $_GET = [];

        $islands = [
            [
                'kode'   => '11.01.40001',
                'nama'   => 'Pulau Rondo',
                'lat'    => 6.07472,
                'lng'    => 95.11306,
                'status' => 'PPKT',
                'luas'   => 0.65
            ],
            [
                'kode'   => '11.01.40002',
                'nama'   => 'Pulau Weh',
                'lat'    => 5.82361,
                'lng'    => 95.29778,
                'status' => 'BP',
                'luas'   => 121.0
            ],
            [
                'kode'   => '11.01.40003',
                'nama'   => 'Pulau Rubiah',
                'lat'    => 5.87111,
                'lng'    => 95.25833,
                'status' => 'BP',
                'luas'   => 0.35
            ],
            [
                'kode'   => '31.01.40001',
                'nama'   => 'Pulau Pramuka',
                'lat'    => -5.74611,
                'lng'    => 106.61444,
                'status' => 'BP',
                'luas'   => 0.16
            ]
        ];

        $regions = [
            [
                'kode'     => '11.72',
                'nama'     => 'KOTA SABANG',
                'lat'      => 5.88969,
                'lng'      => 95.31644,
                'luas'     => 153.0,
                'penduduk' => 42000
            ],
            [
                'kode'     => '11.71',
                'nama'     => 'KOTA BANDA ACEH',
                'lat'      => 5.54829,
                'lng'      => 95.32376,
                'luas'     => 61.36,
                'penduduk' => 250000
            ],
            [
                'kode'     => '31',
                'nama'     => 'DKI JAKARTA',
                'lat'      => -6.1754,
                'lng'      => 106.8272,
                'luas'     => 661.5,
                'penduduk' => 10609681
            ]
        ];

        $this->mockPdo = new MockProximityPDO($islands, $regions);
    }

    protected function tearDown(): void
    {
        $_GET = [];
    }

    private function executeProximity(array $params = []): array
    {
        global $db;
        $db = $this->mockPdo;
        $GLOBALS['db'] = $this->mockPdo;
        $_GET = $params;

        ob_start();
        $code = file_get_contents($this->geoProximityFile);
        $code = preg_replace('/declare\s*\(\s*strict_types\s*=\s*1\s*\)\s*;/', '', $code);
        $appsInc = str_replace('\\', '/', dirname($this->geoProximityFile));
        $code = str_replace('__DIR__', "'" . $appsInc . "'", $code);
        $code = str_replace('header(', '@header(', $code);
        $code = preg_replace('/http_response_code\(([0-9]+)\);/', '$http_code = $1;', $code);
        $code = str_replace('exit;', 'return;', $code);
        $http_code = 200;

        $oldCwd = getcwd();
        chdir(dirname($this->geoProximityFile));
        try {
            eval('?>' . $code);
        } finally {
            chdir($oldCwd);
        }
        $raw = ob_get_clean();

        $json = json_decode($raw, true);

        return [
            'code' => $http_code,
            'raw'  => $raw,
            'json' => $json
        ];
    }

    public function testParameterValidationMissingCoords(): void
    {
        $res = $this->executeProximity([]);
        $this->assertEquals(400, $res['code']);
        $this->assertEquals('error', $res['json']['status']);
        $this->assertStringContainsString("wajib diisi dengan koordinat", $res['json']['message']);

        $resOnlyLat = $this->executeProximity(['lat' => '5.88']);
        $this->assertEquals(400, $resOnlyLat['code']);
    }

    public function testParameterValidationInvalidCoords(): void
    {
        $res = $this->executeProximity(['lat' => 'not_a_number', 'lng' => '95.32']);
        $this->assertEquals(400, $res['code']);
        $this->assertEquals('error', $res['json']['status']);

        $resOutOfRangeLat = $this->executeProximity(['lat' => '95.0', 'lng' => '95.32']);
        $this->assertEquals(400, $resOutOfRangeLat['code']);

        $resOutOfRangeLng = $this->executeProximity(['lat' => '5.0', 'lng' => '200.0']);
        $this->assertEquals(400, $resOutOfRangeLng['code']);
    }

    public function testRadiusClamping(): void
    {
        // 0 km should clamp to 0.1 km
        $resLow = $this->executeProximity(['lat' => '5.88', 'lng' => '95.32', 'radius' => '0']);
        $this->assertEquals(200, $resLow['code']);
        $this->assertEquals(0.1, $resLow['json']['radius_km']);

        // 1000 km should clamp to 500 km
        $resHigh = $this->executeProximity(['lat' => '5.88', 'lng' => '95.32', 'radius' => '1000']);
        $this->assertEquals(200, $resHigh['code']);
        $this->assertEquals(500.0, $resHigh['json']['radius_km']);
    }

    public function testLimitClamping(): void
    {
        $res = $this->executeProximity(['lat' => '5.88', 'lng' => '95.32', 'limit' => '0']);
        $this->assertEquals(200, $res['code']);

        $resOver = $this->executeProximity(['lat' => '5.88', 'lng' => '95.32', 'limit' => '999']);
        $this->assertEquals(200, $resOver['code']);
    }

    public function testHaversineAndBearingPrecisionSabangRondo(): void
    {
        // Sabang center: 5.88969, 95.31644
        // Pulau Rondo is ~28 km away to the North-West
        $res = $this->executeProximity([
            'lat'    => '5.88969',
            'lng'    => '95.31644',
            'radius' => '40',
            'target' => 'islands'
        ]);

        $this->assertEquals(200, $res['code']);
        $this->assertEquals('success', $res['json']['status']);
        $this->assertNotEmpty($res['json']['islands']);

        $rondo = null;
        foreach ($res['json']['islands'] as $isl) {
            if ($isl['nama'] === 'Pulau Rondo') {
                $rondo = $isl;
                break;
            }
        }

        $this->assertNotNull($rondo, 'Pulau Rondo must be found within 40km of Sabang');
        $this->assertGreaterThan(25.0, $rondo['distance_km']);
        $this->assertLessThan(32.0, $rondo['distance_km']);
        // Nautical miles: distance_km / 1.852
        $this->assertEqualsWithDelta($rondo['distance_km'] / 1.852, $rondo['distance_nm'], 0.1);
        // Bearing should be in North-West direction (~300° - ~340°)
        $this->assertGreaterThan(290.0, $rondo['bearing_deg']);
        $this->assertLessThan(350.0, $rondo['bearing_deg']);
        $this->assertContains($rondo['direction'], ['NW', 'NNW', 'WNW', 'BL', 'UBL']);
    }

    public function testTargetFilterIslandsOnly(): void
    {
        $res = $this->executeProximity([
            'lat'    => '5.88',
            'lng'    => '95.32',
            'radius' => '50',
            'target' => 'islands'
        ]);

        $this->assertEquals(200, $res['code']);
        $this->assertEquals('islands', $res['json']['target']);
        $this->assertArrayHasKey('islands', $res['json']);
        $this->assertArrayNotHasKey('regions', $res['json']);

        foreach ($res['json']['islands'] as $item) {
            $this->assertEquals('island', $item['type']);
        }
    }

    public function testTargetFilterRegionsOnly(): void
    {
        $res = $this->executeProximity([
            'lat'    => '5.88',
            'lng'    => '95.32',
            'radius' => '50',
            'target' => 'regions'
        ]);

        $this->assertEquals(200, $res['code']);
        $this->assertEquals('regions', $res['json']['target']);
        $this->assertArrayHasKey('regions', $res['json']);

        foreach ($res['json']['regions'] as $item) {
            $this->assertEquals('region', $item['type']);
        }
    }

    public function testTargetFilterAllSortedByDistance(): void
    {
        $res = $this->executeProximity([
            'lat'    => '5.88',
            'lng'    => '95.32',
            'radius' => '50',
            'target' => 'all'
        ]);

        $this->assertEquals(200, $res['code']);
        $this->assertEquals('all', $res['json']['target']);
        $this->assertArrayHasKey('results', $res['json']);

        $results = $res['json']['results'];
        $this->assertNotEmpty($results);

        // Verify sorted by distance ascending
        for ($i = 0; $i < count($results) - 1; $i++) {
            $this->assertLessThanOrEqual(
                $results[$i + 1]['distance_km'],
                $results[$i]['distance_km'],
                'Results must be sorted by distance ascending'
            );
        }
    }

    public function testCsvExportFormatWithBomAndHeaders(): void
    {
        $res = $this->executeProximity([
            'lat'    => '5.88',
            'lng'    => '95.32',
            'radius' => '40',
            'target' => 'all',
            'format' => 'csv'
        ]);

        $this->assertEquals(200, $res['code']);
        $raw = $res['raw'];

        // Verify UTF-8 BOM
        $bom = "\xEF\xBB\xBF";
        $this->assertStringStartsWith($bom, $raw, 'CSV output must start with UTF-8 BOM');

        // Verify CSV header line
        $this->assertStringContainsString('Kode,Nama,Tipe,Status,Latitude,Longitude,Jarak_km,Jarak_NM,Arah_Derajat,Mata_Angin', $raw);
        // Verify CSV row content contains Pulau Rondo or Kota Sabang
        $this->assertTrue(strpos($raw, 'Pulau Rondo') !== false || strpos($raw, 'KOTA SABANG') !== false);
    }

    public function testEmptyResultsWhenNoEntitiesInRange(): void
    {
        // Far out in the Southern Ocean
        $res = $this->executeProximity([
            'lat'    => '-12.0',
            'lng'    => '95.0',
            'radius' => '10',
            'target' => 'all'
        ]);

        $this->assertEquals(200, $res['code']);
        $this->assertEquals(0, $res['json']['total_found']);
        $this->assertEmpty($res['json']['results']);
    }
}
