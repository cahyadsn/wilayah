<?php

namespace Tests\inc;

use PHPUnit\Framework\TestCase;

class ChangeColorTest extends TestCase
{
    private $targetFile = __DIR__ . '/../../apps/inc/change.color.php';

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testColorIsSetInSession()
    {
        // Simulate a POST request setting 'color'
        $_POST['color'] = 'blue';
        $csrfToken = 'valid_token';
        $_POST['csrf_token'] = $csrfToken;

        // Ensure session array exists to catch the set
        if (session_status() === PHP_SESSION_NONE) {
            $_SESSION = [];
        }
        $_SESSION['csrf_token'] = $csrfToken;

        ob_start();
        $code = file_get_contents($this->targetFile);
        $code = str_replace("require_once __DIR__ . '/session.php';", '', $code);
        eval('?>' . $code);
        ob_get_clean();

        $this->assertArrayHasKey('c', $_SESSION, 'Session variable "c" should be set.');
        $this->assertEquals('blue', $_SESSION['c'], 'Session variable "c" should match the POST "color".');
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testThemeIsSetToLight()
    {
        $_POST = [];
        $_POST['theme'] = 'light';
        $csrfToken = 'valid_token';
        $_POST['csrf_token'] = $csrfToken;

        if (session_status() === PHP_SESSION_NONE) {
            $_SESSION = [];
        }
        $_SESSION['csrf_token'] = $csrfToken;

        ob_start();
        $code = file_get_contents($this->targetFile);
        $code = str_replace("require_once __DIR__ . '/session.php';", '', $code);
        eval('?>' . $code);
        ob_get_clean();

        $this->assertArrayHasKey('theme', $_SESSION, 'Session variable "theme" should be set.');
        $this->assertEquals('light', $_SESSION['theme'], 'Session variable "theme" should be light.');
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testThemeIsSetToDark()
    {
        $_POST = [];
        $_POST['theme'] = 'dark';
        $csrfToken = 'valid_token';
        $_POST['csrf_token'] = $csrfToken;

        if (session_status() === PHP_SESSION_NONE) {
            $_SESSION = [];
        }
        $_SESSION['csrf_token'] = $csrfToken;

        ob_start();
        $code = file_get_contents($this->targetFile);
        $code = str_replace("require_once __DIR__ . '/session.php';", '', $code);
        eval('?>' . $code);
        ob_get_clean();

        $this->assertArrayHasKey('theme', $_SESSION, 'Session variable "theme" should be set.');
        $this->assertEquals('dark', $_SESSION['theme'], 'Session variable "theme" should be dark.');
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testThemeIsSetToDarkForInvalidValues()
    {
        $_POST = [];
        $_POST['theme'] = 'blue';
        $csrfToken = 'valid_token';
        $_POST['csrf_token'] = $csrfToken;

        if (session_status() === PHP_SESSION_NONE) {
            $_SESSION = [];
        }
        $_SESSION['csrf_token'] = $csrfToken;

        ob_start();
        $code = file_get_contents($this->targetFile);
        $code = str_replace("require_once __DIR__ . '/session.php';", '', $code);
        eval('?>' . $code);
        ob_get_clean();

        $this->assertArrayHasKey('theme', $_SESSION, 'Session variable "theme" should be set.');
        $this->assertEquals('dark', $_SESSION['theme'], 'Session variable "theme" should default to dark for invalid values.');
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testFailsWithInvalidCsrfToken()
    {
        $_POST = [];
        $_POST['theme'] = 'light';
        $_POST['csrf_token'] = 'invalid_token';

        if (session_status() === PHP_SESSION_NONE) {
            $_SESSION = [];
        }
        $_SESSION['csrf_token'] = 'valid_token';

        ob_start();
        $code = file_get_contents($this->targetFile);
        $code = str_replace("require_once __DIR__ . '/session.php';", '', $code);
        $code = str_replace('die(\'CSRF token validation failed\');', 'echo "CSRF token validation failed"; return;', $code);
        eval('?>' . $code);
        $output = ob_get_clean();

        $this->assertStringContainsString('CSRF token validation failed', $output);
        $this->assertArrayNotHasKey('theme', $_SESSION, 'Session variable "theme" should not be set with invalid CSRF token.');
        $this->assertEquals(403, http_response_code());
    }
}
