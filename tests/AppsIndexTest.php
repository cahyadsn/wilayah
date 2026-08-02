<?php

namespace Tests;

use PHPUnit\Framework\TestCase;

class AppsIndexTest extends TestCase
{
    private $indexFile = __DIR__ . '/../apps/index.php';

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testCsrfTokenGeneratedOnLoad()
    {
        // Suppress errors about failing to connect to invalid DB host
        $original = ini_get('error_log');
        ini_set('error_log', '/dev/null');

        // Simulate an empty session
        $_SESSION = [];

        ob_start();

        // Read the index file
        $code = file_get_contents($this->indexFile);

        // Suppress headers to prevent "headers already sent" errors in PHPUnit
        $code = str_replace('header(', '@header(', $code);

        // Change working directory to ensure relative requires like 'inc/db.php' resolve correctly
        $oldCwd = getcwd();
        chdir(dirname($this->indexFile));

        // Create a mock PDO so that $db->prepare(...) doesn't fail when the real DB connection fails
        $mockStatement = $this->createMock(\PDOStatement::class);
        $mockStatement->method('execute')->willReturn(true);
        $mockStatement->method('fetchObject')->willReturn(false);

        $mockPdo = $this->createMock(\PDO::class);
        $mockPdo->method('prepare')->willReturn($mockStatement);

        // We override the DB host so inc/db.php fails, but we then set $db to our mock
        putenv('DB_HOST=invalid');

        // Let's replace the require_once 'inc/db.php'; to set up the mock database connection correctly.
        $code = str_replace(
            "require_once 'inc/db.php';",
            "require_once 'inc/db.php'; global \$db; \$db = \$GLOBALS['mockPdo']; \$tbl_wilayah = 'wilayah';",
            $code
        );

        // Modify cache_file path so it writes to the system temp dir during tests
        $code = str_replace(
            "\$cache_file = __DIR__ . '/cache/provinsi_cache.html';",
            "\$cache_file = sys_get_temp_dir() . '/provinsi_cache_' . md5(uniqid()) . '.html';",
            $code
        );

        $GLOBALS['mockPdo'] = $mockPdo;

        // Execute the code
        eval('?>' . $code);

        // Restore working directory
        chdir($oldCwd);

        $output = ob_get_clean();

        // Restore error_log
        ini_set('error_log', $original);

        // Assert that the CSRF token was generated
        $this->assertArrayHasKey('csrf_token', $_SESSION, 'CSRF token should be set in session');
        $this->assertNotEmpty($_SESSION['csrf_token'], 'CSRF token should not be empty');
        $this->assertEquals(64, strlen($_SESSION['csrf_token']), 'CSRF token should be a 64-character string (32 bytes hex)');

        // Assert that the HTML output contains the meta tag with the CSRF token
        $this->assertStringContainsString(
            '<meta name="csrf-token" content="' . $_SESSION['csrf_token'] . '">',
            $output,
            'Output HTML should contain the CSRF token in a meta tag'
        );
    }
}
