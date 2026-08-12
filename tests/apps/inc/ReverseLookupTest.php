<?php

namespace Tests;

use PHPUnit\Framework\TestCase;
use PDO;
use PDOException;

class ReverseLookupTest extends TestCase
{
    private $dbFile = __DIR__ . '/../../../apps/inc/db.php';
    private $reverseLookupFile = __DIR__ . '/../../../apps/inc/reverse_lookup.php';

    private function loadReverseLookupFunctions()
    {
        if (function_exists('buildChain')) {
            return;
        }

        $originalGet = $_GET;
        $originalDb = isset($GLOBALS['db']) ? $GLOBALS['db'] : null;

        $_GET['lat'] = '0';
        $_GET['lng'] = '0';

        $mockPdo = $this->createMock(PDO::class);
        $mockPdo->method('prepare')->willThrowException(new PDOException('Mocked'));
        $GLOBALS['db'] = $mockPdo;

        $tempLogFile = sys_get_temp_dir() . '/test_error_reverse_lookup.log';
        $originalErrorLog = ini_get('error_log');
        ini_set('error_log', $tempLogFile);

        ob_start();
        $cwd = getcwd();
        chdir(dirname($this->reverseLookupFile));
        @require_once basename($this->reverseLookupFile);
        chdir($cwd);
        ob_end_clean();

        ini_set('error_log', $originalErrorLog);
        @unlink($tempLogFile);

        $_GET = $originalGet;
        if ($originalDb !== null) {
            $GLOBALS['db'] = $originalDb;
        } else {
            unset($GLOBALS['db']);
        }
    }

    public function testBuildChainFull()
    {
        $this->loadReverseLookupFunctions();

        $kode = '11.01.01.2001';
        $names = [
            '11' => 'ACEH',
            '11.01' => 'KABUPATEN ACEH SELATAN',
            '11.01.01' => 'BAKONGAN',
            '11.01.01.2001' => 'KEUDE BAKONGAN'
        ];

        $expected = [
            'prov' => ['kode' => '11', 'nama' => 'ACEH'],
            'kab'  => ['kode' => '11.01', 'nama' => 'KABUPATEN ACEH SELATAN'],
            'kec'  => ['kode' => '11.01.01', 'nama' => 'BAKONGAN'],
            'kel'  => ['kode' => '11.01.01.2001', 'nama' => 'KEUDE BAKONGAN']
        ];

        $result = buildChain($kode, $names);
        $this->assertEquals($expected, $result);
    }

    public function testBuildChainPartial()
    {
        $this->loadReverseLookupFunctions();

        $kode = '32.73'; // Only Province and Kabupaten/Kota
        $names = [
            '32' => 'JAWA BARAT',
            '32.73' => 'KOTA BANDUNG'
        ];

        $expected = [
            'prov' => ['kode' => '32', 'nama' => 'JAWA BARAT'],
            'kab'  => ['kode' => '32.73', 'nama' => 'KOTA BANDUNG'],
            'kec'  => null,
            'kel'  => null
        ];

        $result = buildChain($kode, $names);
        $this->assertEquals($expected, $result);
    }

    public function testBuildChainMissingNames()
    {
        $this->loadReverseLookupFunctions();

        $kode = '31.01.01'; // Prov, Kab, Kec
        $names = [
            '31' => 'DKI JAKARTA',
            // Missing Kab name
            '31.01.01' => 'KEPULAUAN SERIBU UTARA'
        ];

        $expected = [
            'prov' => ['kode' => '31', 'nama' => 'DKI JAKARTA'],
            'kab'  => ['kode' => '31.01', 'nama' => null], // Name should be null
            'kec'  => ['kode' => '31.01.01', 'nama' => 'KEPULAUAN SERIBU UTARA'],
            'kel'  => null
        ];

        $result = buildChain($kode, $names);
        $this->assertEquals($expected, $result);
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testThrowableCatchBlockReturnsJsonError()
    {
        $_GET['lat'] = '-6.200000';
        $_GET['lng'] = '106.816666';

        putenv('DB_HOST=invalid');

        $original = ini_get('error_log');
        ini_set('error_log', '/dev/null');

        ob_start();
        require_once $this->dbFile;
        ob_get_clean();

        ini_set('error_log', $original);

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
