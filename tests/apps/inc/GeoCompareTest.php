<?php
/*
BISMILLAAHIRRAHMAANIRRAHIIM - In the Name of Allah, Most Gracious, Most Merciful
================================================================================
filename : GeoCompareTest.php
purpose  : PHPUnit test suite for geo_compare.php (P6 Side-by-Side Region Comparison)
create   : 2026-11-20
last edit: 2026-11-20 21:16:00
author   : cahya dsn
================================================================================
MIT License
copyright (c) 2017-2026 by cahya dsn; cahyadsn@gmail.com
================================================================================*/

namespace Tests\Apps\Inc;

use PHPUnit\Framework\TestCase;
use PDO;

class MockCompareStatement
{
    private string $query;
    private array $regions;
    private ?array $params = null;
    private ?array $listRows = null;

    public function __construct(string $query, array $regions)
    {
        $this->query = $query;
        $this->regions = $regions;
    }

    public function execute($params = null): bool
    {
        $this->params = $params;
        $this->listRows = null;
        return true;
    }

    public function fetchObject()
    {
        // 1. List query: LENGTH(kode) = :len
        if (strpos($this->query, 'LENGTH(kode) = :len') !== false) {
            if ($this->listRows === null) {
                $len = $this->params[':len'] ?? 2;
                $this->listRows = [];
                foreach ($this->regions as $r) {
                    if (strlen((string)$r['kode']) === (int)$len) {
                        $this->listRows[] = (object)[
                            'kode' => $r['kode'],
                            'nama' => $r['nama']
                        ];
                    }
                }
            }
            return array_shift($this->listRows) ?: false;
        }

        // 2. Region detail query: WHERE kode = :kode
        if (strpos($this->query, 'WHERE kode = :kode') !== false) {
            $kode = $this->params[':kode'] ?? null;
            if ($kode && isset($this->regions[$kode])) {
                $r = $this->regions[$kode];
                return (object)[
                    'kode'     => $r['kode'],
                    'nama'     => $r['nama'],
                    'ibukota'  => $r['ibukota'],
                    'lat'      => $r['lat'],
                    'lng'      => $r['lng'],
                    'elv'      => $r['elv'],
                    'tz'       => $r['tz'],
                    'luas'     => $r['luas'],
                    'penduduk' => $r['penduduk'],
                    'path'     => $r['path']
                ];
            }
            return false;
        }

        // 3. Luas table: FROM wilayah_luas
        if (strpos($this->query, 'FROM wilayah_luas') !== false) {
            $kode = $this->params[':kode'] ?? null;
            if ($kode && isset($this->regions[$kode])) {
                return (object)['luas' => $this->regions[$kode]['luas_official']];
            }
            return false;
        }

        // 4. Penduduk table: FROM wilayah_penduduk
        if (strpos($this->query, 'FROM wilayah_penduduk') !== false) {
            $kode = $this->params[':kode'] ?? null;
            if ($kode && isset($this->regions[$kode])) {
                $r = $this->regions[$kode];
                return (object)[
                    'pria'   => $r['pria'],
                    'wanita' => $r['wanita'],
                    'total'  => $r['total']
                ];
            }
            return false;
        }

        // 5. Pulau table: FROM wilayah_pulau
        if (strpos($this->query, 'FROM wilayah_pulau') !== false) {
            $prefix = $this->params[':prefix'] ?? '';
            $kode = rtrim($prefix, '.%');
            $cnt = isset($this->regions[$kode]) ? $this->regions[$kode]['islands'] : 0;
            return (object)['cnt' => $cnt];
        }

        // 6. Hierarchy count: FROM wilayah WHERE ...
        if (strpos($this->query, 'FROM wilayah') !== false && strpos($this->query, 'SUM(') !== false) {
            $prefix = $this->params[':prefix'] ?? '';
            $kode = rtrim($prefix, '.%');
            if (isset($this->regions[$kode])) {
                $r = $this->regions[$kode];
                return (object)[
                    'total_kab'  => $r['total_kab'],
                    'total_kota' => $r['total_kota'],
                    'total_kec'  => $r['total_kec'],
                    'total_kel'  => $r['total_kel'],
                    'total_desa' => $r['total_desa']
                ];
            }
            return (object)[
                'total_kab'  => 0,
                'total_kota' => 0,
                'total_kec'  => 0,
                'total_kel'  => 0,
                'total_desa' => 0
            ];
        }

        return false;
    }
}

