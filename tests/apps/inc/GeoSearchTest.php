<?php
/*
BISMILLAAHIRRAHMAANIRRAHIIM - In the Name of Allah, Most Gracious, Most Merciful
================================================================================
filename : GeoSearchTest.php
purpose  : PHPUnit test for apps/inc/geo_search.php
created  : 2026-11-10
last edit: 2026-11-10 08:28:00
author   : cahya dsn & AI Assistant
================================================================================
MIT License
copyright (c) 2017-2026 by cahya dsn; cahyadsn@gmail.com
================================================================================*/
namespace Tests;

use PHPUnit\Framework\TestCase;
use PDO;

class GeoSearchTest extends TestCase
{
    private $geoSearchFile;

    protected function setUp(): void
    {
        $this->geoSearchFile = realpath(__DIR__ . '/../../../apps/inc/geo_search.php');
        $_GET = [];

        // Ensure database connection is ready
        if (!isset($GLOBALS['db']) || !($GLOBALS['db'] instanceof PDO)) {
            require __DIR__ . '/../../../apps/inc/db.php';
            if (isset($db) && $db instanceof PDO) {
                $GLOBALS['db'] = $db;
            }
        }
    }

    protected function tearDown(): void
    {
        $_GET = [];
    }

    private function executeSearch($params = [])
    {
        global $db;
        if (isset($GLOBALS['db'])) {
            $db = $GLOBALS['db'];
        }
        $_GET = $params;
        ob_start();
        $cwd = getcwd();
        chdir(dirname($this->geoSearchFile));
        @include $this->geoSearchFile;
        chdir($cwd);
        $output = ob_get_clean();

        return json_decode($output, true);
    }

    public function testSearchEmptyQueryReturnsFalse()
    {
        $res = $this->executeSearch([]);
        $this->assertIsArray($res);
        $this->assertFalse($res['status']);
        $this->assertEmpty($res['results']);
    }

    public function testSearchShortQueryReturnsFalse()
    {
        $res = $this->executeSearch(['q' => 'a']);
        $this->assertIsArray($res);
        $this->assertFalse($res['status']);
        $this->assertEmpty($res['results']);
    }

    public function testSearchTooLongQueryReturnsFalse()
    {
        $longQuery = str_repeat('a', 51);
        $res = $this->executeSearch(['q' => $longQuery]);
        $this->assertIsArray($res);
        $this->assertFalse($res['status']);
        $this->assertEmpty($res['results']);
    }

    public function testSearchInvalidCharactersRejected()
    {
        $res = $this->executeSearch(['q' => '<script>alert(1)</script>']);
        $this->assertIsArray($res);
        $this->assertFalse($res['status']);
        $this->assertEmpty($res['results']);
    }

    public function testSearchValidQueryReturnsResults()
    {
        $res = $this->executeSearch(['q' => 'Sleman', 'limit' => 5]);
        $this->assertIsArray($res);
        $this->assertTrue($res['status']);
        $this->assertEquals('Sleman', $res['query']);
        $this->assertNotEmpty($res['results']);

        $first = $res['results'][0];
        $this->assertArrayHasKey('kode', $first);
        $this->assertArrayHasKey('nama', $first);
        $this->assertArrayHasKey('category', $first);
        $this->assertArrayHasKey('tipe', $first);
        $this->assertArrayHasKey('chain', $first);
    }

    public function testSearchCodeReturnsMatchingRegions()
    {
        $res = $this->executeSearch(['q' => '31.71', 'limit' => 5]);
        $this->assertIsArray($res);
        $this->assertTrue($res['status']);
        $this->assertNotEmpty($res['results']);

        foreach ($res['results'] as $item) {
            $this->assertStringStartsWith('31.71', $item['kode']);
        }
    }

    public function testSearchIslandReturnsIslandCategory()
    {
        $res = $this->executeSearch(['q' => 'Rondo', 'limit' => 8]);
        $this->assertIsArray($res);
        $this->assertTrue($res['status']);

        $hasIsland = false;
        foreach ($res['results'] as $item) {
            if ($item['category'] === 'pulau') {
                $hasIsland = true;
                $this->assertArrayHasKey('lat', $item);
                $this->assertArrayHasKey('lng', $item);
                $this->assertArrayHasKey('status', $item);
                $this->assertArrayHasKey('luas', $item);
                break;
            }
        }
        $this->assertTrue($hasIsland, 'Expected at least one island in results for Rondo');
    }

    public function testSearchLimitsMaxResults()
    {
        $res = $this->executeSearch(['q' => 'Jawa', 'limit' => 3]);
        $this->assertIsArray($res);
        $this->assertTrue($res['status']);
        $wilayahCount = 0;
        foreach ($res['results'] as $item) {
            if ($item['category'] === 'wilayah') {
                $wilayahCount++;
            }
        }
        $this->assertLessThanOrEqual(3, $wilayahCount);
    }
}
