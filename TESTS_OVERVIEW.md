# 🧪 AIWP Copilot - Testing Overview

## 📊 Quick Stats

```
Total Tests Written:     44
PHP Tests:              29
JavaScript Tests:       15
Current Coverage:      ~30%
Target Coverage:        90%
Time to Full Coverage:  10 weeks
```

---

## 🎯 Test Categories

### ✅ Unit Tests (PHP)
```
tests/php/unit/
├── test-error-handler.php          (17 tests) ✅ 100% coverage
├── core/
│   └── test-copilot-indexer.php    (0 tests)  ⏳ TODO
├── providers/
│   ├── test-provider-registry.php  (0 tests)  ⏳ TODO
│   └── test-openai-provider.php    (0 tests)  ⏳ TODO
└── specialists/
    ├── test-specialist-registry.php (0 tests) ⏳ TODO
    └── test-specialist-engine.php   (0 tests) ⏳ TODO
```

### ✅ Integration Tests (PHP)
```
tests/php/integration/
└── rest-api/
    └── test-rest-endpoints.php     (12 tests) ✅ ~75% coverage
```

### ✅ Unit Tests (JavaScript)
```
tests/js/unit/
├── copilot.test.js                 (15 tests) ✅ ~85% coverage
└── elementor-scanner.test.js       (0 tests)  ⏳ TODO
```

---

## 🧩 Component Coverage Map

### Backend PHP (1,200 lines)

| Component | Lines | Tests | Coverage | Priority |
|-----------|-------|-------|----------|----------|
| Error Handler | 113 | 17 ✅ | 100% | Critical |
| Provider System | 270 | 0 ⏳ | 0% | Critical |
| Specialist System | 425 | 0 ⏳ | 0% | High |
| REST API | 193 | 12 ✅ | 75% | Critical |
| Core Indexer | 72 | 0 ⏳ | 0% | Medium |
| Main Plugin | 188 | 0 ⏳ | 0% | High |

### Frontend JavaScript (500 lines)

| Component | Lines | Tests | Coverage | Priority |
|-----------|-------|-------|----------|----------|
| Widget | 244 | 15 ✅ | 85% | High |
| Elementor Scanner | ~100 | 0 ⏳ | 0% | Medium |

---

## 🔬 Test Details

### Error Handler Tests (17/17 ✅)

**Rate Limiting (5 tests)**
```
✅ test_rate_limit_allows_first_request
✅ test_rate_limit_increments_counter
✅ test_rate_limit_blocks_at_limit
✅ test_rate_limit_blocks_above_limit
✅ test_rate_limit_per_user
```

**Error Messages (7 tests)**
```
✅ test_get_user_message_invalid_api_key
✅ test_get_user_message_rate_limit
✅ test_get_user_message_rate_limit_exceeded
✅ test_get_user_message_timeout
✅ test_get_user_message_connection_error
✅ test_get_user_message_unknown_error
✅ test_get_user_message_non_wp_error
```

**Logging & Formatting (5 tests)**
```
✅ test_log_error_when_debug_enabled
✅ test_log_error_when_debug_disabled
✅ test_format_api_error_structure
✅ test_format_api_error_with_wp_error
✅ test_format_api_error_with_non_wp_error
```

---

### REST API Tests (12/12 ✅)

**Permissions (2 tests)**
```
✅ test_check_permissions_with_edit_posts_capability
✅ test_check_permissions_without_edit_posts_capability
```

**Complete Endpoint (4 tests)**
```
✅ test_handle_complete_without_messages
✅ test_handle_complete_rate_limit_exceeded
✅ test_handle_complete_with_valid_messages
✅ test_handle_complete_with_no_provider
```

**Context Endpoint (3 tests)**
```
✅ test_handle_get_context_with_valid_post
✅ test_handle_get_context_with_invalid_post
✅ test_handle_get_context_includes_elementor
```

**Other Endpoints (3 tests)**
```
✅ test_handle_validate_with_valid_credentials
✅ test_handle_validate_with_invalid_credentials
✅ test_handle_get_specialists_returns_all
```

---

### JavaScript Widget Tests (15/15 ✅)

**Widget Creation (3 tests)**
```
✅ init creates widget in DOM
✅ widget has correct structure
✅ header contains AI Copilot title
```

**Message Sending (6 tests)**
```
✅ sendMessage validates input not empty
✅ sendMessage adds user message to UI
✅ sendMessage clears input after sending
✅ sendMessage makes API call
✅ sendMessage handles API success
✅ sendMessage handles API error
```

**Message Display (3 tests)**
```
✅ addMessage with user role
✅ addMessage with assistant role
✅ showError displays error message
```

**Event Binding (1 test)**
```
✅ send button click triggers sendMessage
```

---

## 📋 Next Tests to Write

### Phase 2: Critical Components (Priority 1)

**1. Provider System (16 tests needed)**

`test-provider-registry.php`:
```
⏳ test_init_registers_openai_provider
⏳ test_register_adds_provider
⏳ test_register_prevents_duplicates
⏳ test_get_active_returns_provider
⏳ test_get_active_with_no_provider
⏳ test_get_all_returns_all_providers
⏳ test_switching_between_providers
```

