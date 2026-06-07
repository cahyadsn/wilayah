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
}
