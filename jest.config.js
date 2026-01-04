module.exports = {
  // Test environment
  testEnvironment: 'jsdom',

  // Test match patterns
  testMatch: [
    '**/tests/js/**/*.test.js',
    '**/tests/js/**/*.spec.js'
  ],

  // Setup files
  setupFilesAfterEnv: ['<rootDir>/tests/js/setup.js'],

  // Coverage configuration
  collectCoverage: true,
  coverageDirectory: 'coverage/js',
  coverageReporters: ['html', 'text', 'lcov', 'json'],

  // Coverage thresholds
  coverageThreshold: {
    global: {
      branches: 80,
      functions: 80,
      lines: 80,
      statements: 80
    }
  },

  // Files to collect coverage from
  collectCoverageFrom: [
    'assets/js/**/*.js',
    '!assets/js/**/*.min.js',
    '!**/node_modules/**',
    '!**/vendor/**'
  ],

  // Module paths
  modulePaths: ['<rootDir>'],

  // Transform files
  transform: {
    '^.+\\.js$': 'babel-jest'
  },

  // Mock files
  moduleNameMapper: {
    '\\.(css|less|scss|sass)$': '<rootDir>/tests/js/__mocks__/styleMock.js'
  },

  // Ignore patterns
  testPathIgnorePatterns: [
    '/node_modules/',
    '/vendor/'
  ],

  // Verbose output
  verbose: true
};
