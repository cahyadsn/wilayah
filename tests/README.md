# Test Suite

This directory contains PHPUnit tests for the Wilayah application.

## Installation

Install PHPUnit and dependencies:

```bash
composer install --dev
```

Or install PHPUnit globally:

```bash
composer global require phpunit/phpunit
```

## Running Tests

Run all tests:

```bash
vendor/bin/phpunit
```

Run specific test suite:

```bash
vendor/bin/phpunit tests/apps
```

Run with coverage report:

```bash
vendor/bin/phpunit --coverage-html coverage
```

## Test Structure

- `tests/apps/` - Tests for the main application
- `tests/src/` - Tests for the src application
- `tests/inc/` - Tests for includes and AJAX handlers
- `tests/db/` - Database-related tests

## Current Tests

### Main Application Tests
- **[tests/apps/index_php_test.php](file:///D:/laragon/repo/wilayah/tests/apps/index_php_test.php)**: Validates theme handling, session initialization, meta tags, Leaflet integration, caching headers, and database connectivity.
- **[tests/IndexGetParameterTest.php](file:///D:/laragon/repo/wilayah/tests/IndexGetParameterTest.php)**: Tests query string parameters on the index page, ensuring invalid inputs are handled gracefully and valid ones fetch correctly using mocks.

### Includes & AJAX Handler Tests
- **[tests/apps/inc/GeoAjaxTest.php](file:///D:/laragon/repo/wilayah/tests/apps/inc/GeoAjaxTest.php)**: Tests AJAX responses, coordinates validation (`isPathReasonable`), and boundaries generation (`fallbackBox`).
- **[tests/apps/inc/ReverseLookupTest.php](file:///D:/laragon/repo/wilayah/tests/apps/inc/ReverseLookupTest.php)**: Verifies the reverse lookup handler's error handling catch blocks and JSON error reporting.

### Core Helpers & Utility Tests
- **[tests/inc/ChangeColorTest.php](file:///D:/laragon/repo/wilayah/tests/inc/ChangeColorTest.php)**: Tests session-based theme color modification handler.
- **[tests/inc/GeoHelpersTest.php](file:///D:/laragon/repo/wilayah/tests/inc/GeoHelpersTest.php)**: Validates fallback bounding boxes for region coordinates.
- **[tests/inc/GeoUtilsTest.php](file:///D:/laragon/repo/wilayah/tests/inc/GeoUtilsTest.php)**: Tests geographic vector computations and path-to-centroid distance thresholds.
- **[tests/inc/ReverseLookupTest.php](file:///D:/laragon/repo/wilayah/tests/inc/ReverseLookupTest.php)**: Tests polygon intersection (`pointInPath`/`pointInRing`) and coordinate mapping.

## Writing New Tests

When adding a new test file:

1. Place it in the appropriate subdirectory
2. Name the file with `_test.php` or `Test.php` suffix
3. Extend `PHPUnit\Framework\TestCase`
4. Implement `setUp()` and `tearDown()` for test lifecycle
5. Use descriptive test method names that start with `test`

Example:

```php
<?php

namespace Tests\apps;

use PHPUnit\Framework\TestCase;

class MyFeatureTest extends TestCase
{
    protected function setUp(): void
    {
        // Setup before each test
    }

    protected function tearDown(): void
    {
        // Cleanup after each test
    }

    public function testSomethingShouldWork(): void
    {
        $this->assertTrue(true);
    }
}
```

## Notes

- Tests are designed to work without a real database connection where possible
- For integration tests requiring database, ensure test database is configured
- Some tests parse PHP files directly to validate structure without execution