<?php

use PHPUnit\Framework\TestCase;

class GeoUtilsTest extends TestCase {

    protected function setUp(): void {
        require_once __DIR__ . '/../../apps/inc/geo_utils.php';
    }

    public function testEmptyPath() {
        $this->assertFalse(isPathNearCentroid('', 10.0, 20.0, '12'));
        $this->assertFalse(isPathNearCentroid(null, 10.0, 20.0, '12'));
    }

    public function testNullCoordinates() {
        $this->assertFalse(isPathNearCentroid('[[10,20]]', null, 20.0, '12'));
        $this->assertFalse(isPathNearCentroid('[[10,20]]', 10.0, null, '12'));
    }

    public function testInvalidPath() {
        // Not a JSON string
        $this->assertFalse(isPathNearCentroid('invalid_json', 10.0, 20.0, '12'));
        // Empty array after decode
        $this->assertFalse(isPathNearCentroid('[]', 10.0, 20.0, '12'));
        // Decode not to an array
        $this->assertFalse(isPathNearCentroid('123', 10.0, 20.0, '12'));
    }

    public function testEmptyPoints() {
        // nested array without valid coordinates (JSON string representation)
        $this->assertFalse(isPathNearCentroid('[[]]', 10.0, 20.0, '12'));
    }

    public function testNearCentroidWithThresholds() {
        // Path with bounding box
        // latMin = 0, latMax = 10 -> centerLat = 5
        // lngMin = 0, lngMax = 10 -> centerLng = 5
        $path = '[[0,0], [0,10], [10,10], [10,0]]';

        // For kode length < 8 (threshold 2.5)
        // center (5, 5)
        // Check exact match
        $this->assertTrue(isPathNearCentroid($path, 5.0, 5.0, '12'));

        // Check within threshold
        $this->assertTrue(isPathNearCentroid($path, 7.5, 7.5, '1234567'));

        // Check slightly outside threshold
        $this->assertFalse(isPathNearCentroid($path, 7.51, 5.0, '1234567'));
        $this->assertFalse(isPathNearCentroid($path, 5.0, 7.51, '1234567'));

        // For kode length >= 8 and < 13 (threshold 0.08)
        $this->assertTrue(isPathNearCentroid($path, 5.07, 5.07, '12345678'));
        $this->assertFalse(isPathNearCentroid($path, 5.09, 5.0, '12345678'));
        $this->assertFalse(isPathNearCentroid($path, 5.0, 5.09, '12345678'));

        // For kode length >= 13 (threshold 0.03)
        $this->assertTrue(isPathNearCentroid($path, 5.02, 5.02, '1234567890123'));
        $this->assertFalse(isPathNearCentroid($path, 5.04, 5.0, '1234567890123'));
        $this->assertFalse(isPathNearCentroid($path, 5.0, 5.04, '1234567890123'));
    }

    public function testNestedPathArray() {
        // When points are deeply nested: e.g. [[ [lat, lng], [lat, lng] ]]
        // latMin = 0, latMax = 10 -> centerLat = 5
        // lngMin = 0, lngMax = 10 -> centerLng = 5
        $path = '[[[0,0], [0,10], [10,10], [10,0]]]';

        $this->assertTrue(isPathNearCentroid($path, 5.0, 5.0, '12'));
        $this->assertTrue(isPathNearCentroid($path, 7.5, 7.5, '12'));
    }
}
