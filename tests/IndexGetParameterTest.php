<?php

namespace Tests;

use PHPUnit\Framework\TestCase;
use PDO;

class IndexGetParameterTest extends TestCase
{
    private $dbFile = __DIR__ . '/../apps/inc/db.php';
    private $indexFile = __DIR__ . '/../index.php';

    public function testInvalidGetIdLengthHandlesGracefully()
    {
        // Test invalid length 1
        $_GET['id'] = '1';

        ob_start();
        include $this->indexFile;
        $output = ob_get_clean();

        // The output should be completely empty and not throw any undefined index errors
        $this->assertEmpty(trim($output), 'Expected empty output for invalid ID length');
    }

    public function testAnotherInvalidGetIdLengthHandlesGracefully()
    {
        // Test invalid length 3
        $_GET['id'] = '123';

        ob_start();
        include $this->indexFile;
        $output = ob_get_clean();

        $this->assertEmpty(trim($output), 'Expected empty output for invalid ID length');
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testValidGetIdLengthWithMockDb()
    {
        // Suppress errors about failing to connect to invalid DB host
        $original = ini_get('error_log');
        ini_set('error_log', '/dev/null');

        $_GET['id'] = '11';

        // We will create a mock PDO to replace the one created in db.php
        $mockStatement = $this->createMock(\PDOStatement::class);
        $mockStatement->method('execute')->willReturn(true);

        // Simulate returning one row
        $mockData = new \stdClass();
        $mockData->kode = '11.01';
        $mockData->nama = 'KAB. SIMEULUE';

        $mockStatement->expects($this->exactly(2))
            ->method('fetchObject')
            ->willReturnOnConsecutiveCalls($mockData, false);

        $mockPdo = $this->createMock(PDO::class);
        $mockPdo->method('prepare')->willReturn($mockStatement);

        // Disable DB output
        putenv('DB_HOST=invalid');
        ob_start();
        require_once $this->dbFile;
        ob_get_clean();

        // Override the global $db created by db.php
        global $db, $wil;
        $db = $mockPdo;

        ob_start();
        include $this->indexFile;
        $output = ob_get_clean();

        $this->assertStringContainsString('Pilih Kota/Kabupaten', $output);
        $this->assertStringContainsString('11.01', $output);
        $this->assertStringContainsString('KAB. SIMEULUE', $output);
        ini_set('error_log', $original);
    }

    protected function tearDown(): void
    {
        unset($_GET['id']);
    }
}