`test-openai-provider.php`:
```
⏳ test_constructor_loads_settings
⏳ test_complete_with_valid_api_key
⏳ test_complete_with_empty_api_key
⏳ test_complete_with_invalid_api_key
⏳ test_complete_handles_timeout
⏳ test_complete_handles_rate_limit
⏳ test_parse_error_identifies_types
⏳ test_validate_credentials_valid
⏳ test_validate_credentials_invalid
```

**2. Specialist System (20 tests needed)**

`test-specialist-registry.php`:
```
⏳ test_init_registers_all_specialists
⏳ test_register_adds_specialist
⏳ test_get_returns_specialist
⏳ test_get_all_returns_all
⏳ test_each_specialist_has_required_fields
⏳ test_specialist_tiers
... (14 more)
```

**3. Core Indexer (9 tests needed)**

`test-copilot-indexer.php`:
```
⏳ test_get_page_context_with_valid_post
⏳ test_get_page_context_with_invalid_post
⏳ test_get_elementor_structure_with_elementor
⏳ test_get_elementor_structure_without_elementor
⏳ test_get_site_structure
... (4 more)
```

---

## 🚀 Running Tests

### Quick Commands

```bash
# Install dependencies (one-time)
composer install && npm install

# Run all tests
composer test && npm test

# Run with coverage
composer test:coverage && npm test -- --coverage

# Run specific test file
vendor/bin/phpunit tests/php/unit/test-error-handler.php
npm test -- copilot.test.js

# Watch mode (auto-rerun on changes)
npm test -- --watch
```

---

## 📈 Progress Tracking

### Week 1-2 (Current) ✅
- [x] Setup testing infrastructure
- [x] Create phpunit.xml, jest.config.js
- [x] Write 17 Error Handler tests (100% coverage)
- [x] Write 12 REST API tests (75% coverage)
- [x] Write 15 JavaScript Widget tests (85% coverage)
- [x] Setup GitHub Actions CI/CD
- [x] Write comprehensive documentation

### Week 3-4 (Next) ⏳
- [ ] Write 16 Provider System tests (95% coverage)
- [ ] Write 9 Core Indexer tests (100% coverage)
- [ ] Reach 50% overall coverage

### Week 5-6 ⏳
- [ ] Write 20 Specialist System tests (95% coverage)
- [ ] Write Main Plugin tests (85% coverage)
- [ ] Reach 70% overall coverage

### Week 7-8 ⏳
- [ ] Write 15 Security tests
- [ ] Write Integration tests
- [ ] Reach 85% overall coverage

### Week 9-10 ⏳
- [ ] Setup Playwright E2E tests
- [ ] Performance tests
- [ ] Reach 90% overall coverage goal

---

## 🎓 Learning Resources

### PHPUnit (PHP Testing)
- Official Docs: https://phpunit.de/documentation.html
- Brain Monkey: https://brain-wp.github.io/BrainMonkey/
- WordPress Testing: https://make.wordpress.org/core/handbook/testing/

### Jest (JavaScript Testing)
- Official Docs: https://jestjs.io/
- Testing Library: https://testing-library.com/
- Best Practices: https://kentcdodds.com/blog/common-mistakes-with-react-testing-library

---

## 🔧 Debugging Tips

### PHP Test Failures

```bash
# Verbose output
vendor/bin/phpunit --verbose

# Debug specific test
vendor/bin/phpunit --debug --filter test_rate_limit_allows_first_request

# See all assertions
vendor/bin/phpunit --testdox
```

### JavaScript Test Failures

```bash
# Verbose output
npm test -- --verbose

# Debug specific test
npm test -- --testNamePattern="sendMessage"

# Use Chrome DevTools
node --inspect-brk node_modules/.bin/jest --runInBand
```

---

## 📊 Coverage Reports

### Viewing Coverage

**PHP Coverage:**
```bash
composer test:coverage
# Opens: coverage/php/index.html
```

**JavaScript Coverage:**
```bash
npm test -- --coverage
# Opens: coverage/js/lcov-report/index.html
```

### Coverage Badges (for README)

```markdown
![PHP Coverage](https://img.shields.io/badge/PHP%20Coverage-30%25-yellow)
![JS Coverage](https://img.shields.io/badge/JS%20Coverage-85%25-green)
![Overall Coverage](https://img.shields.io/badge/Coverage-30%25-yellow)
```

---

## 🎯 Quality Gates

Before merging to main:

- ✅ All tests pass (44/44)
- ✅ PHPStan level 5 passes
- ✅ PHPCS WordPress standards pass
- ⏳ Coverage doesn't decrease
- ⏳ No new security vulnerabilities

---

## 📞 Get Help

**Documentation:**
- `TESTING_STRATEGY.md` - Full testing plan (400+ lines)
- `TESTING_QUICKSTART.md` - Quick start guide
- `TESTING_SUMMARY.md` - What we've built

**Issues:**
- Check test output for error details
- Run with `--debug` flag
- Review coverage reports
- Check Brain Monkey docs for WordPress mocking

---

## 🏆 Success Criteria

### Definition of Done

- [x] Testing infrastructure setup
- [x] 40+ tests written
- [ ] 90%+ code coverage
- [ ] All critical components tested
- [x] CI/CD pipeline operational
- [ ] Security tests passing
- [x] Documentation complete

---

*Last Updated: 2026-01-03*
*Progress: 44/120 tests (37%)*
*Coverage: 30% (Target: 90%)*
