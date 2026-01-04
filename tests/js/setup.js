/**
 * Jest Setup File
 */

// Add custom matchers
import '@testing-library/jest-dom';

// Mock window.aiwpCopilot global
global.aiwpCopilot = {
  apiUrl: 'http://example.org/wp-json/aiwp/v1',
  nonce: 'test-nonce-123',
  debugMode: false,
  specialist: 'default',
  specialistName: 'General AI'
};

// Mock jQuery
global.$ = global.jQuery = require('jquery');

// Suppress console errors in tests (optional)
global.console = {
  ...console,
  error: jest.fn(),
  warn: jest.fn(),
};