class MockComparePdo extends PDO
{
    private array $regions;

    public function __construct(array $regions)
    {
        $this->regions = $regions;
    }

    #[\ReturnTypeWillChange]
    public function prepare($query, $options = [])
    {
        return new MockCompareStatement($query, $this->regions);
    }
}

class GeoCompareTest extends TestCase
{
    private string $geoCompareFile;
    private $mockPdo;

    protected function setUp(): void
    {
        $this->geoCompareFile = realpath(__DIR__ . '/../../../apps/inc/geo_compare.php');
        $_GET = [];

        $regions = [
            '31' => [
                'kode'          => '31',
                'nama'          => 'DKI JAKARTA',
                'ibukota'       => 'JAKARTA PUSAT',
                'lat'           => -6.1754,
                'lng'           => 106.8272,
                'elv'           => 8.0,
                'tz'            => 7,
                'luas'          => 661.5,
                'penduduk'      => 10609681,
                'path'          => '[[[-6.1,106.7],[-6.1,106.9],[-6.3,106.9],[-6.3,106.7],[-6.1,106.7]]]',
                'luas_official' => 661.52,
                'pria'          => 5334147,
                'wanita'        => 5275534,
                'total'         => 10609681,
                'islands'       => 2,
                'total_kab'     => 1,
                'total_kota'    => 5,
                'total_kec'     => 44,
                'total_kel'     => 267,
                'total_desa'    => 0
            ],
            '32' => [
                'kode'          => '32',
                'nama'          => 'JAWA BARAT',
                'ibukota'       => 'KOTA BANDUNG',
                'lat'           => -6.9147,
                'lng'           => 107.6098,
                'elv'           => 768.0,
                'tz'            => 7,
                'luas'          => 35377.76,
                'penduduk'      => 49405552,
                'path'          => '[[[-6.5,107.0],[-6.5,108.5],[-7.8,108.5],[-7.8,107.0],[-6.5,107.0]]]',
                'luas_official' => 35377.76,
                'pria'          => 25112000,
                'wanita'        => 24293552,
                'total'         => 49405552,
                'islands'       => 1,
                'total_kab'     => 18,
                'total_kota'    => 9,
                'total_kec'     => 627,
                'total_kel'     => 645,
                'total_desa'    => 5312
            ],
            '32.01' => [
                'kode'          => '32.01',
                'nama'          => 'KABUPATEN BOGOR',
                'ibukota'       => 'CIBINONG',
                'lat'           => -6.48,
                'lng'           => 106.85,
                'elv'           => 120.0,
                'tz'            => 7,
                'luas'          => 2664.0,
                'penduduk'      => 5427068,
                'path'          => '[[[-6.3,106.5],[-6.3,107.1],[-6.8,107.1],[-6.8,106.5],[-6.3,106.5]]]',
                'luas_official' => 2664.0,
                'pria'          => 2769000,
                'wanita'        => 2658068,
                'total'         => 5427068,
                'islands'       => 0,
                'total_kab'     => 0,
                'total_kota'    => 0,
                'total_kec'     => 40,
                'total_kel'     => 19,
                'total_desa'    => 416
            ],
            '32.73' => [
                'kode'          => '32.73',
                'nama'          => 'KOTA BANDUNG',
                'ibukota'       => 'BANDUNG',
                'lat'           => -6.9147,
                'lng'           => 107.6098,
                'elv'           => 768.0,
                'tz'            => 7,
                'luas'          => 167.3,
                'penduduk'      => 2452943,
                'path'          => '[[[-6.8,107.5],[-6.8,107.7],[-7.0,107.7],[-7.0,107.5],[-6.8,107.5]]]',
                'luas_official' => 167.31,
                'pria'          => 1235000,
                'wanita'        => 1217943,
                'total'         => 2452943,
                'islands'       => 0,
                'total_kab'     => 0,
                'total_kota'    => 0,
                'total_kec'     => 30,
                'total_kel'     => 151,
                'total_desa'    => 0
            ]
        ];

        $this->mockPdo = new MockComparePdo($regions);
        $GLOBALS['db'] = $this->mockPdo;
    }

