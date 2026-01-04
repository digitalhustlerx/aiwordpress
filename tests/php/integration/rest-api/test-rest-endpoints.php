<?php
/**
 * REST API Endpoints Integration Tests
 *
 * Tests for AIWP_REST_Endpoints class
 */

use PHPUnit\Framework\TestCase;
use Brain\Monkey;
use Brain\Monkey\Functions;

class Test_AIWP_REST_Endpoints extends TestCase {

    protected function setUp(): void {
        parent::setUp();
        Monkey\setUp();

        // Load dependencies
        require_once AIWP_PLUGIN_DIR . 'includes/class-error-handler.php';
        require_once AIWP_PLUGIN_DIR . 'includes/providers/interface-provider.php';
        require_once AIWP_PLUGIN_DIR . 'includes/providers/class-provider-registry.php';
        require_once AIWP_PLUGIN_DIR . 'includes/specialists/class-specialist-registry.php';
        require_once AIWP_PLUGIN_DIR . 'includes/specialists/class-specialist-engine.php';
        require_once AIWP_PLUGIN_DIR . 'includes/core/class-copilot-indexer.php';
        require_once AIWP_PLUGIN_DIR . 'includes/rest-endpoints.php';
    }

    protected function tearDown(): void {
        Monkey\tearDown();
        parent::tearDown();
    }

    /**
     * Test check_permissions with user who can edit posts
     */
    public function test_check_permissions_with_edit_posts_capability() {
        Functions\when('current_user_can')->with('edit_posts')->justReturn(true);

        $result = AIWP_REST_Endpoints::check_permissions();

        $this->assertTrue($result);
    }

    /**
     * Test check_permissions with user who cannot edit posts
     */
    public function test_check_permissions_without_edit_posts_capability() {
        Functions\when('current_user_can')->with('edit_posts')->justReturn(false);

        $result = AIWP_REST_Endpoints::check_permissions();

        $this->assertFalse($result);
    }

    /**
     * Test handle_complete with no messages
     */
    public function test_handle_complete_without_messages() {
        Functions\when('get_current_user_id')->justReturn(1);
        Functions\when('get_transient')->justReturn(false);
        Functions\expect('set_transient')->once();

        // Create mock request
        $request = $this->createMockRequest(array());

        $response = AIWP_REST_Endpoints::handle_complete($request);

        $this->assertInstanceOf('WP_REST_Response', $response);
        $this->assertEquals(400, $response->get_status());

        $data = $response->get_data();
        $this->assertFalse($data['success']);
        $this->assertArrayHasKey('error', $data);
    }

    /**
     * Test handle_complete with rate limit exceeded
     */
    public function test_handle_complete_rate_limit_exceeded() {
        Functions\when('get_current_user_id')->justReturn(1);
        Functions\when('get_transient')->justReturn(100); // At limit

        $request = $this->createMockRequest(array(
            'messages' => array(
                array('role' => 'user', 'content' => 'Test')
            )
        ));

        $response = AIWP_REST_Endpoints::handle_complete($request);

        $this->assertEquals(429, $response->get_status());

        $data = $response->get_data();
        $this->assertFalse($data['success']);
        $this->assertStringContainsString('Rate limit', $data['error']['message']);
    }

    /**
     * Test handle_complete with valid messages
     */
    public function test_handle_complete_with_valid_messages() {
        // Mock rate limit check (pass)
        Functions\when('get_current_user_id')->justReturn(1);
        Functions\when('get_transient')->justReturn(50);
        Functions\expect('set_transient')->once();

        // Mock provider
        $mockProvider = $this->createMock(AIWP_Provider_Interface::class);
        $mockProvider->expects($this->once())
            ->method('complete')
            ->willReturn(array(
                'choices' => array(
                    array('message' => array('content' => 'Response'))
                )
            ));

        // Mock provider registry
        Functions\when('AIWP_Provider_Registry::get_active')->justReturn($mockProvider);

        // Mock specialist engine
        Functions\when('get_option')->alias(function($key) {
            if ($key === 'aiwp_specialist') return 'default';
            if ($key === 'aiwp_debug_mode') return false;
            return false;
        });

        Functions\when('AIWP_Specialist_Engine::inject_specialist_context')
            ->returnArg(0);

        $request = $this->createMockRequest(array(
            'messages' => array(
                array('role' => 'user', 'content' => 'Test message')
            )
        ));

        $response = AIWP_REST_Endpoints::handle_complete($request);

        $this->assertEquals(200, $response->get_status());

        $data = $response->get_data();
        $this->assertTrue($data['success']);
        $this->assertArrayHasKey('data', $data);
    }

    /**
     * Test handle_complete with no provider configured
     */
    public function test_handle_complete_with_no_provider() {
        Functions\when('get_current_user_id')->justReturn(1);
        Functions\when('get_transient')->justReturn(false);
        Functions\expect('set_transient')->once();

        Functions\when('AIWP_Provider_Registry::get_active')->justReturn(null);

        $request = $this->createMockRequest(array(
            'messages' => array(
                array('role' => 'user', 'content' => 'Test')
            )
        ));

        $response = AIWP_REST_Endpoints::handle_complete($request);

        $this->assertEquals(500, $response->get_status());

        $data = $response->get_data();
        $this->assertFalse($data['success']);
        $this->assertStringContainsString('No provider', $data['error']['message']);
    }

