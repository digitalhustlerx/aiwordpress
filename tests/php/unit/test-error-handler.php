<?php
/**
 * Error Handler Tests
 *
 * Tests for AIWP_Error_Handler class
 */

use PHPUnit\Framework\TestCase;
use Brain\Monkey;
use Brain\Monkey\Functions;

class Test_AIWP_Error_Handler extends TestCase {

    protected function setUp(): void {
        parent::setUp();
        Monkey\setUp();

        // Load the class
        require_once AIWP_PLUGIN_DIR . 'includes/class-error-handler.php';
    }

    protected function tearDown(): void {
        Monkey\tearDown();
        parent::tearDown();
    }

    /**
     * Test rate limit allows first request
     */
    public function test_rate_limit_allows_first_request() {
        // Mock WordPress functions
        Functions\when('get_current_user_id')->justReturn(1);
        Functions\when('get_transient')->justReturn(false);
        Functions\expect('set_transient')
            ->once()
            ->with('aiwp_rate_limit_1', 1, 3600)
            ->andReturn(true);

        $result = AIWP_Error_Handler::check_rate_limit();

        $this->assertTrue($result, 'First request should be allowed');
    }

    /**
     * Test rate limit increments counter
     */
    public function test_rate_limit_increments_counter() {
        Functions\when('get_current_user_id')->justReturn(1);
        Functions\when('get_transient')->justReturn(50);
        Functions\expect('set_transient')
            ->once()
            ->with('aiwp_rate_limit_1', 51, 3600)
            ->andReturn(true);

        $result = AIWP_Error_Handler::check_rate_limit();

        $this->assertTrue($result, 'Request should increment counter');
    }

    /**
     * Test rate limit blocks at limit
     */
    public function test_rate_limit_blocks_at_limit() {
        Functions\when('get_current_user_id')->justReturn(1);
        Functions\when('get_transient')->justReturn(100);

        $result = AIWP_Error_Handler::check_rate_limit();

        $this->assertFalse($result, 'Should block at 100 requests');
    }

    /**
     * Test rate limit blocks above limit
     */
    public function test_rate_limit_blocks_above_limit() {
        Functions\when('get_current_user_id')->justReturn(1);
        Functions\when('get_transient')->justReturn(150);

        $result = AIWP_Error_Handler::check_rate_limit();

        $this->assertFalse($result, 'Should block above 100 requests');
    }

    /**
     * Test rate limit per user (different users)
     */
    public function test_rate_limit_per_user() {
        // User 1 at limit
        Functions\when('get_current_user_id')->justReturn(1);
        Functions\when('get_transient')->alias(function($key) {
            return $key === 'aiwp_rate_limit_1' ? 100 : false;
        });

        $result1 = AIWP_Error_Handler::check_rate_limit(1);
        $this->assertFalse($result1, 'User 1 should be blocked');

        // User 2 should still be allowed
        Functions\expect('set_transient')
            ->once()
            ->with('aiwp_rate_limit_2', 1, 3600)
            ->andReturn(true);

        $result2 = AIWP_Error_Handler::check_rate_limit(2);
        $this->assertTrue($result2, 'User 2 should be allowed');
    }

    /**
     * Test get_user_message for invalid API key
     */
    public function test_get_user_message_invalid_api_key() {
        $error = new WP_Error('invalid_api_key', 'Invalid API key provided');

        $message = AIWP_Error_Handler::get_user_message($error);

        $this->assertStringContainsString('🔑', $message);
        $this->assertStringContainsString('API Key Error', $message);
        $this->assertStringContainsString('Invalid API key provided', $message);
    }

    /**
     * Test get_user_message for rate limit
     */
    public function test_get_user_message_rate_limit() {
        $error = new WP_Error('rate_limit', 'Rate limit exceeded');

        $message = AIWP_Error_Handler::get_user_message($error);

        $this->assertStringContainsString('⏱️', $message);
        $this->assertStringContainsString('Rate Limit', $message);
    }

    /**
     * Test get_user_message for rate limit exceeded
     */
    public function test_get_user_message_rate_limit_exceeded() {
        $error = new WP_Error('rate_limit_exceeded', 'Too many requests');

        $message = AIWP_Error_Handler::get_user_message($error);

        $this->assertStringContainsString('⏱️', $message);
        $this->assertStringContainsString('100 requests per hour', $message);
    }

