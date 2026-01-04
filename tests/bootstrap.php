<?php
/**
 * PHPUnit Bootstrap File
 */

// Composer autoload
require_once __DIR__ . '/../vendor/autoload.php';

// Define plugin constants
define('AIWP_PLUGIN_DIR', dirname(__DIR__) . '/');
define('AIWP_PLUGIN_URL', 'http://example.org/wp-content/plugins/aiwp-copilot/');
define('AIWP_VERSION', '2.0.0');
define('AIWP_PLUGIN_FILE', AIWP_PLUGIN_DIR . 'aiwp-copilot.php');

// Define WordPress constants if not already defined
if (!defined('ABSPATH')) {
    define('ABSPATH', '/tmp/wordpress/');
}

// Brain Monkey setup for WordPress function mocking
\Brain\Monkey\setUp();

// Register shutdown function to tear down Brain Monkey
register_shutdown_function(function() {
    \Brain\Monkey\tearDown();
});
