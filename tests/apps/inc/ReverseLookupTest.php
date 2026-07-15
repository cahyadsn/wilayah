<?php

namespace Tests;

use PHPUnit\Framework\TestCase;
use PDO;
use PDOException;

class ReverseLookupTest extends TestCase
{
    private $dbFile = __DIR__ . '/../../../apps/inc/db.php';
    private $reverseLookupFile = __DIR__ . '/../../../apps/inc/reverse_lookup.php';

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testThrowableCatchBlockReturnsJsonError()
    {
        $_GET['lat'] = '-6.200000';
        $_GET['lng'] = '106.816666';

        putenv('DB_HOST=invalid');

        ob_start();
        require_once $this->dbFile;
        ob_get_clean();

        $mockPdo = $this->createMock(PDO::class);
        $mockPdo->method('prepare')
            ->willThrowException(new PDOException('Mocked database failure'));

        global $db;
        $db = $mockPdo;

        ob_start();

        $cwd = getcwd();
        chdir(dirname($this->reverseLookupFile));

        $tempLogFile = sys_get_temp_dir() . '/test_error_reverse_lookup.log';
        $originalErrorLog = ini_get('error_log');
        ini_set('error_log', $tempLogFile);

        include basename($this->reverseLookupFile);

        ini_set('error_log', $originalErrorLog);
        if (file_exists($tempLogFile)) {
            @unlink($tempLogFile);
        }
        chdir($cwd);

        $output = ob_get_clean();

        $this->assertJson($output);
        $decoded = json_decode($output, true);

        $this->assertArrayHasKey('status', $decoded);
        $this->assertFalse($decoded['status']);
        $this->assertArrayHasKey('error', $decoded);
        $this->assertEquals('Internal Server Error', $decoded['error']);
    }
}
