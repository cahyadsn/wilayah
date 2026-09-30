<?php

namespace Tests;

use PHPUnit\Framework\TestCase;
use PDO;

class IndexGetParameterTest extends TestCase
{
    private $indexFile = __DIR__ . '/../index.php';

    public function testInvalidGetIdLengthHandlesGracefully()
    {
        // Test invalid length 1
        $_GET['id'] = '1';

        ob_start();
        // redirect cache file write/read path to temp dir to avoid state leakage
        $code = file_get_contents($this->indexFile);
        $code = str_replace("require_once 'apps/inc/db.php';", '', $code);
        $code = str_replace("__DIR__ . '/cache'", "sys_get_temp_dir()", $code);
        eval('?>' . $code);
        $output = ob_get_clean();

        // The output should be completely empty and not throw any undefined index errors
        $this->assertEmpty(trim($output), 'Expected empty output for invalid ID length');
    }

    public function testAnotherInvalidGetIdLengthHandlesGracefully()
    {
        // Test invalid length 3
        $_GET['id'] = '123';

        ob_start();
        // redirect cache file write/read path to temp dir to avoid state leakage
        $code = file_get_contents($this->indexFile);
        $code = str_replace("require_once 'apps/inc/db.php';", '', $code);
        $code = str_replace("__DIR__ . '/cache'", "sys_get_temp_dir()", $code);
        eval('?>' . $code);
        $output = ob_get_clean();

        $this->assertEmpty(trim($output), 'Expected empty output for invalid ID length');
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testValidGetIdLengthWithMockDb()
    {
        $_GET['id'] = '11';

        // We will create a mock PDO to replace the one created in db.php
        $mockStatement = $this->createMock(\PDOStatement::class);
        $mockStatement->method('execute')->willReturn(true);

        // Simulate returning one row
        $mockData = new \stdClass();
        $mockData->kode = '11.01';
        $mockData->nama = 'KAB. SIMEULUE';

        $mockStatement->method('fetchObject')
            ->willReturnOnConsecutiveCalls($mockData, false);

        $mockPdo = $this->createMock(PDO::class);
        $mockPdo->method('prepare')->willReturn($mockStatement);

        // Override the global $db and bypass db.php
        global $db, $wil;
        $db = $mockPdo;

        ob_start();
        // redirect cache file write/read path to temp dir to avoid state leakage
        $code = file_get_contents($this->indexFile);
        $code = str_replace("require_once 'apps/inc/db.php';", '', $code);
        $code = str_replace("__DIR__ . '/cache'", "sys_get_temp_dir()", $code);
        eval('?>' . $code);
        $output = ob_get_clean();

        $this->assertStringContainsString('Pilih Kota/Kabupaten', $output);
        $this->assertStringContainsString('11.01', $output);
        $this->assertStringContainsString('KAB. SIMEULUE', $output);
    }

    protected function tearDown(): void
    {
        unset($_GET['id']);
    }
}
