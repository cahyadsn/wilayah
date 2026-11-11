<?php
/*
BISMILLAAHIRRAHMAANIRRAHIIM - In the Name of Allah, Most Gracious, Most Merciful
================================================================================
filename : IndexDeepLinkTest.php
purpose  : Tests for Deep Linking, shareable URL metadata, and parameter validation (P2)
create   : 2026-11-11
last edit: 2026-11-11 08:35:00
author   : cahya dsn
================================================================================
MIT License
copyright (c) 2017-2026 by cahya dsn; cahyadsn@gmail.com
================================================================================*/

namespace Tests\Apps;

use PHPUnit\Framework\TestCase;

class IndexDeepLinkTest extends TestCase
{
    private $indexFile;

    protected function setUp(): void
    {
        $this->indexFile = dirname(__DIR__, 2) . '/apps/index.php';
        $_SESSION = [];
        $_GET = [];
    }

    protected function tearDown(): void
    {
        $_SESSION = [];
        $_GET = [];
    }

    private function renderIndexWithParams(array $getParams = []): string
    {
        $_GET = $getParams;
        $original = ini_get('error_log');
        ini_set('error_log', strtoupper(substr(PHP_OS, 0, 3)) === 'WIN' ? 'NUL' : '/dev/null');

        ob_start();
        $code = file_get_contents($this->indexFile);
        $code = str_replace('header(', '@header(', $code);
        $appsDir = str_replace('\\', '/', dirname(__DIR__, 2) . '/apps');
        $code = str_replace(
            "require_once __DIR__ . '/inc/session.php';",
            'require_once "' . $appsDir . '/inc/session.php"; if (session_status() === PHP_SESSION_NONE) { session_start(); }',
            $code
        );

        $oldCwd = getcwd();
        chdir(dirname($this->indexFile));

        $mockStatement = $this->createMock(\PDOStatement::class);
        $mockStatement->method('execute')->willReturn(true);
        $mockStatement->method('fetchObject')->willReturn(false);

        $mockPdo = $this->createMock(\PDO::class);
        $mockPdo->method('prepare')->willReturn($mockStatement);

        $code = str_replace(
            "require_once 'inc/db.php';",
            "global \$db; \$db = \$GLOBALS['mockPdo']; \$tbl_wilayah = 'wilayah';",
            $code
        );

        $code = str_replace('define("_AUTHOR"', 'if (!defined("_AUTHOR")) define("_AUTHOR"', $code);

        $code = str_replace(
            "__DIR__ . '/cache/provinsi_cache.html'",
            "sys_get_temp_dir() . '/provinsi_cache_' . hash('sha256', uniqid()) . '.html'",
            $code
        );

        $GLOBALS['mockPdo'] = $mockPdo;

        eval('?>' . $code);

        chdir($oldCwd);
        $output = ob_get_clean();
        ini_set('error_log', $original);

        return $output;
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testValidDeepLinkParametersRenderMetaTagsAndButtons()
    {
        $output = $this->renderIndexWithParams([
            'kode'  => '32.73',
            'pulau' => '11.01.40001',
            'theme' => 'dark'
        ]);

        $this->assertStringContainsString('<meta name="geo-kode" content="32.73">', $output);
        $this->assertStringContainsString('<meta name="geo-pulau" content="11.01.40001">', $output);
        $this->assertStringContainsString('id="btnShareLink"', $output);
        $this->assertStringContainsString('id="btnCopyLocation"', $output);
        $this->assertStringContainsString('id="toastNotification"', $output);
        $this->assertStringContainsString('class="theme-dark"', $output);
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testMaliciousQueryParametersAreSanitizedAndBlocked()
    {
        $output = $this->renderIndexWithParams([
            'kode'  => '<script>alert(1)</script>',
            'pulau' => 'javascript:alert(1)',
            'theme' => '"><script>alert("xss")</script>'
        ]);

        $this->assertStringNotContainsString('<script>alert(1)</script>', $output);
        $this->assertStringNotContainsString('javascript:alert(1)', $output);
        $this->assertStringNotContainsString('<meta name="geo-kode"', $output);
        $this->assertStringNotContainsString('<meta name="geo-pulau"', $output);
        // Malicious theme should fall back to valid theme class ('theme-light' or 'theme-dark')
        $this->assertMatchesRegularExpression('/class="theme-(light|dark)"/', $output);
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testDifferentHierarchyLevelCodesAllowed()
    {
        $levels = ['11', '32.73', '32.73.01', '32.73.01.1001'];

        foreach ($levels as $code) {
            $output = $this->renderIndexWithParams(['kode' => $code]);
            $this->assertStringContainsString('<meta name="geo-kode" content="' . $code . '">', $output);
        }
    }
}
