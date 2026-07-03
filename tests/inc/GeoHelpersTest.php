<?php

use PHPUnit\Framework\TestCase;

class GeoHelpersTest extends TestCase {

    protected function setUp(): void {
        require_once __DIR__ . '/../../apps/inc/geo_helpers.php';
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
