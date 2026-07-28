<?php

use PHPUnit\Framework\TestCase;

class AppsIncDbTest extends TestCase
{
    protected function tearDown(): void
    {
        putenv('DB_HOST');
        putenv('DB_USER');
        putenv('DB_PASS');
        putenv('DB_NAME');
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testDefaultConnectionVariables()
    {
        putenv('DB_HOST');
        putenv('DB_USER');
        putenv('DB_PASS');
        putenv('DB_NAME');

        $tmpLog = tempnam(sys_get_temp_dir(), 'err_log_');
        ini_set('error_log', $tmpLog);

        ob_start();
        require __DIR__ . '/../apps/inc/db.php';
        ob_get_clean();

        $this->assertEquals('localhost', $dbhost);
        $this->assertEquals('', $dbuser);
        $this->assertEquals('', $dbpass);
        $this->assertEquals('wilayah', $dbname);
        $this->assertEquals("mysql:dbname=wilayah;host=localhost", $db_dsn);

        unlink($tmpLog);
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testCustomEnvironmentVariables()
    {
        putenv('DB_HOST=127.0.0.1');
        putenv('DB_USER=testuser');
        putenv('DB_PASS=testpass');
        putenv('DB_NAME=testdb');

        $tmpLog = tempnam(sys_get_temp_dir(), 'err_log_');
        ini_set('error_log', $tmpLog);

        ob_start();
        require __DIR__ . '/../apps/inc/db.php';
        ob_get_clean();

        $this->assertEquals('127.0.0.1', $dbhost);
        $this->assertEquals('testuser', $dbuser);
        $this->assertEquals('testpass', $dbpass);
        $this->assertEquals('testdb', $dbname);
        $this->assertEquals("mysql:dbname=testdb;host=127.0.0.1", $db_dsn);

        unlink($tmpLog);
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testFailedConnectionHandlesGracefully()
    {
        putenv('DB_HOST=localhost');
        putenv('DB_USER=invalid_user');
        putenv('DB_PASS=invalid_pass');

        $tmpLog = tempnam(sys_get_temp_dir(), 'err_log_');
        ini_set('error_log', $tmpLog);

        ob_start();
        require __DIR__ . '/../apps/inc/db.php';
        $output = ob_get_clean();

        $this->assertStringContainsString('Connection failed: Database error occurred.', $output);
        $logContents = file_get_contents($tmpLog);
        $this->assertStringContainsString('Connection failed: SQLSTATE', $logContents);

        unlink($tmpLog);
    }
}
