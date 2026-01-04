<?php
/**
 * Plugin Name: AIWP Copilot
 * Plugin URI: https://antigravity.dev/aiwp-copilot
 * Description: AI-powered WordPress content assistant with specialist modes for frontend design, SEO, copywriting, and more. Works seamlessly with Elementor and block editor.
 * Version: 2.0.0
 * Author: Antigravity
 * Author URI: https://antigravity.dev
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: aiwp-copilot
 * Domain Path: /languages
 * Requires at least: 6.0
 * Requires PHP: 7.4
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('AIWP_VERSION', '2.0.0');
define('AIWP_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('AIWP_PLUGIN_URL', plugin_dir_url(__FILE__));
define('AIWP_PLUGIN_FILE', __FILE__);

/**
 * Main AIWP Copilot Class
 */
class AIWP_Copilot
{

    /**
     * Single instance
     */
    private static $instance = null;

    /**
     * Get instance
     */
    public static function get_instance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor
     */
    private function __construct()
    {
        $this->load_dependencies();
        $this->init_hooks();
    }

    /**
     * Load required files
     */
    private function load_dependencies()
    {
        // Core classes
        require_once AIWP_PLUGIN_DIR . 'includes/core/class-copilot-indexer.php';
        require_once AIWP_PLUGIN_DIR . 'includes/core/class-copilot-filesystem.php';

        // Provider system
        require_once AIWP_PLUGIN_DIR . 'includes/providers/interface-provider.php';
        require_once AIWP_PLUGIN_DIR . 'includes/providers/class-provider-registry.php';
        require_once AIWP_PLUGIN_DIR . 'includes/providers/class-openai-provider.php';
        require_once AIWP_PLUGIN_DIR . 'includes/providers/class-groq-provider.php';
        require_once AIWP_PLUGIN_DIR . 'includes/providers/class-openrouter-provider.php';
        require_once AIWP_PLUGIN_DIR . 'includes/providers/class-gemini-provider.php';

        // NEW: Specialist system
        require_once AIWP_PLUGIN_DIR . 'includes/specialists/class-specialist-registry.php';
        require_once AIWP_PLUGIN_DIR . 'includes/specialists/class-specialist-engine.php';

        // Error handling
        require_once AIWP_PLUGIN_DIR . 'includes/class-error-handler.php';

        // REST API
        require_once AIWP_PLUGIN_DIR . 'includes/rest-endpoints.php';

        // Settings
        require_once AIWP_PLUGIN_DIR . 'includes/settings-page.php';
    }

    /**
     * Initialize hooks
     */
    private function init_hooks()
    {
        // Enqueue scripts
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));

        // Initialize settings page
        if (is_admin()) {
            new AIWP_Settings_Page();
        }

        // Initialize REST endpoints
        add_action('rest_api_init', array('AIWP_REST_Endpoints', 'register_routes'));

        // Initialize provider registry
        AIWP_Provider_Registry::init();

        // Initialize specialist registry
        AIWP_Specialist_Registry::init();
    }

    /**
     * Enqueue admin scripts and styles
     */
    public function enqueue_admin_scripts($hook)
    {
        // Only load on post editor and settings page
        $allowed_hooks = array('post.php', 'post-new.php', 'settings_page_aiwp-copilot');

        if (!in_array($hook, $allowed_hooks)) {
            return;
        }

        // Main CSS
        wp_enqueue_style(
            'aiwp-copilot-styles',
            AIWP_PLUGIN_URL . 'assets/css/copilot.css',
            array(),
            AIWP_VERSION
        );

        // Main JS
        wp_enqueue_script(
            'aiwp-copilot',
            AIWP_PLUGIN_URL . 'assets/js/copilot.js',
            array('jquery', 'wp-element', 'wp-components'),
            AIWP_VERSION,
            true
        );

        // Elementor scanner (if Elementor is active)
        if (defined('ELEMENTOR_VERSION')) {
            wp_enqueue_script(
                'aiwp-elementor-scanner',
                AIWP_PLUGIN_URL . 'assets/js/elementor-scanner.js',
                array('jquery'),
                AIWP_VERSION,
                true
            );
        }

        // Pass data to JS
        wp_localize_script('aiwp-copilot', 'aiwpCopilot', array(
            'apiUrl' => rest_url('aiwp/v1'),
            'nonce' => wp_create_nonce('wp_rest'),
            'debugMode' => get_option('aiwp_debug_mode', false),
            'specialist' => get_option('aiwp_specialist', 'default'),
            'specialistName' => AIWP_Specialist_Registry::get_specialist_name(get_option('aiwp_specialist', 'default'))
        ));
    }
}

/**
 * Initialize the plugin
 */
function aiwp_copilot_init()
{
    return AIWP_Copilot::get_instance();
}

// Start the plugin
add_action('plugins_loaded', 'aiwp_copilot_init');

/**
 * Activation hook
 */
register_activation_hook(__FILE__, function () {
    // Initialize provider configuration
    $providers = array(
        'groq' => array(
            'api_key' => '',
            'model' => 'llama-3.3-70b-versatile'
        ),
        'openrouter' => array(
            'api_key' => '',
            'model' => 'google/gemini-flash-1.5'
        ),
        'gemini' => array(
            'api_key' => '',
            'model' => 'gemini-1.5-flash'
        ),
        'openai' => array(
            'api_key' => '',
            'model' => 'gpt-4o'
        )
    );

    add_option('aiwp_providers', $providers);
    add_option('aiwp_active_provider', 'groq'); // Default to Groq (free)
    add_option('aiwp_specialist', 'default');
    add_option('aiwp_debug_mode', false);

    // Flush rewrite rules
    flush_rewrite_rules();
});

/**
 * Deactivation hook
 */
register_deactivation_hook(__FILE__, function () {
    // Flush rewrite rules
    flush_rewrite_rules();
});
