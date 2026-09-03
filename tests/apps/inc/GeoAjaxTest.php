<?php
/*
BISMILLAAHIRRAHMAANIRRAHIIM - In the Name of Allah, Most Gracious, Most Merciful
================================================================================
filename : GeoAjaxTest.php
purpose  : Test for geo_ajax.php, specifically the fallbackBox function.
================================================================================
*/
use PHPUnit\Framework\TestCase;

class GeoAjaxTest extends TestCase {

    protected function setUp(): void {
        // Mock DB credentials so the require_once doesn't actually connect to a real database,
        // or uses a safe dummy config to prevent connection errors
        putenv('DB_HOST=127.0.0.1');
        putenv('DB_USER=root');
        putenv('DB_PASS=');
        putenv('DB_NAME=wilayah'); // Adjust this as necessary based on CI environment

        // Include the procedural script and capture output to prevent "headers already sent"
        ob_start();
        // Since geo_ajax.php now outputs headers, we can't just require it if we want to run multiple tests
        // But we need the function isPathReasonable. Since we need to test isPathReasonable,
        // let's suppress header errors during tests or just skip including if we are going to run tests that don't need it.
        // Or we can mock the header function.
        @require_once __DIR__ . '/../../../apps/inc/geo_ajax.php';
        ob_end_clean();
    }

    public function testFallbackBoxDefaultDelta() {
        $lat = 10.5;
        $lng = 20.5;
        // Default delta is 0.01
        $expected = '[['.($lat-0.01).','.($lng-0.01).'],'
                   .'['.($lat+0.01).','.($lng-0.01).'],'
                   .'['.($lat+0.01).','.($lng+0.01).'],'
                   .'['.($lat-0.01).','.($lng+0.01).']]';
        $this->assertEquals($expected, fallbackBox($lat, $lng));
    }

    public function testFallbackBoxCustomDelta() {
        $lat = -6.2;
        $lng = 106.8;
        $delta = 0.05;
        $expected = '[['.($lat-$delta).','.($lng-$delta).'],'
                   .'['.($lat+$delta).','.($lng-$delta).'],'
                   .'['.($lat+$delta).','.($lng+$delta).'],'
                   .'['.($lat-$delta).','.($lng+$delta).']]';
        $this->assertEquals($expected, fallbackBox($lat, $lng, $delta));
    }

    public function testFallbackBoxZeroCoordinates() {
        $lat = 0;
        $lng = 0;
        $delta = 0.1;
        $expected = '[[-0.1,-0.1],[0.1,-0.1],[0.1,0.1],[-0.1,0.1]]';
        $this->assertEquals($expected, fallbackBox($lat, $lng, $delta));
    }

    public function testFallbackBoxNegativeCoordinates() {
        $lat = -15.5;
        $lng = -20.5;
        $delta = 0.5;
        $expected = '[[-16,-21],[-15,-21],[-15,-20],[-16,-20]]';
        $this->assertEquals($expected, fallbackBox($lat, $lng, $delta));
    }

    public function testFallbackBoxLargeValues() {
        $lat = 1000.5;
        $lng = 2000.5;
        $delta = 500;
        $expected = '[[500.5,1500.5],[1500.5,1500.5],[1500.5,2500.5],[500.5,2500.5]]';
        $this->assertEquals($expected, fallbackBox($lat, $lng, $delta));
    }

    // --- Tests for isPathReasonable ---

    public function testIsPathReasonableEmptyOrNullInputs() {
        // empty path
        $this->assertFalse(isPathReasonable("", 0, 0, "12"));
        // null lat
        $this->assertFalse(isPathReasonable("[[0,0]]", null, 0, "12"));
        // null lng
        $this->assertFalse(isPathReasonable("[[0,0]]", 0, null, "12"));
    }

    public function testIsPathReasonableInvalidJson() {
        $this->assertFalse(isPathReasonable("invalid_json", 0, 0, "12"));
    }

    public function testIsPathReasonableEmptyOrInvalidArray() {
        $this->assertFalse(isPathReasonable("[]", 0, 0, "12"));
        $this->assertFalse(isPathReasonable("[[]]", 0, 0, "12"));
        // Invalid array structure (not numbers)
        $this->assertFalse(isPathReasonable('["a","b"]', 0, 0, "12"));
    }

    public function testIsPathReasonableValidPathKodeLen13() {
        // Threshold: 0.03
        $lat = -6.2;
        $lng = 106.8;
        $kode = "1234567890123";
        // Path center is exactly at lat, lng
        $path = '[[-6.21, 106.79], [-6.19, 106.81]]';
        $this->assertTrue(isPathReasonable($path, $lat, $lng, $kode));

        // Center shifted by slightly less than 0.03 to avoid floating point issues, should be true
        $pathShifted = '[[-6.18, 106.82], [-6.17, 106.83]]'; // Center: -6.175, 106.825, diff lat: 0.025, diff lng: 0.025
        $this->assertTrue(isPathReasonable($pathShifted, $lat, $lng, $kode));

        // Center shifted by > 0.03, should be false
        $pathFar = '[[-6.15, 106.85], [-6.13, 106.87]]'; // Center: -6.14, 106.86, diff: >0.03
        $this->assertFalse(isPathReasonable($pathFar, $lat, $lng, $kode));
    }

