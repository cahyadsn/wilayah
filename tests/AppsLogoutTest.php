<?php

namespace Tests;

use PHPUnit\Framework\TestCase;

function mock_header($string) {
    $GLOBALS['mockHeaders'][] = $string;
}

function mock_setcookie($name, $value = "", $expires_or_options = 0, $path = "", $domain = "", $secure = false, $httponly = false) {
    $GLOBALS['mockCookies'][] = compact('name', 'value', 'expires_or_options', 'path', 'domain', 'secure', 'httponly');
}

class AppsLogoutTest extends TestCase
{
    private $logoutFile = __DIR__ . '/../apps/logout.php';

    protected function setUp(): void
    {
        parent::setUp();
        $GLOBALS['mockHeaders'] = [];
        $GLOBALS['mockCookies'] = [];
    }

    /**
     * Helper to run logout.php and return output
     */
    private function runLogoutScript()
    {
        ob_start();
        $code = file_get_contents($this->logoutFile);

        // Mock require_once to just ensure session is started
        $code = str_replace("require_once __DIR__ . '/inc/session.php';", 'if (session_status() === PHP_SESSION_NONE) { session_start(); }', $code);

        // Mock header()
        $code = str_replace('header(', '\\Tests\\mock_header(', $code);

        // Mock setcookie()
        $code = str_replace('setcookie(', '\\Tests\\mock_setcookie(', $code);

        // Mock exit
        $code = str_replace('exit;', 'echo "EXIT_CALLED"; return;', $code);

        // phpcs:ignore Squiz.PHP.Eval.Discouraged
        eval('?>' . $code);
        return ob_get_clean();
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testLogoutDestroysSessionAndRedirects()
    {
        // Override session setting safely
        ini_set('session.use_cookies', '1');

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $_SESSION['author'] = 'cahyadsn';
        $_SESSION['foo'] = 'bar';

        $output = $this->runLogoutScript();

        // Session should be empty
        $this->assertEmpty($_SESSION);

        // It should redirect to index.php
        $this->assertContains('Location: index.php', $GLOBALS['mockHeaders']);

        // It should clear the session cookie
        $this->assertNotEmpty($GLOBALS['mockCookies']);
        $cookie = $GLOBALS['mockCookies'][0];
        $this->assertEquals(session_name(), $cookie['name']);
        $this->assertEquals('', $cookie['value']);

        // It should call exit
        $this->assertStringContainsString('EXIT_CALLED', $output);
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testLogoutWithoutCookies()
    {
        // Override session setting safely
        ini_set('session.use_cookies', '0');

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $_SESSION['author'] = 'cahyadsn';
        $_SESSION['foo'] = 'bar';

        $output = $this->runLogoutScript();

        // Session should be empty
        $this->assertEmpty($_SESSION);

        // It should redirect to index.php
        $this->assertContains('Location: index.php', $GLOBALS['mockHeaders']);

        // It should not clear the session cookie
        $this->assertEmpty($GLOBALS['mockCookies']);

        // It should call exit
        $this->assertStringContainsString('EXIT_CALLED', $output);
    }
}
