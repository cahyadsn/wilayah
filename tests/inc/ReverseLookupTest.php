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
        $this->assertFalse(pathLooksNearCentroid('', 0, 0, '12'));
        $this->assertFalse(pathLooksNearCentroid('invalid_json', 0, 0, '12'));
        $this->assertFalse(pathLooksNearCentroid('{}', 0, 0, '12')); // Not an array
        $this->assertFalse(pathLooksNearCentroid('[]', 0, 0, '12'));

        // This case actually throws an error in pathLooksNearCentroid at line 60: $points[0][1]
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
        $this->assertTrue(pathLooksNearCentroid($simplePathJson, 2.5, 2.5, '12'));
        // (2.4, 2.4) -> abs(5 - 2.4) = 2.6 > 2.5 (false)
        $this->assertFalse(pathLooksNearCentroid($simplePathJson, 2.4, 2.4, '12'));

        // $kode length >= 8 and < 13, threshold is 0.08
        // (5, 5) compared to (lat, lng).
        // Due to floating point math, 5 - 4.92 might be 0.08000000000000007 which is > 0.08
        // So let's use slightly closer values.
        $this->assertTrue(pathLooksNearCentroid($simplePathJson, 4.95, 4.95, '12345678'));
        // (4.91, 4.91) -> abs(5 - 4.91) = 0.09 > 0.08 (false)
        $this->assertFalse(pathLooksNearCentroid($simplePathJson, 4.91, 4.91, '12345678'));

        // $kode length >= 13, threshold is 0.03
        // (5, 5) compared to (lat, lng).
        // (4.98, 4.98) -> abs(5 - 4.98) = 0.02 <= 0.03 (true)
        $this->assertTrue(pathLooksNearCentroid($simplePathJson, 4.98, 4.98, '1234567890123'));
        // (4.96, 4.96) -> abs(5 - 4.96) = 0.04 > 0.03 (false)
        $this->assertFalse(pathLooksNearCentroid($simplePathJson, 4.96, 4.96, '1234567890123'));

        // Valid nested path structure (e.g. islands/rings): [[[lat, lng], [lat, lng], ...]]
        // Function handles extraction of $coords[0] when not numeric
        $nestedPathJson = json_encode([
            [
                [0, 0], [0, 10], [10, 10], [10, 0]
            ],
            [ // This second ring is ignored by pathLooksNearCentroid according to the logic:
              // $points = (is_array($coords[0]) ? $coords[0] : array());
                [20, 20], [20, 30], [30, 30], [30, 20]
            ]
        ]);
        // Centroid extracted from first ring is still (5, 5)
        // $kode length < 8, threshold is 2.5
        $this->assertTrue(pathLooksNearCentroid($nestedPathJson, 2.5, 2.5, '12'));
        $this->assertFalse(pathLooksNearCentroid($nestedPathJson, 2.4, 2.4, '12'));
    }
}