    /**
     * Test handle_get_context with valid post ID
     */
    public function test_handle_get_context_with_valid_post() {
        Functions\when('AIWP_Copilot_Indexer::get_page_context')
            ->justReturn(array(
                'id' => 123,
                'title' => 'Test Post',
                'content' => 'Test content'
            ));

        Functions\when('AIWP_Copilot_Indexer::get_elementor_structure')
            ->justReturn(null);

        $request = $this->createMockRequest(array(), array('id' => 123));

        $response = AIWP_REST_Endpoints::handle_get_context($request);

        $this->assertEquals(200, $response->get_status());

        $data = $response->get_data();
        $this->assertTrue($data['success']);
        $this->assertEquals(123, $data['data']['id']);
    }

    /**
     * Test handle_get_context with invalid post ID
     */
    public function test_handle_get_context_with_invalid_post() {
        Functions\when('AIWP_Copilot_Indexer::get_page_context')
            ->justReturn(null);

        $request = $this->createMockRequest(array(), array('id' => 999));

        $response = AIWP_REST_Endpoints::handle_get_context($request);

        $this->assertEquals(404, $response->get_status());

        $data = $response->get_data();
        $this->assertFalse($data['success']);
        $this->assertStringContainsString('not found', $data['error']['message']);
    }

    /**
     * Test handle_get_context includes Elementor structure
     */
    public function test_handle_get_context_includes_elementor() {
        Functions\when('AIWP_Copilot_Indexer::get_page_context')
            ->justReturn(array('id' => 123, 'title' => 'Test'));

        Functions\when('AIWP_Copilot_Indexer::get_elementor_structure')
            ->justReturn(array('widgets' => array()));

        $request = $this->createMockRequest(array(), array('id' => 123));

        $response = AIWP_REST_Endpoints::handle_get_context($request);

        $data = $response->get_data();
        $this->assertArrayHasKey('elementor', $data['data']);
    }

    /**
     * Test handle_validate with valid credentials
     */
    public function test_handle_validate_with_valid_credentials() {
        $mockProvider = $this->createMock(AIWP_Provider_Interface::class);
        $mockProvider->expects($this->once())
            ->method('validate_credentials')
            ->willReturn(true);

        Functions\when('AIWP_Provider_Registry::get_active')
            ->justReturn($mockProvider);

        $request = $this->createMockRequest(array());

        $response = AIWP_REST_Endpoints::handle_validate($request);

        $this->assertEquals(200, $response->get_status());

        $data = $response->get_data();
        $this->assertTrue($data['success']);
    }

    /**
     * Test handle_validate with invalid credentials
     */
    public function test_handle_validate_with_invalid_credentials() {
        $mockProvider = $this->createMock(AIWP_Provider_Interface::class);
        $mockProvider->expects($this->once())
            ->method('validate_credentials')
            ->willReturn(new WP_Error('invalid_api_key', 'Invalid API key'));

        Functions\when('AIWP_Provider_Registry::get_active')
            ->justReturn($mockProvider);

        $request = $this->createMockRequest(array());

        $response = AIWP_REST_Endpoints::handle_validate($request);

        $this->assertEquals(400, $response->get_status());

        $data = $response->get_data();
        $this->assertFalse($data['success']);
    }

    /**
     * Test handle_get_specialists returns all specialists
     */
    public function test_handle_get_specialists_returns_all() {
        $mockSpecialists = array(
            array('id' => 'default', 'name' => 'General AI', 'tier' => 'free'),
            array('id' => 'frontend', 'name' => 'Frontend Specialist', 'tier' => 'tier1')
        );

        Functions\when('AIWP_Specialist_Registry::get_all')
            ->justReturn($mockSpecialists);

        Functions\when('AIWP_Specialist_Engine::has_access')
            ->justReturn(true);

        $request = $this->createMockRequest(array());

        $response = AIWP_REST_Endpoints::handle_get_specialists($request);

        $this->assertEquals(200, $response->get_status());

        $data = $response->get_data();
        $this->assertTrue($data['success']);
        $this->assertCount(2, $data['data']);
    }

    /**
     * Helper: Create mock WP_REST_Request
     */
    private function createMockRequest($body = array(), $params = array()) {
        $request = $this->getMockBuilder('WP_REST_Request')
            ->disableOriginalConstructor()
            ->getMock();

        $request->method('get_param')
            ->willReturnCallback(function($key) use ($body, $params) {
                if (isset($params[$key])) {
                    return $params[$key];
                }
                return isset($body[$key]) ? $body[$key] : null;
            });

        return $request;
    }
}

/**
 * Mock WP_REST_Request class
 */
if (!class_exists('WP_REST_Request')) {
    class WP_REST_Request {
        private $params = array();

        public function get_param($key) {
            return isset($this->params[$key]) ? $this->params[$key] : null;
        }

        public function set_param($key, $value) {
            $this->params[$key] = $value;
        }
    }
}

/**
 * Mock WP_REST_Response class
 */
if (!class_exists('WP_REST_Response')) {
    class WP_REST_Response {
        private $data;
        private $status;

        public function __construct($data = null, $status = 200) {
            $this->data = $data;
            $this->status = $status;
        }

        public function get_data() {
            return $this->data;
        }

        public function get_status() {
            return $this->status;
        }
    }
}
