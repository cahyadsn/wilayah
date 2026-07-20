<?php

namespace Tests\apps\inc;

use PHPUnit\Framework\TestCase;
use PDO;
use PDOStatement;

class GeoUpdateTest extends TestCase
{
    private $geoUpdateFile = __DIR__ . '/../../../apps/inc/geo_update.php';
    private $dbFile = __DIR__ . '/../../../apps/inc/db.php';

    protected function setUp(): void
    {
        // Suppress session warnings since PHPUnit output might have started
        if (session_status() === PHP_SESSION_NONE) {
            @session_start();
        }

        // Setup default authorized session for tests that don't want to die()
        $_SESSION['author'] = 'cahyadsn';

        // Reset $_POST
        $_POST = [];
    }

    public function testUnauthorizedAccessNoSession()
    {
        // Use exec to run in a true separate PHP process to safely catch die()
        $script = <<<PHP
<?php
session_start();
unset(\$_SESSION['author']);
ob_start();
@include '{$this->geoUpdateFile}';
\$output = ob_get_clean();
echo \$output;
PHP;
        $tmpFile = tempnam(sys_get_temp_dir(), 'test_');
        file_put_contents($tmpFile, $script);
        $output = exec('php ' . escapeshellarg($tmpFile) . ' 2>/dev/null');
        unlink($tmpFile);

        $this->assertJson($output);
        $decoded = json_decode($output, true);
        $this->assertFalse($decoded['status']);
        $this->assertEquals('unauthorized', $decoded['msg']);
    }

    public function testUnauthorizedAccessWrongAuthor()
    {
        $script = <<<PHP
<?php
session_start();
\$_SESSION['author'] = 'wrong_user';
ob_start();
@include '{$this->geoUpdateFile}';
\$output = ob_get_clean();
echo \$output;
PHP;
        $tmpFile = tempnam(sys_get_temp_dir(), 'test_');
        file_put_contents($tmpFile, $script);
        $output = exec('php ' . escapeshellarg($tmpFile) . ' 2>/dev/null');
        unlink($tmpFile);

        $this->assertJson($output);
        $decoded = json_decode($output, true);
        $this->assertFalse($decoded['status']);
        $this->assertEquals('unauthorized', $decoded['msg']);
    }

    public function testNoIdProvided()
    {
        // Require db.php first so require_once in target script skips it
        putenv('DB_HOST=127.0.0.1');

        $originalErrorLog = ini_get('error_log');
        ini_set('error_log', '/dev/null');
        ob_start();
        @require_once $this->dbFile;
        ob_end_clean();
        ini_set('error_log', $originalErrorLog);

        ob_start();
        $cwd = getcwd();
        chdir(dirname($this->geoUpdateFile));

        // @ suppresses session_start() warning if session is already active
        @include basename($this->geoUpdateFile);

        chdir($cwd);
        $output = ob_get_clean();

        $this->assertJson($output);
        $decoded = json_decode($output, true);
        $this->assertFalse($decoded['status']);
        $this->assertEquals('do nothing', $decoded['msg']);
    }

    public function testIdProvidedButNoValidFields()
    {
        $_POST['id'] = '1234';

        putenv('DB_HOST=127.0.0.1');

        $originalErrorLog = ini_get('error_log');
        ini_set('error_log', '/dev/null');
        ob_start();
        @require_once $this->dbFile;
        ob_end_clean();
        ini_set('error_log', $originalErrorLog);

        ob_start();
        $cwd = getcwd();
        chdir(dirname($this->geoUpdateFile));
        @include basename($this->geoUpdateFile);
        chdir($cwd);
        $output = ob_get_clean();

        $this->assertJson($output);
        $decoded = json_decode($output, true);
        $this->assertFalse($decoded['status']);
        $this->assertEquals('No valid fields to update', $decoded['msg']);
    }

    public function testValidUpdate()
    {
        $_POST['id'] = '1234';
        $_POST['lat'] = '1.23';
        $_POST['lng'] = '4.56';
        $_POST['path'] = '[[1.23,4.56]]';

        putenv('DB_HOST=invalid');

        $originalErrorLog = ini_get('error_log');
        ini_set('error_log', '/dev/null');
        ob_start();
        @require_once $this->dbFile;
        ob_end_clean();
        ini_set('error_log', $originalErrorLog);

        $mockStmt = $this->createMock(PDOStatement::class);
        $mockStmt->expects($this->once())
                 ->method('execute')
                 ->with([
                     ':lat' => 1.23,
                     ':lng' => 4.56,
                     ':path' => '[[1.23,4.56]]',
                     ':id' => '1234'
                 ])
                 ->willReturn(true);

        $mockPdo = $this->createMock(PDO::class);
        $mockPdo->expects($this->once())
                ->method('prepare')
                ->with($this->callback(function ($sql) {
                    return strpos($sql, 'UPDATE ') === 0 &&
                           strpos($sql, ' SET lat=:lat,lng=:lng,path=:path WHERE kode=:id') !== false;
                }))
                ->willReturn($mockStmt);

        global $db;
        $db = $mockPdo;

        // Set global $tbl_wilayah properly if it was unset or redefined differently
        global $tbl_wilayah;
        $tbl_wilayah = 'wilayah_level_1_2';

        ob_start();
        $cwd = getcwd();
        chdir(dirname($this->geoUpdateFile));
        @include basename($this->geoUpdateFile);
        chdir($cwd);
        $output = ob_get_clean();

        $this->assertJson($output);
        $decoded = json_decode($output, true);
        $this->assertTrue($decoded['status']);
        $this->assertStringContainsString('data saved', $decoded['msg']);
    }
}
