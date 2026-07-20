<?php

namespace Tests;

use PHPUnit\Framework\TestCase;

class AppsLoginTest extends TestCase
{
    private $loginFile = __DIR__ . '/../apps/login.php';

    /**
     * Helper to run login.php and return output
     */
    private function runLoginScript()
    {
        ob_start();
        $code = file_get_contents($this->loginFile);
        $code = str_replace('header(', '@header(', $code);
        $code = str_replace('exit;', 'echo "EXIT_CALLED"; return;', $code);
        eval('?>' . $code);
        return ob_get_clean();
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testLogoutDestroySessionAndRedirect()
    {
        $_GET['logout'] = '1';
        $_SESSION['author'] = 'cahyadsn';

        $output = $this->runLoginScript();

        // Session should be empty/destroyed
        $this->assertEmpty($_SESSION);
        // It should call exit
        $this->assertStringContainsString('EXIT_CALLED', $output);
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testLoginSuccessSetsSessionAndRedirects()
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST['username'] = 'admin';
        $_POST['password'] = 'secret';

        putenv('ADMIN_USER=admin');
        putenv('ADMIN_PASS=secret');

        $output = $this->runLoginScript();

        // Session should have author set
        $this->assertArrayHasKey('author', $_SESSION);
        $this->assertEquals('cahyadsn', $_SESSION['author']);
        // It should call exit
        $this->assertStringContainsString('EXIT_CALLED', $output);
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testLoginFailureWithWrongCredentials()
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST['username'] = 'admin';
        $_POST['password'] = 'wrong_secret';

        putenv('ADMIN_USER=admin');
        putenv('ADMIN_PASS=secret');

        $output = $this->runLoginScript();

        // Session should not have author
        $this->assertArrayNotHasKey('author', $_SESSION ?? []);
        // Should not exit
        $this->assertStringNotContainsString('EXIT_CALLED', $output);
        // Should show error message
        $this->assertStringContainsString('Invalid credentials', $output);
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testLoginFailureWithMissingEnvVars()
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST['username'] = 'admin';
        $_POST['password'] = 'secret';

        // Ensure env vars are not set
        putenv('ADMIN_USER');
        putenv('ADMIN_PASS');

        $output = $this->runLoginScript();

        $this->assertArrayNotHasKey('author', $_SESSION ?? []);
        $this->assertStringNotContainsString('EXIT_CALLED', $output);
        $this->assertStringContainsString('Invalid credentials', $output);
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testGetRequestShowsLoginForm()
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';

        $output = $this->runLoginScript();

        $this->assertStringContainsString('<form method="POST"', $output);
        $this->assertStringContainsString('Admin Login', $output);
        $this->assertStringNotContainsString('Invalid credentials', $output);
    }
}
