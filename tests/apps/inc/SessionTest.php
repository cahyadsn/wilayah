<?php

use PHPUnit\Framework\TestCase;

class SessionTest extends TestCase
{
    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
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
     * @preserveGlobalState disabled
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
     * @preserveGlobalState disabled
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

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testIsPhp2shellRequestReturnsFalseForNormalRequest()
    {
        $_FILES = [];
        $_POST = ['color' => 'blue'];
        $_GET = ['id' => '11'];
        $_COOKIE = [];
        $_REQUEST = array_merge($_GET, $_POST);
        unset($_SERVER['CONTENT_TYPE']);
        unset($_SERVER['HTTP_CONTENT_TYPE']);

        require __DIR__ . '/../../../apps/inc/session.php';

        $this->assertTrue(function_exists('is_php2shell_request'));
        $this->assertFalse(is_php2shell_request());
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testIsPhp2shellRequestDetectsSessionUploadProgressPost()
    {
        require __DIR__ . '/../../../apps/inc/session.php';

        $_POST['PHP_SESSION_UPLOAD_PROGRESS'] = 'exploit_probe';
        $this->assertTrue(is_php2shell_request());
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testIsPhp2shellRequestDetectsSessionUploadProgressGet()
    {
        require __DIR__ . '/../../../apps/inc/session.php';

        $_GET['PHP_SESSION_UPLOAD_PROGRESS'] = 'exploit_probe';
        $this->assertTrue(is_php2shell_request());
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testIsPhp2shellRequestDetectsSessionUploadProgressCookie()
    {
        require __DIR__ . '/../../../apps/inc/session.php';

        $_COOKIE['upload_progress_test'] = 'payload';
        $this->assertTrue(is_php2shell_request());
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testIsPhp2shellRequestDetectsMultipartContentType()
    {
        require __DIR__ . '/../../../apps/inc/session.php';

        $_SERVER['CONTENT_TYPE'] = 'multipart/form-data; boundary=---------------------------12345';
        $this->assertTrue(is_php2shell_request());
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testIsPhp2shellRequestDetectsFiles()
    {
        require __DIR__ . '/../../../apps/inc/session.php';

        $_FILES['upload'] = [
            'name' => 'test.php',
            'type' => 'text/plain',
            'tmp_name' => '/tmp/php123',
            'error' => 0,
            'size' => 10
        ];
        $this->assertTrue(is_php2shell_request());
    }
}
