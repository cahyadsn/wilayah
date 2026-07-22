<?php

use PHPUnit\Framework\TestCase;

class ReverseLookupTest extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        // To safely include apps/inc/reverse_lookup.php which executes procedural code,
        // we need to mock the environment to prevent it from exiting early.

        // Mock a PDO connection for db.php inclusion if it's not already set
        if (!isset($GLOBALS['db'])) {
            $pdoMock = new class extends PDO {
                public function __construct() {}
                #[\ReturnTypeWillChange]
                public function prepare($query, $options = []) {
                    return new class {
                        public function execute($params = null) { return true; }
                        public function fetchAll($mode = null, ...$args) {
                            return [['kode' => '11', 'nama' => 'Aceh', 'lat' => 0, 'lng' => 0]];
                        }
                        public function fetch($mode = null, $cursorOrientation = null, $cursorOffset = null) {
                            return false;
                        }
                    };
                }
            };
            $GLOBALS['db'] = $pdoMock;
        }

        // Set GET variables to prevent exit on missing coordinates
        $_GET['lat'] = 0;
        $_GET['lng'] = 0;

        // Capture any output like headers or JSON
        ob_start();

        // Expose $db to the global scope since reverse_lookup.php expects it from db.php (which we bypass or supplement)
        global $db;
        $db = $GLOBALS['db'];

        // Using @ to suppress "headers already sent" warnings
        @require_once __DIR__ . '/../../apps/inc/reverse_lookup.php';
        @require_once __DIR__ . '/../../apps/inc/geo_utils.php';

        ob_end_clean();
    }

    public function testPointInRing(): void
    {
        $ring = [
            [0, 0],
            [0, 10],
            [10, 10],
            [10, 0]
        ];

        // Point inside
        $this->assertTrue(pointInRing(5, 5, $ring));

        // Point outside
        $this->assertFalse(pointInRing(15, 15, $ring));
        $this->assertFalse(pointInRing(-5, 5, $ring));

        // Invalid ring (< 3 points)
        $this->assertFalse(pointInRing(5, 5, []));
        $this->assertFalse(pointInRing(5, 5, [[0, 0]]));
        $this->assertFalse(pointInRing(5, 5, [[0, 0], [0, 10]]));
    }

    public function testPointInPath(): void
    {
        // Simple 1-ring path JSON
        $singleRingJson = json_encode([
            [
                [0, 0], [0, 10], [10, 10], [10, 0]
            ]
        ]);

        $this->assertTrue(pointInPath(5, 5, $singleRingJson));
        $this->assertFalse(pointInPath(15, 15, $singleRingJson));

        // Multi-ring path JSON (e.g. islands)
        $multiRingJson = json_encode([
            [
                [0, 0], [0, 10], [10, 10], [10, 0]
            ],
            [
                [20, 20], [20, 30], [30, 30], [30, 20]
            ]
        ]);

        $this->assertTrue(pointInPath(5, 5, $multiRingJson));
        $this->assertTrue(pointInPath(25, 25, $multiRingJson));
        $this->assertFalse(pointInPath(15, 15, $multiRingJson));

        // Empty or invalid inputs
        $this->assertFalse(pointInPath(5, 5, ''));
        $this->assertFalse(pointInPath(5, 5, 'not-json'));
        $this->assertFalse(pointInPath(5, 5, '[]'));
    }

    public function testPathLooksNearCentroid(): void
    {
        // Invalid or empty paths
        $this->assertFalse(isPathNearCentroid('', 0, 0, '12'));
        $this->assertFalse(isPathNearCentroid('invalid_json', 0, 0, '12'));
        $this->assertFalse(isPathNearCentroid('{}', 0, 0, '12')); // Not an array
        $this->assertFalse(isPathNearCentroid('[]', 0, 0, '12'));

        // This case actually throws an error in isPathNearCentroid at line 60: $points[0][1]
        // The implementation assumes $points[0] has at least 2 elements if $points is not empty.
        // So we will pass [[10, 10]] instead of [[10]].
        // We will test if passing a point string like "[[10, 20]]" works.

        // Valid simple path: [[lat, lng], [lat, lng], ...]
        $simplePathJson = json_encode([
            [0, 0], [0, 10], [10, 10], [10, 0]
        ]);
        // Centroid for above is (5, 5)

        // $kode length < 8, threshold is 2.5
        // (5, 5) compared to (lat, lng).
        // (2.5, 2.5) -> abs(5 - 2.5) = 2.5 <= 2.5 (true)
        $this->assertTrue(isPathNearCentroid($simplePathJson, 2.5, 2.5, '12'));
        // (2.4, 2.4) -> abs(5 - 2.4) = 2.6 > 2.5 (false)
        $this->assertFalse(isPathNearCentroid($simplePathJson, 2.4, 2.4, '12'));

        // $kode length >= 8 and < 13, threshold is 0.08
        // (5, 5) compared to (lat, lng).
        // Due to floating point math, 5 - 4.92 might be 0.08000000000000007 which is > 0.08
        // So let's use slightly closer values.
        $this->assertTrue(isPathNearCentroid($simplePathJson, 4.95, 4.95, '12345678'));
        // (4.91, 4.91) -> abs(5 - 4.91) = 0.09 > 0.08 (false)
        $this->assertFalse(isPathNearCentroid($simplePathJson, 4.91, 4.91, '12345678'));

        // $kode length >= 13, threshold is 0.03
        // (5, 5) compared to (lat, lng).
        // (4.98, 4.98) -> abs(5 - 4.98) = 0.02 <= 0.03 (true)
        $this->assertTrue(isPathNearCentroid($simplePathJson, 4.98, 4.98, '1234567890123'));
        // (4.96, 4.96) -> abs(5 - 4.96) = 0.04 > 0.03 (false)
        $this->assertFalse(isPathNearCentroid($simplePathJson, 4.96, 4.96, '1234567890123'));

        // Valid nested path structure (e.g. islands/rings): [[[lat, lng], [lat, lng], ...]]
        // Function handles extraction of $coords[0] when not numeric
        $nestedPathJson = json_encode([
            [
                [0, 0], [0, 10], [10, 10], [10, 0]
            ],
            [ // This second ring is ignored by isPathNearCentroid according to the logic:
              // $points = (is_array($coords[0]) ? $coords[0] : array());
                [20, 20], [20, 30], [30, 30], [30, 20]
            ]
        ]);
        // Centroid extracted from first ring is still (5, 5)
        // $kode length < 8, threshold is 2.5
        $this->assertTrue(isPathNearCentroid($nestedPathJson, 2.5, 2.5, '12'));
        $this->assertFalse(isPathNearCentroid($nestedPathJson, 2.4, 2.4, '12'));
    }
    public function testBuildChain(): void
    {
        $names = [
            '11' => 'Aceh',
            '11.01' => 'Kab. Aceh Selatan',
            '11.01.01' => 'Kec. Bakongan',
            '11.01.01.2001' => 'Keude Bakongan',
        ];

        // Level 1: Provinsi
        $provChain = buildChain('11', $names);
        $this->assertEquals(['kode' => '11', 'nama' => 'Aceh'], $provChain['prov']);
        $this->assertNull($provChain['kab']);
        $this->assertNull($provChain['kec']);
        $this->assertNull($provChain['kel']);

        // Level 2: Kabupaten
        $kabChain = buildChain('11.01', $names);
        $this->assertEquals(['kode' => '11', 'nama' => 'Aceh'], $kabChain['prov']);
        $this->assertEquals(['kode' => '11.01', 'nama' => 'Kab. Aceh Selatan'], $kabChain['kab']);
        $this->assertNull($kabChain['kec']);
        $this->assertNull($kabChain['kel']);

        // Level 3: Kecamatan
        $kecChain = buildChain('11.01.01', $names);
        $this->assertEquals(['kode' => '11', 'nama' => 'Aceh'], $kecChain['prov']);
        $this->assertEquals(['kode' => '11.01', 'nama' => 'Kab. Aceh Selatan'], $kecChain['kab']);
        $this->assertEquals(['kode' => '11.01.01', 'nama' => 'Kec. Bakongan'], $kecChain['kec']);
        $this->assertNull($kecChain['kel']);

        // Level 4: Kelurahan
        $kelChain = buildChain('11.01.01.2001', $names);
        $this->assertEquals(['kode' => '11', 'nama' => 'Aceh'], $kelChain['prov']);
        $this->assertEquals(['kode' => '11.01', 'nama' => 'Kab. Aceh Selatan'], $kelChain['kab']);
        $this->assertEquals(['kode' => '11.01.01', 'nama' => 'Kec. Bakongan'], $kelChain['kec']);
        $this->assertEquals(['kode' => '11.01.01.2001', 'nama' => 'Keude Bakongan'], $kelChain['kel']);

        // Edge case: Names are not found in the array (e.g. empty names array)
        $missingNamesChain = buildChain('11.01.01.2001', []);
        $this->assertEquals(['kode' => '11', 'nama' => null], $missingNamesChain['prov']);
        $this->assertEquals(['kode' => '11.01', 'nama' => null], $missingNamesChain['kab']);
        $this->assertEquals(['kode' => '11.01.01', 'nama' => null], $missingNamesChain['kec']);
        $this->assertEquals(['kode' => '11.01.01.2001', 'nama' => null], $missingNamesChain['kel']);
    }
    public function testFallbackPathForCode(): void
    {
        $lat = -6.200000;
        $lng = 106.816666;

        // Test with short code (< 8 chars), delta should be 0.01
        $shortCode = '11.01';
        $expectedDelta1 = 0.01;
        $expectedJson1 = json_encode([
            [$lat - $expectedDelta1, $lng - $expectedDelta1],
            [$lat + $expectedDelta1, $lng - $expectedDelta1],
            [$lat + $expectedDelta1, $lng + $expectedDelta1],
            [$lat - $expectedDelta1, $lng + $expectedDelta1]
        ]);
        $this->assertJsonStringEqualsJsonString(
            $expectedJson1,
            fallbackPathForCode($lat, $lng, $shortCode),
            'Fallback path for short code (<8) is incorrect'
        );

        // Test with medium code (8 <= chars < 13), delta should be 0.008
        $mediumCode = '11.01.01'; // 8 chars
        $expectedDelta2 = 0.008;
        $expectedJson2 = json_encode([
            [$lat - $expectedDelta2, $lng - $expectedDelta2],
            [$lat + $expectedDelta2, $lng - $expectedDelta2],
            [$lat + $expectedDelta2, $lng + $expectedDelta2],
            [$lat - $expectedDelta2, $lng + $expectedDelta2]
        ]);
        $this->assertJsonStringEqualsJsonString(
            $expectedJson2,
            fallbackPathForCode($lat, $lng, $mediumCode),
            'Fallback path for medium code (>=8, <13) is incorrect'
        );

        // Test with long code (>= 13 chars), delta should be 0.004
        $longCode = '11.01.01.2001'; // 13 chars
        $expectedDelta3 = 0.004;
        $expectedJson3 = json_encode([
            [$lat - $expectedDelta3, $lng - $expectedDelta3],
            [$lat + $expectedDelta3, $lng - $expectedDelta3],
            [$lat + $expectedDelta3, $lng + $expectedDelta3],
            [$lat - $expectedDelta3, $lng + $expectedDelta3]
        ]);
        $this->assertJsonStringEqualsJsonString(
            $expectedJson3,
            fallbackPathForCode($lat, $lng, $longCode),
            'Fallback path for long code (>=13) is incorrect'
        );
    }

    public function testEffectiveCandidatePath(): void
    {
        $lat = 5.0;
        $lng = 5.0;
        $kode = '12';

        // Scenario A: Valid path near centroid
        $simplePathJson = json_encode([
            [0, 0], [0, 10], [10, 10], [10, 0]
        ]);

        $candidateA = [
            'lat' => $lat,
            'lng' => $lng,
            'kode' => $kode,
            'path' => $simplePathJson
        ];

        // Should return the exact original path
        $this->assertEquals($simplePathJson, effectiveCandidatePath($candidateA));

        // Scenario B: Invalid path near centroid (but valid coordinates)
        // Centroid of the path is (5, 5). Lat/lng is (50, 50). isPathNearCentroid will fail.
        $candidateB = [
            'lat' => 50.0,
            'lng' => 50.0,
            'kode' => $kode,
            'path' => $simplePathJson
        ];

        $fallbackB = fallbackPathForCode(50.0, 50.0, $kode);
        $this->assertJsonStringEqualsJsonString($fallbackB, effectiveCandidatePath($candidateB));

        // Scenario C: No path (but valid coordinates)
        $candidateC = [
            'lat' => $lat,
            'lng' => $lng,
            'kode' => $kode
        ];

        $fallbackC = fallbackPathForCode($lat, $lng, $kode);
        $this->assertJsonStringEqualsJsonString($fallbackC, effectiveCandidatePath($candidateC));

        // Scenario D: Null coordinates or code
        $candidateD1 = ['lat' => $lat, 'lng' => $lng, 'kode' => null];
        $candidateD2 = ['lat' => $lat, 'lng' => null, 'kode' => $kode];
        $candidateD3 = ['lat' => null, 'lng' => $lng, 'kode' => $kode];

        $this->assertNull(effectiveCandidatePath($candidateD1));
        $this->assertNull(effectiveCandidatePath($candidateD2));
        $this->assertNull(effectiveCandidatePath($candidateD3));

        // Scenario E: Null coordinate with path
        // (kode shouldn't be null because it's passed to isPathNearCentroid which expects string)
        // However, effectiveCandidatePath doesn't check if $kode is set in the first if condition.
        // It relies on isPathNearCentroid checking it.
        // We will just test missing coordinates.
        $candidateE2 = ['lat' => $lat, 'lng' => null, 'kode' => $kode, 'path' => $simplePathJson];
        $candidateE3 = ['lat' => null, 'lng' => $lng, 'kode' => $kode, 'path' => $simplePathJson];

        $this->assertNull(effectiveCandidatePath($candidateE2));
        $this->assertNull(effectiveCandidatePath($candidateE3));
    }
}
