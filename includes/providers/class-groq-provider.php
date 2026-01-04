<?php
/**
 * Groq Provider
 * 
 * Groq API integration - fast, free inference
 */

if (!defined('ABSPATH')) {
    exit;
}

class AIWP_Groq_Provider implements AIWP_Provider_Interface
{

    /**
     * API endpoint
     */
    private $endpoint = 'https://api.groq.com/openai/v1';

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
        $this->api_key = isset($providers['groq']['api_key']) ? $providers['groq']['api_key'] : '';
        $this->model = isset($providers['groq']['model']) ? $providers['groq']['model'] : 'llama-3.3-70b-versatile';
    }

    /**
     * Send completion request
     */
    public function complete($messages, $options = array())
    {
        if (empty($this->api_key)) {
            return new WP_Error('no_api_key', 'Groq API key not configured');
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
            error_log('AIWP Groq Request: ' . json_encode($body));
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
            error_log('AIWP Groq Response Status: ' . $status_code);
            error_log('AIWP Groq Response Body: ' . json_encode($body_response));
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
        }

        return new WP_Error($error_code, 'Groq API: ' . $error_message, array('status' => $status_code));
    }

    /**
     * Validate credentials
     */
    public function validate_credentials()
    {
        if (empty($this->api_key)) {
            return new WP_Error('no_api_key', 'Groq API key not configured');
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
        return 'Groq';
    }

    /**
     * Get provider metadata
     */
    public static function get_metadata()
    {
        return array(
            'id' => 'groq',
            'name' => 'Groq',
            'description' => 'Ultra-fast inference with free tier',
            'base_url' => 'https://api.groq.com/openai/v1',
            'signup_url' => 'https://console.groq.com/keys',
            'models' => array(
                array(
                    'id' => 'llama-3.3-70b-versatile',
                    'name' => 'Llama 3.3 70B',
                    'free' => true,
                    'context' => '8k tokens',
                    'recommended' => true
                ),
                array(
                    'id' => 'mixtral-8x7b-32768',
                    'name' => 'Mixtral 8x7B',
                    'free' => true,
                    'context' => '32k tokens'
                ),
                array(
                    'id' => 'gemma2-9b-it',
                    'name' => 'Gemma 2 9B',
                    'free' => true,
                    'context' => '8k tokens'
                )
            )
        );
    }
}
