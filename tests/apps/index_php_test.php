<?php
/*
BISMILLAAHIRRAHMAANIRRAHIIM - In the Name of Allah, Most Gracious, Most Merciful
================================================================================
filename  : index_php_test.php
purpose   : PHPUnit tests for apps/index.php
created   : 2026-05-26
author    : Claude
================================================================================*/

declare(strict_types=1);

namespace Tests\apps;

use PHPUnit\Framework\TestCase;

/**
 * Test suite for apps/index.php
 *
 * Tests the main application page functionality including:
 * - Database connection
 * - Session management
 * - Province dropdown rendering
 * - Theme handling
 * - HTML structure and metadata
 */
class IndexPhpTest extends TestCase
{
    private string $testDbFile;
    private array $originalServer;
    private array $originalSession;

    protected function setUp(): void
    {
        // Store original superglobals
        $this->originalServer = $_SERVER;
        $this->originalSession = $_SESSION ?? [];

        // Setup test database
        $this->testDbFile = sys_get_temp_dir() . '/test_wilayah_' . getmypid() . '.sqlite';
    }

    protected function tearDown(): void
    {
        // Restore superglobals
        $_SERVER = $this->originalServer;
        $_SESSION = $this->originalSession;

        // Clean up test database
        if (file_exists($this->testDbFile)) {
            unlink($this->testDbFile);
        }

        // Clear output buffer if test generated output
        if (ob_get_level() > 0) {
            ob_end_clean();
        }
    }

    /**
     * Test that database connection parameters are correctly configured
     */
    public function testDatabaseConnectionParameters(): void
    {
        $dbConfig = $this->extractDbConfig();

        $this->assertArrayHasKey('dbhost', $dbConfig, 'Database host must be defined');
        $this->assertArrayHasKey('dbuser', $dbConfig, 'Database user must be defined');
        $this->assertArrayHasKey('dbname', $dbConfig, 'Database name must be defined');
        $this->assertArrayHasKey('tbl_wilayah', $dbConfig, 'Wilayah table name must be defined');
    }

    /**
     * Test that the application uses the correct database table
     */
    public function testUsesCorrectTableName(): void
    {
        $dbConfig = $this->extractDbConfig();

        $this->assertEquals('wilayah_level_1_2', $dbConfig['tbl_wilayah'],
            'Application should use wilayah_level_1_2 table for main data');
    }

    /**
     * Test that session variables are properly initialized
     */
    public function testSessionInitialization(): void
    {
        // Parse index.php for session initialization
        $content = file_get_contents(__DIR__ . '/../../apps/index.php');

        $this->assertStringContainsString('session_start()', $content,
            'Session must be started at the beginning of the file');

        $this->assertStringContainsString('$_SESSION[\'author\']=\'cahyadsn\'', $content,
            'Session author must be set');

        $this->assertStringContainsString('$_SESSION[\'ver\']', $content,
            'Session version must be set');
    }

    /**
     * Test that theme handling is implemented
     */
    public function testThemeHandling(): void
    {
        $content = file_get_contents(__DIR__ . '/../../apps/index.php');

        // Test session theme retrieval
        $this->assertStringContainsString('$_SESSION[\'c\']', $content,
            'Theme should be retrieved from session');

        // Test GET parameter fallback
        $this->assertStringContainsString('$_GET[\'c\']', $content,
            'Theme should fallback to GET parameter');

        // Test default theme
        $this->assertStringContainsString(':\'indigo\')', $content,
            'Default theme should be indigo');
    }

    /**
     * Test that version number is defined
     */
    public function testVersionIsDefined(): void
    {
        $content = file_get_contents(__DIR__ . '/../../apps/index.php');

        $this->assertStringContainsString('$version=', $content,
            'Version variable must be defined');
    }

    /**
     * Test that HTML structure includes required meta tags
     */
    public function testHtmlMetaTags(): void
    {
        $content = file_get_contents(__DIR__ . '/../../apps/index.php');

        $requiredMeta = [
            'charset="utf-8"' => 'UTF-8 charset',
            'viewport' => 'Viewport meta tag for responsive design',
            'content-language' => 'Content language meta tag',
            'robots' => 'Robots meta tag',
            'keywords' => 'SEO keywords',
            'description' => 'SEO description',
        ];

        foreach ($requiredMeta as $needle => $description) {
            $this->assertStringContainsString($needle, $content,
                "HTML must include {$description}");
        }
    }

    /**
     * Test that Leaflet.js is properly included
     */
    public function testLeafletJsIncluded(): void
    {
        $content = file_get_contents(__DIR__ . '/../../apps/index.php');

        // Test Leaflet CSS
        $this->assertStringContainsString('leaflet.css', $content,
            'Leaflet CSS must be included');

        // Test Leaflet JS
        $this->assertStringContainsString('leaflet.js', $content,
            'Leaflet JS must be included');

        // Test Leaflet Draw
        $this->assertStringContainsString('leaflet.draw', $content,
            'Leaflet Draw plugin must be included');
    }

    /**
     * Test that province dropdown query is correct
     */
    public function testProvinceDropdownQuery(): void
    {
        $content = file_get_contents(__DIR__ . '/../../apps/index.php');

        $this->assertStringContainsString('CHAR_LENGTH(kode)=2', $content,
            'Query must filter for 2-digit province codes');

        $this->assertStringContainsString('ORDER BY nama', $content,
            'Query must order by province name');
    }

    /**
     * Test that AJAX handler script is included
     */
    public function testAjaxHandlerIncluded(): void
    {
        $content = file_get_contents(__DIR__ . '/../../apps/index.php');

        $this->assertStringContainsString('inc/geo_js.php', $content,
            'AJAX handler script must be included');

        $this->assertStringContainsString('js/wilayah.js', $content,
            'Main JavaScript file must be included');
    }

