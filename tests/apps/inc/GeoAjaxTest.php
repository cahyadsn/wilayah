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
        require_once __DIR__ . '/../../../apps/inc/geo_ajax.php';
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
}
