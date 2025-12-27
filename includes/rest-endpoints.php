<?php
/**
 * REST API endpoints for AIWP Copilot.
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Register the AIWP Copilot REST route.
 */
if (function_exists('register_rest_route')) {
    register_rest_route('aiwp-copilot/v1', '/process', [
        'methods' => 'POST',
        'callback' => 'aiwp_copilot_process_request',
        'permission_callback' => function () {
            return current_user_can('edit_posts');
        },
    ]);
}

/**
 * Process the incoming AI request.
 */
function aiwp_copilot_process_request(WP_REST_Request $request)
{
    $params = $request->get_json_params();
    $message = isset($params['message']) ? sanitize_text_field($params['message']) : '';
    $context = isset($params['context']) ? $params['context'] : '';
    $selector = isset($params['selector']) ? sanitize_text_field($params['selector']) : '';
    $html = isset($params['html']) ? wp_kses_post($params['html']) : '';

    // Load dependencies
    require_once AIWP_COPILOT_DIR . 'includes/class-action-handler.php';
    AIWP_Action_Handler::register_save_route();

    require_once AIWP_COPILOT_DIR . 'includes/providers/class-provider-registry.php';
    require_once AIWP_COPILOT_DIR . 'includes/core/class-copilot-indexer.php';
    require_once AIWP_COPILOT_DIR . 'includes/core/class-copilot-filesystem.php';

    $provider = AIWP_Provider_Registry::get_active_provider();
    $settings = AIWP_Provider_Registry::get_settings();
    $settings['selector'] = $selector;
    $settings['html'] = $html;

    // Fast handle for local tool: list files
    if (strpos(strtolower($message), 'list files') !== false) {
        $files = AIWP_Copilot_Indexer::scan_theme();
        return rest_ensure_response([
            'reply' => "I have scanned your active theme files.\n\nFile Map (Top 10):\n" . json_encode(array_slice($files, 0, 10), JSON_PRETTY_PRINT),
            'action' => 'none',
            'data' => $files
        ]);
    }

    if (empty($settings['api_key'])) {
        return rest_ensure_response([
            'reply' => 'Warning: No API Key found. Go to settings.',
            'action' => 'none',
            'data' => (object) [],
        ]);
    }

    try {
        $response_data = $provider->process($message, $context, $settings);
    } catch (Exception $e) {
        return rest_ensure_response([
            'reply' => 'Brain error: ' . $e->getMessage(),
            'action' => 'none',
            'data' => []
        ]);
    }

    // Tool Execution
    if (isset($response_data['action'])) {
        $action = $response_data['action'];
        $data = isset($response_data['data']) ? (array) $response_data['data'] : [];

        // Fallback: if AI omitted selector, but user provided one, reuse it.
        if (empty($data['selector']) && !empty($selector)) {
            $data['selector'] = $selector;
            $response_data['data'] = $data;
        }

        if ($action === 'read_file' && !empty($data['path'])) {
            new AIWP_Copilot_Filesystem();
            $content = AIWP_Copilot_Filesystem::read_file($data['path']);
            if (!is_wp_error($content) && $content !== false) {
                $response_data['reply'] .= "\n[System]: Read " . $data['path'];
                $response_data['data'] = (object) array_merge((array) $response_data['data'], ['content' => $content]);
            } else {
                $response_data['reply'] .= "\n[System]: Failed to read file.";
            }
        }

        if ($action === 'write_file' && !empty($data['path']) && !empty($data['content'])) {
            new AIWP_Copilot_Filesystem();
            $res = AIWP_Copilot_Filesystem::write_file($data['path'], $data['content']);
            if (!is_wp_error($res) && $res !== false) {
                $response_data['reply'] .= "\n[System]: Saved " . $data['path'];
            } else {
                $response_data['reply'] .= "\n[System]: Failed to save file.";
            }
        }
    }

    return rest_ensure_response($response_data);
}
