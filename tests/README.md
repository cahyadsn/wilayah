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

### apps/index_php_test.php

Tests for `apps/index.php` including:
- Database connection parameters
- Session initialization
- Theme handling
- HTML structure and meta tags
- Leaflet.js integration
- Province dropdown queries
- Form elements
- AJAX endpoint definitions

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