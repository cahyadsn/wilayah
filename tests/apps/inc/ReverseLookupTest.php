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
        require_once dirname($this->reverseLookupFile) . '/geo_utils.php';
        @require_once basename($this->reverseLookupFile);
        @require_once __DIR__ . '/../../../apps/inc/geo_utils.php';
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

    public function testEffectiveCandidatePathValid()
    {
        $this->loadReverseLookupFunctions();

        $candidate = [
            'lat' => 10,
            'lng' => 20,
            'kode' => '12',
            'path' => '[[[10, 20], [10.1, 20], [10, 20.1]]]'
        ];

        $result = effectiveCandidatePath($candidate);
        $this->assertEquals('[[[10, 20], [10.1, 20], [10, 20.1]]]', $result);
    }

    public function testEffectiveCandidatePathNotNearCentroidFallsBack()
    {
        $this->loadReverseLookupFunctions();

        $candidate = [
            'lat' => 10,
            'lng' => 20,
            'kode' => '12',
            'path' => '[[[30, 40], [31, 41], [32, 42]]]' // Way off
        ];

        $result = effectiveCandidatePath($candidate);

        // Code len is 2 (>= 0 and < 8), delta is 0.01
        // (float)$lat = 10, (float)$lng = 20
        $expected = json_encode([
            [10 - 0.01, 20 - 0.01],
            [10 + 0.01, 20 - 0.01],
            [10 + 0.01, 20 + 0.01],
            [10 - 0.01, 20 + 0.01]
        ]);
        $this->assertEquals($expected, $result);
    }

    public function testEffectiveCandidatePathEmptyPathFallsBack()
    {
        $this->loadReverseLookupFunctions();

        $candidate = [
            'lat' => 10,
            'lng' => 20,
            'kode' => '12',
            'path' => ''
        ];

        $result = effectiveCandidatePath($candidate);

        $expected = json_encode([
            [10 - 0.01, 20 - 0.01],
            [10 + 0.01, 20 - 0.01],
            [10 + 0.01, 20 + 0.01],
            [10 - 0.01, 20 + 0.01]
        ]);
        $this->assertEquals($expected, $result);
    }

    public function testEffectiveCandidatePathMissingCoordsReturnsNull()
    {
        $this->loadReverseLookupFunctions();

        $candidate = [
            'lat' => null, // missing coordinate
            'lng' => 20,
            'kode' => '12',
            'path' => ''
        ];

        $result = effectiveCandidatePath($candidate);
        $this->assertNull($result);
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
        ini_set('error_log', strtoupper(substr(PHP_OS, 0, 3)) === 'WIN' ? 'NUL' : '/dev/null');

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
