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
}