    /**
     * Test that W3CSS framework is included
     */
    public function testW3CssIncluded(): void
    {
        $content = file_get_contents(__DIR__ . '/../../apps/index.php');

        $this->assertStringContainsString('w3.css', $content,
            'W3CSS framework must be included');

        $this->assertStringContainsString('w3-theme-', $content,
            'W3CSS theme must be loaded dynamically');
    }

    /**
     * Test that map container exists
     */
    public function testMapContainerExists(): void
    {
        $content = file_get_contents(__DIR__ . '/../../apps/index.php');

        $this->assertStringContainsString('id="map-canvas"', $content,
            'Map container div must be present');
    }

    /**
     * Test that color theme options are defined
     */
    public function testColorThemeOptions(): void
    {
        $content = file_get_contents(__DIR__ . '/../../apps/index.php');

        $expectedColors = [
            'black', 'brown', 'pink', 'orange', 'amber',
            'lime', 'green', 'teal', 'purple', 'indigo',
            'blue', 'cyan'
        ];

        foreach ($expectedColors as $color) {
            $this->assertStringContainsString("'{$color}'", $content,
                "Theme option '{$color}' must be defined");
        }
    }

    /**
     * Test that proper HTML5 DOCTYPE is used
     */
    public function testHtml5Doctype(): void
    {
        $content = file_get_contents(__DIR__ . '/../../apps/index.php');

        $this->assertStringContainsString('<!DOCTYPE html>', $content,
            'HTML5 DOCTYPE must be used');
    }

    /**
     * Test that required form elements exist
     */
    public function testFormElementsExist(): void
    {
        $content = file_get_contents(__DIR__ . '/../../apps/index.php');

        $requiredElements = [
            'id="prop"' => 'Province dropdown',
            'id="kota"' => 'City dropdown',
            'name="prop"' => 'Province select name',
            'name="kota"' => 'City select name',
            'onchange="ajax(this.value)"' => 'AJAX trigger on change',
        ];

        foreach ($requiredElements as $needle => $description) {
            $this->assertStringContainsString($needle, $content,
                "Form must include {$description}");
        }
    }

    /**
     * Test that AJAX endpoint is defined
     */
    public function testAjaxEndpointDefined(): void
    {
        $content = file_get_contents(__DIR__ . '/../../apps/js/wilayah.js');

        // Note: This checks the JS file that's included by index.php
        $this->assertStringContainsString('geo_ajax.php', $content,
            'AJAX endpoint must be defined in included JavaScript');
    }

    /**
     * Test that Kepmendagri reference is displayed
     */
    public function testKepmendagriReference(): void
    {
        $content = file_get_contents(__DIR__ . '/../../apps/index.php');

        $this->assertStringContainsString('Kepmendagri', $content,
            'Kepmendagri reference must be displayed');
    }

    /**
     * Test that copyright information is present
     */
    public function testCopyrightPresent(): void
    {
        $content = file_get_contents(__DIR__ . '/../../apps/index.php');

        $this->assertStringContainsString('copyright', $content,
            'Copyright information must be present');

        $this->assertStringContainsString('cahya dsn', $content,
            'Author name must be in copyright');
    }

    /**
     * Test database table query pattern
     */
    public function testDatabaseQueryPattern(): void
    {
        $content = file_get_contents(__DIR__ . '/../../apps/index.php');

        // Check for PDO prepared statements
        $this->assertStringContainsString('$db->prepare', $content,
            'Database queries should use PDO prepare');

        $this->assertStringContainsString('$query->execute', $content,
            'Prepared statements must be executed');
    }

    /**
     * Test that preload indicator exists
     */
    public function testPreloadIndicator(): void
    {
        $content = file_get_contents(__DIR__ . '/../../apps/index.php');

        $this->assertStringContainsString('id="preload"', $content,
            'Preload indicator must exist');
    }

    /**
     * Extract database configuration from apps/inc/db.php
     */
    private function extractDbConfig(): array
    {
        $dbFile = __DIR__ . '/../../apps/inc/db.php';

        if (!file_exists($dbFile)) {
            return [];
        }

        $content = file_get_contents($dbFile);

        // Parse simple variable assignments
        preg_match_all('/\$([a-z_]+)=\s*[\'"]([^\'"]*)[\'"]/', $content, $matches);

        $config = [];
        foreach ($matches[1] as $i => $var) {
            $config[$var] = $matches[2][$i];
        }

        return $config;
    }

    /**
     * Test that db.php is included in index.php
     */
    public function testDbConfigIncluded(): void
    {
        $content = file_get_contents(__DIR__ . '/../../apps/index.php');

        $this->assertStringContainsString('include \'inc/db.php\';', $content,
            'Database configuration file must be included');
    }

    /**
     * Test that proper caching headers are set
     */
    public function testCachingHeaders(): void
    {
        $content = file_get_contents(__DIR__ . '/../../apps/index.php');

        $this->assertStringContainsString('header(\'Expires:', $content,
            'Expires header should be set');

        $this->assertStringContainsString('Cache-Control', $content,
            'Cache-Control header should be set');
    }

    /**
     * Test that Google Fonts are included
     */
    public function testGoogleFontsIncluded(): void
    {
        $content = file_get_contents(__DIR__ . '/../../apps/index.php');

        $this->assertStringContainsString('fonts.googleapis.com', $content,
            'Google Fonts should be included');
    }

    /**
     * Test that message box exists for errors/notifications
     */
    public function testMessageBoxExists(): void
    {
        $content = file_get_contents(__DIR__ . '/../../apps/index.php');

        $this->assertStringContainsString('id="msg_box"', $content,
            'Message box container must exist');
    }
}