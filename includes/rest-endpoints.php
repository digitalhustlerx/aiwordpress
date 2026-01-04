<?php
/**
 * REST API Endpoints
 * 
 * Handles all REST API routes
 */

if (!defined('ABSPATH')) {
    exit;
}

class AIWP_REST_Endpoints
{

    /**
     * Register routes
     */
    public static function register_routes()
    {
        // Complete endpoint
        register_rest_route('aiwp/v1', '/complete', array(
            'methods' => 'POST',
            'callback' => array(__CLASS__, 'handle_complete'),
            'permission_callback' => array(__CLASS__, 'check_permissions')
        ));

        // Context endpoint
        register_rest_route('aiwp/v1', '/context/(?P<id>\d+)', array(
            'methods' => 'GET',
            'callback' => array(__CLASS__, 'handle_get_context'),
            'permission_callback' => array(__CLASS__, 'check_permissions')
        ));

        // Validate endpoint
        register_rest_route('aiwp/v1', '/validate', array(
            'methods' => 'POST',
            'callback' => array(__CLASS__, 'handle_validate'),
            'permission_callback' => array(__CLASS__, 'check_permissions')
        ));

        // Specialists endpoint
        register_rest_route('aiwp/v1', '/specialists', array(
            'methods' => 'GET',
            'callback' => array(__CLASS__, 'handle_get_specialists'),
            'permission_callback' => array(__CLASS__, 'check_permissions')
        ));

        // Providers endpoint
        register_rest_route('aiwp/v1', '/providers', array(
            'methods' => 'GET',
            'callback' => array(__CLASS__, 'handle_get_providers'),
            'permission_callback' => array(__CLASS__, 'check_permissions')
        ));

        // Switch provider endpoint
        register_rest_route('aiwp/v1', '/providers/switch', array(
            'methods' => 'POST',
            'callback' => array(__CLASS__, 'handle_switch_provider'),
            'permission_callback' => array(__CLASS__, 'check_permissions')
        ));
    }

    /**
     * Check permissions
     */
    public static function check_permissions()
    {
        return current_user_can('edit_posts');
    }

    /**
     * Handle complete request
     */
    public static function handle_complete($request)
    {
        // Check rate limit
        if (!AIWP_Error_Handler::check_rate_limit()) {
            return new WP_REST_Response(
                AIWP_Error_Handler::format_api_error(
                    new WP_Error('rate_limit_exceeded', 'Rate limit exceeded')
                ),
                429
            );
        }

        // Get request data
        $messages = $request->get_param('messages');
        $options = $request->get_param('options') ?: array();

        // Validate
        if (empty($messages)) {
            return new WP_REST_Response(
                AIWP_Error_Handler::format_api_error(
                    new WP_Error('invalid_request', 'Messages are required')
                ),
                400
            );
        }

        // Get provider
        $provider = AIWP_Provider_Registry::get_active();

        if (!$provider) {
            return new WP_REST_Response(
                AIWP_Error_Handler::format_api_error(
                    new WP_Error('no_provider', 'No provider configured')
                ),
                500
            );
        }

        // Send request
        $response = $provider->complete($messages, $options);

        // Handle errors
        if (is_wp_error($response)) {
            AIWP_Error_Handler::log_error($response, array(
                'messages' => $messages,
                'options' => $options
            ));

            return new WP_REST_Response(
                AIWP_Error_Handler::format_api_error($response),
                500
            );
        }

        // Success
        return new WP_REST_Response(array(
            'success' => true,
            'data' => $response
        ), 200);
    }

    /**
     * Get page context
     */
    public static function handle_get_context($request)
    {
        $post_id = $request->get_param('id');

        $context = AIWP_Copilot_Indexer::get_page_context($post_id);

        if (!$context) {
            return new WP_REST_Response(
                AIWP_Error_Handler::format_api_error(
                    new WP_Error('not_found', 'Post not found')
                ),
                404
            );
        }

        // Add Elementor structure if available
        $elementor_structure = AIWP_Copilot_Indexer::get_elementor_structure($post_id);
        if ($elementor_structure) {
            $context['elementor'] = $elementor_structure;
        }

        return new WP_REST_Response(array(
            'success' => true,
            'data' => $context
        ), 200);
    }

    /**
     * Validate API credentials
     */
    public static function handle_validate($request)
    {
        $provider = AIWP_Provider_Registry::get_active();

        if (!$provider) {
            return new WP_REST_Response(
                AIWP_Error_Handler::format_api_error(
                    new WP_Error('no_provider', 'No provider configured')
                ),
                500
            );
        }

        $result = $provider->validate_credentials();

        if (is_wp_error($result)) {
            return new WP_REST_Response(
                AIWP_Error_Handler::format_api_error($result),
                400
            );
        }

        return new WP_REST_Response(array(
            'success' => true,
            'message' => 'API credentials are valid'
        ), 200);
    }

    /**
     * Get available specialists
     */
    public static function handle_get_specialists($request)
    {
        $specialists = AIWP_Specialist_Registry::get_all();

        // Filter to only include accessible specialists
        $accessible = array_filter($specialists, function ($specialist) {
            return AIWP_Specialist_Engine::has_access($specialist['id']);
        });

        return new WP_REST_Response(array(
            'success' => true,
            'data' => array_values($accessible)
        ), 200);
    }

    /**
     * Get available providers and their configuration
     */
    public static function handle_get_providers($request)
    {
        $providers_metadata = AIWP_Provider_Registry::get_all_metadata();
        $providers_config = get_option('aiwp_providers', array());
        $active_provider = get_option('aiwp_active_provider', 'groq');

        // Merge metadata with configuration status
        $providers = array();
        foreach ($providers_metadata as $metadata) {
            $provider_id = $metadata['id'];
            $is_configured = !empty($providers_config[$provider_id]['api_key']);

            $providers[] = array(
                'id' => $provider_id,
                'name' => $metadata['name'],
                'description' => $metadata['description'],
                'signup_url' => $metadata['signup_url'],
                'models' => $metadata['models'],
                'is_configured' => $is_configured,
                'is_active' => ($provider_id === $active_provider),
                'current_model' => isset($providers_config[$provider_id]['model']) ?
                    $providers_config[$provider_id]['model'] :
                    $metadata['models'][0]['id']
            );
        }

        return new WP_REST_Response(array(
            'success' => true,
            'data' => $providers
        ), 200);
    }

    /**
     * Switch active provider
     */
    public static function handle_switch_provider($request)
    {
        $provider_id = $request->get_param('provider_id');
        $model = $request->get_param('model');

        if (empty($provider_id)) {
            return new WP_REST_Response(
                AIWP_Error_Handler::format_api_error(
                    new WP_Error('invalid_request', 'Provider ID is required')
                ),
                400
            );
        }

        // Verify provider exists
        $provider = AIWP_Provider_Registry::get($provider_id);
        if (!$provider) {
            return new WP_REST_Response(
                AIWP_Error_Handler::format_api_error(
                    new WP_Error('invalid_provider', 'Provider not found')
                ),
                404
            );
        }

        // Update active provider
        update_option('aiwp_active_provider', $provider_id);

        // Update model if provided
        if (!empty($model)) {
            $providers_config = get_option('aiwp_providers', array());
            if (!isset($providers_config[$provider_id])) {
                $providers_config[$provider_id] = array();
            }
            $providers_config[$provider_id]['model'] = $model;
            update_option('aiwp_providers', $providers_config);
        }

        return new WP_REST_Response(array(
            'success' => true,
            'message' => 'Provider switched successfully',
            'provider' => $provider_id,
            'model' => $model
        ), 200);
    }
}