    public function testIsPathReasonableValidPathKodeLen8() {
        // Threshold: 0.08
        $lat = -6.2;
        $lng = 106.8;
        $kode = "12345678";
        // Path center shifted by slightly less than 0.08
        $path = '[[-6.14, 106.86], [-6.12, 106.88]]'; // Center: -6.13, 106.87, diff: 0.07
        $this->assertTrue(isPathReasonable($path, $lat, $lng, $kode));

        // Path center shifted by > 0.08
        $pathFar = '[[-6.11, 106.89], [-6.09, 106.91]]'; // Center: -6.10, 106.90, diff: 0.10
        $this->assertFalse(isPathReasonable($pathFar, $lat, $lng, $kode));
    }

    public function testIsPathReasonableValidPathKodeLen2() {
        // Threshold: 2.5
        $lat = -6.2;
        $lng = 106.8;
        $kode = "12";
        // Path center shifted by slightly less than 2.5
        $path = '[[-4.0, 109.0], [-3.8, 109.2]]'; // Center: -3.9, 109.1, diff lat: 2.3, lng: 2.3
        $this->assertTrue(isPathReasonable($path, $lat, $lng, $kode));

        // Path center shifted by > 2.5
        $pathFar = '[[-3.0, 110.0], [-2.8, 110.2]]'; // Center: -2.9, 110.1, diff: >2.5
        $this->assertFalse(isPathReasonable($pathFar, $lat, $lng, $kode));
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testGeoAjaxFallbackLogic() {
        // This test must run in a separate process, but PHPUnit might still run setUp()
        // before launching the separate process, or setUp() inside the process might include it.
        // Wait, if setUp() does require_once, it's done. But geo_ajax.php has functions.
        // We will read geo_ajax.php, replace function declarations and require_once, and eval it,
        // or we just let it run if it's a completely fresh process.
        // Let's use file_get_contents and remove the function isPathReasonable to avoid redeclaration,
        // because setUp() includes it! No, wait, setUp() is run for EVERY test, including this one in the new process.

        // Setup env so db.php doesn't actually connect to real DB but we can intercept it
        putenv('DB_HOST=127.0.0.1');

        $mockStmt = $this->createMock(\PDOStatement::class);
        $mockStmt->expects($this->any())
                 ->method('execute')
                 ->willReturn(true);
        $mockStmt->expects($this->any())
                 ->method('fetchObject')
                 ->willReturn(false); // First query returns empty

        $mockPdo = $this->createMock(\PDO::class);
        $mockPdo->expects($this->any())
                ->method('prepare')
                ->willReturn($mockStmt);

        // Include db.php first to mock the $db connection it creates
        $originalErrorLog = ini_get('error_log');
        ini_set('error_log', strtoupper(substr(PHP_OS, 0, 3)) === 'WIN' ? 'NUL' : '/dev/null');
        ob_start();
        @require_once __DIR__ . "/../../../apps/inc/db.php";
        ob_end_clean();
        ini_set('error_log', $originalErrorLog);

        global $db, $tbl_wilayah;
        $db = $mockPdo;
        $tbl_wilayah = 'wilayah';

        $_GET = ['id' => '12', 'geo' => '1'];

        // Execute the procedural script capturing its output
        // We need to strip out function isPathReasonable because setUp() already included geo_ajax.php once
        // and PHP does not allow redeclaring functions.
        $script = file_get_contents(__DIR__ . "/../../../apps/inc/geo_ajax.php");

        // Strip require_once and the function declaration block
        $script = preg_replace('/require_once\s+[^;]+;/', '', $script);
        // Using a regex to remove function isPathReasonable(...) { ... }
        // Since it's a bit complex to regex a function with nested braces, we can just
        // replace the function keyword to create a dummy name, or better, since we know its exact structure:
        $script = str_replace('function isPathReasonable(', 'function dummy_isPathReasonable_test_avoid_redeclare(', $script);

        ob_start();
        eval('?>' . $script);
        $output = ob_get_clean();

        $result = json_decode($output, true);
        $this->assertIsArray($result, "Expected JSON output to decode to an array");
        $this->assertFalse($result['status']);
        $this->assertEquals('an error occured', $result['error']);
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testGeoAjaxIdIsArray() {
        // Setup env so db.php doesn't actually connect to real DB but we can intercept it
        putenv('DB_HOST=127.0.0.1');

        $mockPdo = $this->createMock(\PDO::class);

        // Include db.php first to mock the $db connection it creates
        $originalErrorLog = ini_get('error_log');
        ini_set('error_log', strtoupper(substr(PHP_OS, 0, 3)) === 'WIN' ? 'NUL' : '/dev/null');
        ob_start();
        @require_once __DIR__ . "/../../../apps/inc/db.php";
        ob_end_clean();
        ini_set('error_log', $originalErrorLog);

        global $db, $tbl_wilayah;
        $db = $mockPdo;
        $tbl_wilayah = 'wilayah';

        $_GET = ['id' => ['an_array']];

        $script = file_get_contents(__DIR__ . "/../../../apps/inc/geo_ajax.php");
        $script = preg_replace('/require_once\s+[^;]+;/', '', $script);
        $script = str_replace('function isPathReasonable(', 'function dummy_isPathReasonable_test_avoid_redeclare_array(', $script);

        ob_start();
        eval('?>' . $script);
        $output = ob_get_clean();

        $result = json_decode($output, true);
        $this->assertIsArray($result, "Expected JSON output to decode to an array");
        $this->assertFalse($result['status']);
        $this->assertEquals('an error occured', $result['error']);
    }
}