    /**
     * Test get_user_message for timeout
     */
    public function test_get_user_message_timeout() {
        $error = new WP_Error('timeout', 'Request timeout');

        $message = AIWP_Error_Handler::get_user_message($error);

        $this->assertStringContainsString('⏰', $message);
        $this->assertStringContainsString('Timeout', $message);
    }

    /**
     * Test get_user_message for connection error
     */
    public function test_get_user_message_connection_error() {
        $error = new WP_Error('connection_error', 'Could not connect');

        $message = AIWP_Error_Handler::get_user_message($error);

        $this->assertStringContainsString('❌', $message);
        $this->assertStringContainsString('Connection Error', $message);
    }

    /**
     * Test get_user_message for unknown error
     */
    public function test_get_user_message_unknown_error() {
        $error = new WP_Error('unknown', 'Something went wrong');

        $message = AIWP_Error_Handler::get_user_message($error);

        $this->assertStringContainsString('❌', $message);
        $this->assertStringContainsString('Something went wrong', $message);
    }

    /**
     * Test get_user_message with non-WP_Error
     */
    public function test_get_user_message_non_wp_error() {
        $message = AIWP_Error_Handler::get_user_message('not an error');

        $this->assertEquals('An unknown error occurred', $message);
    }

    /**
     * Test log_error when debug mode enabled
     */
    public function test_log_error_when_debug_enabled() {
        Functions\when('get_option')->alias(function($key) {
            return $key === 'aiwp_debug_mode' ? true : false;
        });

        Functions\expect('error_log')
            ->once()
            ->with(Mockery::type('string'));

        $error = new WP_Error('test', 'Test error');
        AIWP_Error_Handler::log_error($error, array('test' => 'data'));

        // Assertion is handled by Mockery expectations
        $this->assertTrue(true);
    }

    /**
     * Test log_error when debug mode disabled
     */
    public function test_log_error_when_debug_disabled() {
        Functions\when('get_option')->justReturn(false);
        Functions\expect('error_log')->never();

        $error = new WP_Error('test', 'Test error');
        AIWP_Error_Handler::log_error($error);

        // Assertion is handled by Mockery expectations
        $this->assertTrue(true);
    }

    /**
     * Test format_api_error structure
     */
    public function test_format_api_error_structure() {
        $error = new WP_Error('test', 'Test error');

        $formatted = AIWP_Error_Handler::format_api_error($error);

        $this->assertIsArray($formatted);
        $this->assertArrayHasKey('success', $formatted);
        $this->assertArrayHasKey('error', $formatted);
        $this->assertFalse($formatted['success']);
        $this->assertIsArray($formatted['error']);
        $this->assertArrayHasKey('code', $formatted['error']);
        $this->assertArrayHasKey('message', $formatted['error']);
    }

    /**
     * Test format_api_error with WP_Error
     */
    public function test_format_api_error_with_wp_error() {
        $error = new WP_Error('invalid_api_key', 'API key is invalid');

        $formatted = AIWP_Error_Handler::format_api_error($error);

        $this->assertEquals('invalid_api_key', $formatted['error']['code']);
        $this->assertStringContainsString('🔑', $formatted['error']['message']);
    }

    /**
     * Test format_api_error with non-WP_Error
     */
    public function test_format_api_error_with_non_wp_error() {
        $formatted = AIWP_Error_Handler::format_api_error('string error');

        $this->assertEquals('unknown', $formatted['error']['code']);
        $this->assertEquals('An unknown error occurred', $formatted['error']['message']);
    }
}

/**
 * Mock WP_Error class for testing
 */
if (!class_exists('WP_Error')) {
    class WP_Error {
        private $code;
        private $message;
        private $data;

        public function __construct($code, $message, $data = '') {
            $this->code = $code;
            $this->message = $message;
            $this->data = $data;
        }

        public function get_error_code() {
            return $this->code;
        }

        public function get_error_message() {
            return $this->message;
        }

        public function get_error_data() {
            return $this->data;
        }
    }

    function is_wp_error($thing) {
        return ($thing instanceof WP_Error);
    }
}
