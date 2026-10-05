<?php
/*
BISMILLAAHIRRAHMAANIRRAHIIM - In the Name of Allah, Most Gracious, Most Merciful
================================================================================
filename  : GeoHelpersTest.php
purpose   : PHPUnit tests for apps/inc/geo_helpers.php
last edit : 2026-10-05 14:34:45
================================================================================*/

use PHPUnit\Framework\TestCase;

class GeoHelpersTest extends TestCase {

    protected function setUp(): void {
        require_once __DIR__ . '/../../apps/inc/geo_helpers.php';
    }

    public function testFallbackBoxDefaultDelta() {
        $lat = 10.5;
        $lng = 20.5;
        // Default delta is 0.01
        $expected = [
            [$lat-0.01, $lng-0.01],
            [$lat+0.01, $lng-0.01],
            [$lat+0.01, $lng+0.01],
            [$lat-0.01, $lng+0.01]
        ];
        $this->assertEquals($expected, fallbackBox($lat, $lng));
    }

    public function testFallbackBoxCustomDelta() {
        $lat = -6.2;
        $lng = 106.8;
        $delta = 0.05;
        $expected = [
            [$lat-$delta, $lng-$delta],
            [$lat+$delta, $lng-$delta],
            [$lat+$delta, $lng+$delta],
            [$lat-$delta, $lng+$delta]
        ];
        $this->assertEquals($expected, fallbackBox($lat, $lng, $delta));
    }

    public function testFallbackBoxZeroCoordinates() {
        $lat = 0;
        $lng = 0;
        $delta = 0.1;
        $expected = [[-0.1,-0.1],[0.1,-0.1],[0.1,0.1],[-0.1,0.1]];
        $this->assertEquals($expected, fallbackBox($lat, $lng, $delta));
    }

    public function testFallbackBoxNegativeCoordinates() {
        $lat = -15.5;
        $lng = -20.5;
        $delta = 0.5;
        $expected = [[-16,-21],[-15,-21],[-15,-20],[-16,-20]];
        $this->assertEquals($expected, fallbackBox($lat, $lng, $delta));
    }

    public function testFallbackBoxLargeValues() {
        $lat = 1000.5;
        $lng = 2000.5;
        $delta = 500;
        $expected = [[500.5,1500.5],[1500.5,1500.5],[1500.5,2500.5],[500.5,2500.5]];
        $this->assertEquals($expected, fallbackBox($lat, $lng, $delta));
    }

    public function testFallbackPathForCodeLength13() {
        $lat = -6.2;
        $lng = 106.8;
        $kode = '1234567890123'; // 13 characters
        $delta = 0.004;
        $expected = fallbackBox($lat, $lng, $delta);
        $this->assertEquals($expected, fallbackPathForCode($lat, $lng, $kode));
    }

    public function testFallbackPathForCodeLength8() {
        $lat = -6.2;
        $lng = 106.8;
        $kode = '12345678'; // 8 characters
        $delta = 0.008;
        $expected = fallbackBox($lat, $lng, $delta);
        $this->assertEquals($expected, fallbackPathForCode($lat, $lng, $kode));
    }

    public function testFallbackPathForCodeShort() {
        $lat = -6.2;
        $lng = 106.8;
        $kode = '1234'; // 4 characters (less than 8)
        $delta = 0.01;
        $expected = fallbackBox($lat, $lng, $delta);
        $this->assertEquals($expected, fallbackPathForCode($lat, $lng, $kode));
    }

    public function testFallbackPathForCodeEmpty() {
        $lat = -6.2;
        $lng = 106.8;
        $kode = ''; // 0 characters
        $delta = 0.01;
        $expected = fallbackBox($lat, $lng, $delta);
        $this->assertEquals($expected, fallbackPathForCode($lat, $lng, $kode));
    }

    public function testGetIslandsForCodeInvalidInputs() {
        $mockDb = $this->createMock(\PDO::class);
        $this->assertEquals([], getIslandsForCode($mockDb, 'wilayah_pulau', ''));
        $this->assertEquals([], getIslandsForCode($mockDb, 'wilayah_pulau', '11.01.01')); // 8 chars - only 2 and 5 allowed
    }

    public function testGetIslandsForCodeValidProvince() {
        $mockStmt = $this->createMock(\PDOStatement::class);
        $mockStmt->expects($this->once())
                 ->method('execute')
                 ->with([':id' => '11'])
                 ->willReturn(true);
        $mockStmt->expects($this->exactly(2))
                 ->method('fetchObject')
                 ->willReturnOnConsecutiveCalls(
                     (object)[
                         'kode' => '11.01.40001',
                         'nama' => 'Pulau Batukapal',
                         'lat' => '-3.3176',
                         'lng' => '97.1283',
                         'status' => 'TBP',
                         'luas' => 0.0006
                     ],
                     false
                 );

        $mockDb = $this->createMock(\PDO::class);
        $mockDb->expects($this->once())
               ->method('prepare')
               ->with("SELECT kode, nama, lat, lng, status, luas FROM wilayah_pulau WHERE kode LIKE CONCAT(:id, '.%') ORDER BY nama ASC")
               ->willReturn($mockStmt);

        $result = getIslandsForCode($mockDb, 'wilayah_pulau', '11');
        $this->assertCount(1, $result);
        $this->assertEquals('11.01.40001', $result[0]['kode']);
        $this->assertEquals('Pulau Batukapal', $result[0]['nama']);
        $this->assertEquals(-3.3176, $result[0]['lat']);
        $this->assertEquals(97.1283, $result[0]['lng']);
        $this->assertEquals('TBP', $result[0]['status']);
        $this->assertEquals(0.0006, $result[0]['luas']);
    }
}
