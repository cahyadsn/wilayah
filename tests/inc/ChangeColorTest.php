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

}
