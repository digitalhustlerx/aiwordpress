# Testing Quick Start Guide

Get your AIWP Copilot tests up and running in 5 minutes.

---

## Prerequisites

- PHP 7.4 or higher
- Node.js 18 or higher
- Composer installed
- npm installed

---

## Setup (One-time)

### 1. Install PHP Dependencies

```bash
composer install
```

This installs:
- PHPUnit (testing framework)
- Brain Monkey (WordPress function mocking)
- PHPStan (static analysis)
- PHPCS (code standards)

### 2. Install JavaScript Dependencies

```bash
npm install
```

This installs:
- Jest (testing framework)
- Testing Library (DOM testing utilities)
- Babel (JavaScript transpiler)

---

## Running Tests

### PHP Tests

```bash
# Run all PHP tests
composer test

# Run only unit tests
composer test:unit

# Run only integration tests
composer test:integration

# Run only security tests
composer test:security

# Run with coverage report
composer test:coverage
# Then open: coverage/php/index.html
```

### JavaScript Tests

```bash
# Run all JS tests
npm test

# Run with coverage
npm test -- --coverage
# Then open: coverage/js/lcov-report/index.html

# Run in watch mode (auto-rerun on file changes)
npm test -- --watch

# Run specific test file
npm test -- copilot.test.js
```

---

## Code Quality Checks

### PHP Code Standards

```bash
# Check code standards
composer phpcs

# Auto-fix code standards
composer phpcbf
```

### Static Analysis

```bash
# Run PHPStan
composer phpstan
```

---

## Current Test Status

### PHP Tests (17/17 passing)

✅ **Error Handler Tests** - 17 test cases
- Rate limiting functionality
- Error message formatting
- Debug logging
- API error formatting

Run: `vendor/bin/phpunit tests/php/unit/test-error-handler.php`

### JavaScript Tests (15/15 passing)

✅ **Widget Tests** - 15 test cases
- Widget creation and structure
- Message sending and display
- Event handling
- API communication

Run: `npm test -- copilot.test.js`

---

## Writing Your First Test

### PHP Test Example

Create: `tests/php/unit/test-my-feature.php`

```php
<?php
use PHPUnit\Framework\TestCase;
use Brain\Monkey;
use Brain\Monkey\Functions;

class Test_My_Feature extends TestCase {

    protected function setUp(): void {
        parent::setUp();
        Monkey\setUp();
    }

    protected function tearDown(): void {
        Monkey\tearDown();
        parent::tearDown();
    }

    public function test_my_function_works() {
        // Mock WordPress function
        Functions\when('get_option')->justReturn('test_value');

        // Your test code
        $result = my_function();

        // Assertion
        $this->assertEquals('expected', $result);
    }
}
```

### JavaScript Test Example

Create: `tests/js/unit/my-feature.test.js`

```javascript
describe('My Feature', () => {
  test('should work correctly', () => {
    // Setup
    const input = 'test';

    // Execute
    const result = myFunction(input);

    // Assert
    expect(result).toBe('expected');
  });
});
```

---

## Debugging Tests

### PHP Tests Debug

```bash
# Run with verbose output
vendor/bin/phpunit --verbose

# Run with debug output
vendor/bin/phpunit --debug

# Run specific test method
vendor/bin/phpunit --filter test_rate_limit_allows_first_request
```

### JavaScript Tests Debug

```bash
# Run with verbose output
npm test -- --verbose

# Run specific test
npm test -- --testNamePattern="should work correctly"

# Run in debug mode (use Chrome DevTools)
node --inspect-brk node_modules/.bin/jest --runInBand
```

---

## Common Issues & Solutions

### Issue: "Class not found"
**Solution**: Make sure you're requiring the file in your test:
```php
require_once AIWP_PLUGIN_DIR . 'includes/class-error-handler.php';
```

### Issue: "Module not found" (Jest)
**Solution**: Check your `jest.config.js` moduleNameMapper settings.

### Issue: WordPress functions not mocked
**Solution**: Use Brain Monkey:
```php
Functions\when('wp_remote_post')->justReturn($mock_response);
```

### Issue: "Cannot find module 'jquery'"
**Solution**: Install jQuery for tests:
```bash
npm install --save-dev jquery
```

---

## Test Coverage Goals

| Component | Current | Target |
|-----------|---------|--------|
| Error Handler | 100% ✅ | 100% |
| Provider System | 0% | 95% |
| Specialist System | 0% | 95% |
| REST API | 0% | 90% |
| JavaScript Widget | 85% ✅ | 90% |
| **Overall** | **15%** | **90%** |

---

## Next Steps

1. **Run existing tests** to make sure everything works
2. **Review** `TESTING_STRATEGY.md` for comprehensive test plan
3. **Write tests** for Provider System (high priority)
4. **Write tests** for Specialist System (high priority)
5. **Write tests** for REST API endpoints (critical)
6. **Setup CI/CD** using `.github/workflows/tests.yml`

---

## Useful Commands Cheat Sheet

```bash
# PHP
composer test              # Run all PHP tests
composer test:coverage     # Generate coverage report
composer phpstan          # Static analysis
composer phpcs            # Code standards check

# JavaScript
npm test                  # Run all JS tests
npm test -- --coverage    # Generate coverage report
npm test -- --watch       # Watch mode

# Both
composer test && npm test # Run all tests (PHP + JS)
```

---

## Getting Help

- **PHPUnit Docs**: https://phpunit.de/documentation.html
- **Brain Monkey Docs**: https://brain-wp.github.io/BrainMonkey/
- **Jest Docs**: https://jestjs.io/docs/getting-started
- **Testing Library**: https://testing-library.com/docs/

---

## CI/CD Integration

Tests run automatically on:
- Every push to `main` or `develop`
- Every pull request
- Matrix testing across PHP 7.4-8.2 and WordPress 6.0-6.4

See: `.github/workflows/tests.yml`

---

*Last Updated: 2026-01-03*
