<?php
/**
 * OpenRouter Provider
 * 
 * OpenRouter API integration - unified access to many models
 */

if (!defined('ABSPATH')) {
    exit;
}

class AIWP_OpenRouter_Provider implements AIWP_Provider_Interface
{

    /**
     * API endpoint
     */
    private $endpoint = 'https://openrouter.ai/api/v1';

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
        $providers = get_option('aiwp_providers', array());
        $this->api_key = isset($providers['openrouter']['api_key']) ? $providers['openrouter']['api_key'] : '';
        $this->model = isset($providers['openrouter']['model']) ? $providers['openrouter']['model'] : 'google/gemini-flash-1.5';
    }

    /**
     * Send completion request
     */
    public function complete($messages, $options = array())
    {
        if (empty($this->api_key)) {
            return new WP_Error('no_api_key', 'OpenRouter API key not configured');
        }

        // Inject specialist context
        $specialist_id = get_option('aiwp_specialist', 'default');
        $messages = AIWP_Specialist_Engine::inject_specialist_context($messages, $specialist_id);

        // Prepare request
        $body = array(
            'model' => isset($options['model']) ? $options['model'] : $this->model,
            'messages' => $messages,
            'temperature' => isset($options['temperature']) ? $options['temperature'] : 0.7,
            'max_tokens' => isset($options['max_tokens']) ? $options['max_tokens'] : 2000
        );

        // Debug logging
        if (get_option('aiwp_debug_mode', false)) {
            error_log('AIWP OpenRouter Request: ' . json_encode($body));
        }

        // Send request
        $response = wp_remote_post($this->endpoint . '/chat/completions', array(
            'headers' => array(
                'Authorization' => 'Bearer ' . $this->api_key,
                'Content-Type' => 'application/json',
                'HTTP-Referer' => home_url(),
                'X-Title' => 'AIWP Copilot'
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
            error_log('AIWP OpenRouter Response Status: ' . $status_code);
            error_log('AIWP OpenRouter Response Body: ' . json_encode($body_response));
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

            if (isset($error['code'])) {
                $error_code = $error['code'];
            }
        }

        return new WP_Error($error_code, 'OpenRouter API: ' . $error_message, array('status' => $status_code));
    }

    /**
     * Validate credentials
     */
    public function validate_credentials()
    {
        if (empty($this->api_key)) {
            return new WP_Error('no_api_key', 'OpenRouter API key not configured');
        }

        // Test with a simple request
        $test_response = $this->complete(array(
            array('role' => 'user', 'content' => 'Hi')
        ), array('max_tokens' => 5));

        if (is_wp_error($test_response)) {
            return $test_response;
        }

        return true;
    }

    /**
     * Get provider name
     */
    public function get_name()
    {
        return 'OpenRouter';
    }

    /**
     * Get provider metadata
     */
    public static function get_metadata()
    {
        return array(
            'id' => 'openrouter',
            'name' => 'OpenRouter',
            'description' => 'Access to multiple AI models through one API',
            'base_url' => 'https://openrouter.ai/api/v1',
            'signup_url' => 'https://openrouter.ai/keys',
            'models' => array(
                array(
                    'id' => 'google/gemini-flash-1.5',
                    'name' => 'Gemini 1.5 Flash',
                    'free' => true,
                    'context' => '1M tokens',
                    'recommended' => true
                ),
                array(
                    'id' => 'meta-llama/llama-3.1-8b-instruct:free',
                    'name' => 'Llama 3.1 8B (Free)',
                    'free' => true,
                    'context' => '128k tokens'
                ),
                array(
                    'id' => 'gryphe/mythomist-7b:free',
                    'name' => 'Mythomist 7B (Free)',
                    'free' => true,
                    'context' => '32k tokens'
                )
            )
        );
    }
}
