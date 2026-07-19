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

        // Ensure session array exists to catch the set
        if (session_status() === PHP_SESSION_NONE) {
            $_SESSION = [];
        }

        ob_start();
        include $this->targetFile;
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

        if (session_status() === PHP_SESSION_NONE) {
            $_SESSION = [];
        }

        ob_start();
        include $this->targetFile;
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

        if (session_status() === PHP_SESSION_NONE) {
            $_SESSION = [];
        }

        ob_start();
        include $this->targetFile;
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

        if (session_status() === PHP_SESSION_NONE) {
            $_SESSION = [];
        }

        ob_start();
        include $this->targetFile;
        ob_get_clean();

        $this->assertArrayHasKey('theme', $_SESSION, 'Session variable "theme" should be set.');
        $this->assertEquals('dark', $_SESSION['theme'], 'Session variable "theme" should default to dark for invalid values.');
    }
}
