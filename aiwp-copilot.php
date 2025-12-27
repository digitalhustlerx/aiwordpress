<?php
/**
 * Plugin Name: AIWP Copilot Agent
 * Plugin URI: https://example.com
 * Description: Context‑aware WordPress AI copilot with interchangeable LLM providers.
 * Version: 1.0.0
 * Author: AIWP Team
 * License: GPL2
 */

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants for easy reference.
define('AIWP_COPILOT_URL', plugin_dir_url(__FILE__));

/**
 * Main class for AIWP Copilot.
 */
class AIWPCopilot
{

    /**
     * Constructor.
     */
    public function __construct()
    {
        // Define directory paths first.
        if (!defined('AIWP_COPILOT_DIR')) {
            define('AIWP_COPILOT_DIR', plugin_dir_path(__FILE__));
        }

        // Load Core Classes early for global availability.
        require_once AIWP_COPILOT_DIR . 'includes/class-action-handler.php';
        require_once AIWP_COPILOT_DIR . 'includes/core/class-copilot-indexer.php';
        require_once AIWP_COPILOT_DIR . 'includes/core/class-copilot-filesystem.php';

        // Enqueue scripts and styles on both front and back end.
        add_action('wp_enqueue_scripts', [$this, 'enqueue_assets']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_assets']);

        // Register REST routes for AI processing.
        add_action('rest_api_init', [$this, 'register_rest_routes']);

        // Add admin menu and settings.
        add_action('admin_menu', [$this, 'add_admin_menu']);
        add_action('admin_init', [$this, 'register_settings']);
    }

    /**
     * Add admin menu for AIWP Copilot.
     */
    public function add_admin_menu()
    {
        add_menu_page(
            'AIWP Copilot',
            'AIWP Copilot',
            'manage_options',
            'aiwp-copilot',
            [$this, 'render_settings_page'],
            'dashicons-brain',
            100
        );
    }

    /**
     * Register plugin settings.
     */
    public function register_settings()
    {
        register_setting('aiwp_settings_group', 'aiwp_provider');
        register_setting('aiwp_settings_group', 'aiwp_api_key');
        register_setting('aiwp_settings_group', 'aiwp_model');
        register_setting('aiwp_settings_group', 'aiwp_base_url');
    }

    /**
     * Render the settings page.
     */
    public function render_settings_page()
    {
        require_once AIWP_COPILOT_DIR . 'includes/settings-page.php';
    }

    /**
     * Include file that registers REST routes.
     */
    public function register_rest_routes()
    {
        require_once AIWP_COPILOT_DIR . 'includes/rest-endpoints.php';
        require_once AIWP_COPILOT_DIR . 'includes/class-action-handler.php';

        // Explicitly register the save route from Action Handler
        if (method_exists('AIWP_Action_Handler', 'register_save_route')) {
            AIWP_Action_Handler::register_save_route();
        }
    }

    /**
     * Enqueue the copilot widget assets.
     */
    public function enqueue_assets()
    {
        // Only load for logged‑in users with edit permission to avoid exposing the widget to visitors.
        if (!current_user_can('edit_posts')) {
            return;
        }

        // Stylesheet for the floating widget.
        wp_enqueue_style('aiwp-copilot-css', AIWP_COPILOT_URL . 'assets/css/copilot.css', [], null);

        // JavaScript for the widget. Depends on jQuery.
        wp_enqueue_script('aiwp-copilot-js', AIWP_COPILOT_URL . 'assets/js/copilot.js', ['jquery'], null, true);

        // Pass data to the JavaScript via wp_localize_script.
        wp_localize_script('aiwp-copilot-js', 'AIWP_API', [
            'rest_url' => esc_url_raw(rest_url('aiwp-copilot/v1/process')),
            'save_url' => esc_url_raw(rest_url('aiwp-copilot/v1/save')),
            'nonce' => wp_create_nonce('wp_rest'),
            'post_id' => get_the_ID(),
        ]);
    }
}

// Initialize the plugin.
new AIWPCopilot();