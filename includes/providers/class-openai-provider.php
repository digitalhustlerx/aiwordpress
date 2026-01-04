<?php
/**
 * OpenAI Provider
 * 
 * OpenAI API integration with specialist support
 */

if (!defined('ABSPATH')) {
    exit;
}

class AIWP_OpenAI_Provider implements AIWP_Provider_Interface
{

    /**
     * API endpoint
     */
    private $endpoint;

    /**
     * API key
     */
    private $api_key;

    /**
     * Model
     */
    private $model;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->endpoint = get_option('aiwp_api_endpoint', 'https://api.openai.com/v1');
        $this->api_key = get_option('aiwp_api_key', '');
        $this->model = get_option('aiwp_model', 'gpt-4o');
    }

    /**
     * Send completion request
     */
    public function complete($messages, $options = array())
    {
        if (empty($this->api_key)) {
            return new WP_Error('no_api_key', 'API key not configured');
        }

        // Inject specialist context
        $specialist_id = get_option('aiwp_specialist', 'default');
        $messages = AIWP_Specialist_Engine::inject_specialist_context($messages, $specialist_id);

        // Prepare request
        $body = array(
            'model' => $this->model,
            'messages' => $messages,
            'temperature' => isset($options['temperature']) ? $options['temperature'] : 0.7,
            'max_tokens' => isset($options['max_tokens']) ? $options['max_tokens'] : 2000
        );

        // Add Elementor action if specified
        if (isset($options['elementor_action'])) {
            $body['elementor_action'] = $options['elementor_action'];
        }

        // Debug logging
        if (get_option('aiwp_debug_mode', false)) {
            error_log('AIWP Request: ' . json_encode($body));
        }

        // Send request
        $response = wp_remote_post($this->endpoint . '/chat/completions', array(
            'headers' => array(
                'Authorization' => 'Bearer ' . $this->api_key,
                'Content-Type' => 'application/json'
            ),
            'body' => json_encode($body),
            'timeout' => 60
        ));

        // Handle errors
        if (is_wp_error($response)) {
            return $response;
        }

        $status_code = wp_remote_retrieve_response_code($response);
        $body_response = json_decode(wp_remote_retrieve_body($response), true);

        // Debug logging
        if (get_option('aiwp_debug_mode', false)) {
            error_log('AIWP Response Status: ' . $status_code);
            error_log('AIWP Response Body: ' . json_encode($body_response));
        }

        // Error handling
        if ($status_code !== 200) {
            return $this->parse_error($body_response, $status_code);
        }

        return $body_response;
    }

    /**
     * Parse API error
     */
    private function parse_error($response, $status_code)
    {
        $error_message = 'Unknown error';
        $error_code = 'api_error';

        if (isset($response['error'])) {
            $error = $response['error'];

            if (isset($error['message'])) {
                $error_message = $error['message'];
            }

            if (isset($error['type'])) {
                $error_code = $error['type'];
            }

            // Specific error handling
            if (stripos($error_message, 'API key') !== false || stripos($error_message, 'Incorrect') !== false) {
                $error_code = 'invalid_api_key';
            } elseif (stripos($error_message, 'rate limit') !== false || stripos($error_message, 'quota') !== false) {
                $error_code = 'rate_limit';
            } elseif (stripos($error_message, 'timeout') !== false || $status_code === 408) {
                $error_code = 'timeout';
            }
        }

        return new WP_Error($error_code, $error_message, array('status' => $status_code));
    }

    /**
     * Validate credentials
     */
    public function validate_credentials()
    {
        if (empty($this->api_key)) {
            return new WP_Error('no_api_key', 'API key not configured');
        }

        $response = wp_remote_get($this->endpoint . '/models', array(
            'headers' => array(
                'Authorization' => 'Bearer ' . $this->api_key
            ),
            'timeout' => 10
        ));

        if (is_wp_error($response)) {
            return $response;
        }

        $status_code = wp_remote_retrieve_response_code($response);

        if ($status_code === 200) {
            return true;
        }

        $body = json_decode(wp_remote_retrieve_body($response), true);
        return $this->parse_error($body, $status_code);
    }

    /**
     * Get provider name
     */
    public function get_name()
    {
        return 'OpenAI';
    }

    /**
     * Get provider metadata
     */
    public static function get_metadata()
    {
        return array(
            'id' => 'openai',
            'name' => 'OpenAI',
            'description' => 'Official OpenAI API (Paid)',
            'base_url' => 'https://api.openai.com/v1',
            'signup_url' => 'https://platform.openai.com/api-keys',
            'models' => array(
                array(
                    'id' => 'gpt-4o',
                    'name' => 'GPT-4o',
                    'free' => false,
                    'context' => '128k tokens',
                    'recommended' => true
                ),
                array(
                    'id' => 'gpt-4o-mini',
                    'name' => 'GPT-4o Mini',
                    'free' => false,
                    'context' => '128k tokens'
                ),
                array(
                    'id' => 'gpt-3.5-turbo',
                    'name' => 'GPT-3.5 Turbo',
                    'free' => false,
                    'context' => '16k tokens'
                )
            )
        );
    }
}