    protected function tearDown(): void
    {
        $_GET = [];
        unset($GLOBALS['db']);
    }

    private function executeCompare(array $params = []): array
    {
        global $db;
        $db = $this->mockPdo;
        $GLOBALS['db'] = $this->mockPdo;
        $_GET = $params;

        ob_start();
        $code = file_get_contents($this->geoCompareFile);
        $appsInc = str_replace('\\', '/', dirname($this->geoCompareFile));
        $code = str_replace('__DIR__', "'" . $appsInc . "'", $code);
        $code = preg_replace('/require_once\s+[\'"].*?\/db\.php[\'"];/', '// db.php bypassed', $code);
        $code = str_replace('header(', '@header(', $code);
        $code = preg_replace('/http_response_code\(([0-9]+)\);/', '$http_code = $1;', $code);
        $code = str_replace('exit;', 'return;', $code);
        $http_code = 200;

        $oldCwd = getcwd();
        chdir(dirname($this->geoCompareFile));
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

    public function testCalcDeltaPercentHelper(): void
    {
        // Trigger file loading via executeCompare
        $this->executeCompare(['kode1' => '31', 'kode2' => '32', 'nocache' => '1']);

        // Equal values -> 0%
        $this->assertEquals(0.0, \calcDeltaPercent(100, 100));

        // Val1 50% larger than Val2
        $this->assertEquals(50.0, \calcDeltaPercent(150, 100));

        // Val1 50% smaller than Val2
        $this->assertEquals(-50.0, \calcDeltaPercent(50, 100));

        // Val2 is 0 with positive Val1
        $this->assertEquals(100.0, \calcDeltaPercent(50, 0));

        // Both are 0
        $this->assertEquals(0.0, \calcDeltaPercent(0, 0));
    }

    public function testCalcRatioPairHelper(): void
    {
        // Trigger file loading via executeCompare
        $this->executeCompare(['kode1' => '31', 'kode2' => '32', 'nocache' => '1']);

        // Equal values -> [50.0, 50.0]
        $this->assertEquals([50.0, 50.0], \calcRatioPair(100, 100));

        // Proportional 25% and 75%
        $this->assertEquals([25.0, 75.0], \calcRatioPair(25, 75));

        // Zero values -> default [50.0, 50.0]
        $this->assertEquals([50.0, 50.0], \calcRatioPair(0, 0));

        // Extreme ratio
        $pair = \calcRatioPair(1000, 1);
        $this->assertGreaterThan(99.0, $pair[0]);
        $this->assertEquals(100.0, round($pair[0] + $pair[1], 1));
    }

    public function testInvalidParametersReturn400(): void
    {
        $invalidCases = [
            ['kode1' => '', 'kode2' => '32'],
            ['kode1' => '32', 'kode2' => ''],
            ['kode1' => 'abc', 'kode2' => '32'],
            ['kode1' => '32', 'kode2' => 'xyz'],
            ['kode1' => '32.1', 'kode2' => '32.01'],
            ['kode1' => '32', 'kode2' => '32.73'], // hierarchy mismatch: prov vs kab
            ['kode1' => '32.73', 'kode2' => '32'], // hierarchy mismatch: kab vs prov
            ['kode1' => '<script>', 'kode2' => '32'],
            ['kode1' => '32.01.01', 'kode2' => '32.01.02'] // kecamatan not supported
        ];

        foreach ($invalidCases as $case) {
            $res = $this->executeCompare($case);
            $this->assertEquals(400, $res['code'], "Expected 400 for params: " . json_encode($case));
            $this->assertIsArray($res['json']);
            $this->assertEquals('error', $res['json']['status']);
            $this->assertArrayHasKey('message', $res['json']);
        }
    }

    public function testListProvincesEndpoint(): void
    {
        $res = $this->executeCompare(['list' => 'provinsi', 'nocache' => '1']);
        $this->assertEquals(200, $res['code']);
        $this->assertIsArray($res['json']);
        $this->assertEquals('success', $res['json']['status']);
        $this->assertIsArray($res['json']['data']);
        $this->assertGreaterThanOrEqual(2, count($res['json']['data']));

        $first = $res['json']['data'][0];
        $this->assertArrayHasKey('kode', $first);
        $this->assertArrayHasKey('nama', $first);
        $this->assertEquals(2, strlen($first['kode']));
    }

    public function testListKabKotaEndpoint(): void
    {
        $res = $this->executeCompare(['list' => 'kabkota', 'nocache' => '1']);
        $this->assertEquals(200, $res['code']);
        $this->assertIsArray($res['json']);
        $this->assertEquals('success', $res['json']['status']);
        $this->assertIsArray($res['json']['data']);
        $this->assertGreaterThanOrEqual(2, count($res['json']['data']));

        $first = $res['json']['data'][0];
        $this->assertArrayHasKey('kode', $first);
        $this->assertArrayHasKey('nama', $first);
        $this->assertEquals(5, strlen($first['kode']));
    }

    public function testInvalidListTypeReturns400(): void
    {
        $res = $this->executeCompare(['list' => 'kecamatan']);
        $this->assertEquals(400, $res['code']);
        $this->assertIsArray($res['json']);
        $this->assertEquals('error', $res['json']['status']);
    }

    public function testCompareProvincesSuccess(): void
    {
        $res = $this->executeCompare([
            'kode1' => '31',
            'kode2' => '32',
            'nocache' => '1'
        ]);

        $this->assertEquals(200, $res['code']);
        $this->assertIsArray($res['json']);
        $this->assertEquals('success', $res['json']['status']);
        $this->assertEquals('provinsi', $res['json']['level']);

        $entities = $res['json']['entities'];
        $this->assertArrayHasKey('entity1', $entities);
        $this->assertArrayHasKey('entity2', $entities);

        // Region 1 (DKI Jakarta)
        $r1 = $entities['entity1'];
        $this->assertEquals('31', $r1['kode']);
        $this->assertEquals('DKI JAKARTA', $r1['nama']);
        $this->assertGreaterThan(0, $r1['luas_km2']);
        $this->assertGreaterThan(0, $r1['penduduk']);
        $this->assertGreaterThan(0, $r1['kepadatan']);
        $this->assertEquals(2, $r1['total_pulau']);

        // Region 2 (Jawa Barat)
        $r2 = $entities['entity2'];
        $this->assertEquals('32', $r2['kode']);
        $this->assertEquals('JAWA BARAT', $r2['nama']);

        // Comparison metrics & ratios
        $comp = $res['json']['comparison'];
        $this->assertArrayHasKey('luas_delta_percent', $comp);
        $this->assertArrayHasKey('penduduk_delta_percent', $comp);
        $this->assertArrayHasKey('kepadatan_delta_percent', $comp);
        $this->assertArrayHasKey('pulau_delta_percent', $comp);
        $this->assertArrayHasKey('ratios', $comp);

        $ratios = $comp['ratios'];
        $this->assertArrayHasKey('luas', $ratios);
        $this->assertArrayHasKey('penduduk', $ratios);
        $this->assertArrayHasKey('kepadatan', $ratios);
        $this->assertArrayHasKey('pulau', $ratios);
        $this->assertEquals(100.0, round($ratios['penduduk'][0] + $ratios['penduduk'][1], 1));
    }

    public function testCompareRegenciesSuccess(): void
    {
        $res = $this->executeCompare([
            'kode1' => '32.01',
            'kode2' => '32.73',
            'nocache' => '1'
        ]);

        $this->assertEquals(200, $res['code']);
        $this->assertIsArray($res['json']);
        $this->assertEquals('success', $res['json']['status']);
        $this->assertEquals('kabkota', $res['json']['level']);

        $entities = $res['json']['entities'];
        $r1 = $entities['entity1'];
        $this->assertEquals('32.01', $r1['kode']);

        $r2 = $entities['entity2'];
        $this->assertEquals('32.73', $r2['kode']);

        $comp = $res['json']['comparison'];
        $this->assertArrayHasKey('luas_delta_percent', $comp);
        $this->assertArrayHasKey('penduduk_delta_percent', $comp);
        $this->assertArrayHasKey('kepadatan_delta_percent', $comp);
    }

    public function testNonExistentRegionReturns404(): void
    {
        $res = $this->executeCompare([
            'kode1' => '98',
            'kode2' => '99',
            'nocache' => '1'
        ]);

        $this->assertEquals(404, $res['code']);
        $this->assertIsArray($res['json']);
        $this->assertEquals('error', $res['json']['status']);
        $this->assertStringContainsString('not be found', $res['json']['message']);
    }
}
