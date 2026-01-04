<?php
/**
 * Handles persistence of AI-generated actions.
 */

if (!defined('ABSPATH')) {
    exit;
}

class AIWP_Action_Handler
{

    /**
     * Initialize the action handler.
     */
    /**
     * Initialize the action handler.
     */
    public static function init()
    {
        // Add hook to inject CSS on frontend head.
        add_action('wp_head', [__CLASS__, 'inject_custom_css']);
        // Inject HTML overrides late in the footer so DOM exists.
        add_action('wp_footer', [__CLASS__, 'inject_custom_html']);
    }

    /**
     * Register the route to save actions.
     */
    public static function register_save_route()
    {
        if (function_exists('register_rest_route')) {
            register_rest_route('aiwp-copilot/v1', '/save', [
                'methods' => 'POST',
                'callback' => [__CLASS__, 'handle_save_request'],
                'permission_callback' => function () {
                    return current_user_can('edit_posts');
                },
            ]);
        }
    }

    /**
     * Handle the request to save an action (per-page CSS).
     */
    public static function handle_save_request(WP_REST_Request $request)
    {
        $params = $request->get_json_params();
        $post_id = isset($params['post_id']) ? absint($params['post_id']) : 0;
        $css = isset($params['css']) ? $params['css'] : [];
        $selector = isset($params['selector']) ? sanitize_text_field($params['selector']) : '';
        $html = isset($params['html']) ? wp_kses_post($params['html']) : '';

        if (!$post_id || !$selector || (empty($css) && empty($html))) {
            return new WP_Error('aiwp_invalid_data', 'Missing required data to save.', ['status' => 400]);
        }

        $saved = [];

        // Persist CSS overrides.
        if (!empty($css) && is_array($css)) {
            $existing_css = get_post_meta($post_id, '_aiwp_custom_css', true) ?: [];
            $existing_css[$selector] = $css;
            update_post_meta($post_id, '_aiwp_custom_css', $existing_css);
            $saved[] = 'css';
        }

        // Persist HTML rewrites.
        if (!empty($html)) {
            $existing_html = get_post_meta($post_id, '_aiwp_custom_html', true) ?: [];
            $existing_html[$selector] = $html;
            update_post_meta($post_id, '_aiwp_custom_html', $existing_html);
            $saved[] = 'html';
        }

        return rest_ensure_response([
            'success' => true,
            'message' => 'Changes saved: ' . implode(', ', $saved),
            'saved' => $saved
        ]);
    }

    /**
     * Inject custom CSS into the head of the page.
     */
    public static function inject_custom_css()
    {
        if (is_admin())
            return;

        $post_id = get_the_ID();
        if (!$post_id)
            return;

        $custom_css = get_post_meta($post_id, '_aiwp_custom_css', true);

        if (!empty($custom_css) && is_array($custom_css)) {
            echo '<style id="aiwp-custom-styles">';
            foreach ($custom_css as $selector => $styles) {
                echo esc_html($selector) . ' { ';
                foreach ($styles as $prop => $val) {
                    echo esc_html($prop) . ': ' . esc_html($val) . ' !important; ';
                }
                echo ' } ';
            }
            echo '</style>';
        }
    }

    /**
     * Inject saved HTML rewrites on the page.
     */
    public static function inject_custom_html()
    {
        if (is_admin()) {
            return;
        }

        $post_id = get_the_ID();
        if (!$post_id) {
            return;
        }

        $custom_html = get_post_meta($post_id, '_aiwp_custom_html', true);
        if (!empty($custom_html) && is_array($custom_html)) {
            echo '<script id="aiwp-html-overrides">';
            echo 'document.addEventListener("DOMContentLoaded", function(){';
            echo 'const changes = ' . wp_json_encode($custom_html) . ';';
            echo 'Object.keys(changes).forEach(function(sel){';
            echo 'document.querySelectorAll(sel).forEach(function(el){ el.innerHTML = changes[sel]; });';
            echo '});';
            echo '});';
            echo '</script>';
        }
    }
}

// Ensure the class is initialized.
AIWP_Action_Handler::init();
