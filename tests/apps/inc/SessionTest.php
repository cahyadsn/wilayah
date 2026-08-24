<?php

use PHPUnit\Framework\TestCase;

class SessionTest extends TestCase
{
    /**
     * @runInSeparateProcess
     */
    public function testSessionStartsAndConfiguresHttp()
    {
        $_SERVER['HTTPS'] = 'off';

        require __DIR__ . '/../../../apps/inc/session.php';

        $this->assertEquals(PHP_SESSION_ACTIVE, session_status());

        $params = session_get_cookie_params();
        $this->assertFalse($params['secure']);
        $this->assertTrue($params['httponly']);
        $this->assertEquals('Strict', $params['samesite']);
    }

    /**
     * @runInSeparateProcess
     */
    public function testSessionStartsAndConfiguresHttps()
    {
        $_SERVER['HTTPS'] = 'on';

        require __DIR__ . '/../../../apps/inc/session.php';

        $this->assertEquals(PHP_SESSION_ACTIVE, session_status());

        $params = session_get_cookie_params();
        $this->assertTrue($params['secure']);
        $this->assertTrue($params['httponly']);
        $this->assertEquals('Strict', $params['samesite']);
    }

    /**
     * @runInSeparateProcess
     */
    public function testSessionDoesNotRestartIfAlreadyActive()
    {
        // Start a session with some custom params
        session_set_cookie_params([
            'secure' => false,
            'httponly' => false,
            'samesite' => 'Lax'
        ]);
        session_start();

        $this->assertEquals(PHP_SESSION_ACTIVE, session_status());

        // This shouldn't do anything because session is active
        require __DIR__ . '/../../../apps/inc/session.php';

        // Assert params haven't changed
        $params = session_get_cookie_params();
        $this->assertFalse($params['secure']);
        $this->assertFalse($params['httponly']);
        $this->assertEquals('Lax', $params['samesite']);
    }
}
